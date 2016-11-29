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
	'../controllers/WearerOtherChangeOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerOtherChangeOrderComplete = Marionette.LayoutView.extend({
			defaults: {
				data: "",
			},
			initialize: function(options) {
				this.options = options || {};
				this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerOtherChangeOrderComplete,
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
				var snd_kbn = data["snd_kbn"];

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WOC0050;
				var cond = {
					"scr": scr,
					"mode": mode,
					"wearer_data": wearer_data,
					"snd_kbn": snd_kbn,
					"item": item
				};

				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var res_val = res.attributes;
						if (res_val["error_code"] == "1") {
							// 登録処理にエラーがある場合
							$("#h").text('');
							$(".explanation").text('');
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
					var cond = window.sessionStorage.getItem("wearer_size_change_cond");
					window.sessionStorage.setItem("back_wearer_size_change_cond", cond);
					// 検索画面へ遷移
					location.href="wearer_size_change.html";
				},
				// 「ホーム画面へ戻る」ボタン
				'click @ui.backHome': function(){
					// ホーム画面へ遷移
					location.href="home.html";
				},
				// 「返却伝票ダウンロード」ボタン
				'click @ui.returnSlipDownload': function(e){
					e.preventDefault();
					var pdf_vals = e.target.value;

					var pdf_val = pdf_vals.split(':');
					var printData = new Object();
					printData["rntl_cont_no"] = pdf_val[0];
					printData["order_req_no"] = pdf_val[1];

					var msg = "データ量により、ダウンロード処理に時間がかかる可能性があります。ダウンロードを実施してよろしいですか？";
					if (window.confirm(msg)) {
						var cond = {
							"scr": 'PDFダウンロード',
							"cond": printData
						};
						var form = $('<form action="' + App.api.PR0012 + '" method="post"></form>');
						var data = $('<input type="hidden" name="data" />');
						data.val(JSON.stringify(cond));
						form.append(data);
						$('body').append(form);
						form.submit();
						data.remove();
						form.remove();
						form = null;
						return;
					}
				}
			}
		});
	});
});
