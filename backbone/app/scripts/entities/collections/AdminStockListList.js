define([
	"app",
	"entities/models/AdminStockListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminStockListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminStockListItem,
			url: App.api.ST0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
