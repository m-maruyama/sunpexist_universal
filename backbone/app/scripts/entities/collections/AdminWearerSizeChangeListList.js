define([
	"app",
	"entities/models/AdminWearerSizeChangeListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerSizeChangeListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminWearerSizeChangeListItem,
			url: App.api.WSC0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
