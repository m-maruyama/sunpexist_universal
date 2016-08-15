define([
	"app",
	"entities/models/AdminUnreturnListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminUnreturnListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminUnreturnListItem,
			url: App.api.UN0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
