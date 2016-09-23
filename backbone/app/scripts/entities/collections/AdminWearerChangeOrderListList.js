define([
	"app",
	"entities/models/AdminWearerChangeListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerChangeListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminWearerChangeListItem,
			url: App.api.WE0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
