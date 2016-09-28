define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminLoginPassword = Backbone.Model.extend({
			url: App.api.LP0010,
			initialize: function() {
				_.extend(this, Backbone.Validation.mixin);
			},
			defaults: {
				corporate_id: null,
				login_id: null,
				password: null,
				password_c: null
			},
			getReq: function(){
				return {
					corporate_id: this.get('corporate_id'),
					login_id: this.get('login_id'),
					password: this.get('password'),
					password_c: this.get('password_c')
				};
			},
			validation:  {
				"corporate_id": [
					{
						required:true,
						msg: "企業IDを入力して下さい。"
					}
				],
				"login_id": [
					{
						required:true,
						msg: "ログインIDを入力してください。"
					}
				]
			}
		});
	});
});
