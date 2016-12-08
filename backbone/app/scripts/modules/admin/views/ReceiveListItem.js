define([
	'app',
	'../Templates',
	'./ReceiveListItem',
	"entities/models/ReceiveAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ReceiveListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.receiveListItem,
			tagName: "tr",
			ui: {
				"detailLink": "a.detail",
				"receive_check": ".update_check",
			},
			bindings: {
				'.update_check': 'updateFlag'
			},
			onRender: function() {
				this.stickit();
			},
			events: {
				'click @ui.detailLink': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model);
				},
				'change @ui.receive_check': function(e){
						//チェックがオンの時に対象商品が返却情報トランにないかチェックする
						var that = this;
						var receive_check_id = e.target.id;
						// console.log($('#'+receive_check_id.replace( /receive_check/g , "size_cd" )).val());
						var rntl_cont_no = $('#'+receive_check_id.replace( /receive_check/g , "rntl_cont_no" )).val();
						var werer_cd = $('#'+receive_check_id.replace( /receive_check/g , "werer_cd" )).val();
						var size_cd = $('#'+receive_check_id.replace( /receive_check/g , "size_cd" )).val();
						var item_cd = $('#'+receive_check_id.replace( /receive_check/g , "item_cd" )).val();
						var color_cd = $('#'+receive_check_id.replace( /receive_check/g , "color_cd" )).val();
						// $('#return_num'+e.target.value).val();

						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.RE0030;
						var cond = {
							"scr": '受領商品チェック',
							"cond": {
								'rntl_cont_no':rntl_cont_no,
								'werer_cd':werer_cd,
								'size_cd':size_cd,
								'item_cd':item_cd,
								'color_cd':color_cd
							}
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var res_val = res.attributes;
								if (res_val["error_code"] == "1") {
									if(e.target.checked){
										e.target.checked = false;
									}else{
										e.target.checked = true;
									}
									alert(res_val["error_msg"]);
								}
							}
						});
                    //
                    //
					// var list_cnt = $('#list_cnt').val();
					// var sum_order_num = 0;
					// var sum_return_num = 0;
					// for (var i=0; i<list_cnt; i++) {
					// 	sum_order_num += parseInt($('#order_num'+i).val());
					// 	sum_return_num += parseInt($('#return_num'+i).val());
					// }
					// $('#order_count').val(sum_order_num);
					// $('#return_count').val(sum_return_num);
				}
			},
			templateHelpers: {
				//ステータス
				statusText: function(){
					var data = this.receipt_status;
					var retunr_str = '';
					if (data == 1) {
						retunr_str = retunr_str + "未受領";
					} else if (data == 2) {
						retunr_str = retunr_str + "受領済";
					}
					return retunr_str;
				},
				//よろず発注区分
				kubunText: function(){
					var data = this.kubun;
					if (data == 1) {
						return "貸与";
					} else if (data == 3) {
						return "サイズ交換";
					} else if (data == 4) {
						return "消耗交換";
					} else if (data == 5) {
						return "異動";
					}
					//throw "invalid Data";
					return 'invalid';
				},
			}
		});
	});
});
