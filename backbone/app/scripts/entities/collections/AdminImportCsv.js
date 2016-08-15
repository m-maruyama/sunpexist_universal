define([
	"app",
	"entities/models/AdminImportCsv"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminImportCsv = Backbone.Collection.extend({
			model: App.Entities.Models.AdminImportCsv,
			url: App.api.IM0010,
			parse:function(res, xhr){
				return res;
			}
		});
	});
});
