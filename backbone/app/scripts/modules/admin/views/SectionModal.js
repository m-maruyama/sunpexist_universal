define([
	'app',
	'../Templates',
	'blockUI',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SectionModal = Marionette.LayoutView.extend({
			template: App.Admin.Templates.sectionModal,
			ui: {
				'modal': '#section_modal',
			},
			regions: {
				"page": ".page",
				"condition": ".condition",
				"listTable": ".listTable",
			},
			model: new Backbone.Model(),
			onShow: function() {
			},
			events: {
			},
			templateHelpers: {
			}
		});
	});
});
