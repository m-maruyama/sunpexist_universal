define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'../behaviors/Alerts',
	'bootstrap',
	'typeahead',
	'bloodhound',
	'blockUI',
	'../controllers/WearerReturnOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerReturnOrderSendComplete = Marionette.LayoutView.extend({
			defaults: {
				data: "",
			},
			initialize: function(options) {
				this.options = options || {};
				this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerReturnOrderSendComplete,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'continueInput': '.continueInput',
				'backHome': '.backHome',
				'returnSlipDownload': '.returnSlipDownload',
			},
			bindings: {
			},
			onShow: function() {
				var that = this;
				var data = this.options.data;
				var scr = data["scr"];
				var mode = data["mode"];
				var wearer_data = data["wearer_data"];
				var item = data["item"];

				// 入力内容登録処理
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WR0023;
				var cond = {
					"scr": scr,
					"mode": mode,
					"wearer_data": wearer_data,
					"item": item
				};
				//console.log(cond);

				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var res_val = res.attributes;
						if (res_val["error_code"] == "1") {
							// 登録処理にエラーがある場合
							that.triggerMethod('showAlerts', res_val["error_msg"]);
						} else {
							// 正常完了の場合、「返却伝票印刷」ボタンを表示
							$('.returnSlipDownload').css('display', '');
						}
					}
				});
			},
			events: {
				// 「続けて入力する」ボタン
				'click @ui.continueInput': function(){
					// 検索画面の条件項目を取得
					var cond = window.sessionStorage.getItem("wearer_other_cond");
					window.sessionStorage.setItem("back_wearer_other_cond", cond);
					// 検索画面へ遷移
					location.href="wearer_other.html";
				},
				// 「ホーム画面へ戻る」ボタン
				'click @ui.backHome': function(){
					// ホーム画面へ遷移
					location.href="home.html";
				}
			}
		});
	});
});
