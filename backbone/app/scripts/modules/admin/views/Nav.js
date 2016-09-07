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
				"account": "li.nav_account",
				"info": "li.nav_info",
				"wearerInput": "li.nav_wearerInput",
				"logout": "li.logout a"
			},
			onShow: function() {
				var that = this;
				var cond = {
					"scr": 'グローバルメニュー'
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