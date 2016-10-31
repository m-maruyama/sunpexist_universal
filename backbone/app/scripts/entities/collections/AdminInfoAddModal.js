define([
	"app",
	"entities/models/AdminInfoAddModal"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminInfoAddModal = Backbone.Collection.extend({
			model: App.Entities.Models.AdminInfoAddModal,
			url: App.api.IN0020,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
