define([
	'app',
	'../Templates',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SectionConditionChange = Marionette.LayoutView.extend({
			defaults: {
				agreement_no: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.sectionConditionChange,
			model: new Backbone.Model(),
			ui: {
				'section': '#section',
				'section_btn': '#section_btn'
			},
			bindings: {
				'.section': 'section',
				'.section_btn': 'section_btn'
			},
			onShow: function() {
				var agreement_no = this.options.agreement_no;
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0014;
				var cond = {
					"scr": '拠点',
					"agreement_no": agreement_no,
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
