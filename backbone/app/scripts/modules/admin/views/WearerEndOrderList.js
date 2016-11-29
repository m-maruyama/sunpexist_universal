define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'../behaviors/Alerts',
	'bootstrap',
	'typeahead',
	'bloodhound',
	'blockUI',
	'../controllers/WearerEndOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerEndOrderList = Marionette.LayoutView.extend({
			defaults: {
				job_type: '',
			},
			initialize: function(options) {
				this.options = options || {};
				this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerEndOrderList,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'order_count': '#order_count',
				'return_count': '#return_count',
				'target_flg': '#target_flg',
				'delete': '.delete',
			},
			bindings: {
				'#order_count': 'order_count',
				'#return_count': 'return_count',
				'#target_flg': 'target_flg',
			},
			onShow: function(job_type) {
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				var that = this;
				if($('#job_type').val()){
					var job_types = $('#job_type').val().split(':');
					job_type = job_types[0];
				}
				var data = {
					'job_type':job_type
				}
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WN0015;
				var cond = {
					"scr": '返却商品一覧',
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

						if (res_list['tran_flg']) {
							$(".delete").hide();
						}
						that.render(res_list);
						if (res_list["individual_flg"] == '1') {
							$('.individual_flg').css('display','');
						}
						$.unblockUI();
					}
				});
			},
			events: {
			},
		});
	});
});
