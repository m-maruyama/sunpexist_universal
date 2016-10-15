define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerOrder = Marionette.LayoutView.extend({
			template: App.Admin.Templates.wearerOrder,
			ui: {
			},
			regions: {
				"condition": ".condition",
				"listTable": ".listTable",
				"sectionModal": ".section_modal",
				"sectionModal_2": ".section_modal_2",
			},
			model: new Backbone.Model(),
			onShow: function() {

			},
			events: {
			}

		});
	});
});
