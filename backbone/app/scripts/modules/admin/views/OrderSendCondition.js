define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/OrderSend',
	'./SectionCondition',
	'./JobTypeCondition',
	'./SexKbnCondition',
	'./SndKbnCondition',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.OrderSendCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.orderSendCondition,
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
				'snd_kbn': '.snd_kbn',
			},
			ui: {
				'agreement_no': '#agreement_no',
				'cster_emply_cd': '#cster_emply_cd',
				'werer_name': '#werer_name',
				'section': '#section',
				'sex_kbn': '.sex_kbn',
				'snd_kbn': '.snd_kbn',
				'job_type': '#job_type',
				"reset": '.reset',
				"search": '.search',
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#cster_emply_cd': 'cster_emply_cd',
				'#member_no': 'member_no',
				'#member_name': 'member_name',
				'#section': 'section',
				'#sex_kbn': 'sex_kbn',
				'#snd_kbn': 'snd_kbn',
				'#job_type': 'job_type',
				'#search': 'search',
			},
			onRender: function() {
				var that = this;
				this.triggerMethod('first:section');
			},
			events: {
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					sessionStorage.clear();

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
					var snd_kbn = $("select[name='snd_kbn']").val();
					this.model.set('snd_kbn', snd_kbn);
					this.model.set('search', this.ui.search.val());
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}

					this.triggerMethod('click:search','order_req_no','asc',1);
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
				'change @ui.snd_kbn': function(){
					this.ui.snd_kbn = $('#snd_kbn');
				}
			},
		});
	});
});
