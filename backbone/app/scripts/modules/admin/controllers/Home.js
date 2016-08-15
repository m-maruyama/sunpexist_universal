define([
	'app',
	'./Abstract',
	'../views/Home',
	"entities/collections/AdminHome",
	"entities/models/AdminHome",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Home = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				this.setNav('home');
				var homeCollection = new App.Entities.Collections.AdminHome();
				var homeView = new App.Admin.Views.Home({
					collection: homeCollection
				});
				App.main.show(homeView);
			}
		});
	});
	return App.Admin.Controllers.Home;
});
