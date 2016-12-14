
define([
	'app',
	"entities/models/AdminPassword",
	'../views/Password'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Password = Marionette.Controller.extend({
			initialize: function() {
			},
			top: function(){
				var that = this;
				//
				// this.setNav('password');

				var passwordView = new App.Admin.Views.Password({
					model: new App.Entities.Models.AdminPassword()
				});
				passwordView.listenTo(passwordView, 'success', function(){

					var account_param = JSON.parse(window.sessionStorage.getItem('account_param'));
					if (account_param !== null){
						if(account_param.page_from == 'account') {
							window.sessionStorage.setItem('page_from', 'password');
							location.href = './account.html';
						}else{
							location.href = './login.html';
							return;
						}
					}else {
						location.href = './login.html';
						return;
					}
				});
				App.main.show(passwordView);
			}
		});
	});
	return App.Admin.Controllers.Password;
});
