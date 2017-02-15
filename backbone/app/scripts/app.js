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
			'AdminInquiry': 'modules/admin/Inquiry',
			'AdminInquiryInput': 'modules/admin/InquiryInput',
			'AdminInquiryDetail': 'modules/admin/InquiryDetail',
			'AdminHistory': 'modules/admin/History',
			'AdminUnreturn': 'modules/admin/Unreturn',
			'AdminPrint': 'modules/admin/Print',
			'AdminStock': 'modules/admin/Stock',
			'AdminLend': 'modules/admin/Lend',
			'AdminReceive': 'modules/admin/Receive',
			'AdminManpowerInfo': 'modules/admin/ManpowerInfo',
			'AdminWearer': 'modules/admin/Wearer',
			'AdminAccount': 'modules/admin/Account',
			'AdminInfo': 'modules/admin/Info',
			'AdminQa': 'modules/admin/Qa',
			'AdminQaInput': 'modules/admin/QaInput',
			'AdminPassword': 'modules/admin/Password',
			'AdminLoginPassword': 'modules/admin/LoginPassword',
			'AdminOrderSend': 'modules/admin/OrderSend',
			'AdminPurchaseInput': 'modules/admin/PurchaseInput',
			'AdminPurchaseHistory': 'modules/admin/PurchaseHistory',
			'AdminWearerEdit': 'modules/admin/WearerEdit',
			'AdminWearerEditOrder': 'modules/admin/WearerEditOrder',
			'AdminWearerInput': 'modules/admin/WearerInput',
			'AdminWearerEnd': 'modules/admin/WearerEnd',
			'AdminWearerEndOrder': 'modules/admin/WearerEndOrder',
			'AdminWearerChange': 'modules/admin/WearerChange',
			'AdminWearerChangeOrder': 'modules/admin/WearerChangeOrder',
			'AdminWearerOther': 'modules/admin/WearerOther',
			'AdminWearerSizeChange': 'modules/admin/WearerSizeChange',
			'AdminWearerOtherChangeOrder': 'modules/admin/WearerOtherChangeOrder',
			'AdminWearerExchangeOrder': 'modules/admin/WearerExchangeOrder',
			'AdminWearerAddOrder': 'modules/admin/WearerAddOrder',
			'AdminWearerReturnOrder': 'modules/admin/WearerReturnOrder',
			'AdminWearerInputComplete': 'modules/admin/WearerInputComplete',
			'AdminWearerSearch': 'modules/admin/WearerSearch',
			'AdminWearerOrder': 'modules/admin/WearerOrder',
			'AdminWearerOrderComplete': 'modules/admin/WearerOrderComplete',
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
//		var host = "//" + location.host + "/app";
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
			"CM0063": host + "/corporate_id",
			"CM0064": host + "/corporate_id_all",
			"CM0070": host + "/input_item",
			"CM0080": host + "/item_color",
			"CM0090": host + "/section_modal",
			"CM0100": host + "/individual_num",
			"CM0110": host + "/sex_kbn",
			"CM0120": host + "/reason_kbn",
			"CM0130": host + "/update_possible_chk",
			"CM0140": host + "/btn_possible_chk",
			"CM0150": host + "/snd_kbn",
			"IM0010": host + "/import_csv",
			"IM0020": host + "/csv",
			"DL0010": host + "/csv_download",
			"DL0020": host + "/common_download",
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
			"RE0030": host + "/receive/check",
			"MI0010": host + "/manpower_info/search",
			"MI0020": host + "/manpower_info/detail",
			"MI0030": host + "/manpower_info/download",
			"WE0010": host + "/wearer/search",
			"WE0020": host + "/wearer/detail",
			"AC0010": host + "/account/search",
			"AC0020": host + "/account/modal",
			"IN0010": host + "/info/search",
			"IN0020": host + "/info/add",
			"IN0030": host + "/info/edit",
			"IN0040": host + "/info/delete",
			"CU0010": host + "/inquiry/corporate",
			"CU0011": host + "/inquiry/agreement_no",
			"CU0012": host + "/inquiry/genre",
			"CU0013": host + "/inquiry/section",
			"CU0014": host + "/inquiry/section_modal",
			"CU0020": host + "/inquiry/search",
			"CU0030": host + "/inquiry/input",
			"CU0031": host + "/inquiry/complete",
			"CU0040": host + "/inquiry/detail",
			"CU0041": host + "/inquiry/update",
			"QA0010": host + "/qa/condition",
			"QA0020": host + "/qa/search",
			"QA0030": host + "/qa/input",
			"HM0010": host + "/home",
			"HM0011": host + "/home_manual",
			"LO0010": host + "/login",
			"OU0010": host + "/logout",
			"LP0010": host + "/login_password",
			"OS0010": host + "/order_send/search",
			"OS0011": host + "/order_send/send",
			"OS0012": host + "/order_send/cancel",
			"OS0013": host + "/order_send/delete",
			"GL0010": host + "/global_menu",
			"PA0010": host + "/password",
			"PR0010": host + "/print/search",
			"PR0011": host + "/print/pdf",
			"PR0012": host + "/print/pdf_tran",
			"PI0010": host + "/purchase_input",
			"PU0010": host + "/purchase_update",
			"PH0010": host + "/purchase_history/search",
			"PN0010": host + "/purchase/agreement_no",
			"PH0011": host + "/purchase/input_item",
			"PH0012": host + "/purchase/item_color",
			"WU0010": host + "/wearer_edit/search",
			"WU0011": host + "/wearer_edit/req_param",
			"WU0012": host + "/wearer_edit_info",
			"WU0013": host + "/wearer_edit_delete",
			"WU0014": host + "/wearer_edit_complete",
			"WU0015": host + "/wearer_edit_send",
			"WU0016": host + "/wearer_edit/order_check",
			"WI0010": host + "/wearer_input",
			"WI0011": host + "/change_section",
			"WI0012": host + "/input_insert",
			"WI0013": host + "/input_delete_check",
			"WI0014": host + "/input_delete",
			"WI0015": host + "/wearer_item_button",
			"WN0010": host + "/wearer_end/search",
			"WN0011": host + "/wearer_end/reason_kbn",
			"WN0012": host + "/section_wearer_end",
			"WN0013": host + "/job_type_wearer_end",
			"WN0014": host + "/wearer_end_order_info",
			"WN0015": host + "/wearer_end_order_list",
			"WN0016": host + "/wearer_end/order_check",
			"WN0017": host + "/wearer_end_order_insert",
			"WN0018": host + "/wearer_end_order_delete",
			"WC0010": host + "/wearer_change/search",
			"WC0011": host + "/wearer_change/req_param",
			"WC0012": host + "/agreement_no_change",
			"WC0013": host + "/reason_kbn_change",
			"WC0014": host + "/section_change",
			"WC0015": host + "/sex_kbn_change",
			"WC0016": host + "/job_type_change",
			"WC0017": host + "/shipment_change",
			"WC0018": host + "/wearer_change/info",
			"WC0019": host + "/wearer_change/list",
			"WC0020": host + "/wearer_change/delete",
			"WC0021": host + "/wearer_change/complete",
			"WC0022": host + "/wearer_change/send",
			"WC0023": host + "/wearer_change/order_check",
			"WC0024": host + "/snd_kbn_change",
			"WR0010": host + "/wearer_other/search",
			"WR0011": host + "/wearer_other/req_param",
			"WR0012": host + "/wearer_add/order_check",
			"WR0013": host + "/wearer_return/order_check",
			"WR0014": host + "/wearer_add/info",
			"WR0015": host + "/wearer_add/list",
			"WR0016": host + "/wearer_add/delete",
			"WR0017": host + "/wearer_add/complete",
			"WR0018": host + "/wearer_add/send",
			"WR0019": host + "/wearer_return/info",
			"WR0020": host + "/wearer_return/list",
			"WR0021": host + "/wearer_return/delete",
			"WR0022": host + "/wearer_return/complete",
			"WR0023": host + "/wearer_return/send",
			"WS0010": host + "/wearer_search/search",
			"WS0011": host + "/wearer_search/req_param",
			"WO0010": host + "/wearer_order_info",
			"WO0011": host + '/reason_kbn_order',
			"WO0012": host + '/section_order',
			"WO0013": host + '/wearer_order_list',
			"WO0014": host + '/wearer_order_insert',
			"WO0015": host + '/wearer_order_delete',
			"WO0016": host + '/job_type_order',
			"WSC0010": host + "/wearer_size_change/search",
			"WSC0011": host + "/wearer_size_change/req_param",
			"WSC0012": host + "/wearer_size_change/order_check",
			"WSC0013": host + "/wearer_other_change/order_check",
			"WX0010": host + "/wearer_exchange/info",
			"WX0011": host + "/wearer_exchange/list",
			"WX0012": host + "/wearer_exchange/delete",
			"WX0013": host + "/wearer_exchange/complete",
			"WX0014": host + "/wearer_exchange/send",
			"WX0015": host + "/wearer_exchange/add_size",
			"WOC0010": host + "/wearer_other_change_info",
			"WOC0020": host + "/wearer_other_change_list",
			"WOC0030": host + "/wearer_other_change_delete",
			"WOC0050": host + "/wearer_other_change_insert",

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

		/**
		 * メッセージ定義
		 *
		 *
		 */
		App.delete_msg = '削除しますが、よろしいですか？';
		App.input_msg = '入力を完了しますが、よろしいですか？';
		App.complete_msg = '発注送信を行いますが、よろしいですか？';
		App.input_insert_msg = '入力を保存しますが、よろしいですか？';
		App.cancel_msg = '入力がある場合、入力された情報が破棄されますが、よろしいですか？';
		App.wearer_delete_msg = '着用者入力を削除しますが、よろしいですか？';
		App.dl_msg = 'データ量により、ダウンロード処理に時間がかかる可能性があります。ダウンロードを実施してよろしいですか？';

		App.order_send_check_msg = '発注送信を行う場合は選択欄の何れかにチェックを入れてください。';
		App.order_send_confirm_msg = '選択されているデータの発注送信を行います。よろしいですか？';
		App.account_edit_msg = 'アカウントを編集しました。';
		App.account_del_msg = 'アカウントを削除しました。';
		App.account_lock_msg = 'アカウントロックを解除しました。';
		App.account_add_msg = 'アカウントを登録しました。';






		return App;

	}
);
