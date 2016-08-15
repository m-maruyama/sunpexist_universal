define([
	'app',
	'../Templates',
	'./InfoListItem',
	"entities/models/InfoAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InfoListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.infoListItem,
			tagName: "tr",
			ui: {
				"lockBtn": ".lock",
				"editBtn": ".edit",
				"deleteBtn": ".delete"
			},
			onRender: function() {
			},
			events: {
				'click @ui.lockBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model, '3');
				},
				'click @ui.editBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model, '1');
				},
				'click @ui.deleteBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model, '2');
				},
			},
			templateHelpers: {
			}

		});
	});
});