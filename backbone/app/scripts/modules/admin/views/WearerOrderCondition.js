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
				'zip_no': '#zip_no',
				'address': '#address',
				"back": '.back',
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

				// 着用者情報(着用者名、(読み仮名)、社員コード、発令日)
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
						that.ui.order_count.val(res_list['order_count']);

					}
				});
			},
			templateHelpers: function(res_list) {
				return res_list;
			},
			events: {
				// 「キャンセル」ボタン
				'click @ui.cancel': function(){
					// 検索画面以外から遷移してきた場合はホーム画面に戻る
					if(window.sessionStorage.getItem('referrer')=='wearer_search'){
						location.href = './wearer_search.html';
					}else{
						location.href = './home.html';

					}
				},
				// 「戻る」ボタン
				'click @ui.back': function(){
					if(window.sessionStorage.getItem('referrer')=='wearer_search'){
						location.href = './wearer_search.html';
					}else{
						location.href = './wearer_input.html';

					}
				},
				// 「発注取消」ボタン
				'click @ui.delete': function(){
					var that = this;

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '更新可否チェック',
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							that.onShow(res_val, type);
						}
					});
				},
				'change @ui.section': function(){
					this.ui.section = $('#section');
					var section = $("select[name='section']").val();
					// 入力完了、発注送信ボタン表示/非表示制御
					var data = {
						'rntl_sect_cd': section
					};
					var modelForUpdate = this.model;
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
							console.log(CM0140_res);
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
						}
					});




				},
				'click @ui.section_btn': function (e) {
					e.preventDefault();
					this.triggerMethod('click:section_btn', this.model);
				},
				// 「入力完了」ボタン
				'click @ui.complete': function(){
					alert('発注入力が完了しました。');
				},
				// 「発注送信」ボタン
				'click @ui.orderSend': function(){
					alert('発注送信が完了しました。');
					location.href="wearer_order_complete.html";
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
			onShow: function(val, type) {
				var that = this;

				// 更新可否チェック結果処理
				if (type == "cm0130_res") {
					if (!val["chk_flg"]) {
						// 更新可否フラグ=更新不可の場合はアラートメッセージ表示
						alert(val["error_msg"]);
					} else {
						// 発注取消処理へ移行
						var type = "WC0020_req";
						var res_val = "";
					}
				}
				// 発注取消処理
				if (type == "WC0020_req") {
					var msg = "削除しますが、よろしいですか？";
					if (window.confirm(msg)) {
						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WC0020;
						var cond = {
							"scr": '発注取消',
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var type = "WC0020_res";
								var res_val = res.attributes;

								if (res_val["error_code"] == "0") {
									alert('発注取消が完了しました。');
								} else {
									alert('発注取消中にエラーが発生しました。');
								}
							}
						});
					}
				}
			},
		});
	});
});
