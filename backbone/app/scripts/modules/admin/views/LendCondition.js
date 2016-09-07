define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'typeahead',
	'blockUI',
	'bloodhound',
	'./SectionCondition',
	'./JobTypeCondition',
	'./InputItemCondition',
	'./ItemColorCondition',
	'./IndividualNumberCondition',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var mode ='';
		var search_flg ='';
		Views.LendCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.lendCondition,
			regions: {
				"agreement_no": ".agreement_no",
				"section": ".section",
				"job_type": ".job_type",
				"input_item": ".input_item",
				"item_color": ".item_color",
				"individual_number": ".individual_number",
			},
			ui: {
				'agreement_no': '#agreement_no',
				'member_no': '#member_no',
				'member_name': '#member_name',
				'section': '#section',
				'job_type': '#job_type',
				"input_item": "#input_item",
				"item_color": "#item_color",
				"item_size": "#item_size",
				"individual_number": "#individual_number",
				"reset": '.reset',
				"search": '.search',
//				'datepicker': '.datepicker',
//				'timepicker': '.timepicker'
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#member_no': 'member_no',
				'#member_name': 'member_name',
				'#section': 'section',
				'#job_type': 'job_type',
				"#input_item": "input_item",
				"#item_color": "item_color",
				"#item_size": "item_size",
				"#individual_number": "individual_number",
				"#reset": 'reset',
				'#search': 'search',
//				'#datepicker': 'datepicker',
//				'#timepicker': 'timepicker'
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
					var section = $("select[name='section']").val();
					this.model.set('section', section);
					var job_type = $("select[name='job_type']").val();
					this.model.set('job_type', job_type);
					var input_item = $("select[name='input_item']").val();
					this.model.set('input_item', input_item);
					var item_color = $("select[name='item_color']").val();
					this.model.set('item_color', item_color);
					this.model.set('item_size', this.ui.item_size.val());
					this.model.set('individual_number', this.ui.individual_number.val());
					this.model.set('search', this.ui.search.val());
	//				this.model.set('datepicker', this.ui.datepicker.val());
	//				this.model.set('timepicker', this.ui.timepicker.val());
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}
					search_flg = 'on';
					this.triggerMethod('click:search',this.model.get('sort_key'),this.model.get('order'));
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
					// 商品セレクト
					var inputItemConditionView = new App.Admin.Views.InputItemCondition({
						agreement_no:agreement_no,
						job_type:job_type,
					});
					inputItemConditionView.onShow();
					this.input_item.show(inputItemConditionView);
					// 色セレクト
					var itemColorConditionView = new App.Admin.Views.ItemColorCondition({
						agreement_no:agreement_no,
						job_type:job_type,
						input_item:input_item,
					});
					itemColorConditionView.onShow();
					this.item_color.show(itemColorConditionView);
					// 個体管理番号
					var individualNumberConditionView = new App.Admin.Views.IndividualNumberCondition({
						agreement_no:agreement_no,
					});
					individualNumberConditionView.onShow();
					// セレクトボックス連動--ここまで
				},
				'change @ui.job_type': function(){
					this.ui.job_type = $('#job_type');

					// 検索セレクトボックス連動--ここから
					var agreement_no = $("select[name='agreement_no']").val();
					var job_type = $("select[name='job_type']").val();
					var input_item = '';

					// 商品セレクト
					var inputItemConditionView = new App.Admin.Views.InputItemCondition({
						agreement_no:agreement_no,
						job_type:job_type,
					});
					inputItemConditionView.onShow();
					this.input_item.show(inputItemConditionView);
					// 色セレクト
					var itemColorConditionView = new App.Admin.Views.ItemColorCondition({
						agreement_no:agreement_no,
						job_type:job_type,
						input_item:input_item,
					});
					itemColorConditionView.onShow();
					this.item_color.show(itemColorConditionView);
					// セレクトボックス連動--ここまで
				},
				'change @ui.section': function(){
					this.ui.section = $('#section');
				},
				'change @ui.input_item': function(){
					this.ui.input_item = $('#input_item');

					// 検索セレクトボックス連動--ここから
					var agreement_no = $("select[name='agreement_no']").val();
					var job_type = $("select[name='job_type']").val();
					var input_item = $("select[name='input_item']").val();

					// 色セレクト
					var itemColorConditionView = new App.Admin.Views.ItemColorCondition({
						agreement_no:agreement_no,
						job_type:job_type,
						input_item:input_item,
					});
					itemColorConditionView.onShow();
					this.item_color.show(itemColorConditionView);
					// セレクトボックス連動--ここまで
				},
				'change @ui.item_color': function(){
					this.ui.item_color = $('#item_color');
				},
				'change @ui.individual_number': function(){
					this.ui.individual_number = $('#individual_number');
				},
				'click @ui.reset': function(){
					// 検索項目リセット
					var agreement_no = $("select[name='agreement_no']").val();
					var job_type = '';
					var input_item = '';

					// 貸与パターンセレクト
					var jobTypeConditionView = new App.Admin.Views.JobTypeCondition({
						agreement_no:agreement_no,
					});
					jobTypeConditionView.onShow();
					this.job_type.show(jobTypeConditionView);
					// 商品セレクト
					var inputItemConditionView = new App.Admin.Views.InputItemCondition({
						agreement_no:agreement_no,
						job_type:job_type,
					});
					inputItemConditionView.onShow();
					this.input_item.show(inputItemConditionView);
					// 色セレクト
					var itemColorConditionView = new App.Admin.Views.ItemColorCondition({
						agreement_no:agreement_no,
						job_type:job_type,
						input_item:input_item,
					});
					itemColorConditionView.onShow();
					this.item_color.show(itemColorConditionView);
				}
			}
		});
	});
});
