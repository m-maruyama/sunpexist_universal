define([
	"app",
	"entities/models/AdminWearerOtherListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerOtherListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminWearerOtherListItem,
			url: App.api.WC0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
