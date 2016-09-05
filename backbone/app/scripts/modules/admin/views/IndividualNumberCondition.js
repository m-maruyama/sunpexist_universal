define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.IndividualNumberCondition = Marionette.ItemView.extend({
			defaults: {
				agreement_no: ''
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.individualNumberCondition,
			model: new Backbone.Model(),
			ui: {
				'individual_number': '.individual_number',
			},
			bindings: {
				'.individual_number': 'individual_number',
			},
			onShow: function() {
				var agreement_no = this.options.agreement_no;
				var that = this;
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0100;
				var cond = {
					"scr": '個体管理番号',
					"agreement_no": agreement_no,
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var errors = res.get('errors');
						var accout = res.attributes.valueOf();
						if(errors) {
							var errorMessages = errors.map(function(v){
								return v.error_message;
							});
							that.triggerMethod('showAlerts', errorMessages);
						}
						if (accout.individual_flg.valueOf()=='1') {
							$('.individual_number').css('display','');
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
