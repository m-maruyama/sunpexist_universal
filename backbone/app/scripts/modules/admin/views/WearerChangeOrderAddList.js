define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/WearerChangeOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerChangeOrderAddList = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerChangeOrderAddList,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				'agreement_no': '.agreement_no',
				'reason_kbn': '.reason_kbn',
				'sex_kbn': '.sex_kbn',
				"section": ".section",
				"job_type": ".job_type",
				"shipment": ".shipment",
				"wearer_info": ".wearer_info",
			},
			ui: {
				'agreement_no': '#agreement_no',
				'reason_kbn': '#reason_kbn',
				'sex_kbn': '#sex_kbn',
				'member_no': '#member_no',
				'member_name': '#member_name',
				'member_name_kana': '#member_name_kana',
				'appointment_ymd': '#appointment_ymd',
				'section': '#section',
				'job_type': '#job_type',
				'shipment': '#shipment',
				'post_number': '#post_number',
				'address': '#address',
				"reset": '.reset',
				"search": '.search',
				'datepicker': '.datepicker',
				'timepicker': '.timepicker',
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#reason_kbn': 'reason_kbn',
				'#sex_kbn': 'sex_kbn',
				'#member_no': 'member_no',
				'#member_name': 'member_name',
				'#member_name_kana': 'member_name_kana',
				'#appointment_ymd': 'appointment_ymd',
				'#section': 'section',
				'#job_type': 'job_type',
				'#shipment': 'shipment',
				'#post_number': 'post_number',
				'#address': 'address',
				"#reset": 'reset',
				'#search': 'search',
				'#datepicker': 'datepicker',
				'#timepicker': 'timepicker',
			},
			onShow: function() {
				var that = this;

				// 現在貸与中のアイテム
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0021;
				var cond = {
					"scr": '現在貸与中のアイテム',
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
						//console.log(res_list['wearer_info']);
					}
				});
			},
			events: {
			},
		});
	});
});
