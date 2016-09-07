define([
	"app",
	"entities/models/AdminAccountModal"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminAccountModal = Backbone.Collection.extend({
			model: App.Entities.Models.AdminAccountModal,
			url: App.api.AC0020,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
