define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/WearerChange',
	'./SectionCondition',
	'./JobTypeCondition',
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
				"section": ".section",
				"job_type": ".job_type",
			},
			ui: {
				'agreement_no': '#agreement_no',
				'member_no': '#member_no',
				'member_name': '#member_name',
				'wearer_name_src1': '#wearer_name_src1',
				'wearer_name_src2': '#wearer_name_src2',
				'section': '#section',
				'job_type': '#job_type',
				"reset": '.reset',
				"search": '.search',
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#member_no': 'member_no',
				'#member_name': 'member_name',
				'#wearer_name_src1': 'wearer_name_src1',
				'#wearer_name_src2': 'wearer_name_src2',
				'#section': 'section',
				'#job_type': 'job_type',
				"#reset": 'reset',
				'#search': 'search',
			},
			onRender: function() {
				var that = this;

			},
			events: {
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					var agreement_no = $("select[name='agreement_no']").val();
					this.model.set('agreement_no', agreement_no);
					this.model.set('member_no', this.ui.member_no.val());
					this.model.set('member_name', this.ui.member_name.val());
					this.model.set('wearer_name_src1', this.ui.wearer_name_src1.prop('checked'));
					this.model.set('wearer_name_src2', this.ui.wearer_name_src2.prop('checked'));
					var section = $("select[name='section']").val();
					this.model.set('section', section);
					var job_type = $("select[name='job_type']").val();
					this.model.set('job_type', job_type);
					this.model.set('search', this.ui.search.val());
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}
					this.triggerMethod('click:search','order_req_no','asc');

				},
				'change @ui.agreement_no': function(){
					this.ui.agreement_no = $('#agreement_no');

					// 検索セレクトボックス連動--ここから
					var agreement_no = $("select[name='agreement_no']").val();
					var job_type = '';
					var input_item = '';

					// 拠点セレクト
					this.triggerMethod('change:section_select',agreement_no);
					// 貸与パターンセレクト
					var jobTypeConditionView = new App.Admin.Views.JobTypeCondition({
						agreement_no:agreement_no,
					});
					jobTypeConditionView.onShow();
					this.job_type.show(jobTypeConditionView);
					// セレクトボックス連動--ここまで
				},
				'change @ui.job_type': function(){
					this.ui.job_type = $('#job_type');

					// 検索セレクトボックス連動--ここから
					var agreement_no = $("select[name='agreement_no']").val();
					var job_type = $("select[name='job_type']").val();
					// セレクトボックス連動--ここまで
				},
				'change @ui.section': function(){
					this.ui.section = $('#section');
				},
			},
		});
	});
});
