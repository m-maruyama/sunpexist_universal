define([
	"app",
	"entities/models/AdminInfoModal"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminInfoModal = Backbone.Collection.extend({
			model: App.Entities.Models.AdminInfoModal,
			url: App.api.IN0020,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
