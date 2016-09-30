define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/WearerChangeOrder',
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
				'member_no': '#member_no',
				'member_name': '#member_name',
				'member_name_kana': '#member_name_kana',
				'appointment_ymd': '#appointment_ymd',
				'section': '#section',
				'job_type': '#job_type',
				'shipment': '#shipment',
				'post_number': '#post_number',
				'address': '#address',
				"reset": '.reset',
				"search": '.search',
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
				"#reset": 'reset',
				'#search': 'search',
				'#datepicker': 'datepicker',
				'#timepicker': 'timepicker',
			},
			onRender: function() {
				var that = this;

				// 着用者情報(着用者名、(読み仮名)、社員コード、発令日)
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0018;
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
						//console.log(res_list['wearer_info']);
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
						that.ui.datepicker.on('dp.change', function(){
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
/*
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					var agreement_no = $("select[name='agreement_no']").val();
					this.model.set('agreement_no', agreement_no);
					var reason_kbn = $("select[name='reason_kbn']").val();
					this.model.set('reason_kbn', reason_kbn);
					var sex_kbn = $("select[name='sex_kbn']").val();
					this.model.set('sex_kbn', sex_kbn);
					this.model.set('member_no', this.ui.member_no.val());
					this.model.set('member_name', this.ui.member_name.val());
					this.model.set('wearer_name_src1', this.ui.wearer_name_src1.prop('checked'));
					this.model.set('wearer_name_src2', this.ui.wearer_name_src2.prop('checked'));
					this.model.set('appointment_ymd', this.ui.appointment_ymd.val());
					var section = $("select[name='section']").val();
					this.model.set('section', section);
					var job_type = $("select[name='job_type']").val();
					this.model.set('job_type', job_type);
					var shipment = $("select[name='shipment']").val();
					this.model.set('shipment', shipment);
					this.model.set('search', this.ui.search.val());
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}
					this.triggerMethod('click:search','order_req_no','asc');

				},
*/
				// 契約No
				'change @ui.agreement_no': function(){
					this.ui.agreement_no = $('#agreement_no');

				},
				// 拠点
				'change @ui.section': function(){
					this.ui.section = $('#section');

				},
				// 貸与パターン
				'change @ui.job_type': function(){
					var that = this;
					this.ui.job_type = $('#job_type');
					// 選択前のvalue値
					var before_vals = window.sessionStorage.getItem("job_type_sec");
					// 選択後のvalue値
					var after_vals = $("select[name='job_type']").val();
					//console.log(before_vals);
					//console.log(after_vals);
					var val = after_vals.split(':');
					var job_type = val[0];
					var sp_job_type_flg = val[1];

					if (sp_job_type_flg == "1") {
						// 特別職種フラグ有りの場合
						var msg = "社内申請手続きを踏んでいますか？";
						if (window.confirm(msg)) {
							that.triggerMethod('change:job_type', job_type);
						} else {
							// キャンセルの場合は選択前の状態に戻す
							document.getElementById('job_type').value = before_vals;
						}
					} else {
						// 特別職種フラグ無しの場合
						window.sessionStorage.setItem("job_type_sec", after_vals);
						that.triggerMethod('change:job_type', job_type);
					}
				},
				// 出荷先
				'change @ui.shipment': function(){
					this.ui.shipment = $('#shipment');

					var vals = $("select[name='shipment']").val();
					var val = vals.split(':');
					var ship_to_cd = val[0];
					var ship_to_brnch_cd = val[1];
					var shipmentConditionChangeView = new App.Admin.Views.ShipmentConditionChange({
						ship_to_cd: ship_to_cd,
						ship_to_brnch_cd: ship_to_brnch_cd,
						chg_flg: '1',
					});
					//shipmentConditionChangeView.onShow();
					this.shipment.show(shipmentConditionChangeView);
				},
			},
		});
	});
});
