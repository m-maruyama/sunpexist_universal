define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'./JobTypeZaikoCondition',
	'./ItemZaikoCondition',
	'./ItemColorZaikoCondition',
	'bloodhound'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var mode ='';
		var search_flg ='';
		Views.StockCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.stockCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"agreement_no": ".agreement_no",
				"job_type_zaiko": ".job_type_zaiko",
				"item": ".item",
				"item_color": ".item_color",
			},
			ui: {
				'agreement_no': '#agreement_no',
				'job_type_zaiko': '#job_type_zaiko',
				"item": "#item",
				"item_color": "#item_color",
				"item_size": "#item_size",
				"reset": '.reset',
				"search": '.search',
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#job_type_zaiko': 'job_type_zaiko',
				"#item": "item",
				"#item_color": "item_color",
				"#item_size": "item_size",
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
					var job_type_zaiko = $("select[name='job_type_zaiko']").val();
					this.model.set('job_type_zaiko', job_type_zaiko);
					var item = $("select[name='item']").val();
					this.model.set('item', item);
					var item_color = $("select[name='item_color']").val();
					this.model.set('item_color', item_color);
					this.model.set('item_size', this.ui.item_size.val());
					this.model.set('search', this.ui.search.val());
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
					var job_type_zaiko = '';
					var item = '';

					// 貸与パターンセレクト
					var jobTypeZaikoConditionView = new App.Admin.Views.JobTypeZaikoCondition({
						agreement_no:agreement_no,
					});
					jobTypeZaikoConditionView.onShow();
					this.job_type_zaiko.show(jobTypeZaikoConditionView);
					// 商品セレクト
					var itemZaikoConditionView = new App.Admin.Views.ItemZaikoCondition({
						agreement_no:agreement_no,
						job_type_zaiko:job_type_zaiko,
					});
					itemZaikoConditionView.onShow();
					this.item.show(itemZaikoConditionView);
					// 色セレクト
					var itemColorZaikoConditionView = new App.Admin.Views.ItemColorZaikoCondition({
						agreement_no:agreement_no,
						job_type_zaiko:job_type_zaiko,
						item:item,
					});
					itemColorZaikoConditionView.onShow();
					this.item_color.show(itemColorZaikoConditionView);
					// セレクトボックス連動--ここまで
				},
				'change @ui.job_type_zaiko': function(){
					this.ui.job_type_zaiko = $('#job_type_zaiko');

					// 検索セレクトボックス連動--ここから
					var agreement_no = $("select[name='agreement_no']").val();
					var job_type_zaiko = $("select[name='job_type_zaiko']").val();
					var item = '';

					// 商品セレクト
					var itemZaikoConditionView = new App.Admin.Views.ItemZaikoCondition({
						agreement_no:agreement_no,
						job_type_zaiko:job_type_zaiko,
					});
					itemZaikoConditionView.onShow();
					this.item.show(itemZaikoConditionView);
					// 色セレクト
					var itemColorZaikoConditionView = new App.Admin.Views.ItemColorZaikoCondition({
						agreement_no:agreement_no,
						job_type_zaiko:job_type_zaiko,
						item:item,
					});
					itemColorZaikoConditionView.onShow();
					this.item_color.show(itemColorZaikoConditionView);
					// セレクトボックス連動--ここまで
				},
				'change @ui.item': function(){
					this.ui.item = $('#item');

					// 検索セレクトボックス連動--ここから
					var agreement_no = $("select[name='agreement_no']").val();
					var job_type_zaiko = $("select[name='job_type_zaiko']").val();
					var item = $("select[name='item']").val();

					// 色セレクト
					var itemColorZaikoConditionView = new App.Admin.Views.ItemColorZaikoCondition({
						agreement_no:agreement_no,
						job_type_zaiko:job_type_zaiko,
						item:item,
					});
					itemColorZaikoConditionView.onShow();
					this.item_color.show(itemColorZaikoConditionView);
					// セレクトボックス連動--ここまで
				},
				'change @ui.item_color': function(){
					this.ui.item_color = $('#item_color');
				},
				'click @ui.reset': function(){
					// 検索項目リセット
					var agreement_no = $("select[name='agreement_no']").val();
					var job_type_zaiko = '';
					var item = '';

					// 貸与パターンセレクト
					var jobTypeZaikoConditionView = new App.Admin.Views.JobTypeZaikoCondition({
						agreement_no:agreement_no,
					});
					jobTypeZaikoConditionView.onShow();
					this.job_type_zaiko.show(jobTypeZaikoConditionView);
					// 商品セレクト
					var itemZaikoConditionView = new App.Admin.Views.ItemZaikoCondition({
						agreement_no:agreement_no,
						job_type_zaiko:job_type_zaiko,
					});
					itemZaikoConditionView.onShow();
					this.item.show(itemZaikoConditionView);
					// 色セレクト
					var itemColorZaikoConditionView = new App.Admin.Views.ItemColorZaikoCondition({
						agreement_no:agreement_no,
						job_type_zaiko:job_type_zaiko,
						item:item,
					});
					itemColorZaikoConditionView.onShow();
					this.item_color.show(itemColorZaikoConditionView);
				},
/*
				'click @ui.download': function(e){
					if(!search_flg){
						alert('実行ボタンをクリックして検索を行ってください。');
						return;
					}
					//$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
					var that = this;
					var cond = {
						"scr": '在庫照会ダウンロード',
						"page":this.options.pagerModel.getPageRequest(),
						"cond": this.model.getReq()
					};
					var form = $('<form action="' + App.api.ST0020 + '" method="post"></form>');
					var data = $('<input type="hidden" name="data" />');
					data.val(JSON.stringify(cond));
					form.append(data);
					$('body').append(form);
					form.submit();
					data.remove();
					form.remove();
					form=null;
					//$.unblockUI();
					return;
				},
*/
			}
		});
	});
});
