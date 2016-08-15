define([
	"app",
	"entities/models/AdminHistoryListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminHistoryListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminHistoryListItem,
			url: App.api.HI0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
