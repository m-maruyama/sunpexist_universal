define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/PurchaseHistory',
	'./SectionCondition',
	//'./JobTypeCondition',
	'./InputItemCondition',
	'./ItemColorCondition',
	//'./IndividualNumberCondition',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.PurchaseHistoryCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.purchaseHistoryCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"agreement_no": ".agreement_no",
				"section": ".section",
				//"job_type": ".job_type",
				"input_item": ".input_item",
				"item_color": ".item_color",
				//"individual_number": ".individual_number",
			},
			ui: {
				"agreement_no": ".agreement_no",
				"section": ".section",
				"input_item": ".input_item",
				"item_color": ".item_color",
				"reset": '.reset',
				"search": '.search',
				'datepicker': '.datepicker',
				'timepicker': '.timepicker'
			},
			bindings: {
				".agreement_no": "agreement_no",
				".section": "section",
				".input_item": "input_item",
				".item_color": "item_color",
				"#reset": 'reset',
				'#search': 'search',
				'#datepicker': 'datepicker',
				'#timepicker': 'timepicker'
			},
			onRender: function() {
				var that = this;

				var maxTime = new Date();
				maxTime.setHours(15);
				maxTime.setMinutes(59);
				maxTime.setSeconds(59);
				var minTime = new Date();
				minTime.setHours(9);
				minTime.setMinutes(0);
				this.ui.datepicker.datetimepicker({
					format: 'YYYY/MM/DD',
					//useCurrent: 'day',
					//defaultDate: yesterday,
					//maxDate: yesterday,
					locale: 'ja',
					sideBySide:true,
					useCurrent: false,
					// daysOfWeekDisabled:[0,6]
				});
				this.ui.datepicker.on('dp.change', function(){
					$(this).data('DateTimePicker').hide();
					//$(this).find('input').trigger('input');
				});

				//this.stickit();

				var options = {
					url: null,
					index: null,
					suggestFields: ['office_cd','office_name'],
					displayKey:'office_name',
					fuzziness:0,
				};
				options = $.extend({}, options);

				var url = App.api.CM0020;
				var suggestItems;

				var htmlentities = function (str) {
					if (typeof str !== 'string') {
						return str;
					}
					return str.replace(/&/g, "&amp;")
						.replace(/"/g, "&quot;")
						.replace(/</g, "&lt;")
						.replace(/>/g, "&gt;");
				};

				var suggester = new Bloodhound({
					datumTokenizer: Bloodhound.tokenizers.obj.whitespace(options.displayKey),
					queryTokenizer: Bloodhound.tokenizers.whitespace,
					remote: {
						url: url,
						prepare: function(query, settings){
							//検索文字列に怪しい文字があったら削除
							//query = query.replace(/[\?%\$\&=\-\+\'\"\*\^;:\/\[\]\{\}]/g, '');
							//検索文字列の全角数字を半角にしている。
							query = query.replace(/[０-９]/g, function(s) {
								return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
							});
							var data = {
								"text" : query,
								index: options.index,
								//type: options.type,
								suggestFields: options.suggestFields,
								fuzziness:options.fuzziness,
								size: options.limit
							};
							settings.type = 'POST';
							settings.contentType = 'application/json';
							settings.xhrFields = {
								withCredentials: false
							};
							settings.data = JSON.stringify(data);
							return settings;
						},
						//transform: options.transform,
						transform: function(items){
							suggestItems = items;
							return items;
						},
						rateLimitWait: 300//検索し出すまでの待機時間 ms
					}
					//local:[{text:'a'},{text:'b'},{text:'ab'},{text:'abc def'}]
				});
			},
			events: {
				'click @ui.search': function(e){
					e.preventDefault();

					this.triggerMethod('hideAlerts');
					var agreement_no = $("select[name='agreement_no']").val();
					this.model.set('agreement_no', agreement_no);
					this.model.set('search', this.ui.search.val());
					this.model.set('datepicker', this.ui.datepicker.val());
					this.model.set('timepicker', this.ui.timepicker.val());
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}
console.log(this.ui.datepicker.val());
					this.triggerMethod('click:search','line_no','desc');


				},
				'change @ui.agreement_no': function(){
					this.ui.agreement_no = $('#agreement_no');

					// 検索セレクトボックス連動--ここから
					var agreement_no = $("select[name='agreement_no']").val();
					var input_item = '';

					// 拠点セレクト
					this.triggerMethod('change:section_select',agreement_no);
					// 貸与パターンセレクト
					var jobTypeConditionView = new App.Admin.Views.JobTypeCondition({
						agreement_no:agreement_no,
					});
					// 商品セレクト
					var inputItemConditionView = new App.Admin.Views.InputItemCondition({
						agreement_no:agreement_no,
					});
					inputItemConditionView.onShow();
					this.input_item.show(inputItemConditionView);
					// 色セレクト
					var itemColorConditionView = new App.Admin.Views.ItemColorCondition({
						agreement_no:agreement_no,
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
						input_item:input_item,
					});
					itemColorConditionView.onShow();
					this.item_color.show(itemColorConditionView);
					// セレクトボックス連動--ここまで
				},
				'change @ui.item_color': function(){
					this.ui.item_color = $('#item_color');
				},
				'click @ui.reset': function(){
					// 検索項目リセット
					var agreement_no = $("select[name='agreement_no']").val();
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
					});
					inputItemConditionView.onShow();
					this.input_item.show(inputItemConditionView);
					// 色セレクト
					var itemColorConditionView = new App.Admin.Views.ItemColorCondition({
						agreement_no:agreement_no,
						input_item:input_item,
					});
					itemColorConditionView.onShow();
					this.item_color.show(itemColorConditionView);
				}
			},
		});
	});
});
