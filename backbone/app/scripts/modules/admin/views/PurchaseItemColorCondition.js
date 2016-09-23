define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ItemColorCondition = Marionette.ItemView.extend({
			defaults: {
				agreement_no: '',
				job_type: '',
				input_item: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.itemColorCondition,
			model: new Backbone.Model(),
			ui: {
				'item_color': '.item_color'
			},
			bindings: {
				'.item_color': 'item_color',
			},
			onShow: function() {
				var agreement_no = this.options.agreement_no;
				var job_type = this.options.job_type;
				var input_item = this.options.input_item;
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0080;
				var cond = {
					"scr": '色',
					"agreement_no": agreement_no,
					"job_type": job_type,
					"input_item": input_item,
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
			}
		});
	});
});
