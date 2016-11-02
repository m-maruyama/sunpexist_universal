define([
	"app",
	"entities/models/AdminWearerOtherChangeListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerOtherChangeListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminWearerOtherChangeListItem,
			url: App.api.WE0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
