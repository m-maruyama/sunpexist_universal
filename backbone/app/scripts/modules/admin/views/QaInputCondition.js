define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'blockUI',
	'../controllers/QaInput'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.QaInputCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.qaInputCondition,
			model: new Backbone.Model(),
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
				'case_info': '#case_info',
				'checkArea': '#checkArea',
				"back": '.back',
				"confirm": '.confirm',
				"input_back": '.input_back',
				"complete": '.complete'
			},
			bindings: {
				'#corporate': 'corporate',
				'#corporate_id': 'corporate_id',
				'#case_info': 'case_info',
				"#back": 'back',
				"#confirm": 'confirm',
				"#input_back": 'input_back',
				"#complete": 'complete'
			},
			onRender: function() {
				var that = this;
				if (window.sessionStorage.getItem("qa_cond")) {
					var cond = window.sessionStorage.getItem("qa_cond");
					var arr_str = new Array();
					arr_str = cond.split(",");
					var data = {
						'mode': "input",
						'corporate': arr_str[0]
					};
				} else {
					var data = "";
				}
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.QA0030;
				var cond = {
					"scr": 'Q&A編集-入力項目',
					"log_type": '2',
					"data": data
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var res_list = res.attributes;
						if (res_list["error_code"] == "0") {
							that.ui.corporate.val(res_list["corporate_name"]);
							that.ui.corporate_id.val(res_list["corporate_id"]);
							that.ui.case_info.val(res_list["case_info"]);
						} else {
							alert("対象の企業情報が存在しなかった為、表示できません。");
							location.href="q_and_a.html";
						}
					}
				});
			},
			events: {
				// 入力画面-戻るボタン
				'click @ui.back': function(){
					var that = this;

					// 検索画面の条件項目を取得
					var cond = window.sessionStorage.getItem("qa_cond");
					window.sessionStorage.setItem("back_qa_cond", cond);

					// 検索一覧画面へ遷移
					location.href="q_and_a.html";
				},
				// 入力画面-確認ボタン
				'click @ui.confirm': function(){
					var that = this;
					var find = $("#case_info").val();
					//find.replace((/'/g, ''));
					//console.log(find);
					$("#case_info").css('display', 'none');
					$("#checkArea").html(find);
					// 説明文、ボタンの表示切り替え
					$('.confirm').css('display', 'none');
					$('.back').css('display', 'none');
					$('.complete').css('display', '');
					$('.input_back').css('display', '');

					// 入力項目をdisabled設定
					//$("#case_info").prop("disabled", true);
				},
				// 確認画面-戻るボタン
				'click @ui.input_back': function(){
					var that = this;

					// 説明文、ボタンの表示切り替え
					$('.confirm').css('display', '');
					$('.back').css('display', '');
					$('.complete').css('display', 'none');
					$('.input_back').css('display', 'none');

					// 入力項目をdisabled解除
					$("#case_info").css('display', '');
					$("#checkArea").html('');
					//$("#case_info").prop("disabled", false);
				},
				// 確認画面-OKボタン
				'click @ui.complete': function(){
					var that = this;

					$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 更新処理中...</p>' });

					// 入力値
					var data = {
						"mode": "update",
						"corporate": this.ui.corporate_id.val(),
						"case_info": this.ui.case_info.val()
					};
					//console.log(data);

					// 入力値チェック、更新処理
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.QA0030;
					var cond = {
						"scr": 'Q&A-入力値チェック・更新',
						"log_type": '3',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_list = res.attributes;
							//console.log(res_list);
							if (res_list["error_code"] == "0") {
								// 正常完了の場合は検索画面へ遷移
								$.unblockUI();
								alert("Q&A内容の更新が完了しました。このままQ&A画面へ移行します。");

								// 検索画面の条件項目を取得
								var cond = window.sessionStorage.getItem("qa_cond");
								window.sessionStorage.setItem("back_qa_cond", cond);
								location.href="q_and_a.html";
							} else {
								// エラーがあった場合はエラーメッセージを表示
								$('.confirm').css('display', '');
								$('.back').css('display', '');
								$('.complete').css('display', 'none');
								$('.input_back').css('display', 'none');

								$("#case_info").prop("disabled", false);

								that.triggerMethod('showAlerts', res_list["error_msg"]);
								$.unblockUI();
							}
						}
					});
				}
			}
		});
	});
});
