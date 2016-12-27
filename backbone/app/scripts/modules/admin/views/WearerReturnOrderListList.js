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
	'../controllers/WearerReturnOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerReturnOrderListList = Marionette.LayoutView.extend({
			defaults: {
				data: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerReturnOrderList,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'return_count': '#return_count',
				'return_num': '.return_num',
				'target_flg': '.target_flg',
			},
			bindings: {
				'#return_count': 'return_count',
				'.return_num': 'return_num',
				'.target_flg': 'target_flg',
			},
			onShow: function() {
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				var that = this;
				var data = this.options.data;

				// 発注商品一覧、
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WR0020;
				var cond = {
					"scr": '不要品返却-発注商品一覧',
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
						if (res_list["individual_flg"] == true) {
							$('.individual_flg').css('display', '');
						}
						$.unblockUI();
					}
				});
			},
			events: {
				'change @ui.target_flg': function(e){
					var return_num = 0;

					var target_id = e.target.id;
					var return_id = target_id.replace( /target_flg/g , "return_num" ) ;
					return_num = parseInt($('#'+return_id).val());
					if(e.target.checked){
						return_num += 1;
					}else{
						return_num -= 1;
					}
					$('#'+return_id).val(return_num);
					var return_count = parseInt(0);
					$(".return_num").each(function () {
						if ($(this).val()) {
							return_count += parseInt($(this).val());
						}
					});
					$("input[name='return_count']").val(return_count);
				},
				'change @ui.return_num': function(e){
					var return_count = 0;
					return_count = parseInt(e.target.value);
					if(isNaN(return_count)){
						return_count = 0;
					}
					$('#'+e.target.id).val(return_count);
					var list_cnt = $('#list_cnt').val();
					var sum_return_num = 0;
					for (var i=0; i<list_cnt; i++) {
						sum_return_num += parseInt($('#return_num'+i).val());
					}
					$('#return_count').val(sum_return_num);
				},
			},
		});
	});
});
