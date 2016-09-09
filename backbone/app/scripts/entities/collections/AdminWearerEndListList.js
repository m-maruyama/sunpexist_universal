define([
	"app",
	"entities/models/AdminWearerEndListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerEndListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminWearerEndListItem,
			url: App.api.HI0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
