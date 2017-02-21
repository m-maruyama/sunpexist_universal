define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'./SectionCondition',
	'typeahead',
	'bloodhound',
	'blockUI',
	'../controllers/WearerChangeOrder',
	'./ReasonKbnConditionChange',
	'./ShipmentConditionChange',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerChangeOrderCondition = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerChangeOrderCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				'agreement_no': '.agreement_no',
				//'reason_kbn': '.reason_kbn',
				'sex_kbn': '.sex_kbn',
				"section": ".section",
				"job_type": ".job_type",
				"shipment": ".shipment",
				"wearer_info": ".wearer_info",
			},
			ui: {
				'agreement_no': '#agreement_no',
				//'reason_kbn': '#reason_kbn',
				'sex_kbn': '#sex_kbn',
				//'emply_cd_flg': '#emply_cd_flg',
				'member_no': '#member_no',
				'member_name': '#member_name',
				'member_name_kana': '#member_name_kana',
				'appointment_ymd': '#appointment_ymd',
				'resfl_ymd': '#resfl_ymd',
				'section': '#section',
				'job_type': '#job_type',
				'shipment': '#shipment',
				'post_number': '#post_number',
				'address': '#address',
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
				//'#reason_kbn': 'reason_kbn',
				'#sex_kbn': 'sex_kbn',
				//'#emply_cd_flg': 'emply_cd_flg',
				'#member_no': 'member_no',
				'#member_name': 'member_name',
				'#member_name_kana': 'member_name_kana',
				'#appointment_ymd': 'appointment_ymd',
				'#resfl_ymd': 'resfl_ymd',
				'#section': 'section',
				'#job_type': 'job_type',
				'#shipment': 'shipment',
				'#post_number': 'post_number',
				'#address': 'address',
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
				modelForUpdate.url = App.api.WC0018;
				var cond = {
					"scr": '職種変更または異動-着用者情報',
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
						//以前の拠点画面に埋め込み
						$("#bef_rntl_sect_cd").val(res_list['bef_rntl_sect_cd']);
						//以前の職種画面に埋め込み
						$("#bef_job_type_cd").val(res_list['bef_job_type_cd']);

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

						if (res_list['order_tran_flg'] == '1') {
							$('.delete').css('display', '');
						}

						var data = {
							'rntl_cont_no': res_list['rntl_cont_no'],
							'rntl_sect_cd': res_list['rntl_sect_cd']
						};
						var modelForUpdate2 = that.model;
						modelForUpdate2.url = App.api.CM0140;
						var cond = {
							"scr": '職種変更または異動-発注入力・送信可否チェック',
							"log_type": '3',
							"data": data,
						};
						modelForUpdate2.fetchMx({
							data:cond,
							success:function(res){
								var CM0140_res = res.attributes

								if (CM0140_res['order_input_ok_flg'] == "1" || CM0140_res['order_send_ok_flg'] == "1") {
									$('.complete').css('display', '');
								}
								if (CM0140_res['order_send_ok_flg'] == "1") {
									$('.orderSend').css('display', '');
								}
							},
							complete: function (res) {

								//拠点と出荷先が同じだったら、拠点と同じに変更
								var section_name = $('[name=section] option:selected').text();
								var m_shipment_to = $('[name=shipment] option:selected').text();

								if(section_name == m_shipment_to){
									$('#shipment').prop('selectedIndex',0);
								}
							}
						});

						if (res_list['wearer_info'][0]) {
							that.ui.member_no.val(res_list['wearer_info'][0]['cster_emply_cd']);
							that.ui.member_name.val(res_list['wearer_info'][0]['werer_name']);
							that.ui.member_name_kana.val(res_list['wearer_info'][0]['werer_name_kana']);
							that.ui.comment.val(res_list['wearer_info'][0]['comment']);
						}
						var maxTime = new Date();
						maxTime.setHours(15);
						maxTime.setMinutes(59);
						maxTime.setSeconds(59);
						var minTime = new Date();
						minTime.setHours(9);
						minTime.setMinutes(0);
						var appointment_ymd = "";
						if (res_list['wearer_info'][0]) {
							var appointment_ymd = res_list['wearer_info'][0]['appointment_ymd'];
						}
						that.ui.appointment_ymd.val(appointment_ymd);
						that.ui.datepicker1.datetimepicker({
							format: 'YYYY/MM/DD',
							//useCurrent: 'day',
							//defaultDate: appointment_ymd,
							//maxDate: yesterday,
							locale: 'ja',
							sideBySide:true,
							useCurrent: false,
							// daysOfWeekDisabled:[0,6]
						});
						that.ui.datepicker1.on('dp.change', function(){
							$(this).data('DateTimePicker').hide();
						});
						var resfl_ymd = "";
						if (res_list['wearer_info'][0]) {
							var resfl_ymd = res_list['wearer_info'][0]['resfl_ymd'];
						}
						that.ui.resfl_ymd.val(resfl_ymd);
						that.ui.datepicker2.datetimepicker({
							format: 'YYYY/MM/DD',
							//useCurrent: 'day',
							//defaultDate: resfl_ymd,
							//maxDate: yesterday,
							locale: 'ja',
							sideBySide:true,
							useCurrent: false,
							// daysOfWeekDisabled:[0,6]
						});
						that.ui.datepicker2.on('dp.change', function(){
							$(this).data('DateTimePicker').hide();
						});
					}
				});
			},
			events: {
				'click @ui.back': function(){
					var cond = window.sessionStorage.getItem("wearer_change_cond");
					window.sessionStorage.setItem("back_wearer_change_cond", cond);
					location.href="wearer_change.html";
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
						"scr": '職種変更または異動-発注取消-更新可否チェック',
						"log_type": '3',
						"data": data,
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no,
						"update_skip_flg": true
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							var type = "cm0130_res";
							var transition = "WC0020_req";
							var data = cond["data"];
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				'click @ui.complete': function(){
					var that = this;

					//更新可否フラグ絞り込み用 セレクトボックスの拠点cd取得
					var rntl_sect_cd = $("select[name='section']").val();
					var rntl_cont_no = $("select[name='agreement_no']").val();
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '職種変更または異動-入力完了-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no,
						"update_skip_flg": true
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WC0021_req";
							var data = "";
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				'click @ui.orderSend': function(){
					var that = this;

					//更新可否フラグ絞り込み用 セレクトボックスの拠点cd取得
					var rntl_sect_cd = $("select[name='section']").val();
					var rntl_cont_no = $("select[name='agreement_no']").val();

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '職種変更または異動-発注送信-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no,
						"update_skip_flg": true
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WC0022_req";
							var data = "";
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				'change @ui.section': function(){
					this.ui.section = $('#section');
					var agreement_no = $("select[name='agreement_no']").val();
					var section = $("select[name='section']").val();

					var shipment_vals = $("select[name='shipment']").val();
					var val = shipment_vals.split(':');
					var ship_to_cd = val[0];
					var ship_to_brnch_cd = val[1];
					var shipmentConditionChangeView = new App.Admin.Views.ShipmentConditionChange({
						section: section,
						ship_to_cd: ship_to_cd,
						ship_to_brnch_cd: ship_to_brnch_cd,
						chg_flg: '1',
					});
					this.shipment.show(shipmentConditionChangeView);

					var data = {
						'rntl_cont_no': agreement_no,
						'rntl_sect_cd': section
					};
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0140;
					var cond = {
						"scr": '職種変更または異動-発注入力・送信可否チェック',
						"log_type": '3',
						"data": data,
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res) {
							var CM0140_res = res.attributes;
							/*
							if (CM0140_res['order_input_ok_flg'] == "1" && CM0140_res['order_send_ok_flg'] == "1") {
								$('.complete').css('display', '');
								$('.orderSend').css('display', '');
							}
							if (CM0140_res['order_input_ok_flg'] == "0" && CM0140_res['order_send_ok_flg'] == "0") {
								$('.complete').css('display', 'none');
								$('.orderSend').css('display', 'none');
							}
							if (CM0140_res['order_input_ok_flg'] == "0" && CM0140_res['order_send_ok_flg'] == "1") {
								$('.complete').css('display', 'none');
								$('.orderSend').css('display', '');
							}
							if (CM0140_res['order_input_ok_flg'] == "1" && CM0140_res['order_send_ok_flg'] == "0") {
								$('.complete').css('display', '');
								$('.orderSend').css('display', 'none');
							}
							*/
						}
					});
				},
				'change @ui.job_type': function(){
					var that = this;
					this.ui.job_type = $('#job_type');
					var section = $("select[name='section']").val();
					var before_vals = window.sessionStorage.getItem("job_type_sec");
					var after_vals = $("select[name='job_type']").val();
					var val = after_vals.split(':');
					var job_type = val[0];
					var sp_job_type_flg = val[1];
					var data = {
						'section': section,
						'job_type': job_type
					}

					if (sp_job_type_flg == "1") {
						// JavaScript モーダルで表示
						$('#myModal').modal('show'); //追加
						//メッセージの修正
						document.getElementById("confirm_txt").innerHTML=App.apply_msg; //追加　このメッセージはapp.jsで定義
						$("#btn_ok").off();
						$("#btn_cancel").off();
						$("#btn_ok").on('click',function() { //追加
							$('#myModal').modal('hide');
						// var msg = "社内申請手続きを踏んでいますか？";
						// if (window.confirm(msg)) {
							var reasonKbnConditionChangeView = new App.Admin.Views.ReasonKbnConditionChange({
								job_type: job_type
							});
							// that.reason_kbn.show(reasonKbnConditionChangeView);
							that.triggerMethod('change:job_type', data);
						});
						$("#btn_cancel").on('click',function() { //追加
							$('#myModal').modal('hide');
							// that.reason_kbn.show(reasonKbnConditionChangeView);
							document.getElementById('job_type').value = before_vals;
						});

					} else {
						window.sessionStorage.setItem("job_type_sec", after_vals);

						var reasonKbnConditionChangeView = new App.Admin.Views.ReasonKbnConditionChange({
							job_type: job_type
						});
						//this.reason_kbn.show(reasonKbnConditionChangeView);
						that.triggerMethod('change:job_type', data);
					}
				},
				'change @ui.shipment': function(){
					this.ui.shipment = $('#shipment');
					var section = $("select[name='section']").val();
					var shipment_vals = $("select[name='shipment']").val();
					var val = shipment_vals.split(':');
					var ship_to_cd = val[0];
					var ship_to_brnch_cd = val[1];
					var shipmentConditionChangeView = new App.Admin.Views.ShipmentConditionChange({
						section: section,
						ship_to_cd: ship_to_cd,
						ship_to_brnch_cd: ship_to_brnch_cd,
						chg_flg: 'shipment',
					});
					this.shipment.show(shipmentConditionChangeView);
				},
			},
			go_change: function () {
				this.ui.shipment = $('#shipment');
				var section = $("select[name='section']").val();
				var shipment_vals = $("select[name='shipment']").val();
				var val = shipment_vals.split(':');
				var ship_to_cd = val[0];
				var ship_to_brnch_cd = val[1];
				change_select(section,ship_to_cd,ship_to_brnch_cd,this.shipment);
			},
			onShow: function(val, type, transition, data) {
				var that = this;

				if (type == "cm0130_res") {
					if (!val["chk_flg"]) {
                        // JavaScript モーダルで表示
                        $('#myModalAlert').modal('show'); //追加
                        //メッセージの修正
                        document.getElementById("alert_txt").innerHTML=res_val["error_msg"];
						// alert(val["error_msg"]);
					} else {
						if (transition == "WC0020_req") {
							var type = transition;
							var res_val = "";
						} else if (transition == "WC0021_req") {
							var type = transition;
							var res_val = "";
						} else if (transition == "WC0022_req") {
							var type = transition;
							var res_val = "";
						}
					}
				}
				if (type == "WC0020_req") {
					// JavaScript モーダルで表示
					$('#myModal').modal('show'); //追加
					//メッセージの修正
					document.getElementById("confirm_txt").innerHTML=App.delete_msg; //追加　このメッセージはapp.jsで定義
					$("#btn_ok").off();
					$("#btn_cancel").off();
					$("#btn_ok").on('click',function() { //追加
						hideModal();
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var modelForUpdate = that.model;
						modelForUpdate.url = App.api.WC0020;
						var cond = {
							"scr": '発注取消',
							"data": data,
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var type = "WC0020_res";
								var res_val = res.attributes;

								// if (res_val["error_code"] == "0") {
									$.unblockUI();
									// alert('発注取消が完了しました。このまま検索画面へ移行します。');

									var cond = window.sessionStorage.getItem("wearer_change_cond");
									window.sessionStorage.setItem("back_wearer_change_cond", cond);
									location.href="wearer_change.html";
								// } else {
								// 	$.unblockUI();
								// 	alert('発注取消中にエラーが発生しました');
								// }
							}
						});
					});
				}
				if (type == "WC0021_req") {
					//以前の職種と拠点
					var	bef_rntl_sect_cd = $("#bef_rntl_sect_cd").val();
					var bef_job_type_cd = $("#bef_job_type_cd").val();
					//変更ごの職種と拠点
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var job_type_result = job_type.split(':');

					if(bef_job_type_cd == '24'){
						var reason_kbn = '24'; //
					} else if(bef_job_type_cd !== job_type_result[0] && bef_rntl_sect_cd == section){
						var reason_kbn = '09'; //職種変更
					}else if(bef_job_type_cd == job_type_result[0] && bef_rntl_sect_cd !== section){
						var reason_kbn = '10'; //拠点異動
					}else if(bef_job_type_cd !== job_type_result[0] && bef_rntl_sect_cd !== section) {
						var reason_kbn = '11'; //貸与パターン変更&拠点異動
					}
					var tran_req_no = $("button[name='complete_param']").val();
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					//var emply_cd_flg = $("#emply_cd_flg").prop("checked");
					var member_no = $("input[name='member_no']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var appointment_ymd = $("input[name='appointment_ymd']").val();
					var resfl_ymd = $("input[name='resfl_ymd']").val();
					var shipment = $("select[name='shipment']").val();
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
						'appointment_ymd': appointment_ymd,
						'resfl_ymd': resfl_ymd,
						'section': section,
						'job_type': job_type,
						'shipment': shipment,
						'comment': comment,
					}

					var now_target_flg = 'now_target_flg[]';
					var now_list_cnt = $("input[name='now_list_cnt']").val();
					var now_item = new Object();
					for (var i=0; i<now_list_cnt; i++) {
						now_item[i] = new Object();
						now_item[i]["now_rntl_sect_cd"] = $("input[name='now_rntl_sect_cd"+i+"']").val();
						now_item[i]["now_job_type_cd"] = $("input[name='now_job_type_cd"+i+"']").val();
						now_item[i]["now_job_type_item_cd"] = $("input[name='now_job_type_item_cd"+i+"']").val();
						now_item[i]["now_item_cd"] = $("input[name='now_item_cd"+i+"']").val();
						now_item[i]["now_color_cd"] = $("input[name='now_color_cd"+i+"']").val();
						now_item[i]["now_choice_type"] = $("input[name='now_choice_type"+i+"']").val();
						now_item[i]["now_std_input_qty"] = $("input[name='now_std_input_qty"+i+"']").val();
						now_item[i]["now_size_cd"] = $("input[name='now_size_cd"+i+"']").val();
						now_item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
						now_item[i]["possible_num"] = $("input[name='possible_num"+i+"']").val();
						now_item[i]["individual_flg"] = $("input[name='individual_flg']").val();
						now_item[i]["individual_data"] = new Object();
						if (now_item[i]["individual_flg"]) {
							now_item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
							var Name = 'now_target_flg'+i;
							var chk_num = 0;
							for (var j=0; j<now_item[i]["individual_cnt"]; j++) {
								var chk_val = document.getElementsByName(Name)[j].value;
								now_item[i]["individual_data"][j] = new Object();
								var checked = document.getElementsByName(Name)[j].checked;
								if(checked == true){
									now_item[i]["individual_data"][j]["now_target_flg"] = '1';
									now_item[i]["individual_data"][j]["individual_ctrl_no"] = chk_val;
									chk_num = chk_num + 1;
								} else {
									now_item[i]["individual_data"][j]["now_target_flg"] = '0';
									now_item[i]["individual_data"][j]["individual_ctrl_no"] = chk_val;
								}
								now_item[i]["individual_data"][j]["return_num"] = chk_num;
							}
						} else {
							now_item[i]["now_return_num"] = $("input[name='now_return_num"+i+"']").val();
						}
						now_item[i]["now_return_num"] = $("input[name='now_return_num"+i+"']").val();
						now_item[i]["now_return_num_disable"] = $("input[name='now_return_num_disable"+i+"']").val();
					}
					var add_list_cnt = $("input[name='add_list_cnt']").val();
					var add_item = new Object();
					for (var i=0; i<add_list_cnt; i++) {
						add_item[i] = new Object();
						add_item[i]["add_rntl_sect_cd"] = $("input[name='add_rntl_sect_cd"+i+"']").val();
						add_item[i]["add_job_type_cd"] = $("input[name='add_job_type_cd"+i+"']").val();
						add_item[i]["add_job_type_item_cd"] = $("input[name='add_job_type_item_cd"+i+"']").val();
						add_item[i]["add_item_cd"] = $("input[name='add_item_cd"+i+"']").val();
						add_item[i]["add_color_cd"] = $("input[name='add_color_cd"+i+"']").val();
						add_item[i]["add_choice_type"] = $("input[name='add_choice_type"+i+"']").val();
						add_item[i]["add_std_input_qty"] = $("input[name='add_std_input_qty"+i+"']").val();
						add_item[i]["add_size_cd"] = $("select[name='add_size_cd"+i+"']").val();
						add_item[i]["add_order_num"] = $("input[name='add_order_num"+i+"']").val();
						add_item[i]["add_order_num_disable"] = $("input[name='add_order_num_disable"+i+"']").val();
					}
					//console.log(now_item);
					//console.log(add_item);

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WC0021;
					var cond = {
						"scr": '入力完了',
						"mode": "check",
						"wearer_data": wearer_data,
						"now_item": now_item,
						"add_item": add_item,
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
								$("#btn_cancel").off();
								$("#btn_ok").on('click',function() { //追加
									hideModal();
									var data = {
										"scr": '入力完了',
										"mode": "update",
										"wearer_data": wearer_data,
										"now_item": now_item,
										"add_item": add_item,
									};
									//console.log(data);
									that.triggerMethod('inputComplete', data);
								});
							} else {
								that.triggerMethod('showAlerts', res_val["error_msg"]);
								return;
							}
						}
					});
				}
				if (type == "WC0022_req") {
					//以前の職種と拠点
					var	bef_rntl_sect_cd = $("#bef_rntl_sect_cd").val();
					var bef_job_type_cd = $("#bef_job_type_cd").val();
					//変更ごの職種と拠点
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var job_type_result = job_type.split(':');

					if(bef_job_type_cd == '24'){
						var reason_kbn = '24'; //
					} else if(bef_job_type_cd !== job_type_result[0] && bef_rntl_sect_cd == section){
						var reason_kbn = '09'; //職種変更
					}else if(bef_job_type_cd == job_type_result[0] && bef_rntl_sect_cd !== section){
						var reason_kbn = '10'; //拠点異動
					}else if(bef_job_type_cd !== job_type_result[0] && bef_rntl_sect_cd !== section) {
						var reason_kbn = '11'; //貸与パターン変更&拠点異動
					}

					var tran_req_no = $("button[name='send_param']").val();
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = reason_kbn;
					//var emply_cd_flg = $("#emply_cd_flg").prop("checked");
					var member_no = $("input[name='member_no']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var appointment_ymd = $("input[name='appointment_ymd']").val();
					var resfl_ymd = $("input[name='resfl_ymd']").val();
					var shipment = $("select[name='shipment']").val();
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
						'appointment_ymd': appointment_ymd,
						'resfl_ymd': resfl_ymd,
						'section': section,
						'job_type': job_type,
						'shipment': shipment,
						'comment': comment,
					}

					var now_target_flg = 'now_target_flg[]';
					var now_list_cnt = $("input[name='now_list_cnt']").val();
					var now_item = new Object();
					for (var i=0; i<now_list_cnt; i++) {
						now_item[i] = new Object();
						now_item[i]["now_rntl_sect_cd"] = $("input[name='now_rntl_sect_cd"+i+"']").val();
						now_item[i]["now_job_type_cd"] = $("input[name='now_job_type_cd"+i+"']").val();
						now_item[i]["now_job_type_item_cd"] = $("input[name='now_job_type_item_cd"+i+"']").val();
						now_item[i]["now_item_cd"] = $("input[name='now_item_cd"+i+"']").val();
						now_item[i]["now_color_cd"] = $("input[name='now_color_cd"+i+"']").val();
						now_item[i]["now_choice_type"] = $("input[name='now_choice_type"+i+"']").val();
						now_item[i]["now_std_input_qty"] = $("input[name='now_std_input_qty"+i+"']").val();
						now_item[i]["now_size_cd"] = $("input[name='now_size_cd"+i+"']").val();
						now_item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
						now_item[i]["possible_num"] = $("input[name='possible_num"+i+"']").val();
						now_item[i]["individual_flg"] = $("input[name='individual_flg']").val();
						now_item[i]["individual_data"] = new Object();
						if (now_item[i]["individual_flg"]) {
							now_item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
							var Name = 'now_target_flg'+i;
							var chk_num = 0;
							for (var j=0; j<now_item[i]["individual_cnt"]; j++) {
								var chk_val = document.getElementsByName(Name)[j].value;
								now_item[i]["individual_data"][j] = new Object();
								var checked = document.getElementsByName(Name)[j].checked;
								if(checked == true){
									now_item[i]["individual_data"][j]["now_target_flg"] = '1';
									now_item[i]["individual_data"][j]["individual_ctrl_no"] = chk_val;
									chk_num = chk_num + 1;
								} else {
									now_item[i]["individual_data"][j]["now_target_flg"] = '0';
									now_item[i]["individual_data"][j]["individual_ctrl_no"] = chk_val;
								}
								now_item[i]["individual_data"][j]["return_num"] = chk_num;
							}
						} else {
							now_item[i]["now_return_num"] = $("input[name='now_return_num"+i+"']").val();
						}
						now_item[i]["now_return_num"] = $("input[name='now_return_num"+i+"']").val();
						now_item[i]["now_return_num_disable"] = $("input[name='now_return_num_disable"+i+"']").val();
					}

					var add_list_cnt = $("input[name='add_list_cnt']").val();
					var add_item = new Object();
					for (var i=0; i<add_list_cnt; i++) {
						add_item[i] = new Object();
						add_item[i]["add_rntl_sect_cd"] = $("input[name='add_rntl_sect_cd"+i+"']").val();
						add_item[i]["add_job_type_cd"] = $("input[name='add_job_type_cd"+i+"']").val();
						add_item[i]["add_job_type_item_cd"] = $("input[name='add_job_type_item_cd"+i+"']").val();
						add_item[i]["add_item_cd"] = $("input[name='add_item_cd"+i+"']").val();
						add_item[i]["add_color_cd"] = $("input[name='add_color_cd"+i+"']").val();
						add_item[i]["add_choice_type"] = $("input[name='add_choice_type"+i+"']").val();
						add_item[i]["add_std_input_qty"] = $("input[name='add_std_input_qty"+i+"']").val();
						add_item[i]["add_size_cd"] = $("select[name='add_size_cd"+i+"']").val();
						add_item[i]["add_order_num"] = $("input[name='add_order_num"+i+"']").val();
						add_item[i]["add_order_num_disable"] = $("input[name='add_order_num_disable"+i+"']").val();
					}

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WC0022;
					var cond = {
						"scr": '発注送信',
						"mode": "check",
						"wearer_data": wearer_data,
						"now_item": now_item,
						"add_item": add_item,
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
								$("#btn_cancel").off();
								$("#btn_ok").on('click',function() { //追加
									hideModal();
									var data = {
										"scr": '発注送信',
										"mode": "update",
										"wearer_data": wearer_data,
										"now_item": now_item,
										"add_item": add_item,
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
	function change_select(section, m_shipment_to, ship_to_brnch_cd,shipment_obj) {
		var shipmentConditionChangeView = new App.Admin.Views.ShipmentConditionChange({
			section: section,
			ship_to_cd: m_shipment_to,
			ship_to_brnch_cd: ship_to_brnch_cd,
			chg_flg: 'shipment',
		});
		shipment_obj.show(shipmentConditionChangeView);
	}
});
