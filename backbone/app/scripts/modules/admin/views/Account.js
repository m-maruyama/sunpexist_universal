define([
	'app',
	'../Templates',
	'../behaviors/Alerts'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Account = Marionette.LayoutView.extend({
			template: App.Admin.Templates.account,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'modal': '#myModal',
				'addBtn': '.add',
				'message': '#message',
				'updateBtn': 'button.update'
			},
			regions: {
				"page": ".page",
				"page_2": ".page_2",
				"condition": ".condition",
				"accountModal": '.account_modal',
				"listTable": '.listTable'
			},
			bindings: {
			},
			onRender: function() {
				var that = this;
			},
			events: {
				'click @ui.addBtn': function(e){
					e.preventDefault();
					$("#password-group").removeClass("hidden");
					$(".accnt_no_group").addClass("hidden");
				},
			},
		});
	});

});
