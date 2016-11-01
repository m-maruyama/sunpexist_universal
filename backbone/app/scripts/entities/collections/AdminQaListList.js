define([
	"app",
	"entities/models/AdminQaListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminQaListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminQaListItem,
			url: App.api.QA0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
