define([
	'app',
	'../Templates',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ShipmentConditionChange = Marionette.LayoutView.extend({
			defaults: {
				section: '',
				ship_to_cd: '',
				ship_to_brnch_cd: '',
				chg_flg: '',
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
				var that = this;
				var data = {
					'section': this.options.section,
					'ship_to_cd': this.options.ship_to_cd,
					'ship_to_brnch_cd': this.options.ship_to_brnch_cd,
					'chg_flg': this.options.chg_flg,
				};

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0017;
				var cond = {
					"scr": '職種変更または異動:出荷先セレクトボックス',
					"data": data,
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
