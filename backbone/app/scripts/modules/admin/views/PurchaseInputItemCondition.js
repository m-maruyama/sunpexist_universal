define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.PurchaseInputItemCondition = Marionette.ItemView.extend({
			defaults: {
				agreement_no: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.purchaseInputItemCondition,
			model: new Backbone.Model(),
			ui: {
				'input_item': '.input_item'
			},
			bindings: {
				'.input_item': 'input_item',
			},
			onShow: function() {
				var agreement_no = this.options.agreement_no;
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.PH0011;

				var cond = {
					"scr": '商品',
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
			}
		});
	});
});
