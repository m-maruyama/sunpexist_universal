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
				"editBtn": ".edit",
				"deleteBtn": ".delete"
			},
			onRender: function() {
			},
			events: {
				'click @ui.editBtn': function(e){
					e.preventDefault();
					var that = this;
					var target_id = e.target.id;
					alert(target_id)
					this.triggerMethod('click:editBtn', target_id);
				},
				'click @ui.deleteBtn': function(e){
					e.preventDefault();
					var that = this;
					var target_id = e.target.id;
					this.triggerMethod('click:deleteBtn', target_id);
				},
			},
			templateHelpers: {
			}

		});
	});
});
