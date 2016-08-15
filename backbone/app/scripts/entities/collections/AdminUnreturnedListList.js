define([
	"app",
	"entities/models/AdminUnreturnedListItem",
	"lib/ecl"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminUnreturnedListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminUnreturnedListItem,
			url: App.api.UD0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
