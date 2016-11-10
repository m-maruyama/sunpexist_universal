
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

				var passwordView = new App.Admin.Views.Password;//({model: new App.Entities.Models.AdminPassword()});
				passwordView.listenTo(passwordView, 'success', function(){
					location.href = './login.html';
					return;
				});
				App.main.show(passwordView);
			}
		});
	});
	return App.Admin.Controllers.Password;
});
