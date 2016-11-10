define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminPassword = Backbone.Model.extend({
			url: App.api.PA0010,
			initialize: function() {
				_.extend(this, Backbone.Validation.mixin);
			},
			defaults: {
				user_id: null,
				password: null,
				password_c: null
			},
			getReq: function(){
				return {
					user_id: this.get('user_id'),
					password: this.get('password'),
					password_c: this.get('password_c')
				};
			},
			validation:  {
				"password": [
					{
						required:true,
						msg: "パスワードを入力して下さい。"
					}
				],
				"password_c": [
					{
						required:true,
						msg: "確認用のパスワードを入力していません。"
					},
					{
						equalTo: 'password',
						msg: "確認用のパスワードがパスワードと一致していません。"
					}
				]
			}
		});
	});
});
