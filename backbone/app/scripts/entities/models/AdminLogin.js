define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminLogin = Backbone.Model.extend({
			url: App.api.LO0010,
			initialize: function() {
				_.extend(this, Backbone.Validation.mixin);
			},
			validation:  {
				"login_id": [
					{
						required:true,
						msg: "ログインIDを入力して下さい。"
					}
				],
				"password": [
					{
						required:true,
						msg: "パスワードを入力して下さい。"
					}
				]
			}
		});
	});
});
