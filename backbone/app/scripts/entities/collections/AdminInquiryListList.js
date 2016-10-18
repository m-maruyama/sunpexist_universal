define([
	"app",
	"entities/models/AdminInquiryListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminInquiryListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminInquiryListItem,
			url: App.api.CU0020,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
