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
					location.href="wearer_change.html";
				},
				// 「発注取消」ボタン
				'click @ui.delete': function(){
					var that = this;
					var model = this.model;
					if(confirm("発注を削除しますが、よろしいですか？")){
						var cond = {
							"scr": '発注取消',
						};
						model.url = App.api.WO0015;

						model.fetchMx({
							data:cond,
							success:function(res){
								var res_val = res.attributes;
								if(res_val["error_msg"]) {
									that.triggerMethod('error_msg', res_val["error_msg"]);
								}else{
									window.sessionStorage.setItem('referrer', 'wearer_input');
									location.href = './wearer_input.html';
								}
							}
						});
					};

				},
				// // 「保存」ボタン
				'click @ui.inputButton': function(e){
					e.preventDefault();
					var that = this;

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WO0014;
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
					var cond = {
						"scr": '貸与終了入力完了',
						"cond": data,
						"snd_kbn": '0',
						"add_item": add_item
					};
					modelForUpdate.fetchMx({
						data: cond,
						success: function (res) {
							var res_val = res.attributes;
							if(res_val["error_code"]=='1') {
								that.triggerMethod('error_msg', res_val["error_msg"]);
							}else{
								window.sessionStorage.setItem('referrer', 'wearer_order_input');
								location.href="wearer_order_complete.html";
							}
						}
					});
				},
				// 「発注送信」ボタン
				'click @ui.orderSend': function(e){
					e.preventDefault();
					if(confirm('発注内容を送信します。よろしいですか？')){

						var that = this;

						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WO0014;
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
						var cond = {
							"scr": '貸与終了発注送信',
							"cond": data,
							"snd_kbn": '1',
							"add_item": add_item
						};
						modelForUpdate.fetchMx({
							data: cond,
							success: function (res) {
								var res_val = res.attributes;
								if(res_val["error_code"]=='1') {
									that.triggerMethod('error_msg', res_val["error_msg"]);
								}else{
									window.sessionStorage.setItem('referrer', 'wearer_order_send');
									location.href="wearer_order_complete.html";
								}
							}
						});
					};
				},
				'change @ui.job_type': function (e) {
					//貸与パターン」のセレクトボックス変更時に、職種マスタ．特別職種フラグ＝ありの貸与パターンだった場合、アラートメッセージを表示する。
					var job_types = $('#job_type').val().split(':');
					if (job_types[1] === '1') {
						alert('社内申請手続きを踏んでますか？');
						return;
					}
					e.preventDefault();
					this.triggerMethod('change:job_type');
				},
			},
			onShow: function() {
				$('#section_btn').addClass("disabled");

			}
		});
	});
});
