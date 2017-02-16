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
	'../controllers/WearerReturnOrder',
	'./ReasonKbnConditionChange',
	'./ShipmentConditionChange'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerReturnOrderCondition = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerReturnOrderCondition,
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
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WR0019;
				var cond = {
					"scr": '不要品返却-着用者情報',
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

						var delete_param =
								res_list['rntl_cont_no'] + ":"
								+ res_list['rntl_sect_cd'] + ":"
								+ res_list['job_type_cd'] + ":"
								+ res_list['werer_cd'] + ":"
								+ res_list['order_req_no'] + ":"
								+ res_list['return_req_no']
							;
						that.ui.delete.val(delete_param);
						that.ui.complete.val(res_list['order_req_no']);
						that.ui.orderSend.val(res_list['order_req_no']);
						$('#werer_cd').val(res_list['werer_cd']);

						if (res_list['order_tran_flg'] == '1' && res_list['return_tran_flg'] == '1') {
							$('.delete').css('display', '');
						}

						var data = {
							'rntl_cont_no': res_list['rntl_cont_no'],
							'rntl_sect_cd': res_list['rntl_sect_cd']
						}
						var modelForUpdate2 = that.model;
						modelForUpdate2.url = App.api.CM0140;
						var cond = {
							"scr": '不要品返却-発注入力・送信可否チェック',
							"log_type": '3',
							"data": data,
						};
						modelForUpdate2.fetchMx({
							data:cond,
							success:function(res){
								var CM0140_res = res.attributes;
								//console.log(CM0140_res);

								if (CM0140_res['order_input_ok_flg'] == "1" || CM0140_res['order_send_ok_flg'] == "1") {
									$('.complete').css('display', '');
								}
								if (CM0140_res['order_send_ok_flg'] == "1") {
									$('.orderSend').css('display', '');
								}
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
						// console.log(res_list['job_type_list']);
						if (res_list['job_type_list'][0]) {
							var option3 = document.createElement('option');
							var text3 = document.createTextNode(res_list['job_type_list'][0]['job_type_name']);
							option3.setAttribute('value', res_list['job_type_list'][0]['job_type_cd']);
							option3.appendChild(text3);
							document.getElementById('job_type').appendChild(option3);
							if(res_list['job_type_list'][0]['order_control_unit'] == '1'){
								//optionの配列を作成
								$("#reason_kbn").append($('<option value="07">不用品返却</option>'));
								$("#reason_kbn").val("07");
							}
							if(res_list['job_type_list'][0]['order_control_unit'] == '2'){
								//optionの配列を作成
								$("#reason_kbn").append($('<option value="28">不用品返却</option>'));
								$("#reason_kbn").val("28");
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
					var cond = window.sessionStorage.getItem("wearer_other_cond");
					window.sessionStorage.setItem("back_wearer_other_cond", cond);
					location.href="wearer_other.html";
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
					var return_req_no = val[5];
					var data = {
						"werer_cd": werer_cd,
						"order_req_no": order_req_no,
						"return_req_no": return_req_no,
						"rntl_cont_no": rntl_cont_no,
						"rntl_sect_cd": rntl_sect_cd,
						"job_type_cd": job_type_cd
					};

					var rntl_cont_no = $("select[name='agreement_no']").val();
					var rntl_sect_cd = $("select[name='section']").val();
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '不要品返却-発注取消-更新可否チェック',
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
							var transition = "WR0021_req";
							var data = cond["data"];
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				'click @ui.complete': function(){
					var that = this;
					var rntl_sect_cd = $("select[name='section']").val();
					var rntl_cont_no = $("select[name='agreement_no']").val();
					var modelForUpdate = that.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '不要品返却-入力完了-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WR0022_req";
							var data = "";
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				'click @ui.orderSend': function(){
					var that = this;
					var rntl_sect_cd = $("select[name='section']").val();
					var rntl_cont_no = $("select[name='agreement_no']").val();

					var data = {
						"rntl_cont_no": rntl_cont_no,
						"werer_cd": $("#werer_cd").val(),
					};
					// 発注入力遷移前に発注NGパターンチェック実施
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0013;
					var cond = {
						"scr": 'その他貸与/返却(不要品返却)-発注NGパターンチェック',
						"log_type": '3',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["err_cd"] == "0") {
								var modelForUpdate = that.model;
								modelForUpdate.url = App.api.CM0130;
								var cond = {
									"scr": '不要品返却-発注送信-更新可否チェック',
									"log_type": '1',
									"rntl_sect_cd": rntl_sect_cd,
									"rntl_cont_no": rntl_cont_no
								};
								modelForUpdate.fetchMx({
									data:cond,
									success:function(res){
										var type = "cm0130_res";
										var res_val = res.attributes;
										var transition = "WR0023_req";
										var data = "";
										that.onShow(res_val, type, transition, data);
									}
								});
							} else {
								// JavaScript モーダルで表示
								//$('#myModal_alert').modal('show');
								var data = {
									error_msg:res_val["err_msg"]
								};
								that.triggerMethod('inputComplete', data);
								//document.getElementById("alert_txt").innerHTML=res_val["err_msg"];
								// NGエラーアラート表示
								//alert(res_val["err_msg"]);
								return true;
							}
						}
					});





				}
			},
			onShow: function(val, type, transition, data) {
				var that = this;


				if (type == "cm0130_res") {
					if (!val["chk_flg"]) {
						alert(val["error_msg"]);
					} else {
						if (transition == "WR0021_req") {
							var type = transition;
							var res_val = "";
						} else if (transition == "WR0022_req") {
							var type = transition;
							var res_val = "";
						} else if (transition == "WR0023_req") {
							var type = transition;
							var res_val = "";
						}
					}
				}
				if (type == "WR0021_req") {
					$("#btn_ok").off();
					//メッセージの修正い
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0021;
					// JavaScript モーダルで表示
					$('#myModal').modal();
					document.getElementById("confirm_txt").innerHTML=App.delete_msg;

						var cond = {
							"scr": '不要品返却-発注取消',
							"data": data
						};
					$("#btn_ok").on('click',function() {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var type = "WR0021_res";
								var res_val = res.attributes;

								if (res_val["error_code"] == "0") {
									$.unblockUI();
									hideModal();
									//alert('発注取消が完了しました。このまま検索画面へ移行します。');
									var cond = window.sessionStorage.getItem("wearer_other_cond");
									window.sessionStorage.setItem("back_wearer_other_cond", cond);
									location.href="wearer_other.html";
								} else {
									$.unblockUI();
									hideModal();
									//alert('発注取消中にエラーが発生しました');
									return;
								}
							}
						});
					});
				}
				if (type == "WR0022_req") {
					$("#btn_ok").off();
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
					var werer_cd = $("#werer_cd").val();
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
						'comment': comment,
						'werer_cd': werer_cd
					}

					var list_cnt = $("input[name='list_cnt']").val();
					var item = new Object();
					for (var i=0; i<list_cnt; i++) {
						item[i] = new Object();
						item[i]["rntl_sect_cd"] = $("input[name='rntl_sect_cd"+i+"']").val();
						item[i]["job_type_cd"] = $("input[name='job_type_cd"+i+"']").val();
						item[i]["job_type_item_cd"] = $("input[name='job_type_item_cd"+i+"']").val();
						item[i]["item_cd"] = $("input[name='item_cd"+i+"']").val();
						item[i]["color_cd"] = $("input[name='color_cd"+i+"']").val();
						item[i]["size_cd"] = $("input[name='size_cd"+i+"']").val();
						item[i]["possible_num"] = $("input[name='possible_num"+i+"']").val();
						item[i]["individual_flg"] = $("input[name='individual_flg"+i+"']").val();
						item[i]["individual_data"] = new Object();
						if (item[i]["individual_flg"]) {
							item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
							var Name = 'target_flg'+i;
							var chk_num = 0;
							for (var j=0; j<item[i]["individual_cnt"]; j++) {
								var chk_val = document.getElementsByName(Name)[j].value;
								item[i]["individual_data"][j] = new Object();
								var checked = document.getElementsByName(Name)[j].checked;
								if(checked == true){
									item[i]["individual_data"][j]["target_flg"] = '1';
									item[i]["individual_data"][j]["individual_ctrl_no"] = chk_val;
									chk_num = chk_num + 1;
								} else {
									item[i]["individual_data"][j]["target_flg"] = '0';
									item[i]["individual_data"][j]["individual_ctrl_no"] = chk_val;
								}
								item[i]["individual_data"][j]["return_num"] = chk_num;
							}
						} else {
							item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
							item[i]["individual_no"] = $("input[name='target_flg"+i+"']").val();
							item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						}
					}
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0022;
					var cond = {
						"scr": '不要品返却-入力完了-check',
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
								$('#myModal').modal('show');
								//メッセージの修正い
								document.getElementById("confirm_txt").innerHTML=App.input_msg;
								//var msg = "入力を完了しますが、よろしいですか？";
								//if (window.confirm(msg)) {
								$("#btn_ok").on('click',function() {
									hideModal();
									var data = {
										"scr": '不要品返却-入力完了-update',
										"mode": "update",
										"wearer_data": wearer_data,
										"item": item
									};
									that.triggerMethod('inputComplete', data);
								});
							} else if (res_val["error_code"] == "1") {
								that.triggerMethod('showAlerts', res_val["error_msg"]);
							//	return;
							}

						}
					});
				}
				if (type == "WR0023_req") {
					$("#btn_ok").off();
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
					var werer_cd = $("#werer_cd").val();
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
						'comment': comment,
						'werer_cd': werer_cd
					}

					var list_cnt = $("input[name='list_cnt']").val();
					var item = new Object();
					for (var i=0; i<list_cnt; i++) {
						item[i] = new Object();
						item[i]["rntl_sect_cd"] = $("input[name='rntl_sect_cd"+i+"']").val();
						item[i]["job_type_cd"] = $("input[name='job_type_cd"+i+"']").val();
						item[i]["job_type_item_cd"] = $("input[name='job_type_item_cd"+i+"']").val();
						item[i]["item_cd"] = $("input[name='item_cd"+i+"']").val();
						item[i]["color_cd"] = $("input[name='color_cd"+i+"']").val();
						item[i]["size_cd"] = $("input[name='size_cd"+i+"']").val();
						item[i]["possible_num"] = $("input[name='possible_num"+i+"']").val();
						item[i]["individual_flg"] = $("input[name='individual_flg"+i+"']").val();
						item[i]["individual_data"] = new Object();
						if (item[i]["individual_flg"]) {
							item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
							var Name = 'target_flg'+i;
							var chk_num = 0;
							for (var j=0; j<item[i]["individual_cnt"]; j++) {
								var chk_val = document.getElementsByName(Name)[j].value;
								item[i]["individual_data"][j] = new Object();
								var checked = document.getElementsByName(Name)[j].checked;
								if(checked == true){
									item[i]["individual_data"][j]["target_flg"] = '1';
									item[i]["individual_data"][j]["individual_ctrl_no"] = chk_val;
									chk_num = chk_num + 1;
								} else {
									item[i]["individual_data"][j]["target_flg"] = '0';
									item[i]["individual_data"][j]["individual_ctrl_no"] = chk_val;
								}
								item[i]["individual_data"][j]["return_num"] = chk_num;
							}
						} else {
							item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
							item[i]["individual_no"] = $("input[name='target_flg"+i+"']").val();
							item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						}
					}

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0023;
					var cond = {
						"scr": '不要品返却-発注送信',
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
								$('#myModal').modal('show');
								//メッセージの修正
								document.getElementById("confirm_txt").innerHTML=App.complete_msg;
								$("#btn_ok").on('click',function() {
									hideModal();
									var data = {
										"scr": '不要品返却-発注送信-update',
										"mode": "update",
										"wearer_data": wearer_data,
										"item": item
									};
									//console.log(data);
									that.triggerMethod('sendComplete', data);
								});
								//$("#btn_ok").off();
							} else {
								that.triggerMethod('showAlerts', res_val["error_msg"]);
							}
						}
					});
				}
			},
		});
	});
});
