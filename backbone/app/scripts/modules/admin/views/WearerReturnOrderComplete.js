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
		Views.WearerReturnOrderComplete = Marionette.LayoutView.extend({
			defaults: {
				data: "",
			},
			initialize: function(options) {
				this.options = options || {};
				this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerReturnOrderComplete,
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
				if (window.sessionStorage.getItem("referrer")=='wearer_return_order_complete') {
					location.href = './wearer_other.html';
				}else{
					window.sessionStorage.setItem("referrer","wearer_return_order_complete");
				}
				var that = this;
				var data = this.options.data;
				var scr = data["scr"];
				var mode = data["mode"];
				var wearer_data = data["wearer_data"];
				var item = data["item"];

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WR0022;
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
							$("#h").text('');
							$(".explanation").text('');
							that.triggerMethod('showAlerts', res_val["error_msg"]);
						} else {
							$('.returnSlipDownload').css('display', '');
							$('#return_slip_dl').val(res_val["param"]);
						}
					}
				});
			},
			events: {
				'click @ui.continueInput': function(){
					var cond = window.sessionStorage.getItem("wearer_other_cond");
					window.sessionStorage.setItem("back_wearer_other_cond", cond);
					location.href="wearer_other.html";
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
