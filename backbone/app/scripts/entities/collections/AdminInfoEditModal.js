define([
	"app",
	"entities/models/AdminInfoEditModal"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminInfoEditModal = Backbone.Collection.extend({
			model: App.Entities.Models.AdminInfoEditModal,
			url: App.api.IN0020,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
