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
	'../controllers/WearerEndOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerEndOrderSendComplete = Marionette.LayoutView.extend({
			defaults: {
				data: "",
			},
			initialize: function(options) {
				this.options = options || {};
				this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerEndOrderSendComplete,
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
				if (window.sessionStorage.getItem("referrer")=='wearer_end_order_complete') {
					location.href = './wearer_end.html';
				}else{
					window.sessionStorage.setItem("referrer","wearer_end_order_complete");
				}
				var that = this;
				var data = this.options.data;
				var scr = data["scr"];
				var mode = "update";
				var wearer_data = data["wearer_data"];
				var item = data["item"];

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WN0017;
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

						if (res_val["error_code"] == "0") {
							$('.returnSlipDownload').css('display', '');
							$('#return_slip_dl').val(res_val["param"]);
						} else if (res_val["error_code"] == "1") {
							$("#h").text('');
							$(".explanation").text('');
							that.triggerMethod('showAlerts', res_val["error_msg"]);
						} else {
							alert("予期せぬエラーが発生しました。")
						}
					}
				});
			},
			events: {
				'click @ui.continueInput': function(){
					var cond = window.sessionStorage.getItem("wearer_end_cond");
					window.sessionStorage.setItem("back_wearer_end_cond", cond);
					location.href="wearer_end.html";
				},
				'click @ui.backHome': function(){
					location.href="home.html";
				},
				'click @ui.returnSlipDownload': function (e) {
					e.preventDefault();
					var pdf_vals = e.target.value;

					var pdf_val = pdf_vals.split(':');
					var printData = new Object();
					printData["rntl_cont_no"] = pdf_val[0];
					printData["order_req_no"] = pdf_val[1];

					// JavaScript モーダルで表示
					$('#myModal').modal(); //追加
					//メッセージの修正
					document.getElementById("confirm_txt").innerHTML=App.dl_msg; //追加　このメッセージはapp.jsで定義
					$("#btn_ok").off();
					$("#btn_ok").on('click',function() { //追加
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
						$('#myModal').modal('hide'); //追加
					});
				}
			}
		});
	});
});
