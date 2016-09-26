define([
	"app",
	"entities/models/AdminWearerSearchListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerSearchListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminWearerSearchListItem,
			url: App.api.WS0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
