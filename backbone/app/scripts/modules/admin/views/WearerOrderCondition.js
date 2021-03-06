define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'./ShipmentConditionChange',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerOrderCondition = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerOrderCondition,
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
				"section_modal": ".section_modal",
			},
			ui: {
				'agreement_no': '#agreement_no',
				'reason_kbn': '#reason_kbn',
				'sex_kbn': '#sex_kbn',
				'member_no': '#member_no',
				'member_name': '#member_name',
				'member_name_kana': '#member_name_kana',
				'appointment_ymd': '#appointment_ymd',
				'shipment_to': '#shipment_to',
				'section': '#section',
				"job_type": "#job_type",
				'post_number': '#post_number',
				'comment': '#comment',
				'zip_no': '#zip_no',
				'address': '#address',
				"back": '.back',
				"cancel": '.cancel',
				"delete": '.delete',
				"complete": '.complete',
				"orderSend": '.orderSend',
				"order_count": '#order_count',
				"inputButton": '.inputButton',
				'datepicker': '.datepicker',
				'timepicker': '.timepicker',
				'm_job_type_comb_hkey': '#m_job_type_comb_hkey',
				'm_section_comb_hkey': '#m_section_comb_hkey',
			},
			onRender: function() {
				var that = this;
				// 着用者情報(着用者名、(読み仮名)、社員番号、発令日)
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WO0010;
				var cond = {
					"scr": '着用者部分情報',
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
						that.ui.member_no.val(res_list['wearer_info'][0]['cster_emply_cd']);
						that.ui.member_name.val(res_list['wearer_info'][0]['werer_name']);
						that.ui.member_name_kana.val(res_list['wearer_info'][0]['werer_name_kana']);
						that.ui.shipment_to.append($("<option>")
							.val(res_list['ship_to_cd']).text(res_list['cust_to_brnch_name']));

						that.ui.zip_no.val(res_list['zip_no']);
						that.ui.address.val(res_list['address1']+res_list['address2']+res_list['address3']+res_list['address4']);
						that.ui.member_name_kana.val(res_list['wearer_info'][0]['werer_name_kana']);
						that.ui.back.val(res_list['param']);
						that.ui.comment.val(res_list['comment']);

						// 入力完了、発注送信ボタン表示/非表示制御
						var data = {
							'rntl_cont_no': res_list['rntl_cont_no'],
							'rntl_sect_cd': res_list['rntl_sect_cd']
						};
						modelForUpdate.url = App.api.CM0140;
						var cond = {
							"scr": '貸与開始-発注入力・送信可否チェック',
							"log_type": '3',
							"data": data,
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res) {
								var CM0140_res = res.attributes;
								// ボタン表示制御
								if (CM0140_res['order_input_ok_flg'] == "1" || CM0140_res['order_send_ok_flg'] == "1") {
									$('.inputButton').css('display', '');
									$('.orderSend').css('display', '');
								}
								if (CM0140_res['order_input_ok_flg'] == "1" && CM0140_res['order_send_ok_flg'] == "0") {
									$('.inputButton').css('display', '');
									$('.orderSend').css('display', 'none');
								}
								if (CM0140_res['order_input_ok_flg'] == "0" && CM0140_res['order_send_ok_flg'] == "1") {
									$('.inputButton').css('display', '');
									$('.orderSend').css('display', '');
								}
								if (CM0140_res['order_input_ok_flg'] == "0" && CM0140_res['order_send_ok_flg'] == "0") {
									$('.inputButton').css('display', 'none');
									$('.orderSend').css('display', 'none');
								}
								//発注画面 ナビ非表示
								$('#pageNav').css('display', 'none');
							}
						});
					}
				});
			},
			templateHelpers: function(res_list) {
				return res_list;
			},
			events: {
				// 「キャンセル」ボタン
				'click @ui.cancel': function(){
					// JavaScript モーダルで表示
					$('#myModal').modal(); //追加
					//メッセージの修正
					document.getElementById("confirm_txt").innerHTML=App.cancel_msg; //追加　このメッセージはapp.jsで定義
					$("#btn_ok").off();
					$("#btn_ok").on('click',function() { //追加
						hideModal();
						// 検索画面以外から遷移してきた場合はホーム画面に戻る
						if(window.sessionStorage.getItem('wearer_input_ref')=='wearer_search'){
							//検索条件
							var cond = window.sessionStorage.getItem("wearer_search_cond");
							window.sessionStorage.setItem("back_wearer_search_cond", cond);

							location.href = './wearer_search.html';
						}else{
							location.href = './home.html';
						}
					});
				},
				// 「戻る」ボタン
				'click @ui.back': function(e) {
					e.preventDefault();
					var data = {
						'werer_name_kana': this.ui.member_name_kana.val(),
						'cster_emply_cd': this.ui.member_no.val(),
						'order_reason_kbn': $("select[name='reason_kbn']").val(),
						'comment': this.ui.comment.val()
					};

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
					modelForUpdate.url = App.api.WS0011;
					var cond = {
						"scr": '着用開始商品詳細',
						"data": data,
						"add_item": add_item,
					};
					modelForUpdate.fetchMx({
						data: cond,
						success: function (res) {
							var errors = res.get('errors');
							if (errors) {
								var errorMessages = errors.map(function (v) {
									return v.error_message;
								});
								that.triggerMethod('showAlerts', errorMessages);
							}
							window.sessionStorage.setItem('referrer', 'wearer_input');
							var res_list = res.attributes;
							var $form = $('<form/>', {'action': '/universal/wearer_input.html', 'method': 'post'});
/*
							if(res_list['m_wearer_std_comb_hkey']){
								window.sessionStorage.setItem('referrer', 'wearer_order_search');
							}else{
								window.sessionStorage.setItem('referrer', 'wearer_order');
							}
*/
							$form.appendTo(document.body);
							$form.submit();
						}
					});
				},
				// 「発注取消」ボタン
				'click @ui.delete': function(){
					var that = this;
					var model = this.model;

					var rntl_cont_no = $("select[name='agreement_no']").val();
					this.ui.section = $('#section');
					var section = $("select[name='section']").val();
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '貸与開始-発注取消-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": section,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							if(res_val.chk_flg == false){
								// JavaScript モーダルで表示
								$('#myModalAlert').modal('show'); //追加
								//メッセージの修正
								document.getElementById("alert_txt").innerHTML=res_val["error_msg"]; //追加　このメッセージはapp.jsで定義
								// alert(res_val["error_msg"]);
							}else {
								// JavaScript モーダルで表示
								$('#myModal').modal('show'); //追加
								//メッセージの修正
								document.getElementById("confirm_txt").innerHTML = App.delete_msg; //追加　このメッセージはapp.jsで定義
								$("#btn_ok").off();
								$("#btn_ok").on('click', function () { //追加
									hideModal();
									var cond = {
										"scr": '発注取消',
									};
									model.url = App.api.WO0015;

									model.fetchMx({
										data: cond,
										success: function (res) {
											var res_val = res.attributes;
											if (res_val["error_cd"] == '1') {
												that.triggerMethod('error_msg', res_val["error_msg"]);
											} else {
												window.sessionStorage.setItem('referrer', 'wearer_delete');
												location.href = './wearer_input.html';
											}
										}
									});
								});
							}
						}

					});
				},
				// 「保存」ボタン
				'click @ui.inputButton': function(e) {
					e.preventDefault();
					var that = this;

					var rntl_cont_no = $("select[name='agreement_no']").val();
					this.ui.section = $('#section');
					var section = $("select[name='section']").val();
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '貸与開始-保存（後で送信）-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": section,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							if(res_val.chk_flg == false){
								// JavaScript モーダルで表示
								$('#myModalAlert').modal('show'); //追加
								//メッセージの修正
								document.getElementById("alert_txt").innerHTML=res_val["error_msg"]; //追加　このメッセージはapp.jsで定義
								// alert(res_val["error_msg"]);

							}else{
								if (that.ui.shipment_to.val()) {
									var m_shipment_to_array = that.ui.shipment_to.val().split(',');
								}
								var job_types = $('#job_type').val().split(':');
								that.ui.comment = $('#comment');

								var data = {
									'reason_kbn': $("select[name='reason_kbn']").val(),
									'rntl_sect_cd': section,
									'job_type': job_types[0],
									'order_count': that.ui.order_count.val(),
									'm_job_type_comb_hkey': that.ui.m_job_type_comb_hkey.val(),
									'm_section_comb_hkey': that.ui.m_section_comb_hkey.val(),
									'comment': that.ui.comment.val()
								};
								// 追加されるアイテム
								var add_list_cnt = $("input[name='add_list_cnt']").val();
								var add_item = new Object();
								for (var i = 0; i < add_list_cnt; i++) {
									if(!$("input[name='add_order_num"+i+"']").val()&&!$("select[name='add_size_cd"+i+"']").val()){
										continue;
									}
									add_item[i] = new Object();
									add_item[i]["add_rntl_sect_cd"] = $("input[name='add_rntl_sect_cd" + i + "']").val();
									add_item[i]["add_job_type_cd"] = $("input[name='add_job_type_cd" + i + "']").val();
									add_item[i]["add_job_type_item_cd"] = $("input[name='add_job_type_item_cd" + i + "']").val();
									add_item[i]["add_item_cd"] = $("input[name='add_item_cd" + i + "']").val();
									add_item[i]["add_color_cd"] = $("input[name='add_color_cd" + i + "']").val();
									add_item[i]["add_choice_type"] = $("input[name='add_choice_type" + i + "']").val();
									add_item[i]["add_std_input_qty"] = $("input[name='add_std_input_qty" + i + "']").val();
									add_item[i]["add_size_cd"] = $("select[name='add_size_cd" + i + "']").val();
									add_item[i]["add_order_num"] = $("input[name='add_order_num" + i + "']").val();
									add_item[i]["add_order_num_disable"] = $("input[name='add_order_num_disable" + i + "']").val();
								}

								var cond = {
									"scr": '貸与開始-保存（後で送信）-check',
									"cond": data,
									"snd_kbn": '0',
									"add_item": add_item,
									"mode": "check",
								};
								var modelForUpdate = that.model;
								modelForUpdate.url = App.api.WO0014;
								modelForUpdate.fetchMx({
									data: cond,
									success: function (res) {
										var res_val = res.attributes;
										if (res_val["error_code"] == "0") {
											// JavaScript モーダルで表示
											$('#myModal').modal('show'); //追加
											//メッセージの修正
											document.getElementById("confirm_txt").innerHTML=App.input_insert_msg; //追加　このメッセージはapp.jsで定義
											$("#btn_ok").off();
											$("#btn_ok").on('click',function() { //追加
												hideModal();
												var cond = {
													"scr": '貸与開始-保存（後で送信）-update',
													"cond": data,
													"snd_kbn": '0',
													"add_item": add_item,
													"mode": "update",
												};
												modelForUpdate.fetchMx({
													data: cond,
													success: function (res) {
														var res_val = res.attributes;
														if (res_val["error_code"] == '1') {
															that.triggerMethod('error_msg', res_val["error_msg"]);
														} else {
															window.sessionStorage.setItem('referrer', 'wearer_order');
															window.sessionStorage.removeItem('wearer_input_ref');
															location.href = "wearer_order_complete.html";
														}
													}
												});
											});
										}else{
											that.triggerMethod('error_msg', res_val["error_msg"]);
										}
									},
								});
							}
						}
					});
                },
                // 「発注送信」ボタン
                'click @ui.orderSend': function(e){
                    e.preventDefault();
                    // if(confirm('発注内容を送信します。よろしいですか？')){

						var that = this;

					var rntl_cont_no = $("select[name='agreement_no']").val();
					this.ui.section = $('#section');
					var section = $("select[name='section']").val();
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '貸与開始-発注送信-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": section,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							if(res_val.chk_flg == false){
								// JavaScript モーダルで表示
								$('#myModalAlert').modal('show'); //追加
								//メッセージの修正
								document.getElementById("alert_txt").innerHTML=res_val["error_msg"];
							}else{
								var modelForUpdate = that.model;
								modelForUpdate.url = App.api.WO0014;
								if(that.ui.shipment_to.val()){
									var m_shipment_to_array = that.ui.shipment_to.val().split(',');
								}
								var job_types = $('#job_type').val().split(':');
								that.ui.comment = $('#comment');
								var data = {
									'reason_kbn': $("select[name='reason_kbn']").val(),
									'rntl_sect_cd': section,
									'job_type': job_types[0],
									'order_count': that.ui.order_count.val(),
									'comment': that.ui.comment.val()
								};
								// 追加されるアイテム
								var add_list_cnt = $("input[name='add_list_cnt']").val();
								var add_item = new Object();
								for (var i=0; i<add_list_cnt; i++) {
									if(!$("input[name='add_order_num"+i+"']").val()&&!$("select[name='add_size_cd"+i+"']").val()){
										continue;
									}
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

								var cond = {
									"scr": '貸与開始-発注送信-check',
									"cond": data,
									"snd_kbn": '1',
									"add_item": add_item,
									"mode": "check",
								};
								modelForUpdate.fetchMx({
									data: cond,
									success: function (res) {
										var res_val = res.attributes;
										if (res_val["error_code"] == "0") {
											// JavaScript モーダルで表示
											$('#myModal').modal(); //追加
											//メッセージの修正
											document.getElementById("confirm_txt").innerHTML=App.complete_msg; //追加　このメッセージはapp.jsで定義
											$("#btn_ok").off();
											$("#btn_ok").on('click',function() { //追加
												hideModal();
												var cond = {
													"scr": '貸与開始-発注送信-update',
													"cond": data,
													"snd_kbn": '1',
													"add_item": add_item,
													"mode": "update",
												};
												modelForUpdate.fetchMx({
													data: cond,
													success: function (res) {
														var res_val = res.attributes;
														if(res_val["error_code"]=='1') {
															that.triggerMethod('error_msg', res_val["error_msg"]);
														}else{
															window.sessionStorage.setItem('referrer', 'wearer_order_send');
															window.sessionStorage.removeItem('wearer_input_ref');
															location.href="wearer_order_complete.html";
														}
													}
												});
											});
										}else{
											that.triggerMethod('error_msg', res_val["error_msg"]);
										}
									},
								});
							}
						},
					});
                },
				onShow: function(val, type) {

				}

            },
        });
    });
});
