define([
	"app",
	"entities/models/AdminWearerInputListCondition"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminWearerInputListList = Backbone.Collection.extend({
			// model: App.Entities.Models.AdminWearerInputListCondition,
			// url: App.api.WI0010,
			// parse:function(res, xhr){
			// 	this.trigger('parsed',res);
			// 	return res.list;
			// }
		});
	});
});
