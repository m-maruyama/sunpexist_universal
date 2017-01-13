define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SexKbnConditionChangeOrder = Marionette.ItemView.extend({
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.sexKbnConditionChangeOrder,
			model: new Backbone.Model(),
			ui: {
				'sex_kbn': '#sex_kbn'
			},
			bindings: {
				'#sex_kbn': 'sex_kbn',
			},
			onShow: function() {
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0015;
				var cond = {
					"scr": '性別'
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
