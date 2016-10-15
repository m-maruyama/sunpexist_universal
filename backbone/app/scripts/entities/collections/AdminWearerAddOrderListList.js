define([
	"app",
	"entities/models/AdminWearerAddListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerAddListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminWearerAddListItem,
			url: App.api.WE0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
