define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/Inquiry',
	'./SectionCondition',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InquiryCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.inquiryCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"section": ".section",
			},
			ui: {
				'corporate': '#corporate',
				'agreement_no': '#agreement_no',
				'answer_kbn0': '#answer_kbn0',
				'answer_kbn1': '#answer_kbn1',
				'contact_day_from': '#contact_day_from',
				'contact_day_to': '#contact_day_to',
				'answer_day_from': '#answer_day_from',
				'answer_day_to': '#answer_day_to',
				'section': '#section',
				'interrogator_name': '#interrogator_name',
				'genre': '#genre',
				'interrogator_info': '#interrogator_info',
				"search": '.search',
				"newInput": '.newInput',
				'datepicker': '.datepicker',
				'timepicker': '.timepicker'
			},
			bindings: {
				'#corporate': 'corporate',
				'#agreement_no': 'agreement_no',
				'#answer_kbn0': 'answer_kbn0',
				'#answer_kbn1': 'answer_kbn1',
				'#contact_day_from': 'contact_day_from',
				'#contact_day_to': 'contact_day_to',
				'#answer_day_from': 'answer_day_from',
				'#answer_day_to': 'answer_day_to',
				'#section': 'section',
				'#interrogator_name': 'interrogator_name',
				'#genre': 'genre',
				'#interrogator_info': 'interrogator_info',
				'#search': 'search',
				"#newInput": 'newInput',
				'#datepicker': 'datepicker',
				'#timepicker': 'timepicker'
			},
			onRender: function() {
				var that = this;

				// 検索項目：企業名
				var data = ""
				var modelForUpdate = that.model;
				modelForUpdate.url = App.api.CU0010;
				var cond = {
					"scr": 'お問い合わせ一覧-企業名',
					"log_type": '2',
					"data": data,
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var res_list = res.attributes;
						// アカウントのユーザー区分=一般ユーザー以上で表示
						if (res_list["user_type"] == "1") {
							$('.corporate').css('display', '');
							for (var i=0; i<res_list['corporate_list'].length; i++) {
								var option = document.createElement('option');
								var str = res_list['corporate_list'][i]['corporate_id'] + ' ' + res_list['corporate_list'][i]['corporate_name'];
								var text = document.createTextNode(str);
								option.setAttribute('value', res_list['corporate_list'][i]['corporate_id']);
								option.appendChild(text);
								document.getElementById('corporate').appendChild(option);
							}
						}
					}
				});
				// 検索項目：契約No
				var data = ""
				var modelForUpdate = that.model;
				modelForUpdate.url = App.api.CU0011;
				var cond = {
					"scr": 'お問い合わせ一覧-契約No',
					"log_type": '2',
					"data": data,
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var res_list = res.attributes;
						for (var i=0; i<res_list['agreement_no_list'].length; i++) {
							var option = document.createElement('option');
							var str = res_list['agreement_no_list'][i]['rntl_cont_no'] + ' ' + res_list['agreement_no_list'][i]['rntl_cont_name'];
							var text = document.createTextNode(str);
							option.setAttribute('value', res_list['agreement_no_list'][i]['rntl_cont_no']);
							option.appendChild(text);
							document.getElementById('agreement_no').appendChild(option);
						}
					}
				});
				// 検索項目：ジャンル
				var data = ""
				var modelForUpdate = that.model;
				modelForUpdate.url = App.api.CU0012;
				var cond = {
					"scr": 'お問い合わせ一覧-ジャンル',
					"log_type": '2',
					"data": data,
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var res_list = res.attributes;
						for (var i=0; i<res_list['genre_list'].length; i++) {
							var option = document.createElement('option');
							var text = document.createTextNode(res_list['genre_list'][i]['gen_name']);
							option.setAttribute('value', res_list['genre_list'][i]['gen_cd']);
							option.appendChild(text);
							document.getElementById('genre').appendChild(option);
						}
					}
				});

				// お問い合わせ日付、回答日付
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
					sideBySide:true,
					useCurrent: false,
					// daysOfWeekDisabled:[0,6]
				});
				this.ui.datepicker.on('dp.change', function(){
					$(this).data('DateTimePicker').hide();
					//$(this).find('input').trigger('input');
				});
			},
			events: {
				// 検索ボタン
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					var corporate = $("select[name='corporate']").val();
					this.model.set('corporate', corporate);
					var agreement_no = $("select[name='agreement_no']").val();
					this.model.set('agreement_no', agreement_no);
					this.model.set('answer_kbn0', this.ui.answer_kbn0.prop('checked'));
					this.model.set('answer_kbn1', this.ui.answer_kbn1.prop('checked'));
					this.model.set('contact_day_from', this.ui.contact_day_from.val());
					this.model.set('contact_day_to', this.ui.contact_day_to.val());
					this.model.set('answer_day_from', this.ui.answer_day_from.val());
					this.model.set('answer_day_to', this.ui.answer_day_to.val());
					var section = $("select[name='section']").val();
					this.model.set('section', section);
					this.model.set('interrogator_name', this.ui.interrogator_name.val());
					var genre = $("select[name='genre']").val();
					this.model.set('genre', genre);
					this.model.set('interrogator_info', this.ui.interrogator_info.val());
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}

					this.triggerMethod('click:search','order_req_no','asc');
				},
				// 新規問い合わせ入力ボタン
				'click @ui.newInput': function(){
					alert("未実装！！");
				},
				'change @ui.agreement_no': function(){
					this.ui.agreement_no = $('#agreement_no');
					var agreement_no = $("select[name='agreement_no']").val();
					// 拠点セレクト変更
					this.triggerMethod('change:section_select',agreement_no);
				}
			},
		});
	});
});
