define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.IndividualNumberCondition = Marionette.ItemView.extend({
			template: App.Admin.Templates.individualNumberCondition,
			model: new Backbone.Model(),
			ui: {
				'individual_number': '.individual_number'
			},
			bindings: {
				'.individual_number': 'individual_number',
			},
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0001;
				var cond = {
					"scr": '個体管理番号'
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
						if (accout.individual_flg.valueOf() == '0') {
							$('.individual_number').hide();
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
