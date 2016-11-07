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
				'close': '.close'
			},
			regions: {
				"page": ".page",
				"condition": ".condition",
				"listTable": ".listTable",
			},
			model: new Backbone.Model(),
			onRender: function() {
			},
			events: {
				'click @ui.close': function(){
					//$('#modal_page').css('display', 'none');
					//$('#modal_listTable').css('display', 'none');
				},
			},
			templateHelpers: {
			}
		});
	});
});
