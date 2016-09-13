define([
	"app",
	"entities/models/AdminManpowerInfoListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminManpowerInfoListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminManpowerInfoListItem,
			url: App.api.MI0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
