define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/WearerEnd'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerEndOrderCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.wearerEndOrderCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				'reason_kbn': '.reason_kbn'
			},
			ui: {
				'agreement_no': '#agreement_no',
				'reason_kbn': '#reason_kbn',
				'cster_emply_cd': '#cster_emply_cd',
				'member_name': '#member_name',
				'member_name_kana': '#member_name_kana',
				'sex_kbn': '#sex_kbn',
				'shipment': '#shipment',
				'section': '#section',
				"job_type": "#job_type",
				'resfl_ymd' : '#resfl_ymd',
				'comment': '#comment',
				"back": '.back',
				"cancel": '.cancel',
				"delete": '.delete',
				"complete": '.complete',
				"orderSend": '.orderSend',
				'datepicker': '.datepicker',
				'timepicker': '.timepicker',
			},
			onRender: function() {
				var that = this;

				var maxTime = new Date();
				maxTime.setHours(15);
				maxTime.setMinutes(59);
				maxTime.setSeconds(59);
				var minTime = new Date();
				minTime.setHours(9);
				minTime.setMinutes(0);
				this.ui.datepicker.datetimepicker({
					format: 'YYYY/MM/DD',
					locale: 'ja',
					sideBySide: true,
					useCurrent: false,
				});
				this.ui.datepicker.on('dp.change', function () {
					$(this).data('DateTimePicker').hide();
				});
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WN0014;
				var cond = {
					"scr": '貸与終了-着用者情報',
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
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
						if (res_list['order_tran_flg'] == '1' && res_list['return_tran_flg'] == '1') {
							$('.delete').css('display', '');
						}
						that.ui.cster_emply_cd.prop('disabled',true);
						if (res_list['wearer_info'][0]) {
							that.ui.cster_emply_cd.val(res_list['wearer_info'][0]['cster_emply_cd']);
							that.ui.member_name.val(res_list['wearer_info'][0]['werer_name']);
							that.ui.member_name_kana.val(res_list['wearer_info'][0]['werer_name_kana']);
							that.ui.sex_kbn.val(res_list['wearer_info'][0]['sex_kbn']);
							that.ui.resfl_ymd.val(res_list['wearer_info'][0]['resfl_ymd']);
							that.ui.comment.val(res_list['wearer_info'][0]['comment']);
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
						}
						if (res_list['shipment_list'][0]) {
							var shipment = res_list['shipment_list'][0]['ship_to_cd'] + ":" + res_list['shipment_list'][0]['ship_to_brnch_cd'];
							that.ui.shipment.val(shipment);
						}

						var data = {
							'rntl_cont_no': res_list['rntl_cont_no'],
							'rntl_sect_cd': res_list['rntl_sect_cd']
						};
						modelForUpdate.url = App.api.CM0140;
						var cond = {
							"scr": '貸与終了-発注入力・送信可否チェック',
							"log_type": '3',
							"data": data,
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res) {
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
						var cond = window.sessionStorage.getItem("wearer_end_cond");
						window.sessionStorage.setItem("back_wearer_end_cond", cond);
						location.href="wearer_end.html";
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
					var return_req_no = val[5];
					var data = {
						"werer_cd": werer_cd,
						"order_req_no": order_req_no,
						"return_req_no": return_req_no,
						"rntl_cont_no": rntl_cont_no,
						"rntl_sect_cd": rntl_sect_cd,
						"job_type_cd": job_type_cd
					};
					var rntl_sect_cd = $("select[name='section']").val();
					var rntl_cont_no = $("select[name='agreement_no']").val();

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '貸与終了-発注取消-更新可否チェック',
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
							var transition = "WN0018_req";
							var data = cond["data"];
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				'click @ui.complete': function(){
					var that = this;
					var rntl_cont_no = $("select[name='agreement_no']").val();
					var rntl_sect_cd = $("select[name='section']").val();
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '貸与終了-入力完了-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WN0017_input_req";
							var data = "";
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				'click @ui.orderSend': function() {
					var that = this;
					var rntl_cont_no = $("select[name='agreement_no']").val();
					var rntl_sect_cd = $("select[name='section']").val();

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '貸与終了-発注送信-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WN0017_send_req";
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
						if (transition == "WN0017_input_req") {
							var type = transition;
							var res_val = "";
						} else if (transition == "WN0017_send_req") {
							var type = transition;
							var res_val = "";
						} else if (transition == "WN0018_req") {
							var type = transition;
							var res_val = "";
						}
					}
				}
				if (type == "WN0017_input_req") {
					var tran_req_no = $("button[name='complete_param']").val();
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					//var emply_cd_flg = $("#emply_cd_flg").prop("checked");
					var resfl_ymd = $("input[name='resfl_ymd']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var comment = $("#comment").val();
					var member_no = $("input[name='cster_emply_cd']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("input[name='sex_kbn']").val();
					var shipment = $("input[name='shipment']").val();
					var wearer_data = {
						'tran_req_no': tran_req_no,
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						//'emply_cd_flg': emply_cd_flg,
						'resfl_ymd': resfl_ymd,
						'member_no': member_no,
						'member_name': member_name,
						'member_name_kana': member_name_kana,
						'sex_kbn': sex_kbn,
						'section': section,
						'job_type': job_type,
						'shipment': shipment,
						'comment': comment,
						'snd_kbn': "0"
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
						item[i]["choice_type"] = $("input[name='choice_type"+i+"']").val();
						item[i]["std_input_qty"] = $("input[name='std_input_qty"+i+"']").val();
						item[i]["size_cd"] = $("input[name='size_cd"+i+"']").val();
						item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
						item[i]["possible_num"] = $("input[name='possible_num"+i+"']").val();
						item[i]["individual_flg"] = $("input[name='individual_flg']").val();
						item[i]["individual_data"] = new Object();
						if (item[i]["individual_flg"]){
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
							item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						}
						item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						item[i]["return_num_disable"] = $("input[name='return_num_disable"+i+"']").val();
					}

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WN0017;
					var cond = {
						"scr": '貸与終了-入力完了-check',
						"log_type": '3',
						"mode": "check",
						"wearer_data": wearer_data,
						"item": item
					};
					modelForUpdate.fetchMx({
						data: cond,
						success: function (res) {
							var res_val = res.attributes;

							if(res_val["error_code"] == '0') {
								// JavaScript モーダルで表示
								$('#myModal').modal('show'); //追加
								//メッセージの修正
								document.getElementById("confirm_txt").innerHTML=App.input_msg; //追加　このメッセージはapp.jsで定義
								$("#btn_ok").off();
								$("#btn_ok").on('click',function() { //追加
									hideModal();
									that.triggerMethod('inputComplete', cond);
								});
							}else if(res_val["error_code"] == '1') {
								that.triggerMethod('showAlerts', res_val["error_msg"]);
								return;
							}
						}
					});
				}
				if (type == "WN0017_send_req") {
					var tran_req_no = $("button[name='complete_param']").val();
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					//var emply_cd_flg = $("#emply_cd_flg").prop("checked");
					var resfl_ymd = $("input[name='resfl_ymd']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var comment = $("#comment").val();
					var member_no = $("input[name='cster_emply_cd']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("input[name='sex_kbn']").val();
					var shipment = $("input[name='shipment']").val();
					var wearer_data = {
						'tran_req_no': tran_req_no,
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						//'emply_cd_flg': emply_cd_flg,
						'resfl_ymd': resfl_ymd,
						'member_no': member_no,
						'member_name': member_name,
						'member_name_kana': member_name_kana,
						'sex_kbn': sex_kbn,
						'section': section,
						'job_type': job_type,
						'shipment': shipment,
						'comment': comment,
						'snd_kbn': "1"
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
						item[i]["choice_type"] = $("input[name='choice_type"+i+"']").val();
						item[i]["std_input_qty"] = $("input[name='std_input_qty"+i+"']").val();
						item[i]["size_cd"] = $("input[name='size_cd"+i+"']").val();
						item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
						item[i]["possible_num"] = $("input[name='possible_num"+i+"']").val();
						item[i]["individual_flg"] = $("input[name='individual_flg']").val();
						item[i]["individual_data"] = new Object();
						if (item[i]["individual_flg"]){
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
							item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						}
						item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						item[i]["return_num_disable"] = $("input[name='return_num_disable"+i+"']").val();
					}

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WN0017;
					var cond = {
						"scr": '貸与終了-発注送信-check',
						"mode": "check",
						"log_type": '3',
						"wearer_data": wearer_data,
						"item": item
					};
					modelForUpdate.fetchMx({
						data: cond,
						success: function (res) {
							var res_val = res.attributes;
							//console.log(res_val["param"]);

							if(res_val["error_code"] == '0') {
								// JavaScript モーダルで表示
								$('#myModal').modal('show'); //追加
								//メッセージの修正
								document.getElementById("confirm_txt").innerHTML=App.complete_msg; //追加　このメッセージはapp.jsで定義
								$("#btn_ok").off();
								$("#btn_ok").on('click',function() { //追加
									hideModal();
									that.triggerMethod('sendComplete', cond);
								});
							}else if(res_val["error_code"] == '1') {
								that.triggerMethod('showAlerts', res_val["error_msg"]);
								return;
							}
						}
					});
				}
				if (type == "WN0018_req") {
					// JavaScript モーダルで表示
					$('#myModal').modal('show'); //追加
					//メッセージの修正
					document.getElementById("confirm_txt").innerHTML=App.delete_msg; //追加　このメッセージはapp.jsで定義
					$("#btn_ok").off();
					$("#btn_ok").on('click',function() { //追加
						hideModal();
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var modelForUpdate = that.model;
						modelForUpdate.url = App.api.WN0018;
						var cond = {
							"scr": '貸与終了-発注取消',
							"log_type": '3',
							"data": data,
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var res_val = res.attributes;

								// if (res_val["error_code"] == "0") {
									$.unblockUI();
								// 	alert('発注取消が完了しました。このまま検索画面へ移行します。');
									var cond = window.sessionStorage.getItem("wearer_end_cond");
									window.sessionStorage.setItem("back_wearer_end_cond", cond);
									location.href="wearer_end.html";
								// } else {
								// 	$.unblockUI();
								// 	alert('発注取消中にエラーが発生しました');
								// }
							}
						});
					});
				}
			}
		});
	});
});
