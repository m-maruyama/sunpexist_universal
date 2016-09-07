define([
	"app",
	"entities/models/AdminaccountModal"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminaccountModal = Backbone.Collection.extend({
			model: App.Entities.Models.AdminaccountModal,
			url: App.api.AC0020,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
