define([
	"app",
	"entities/models/AdminWearerReturnListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerReturnListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminWearerReturnListItem,
			url: App.api.WE0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
