define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'blockUI',
	'../controllers/WearerEditOrder',
	'./SexKbnConditionChange',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerEditOrderCondition = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerEditOrderCondition,
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
				'member_no': '#member_no',
				'member_name': '#member_name',
				'member_name_kana': '#member_name_kana',
				'resfl_ymd': '#resfl_ymd',
				'section': '#section',
				'job_type': '#job_type',
				'shipment': '#shipment',
				'zip_no': '#zip_no',
				'address': '#address',
				"back": '.back',
				"delete": '.delete',
				"complete": '.complete',
				"orderSend": '.orderSend'
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#reason_kbn': 'reason_kbn',
				'#sex_kbn': 'sex_kbn',
				'#member_no': 'member_no',
				'#member_name': 'member_name',
				'#member_name_kana': 'member_name_kana',
				'#resfl_ymd': 'resfl_ymd',
				'#section': 'section',
				'#job_type': 'job_type',
				'#shipment': 'shipment',
				'#zip_no': 'zip_no',
				'#address': 'address',
				"#delete": 'delete',
				"#complete": 'complete',
				"#orderSend": 'orderSend'
			},
			onRender: function() {
				var that = this;

				// 着用者情報
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WU0012;
				var cond = {
					"scr": '着用者編集-着用者情報',
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
							+ res_list['werer_cd']
						;
						that.ui.delete.val(delete_param);

						// 着用者基本マスタトラン(着用者編集)がある場合は発注取消ボタンを表示
						if (res_list['tran_flg'] == '1') {
							$('.delete').css('display', '');
						}

						// 入力完了、発注送信ボタン表示/非表示制御
						var data = {
							'rntl_sect_cd': res_list['rntl_sect_cd']
						}
						var modelForUpdate2 = that.model;
						modelForUpdate2.url = App.api.CM0140;
						var cond = {
							"scr": '着用者編集-発注入力・送信可否チェック',
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
						// 社員コード、着用者名、読みかな、異動日
						if (res_list['wearer_info'][0]) {
							that.ui.member_no.val(res_list['wearer_info'][0]['cster_emply_cd']);
							that.ui.member_name.val(res_list['wearer_info'][0]['werer_name']);
							that.ui.member_name_kana.val(res_list['wearer_info'][0]['werer_name_kana']);
							that.ui.resfl_ymd.val(res_list['wearer_info'][0]['resfl_ymd']);
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
							var text1 = document.createTextNode(res_list['agreement_no_list'][0]['rntl_cont_name']);
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
						// 出荷先セレクト(固定)、郵便番号、住所
						if (res_list['shipment_list'][0]) {
							var shipment = res_list['shipment_list'][0]['ship_to_cd'] + ":" + res_list['shipment_list'][0]['ship_to_brnch_cd'];
							var shipment_name = res_list['shipment_list'][0]['cust_to_brnch_name1'] + res_list['shipment_list'][0]['cust_to_brnch_name2'];
							var option4 = document.createElement('option');
							var text4 = document.createTextNode(shipment_name);
							option4.setAttribute('value', shipment);
							option4.appendChild(text4);
							document.getElementById('shipment').appendChild(option4);
							that.ui.zip_no.val(res_list['shipment_list'][0]['zip_no']);
							that.ui.address.val(res_list['shipment_list'][0]['address']);
						}
					}
				});
			},
			events: {
				// 「戻る」ボタン
				'click @ui.back': function(){
					// 検索画面の条件項目を取得
					var cond = window.sessionStorage.getItem("wearer_edit_cond");
					window.sessionStorage.setItem("back_wearer_edit_cond", cond);
					// 検索一覧画面へ遷移
					location.href="wearer_edit.html";
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
					var data = {
						"werer_cd": werer_cd,
						"rntl_cont_no": rntl_cont_no,
						"rntl_sect_cd": rntl_sect_cd,
						"job_type_cd": job_type_cd
					};

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '着用者編集-発注取消-更新可否チェック',
						"log_type": '3',
						"data": data,
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							var type = "cm0130_res";
							var transition = "WU0013_req";
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
						"scr": '着用者編集-入力完了-更新可否チェック',
						"log_type": '1',
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WU0014_req";
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
						"scr": '着用者編集-発注送信-更新可否チェック',
						"log_type": '1',
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							var transition = "WU0015_req";
							var data = "";
							that.onShow(res_val, type, transition, data);
						}
					});
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
						if (transition == "WU0013_req") {
							// 発注取消処理
							var type = transition;
							var res_val = "";
						} else if (transition == "WU0014_req") {
							// 入力完了処理
							var type = transition;
							var res_val = "";
						} else if (transition == "WU0015_req") {
							// 発注送信処理
							var type = transition;
							var res_val = "";
						}
					}
				}
				// 発注取消処理
				if (type == "WU0013_req") {
					var msg = "削除しますが、よろしいですか？";
					if (window.confirm(msg)) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WU0013;
						var cond = {
							"scr": '着用者編集-発注取消',
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var type = "WU0013_res";
								var res_val = res.attributes;

								if (res_val["error_code"] == "0") {
									// 発注取消完了後、検索一覧へ遷移
									$.unblockUI();
									alert('発注取消が完了しました。');

									// 検索画面の条件項目を取得
									var cond = window.sessionStorage.getItem("wearer_edit_cond");
									window.sessionStorage.setItem("back_wearer_edit_cond", cond);
									// 検索一覧画面へ遷移
									location.href="wearer_edit.html";
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました');
								}
							}
						});
					}
				}
				// 入力完了処理
				if (type == "WU0014_req") {
					//--画面入力項目--//
					// 着用者情報
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					var emply_cd_flg = $("#emply_cd_flg").prop("checked");
					var member_no = $("input[name='member_no']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var resfl_ymd = $("input[name='resfl_ymd']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var shipment = $("select[name='shipment']").val();
					var wearer_data = {
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						'emply_cd_flg': emply_cd_flg,
						'member_no': member_no,
						'member_name': member_name,
						'member_name_kana': member_name_kana,
						'sex_kbn': sex_kbn,
						'resfl_ymd': resfl_ymd,
						'section': section,
						'job_type': job_type,
						'shipment': shipment
					}

					// 入力項目チェック処理
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WU0014;
					var cond = {
						"scr": '着用者編集-入力完了-チェック',
						"mode": "check",
						"wearer_data": wearer_data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["error_code"] == "0") {
								var msg = "入力を完了しますが、よろしいですか？";
								if (window.confirm(msg)) {
									var data = {
										"scr": '着用者編集-入力完了-登録・更新',
										"mode": "update",
										"wearer_data": wearer_data
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
				if (type == "WU0015_req") {
					//--画面入力項目--//
					// 着用者情報
					var agreement_no = $("select[name='agreement_no']").val();
					var reason_kbn = $("select[name='reason_kbn']").val();
					var emply_cd_flg = $("#emply_cd_flg").prop("checked");
					var member_no = $("input[name='member_no']").val();
					var member_name = $("input[name='member_name']").val();
					var member_name_kana = $("input[name='member_name_kana']").val();
					var sex_kbn = $("select[name='sex_kbn']").val();
					var resfl_ymd = $("input[name='resfl_ymd']").val();
					var section = $("select[name='section']").val();
					var job_type = $("select[name='job_type']").val();
					var shipment = $("select[name='shipment']").val();
					var wearer_data = {
						'agreement_no': agreement_no,
						'reason_kbn': reason_kbn,
						'emply_cd_flg': emply_cd_flg,
						'member_no': member_no,
						'member_name': member_name,
						'member_name_kana': member_name_kana,
						'sex_kbn': sex_kbn,
						'resfl_ymd': resfl_ymd,
						'section': section,
						'job_type': job_type,
						'shipment': shipment
					}

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WU0015;
					var cond = {
						"scr": '着用者編集-発注送信-チェック',
						"mode": "check",
						"wearer_data": wearer_data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["error_code"] == "0") {
								var msg = "発注送信を行いますが、よろしいですか？";
								if (window.confirm(msg)) {
									var data = {
										"scr": '着用者編集-発注送信-登録・更新',
										"mode": "update",
										"wearer_data": wearer_data
									};
									//console.log(data);
									// 発注送信画面処理へ移行
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
