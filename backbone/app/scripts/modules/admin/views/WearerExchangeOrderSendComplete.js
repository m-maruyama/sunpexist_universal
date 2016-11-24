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
	'../controllers/WearerExchangeOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerExchangeOrderSendComplete = Marionette.LayoutView.extend({
			defaults: {
				data: "",
			},
			initialize: function(options) {
				this.options = options || {};
				this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerExchangeOrderSendComplete,
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

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WX0014;
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
						} else {
							$("#h").text('');
							$(".explanation").text('');
							that.triggerMethod('showAlerts', res_val["error_msg"]);
						}
					}
				});
			},
			events: {
				'click @ui.continueInput': function(){
					var cond = window.sessionStorage.getItem("wearer_size_change_cond");
					window.sessionStorage.setItem("back_wearer_size_change_cond", cond);
					location.href="wearer_size_change.html";
				},
				'click @ui.backHome': function(){
					location.href="home.html";
				},
				'click @ui.returnSlipDownload': function(){
					alert("てっちゃん、機能の実装よろしくお願いします。");
				},
			}
		});
	});
});
