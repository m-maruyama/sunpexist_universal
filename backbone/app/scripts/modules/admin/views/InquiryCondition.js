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

				// 前検索結果状態を表示
				if (window.sessionStorage.getItem("back_inquiry_cond")) {
					this.triggerMethod('hideAlerts');
					var cond = window.sessionStorage.getItem("back_inquiry_cond");
					window.sessionStorage.removeItem("back_inquiry_cond");
					var arr_str = new Array();
					arr_str = cond.split(",");
					var data = {
						'corporate': arr_str[0],
						'agreement_no': arr_str[1],
						'answer_kbn0': arr_str[2],
						'answer_kbn1': arr_str[3],
						'contact_day_from': arr_str[4],
						'contact_day_to': arr_str[5],
						'answer_day_from': arr_str[6],
						'answer_day_to': arr_str[7],
						'section': arr_str[8],
						'interrogator_name': arr_str[9],
						'genre': arr_str[10],
						'interrogator_info': arr_str[11],
						'page': arr_str[12]
					};
					//console.log(data);

					// 拠点
					that.triggerMethod('research:section', data);

					//--検索結果一覧--//
					that.model.set('corporate', data["corporate"]);
					that.model.set('agreement_no', data["agreement_no"]);
					if (data["answer_kbn0"] == "true") {
						that.model.set('answer_kbn0', true);
					} else {
						that.model.set('answer_kbn0', false);
					}
					if (data["answer_kbn1"] == "true") {
						that.model.set('answer_kbn1', true);
					} else {
						that.model.set('answer_kbn1', false);
					}
					that.model.set('contact_day_from', data["contact_day_from"]);
					that.model.set('contact_day_to', data["contact_day_to"]);
					that.model.set('answer_day_from', data["answer_day_from"]);
					that.model.set('answer_day_to', data["answer_day_to"]);
					that.model.set('section', data["section"]);
					that.model.set('interrogator_name', data["interrogator_name"]);
					that.model.set('genre', data["genre"]);
					that.model.set('interrogator_info', data["interrogator_info"]);
					var page = data["page"];

					if (page != "") {
						that.triggerMethod('back:research', 'order_req_no', 'asc', page);
					}
				} else {
					// 未検索時の遷移時の場合はデフォルト値を設定
					var data = ""
					that.ui.answer_kbn0.prop("checked", true);
					that.ui.answer_kbn1.prop("checked", true);
					that.triggerMethod('first:section');
				}

				// 検索項目：企業名
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
								if (res_list['corporate_list'][i]['selected'] != "") {
									option.setAttribute('selected', res_list['corporate_list'][i]['selected']);
								}
								option.appendChild(text);
								document.getElementById('corporate').appendChild(option);
							}
						}
						// 新規お問い合わせボタン表示/非表示
						if (res_list["user_type"] == "0") {
							$('.newInput').css('display', '');
						}
					}
				});
				// 検索項目：契約No
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
							if (res_list['agreement_no_list'][i]['selected'] != "") {
								option.setAttribute('selected', res_list['agreement_no_list'][i]['selected']);
							}
							option.appendChild(text);
							document.getElementById('agreement_no').appendChild(option);
						}
					}
				});
				// 回答状況
				if (data != "") {
					if (data["answer_kbn0"] == "true") {
						that.ui.answer_kbn0.prop("checked", true);
					} else {
						that.ui.answer_kbn0.prop("checked", false);
					}
					if (data["answer_kbn1"] == "true") {
						that.ui.answer_kbn1.prop("checked", true);
					} else {
						that.ui.answer_kbn1.prop("checked", false);
					}
				}
				// お問い合わせ、回答日付
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
					//defaultDate: contact_day_from,
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
				if (data != "") {
					this.ui.contact_day_from.val(data["contact_day_from"])
					this.ui.contact_day_to.val(data["contact_day_to"])
					this.ui.answer_day_from.val(data["answer_day_from"])
					this.ui.answer_day_to.val(data["answer_day_to"])
				}

				// 検索項目：ジャンル
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
							if (res_list['genre_list'][i]['selected'] != "") {
								option.setAttribute('selected', res_list['genre_list'][i]['selected']);
							}
							option.appendChild(text);
							document.getElementById('genre').appendChild(option);
						}
					}
				});
				// お名前
				if (data != "") {
					this.ui.interrogator_name.val(data["interrogator_name"])
				}
				// お問い合わせ内容
				if (data != "") {
					this.ui.interrogator_info.val(data["interrogator_info"])
				}
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
					// 検索前後確認
					if (document.getElementsByClassName("active")[0]) {
						var page = document.getElementsByClassName("active")[0].getElementsByTagName("a")[0].text;
					} else {
						var page = '';
					}
					var cond = new Array(
						$("select[name='corporate']").val(),
						$("select[name='agreement_no']").val(),
						this.ui.answer_kbn0.prop('checked'),
						this.ui.answer_kbn1.prop('checked'),
						this.ui.contact_day_from.val(),
						this.ui.contact_day_to.val(),
						this.ui.answer_day_from.val(),
						this.ui.answer_day_to.val(),
						$("select[name='section']").val(),
						this.ui.interrogator_name.val(),
						$("select[name='genre']").val(),
						this.ui.interrogator_info.val(),
						page
					);
					var arr_str = cond.toString();

					// 検索項目値、ページ数のセッション保持
					window.sessionStorage.setItem("inquiry_cond", arr_str);
					location.href = "inquiry_input.html";
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
