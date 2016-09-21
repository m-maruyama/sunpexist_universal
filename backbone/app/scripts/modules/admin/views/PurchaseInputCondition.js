define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'./SectionPurchaseCondition',
	'./SectionCondition',

], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var mode ='';
		var search_flg ='';
		Views.PurchaseInputCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.purchaseInputCondition,

			regions: {
				"agreement_no": ".agreement_no",
				"section": ".section",
				"individual_number": ".individual_number",
			},
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'agreement_no': '#agreement_no',
				'section': '#section',
				"search": '.search'
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#section': 'section',
				'#search': 'search'
			},

			onShow:   function() {



			},
			onRender: function() {
					var that = this;





					},
			events: {
				//'click @ui.search': function(e){
				//	e.preventDefault();
				//	this.triggerMethod('hideAlerts');
				//	var agreement_no = $("select[name='agreement_no']").val();

				//	this.model.set('agreement_no', agreement_no);
				//	this.model.set('search', this.ui.search.val());
				//	var errors = this.model.validate();
				//	if(errors) {
				//		this.triggerMethod('showAlerts', errors);
				//		return;
				//	}
				//	search_flg = 'on';
				//	this.triggerMethod('click:search',this.model.get('sort_key'),this.model.get('account'));
				//},
				'change @ui.agreement_no': function(){

					$("#total_price").text('0');
					this.ui.agreement_no = $('#agreement_no');
					var agreement_no = $("select[name='agreement_no']").val();
					this.model.set('agreement_no', agreement_no);
					// 検索セレクトボックス連動--ここから


					// 拠点セレクト
					this.triggerMethod('change:section_select',agreement_no);

					var individualNumberConditionView = new App.Admin.Views.IndividualNumberCondition({
						agreement_no:agreement_no,
					});

					individualNumberConditionView.onShow();
					// セレクトボックス連動--ここまで


					$(".quantity").val(0);
					//契約noを変更したタイミングで数量に0を設定

					//$(".table tbody tr").css('display' , 'none');
					//$(".table tbody ."+agreement_no).css('display' , 'table-row');







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
					"scr": '貸与リストダウンロード',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": this.model.getReq()
				};
				var form = $('<form action="' + App.api.LE0020 + '" method="post"></form>');
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
		$('#agreement_no').ready(function(){
			var agreement_no = $("select[name='agreement_no']").val();
		});
	});
});
