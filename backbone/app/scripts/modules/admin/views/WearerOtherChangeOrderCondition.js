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
	'../controllers/WearerOtherChangeOrder',
	'./ReasonKbnConditionChange',
	'./ShipmentConditionChange',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerOtherChangeOrderCondition = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerOtherChangeOrderCondition,
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
				'return_date': '#return_date',
				'cster_emply_cd_chk': '#cster_emply_cd_chk',
				'cster_emply_cd': '#cster_emply_cd',
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
				'datepicker': '.datepicker',
				'timepicker': '.timepicker'
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#reason_kbn': 'reason_kbn',
				'#sex_kbn': 'sex_kbn',
				'#return_date': 'return_date',
				'#cster_emply_cd_chk': 'cster_emply_cd_chk',
				'#cster_emply_cd': 'cster_emply_cd',
				'#member_name': 'member_name',
				'#member_name_kana': 'member_name_kana',
				'#section': 'section',
				'#job_type': 'job_type',
				'#shipment': 'shipment',
				'#comment': 'comment',
				"#delete": 'delete',
				"#complete": 'complete',
				"#orderSend": 'orderSend',
				'datepicker': '.datepicker',
				'timepicker': '.timepicker'
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
					//useCurrent: 'day',
					//defaultDate: yesterday,
					//maxDate: yesterday,
					locale: 'ja',
					sideBySide: true,
					useCurrent: false,
					// daysOfWeekDisabled:[0,6]
				});
				this.ui.datepicker.on('dp.change', function () {
					$(this).data('DateTimePicker').hide();
					//$(this).find('input').trigger('input');
				});

				// 着用者情報
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WOC0010;
				var cond = {
					"scr": 'その他交換-着用者情報',
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

						// トラン情報にその他交換がある場合は発注取消ボタンを表示
						if (res_list['order_tran_flg'] == '1' && res_list['return_tran_flg'] == '1') {
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
							"scr": 'その他交換-発注入力・送信可否チェック',
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
						that.ui.cster_emply_cd.prop('disabled',true);
						if (res_list['wearer_info'][0]) {
							if(res_list['wearer_info'][0]['cster_emply_cd']){
								that.ui.cster_emply_cd_chk.prop("checked", true);
								that.ui.cster_emply_cd.val(res_list['wearer_info'][0]['cster_emply_cd']);
								that.ui.cster_emply_cd.prop('disabled',false);
							}
							that.ui.member_name.val(res_list['wearer_info'][0]['werer_name']);
							that.ui.member_name_kana.val(res_list['wearer_info'][0]['werer_name_kana']);
							that.ui.return_date.val(res_list['wearer_info'][0]['return_date']);
							that.ui.comment.val(res_list['wearer_info'][0]['comment']);
						}
						// 性別
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
						// 性別
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
						// 契約No(固定)
						if (res_list['agreement_no_list'][0]) {
							var option1 = document.createElement('option');
							var str = res_list['agreement_no_list'][0]['rntl_cont_no'] + " " + res_list['agreement_no_list'][0]['rntl_cont_name'];
							var text1 = document.createTextNode(str);
							option1.setAttribute('value', res_list['agreement_no_list'][0]['rntl_cont_no']);
							option1.appendChild(text1);
							document.getElementById('agreement_no').appendChild(option1);
						}
						// 拠点セレクト(固定)
						if (res_list['section_list'][0]) {
							var option2 = document.createElement('option');
							var text2 = document.createTextNode(res_list['section_list'][0]['rntl_sect_name']);
							option2.setAttribute('value', res_list['section_list'][0]['rntl_sect_cd']);
							option2.appendChild(text2);
							document.getElementById('section').appendChild(option2);
						}
						// 貸与パターンセレクト(固定)
						if (res_list['job_type_list'][0]) {
							var option3 = document.createElement('option');
							var text3 = document.createTextNode(res_list['job_type_list'][0]['job_type_name']);
							option3.setAttribute('value', res_list['job_type_list'][0]['job_type_cd']);
							option3.appendChild(text3);
							document.getElementById('job_type').appendChild(option3);
						}
						// 出荷先(hidden)
						if (res_list['shipment_list'][0]) {
							var shipment = res_list['shipment_list'][0]['ship_to_cd'] + ":" + res_list['shipment_list'][0]['ship_to_brnch_cd'];
							that.ui.shipment.val(shipment);
						}
					}
				});
			},
			events: {
				'change @ui.cster_emply_cd_chk': function(e){
					if(e.target.checked){
						this.ui.cster_emply_cd.prop('disabled',false);
					}else{
						this.ui.cster_emply_cd.prop('disabled',true);
					}
				},
				// 「戻る」ボタン
				'click @ui.back': function(){
					// 検索画面の条件項目を取得
					var cond = window.sessionStorage.getItem("wearer_size_change_cond");
					window.sessionStorage.setItem("back_wearer_size_change_cond", cond);
					// 検索一覧画面へ遷移
					location.href="wearer_size_change.html";
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
						"scr": 'その他交換-発注取消-更新可否チェック',
						"log_type": '3',
						"data": data,
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
				// 「入力完了」ボタン
				'click @ui.complete': function(e){

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
						"scr": 'その他交換-入力完了-更新可否チェック',
						"log_type": '1',
						"cond": data,
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WOC0050_req";
							var data = "";
							that.onShow(res_val, type, transition, data);
						}
					});
				},
				// 「発注送信」ボタン
				'click @ui.orderSend': function(e){
					e.preventDefault();
					var that = this;

					//--画面入力項目--//
					// 着用者情報
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					var cster_emply_cd_chk = $("#cster_emply_cd_chk").prop("checked");
					var cster_emply_cd = $("input[name='cster_emply_cd']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var comment = $("#comment").val();
					var return_date = $("#return_date").val();
					var order_count = $("#order_count").val();
					var data = {
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						'cster_emply_cd_chk': cster_emply_cd_chk,
						'cster_emply_cd': cster_emply_cd,
						'member_name': member_name,
						'member_name_kana': member_name_kana,
						'sex_kbn': sex_kbn,
						'section': section,
						'job_type': job_type,
						'return_date': return_date,
						'order_count': order_count,
						'comment': comment
					}


					// 発注商品一覧
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
							//個体管理番号表示フラグがONの場合、対象、個体管理番号単位
							item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
							var Name = 'target_flg'+i;
							var individual_ctrl_no = $("input[name='individual_val"+i+"']").val();
							var individual_ctrl_no_list = individual_ctrl_no.split(",");
							var chk_num = 0;
							for (var j=0; j<item[i]["individual_cnt"]; j++) {
								var chk_val = document.getElementsByName(Name)[j].value;
								item[i]["individual_data"][j] = new Object();
								var checked = document.getElementsByName(Name)[j].checked;
								if(checked == true){
									item[i]["individual_data"][j]["target_flg"] = '1';
									//個体管理番号
									item[i]["individual_data"][j]["individual_ctrl_no"] = individual_ctrl_no_list[j];
									chk_num = chk_num + 1;
								} else {
									item[i]["individual_data"][j]["target_flg"] = '0';
									//個体管理番号
									item[i]["individual_data"][j]["individual_ctrl_no"] = individual_ctrl_no_list[j];
								}
								// 対象=trueの数（商品単位返却数）
								item[i]["individual_data"][j]["return_num"] = chk_num;
							}
						} else {
							//個体管理番号表示フラグがOFFの場合、商品単位の返却数
							item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						}
					}
					// 入力項目チェック処理
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WOC0050;
					var cond = {
						"scr": 'その他交換-発注送信',
						"mode": "check",
						"snd_kbn": '1',
						"wearer_data": data,
						"item": item
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["error_code"] == "0") {
								var msg = "発注送信を行いますが、よろしいですか？";
								if (window.confirm(msg)) {
									var data = {
										"scr": 'その他交換-発注送信-update',
										"mode": "update",
										"wearer_data": data,
										"item": item
									};

									// 入力完了画面処理へ移行
									that.triggerMethod('sendComplete', data);
								}
							} else {
								that.triggerMethod('showAlerts', res_val["error_msg"]);
								return;
							}
						}
					});



					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WOC0050;
					modelForUpdate.fetchMx({
						data: cond,
						success: function (res) {
							var res_val = res.attributes;
							if(res_val["error_code"]=='1') {
								that.triggerMethod('error_msg', res_val["error_msg"]);
							}else if(res_val["error_code"]=='2') {
								window.sessionStorage.setItem('error_msg', res_val["error_msg"]);
								window.sessionStorage.setItem('referrer', 'wearer_other_change_order_err');
								// 発注送信画面処理へ移行
								that.triggerMethod('sendComplete', data);
							}else{
								window.sessionStorage.setItem('referrer', 'wearer_other_change_order');
								// 発注送信画面処理へ移行
								that.triggerMethod('sendComplete', data);
							}
						}
					});
					var that = this;

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": 'その他交換-発注送信-更新可否チェック',
						"log_type": '1',
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
					var that = this;
					// 入力項目チェック処理
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WSC0013;
					var cond = {
						"scr": 'その他交換NGパターンチェック'
					};
					modelForUpdate.fetchMx({
						data: cond,
						success: function (res) {
							var res_val = res.attributes;
							if (res_val["error_code"] == "1") {
								that.triggerMethod('error_msg', res_val["error_msg"]);
							}else{
								if(confirm('入力を完了します。よろしいですか？')){

									var modelForUpdate = that.model;
									modelForUpdate.url = App.api.WN0017;
									if(that.ui.shipment_to.val()){
										var m_shipment_to_array = that.ui.shipment_to.val().split(',');
									}
									that.ui.section = $('#section');
									var section = $("select[name='section']").val();
									var job_types = $('#job_type').val().split(':');
									that.ui.comment = $('#comment');
									var data = {
										'reason_kbn': $("select[name='reason_kbn']").val(),
										'rntl_sect_cd': section,
										'job_type': job_types[0],
										'return_date': that.ui.return_date.val(),
										'order_count': that.ui.order_count.val(),
										'comment': that.ui.comment.val()
									};
									// 追加されるアイテム
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
										now_item[i]["now_return_num"] = $("input[name='now_return_num"+i+"']").val();
										now_item[i]["now_return_num_disable"] = $("input[name='now_return_num_disable"+i+"']").val();
									}
									var cond = {
										"scr": '貸与終了発注送信',
										"cond": data,
										"snd_kbn": '1',
										"now_item": now_item
									};
									modelForUpdate.fetchMx({
										data: cond,
										success: function (res) {
											var res_val = res.attributes;
											if(res_val["error_code"]=='1') {
												that.triggerMethod('error_msg', res_val["error_msg"]);
											}else if(res_val["error_code"]=='2') {
												window.sessionStorage.setItem('error_msg', res_val["error_msg"]);
												window.sessionStorage.setItem('referrer', 'wearer_end_order_err');
												location.href="wearer_order_complete.html";
											}else{
												window.sessionStorage.setItem('referrer', 'wearer_end_order');
												location.href="wearer_order_complete.html";
											}
										}
									});
								};
							}
						}
					});
				}
			},
			onShow: function(val, type, transition, data) {
				var that = this;
				var res_val = "";
				// 更新可否チェック結果処理
				if (type == "cm0130_res") {
					if (!val["chk_flg"]) {
						// 更新可否フラグ=更新不可の場合はアラートメッセージ表示
						alert(val["error_msg"]);
					} else {
						// エラーがない場合は各対応処理へ移行
						if (transition == "WR0021_req") {
							// 発注取消処理
							type = transition;
						} else if (transition == "WOC0050_req") {
							// 入力完了処理,発注送信処理
							type = transition;
						}
					}
				}
				// 発注取消処理
				if (type == "WR0021_req") {
					var msg = "削除しますが、よろしいですか？";
					if (window.confirm(msg)) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WR0021;
						var cond = {
							"scr": 'その他交換-発注取消',
							"data": data,
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var type = "WR0021_res";
								var res_val = res.attributes;

								if (res_val["error_code"] == "0") {
									// 発注取消完了後、検索一覧へ遷移
									$.unblockUI();
									alert('発注取消が完了しました。このまま検索画面へ移行します。');

									// 検索画面の条件項目を取得
									var cond = window.sessionStorage.getItem("wearer_other_cond");
									window.sessionStorage.setItem("back_wearer_other_cond", cond);
									// 検索一覧画面へ遷移
									location.href="wearer_other.html";
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました');
								}
							}
						});
					}
				}
				// 入力完了処理
				if (type == "WOC0050_req") {
					var that = this;

					//--画面入力項目--//
					// 着用者情報
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					var cster_emply_cd_chk = $("#cster_emply_cd_chk").prop("checked");
					var cster_emply_cd = $("input[name='cster_emply_cd']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var comment = $("#comment").val();
					var return_date = $("#return_date").val();
					var order_count = $("#order_count").val();
					var wearer_data = {
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						'cster_emply_cd_chk': cster_emply_cd_chk,
						'cster_emply_cd': cster_emply_cd,
						'member_name': member_name,
						'member_name_kana': member_name_kana,
						'sex_kbn': sex_kbn,
						'section': section,
						'job_type': job_type,
						'return_date': return_date,
						'order_count': order_count,
						'comment': comment
					}


					// 発注商品一覧
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
							//個体管理番号表示フラグがONの場合、対象、個体管理番号単位
							item[i]["individual_cnt"] = $("input[name='individual_cnt"+i+"']").val();
							var Name = 'target_flg'+i;
							var individual_ctrl_no = $("input[name='individual_val"+i+"']").val();
							var individual_ctrl_no_list = individual_ctrl_no.split(",");
							var chk_num = 0;
							for (var j=0; j<item[i]["individual_cnt"]; j++) {
								var chk_val = document.getElementsByName(Name)[j].value;
								item[i]["individual_data"][j] = new Object();
								var checked = document.getElementsByName(Name)[j].checked;
								if(checked == true){
									item[i]["individual_data"][j]["target_flg"] = '1';
									//個体管理番号
									item[i]["individual_data"][j]["individual_ctrl_no"] = individual_ctrl_no_list[j];
									chk_num = chk_num + 1;
								} else {
									item[i]["individual_data"][j]["target_flg"] = '0';
									//個体管理番号
									item[i]["individual_data"][j]["individual_ctrl_no"] = individual_ctrl_no_list[j];
								}
								// 対象=trueの数（商品単位返却数）
								item[i]["individual_data"][j]["return_num"] = chk_num;
							}
						} else {
							//個体管理番号表示フラグがOFFの場合、商品単位の返却数
							item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						}
					}
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WOC0050;
					var data = {
							"scr": 'その他交換-入力完了',
							"wearer_data": wearer_data,
							"snd_kbn": '0',
							"mode": 'check',
							"item": item
						};
					modelForUpdate.fetchMx({
						data: data,
						success: function (res) {
							var res_val = res.attributes;
							if(res_val["error_code"]=='1') {
								that.triggerMethod('error_msg', res_val["error_msg"]);
							}else if(res_val["error_code"]=='2') {
								window.sessionStorage.setItem('error_msg', res_val["error_msg"]);
								window.sessionStorage.setItem('referrer', 'wearer_other_change_order_err');
								// 入力完了画面処理へ移行
								that.triggerMethod('inputComplete', data);
							}else{
								window.sessionStorage.setItem('referrer', 'wearer_other_change_order');
								// 入力完了画面処理へ移行
								var data = {
									"scr": 'その他交換-入力完了',
									"wearer_data": wearer_data,
									"snd_kbn": '0',
									"mode": 'input',
									"item": item
								};
								that.triggerMethod('inputComplete', data);
							}
						}
					});
                    //
					// // 入力項目チェック処理
					// var modelForUpdate = this.model;
					// modelForUpdate.url = App.api.WSC0013;
					// var cond = {
					// 	"scr": 'その他交換-入力完了-check',
					// 	"mode": "check",
					// 	"wearer_data": data,
					// 	"item": item
					// };
					// modelForUpdate.fetchMx({
					// 	data:cond,
					// 	success:function(res){
					// 		var res_val = res.attributes;
					// 		if (res_val["error_code"] == "0") {
					// 			var msg = "入力を完了しますが、よろしいですか？";
					// 			if (window.confirm(msg)) {
					// 				var data = {
					// 					"scr": 'その他交換-入力完了-update',
					// 					"mode": "update",
					// 					"wearer_data": wearer_data,
					// 					"item": item
					// 				};
                    //
					// 				// 入力完了画面処理へ移行
					// 				that.triggerMethod('inputComplete', data);
					// 			}
					// 		} else {
					// 			that.triggerMethod('showAlerts', res_val["error_msg"]);
					// 			return;
					// 		}
					// 	}
					// });
				}
				// 発注送信処理
				if (type == "WR0023_req") {
					//--画面入力項目--//
					// 着用者情報
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					var cster_emply_cd_chk = $("#cster_emply_cd_chk").prop("checked");
					var cster_emply_cd = $("input[name='cster_emply_cd']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var shipment = $("input[name='shipment']").val();
					var comment = $("#comment").val();
					var wearer_data = {
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						'cster_emply_cd_chk': cster_emply_cd_chk,
						'cster_emply_cd': cster_emply_cd,
						'member_name': member_name,
						'member_name_kana': member_name_kana,
						'sex_kbn': sex_kbn,
						'section': section,
						'job_type': job_type,
						'shipment': shipment,
						'comment': comment
					}

					// 発注商品一覧
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
							//個体管理番号表示フラグがONの場合、対象、個体管理番号単位
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
								// 対象=trueの数（商品単位返却数）
								item[i]["individual_data"][j]["return_num"] = chk_num;
							}
						} else {
							//個体管理番号表示フラグがOFFの場合、商品単位の返却数
							item[i]["return_num"] = $("input[name='return_num"+i+"']").val();
						}
					}

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0023;
					var cond = {
						"scr": 'その他交換-発注送信',
						"mode": "check",
						"wearer_data": wearer_data,
						"item": item
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["error_code"] == "0") {
								var msg = "発注送信を行いますが、よろしいですか？";
								if (window.confirm(msg)) {
									var data = {
										"scr": 'その他交換-発注送信-update',
										"mode": "update",
										"wearer_data": wearer_data,
										"item": item
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
