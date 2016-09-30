define([
	'app',
	"entities/models/AdminLogin",
	'../views/Login'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Login = Marionette.Controller.extend({
			initialize: function() {
			},
			top: function(){
				var that = this;
				var loginView = new App.Admin.Views.Login({model: new App.Entities.Models.AdminLogin()});
				loginView.listenTo(loginView, 'success', function(){
					location.href = './home.html';
					return;
				});
				loginView.listenTo(loginView, 'password', function(){
					var corporate_id = $("#corporate_id").val();
					var user_id = $("#login_id").val();
					window.sessionStorage.setItem('corporate_id', corporate_id);
					window.sessionStorage.setItem('user_id', user_id);
					location.href = './password.html?page=login';
					return;
				});
				App.main.show(loginView);
			}
		});
	});
	return App.Admin.Controllers.Login;
});
