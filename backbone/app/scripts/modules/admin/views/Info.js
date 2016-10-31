define([
	'app',
	'../Templates',
	'../behaviors/Alerts'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Info = Marionette.LayoutView.extend({
			template: App.Admin.Templates.info,
			model: new Backbone.Model(),
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"page": ".page",
				"page_2": ".page_2",
				"listTable": ".listTable",
				"infoAddModal": '.info_add_modal',
				"infoEditModal": '.info_edit_modal',
				"infoDeleteModal": '.info_delete_modal'
			},
			ui: {
				'modal': '#myModal',
				'addBtn': '.add',
				'message': '#message'
			},
			bindings: {
			},
			onRender: function() {
				var that = this;

			},
			events: {
				'click @ui.addBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:addBtn');
				},
			}
		});
	});
});
