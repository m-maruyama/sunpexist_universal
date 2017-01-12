define([
	'app',
	'../Templates',
	'blockUI',
	'../behaviors/Alerts'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Qa = Marionette.LayoutView.extend({
			defaults: {
				data: ''
			},
			initialize: function(options) {
				this.options = options || {};
				this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.qa,
			model: new Backbone.Model(),
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"condition": ".condition",
				"qa_area": ".qa_area"
			},
			ui: {
				"qa_area": ".qa_area"
			},
			bindings: {
			},
			onRender: function() {
				var that = this;

				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				if (window.sessionStorage.getItem("back_qa_cond")) {
					var cond = window.sessionStorage.getItem("back_qa_cond");
					//window.sessionStorage.removeItem("back_qa_cond");
					var arr_str = new Array();
					arr_str = cond.split(",");
					var data = {
						'corporate': arr_str[0]
					};
					//console.log(data);
				} else {
					var data = this.options.data;
					//console.log(data);
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
			events: {
				'click @ui.updateBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:updateBtn');
				},
			}
		});
	});
});
