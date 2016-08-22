define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SectionCondition = Marionette.ItemView.extend({
			template: App.Admin.Templates.sectionCondition,
			model: new Backbone.Model(),
			ui: {
				'section': '.section',
				'section_btn': '#section_btn'
			},
			bindings: {
				'.section': 'section',
				'.section_btn': 'section_btn'
			},
			onShow: function() {
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0020;
				var cond = {
					"scr": '拠点'
				};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var errors = res.get('errors');
							if(errors) {
								var errorMessages = errors.map(function(v){
									return v.error_message;
								});
								that.triggerMethod('showAlerts', errorMessages);
							}
							that.render();
						}
					});
			},
			events: {
				'click @ui.section_btn': function(e){
					e.preventDefault();
					this.triggerMethod('click:section_btn', this.model);
				}
			}
		});
	});
});
