define([
	'app',
	'../Templates',
	'./LendListItem',
	"entities/models/LendAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.LendListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.lendListItem,
			tagName: "tr",
			ui: {
				"detailLink": "a.detail"
			},
			onRender: function() {
			},
			events: {
				'click @ui.detailLink': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model);
				}
			},
			templateHelpers: {
			}

		});
	});
});