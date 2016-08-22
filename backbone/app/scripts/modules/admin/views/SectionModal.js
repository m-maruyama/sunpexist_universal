define([
	'app',
	'../Templates',
	'blockUI',
	'bootstrap',
	'bootstrap-datetimepicker'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SectionModal = Marionette.ItemView.extend({
			template: App.Admin.Templates.sectionModal,
			emptyView: Backbone.Marionette.ItemView.extend({
                tagName: "tr",
				template: App.Admin.Templates.sectionEmpty,
            }),
			ui: {
				'modal': '#section_modal',
				'search': '.search',
				'rntl_sect_cd': '#rntl_sect_cd',
				'rntl_sect_name': '#rntl_sect_name'
			},
			bindings: {
			},
			onRender: function() {
			},
			events: {
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					this.model.set('rntl_sect_cd', this.ui.rntl_sect_cd.val());
					this.model.set('rntl_sect_name', this.ui.rntl_sect_name.val());
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}
					this.triggerMethod('click:search','rntl_sect_cd','asc');
				},
			},
			templateHelpers: {
			}
		});
	});
});
