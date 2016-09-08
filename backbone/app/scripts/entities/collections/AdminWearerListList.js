define([
	"app",
	"entities/models/AdminWearerListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminWearerListItem,
			url: App.api.HI0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
