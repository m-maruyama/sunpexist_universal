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
				'reason_kbn': '.reason_kbn',
				'sex_kbn': '.sex_kbn',
				"section": ".section",
				"job_type": ".job_type",
				"shipment": ".shipment",
				"wearer_info": ".wearer_info",
			},
			ui: {
				'agreement_no': '#agreement_no',
				'reason_kbn': '#reason_kbn',
				'sex_kbn': '#sex_kbn',
				'emply_cd_flg': '#emply_cd_flg',
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
				'#reason_kbn': 'reason_kbn',
				'#sex_kbn': 'sex_kbn',
				'#emply_cd_flg': 'emply_cd_flg',
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

				// 着用者情報(着用者名、(読み仮名)、社員コード、発令日、セッションパラメータ)
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
						//console.log(res_list);

						// 発注取消ボタンvalue値設定
						var delete_param =
							res_list['rntl_cont_no'] + ":"
							+ res_list['rntl_sect_cd'] + ":"
							+ res_list['job_type_cd'] + ":"
							+ res_list['werer_cd'] + ":"
							+ res_list['order_req_no'] + ":"
							+ res_list['return_req_no']
						;
						that.ui.delete.val(delete_param);

						// 発注情報トランがある場合は発注取消ボタンを表示
						if (res_list['order_tran_flg'] == '1') {
							$('.delete').css('display', '');
						}

						// 入力完了、発注送信ボタン表示/非表示制御
						var data = {
							'rntl_cont_no': res_list['rntl_cont_no'],
							'rntl_sect_cd': res_list['rntl_sect_cd']
						}
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
								var CM0140_res = res.attributes;
								//console.log(CM0140_res);

								//「入力完了」ボタン表示制御
								if (CM0140_res['order_input_ok_flg'] == "1" || CM0140_res['order_send_ok_flg'] == "1") {
									$('.complete').css('display', '');
								}
								if (CM0140_res['order_send_ok_flg'] == "1") {
									$('.orderSend').css('display', '');
								}
							}
						});

						// 社員コード、着用者名、読みかな、コメント欄
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
						// 発令日
						var appointment_ymd = "";
						if (res_list['wearer_info'][0]) {
							var appointment_ymd = res_list['wearer_info'][0]['appointment_ymd'];
						}
						that.ui.datepicker1.datetimepicker({
							format: 'YYYY/MM/DD',
							//useCurrent: 'day',
							defaultDate: appointment_ymd,
							//maxDate: yesterday,
							locale: 'ja',
							sideBySide:true,
							useCurrent: false,
							// daysOfWeekDisabled:[0,6]
						});
						that.ui.datepicker1.on('dp.change', function(){
							$(this).data('DateTimePicker').hide();
							//$(this).find('input').trigger('input');
						});
						// 着用開始日
						var resfl_ymd = "";
						if (res_list['wearer_info'][0]) {
							var resfl_ymd = res_list['wearer_info'][0]['resfl_ymd'];
						}
						that.ui.datepicker2.datetimepicker({
							format: 'YYYY/MM/DD',
							//useCurrent: 'day',
							defaultDate: resfl_ymd,
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
				// 「戻る」ボタン
				'click @ui.back': function(){
					// 検索画面の条件項目を取得
					var cond = window.sessionStorage.getItem("wearer_change_cond");
					window.sessionStorage.setItem("back_wearer_change_cond", cond);
					// 検索一覧画面へ遷移
					location.href="wearer_change.html";
				},
				// 「発注取消」ボタン
				'click @ui.delete': function(){
					var that = this;

					// 発注取消パラメータ取得
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

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '職種変更または異動-発注取消-更新可否チェック',
						"log_type": '3',
						"data": data,
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
				// 「入力完了」ボタン
				'click @ui.complete': function(){
					var that = this;

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '職種変更または異動-入力完了-更新可否チェック',
						"log_type": '1',
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
				// 「発注送信」ボタン
				'click @ui.orderSend': function(){
					var that = this;

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '職種変更または異動-発注送信-更新可否チェック',
						"log_type": '1',
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
				// 拠点
				'change @ui.section': function(){
					this.ui.section = $('#section');
					var agreement_no = $("select[name='agreement_no']").val();
					var section = $("select[name='section']").val();

					// 出荷先以降の内容変更
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

					// 入力完了、発注送信ボタン表示/非表示制御
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
							//「入力完了」ボタン表示制御
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
						}
					});
				},
				// 貸与パターン
				'change @ui.job_type': function(){
					var that = this;
					this.ui.job_type = $('#job_type');
					var section = $("select[name='section']").val();
					// 選択前のvalue値
					var before_vals = window.sessionStorage.getItem("job_type_sec");
					// 選択後のvalue値
					var after_vals = $("select[name='job_type']").val();
					var val = after_vals.split(':');
					var job_type = val[0];
					var sp_job_type_flg = val[1];
					var data = {
						'section': section,
						'job_type': job_type
					}

					if (sp_job_type_flg == "1") {
						//--特別職種フラグ有りの場合--//
						var msg = "社内申請手続きを踏んでいますか？";
						if (window.confirm(msg)) {
							// 理由区分表示切り替え
							var reasonKbnConditionChangeView = new App.Admin.Views.ReasonKbnConditionChange({
								job_type: job_type
							});
							this.reason_kbn.show(reasonKbnConditionChangeView);

							// 現在貸与中のアイテム、新たに追加されるアイテム表示切り替え
							that.triggerMethod('change:job_type', data);
						} else {
							// キャンセルの場合は選択前の状態に戻す
							document.getElementById('job_type').value = before_vals;
						}
					} else {
						//--特別職種フラグ無しの場合--//
						window.sessionStorage.setItem("job_type_sec", after_vals);

						// 理由区分表示切り替え
						var reasonKbnConditionChangeView = new App.Admin.Views.ReasonKbnConditionChange({
							job_type: job_type
						});
						this.reason_kbn.show(reasonKbnConditionChangeView);

						// 現在貸与中のアイテム、新たに追加されるアイテム表示切り替え
						that.triggerMethod('change:job_type', data);
					}
				},
				// 出荷先
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
			onShow: function(val, type, transition, data) {
				var that = this;

				// 更新可否チェック結果処理
				if (type == "cm0130_res") {
					if (!val["chk_flg"]) {
						// 更新可否フラグ=更新不可の場合はアラートメッセージ表示
						alert(val["error_msg"]);
					} else {
						// エラーがない場合は各対応処理へ移行
						if (transition == "WC0020_req") {
							// 発注取消処理
							var type = transition;
							var res_val = "";
						} else if (transition == "WC0021_req") {
							// 入力完了処理
							var type = transition;
							var res_val = "";
						} else if (transition == "WC0022_req") {
							// 発注送信処理
							var type = transition;
							var res_val = "";
						}
					}
				}
				// 発注取消処理
				if (type == "WC0020_req") {
					var msg = "削除しますが、よろしいですか？";
					if (window.confirm(msg)) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var modelForUpdate = this.model;
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

								if (res_val["error_code"] == "0") {
									// 発注取消完了後、検索一覧へ遷移
									$.unblockUI();
									alert('発注取消が完了しました。このまま検索画面へ移行します。');

									// 検索画面の条件項目を取得
									var cond = window.sessionStorage.getItem("wearer_change_cond");
									window.sessionStorage.setItem("back_wearer_change_cond", cond);
									// 検索一覧画面へ遷移
									location.href="wearer_change.html";
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました');
								}
							}
						});
					}
				}
				// 入力完了処理
				if (type == "WC0021_req") {
					//--画面入力項目--//
					// 着用者情報
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					var emply_cd_flg = $("#emply_cd_flg").prop("checked");
					var member_no = $("input[name='member_no']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var appointment_ymd = $("input[name='appointment_ymd']").val();
					var resfl_ymd = $("input[name='resfl_ymd']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var shipment = $("select[name='shipment']").val();
					var comment = $("#comment").val();
					var wearer_data = {
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						'emply_cd_flg': emply_cd_flg,
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

					// 現在貸与中のアイテム
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
						// 商品毎の「対象」チェック状態、「個体管理番号」を取得
						now_item[i]["individual_data"] = new Object();
						if (now_item[i]["individual_flg"]) {
							//個体管理番号表示フラグがONの場合、対象、個体管理番号単位
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
								// 対象=trueの数（商品単位返却数）
								now_item[i]["individual_data"][j]["return_num"] = chk_num;
							}
						} else {
							//個体管理番号表示フラグがOFFの場合、商品単位の返却数
							now_item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						}
//						now_item[i]["now_order_num"] = $("input[name='now_order_num"+i+"']").val();
//						now_item[i]["now_order_num_disable"] = $("input[name='now_order_num_disable"+i+"']").val();
						now_item[i]["now_return_num"] = $("input[name='now_return_num"+i+"']").val();
						now_item[i]["now_return_num_disable"] = $("input[name='now_return_num_disable"+i+"']").val();
					}

					// 追加されるアイテム
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

					// 入力項目チェック処理
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
								var msg = "入力を完了しますが、よろしいですか？";
								if (window.confirm(msg)) {
									var data = {
										"scr": '入力完了',
										"mode": "update",
										"wearer_data": wearer_data,
										"now_item": now_item,
										"add_item": add_item,
									};
									//console.log(data);
									// 入力完了画面処理へ移行
									that.triggerMethod('inputComplete', data);
								}
							} else {
								that.triggerMethod('showAlerts', res_val["error_msg"]);
								return;
							}
						}
					});
				}
				// 発注送信処理
				if (type == "WC0022_req") {
					//--画面入力項目--//
					// 着用者情報
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					var emply_cd_flg = $("#emply_cd_flg").prop("checked");
					var member_no = $("input[name='member_no']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var appointment_ymd = $("input[name='appointment_ymd']").val();
					var resfl_ymd = $("input[name='resfl_ymd']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var shipment = $("select[name='shipment']").val();
					//var post_number = $("input[name='post_number']").val();
					//var address = $("input[name='address']").val();
					var comment = $("#comment").val();
					var wearer_data = {
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						'emply_cd_flg': emply_cd_flg,
						'member_no': member_no,
						'member_name': member_name,
						'member_name_kana': member_name_kana,
						'sex_kbn': sex_kbn,
						'appointment_ymd': appointment_ymd,
						'resfl_ymd': resfl_ymd,
						'section': section,
						'job_type': job_type,
						'shipment': shipment,
						//'post_number': post_number,
						//'address': address,
						'comment': comment,
					}

					// 現在貸与中のアイテム
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
						// 商品毎の「対象」チェック状態、「個体管理番号」を取得
						now_item[i]["individual_data"] = new Object();
						if (now_item[i]["individual_flg"]) {
							//個体管理番号表示フラグがONの場合、対象、個体管理番号単位
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
								// 対象=trueの数（商品単位返却数）
								now_item[i]["individual_data"][j]["return_num"] = chk_num;
							}
						} else {
							//個体管理番号表示フラグがOFFの場合、商品単位の返却数
							now_item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						}
//						now_item[i]["now_order_num"] = $("input[name='now_order_num"+i+"']").val();
//						now_item[i]["now_order_num_disable"] = $("input[name='now_order_num_disable"+i+"']").val();
						now_item[i]["now_return_num"] = $("input[name='now_return_num"+i+"']").val();
						now_item[i]["now_return_num_disable"] = $("input[name='now_return_num_disable"+i+"']").val();
					}

					// 追加されるアイテム
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
								var msg = "発注送信を行いますが、よろしいですか？";
								if (window.confirm(msg)) {
									var data = {
										"scr": '発注送信',
										"mode": "update",
										"wearer_data": wearer_data,
										"now_item": now_item,
										"add_item": add_item,
									};
									//console.log(data);

									// 入力完了画面処理へ移行
									that.triggerMethod('sendComplete', data);
								}
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
