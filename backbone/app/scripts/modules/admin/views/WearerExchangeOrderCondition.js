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
	'../controllers/WearerExchangeOrder',
	'./ReasonKbnConditionChange',
	'./ShipmentConditionChange',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerExchangeOrderCondition = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerExchangeOrderCondition,
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
			},
			onRender: function() {
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WX0010;
				var cond = {
					"scr": 'サイズ交換-着用者情報',
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
						//セッションが破棄されていたら検索画面へ
						if(res_list['no_session_flg'] == '1'){
							// 検索画面の条件項目を取得
							var cond = window.sessionStorage.getItem("wearer_size_change_cond");
							window.sessionStorage.setItem("back_wearer_size_change_cond", cond);
							// 検索一覧画面へ遷移
							location.href="wearer_size_change.html";
						}
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

						var data = {
							'rntl_cont_no': res_list['rntl_cont_no'],
							'rntl_sect_cd': res_list['rntl_sect_cd']
						}
						var modelForUpdate2 = that.model;
						modelForUpdate2.url = App.api.CM0140;
						var cond = {
							"scr": 'サイズ交換-発注入力・送信可否チェック',
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
						if (res_list['reason_kbn_list']) {
							for (var i=0; i<res_list['reason_kbn_list'].length; i++) {
								var option = document.createElement('option');
								var text = document.createTextNode(res_list['reason_kbn_list'][i]['reason_kbn_name']);
								option.setAttribute('value', res_list['reason_kbn_list'][i]['reason_kbn']);
								if (res_list['reason_kbn_list'][i]['selected'] != "") {
									option.setAttribute('selected', res_list['reason_kbn_list'][i]['selected']);
								}
								option.appendChild(text);
								document.getElementById('reason_kbn').appendChild(option);
							}
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
						var cond = window.sessionStorage.getItem("wearer_size_change_cond");
						window.sessionStorage.setItem("back_wearer_size_change_cond", cond);
						location.href="wearer_size_change.html";
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
					//更新可否フラグ絞り込み用 セレクトボックスの拠点cd取得
					var rntl_sect_cd = $("select[name='section']").val();
					var rntl_cont_no = $("select[name='agreement_no']").val();

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": 'サイズ交換-発注取消-更新可否チェック',
						"log_type": '3',
						"data": data,
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no,
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							var type = "cm0130_res";
							var transition = "WX0012_req";
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
						"scr": 'サイズ交換-入力完了-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no

					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WX0013_req";
							var data = "";
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				'click @ui.orderSend': function(){
					var that = this;
					var rntl_cont_no = $("select[name='agreement_no']").val();
					var rntl_sect_cd = $("select[name='section']").val();
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": 'サイズ交換-発注送信-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WX0014_req";
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
						$('#myModalAlert').modal(); //追加
						//メッセージの修正
						document.getElementById("alert_txt").innerHTML=val["error_msg"];
					} else {
						if (transition == "WX0012_req") {
							var type = transition;
							var res_val = "";
						} else if (transition == "WX0013_req") {
							var type = transition;
							var res_val = "";
						} else if (transition == "WX0014_req") {
							var type = transition;
							var res_val = "";
						}
					}
				}
				if (type == "WX0012_req") {
					// JavaScript モーダルで表示
					$('#myModal').modal('show'); //追加
					//メッセージの修正
					document.getElementById("confirm_txt").innerHTML=App.delete_msg; //追加　このメッセージはapp.jsで定義
					$("#btn_ok").off();
					$("#btn_ok").on('click',function() { //追加
						hideModal();
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var modelForUpdate = that.model;
						modelForUpdate.url = App.api.WX0012;
						var cond = {
							"scr": 'サイズ交換-発注取消',
							"data": data,
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var type = "WX0012_res";
								var res_val = res.attributes;

								// if (res_val["error_code"] == "0") {
								// 	$.unblockUI();
								// 	alert('発注取消が完了しました。このまま検索画面へ移行します。');

									var cond = window.sessionStorage.getItem("wearer_size_change_cond");
									window.sessionStorage.setItem("back_wearer_size_change_cond", cond);
									location.href="wearer_size_change.html";
								// } else {
								// 	$.unblockUI();
								// 	alert('発注取消中にエラーが発生しました');
								// }
							}
						});
					});
				}
				if (type == "WX0013_req") {
					var tran_req_no = $("button[name='complete_param']").val();
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					var emply_cd_flg = $("#emply_cd_flg").prop("checked");
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
						item[i] = new Object();
						item[i]["rntl_sect_cd"] = $("input[name='rntl_sect_cd"+i+"']").val();
						item[i]["job_type_cd"] = $("input[name='job_type_cd"+i+"']").val();
						item[i]["job_type_item_cd"] = $("input[name='job_type_item_cd"+i+"']").val();
						item[i]["item_cd"] = $("input[name='item_cd"+i+"']").val();
						item[i]["color_cd"] = $("input[name='color_cd"+i+"']").val();
						item[i]["now_size_cd"] = $("input[name='now_size_cd"+i+"']").val();
						item[i]["size_cd"] = $("select[name='size_cd"+i+"']").val();
						item[i]["possible_num"] = $("input[name='possible_num"+i+"']").val();
						item[i]["order_num"] = $("input[name='order_num"+i+"']").val();
						item[i]["exchange_possible_num"] = $("input[name='exchange_possible_num"+i+"']").val();
						item[i]["individual_flg"] = $("input[name='individual_flg"+i+"']").val();
						item[i]["add_flg"] = $("input[name='add_flg"+i+"']").val();
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
						}
						item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						item[i]["size_add_data"] = new Object();
						if (item[i]["add_flg"] == "1") {
							var cnt = 0;
							for (var j=1; j<6; j++) {
								if ($('input[name="add_no'+i+'-'+j+'"]').length == 0) {
									continue;
								}
								item[i]["size_add_data"][cnt] = new Object();
								item[i]["size_add_data"][cnt]["rntl_sect_cd"] = $("input[name='rntl_sect_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["job_type_cd"] = $("input[name='job_type_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["job_type_item_cd"] = $("input[name='job_type_item_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["item_cd"] = $("input[name='item_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["color_cd"] = $("input[name='color_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["now_size_cd"] = $("input[name='now_size_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["size_cd"] = $("select[name='size_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["possible_num"] = $("input[name='possible_num"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["order_num"] = $("input[name='order_num"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["exchange_possible_num"] = $("input[name='exchange_possible_num"+i+'-'+j+"']").val();
								cnt++;
							}
						}
					}
					//console.log(item);
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WX0013;
					var cond = {
						"scr": 'サイズ交換-入力完了-check',
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
										"scr": 'サイズ交換-入力完了-update',
										"mode": "update",
										"wearer_data": wearer_data,
										"item": item
									};
									that.triggerMethod('inputComplete', data);
								});
							} else {
								that.triggerMethod('showAlerts', res_val["error_msg"]);
								return;
							}
						}
					});
				}
				if (type == "WX0014_req") {
					var tran_req_no = $("button[name='send_param']").val();
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					var emply_cd_flg = $("#emply_cd_flg").prop("checked");
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
						item[i] = new Object();
						item[i]["rntl_sect_cd"] = $("input[name='rntl_sect_cd"+i+"']").val();
						item[i]["job_type_cd"] = $("input[name='job_type_cd"+i+"']").val();
						item[i]["job_type_item_cd"] = $("input[name='job_type_item_cd"+i+"']").val();
						item[i]["item_cd"] = $("input[name='item_cd"+i+"']").val();
						item[i]["color_cd"] = $("input[name='color_cd"+i+"']").val();
						item[i]["now_size_cd"] = $("input[name='now_size_cd"+i+"']").val();
						item[i]["size_cd"] = $("select[name='size_cd"+i+"']").val();
						item[i]["possible_num"] = $("input[name='possible_num"+i+"']").val();
						item[i]["order_num"] = $("input[name='order_num"+i+"']").val();
						item[i]["exchange_possible_num"] = $("input[name='exchange_possible_num"+i+"']").val();
						item[i]["individual_flg"] = $("input[name='individual_flg"+i+"']").val();
						item[i]["add_flg"] = $("input[name='add_flg"+i+"']").val();
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
						}
						item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						item[i]["size_add_data"] = new Object();
						if (item[i]["add_flg"] == "1") {
							var cnt = 0;
							for (var j=1; j<6; j++) {
								if ($('input[name="add_no'+i+'-'+j+'"]').length == 0) {
									continue;
								}
								item[i]["size_add_data"][cnt] = new Object();
								item[i]["size_add_data"][cnt]["rntl_sect_cd"] = $("input[name='rntl_sect_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["job_type_cd"] = $("input[name='job_type_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["job_type_item_cd"] = $("input[name='job_type_item_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["item_cd"] = $("input[name='item_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["color_cd"] = $("input[name='color_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["now_size_cd"] = $("input[name='now_size_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["size_cd"] = $("select[name='size_cd"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["possible_num"] = $("input[name='possible_num"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["order_num"] = $("input[name='order_num"+i+'-'+j+"']").val();
								item[i]["size_add_data"][cnt]["exchange_possible_num"] = $("input[name='exchange_possible_num"+i+'-'+j+"']").val();
								cnt++;
							}
						}
					}

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WX0014;
					var cond = {
						"scr": 'サイズ交換-発注送信',
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
										"scr": 'サイズ交換-発注送信-update',
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
