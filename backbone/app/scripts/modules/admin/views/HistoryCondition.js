define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/History',
	'./SectionCondition',
	'./JobTypeCondition',
	'./InputItemCondition',
	'./ItemColorCondition',
	'./IndividualNumberCondition',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.HistoryCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.historyCondition,
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
				'no': '#no',
				'emply_order_no': '#emply_order_no',
				'member_no': '#member_no',
				'member_name': '#member_name',
				'section': '#section',
				'job_type': '#job_type',
				"input_item": "#input_item",
				"item_color": "#item_color",
				"item_size": "#item_size",
				'order_day_from': '#order_day_from',
				'order_day_to': '#order_day_to',
				'send_day_from': '#send_day_from',
				'send_day_to': '#send_day_to',
				'status0': '#status0',
				'status1': '#status1',
				'order_kbn0': '#order_kbn0',
				'order_kbn1': '#order_kbn1',
				'order_kbn2': '#order_kbn2',
				'order_kbn3': '#order_kbn3',
				'order_kbn4': '#order_kbn4',
				'reason_kbn0': '#reason_kbn0',
				'reason_kbn1': '#reason_kbn1',
				'reason_kbn2': '#reason_kbn2',
				'reason_kbn3': '#reason_kbn3',
				'reason_kbn4': '#reason_kbn4',
				'reason_kbn5': '#reason_kbn5',
				'reason_kbn6': '#reason_kbn6',
				'reason_kbn7': '#reason_kbn7',
				'reason_kbn8': '#reason_kbn8',
				'reason_kbn9': '#reason_kbn9',
				'reason_kbn10': '#reason_kbn10',
				'reason_kbn11': '#reason_kbn11',
				'reason_kbn12': '#reason_kbn12',
				'reason_kbn13': '#reason_kbn13',
				'reason_kbn14': '#reason_kbn14',
				'reason_kbn15': '#reason_kbn15',
				'reason_kbn16': '#reason_kbn16',
				'reason_kbn17': '#reason_kbn17',
				'reason_kbn18': '#reason_kbn18',
				'reason_kbn19': '#reason_kbn19',
				"individual_number": "#individual_number",
				"reset": '.reset',
				"search": '.search',
				'datepicker': '.datepicker',
				'timepicker': '.timepicker'
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#no': 'no',
				'#emply_order_no': 'emply_order_no',
				'#member_no': 'member_no',
				'#member_name': 'member_name',
				'#section': 'section',
				'#job_type': 'job_type',
				"#input_item": "input_item",
				"#item_color": "item_color",
				"#item_size": "item_size",
				'#order_day_from': 'order_day_from',
				'#order_day_to': 'order_day_to',
				'#send_day_from': 'send_day_from',
				'#send_day_to': 'send_day_to',
				'#status0': 'status0',
				'#status1': 'status1',
				'#order_kbn0': 'order_kbn0',
				'#order_kbn1': 'order_kbn1',
				'#order_kbn2': 'order_kbn2',
				'#order_kbn3': 'order_kbn3',
				'#order_kbn4': 'order_kbn4',
				'#reason_kbn0': 'reason_kbn0',
				'#reason_kbn1': 'reason_kbn1',
				'#reason_kbn2': 'reason_kbn2',
				'#reason_kbn3': 'reason_kbn3',
				'#reason_kbn4': 'reason_kbn4',
				'#reason_kbn5': 'reason_kbn5',
				'#reason_kbn6': 'reason_kbn6',
				'#reason_kbn7': 'reason_kbn7',
				'#reason_kbn8': 'reason_kbn8',
				'#reason_kbn9': 'reason_kbn9',
				'#reason_kbn10': 'reason_kbn10',
				'#reason_kbn11': 'reason_kbn11',
				'#reason_kbn12': 'reason_kbn12',
				'#reason_kbn13': 'reason_kbn13',
				'#reason_kbn14': 'reason_kbn14',
				'#reason_kbn15': 'reason_kbn15',
				'#reason_kbn16': 'reason_kbn16',
				'#reason_kbn17': 'reason_kbn17',
				'#reason_kbn18': 'reason_kbn18',
				'#reason_kbn19': 'reason_kbn19',
				"#individual_number": "individual_number",
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
					this.model.set('no', this.ui.no.val());
					this.model.set('emply_order_no', this.ui.emply_order_no.val());
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
					this.model.set('order_day_from', this.ui.order_day_from.val());
					this.model.set('order_day_to', this.ui.order_day_to.val());
					this.model.set('send_day_from', this.ui.send_day_from.val());
					this.model.set('send_day_to', this.ui.send_day_to.val());
					this.model.set('status0', this.ui.status0.prop('checked'));
					this.model.set('status1', this.ui.status1.prop('checked'));
					this.model.set('order_kbn0', this.ui.order_kbn0.prop('checked'));
					this.model.set('order_kbn1', this.ui.order_kbn1.prop('checked'));
					this.model.set('order_kbn2', this.ui.order_kbn2.prop('checked'));
					this.model.set('order_kbn3', this.ui.order_kbn3.prop('checked'));
					this.model.set('order_kbn4', this.ui.order_kbn4.prop('checked'));
					this.model.set('reason_kbn0', this.ui.reason_kbn0.prop('checked'));
					this.model.set('reason_kbn1', this.ui.reason_kbn1.prop('checked'));
					this.model.set('reason_kbn2', this.ui.reason_kbn2.prop('checked'));
					this.model.set('reason_kbn3', this.ui.reason_kbn3.prop('checked'));
					this.model.set('reason_kbn4', this.ui.reason_kbn4.prop('checked'));
					this.model.set('reason_kbn5', this.ui.reason_kbn5.prop('checked'));
					this.model.set('reason_kbn6', this.ui.reason_kbn6.prop('checked'));
					this.model.set('reason_kbn7', this.ui.reason_kbn7.prop('checked'));
					this.model.set('reason_kbn8', this.ui.reason_kbn8.prop('checked'));
					this.model.set('reason_kbn9', this.ui.reason_kbn9.prop('checked'));
					this.model.set('reason_kbn10', this.ui.reason_kbn10.prop('checked'));
					this.model.set('reason_kbn11', this.ui.reason_kbn11.prop('checked'));
					this.model.set('reason_kbn12', this.ui.reason_kbn12.prop('checked'));
					this.model.set('reason_kbn13', this.ui.reason_kbn13.prop('checked'));
					this.model.set('reason_kbn14', this.ui.reason_kbn14.prop('checked'));
					this.model.set('reason_kbn15', this.ui.reason_kbn15.prop('checked'));
					this.model.set('reason_kbn16', this.ui.reason_kbn16.prop('checked'));
					this.model.set('reason_kbn17', this.ui.reason_kbn17.prop('checked'));
					this.model.set('reason_kbn18', this.ui.reason_kbn18.prop('checked'));
					this.model.set('reason_kbn19', this.ui.reason_kbn19.prop('checked'));
					this.model.set('individual_number', this.ui.individual_number.val());
					this.model.set('search', this.ui.search.val());
					this.model.set('datepicker', this.ui.datepicker.val());
					this.model.set('timepicker', this.ui.timepicker.val());
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
