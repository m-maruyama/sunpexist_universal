define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/InquiryInput',
	'./SectionCondition',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InquiryInputCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.inquiryInputCondition,
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
				'corporate_id': '#corporate_id',
				'agreement_no': '#agreement_no',
				'section': '#section',
				'interrogator_name': '#interrogator_name',
				'genre': '#genre',
				'interrogator_info': '#interrogator_info',
				"back": '.back',
				"confirm": '.confirm',
				"input_back": '.input_back',
				"complete": '.complete'
			},
			bindings: {
				'#corporate': 'corporate',
				'#corporate_id': 'corporate_id',
				'#agreement_no': 'agreement_no',
				'#section': 'section',
				'#interrogator_name': 'interrogator_name',
				'#genre': 'genre',
				'#interrogator_info': 'interrogator_info',
				"#back": 'back',
				"#confirm": 'confirm',
				"#input_back": 'input_back',
				"#complete": 'complete',
			},
			onRender: function() {
				var that = this;

				// 検索項目：企業名
				var modelForUpdate = that.model;
				modelForUpdate.url = App.api.CU0030;
				var cond = {
					"scr": 'お問い合わせ入力-入力項目',
					"log_type": '2'
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var res_list = res.attributes;

						// 企業名
						if (res_list["corporate_list"][0]) {
							var corporate_name = res_list["corporate_list"][0]["corporate_id"] + " " +res_list["corporate_list"][0]["corporate_name"];
							that.ui.corporate.val(corporate_name);
							that.ui.corporate_id.val(res_list["corporate_list"][0]["corporate_id"]);
						}
						// 契約No
						for (var i=0; i<res_list['agreement_no_list'].length; i++) {
							var option = document.createElement('option');
							var str = res_list['agreement_no_list'][i]['rntl_cont_no'] + ' ' + res_list['agreement_no_list'][i]['rntl_cont_name'];
							var text = document.createTextNode(str);
							option.setAttribute('value', res_list['agreement_no_list'][i]['rntl_cont_no']);
							option.appendChild(text);
							document.getElementById('agreement_no').appendChild(option);
						}
						// ジャンル
						for (var i=0; i<res_list['genre_list'].length; i++) {
							var option = document.createElement('option');
							var text = document.createTextNode(res_list['genre_list'][i]['gen_name']);
							option.setAttribute('value', res_list['genre_list'][i]['gen_cd']);
							option.appendChild(text);
							document.getElementById('genre').appendChild(option);
						}
					}
				});
			},
			events: {
				// 入力画面-戻るボタン
				'click @ui.back': function(){
					var that = this;

					// 検索画面の条件項目を取得
					var cond = window.sessionStorage.getItem("inquiry_cond");
					window.sessionStorage.setItem("back_inquiry_cond", cond);

					// 検索一覧画面へ遷移
					location.href="inquiry.html";
				},
				// 入力画面-確認ボタン
				'click @ui.confirm': function(){
					var that = this;

					// 説明文、ボタンの表示切り替え
					$('.input_ex').css('display', 'none');
					$('.confirm_ex').css('display', '');
					$('.confirm').css('display', 'none');
					$('.back').css('display', 'none');
					$('.complete').css('display', '');
					$('.input_back').css('display', '');

					// 入力項目をdisabled設定
					$("#agreement_no").prop("disabled", true);
					$("#section").prop("disabled", true);
					$("#interrogator_name").prop("disabled", true);
					$("#genre").prop("disabled", true);
					$("#interrogator_info").prop("disabled", true);
				},
				// 確認画面-戻るボタン
				'click @ui.input_back': function(){
					var that = this;

					// 説明文、ボタンの表示切り替え
					$('.input_ex').css('display', '');
					$('.confirm_ex').css('display', 'none');
					$('.confirm').css('display', '');
					$('.back').css('display', '');
					$('.complete').css('display', 'none');
					$('.input_back').css('display', 'none');

					// 入力項目をdisabled解除
					$("#agreement_no").prop("disabled", false);
					$("#section").prop("disabled", false);
					$("#interrogator_name").prop("disabled", false);
					$("#genre").prop("disabled", false);
					$("#interrogator_info").prop("disabled", false);
				},
				// 確認画面-OKボタン
				'click @ui.complete': function(){
					$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 登録処理中...</p>' });

					var that = this;

					// 入力値
					var data = {
						corporate: this.ui.corporate_id.val(),
						agreement_no: this.ui.agreement_no.val(),
						section: $("select[name='section']").val(),
						interrogator_name: this.ui.interrogator_name.val(),
						genre: this.ui.genre.val(),
						interrogator_info: this.ui.interrogator_info.val()
					};
					//console.log(data);

					// 入力値チェック、登録処理
					var modelForUpdate = that.model;
					modelForUpdate.url = App.api.CU0031;
					var cond = {
						"scr": 'お問い合わせ入力-入力値チェック・登録',
						"log_type": '3',
						"data": data,
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_list = res.attributes;
							//console.log(res_list);
							if (res_list["err_code"] != "0") {
								// エラーがあった場合はエラーメッセージを表示
								$('.input_ex').css('display', '');
								$('.confirm_ex').css('display', 'none');
								$('.confirm').css('display', '');
								$('.back').css('display', '');
								$('.complete').css('display', 'none');
								$('.input_back').css('display', 'none');

								$("#agreement_no").prop("disabled", false);
								$("#section").prop("disabled", false);
								$("#interrogator_name").prop("disabled", false);
								$("#genre").prop("disabled", false);
								$("#interrogator_info").prop("disabled", false);

								that.triggerMethod('showAlerts', res_list["err_msg"]);
								$.unblockUI();
							} else {
								// 正常完了の場合は検索画面へ遷移
								$.unblockUI();
								alert("登録が完了しました。このまま検索画面へ移行します。");
								location.href="inquiry.html";
							}
						}
					});
				},
				// 契約No
				'change @ui.agreement_no': function(){
					var that = this;

					this.ui.agreement_no = $('#agreement_no');
					var agreement_no = $("select[name='agreement_no']").val();
					// 拠点セレクト変更
					this.triggerMethod('change:section_select',agreement_no);
				}
			},
		});
	});
});
