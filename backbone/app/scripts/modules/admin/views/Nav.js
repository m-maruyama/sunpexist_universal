define([
	'app',
	'../Templates',
	'cookie'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Nav = Marionette.ItemView.extend({
			template: App.Admin.Templates.nav,
			ui: {
				"home": "li.nav_home",
				"login": "li.nav_login",
				"importCsv": "li.nav_importCsv",
				"history": "li.nav_history",
				"delivery": "li.nav_delivery",
				"unreturn": "li.nav_unreturn",
				"unreturned": "li.nav_unreturned",
				"stock": "li.nav_stock",
				"lend": "li.nav_lend",
				"receive": "li.nav_receive",
				"manpowerInfo": "li.nav_manpowerInfo",
				"wearer": "li.nav_wearer",
				"account": "li.nav_account",
				"orderSend": "li.nav_orderSend",
				"info": "li.nav_info",
				"purchaseInput": "li.nav_purchaseInput",
				"wearerEdit": "li.nav_wearerEdit",
				"wearerEditOrder": "li.nav_wearerEditOrder",
				"wearerInput": "li.nav_wearerInput",
				"wearerEnd": "li.nav_wearerEnd",
				"wearerEndOrder": "li.nav_wearerEndOrder",
				"wearerChange": "li.nav_wearerChange",
				"wearerChangeOrder": "li.nav_wearerChangeOrder",
				"wearerOther": "li.nav_wearerOther",
				"wearerInputComplete": "li.nav_wearerInputComplete",
				"wearerSearch": "li.nav_wearerSearch",
				"wearerOrder": "li.nav_wearerOrder",
				"logout": "li.logout a"
			},
			onShow: function() {
				var that = this;
				var cond = {
					"scr": 'グローバルメニュー',
					"log_type": '2'
				};
				var modelForUpdate = this.model;
					modelForUpdate.url = App.api.GL0010;
					modelForUpdate.fetchMx({
					 data: cond,
					 success:function(res){
						 var errors = res.get('errors');
						 if(errors) {
							 var errorMessages = errors.map(function(v){
								 return v.error_message;
							 });
							 that.triggerMethod('showAlerts', errorMessages);
						 }
						 that.render();
					 }
					});
			},
			onRender: function(){
				if(this.options.active || typeof this.ui[this.options.active] !== 'undefined') {
					this.ui[this.options.active].addClass('active');
				}
			},
			model: new Backbone.Model(),
			events: {
				"click @ui.logout": function(e){
					e.preventDefault();
					App.container.logout = true;//これがtrueだったら403で飛ばないようにしてある
					var that = this;
					var logout = this.model;
					logout.url = App.api.OU0010;
					logout.fetchMx({
						success:function(res){
							var errors = res.get('errors');
							if(errors) {
								var errorMessages = errors.map(function(v){
									return v.error_message;
								});
								that.triggerMethod('showAlerts', errorMessages);
								return;
							}
						}
					});
					location.href = './login.html';
				}
			}

		});
	});
});
