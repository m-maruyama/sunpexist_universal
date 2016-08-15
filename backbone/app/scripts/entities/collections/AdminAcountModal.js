define([
	"app",
	"entities/models/AdminAcountModal"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminAcountModal = Backbone.Collection.extend({
			model: App.Entities.Models.AdminAcountModal,
			url: App.api.AC0020,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
