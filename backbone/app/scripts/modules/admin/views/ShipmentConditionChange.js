define([
	'app',
	'../Templates',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ShipmentConditionChange = Marionette.LayoutView.extend({
			defaults: {
				agreement_no: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.shipmentConditionChange,
			model: new Backbone.Model(),
			ui: {
				'shipment': '#shipment',
			},
			bindings: {
				'.shipment': 'shipment',
			},
			onShow: function() {
//				var agreement_no = this.options.agreement_no;
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0017;
				var cond = {
					"scr": '出荷先',
//					"agreement_no": agreement_no,
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
						var res_list = res.attributes;
						//console.log(res_list);
						that.render(res_list);
					}
				});
			},
		});
	});
});
