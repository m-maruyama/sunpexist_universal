define([
	'app',
	'../Templates',
	'./SectionModalListItem',
	"entities/models/SectionModalAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SectionModalListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.sectionModalListItem,
			tagName: "tr",
			ui: {
				"sectionSelect": "a.section_select"
			},
			onRender: function() {
			},
			events: {
				'click @ui.sectionSelect': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model);
				}
			},
			templateHelpers: {
			}

		});
	});
});