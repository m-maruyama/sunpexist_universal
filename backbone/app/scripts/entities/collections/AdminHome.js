define([
	"app",
	"entities/models/AdminHome"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminHome = Backbone.Collection.extend({
			model: App.Entities.Models.AdminHome,
			url: App.api.HM0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return {
					'list': res.list
				};
			}
		});
	});
});