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
				'target_flg': '.target_flg',
				'size_cd': '.size_cd',
				'order_num': '.order_num',
				'return_num': '.return_num',
				'size_add': '.size_add',
				'size_del': '.size_del'
			},
			bindings: {
				'#order_count': 'order_count',
				'#return_count': 'return_count',
				'.target_flg': 'target_flg',
				'.size_cd': 'size_cd',
				'.order_num': 'order_num',
				'.return_num': 'return_num',
				'.size_add': 'size_add',
				'.size_del': 'size_del'
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
				'change @ui.size_cd': function(e) {
					e.preventDefault();
					var that = this;

 				},
				'click @ui.size_add': function(e) {
					e.preventDefault();
					var that = this;

					var table = document.getElementById("order_table");
					var rowIndex = parseInt(e.target.parentNode.parentNode.rowIndex) + parseInt(1);
					var target_vals = e.target.value;
					var target_val = target_vals.split(':');
					var line_no = target_val[0];
 					var data = {
						"item_cd": target_val[1],
						"color_cd": target_val[2],
						"size_cd": target_val[3]
					};
					var individual_flg = target_val[4];
					var add_cnt = $("input[name='add_cnt"+line_no+"']").val();
					add_cnt = parseInt(add_cnt) + parseInt(1);
					$("input[name='add_cnt"+line_no+"']").val(add_cnt);
					if (parseInt(add_cnt) == 5) {
						$("#size_add"+line_no).prop("disabled", true);
					}

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WX0015;
					var cond = {
						"scr": 'サイズ交換-サイズ追加',
						"log_type": '3',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_list = res.attributes;

							if (res_list["add_item"][0]) {
								var new_row = table.insertRow(rowIndex);
								var cell1 = new_row.insertCell(0);
								var cell2 = new_row.insertCell(1);
								var cell3 = new_row.insertCell(2);
								var cell4 = new_row.insertCell(3);
								var cell5 = new_row.insertCell(4);
								var cell6 = new_row.insertCell(5);
								var cell7 = new_row.insertCell(6);
								var cell8 = new_row.insertCell(7);
								var cell9 = new_row.insertCell(8);
								var cell10 = new_row.insertCell(9);
								if (individual_flg) {
									var cell11 = new_row.insertCell(10);
								}

								var color = "#F2F5A9";
								cell1.style.backgroundColor = color;
								cell2.style.backgroundColor = color;
								cell3.style.backgroundColor = color;
								cell4.style.backgroundColor = color;
								cell5.style.backgroundColor = color;
								cell6.style.backgroundColor = color;
								cell7.style.backgroundColor = color;
								cell8.style.backgroundColor = color;
								cell9.style.backgroundColor = color;
								cell10.style.backgroundColor = color;
								if (individual_flg) {
									cell11.style.backgroundColor = color;
								}

								for (var i=1; i<6; i++) {
									if ($('input[name="add_no'+line_no+'-'+i+'"]').length == 0) {
										var add_no = i;
										break;
									}
								}
								var cell1_html =
									'<input type="hidden" name="rntl_sect_cd'+line_no+'-'+add_no+'" value="'+res_list["add_item"][0]["rntl_sect_cd"]+'">'+
									'<input type="hidden" name="job_type_cd'+line_no+'-'+add_no+'" value="'+res_list["add_item"][0]["job_type_cd"]+'">'+
									'<input type="hidden" name="job_type_item_cd'+line_no+'-'+add_no+'" value="'+res_list["add_item"][0]["job_type_item_cd"]+'">'+
									'<input type="hidden" name="item_cd'+line_no+'-'+add_no+'" value="'+res_list["add_item"][0]["item_cd"]+'">'+
									'<input type="hidden" name="color_cd'+line_no+'-'+add_no+'" value="'+res_list["add_item"][0]["color_cd"]+'">'+
									'<input type="hidden" class="add_no" name="add_no'+line_no+'-'+add_no+'" value="'+add_no+'">';
									'<input type="hidden" id="add_flg" name="add_flg'+line_no+'-'+add_no+'" value="'+add_no+'">';
								var cell2_html = res_list["add_item"][0]["item_name"];
								var cell3_html = res_list["add_item"][0]["possible_num"]+'<input type="hidden" name="possible_num'+line_no+'-'+add_no+'" value="'+res_list["add_item"][0]["possible_num"]+'">';
								var cell4_html = res_list["add_item"][0]["item_and_color"]+'<br/>'+res_list["add_item"][0]["input_item_name"];
								var cell5_html = '<input type="hidden" name="now_size_cd'+line_no+'-'+add_no+'" value="'+res_list["add_item"][0]["now_size_cd"]+'">';
								var option_str = '';
								for (var i=0; i<res_list["add_item"][0]["size_cd"].length; i++) {
									option_str += '<option value="'+res_list["add_item"][0]["size_cd"][i]["size"]+'">'+res_list["add_item"][0]["size_cd"][i]["size"]+'</option>';
								}
								var cell6_html = '<select class="form-control input-sm" id="size_cd" name="size_cd'+line_no+'-'+add_no+'">'+option_str+'</select>';
								var cell7_html = '<input type="hidden" name="exchange_possible_num'+line_no+'-'+add_no+'" value="'+res_list["add_item"][0]["exchange_possible_num"]+'">';
								if (individual_flg) {
									var cell8_html = '';
									var cell9_html = '<input type="text" style="width:4em; font-weight:normal; text-align:center;" class="input-sm order_num" id="order_num'+line_no+'-'+add_no+'" name="order_num'+line_no+'-'+add_no+'" value="0">';
									var cell10_html = '';
									var cell11_html = '<button type="button" class="btn btn-primary size_del" title="削除" id="size_del'+line_no+'" value="'+line_no+'">削除</button>';
								} else {
									var cell8_html = '<input type="text" style="width:4em; font-weight:normal; text-align:center;" class="input-sm order_num" id="order_num'+line_no+'-'+add_no+'" name="order_num'+line_no+'-'+add_no+'" value="0">';
									var cell9_html = '';
									var cell10_html = '<button type="button" class="btn btn-primary size_del" title="削除" id="size_del'+line_no+'" value="'+line_no+'">削除</button>';
								}

								cell1.innerHTML = cell1_html;
								cell2.innerHTML = cell2_html;
								cell3.innerHTML = cell3_html;
								cell4.innerHTML = cell4_html;
								cell5.innerHTML = cell5_html;
								cell6.innerHTML = cell6_html;
								cell7.innerHTML = cell7_html;
								cell8.innerHTML = cell8_html;
								cell9.innerHTML = cell9_html;
								cell10.innerHTML = cell10_html;
								if (individual_flg) {
									cell11.innerHTML = cell11_html;
								}
							}
						}
					});
				},
				'click @ui.size_del': function(e) {
					e.preventDefault();
					var that = this;
					var line_no = e.target.value;
					var add_cnt = $("input[name='add_cnt"+line_no+"']").val();

					var table = document.getElementById("order_table");
					var rowIndex = e.target.parentNode.parentNode.rowIndex
					var rows = table.deleteRow(rowIndex);

					add_cnt = parseInt(add_cnt) - parseInt(1);
					$("#size_add"+line_no).prop("disabled", false);
					$("input[name='add_cnt"+line_no+"']").val(add_cnt);

					var order_count = parseInt(0);
					$(".order_num").each(function () {
						if ($(this).val()) {
							order_count += parseInt($(this).val());
						}
					});
					$("input[name='order_count']").val(order_count);
				},
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
				'change @ui.return_num': function(e) {
					e.preventDefault();
					var that = this;

					var return_num = parseInt(e.target.value);
					if(isNaN(return_num)){
						return_num = 0;
					}
					$("#"+e.target.id).val(return_num);

					var return_count = parseInt(0);
					$(".return_num").each(function () {
						if ($(this).val()) {
							return_count += parseInt($(this).val());
						}
					});
					$("input[name='return_count']").val(return_count);
				},
				'change @ui.target_flg': function(e) {
					e.preventDefault();
					var that = this;

					var no = e.target.classList[1];
					var cnt = parseInt(0);
					$("#"+e.target.id).each(function () {
						if ($(this).prop("checked")) {
							cnt += parseInt(1);
						}
					});
					$("#return_num"+no).val(cnt);

					var return_count = parseInt(0);
					$(".return_num").each(function () {
						if ($(this).val()) {
							return_count += parseInt($(this).val());
						}
					});
					$("input[name='return_count']").val(return_count);
				}
			}
		});
	});
});
