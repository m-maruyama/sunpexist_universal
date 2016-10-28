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

				},
			}
		});
		$('#agreement_no').ready(function(){
			var agreement_no = $("select[name='agreement_no']").val();
		});
	});
});
