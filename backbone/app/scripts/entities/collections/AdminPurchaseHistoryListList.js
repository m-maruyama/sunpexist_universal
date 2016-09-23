define([
	"app",
	"entities/models/AdminPurchaseHistoryListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminPurchaseHistoryListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminPurchaseHistoryListItem,
			url: App.api.PH0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
