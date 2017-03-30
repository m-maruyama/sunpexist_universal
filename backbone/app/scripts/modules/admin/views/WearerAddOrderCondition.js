define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'blockUI',
	'../controllers/WearerAddOrder',
	'./ReasonKbnConditionChange',
	'./ShipmentConditionChange',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerAddOrderCondition = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerAddOrderCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
			},
			ui: {
				'agreement_no': '#agreement_no',
				'reason_kbn': '#reason_kbn',
				'sex_kbn': '#sex_kbn',
				//'emply_cd_flg': '#emply_cd_flg',
				'member_no': '#member_no',
				'member_name': '#member_name',
				'member_name_kana': '#member_name_kana',
				'section': '#section',
				'job_type': '#job_type',
				'shipment': '#shipment',
				'comment': '#comment',
				"back": '.back',
				"delete": '.delete',
				"complete": '.complete',
				"orderSend": '.orderSend',
				'datepicker1': '.datepicker1',
				'datepicker2': '.datepicker2',
				'timepicker': '.timepicker',
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#reason_kbn': 'reason_kbn',
				'#sex_kbn': 'sex_kbn',
				//'#emply_cd_flg': 'emply_cd_flg',
				'#member_no': 'member_no',
				'#member_name': 'member_name',
				'#member_name_kana': 'member_name_kana',
				'#section': 'section',
				'#job_type': 'job_type',
				'#shipment': 'shipment',
				'#comment': 'comment',
				"#delete": 'delete',
				"#complete": 'complete',
				"#orderSend": 'orderSend',
				'#datepicker1': 'datepicker1',
				'#datepicker2': 'datepicker2',
				'#timepicker': 'timepicker',
			},
			onRender: function() {
				var that = this;

				// 着用者情報
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WR0014;
				var cond = {
					"scr": '追加貸与-着用者情報',
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
						// console.log(res_list);

						var delete_param =
							res_list['rntl_cont_no'] + ":"
							+ res_list['rntl_sect_cd'] + ":"
							+ res_list['job_type_cd'] + ":"
							+ res_list['werer_cd'] + ":"
							+ res_list['order_req_no']
						;
						that.ui.delete.val(delete_param);
						that.ui.complete.val(res_list['order_req_no']);
						that.ui.orderSend.val(res_list['order_req_no']);

						if (res_list['order_tran_flg'] == '1') {
							$('.delete').css('display', '');
						}

						var data = {
							'rntl_cont_no': res_list['rntl_cont_no'],
							'rntl_sect_cd': res_list['rntl_sect_cd']
						}
						var modelForUpdate2 = that.model;
						modelForUpdate2.url = App.api.CM0140;
						var cond = {
							"scr": '追加貸与-発注入力・送信可否チェック',
							"log_type": '3',
							"data": data,
						};
						modelForUpdate2.fetchMx({
							data:cond,
							success:function(res){
								var CM0140_res = res.attributes;

								if (CM0140_res['order_input_ok_flg'] == "1" || CM0140_res['order_send_ok_flg'] == "1") {
									$('.complete').css('display', '');
								}
								if (CM0140_res['order_send_ok_flg'] == "1") {
									$('.orderSend').css('display', '');
								}
								//発注画面 ナビ非表示
								$('#pageNav').css('display', 'none');
							}
						});
						if (res_list['wearer_info'][0]) {
							that.ui.member_no.val(res_list['wearer_info'][0]['cster_emply_cd']);
							that.ui.member_name.val(res_list['wearer_info'][0]['werer_name']);
							that.ui.member_name_kana.val(res_list['wearer_info'][0]['werer_name_kana']);
							that.ui.comment.val(res_list['wearer_info'][0]['comment']);
						}
						if (res_list['sex_kbn_list']) {
							for (var i=0; i<res_list['sex_kbn_list'].length; i++) {
								var option = document.createElement('option');
								var text = document.createTextNode(res_list['sex_kbn_list'][i]['sex_kbn_name']);
								option.setAttribute('value', res_list['sex_kbn_list'][i]['sex_kbn']);
								if (res_list['sex_kbn_list'][i]['selected'] != "") {
									option.setAttribute('selected', res_list['sex_kbn_list'][i]['selected']);
								}
								option.appendChild(text);
								document.getElementById('sex_kbn').appendChild(option);
							}
						}
						if (res_list['agreement_no_list'][0]) {
							var option1 = document.createElement('option');
							var str = res_list['agreement_no_list'][0]['rntl_cont_no'] + " " + res_list['agreement_no_list'][0]['rntl_cont_name'];
							var text1 = document.createTextNode(str);
							option1.setAttribute('value', res_list['agreement_no_list'][0]['rntl_cont_no']);
							option1.appendChild(text1);
							document.getElementById('agreement_no').appendChild(option1);
						}
						if (res_list['section_list'][0]) {
							var option2 = document.createElement('option');
							var text2 = document.createTextNode(res_list['section_list'][0]['rntl_sect_name']);
							option2.setAttribute('value', res_list['section_list'][0]['rntl_sect_cd']);
							option2.appendChild(text2);
							document.getElementById('section').appendChild(option2);
						}
						if (res_list['job_type_list'][0]) {
							var option3 = document.createElement('option');
							var text3 = document.createTextNode(res_list['job_type_list'][0]['job_type_name']);
							option3.setAttribute('value', res_list['job_type_list'][0]['job_type_cd']);
							option3.appendChild(text3);
							document.getElementById('job_type').appendChild(option3);
							if(res_list['job_type_list'][0]['order_control_unit'] == '1'){
								//optionの配列を作成
								$("#reason_kbn").append($('<option value="03">追加貸与</option>'));
								$("#reason_kbn").val("03");
							}
							if(res_list['job_type_list'][0]['order_control_unit'] == '2'){
								//optionの配列を作成
								$("#reason_kbn").append($('<option value="27">追加貸与</option>'));
								$("#reason_kbn").val("27");
							}
						}
						if (res_list['shipment_list'][0]) {
							var shipment = res_list['shipment_list'][0]['ship_to_cd'] + ":" + res_list['shipment_list'][0]['ship_to_brnch_cd'];
							that.ui.shipment.val(shipment);
						}
					}
				});
			},
			events: {
				'click @ui.back': function(){
					$('#myModal').modal(); //追加
					//メッセージの修正
					document.getElementById("confirm_txt").innerHTML=App.cancel_msg; //追加　このメッセージはapp.jsで定義
					$("#btn_ok").off();
					$("#btn_ok").on('click',function() { //追加
						hideModal();
						var cond = window.sessionStorage.getItem("wearer_other_cond");
						window.sessionStorage.setItem("back_wearer_other_cond", cond);
						location.href="wearer_other.html";
					});
				},
				'click @ui.delete': function(){
					var that = this;

					var delete_vals = $("button[name='delete_param']").val();
					var val = delete_vals.split(':');
					var rntl_cont_no = val[0];
					var rntl_sect_cd = val[1];
					var job_type_cd = val[2];
					var werer_cd = val[3];
					var order_req_no = val[4];
					var data = {
						"werer_cd": werer_cd,
						"order_req_no": order_req_no,
						"rntl_cont_no": rntl_cont_no,
						"rntl_sect_cd": rntl_sect_cd,
						"job_type_cd": job_type_cd
					};
					var rntl_sect_cd = $("select[name='section']").val();
					var rntl_cont_no = $("select[name='agreement_no']").val();

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '追加貸与-発注取消-更新可否チェック',
						"log_type": '3',
						"data": data,
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							var type = "cm0130_res";
							var transition = "WR0016_req";
							var data = cond["data"];
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				'click @ui.complete': function(){
					var that = this;
					var rntl_sect_cd = $("select[name='section']").val();
					var rntl_cont_no = $("select[name='agreement_no']").val();

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '追加貸与-入力完了-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WR0017_req";
							var data = "";
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				'click @ui.orderSend': function(){
					var that = this;
					var rntl_sect_cd = $("select[name='section']").val();
					var rntl_cont_no = $("select[name='agreement_no']").val();

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '追加貸与-発注送信-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WR0018_req";
							var data = "";
							that.onShow(res_val, type, transition, data);
						}
					});
				}
			},
			onShow: function(val, type, transition, data) {
				var that = this;

				if (type == "cm0130_res") {
					if (!val["chk_flg"]) {
						// JavaScript モーダルで表示
						$('#myModalAlert').modal('show'); //追加
						//メッセージの修正
						document.getElementById("alert_txt").innerHTML=val["error_msg"];
					} else {
						if (transition == "WR0016_req") {
							var type = transition;
							var res_val = "";
						} else if (transition == "WR0017_req") {
							var type = transition;
							var res_val = "";
						} else if (transition == "WR0018_req") {
							var type = transition;
							var res_val = "";
						}
					}
				}
				if (type == "WR0016_req") {
					// JavaScript モーダルで表示
					$('#myModal').modal('show'); //追加
					//メッセージの修正
					document.getElementById("confirm_txt").innerHTML=App.delete_msg; //追加　このメッセージはapp.jsで定義
					$("#btn_ok").off();
					$("#btn_ok").on('click',function() { //追加
						hideModal();
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var modelForUpdate = that.model;
						modelForUpdate.url = App.api.WR0016;
						var cond = {
							"scr": '追加貸与-発注取消',
							"data": data,
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var type = "WR0016_res";
								var res_val = res.attributes;

								// if (res_val["error_code"] == "0") {
									$.unblockUI();
									// alert('発注取消が完了しました。このまま検索画面へ移行します。');

									var cond = window.sessionStorage.getItem("wearer_other_cond");
									window.sessionStorage.setItem("back_wearer_other_cond", cond);
									location.href="wearer_other.html";
								// } else {
								// 	$.unblockUI();
								// 	alert('発注取消中にエラーが発生しました');
								// }
							}
						});
					});
				}
				if (type == "WR0017_req") {
					var tran_req_no = $("button[name='complete_param']").val();
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					//var emply_cd_flg = $("#emply_cd_flg").prop("checked");
					var member_no = $("input[name='member_no']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var shipment = $("input[name='shipment']").val();
					var comment = $("#comment").val();
					var wearer_data = {
						'tran_req_no': tran_req_no,
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						//'emply_cd_flg': emply_cd_flg,
						'member_no': member_no,
						'member_name': member_name,
						'member_name_kana': member_name_kana,
						'sex_kbn': sex_kbn,
						'section': section,
						'job_type': job_type,
						'shipment': shipment,
						'comment': comment
					}

					var list_cnt = $("input[name='list_cnt']").val();
					var item = new Object();
					for (var i=0; i<list_cnt; i++) {
						if($("input[name='order_num"+i+"']").val()==0&&!$("select[name='size_cd"+i+"']").val()){
							continue;
						}
						item[i] = new Object();
						item[i]["rntl_sect_cd"] = $("input[name='rntl_sect_cd"+i+"']").val();
						item[i]["job_type_cd"] = $("input[name='job_type_cd"+i+"']").val();
						item[i]["job_type_item_cd"] = $("input[name='job_type_item_cd"+i+"']").val();
						item[i]["item_cd"] = $("input[name='item_cd"+i+"']").val();
						item[i]["color_cd"] = $("input[name='color_cd"+i+"']").val();
						item[i]["choice_type"] = $("input[name='choice_type"+i+"']").val();
						item[i]["std_input_qty"] = $("input[name='std_input_qty"+i+"']").val();
						item[i]["size_cd"] = $("select[name='size_cd"+i+"']").val();
						item[i]["order_num"] = $("input[name='order_num"+i+"']").val();
						item[i]["order_num_disable"] = $("input[name='order_num_disable"+i+"']").val();
					}

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0017;
					var cond = {
						"scr": '追加貸与-入力完了-check',
						"mode": "check",
						"wearer_data": wearer_data,
						"item": item
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["error_code"] == "0") {
								// JavaScript モーダルで表示
								$('#myModal').modal('show'); //追加
								//メッセージの修正
								document.getElementById("confirm_txt").innerHTML=App.input_msg; //追加　このメッセージはapp.jsで定義
								$("#btn_ok").off();
								$("#btn_ok").on('click',function() { //追加
									hideModal();
									var data = {
										"scr": '追加貸与-入力完了-update',
										"mode": "update",
										"wearer_data": wearer_data,
										"item": item
									};

									// 入力完了画面処理へ移行
									that.triggerMethod('inputComplete', data);
								});
							} else {
								that.triggerMethod('showAlerts', res_val["error_msg"]);
								return;
							}
						}
					});
				}
				if (type == "WR0018_req") {
					var tran_req_no = $("button[name='send_param']").val();
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					//var emply_cd_flg = $("#emply_cd_flg").prop("checked");
					var member_no = $("input[name='member_no']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var shipment = $("input[name='shipment']").val();
					var comment = $("#comment").val();
					var wearer_data = {
						'tran_req_no': tran_req_no,
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						//'emply_cd_flg': emply_cd_flg,
						'member_no': member_no,
						'member_name': member_name,
						'member_name_kana': member_name_kana,
						'sex_kbn': sex_kbn,
						'section': section,
						'job_type': job_type,
						'shipment': shipment,
						'comment': comment
					}

					var list_cnt = $("input[name='list_cnt']").val();
					var item = new Object();
					for (var i=0; i<list_cnt; i++) {
						if($("input[name='order_num"+i+"']").val()==0&&!$("select[name='size_cd"+i+"']").val()){
							continue;
						}
						item[i] = new Object();
						item[i]["rntl_sect_cd"] = $("input[name='rntl_sect_cd"+i+"']").val();
						item[i]["job_type_cd"] = $("input[name='job_type_cd"+i+"']").val();
						item[i]["job_type_item_cd"] = $("input[name='job_type_item_cd"+i+"']").val();
						item[i]["item_cd"] = $("input[name='item_cd"+i+"']").val();
						item[i]["color_cd"] = $("input[name='color_cd"+i+"']").val();
						item[i]["choice_type"] = $("input[name='choice_type"+i+"']").val();
						item[i]["std_input_qty"] = $("input[name='std_input_qty"+i+"']").val();
						item[i]["size_cd"] = $("select[name='size_cd"+i+"']").val();
						item[i]["order_num"] = $("input[name='order_num"+i+"']").val();
						item[i]["order_num_disable"] = $("input[name='order_num_disable"+i+"']").val();
					}

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0018;
					var cond = {
						"scr": '追加貸与-発注送信',
						"mode": "check",
						"wearer_data": wearer_data,
						"item": item
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["error_code"] == "0") {
								// JavaScript モーダルで表示
								$('#myModal').modal('show'); //追加
								//メッセージの修正
								document.getElementById("confirm_txt").innerHTML=App.complete_msg; //追加　このメッセージはapp.jsで定義
								$("#btn_ok").off();
								$("#btn_ok").on('click',function() { //追加
									hideModal();
									var data = {
										"scr": '追加貸与-発注送信-update',
										"mode": "update",
										"wearer_data": wearer_data,
										"item": item
									};
									//console.log(data);

									that.triggerMethod('sendComplete', data);
								});
							} else {
								that.triggerMethod('showAlerts', res_val["error_msg"]);
								return;
							}
						}
					});
				}
			},
		});
	});
});
