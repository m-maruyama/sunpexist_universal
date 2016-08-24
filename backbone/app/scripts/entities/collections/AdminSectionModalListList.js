define([
	"app",
	"entities/models/AdminSectionModalListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminSectionModalListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminSectionModalListItem,
			url: App.api.CM0090,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
