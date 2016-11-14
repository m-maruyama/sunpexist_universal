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
	'../controllers/WearerExchangeOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerExchangeOrderListList = Marionette.LayoutView.extend({
			defaults: {
				data: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerExchangeOrderList,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'order_count': '#order_count',
				'return_count': '#return_count',
				'target_flg': '#target_flg',
			},
			bindings: {
				'#order_count': 'order_count',
				'#return_count': 'return_count',
				'#target_flg': 'target_flg',
			},
			onShow: function() {
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				var that = this;
				var data = this.options.data;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WX0011;
				var cond = {
					"scr": 'サイズ交換-発注商品一覧',
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
						if (res_list["individual_flg"] == '1') {
							$('.individual_flg').css('display','');
						} else {
							$('.exchange_possible_num').css('display','');
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
