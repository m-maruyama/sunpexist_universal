define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SndKbnCondition = Marionette.ItemView.extend({
			defaults: {
				data: "",
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.sndKbnCondition,
			model: new Backbone.Model(),
			ui: {
				'snd_kbn': '#snd_kbn'
			},
			bindings: {
				'#snd_kbn': 'snd_kbn',
			},
			onShow: function() {
				var that = this;
				var data = this.options.data;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0150;
				var cond = {
					"scr": '状態',
					"data": data
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
