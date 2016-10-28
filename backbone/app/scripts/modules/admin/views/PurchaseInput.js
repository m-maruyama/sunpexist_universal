define([
	'app',
	'../Templates',
    '../behaviors/Alerts',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var search_flg ='';

		Views.PurchaseInput = Marionette.LayoutView.extend({
			template: App.Admin.Templates.purchaseInput,
            behaviors: {
                "Alerts": {
                    behaviorClass: App.Admin.Behaviors.Alerts
                }
            },
			ui: {
				"agreement_no": ".agreement_no",
			},
			regions: {
				"condition": ".condition",
				"listTable": ".listTable",
			},
			binding: {
                "#input_insert": "input_insert",
			},
			model: new Backbone.Model(),
			onRender: function() {

			},
			events: {
				'change @ui.agreement_no': function(e){
					e.preventDefault();
				}
			}

		});
	});
});
