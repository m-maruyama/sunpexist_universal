define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/Wearer',
	'./SectionCondition',
	'./JobTypeCondition',
	'./InputItemCondition',
	'./ItemColorCondition',
	'./IndividualNumberCondition',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.wearerCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
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
				'wearer_name_src1': '#wearer_name_src1',
				'wearer_name_src2': '#wearer_name_src2',
				'section': '#section',
				'job_type': '#job_type',
				"input_item": "#input_item",
				"item_color": "#item_color",
				"item_size": "#item_size",
				"individual_number": "#individual_number",
				'wearer_kbn0': '#wearer_kbn0',
				'wearer_kbn1': '#wearer_kbn1',
				'wearer_kbn2': '#wearer_kbn2',
				'wearer_kbn3': '#wearer_kbn3',
				'wearer_kbn4': '#wearer_kbn4',
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
				"#input_item": "input_item",
				"#item_color": "item_color",
				"#item_size": "item_size",
				"#individual_number": "individual_number",
				'#wearer_kbn0': 'wearer_kbn0',
				'#wearer_kbn1': 'wearer_kbn1',
				'#wearer_kbn2': 'wearer_kbn2',
				'#wearer_kbn3': 'wearer_kbn3',
				'#wearer_kbn4': 'wearer_kbn4',
				"#reset": 'reset',
				'#search': 'search',
			},
			onRender: function() {
				var that = this;

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
					this.model.set('member_no', this.ui.member_no.val());
					this.model.set('member_name', this.ui.member_name.val());
					this.model.set('wearer_name_src1', this.ui.wearer_name_src1.prop('checked'));
					this.model.set('wearer_name_src2', this.ui.wearer_name_src2.prop('checked'));
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
					this.model.set('wearer_kbn0', this.ui.wearer_kbn0.prop('checked'));
					this.model.set('wearer_kbn1', this.ui.wearer_kbn1.prop('checked'));
					this.model.set('wearer_kbn2', this.ui.wearer_kbn2.prop('checked'));
					this.model.set('wearer_kbn3', this.ui.wearer_kbn3.prop('checked'));
					this.model.set('wearer_kbn4', this.ui.wearer_kbn4.prop('checked'));
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
			},
		});
	});
});
