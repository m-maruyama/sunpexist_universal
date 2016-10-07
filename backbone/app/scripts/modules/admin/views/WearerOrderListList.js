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
	'../controllers/WearerOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerOrderListList = Marionette.LayoutView.extend({
			defaults: {
				job_type: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerOrderList,
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
			onShow: function() {
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				var that = this;
				var data = {
					'job_type': this.options.job_type,
				}

				// 発注送信一覧、
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WO0013;
				var cond = {
					"scr": '発注送信一覧',
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

						// 検索画面から遷移してきた場合、かつ発注トランにデータがある場合は「発注取消」ボタンを表示
						console.log(res_list['tran_flg']);
						if (res_list['tran_flg']) {
							$(".delete").show();
						}
						//console.log(res_list);
						that.render(res_list);
						// if (res_list["individual_flg"] == '1') {
						// 	$('.individual_flg').css('display','');
						// }
						$.unblockUI();
					}
				});
			},
			events: {
			},
		});
	});
});
