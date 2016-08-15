define([
	"app",
	"entities/models/AdminLendListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminLendListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminLendListItem,
			url: App.api.LE0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
