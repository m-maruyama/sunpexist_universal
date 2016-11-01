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

				// 画面コンディション
				var data = "";
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
				// 企業名
				'change @ui.corporate': function(){
				},
				// 編集ボタン
				'click @ui.updateBtn': function(){
				}
			},
		});
	});
});
