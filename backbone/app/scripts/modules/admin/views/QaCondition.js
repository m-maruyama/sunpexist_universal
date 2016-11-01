define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'blockUI',
	'typeahead',
	'bloodhound',
	'../controllers/Qa'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.QaCondition = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.qaCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
			},
			ui: {
				"corporate": "#corporate",
				'updateBtn': '.update'
			},
			bindings: {
				'#corporate': 'corporate'
			},
			onRender: function() {
				var that = this;

				if (window.sessionStorage.getItem("back_qa_cond")) {
					var cond = window.sessionStorage.getItem("back_qa_cond");
					window.sessionStorage.removeItem("back_qa_cond");
					var arr_str = new Array();
					arr_str = cond.split(",");
					var data = {
						'corporate': arr_str[0]
					};
					//console.log(data);
				} else {
					var data = "";
				}
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.QA0010;
				var cond = {
					"scr": 'Q&A-画面コンディション',
					"log_type": '2',
					"data": data
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var res_list = res.attributes;
						if (res_list["user_type"] !== "1") {
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
							$('.update').css('display', '');
						}
					}
				});
			},
			events: {
				'change @ui.corporate': function() {
					$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
					var data = {
						"corporate": $("select[name='corporate']").val()
					}
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.QA0020;
					var cond = {
						"scr": 'Q&A-QA内容',
						"log_type": '2',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_list = res.attributes;
							$(".qa_area").html(res_list["case_info"]);
						}
					});
					$.unblockUI();
				},
				'click @ui.updateBtn': function() {
					var cond = new Array(
						$("select[name='corporate']").val()
					);
					var arr_str = cond.toString();

					// 検索項目値、ページ数のセッション保持
					window.sessionStorage.setItem("qa_cond", arr_str);
					location.href = "q_and_a_input.html";
				}
			},
		});
	});
});
