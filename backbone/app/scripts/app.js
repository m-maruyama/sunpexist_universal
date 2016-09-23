define([
		'backbone.marionette',
		'underscore',
		'jquery',
		'jquery-spin',
	],
	function(Marionette,_,$) {
		'use strict';
		var App = new Backbone.Marionette.Application();
		App.moduleList = {
			'AdminHome': 'modules/admin/Home',
			'AdminLogin': 'modules/admin/Login',
			'AdminImportCsv': 'modules/admin/ImportCsv',
			'AdminHistory': 'modules/admin/History',
			'AdminDelivery': 'modules/admin/Delivery',
			'AdminUnreturn': 'modules/admin/Unreturn',
			'AdminUnreturned': 'modules/admin/Unreturned',
			'AdminStock': 'modules/admin/Stock',
			'AdminLend': 'modules/admin/Lend',
			'AdminReceive': 'modules/admin/Receive',
			'AdminManpowerInfo': 'modules/admin/ManpowerInfo',
			'AdminWearer': 'modules/admin/Wearer',
			'AdminAccount': 'modules/admin/Account',
			'AdminInfo': 'modules/admin/Info',
			'AdminPassword': 'modules/admin/Password',
			'AdminPurchaseInput': 'modules/admin/PurchaseInput',
			'AdminPurchaseHistory': 'modules/admin/PurchaseHistory',
			'AdminWearerInput': 'modules/admin/WearerInput',
			'AdminWearerEnd': 'modules/admin/WearerEnd',
			'AdminWearerEndOrder': 'modules/admin/WearerEndOrder',
			'AdminWearerChange': 'modules/admin/WearerChange',
			'AdminWearerChangeOrder': 'modules/admin/WearerChangeOrder',
			'AdminWearerInputComplete': 'modules/admin/WearerInputComplete',
		};
		App.addRegions({
			// "alert": "#alert",
		});

		App.on("start", function(){
			if (Backbone.history) {
				App.currentModule = $module;
				if(!App.moduleList[$module]) {
					throw "Invalid Module Name.";
				}
				require([App.moduleList[$module]], function () {
					Backbone.history.start();
				});
			}

			App.log('Initialization Finished', 'App', 2);
		});

		// An init function for your main application object
		App.addInitializer(function() {
			this.debug = 1;
			this.root = '/'; // <- insert app name here? eg: app-name/
		});

		App.navigate = function(route, options) {
			Backbone.history.navigate(route, options || {});
		};

		App.getCurrentRoute = function() {
			return Backbone.history.fragment;
		};

		/**
		 * Log function.
		 * Pass all messages through here so we can disable for prod
		 */
		App.log = function(message, domain, level) {
			if (App.debug < level) {
				return;
			}
			if (typeof message !== 'string') {
				console.log('Fancy object (' + domain + ')', message);
			} else {
				console.log((domain || false ? '(' + domain + ') ' : '') + message);
			}
		};
		//共通の関数を定義
		App.fn = [];

		//定数もどき
		App.const = {};

		App.const.logedOutUrl = "//" + location.host + '/universal/login.html';//403の時の飛び先

		//アカウントロックの時の飛び先
		App.const.accountLockText = 'ログインが規定回数以上失敗したためアカウントがロックされました。';
		App.const.accountLockUrl = 'https://xxxxxx/AccountLock.jsp';


		//グローバルに変数を使いたかったらこれを使うこと！！
		App.container = {};
		App.container.logout = false;


		/**
		 * APIのURL
		 * @type {{}}
		 */

		var host = "//" + location.host;
		App.container.withCredentials = true;//CORSでアクセスしたいときはこれをtrueにすること。
		App.api = {
			"CM0001": host + "/account_session",
			"CM0010": host + "/job_type",
			"CM0020": host + "/section",
			"CM0021": host + "/section_purchase",
			"CM0030": host + "/detail",
			"CM0040": host + "/log",
			"CM0050": host + "/zaiko_job_type",
			"CM0051": host + "/zaiko_item",
			"CM0052": host + "/zaiko_item_color",
			"CM0060": host + "/agreement_no",
			"CM0061": host + "/agreement_no_input",
			"CM0062": host + "/agreement_no_change",
			"CM0063": host + "/corporate_id",
			"CM0064": host + "/corporate_id_all",//全て選択肢あり
			"CM0070": host + "/input_item",
			"CM0080": host + "/item_color",
			"CM0090": host + "/section_modal",
			"CM0100": host + "/individual_num",
			"CM0110": host + "/sex_kbn",
			"IM0010": host + "/import_csv",
			"IM0020": host + "/csv",
			"DL0010": host + "/csv_download",
			"HI0010": host + "/history/search",
			"DE0010": host + "/delivery/search",
			"DE0020": host + "/delivery/download",
			"UN0010": host + "/unreturn/search",
			"UD0010": host + "/unreturned/search",
			"UD0020": host + "/unreturned/download",
			"ST0010": host + "/stock/search",
			"ST0020": host + "/stock/download",
			"LE0010": host + "/lend/search",
			"LE0020": host + "/lend/download",
			"RE0010": host + "/receive/search",
			"RE0020": host + "/receive/update",
			"MI0010": host + "/manpower_info/search",
			"MI0020": host + "/manpower_info/detail",
			"MI0030": host + "/manpower_info/download",
			"WE0010": host + "/wearer/search",
			"WE0020": host + "/wearer/detail",
			"AC0010": host + "/account/search",
			"AC0020": host + "/account/modal",
			"IN0010": host + "/info/search",
			"IN0020": host + "/info/modal",
			"HM0010": host + "/home",
			"LO0010": host + "/login",
			"OU0010": host + "/logout",
			"GL0010": host + "/global_menu",
			"PA0010": host + "/password",
			"PI0010": host + "/purchase_input",
			"PU0010": host + "/purchase_update",
			"PH0010": host + "/purchase_history/search",
			"PN0010": host + "/purchase/agreement_no",
			"PH0011": host + "/purchase/input_item",
			"PH0012": host + "/purchase/item_color",
			"WI0010": host + "/wearer_input",
			"WI0011": host + "/change_section",
			"WI0012": host + "/input_insert",
			"WN0010": host + "/wearer_end/search",
			"WC0010": host + "/wearer_change/search",
			"WC0011": host + "/wearer_change/req_param",
			"CM9010": host + "/api/CM9010"
		};


		App.error = function (message) {
			alert(message);
		};

		/**
		 * クルクルマーク
		 * @param target jQueryオブジェクト
		 * @param preset
		 */
		App.fn.spin = function (target, preset) {
			if(preset === 'destroy'){
				target.spin(false);
				return;
			}
			if(!preset) {
				preset = 'default';
			}
			setTimeout(function(){
				target.spin(preset);
			},10);
		};
		return App;

	}
);
