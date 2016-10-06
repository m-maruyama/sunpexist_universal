define([
	"app",
	"entities/models/AdminWearerEditListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerEditListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminWearerEditListItem,
			url: App.api.WU0020,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
