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
	'../controllers/WearerOtherChangeOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerOtherChangeOrderListList = Marionette.LayoutView.extend({
			defaults: {
				data: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerOtherChangeOrderList,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'order_count': '#order_count',
				'return_count': '#return_count',
				'target_flg': '#target_flg',
				'order_num': '.order_num',
			},
			bindings: {
				'order_count': '#order_count',
				'return_count': '#return_count',
				'target_flg': '#target_flg',
				'order_num': '.order_num',
			},
			onShow: function() {
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				var that = this;
				var data = this.options.data;

				// 発注商品一覧、
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WOC0020;
				var cond = {
					"scr": 'その他交換-発注商品一覧',
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
						that.render(res_list);
						if (res_list["individual_flg"] == true) {
							$('.individual_flg').css('display', '');
						}else{
							$('.no_individual_flg').css('display', '');

						}
						$.unblockUI();
					}
				});
			},
			events: {
				'change @ui.target_flg': function(e){
					var order_num = 0;
					order_num = parseInt($('#order_num'+e.target.value).val());
					if(e.target.checked){
						order_num += 1;
					}else{
						order_num -= 1;
					}
					$('#order_num'+e.target.value).val(order_num);
					$('#return_num'+e.target.value).val(order_num);


					var list_cnt = $('#list_cnt').val();
					var sum_order_num = 0;
					var sum_return_num = 0;
					for (var i=0; i<list_cnt; i++) {
						sum_order_num += parseInt($('#order_num'+i).val());
						sum_return_num += parseInt($('#return_num'+i).val());
					}
					$('#order_count').val(sum_order_num);
					$('#return_count').val(sum_return_num);
				},
				'change @ui.order_num': function(e){
					var order_num = 0;
					order_num = parseInt(e.target.value);
					var order_id = e.target.id;
					var return_num = order_id.replace( /order_num/g , "return_num" ) ;
					$('#'+return_num).val(order_num);

					var list_cnt = $('#list_cnt').val();
					var sum_order_num = 0;
					var sum_return_num = 0;
					for (var i=0; i<list_cnt; i++) {
						sum_order_num += parseInt($('#order_num'+i).val());
						sum_return_num += parseInt($('#return_num'+i).val());
					}
					$('#order_count').val(sum_order_num);
					$('#return_count').val(sum_return_num);
				},
			},
		});
	});
});
