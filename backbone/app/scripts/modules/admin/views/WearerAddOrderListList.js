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
	'../controllers/WearerAddOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerAddOrderListList = Marionette.LayoutView.extend({
			defaults: {
				data: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerAddOrderList,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'order_count': '#order_count',
				'order_num': '.order_num',
			},
			bindings: {
				'#order_count': 'order_count',
			},
			onShow: function() {
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				var that = this;
				var data = this.options.data;

				// 発注商品一覧、
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WR0015;
				var cond = {
					"scr": '追加貸与-発注商品一覧',
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
						$.unblockUI();
					}
				});
			},
			events: {
				'change @ui.order_num': function(e) {
					e.preventDefault();
					var that = this;
					var order_num = parseInt(e.target.value);
					if(isNaN(order_num)){
						order_num = 0;
					}
					$("#"+e.target.id).val(order_num);

					var order_count = parseInt(0);
					$(".order_num").each(function () {
						if ($(this).val()) {
							order_count += parseInt($(this).val());
						}
					});
					$("input[name='order_count']").val(order_count);
				},
			},
		});
	});
});
