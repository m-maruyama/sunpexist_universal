define([
	"app",
	"entities/models/AdminInquiryInputListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminInquiryInputListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminInquiryInputListItem,
			url: App.api.CU0020,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
