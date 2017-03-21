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
	'../controllers/WearerChangeOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerChangeOrderListList = Marionette.LayoutView.extend({
			defaults: {
				data: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerChangeOrderList,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'order_count': '#order_count',
				'return_count': '#return_count',
				'target_flg': '.now_target_flg',
				'add_order_num': '.add_order_num',
				'now_return_num': '.now_return_num',
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

				// 現在貸与中のアイテム・新たに追加されるアイテム一覧、
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0019;
				var cond = {
					"scr": '現在貸与中のアイテム',
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

							var return_count_num = 0;
							for(var i=0;i<res_list.now_list_cnt;i++){
								if ($("input[name='now_size_cd" + i + "']").val()) {

									var possible_num = $("input[name='possible_num" + i + "']").val();
									// $("#now_return_num" + i).val(possible_num);
									// $("#now_return_num" + i).val('0');
									// return_count_num = parseInt(return_count_num) + parseInt(possible_num);

								} else {
									$("#now_return_num" + i).val('0');
								}
							}
							// if(res_list['need_return_num_flg']){
							// 	//返却枚数合計
							// 	$("input[name='now_return_count']").val('0');
							// }else{
							// 	//返却枚数合計
							// 	$("input[name='now_return_count']").val(return_count_num);
							// }


						}
						$.unblockUI();
					}
				});
			},
			events: {
				'change @ui.target_flg': function(e){
					var return_num = 0;
					var name_no = e.target.id.replace( /now_target_flg/g , "" );
					return_num = parseInt($('#now_return_num'+name_no).val());
					if(e.target.checked){
						return_num += 1;
					}else{
						return_num -= 1;
					}
					// $('#order_num'+e.target.value).val(order_num);
					$('#now_return_num'+name_no).val(return_num);

					var list_cnt = $('#now_list_cnt').val();
					// var sum_order_num = 0;
					var sum_return_num = 0;
					for (var i=0; i<list_cnt; i++) {
						var now_return_num = parseInt($('#now_return_num'+i).val());
						if(isNaN(now_return_num)){
							continue;
						}
						// sum_order_num += parseInt($('#order_num'+i).val());
						sum_return_num += now_return_num;
					}
					// $('#order_count').val(sum_order_num);
					$('#return_count').val(sum_return_num);
				},
				'change @ui.now_return_num': function(e){
					var return_num = 0;
					return_num = parseInt(e.target.value);
					if(isNaN(return_num)){
						return_num = 0;
					}
					$('#'+e.target.id).val(return_num);
					var list_cnt = $('#now_list_cnt').val();
					var sum_return_num = 0;
					// var sum_return_num = 0;
					for (var i=0; i<list_cnt; i++) {
						sum_return_num += parseInt($('#now_return_num'+i).val());
						// sum_return_num += parseInt($('#return_num'+i).val());
					}
					$('#return_count').val(sum_return_num);
					// $('#return_count').val(sum_return_num);
				},
				'change @ui.add_order_num': function(e){
					var order_num = 0;
					order_num = parseInt(e.target.value);
					if(isNaN(order_num)){
						order_num = 0;
					}
					$('#'+e.target.id).val(order_num);
					var list_cnt = $('#add_list_cnt').val();
					var sum_order_num = 0;
					// var sum_return_num = 0;
					for (var i=0; i<list_cnt; i++) {
						sum_order_num += parseInt($('#add_order_num'+i).val());
					}
					$('#order_count').val(sum_order_num);
				},
			},
		});
	});
});
