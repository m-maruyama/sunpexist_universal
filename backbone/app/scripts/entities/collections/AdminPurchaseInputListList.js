define([
	"app",
	"entities/models/AdminPurchaseInputListCondition"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminPurchaseInputListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminPurchaseInputListCondition,
			url: App.api.PI0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
