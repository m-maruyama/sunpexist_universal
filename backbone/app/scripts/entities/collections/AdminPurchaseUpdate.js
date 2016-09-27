define([
	"app",
	"entities/models/AdminPurchaseUpdate"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminPurchaseUpdate = Backbone.Collection.extend({
			model: App.Entities.Models.AdminPurchaseUpdate,
			url: App.api.PU0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				//console.log(res);

				return res.list;
			}
		});
	});
});
