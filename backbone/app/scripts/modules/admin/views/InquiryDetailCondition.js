define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'blockUI',
	'../controllers/InquiryDetail'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InquiryDetailCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.inquiryDetailCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
			},
			ui: {
				'corporate': '#corporate',
				'corporate_id': '#corporate_id',
				'agreement_no': '#agreement_no',
				'section': '#section',
				'interrogator_name': '#interrogator_name',
				'genre': '#genre',
				'interrogator_info': '#interrogator_info',
				'interrogator_answer': '#interrogator_answer',
				'inquiry_id': '#inquiry_id',
				"back": '.back',
				"answer": '.answer',
				"detail_back": '.detail_back',
				"update": '.update'
			},
			bindings: {
				'#corporate': 'corporate',
				'#corporate_id': 'corporate_id',
				'#agreement_no': 'agreement_no',
				'#section': 'section',
				'#interrogator_name': 'interrogator_name',
				'#genre': 'genre',
				'#interrogator_info': 'interrogator_info',
				'#interrogator_answer': 'interrogator_answer',
				'#inquiry_id': 'inquiry_id',
				"#back": 'back',
				"#answer": 'answer',
				"#detail_back": 'detail_back',
				"#update": 'update',
			},
			onRender: function() {
				var that = this;

				var index = window.sessionStorage.getItem("inquiry_id");
				var data = {
					"index": index
				};
				var modelForUpdate = that.model;
				modelForUpdate.url = App.api.CU0040;
				var cond = {
					"scr": 'お問い合わせ詳細-詳細項目',
					"log_type": '2',
					"data": data
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var res_list = res.attributes;

						// 詳細データが存在しない場合は検索一覧にリダイレクト
						if (res_list["detail_cnt"] == 0) {
							var cond = window.sessionStorage.getItem("inquiry_cond");
							window.sessionStorage.setItem("back_inquiry_cond", cond);
							$('#myModal_alert').modal('show');
							document.getElementById("alert_txt").innerHTML=App.inquiry_nothing_msg;
							location.href="inquiry.html";
						}

						// ユーザー区分による表示切り替え
						if (res_list["user_type"] != "1") {
							// 更新、回答ボタン非表示
							$('.answer').css('display', '');
						}

						// 企業名
						that.ui.corporate.val(res_list["detail_list"][0]["corporate"]);
						// 契約No
						that.ui.agreement_no.val(res_list["detail_list"][0]["agreement"]);
						// 拠点
						that.ui.section.val(res_list["detail_list"][0]["section"]);
						// お名前
						that.ui.interrogator_name.val(res_list["detail_list"][0]["interrogator_name"]);
						// ジャンル
						that.ui.genre.val(res_list["detail_list"][0]["category_name"]);
						// お問い合わせ内容
						that.ui.interrogator_info.val(res_list["detail_list"][0]["interrogator_info"]);
						// お問い合わせ回答
						that.ui.interrogator_answer.val(res_list["detail_list"][0]["interrogator_answer"]);
						$("#interrogator_answer").prop("disabled", true);
						// お問い合わせID（INDEX）
						that.ui.inquiry_id.val(res_list["detail_list"][0]["index"]);
					}
				});
			},
			events: {
				// 詳細画面-戻るボタン
				'click @ui.back': function(){
					var that = this;

					// 検索画面の条件項目を取得
					var cond = window.sessionStorage.getItem("inquiry_cond");
					window.sessionStorage.setItem("back_inquiry_cond", cond);

					// 検索一覧画面へ遷移
					location.href="inquiry.html";
				},
				// 詳細画面-回答ボタン
				'click @ui.answer': function(){
					var that = this;

					// ボタンの表示切り替え
					$('.answer').css('display', 'none');
					$('.update').css('display', '');
					$('.back').css('display', 'none');
					$('.detail_back').css('display', '');

					// 入力項目をdisabled切り替え
					$("#interrogator_answer").prop("disabled", false);
				},
				// 回答画面-戻るボタン
				'click @ui.detail_back': function(){
					var that = this;

					// ボタンの表示切り替え
					$('.answer').css('display', '');
					$('.update').css('display', 'none');
					$('.back').css('display', '');
					$('.detail_back').css('display', 'none');

					// 入力項目をdisabled切り替え
					$("#interrogator_answer").prop("disabled", true);
				},
				// 回答画面-更新ボタン
				'click @ui.update': function(){
					$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 更新処理中...</p>' });
					var that = this;

					// 入力値
					var data = {
						"index": this.ui.inquiry_id.val(),
						"interrogator_answer": this.ui.interrogator_answer.val()
					};
					//console.log(data);

					// 入力値チェック、登録処理
					var modelForUpdate = that.model;
					modelForUpdate.url = App.api.CU0041;
					var cond = {
						"scr": 'お問い合わせ詳細-入力値チェック・回答',
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
								that.triggerMethod('showAlerts', res_list["err_msg"]);
								$.unblockUI();
							} else {
								// 正常完了の場合は検索画面へ遷移
								$.unblockUI();

								// 検索画面の条件項目を取得
								var cond = window.sessionStorage.getItem("inquiry_cond");
								window.sessionStorage.setItem("back_inquiry_cond", cond);
								location.href="inquiry.html";
							}
						}
					});
				}
			}
		});
	});
});
