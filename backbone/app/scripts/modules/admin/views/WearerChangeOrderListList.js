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
									$("#now_return_num" + i).val(possible_num);
									return_count_num = parseInt(return_count_num) + parseInt(possible_num);

								} else {
									$("#now_return_num" + i).val('0');
								}
							}
							//返却枚数合計
							$("input[name='now_return_count']").val(return_count_num);

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
