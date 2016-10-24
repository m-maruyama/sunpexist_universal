define([
	"app",
	"entities/models/AdminInquiryDetailListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminInquiryDetailListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminInquiryDetailListItem,
			url: App.api.CU0020,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
