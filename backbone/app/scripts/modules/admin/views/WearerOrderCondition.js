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
			},
			ui: {
				'agreement_no': '#agreement_no',
				'reason_kbn': '#reason_kbn',
				'sex_kbn': '#sex_kbn',
				'member_no': '#member_no',
				'member_name': '#member_name',
				'member_name_kana': '#member_name_kana',
				'appointment_ymd': '#appointment_ymd',
				'section': '#section',
				'job_type': '#job_type',
				'shipment': '#shipment',
				'post_number': '#post_number',
				'address': '#address',
				"back": '.back',
				"delete": '.delete',
				"complete": '.complete',
				"orderSend": '.orderSend',
				'datepicker': '.datepicker',
				'timepicker': '.timepicker',
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#reason_kbn': 'reason_kbn',
				'#sex_kbn': 'sex_kbn',
				'#member_no': 'member_no',
				'#member_name': 'member_name',
				'#member_name_kana': 'member_name_kana',
				'#appointment_ymd': 'appointment_ymd',
				'#section': 'section',
				'#job_type': 'job_type',
				'#shipment': 'shipment',
				'#post_number': 'post_number',
				'#address': 'address',
				"#delete": 'delete',
				"#complete": 'complete',
				"#orderSend": 'orderSend',
				'#datepicker': 'datepicker',
				'#timepicker': 'timepicker',
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

						var maxTime = new Date();
						maxTime.setHours(15);
						maxTime.setMinutes(59);
						maxTime.setSeconds(59);
						var minTime = new Date();
						minTime.setHours(9);
						minTime.setMinutes(0);
						that.ui.datepicker.datetimepicker({
							format: 'YYYY/MM/DD',
							//useCurrent: 'day',
							defaultDate: res_list['wearer_info'][0]['appointment_ymd'],
							//maxDate: yesterday,
							locale: 'ja',
							sideBySide:true,
							useCurrent: false,
							// daysOfWeekDisabled:[0,6]
						});
						that.ui.datepicker.on('dp.', function(){
							$(this).data('DateTimePicker').hide();
							//$(this).find('input').trigger('input');
						});
					}
				});
			},
			templateHelpers: function(res_list) {
				//console.log(res_list);
				return res_list;
			},
			events: {
				// 「戻る」ボタン
				'click @ui.back': function(){
						location.href="wearer_.html";
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
				// 「入力完了」ボタン
				'click @ui.complete': function(){
						alert('発注入力が完了しました。');
				},
				// 「発注送信」ボタン
				'click @ui.orderSend': function(){
					alert('発注送信が完了しました。');
					location.href="wearer_.html";
				},
				// 貸与パターン
				'change @ui.job_type': function(){
					var that = this;
					this.ui.job_type = $('#job_type');
					// 選択前のvalue値
					var before_vals = window.sessionStorage.getItem("job_type_sec");
					// 選択後のvalue値
					var after_vals = $("select[name='job_type']").val();
					var val = after_vals.split(':');
					var job_type = val[0];
					var sp_job_type_flg = val[1];

					if (sp_job_type_flg == "1") {
						// 特別職種フラグ有りの場合
						var msg = "社内申請手続きを踏んでいますか？";
						if (window.confirm(msg)) {
							that.triggerMethod(':job_type', job_type);
						} else {
							// キャンセルの場合は選択前の状態に戻す
							document.getElementById('job_type').value = before_vals;
						}
					} else {
						// 特別職種フラグ無しの場合
						window.sessionStorage.setItem("job_type_sec", after_vals);
						that.triggerMethod(':job_type', job_type);
					}
				},
				// 出荷先
				'change @ui.shipment': function(){
					this.ui.shipment = $('#shipment');

					var vals = $("select[name='shipment']").val();
					var val = vals.split(':');
					var ship_to_cd = val[0];
					var ship_to_brnch_cd = val[1];
					var shipmentConditionView = new App.Admin.Views.ShipmentConditionChange({
						ship_to_cd: ship_to_cd,
						ship_to_brnch_cd: ship_to_brnch_cd,
						chg_flg: '1',
					});
					//shipmentConditionView.onShow();
					this.shipment.show(shipmentConditionView);
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
			//						location.href="wearer_.html";
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
