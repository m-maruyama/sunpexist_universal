define([
	'app',
	'../Templates',
	'backbone.stickit',
	'blockUI',

], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SectionPurchaseCondition = Marionette.LayoutView.extend({
			defaults: {
				agreement_no: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.sectionPurchaseCondition,
			model: new Backbone.Model(),
			ui: {
				'section': '#section'
			},
			bindings: {
				'.section': 'section'
			},
			onShow: function() {
				var agreement_no = this.options.agreement_no;
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0021;

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
				//'click @ui.section_btn': function(e){
				//	e.preventDefault();
				//	this.triggerMethod('click:section_btn', this.model);
				//}
			}
		});
	});
});
