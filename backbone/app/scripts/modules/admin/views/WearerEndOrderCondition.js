define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/WearerEnd',
	'./SectionCondition',
	'./JobTypeCondition',
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
				'agreement_no': '.agreement_no',
				'reason_kbn': '.reason_kbn',
				'sex_kbn': '.sex_kbn',
				"section": ".section",
				"job_type": ".job_type",
				"shipment": ".shipment",
				"wearer_info": ".wearer_info",
				"section_btn" : "#section_btn",
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
				'resfl_ymd' : '#resfl_ymd',
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
				// 着用者情報（異動日、備考欄)
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WN0014;
				var cond = {
					"scr": '着用者情報',
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
						if (res_list['wearer_info'][0]) {
							that.ui.resfl_ymd.val(res_list['wearer_info'][0]['resfl_ymd']);
							that.ui.resfl_ymd.val(res_list['wearer_info'][0]['memo']);
						}
						that.ui.shipment_to.append($("<option>")
							.val(res_list['ship_to_cd']).text(res_list['cust_to_brnch_name']));

						that.ui.zip_no.val(res_list['zip_no']);
						that.ui.address.val(res_list['address1']+res_list['address2']+res_list['address3']+res_list['address4']);

						that.ui.order_count.val(res_list['order_count']);
						that.ui.back.val(res_list['param']);
						var flg = false;
						if(res_list['order_req_no']){
							flg = true;
							that.ui.delete.val(res_list['order_req_no']);
						}
						// 入力完了、発注送信ボタン表示/非表示制御
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
								//「入力完了」ボタン表示制御
								if (CM0140_res['order_input_ok_flg'] == "1" && CM0140_res['order_send_ok_flg'] == "1") {
									$('.inputButton').css('display', '');
									$('.orderSend').css('display', '');
								}
								if (CM0140_res['order_input_ok_flg'] == "0" && CM0140_res['order_send_ok_flg'] == "0") {
									$('.inputButton').css('display', 'none');
									$('.orderSend').css('display', 'none');
								}
								if (CM0140_res['order_input_ok_flg'] == "0" && CM0140_res['order_send_ok_flg'] == "1") {
									$('.inputButton').css('display', 'none');
									$('.orderSend').css('display', '');
								}
								if (CM0140_res['order_input_ok_flg'] == "1" && CM0140_res['order_send_ok_flg'] == "0") {
									$('.inputButton').css('display', '');
									$('.orderSend').css('display', 'none');
								}
								if(flg){
									that.ui.delete.css('display', '');
								}
							}
						});

					}
				});
			},
			events: {
				// 「戻る」ボタン
				'click @ui.back': function(){
					// 検索画面の条件項目を取得
					var cond = window.sessionStorage.getItem("wearer_end_cond");
					window.sessionStorage.setItem("back_wearer_end_cond", cond);
					// 検索一覧画面へ遷移
					location.href="wearer_end.html";
				},
				// 「発注取消」ボタン
				'click @ui.delete': function(){
					var that = this;
					var model = this.model;
					if(confirm("発注を削除しますが、よろしいですか？")){
						var cond = {
							"scr": '発注取消',
						};
						model.url = App.api.WN0018;

						model.fetchMx({
							data:cond,
							success:function(res){
								var res_val = res.attributes;
								if(res_val["error_cd"]=='1') {
									that.triggerMethod('error_msg', res_val["error_msg"]);
								}else{
									window.sessionStorage.setItem('referrer', 'wearer_end_order');
									location.href = 'wearer_end.html';
								}
							}
						});
					};

				},
				// 「入力完了」ボタン
				'click @ui.inputButton': function(e){
					e.preventDefault();
					var that = this;

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WN0017;
					if(this.ui.shipment_to.val()){
						var m_shipment_to_array = this.ui.shipment_to.val().split(',');
					}
					this.ui.section = $('#section');
					var section = $("select[name='section']").val();
					var job_types = $('#job_type').val().split(':');
					this.ui.comment = $('#comment');

					var data = {
						'reason_kbn': $("select[name='reason_kbn']").val(),
						'rntl_sect_cd': section,
						'job_type': job_types[0],
						'order_count': that.ui.order_count.val(),
						'resfl_ymd' : that.ui.resfl_ymd.val(),
						'comment': this.ui.comment.val()
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
						if (now_item[i]["individual_flg"]){
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
						"scr": '貸与終了入力完了',
						"cond": data,
						"snd_kbn": '0',
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
				},
				// 「発注送信」ボタン
				'click @ui.orderSend': function(e) {
					e.preventDefault();
					var that = this;
					// 入力項目チェック処理
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WN0019;
					var cond = {
						"scr": '貸与終了NGパターンチェック'
					};
					modelForUpdate.fetchMx({
						data: cond,
						success: function (res) {
							var res_val = res.attributes;
							if (res_val["error_code"] == "1") {
								that.triggerMethod('error_msg', res_val["error_msg"]);
							}else{
								if(confirm('発注内容を送信します。よろしいですか？')){

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
										'resfl_ymd': that.ui.resfl_ymd.val(),
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
			onShow: function() {
				$('#section_btn').addClass("disabled");

			}
		});
	});
});
