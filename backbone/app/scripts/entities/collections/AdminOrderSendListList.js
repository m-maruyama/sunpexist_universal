define([
	"app",
	"entities/models/AdminOrderSendListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminOrderSendListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminOrderSendListItem,
			url: App.api.OS0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
