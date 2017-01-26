define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ReasonKbnConditionOrder= Marionette.ItemView.extend({
			template: App.Admin.Templates.ReasonKbnConditionOrder,
			model: new Backbone.Model(),
			ui: {
				'reason_kbn': '.reason_kbn'
			},
			bindings: {
				'.reason_kbn': 'reason_kbn'
			},
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
				var path = location.pathname;
				if(path=='/universal/wearer_end_order.html'){
					modelForUpdate.url = App.api.WN0011;
				}else if(path=='/universal/wearer_order.html'){
					modelForUpdate.url = App.api.WO0011;
				}
				var cond = {
					"scr": '理由区分'
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
							if(path=='/universal/wearer_end_order.html') {
								$('.reason_require_mark').css('display', 'inline');
							}
							if(path=='/universal/wearer_order.html') {
								$('.reason_require_mark').css('display', 'inline');
							}						}
					});

			},
			events: {
			}
		});
	});
});
