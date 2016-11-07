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
				"sectionSelect": ".select_section"
			},
			onRender: function() {
			},
			events: {
				'click @ui.sectionSelect': function(e){
					e.preventDefault();
					this.triggerMethod('click:section_select', this.model);
					$('#modal_page').css('display', 'none');
					$('#modal_listTable').css('display', 'none');
				}
			},
			templateHelpers: {
			}

		});
	});
});
