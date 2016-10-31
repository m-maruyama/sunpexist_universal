define([
	"app",
	"entities/models/AdminPrintListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminPrintListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminPrintListItem,
			url: App.api.PR0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
