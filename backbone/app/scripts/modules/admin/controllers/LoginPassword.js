
define([
	'app',
	"entities/models/AdminLoginPassword",
	'../views/LoginPassword'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.LoginPassword = Marionette.Controller.extend({
			initialize: function() {
			},
			top: function(){
				var that = this;
				//
				// this.setNav('password');

				var loginPasswordView = new App.Admin.Views.LoginPassword({model: new App.Entities.Models.AdminLoginPassword()});
				loginPasswordView.listenTo(loginPasswordView, 'success', function(){
					location.href = './login.html';
					return;
				});
				App.main.show(loginPasswordView);
			}
		});
	});
	return App.Admin.Controllers.LoginPassword;
});
