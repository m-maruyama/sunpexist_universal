define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/WearerChange',
	'./AgreementNoCondition',
	'./SectionCondition',
	'./JobTypeCondition',
	'./SexKbnCondition',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerChangeCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.wearerChangeCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"agreement_no": ".agreement_no",
				"section": ".section",
				"job_type": ".job_type",
				'sex_kbn': '.sex_kbn',
			},
			ui: {
				'agreement_no': '#agreement_no',
				'cster_emply_cd': '#cster_emply_cd',
				'werer_name': '#werer_name',
				'section': '#section',
				'sex_kbn': '.sex_kbn',
				'job_type': '#job_type',
				"search": '.search'
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#cster_emply_cd': 'cster_emply_cd',
				'#member_no': 'member_no',
				'#member_name': 'member_name',
				'#section': 'section',
				'#sex_kbn': 'sex_kbn',
				'#job_type': 'job_type',
				'#search': 'search',
			},
			onShow: function() {
				var that = this;

				// 前検索結果状態を表示
				if (window.sessionStorage.getItem("back_wearer_change_cond")) {
					this.triggerMethod('hideAlerts');
					var cond = window.sessionStorage.getItem("back_wearer_change_cond");
					window.sessionStorage.removeItem("back_wearer_change_cond");
					var arr_str = new Array();
					arr_str = cond.split(",");
					var cond_arr = {
						'agreement_no': arr_str[0],
						'cster_emply_cd': arr_str[1],
						'werer_name': arr_str[2],
						'sex_kbn': arr_str[3],
						'section': arr_str[4],
						'job_type': arr_str[5],
						'page': arr_str[6]
					};
					//--各検索項目--//
					// 契約No
					that.triggerMethod('research:agreement_no', cond_arr);
					// 社員番号
					that.ui.cster_emply_cd.val(cond_arr["cster_emply_cd"]);
					// 着用者名
					that.ui.werer_name.val(cond_arr["werer_name"]);
					// 性別
					that.triggerMethod('research:sex', cond_arr);
					// 拠点
					that.triggerMethod('research:section', cond_arr);
					// 貸与パターン
					that.triggerMethod('research:job_type', cond_arr);
					// 検索結果一覧
					that.model.set('agreement_no', cond_arr["agreement_no"]);
					that.model.set('cster_emply_cd', cond_arr["cster_emply_cd"]);
					that.model.set('werer_name', cond_arr["werer_name"]);
					that.model.set('sex_kbn', cond_arr["sex_kbn"]);
					that.model.set('section', cond_arr["section"]);
					that.model.set('job_type', cond_arr["job_type"]);
					var page = arr_str[6];
					that.triggerMethod('back:research', 'order_req_no', 'asc', page);
				} else {
					that.triggerMethod('first:section');
				}
			},
			events: {
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					var agreement_no = $("select[name='agreement_no']").val();
					this.model.set('agreement_no', agreement_no);
					this.model.set('cster_emply_cd', this.ui.cster_emply_cd.val());
					this.model.set('werer_name', this.ui.werer_name.val());
					var sex_kbn = $("select[name='sex_kbn']").val();
					this.model.set('sex_kbn', sex_kbn);
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
				'change @ui.sex_kbn': function(){
					this.ui.sex_kbn = $('#sex_kbn');
				},
			},
		});
	});
});
