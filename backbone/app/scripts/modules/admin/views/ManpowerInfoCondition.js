define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/ManpowerInfo',
	'./SectionCondition',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ManpowerInfoCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.manpowerInfoCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"agreement_no": ".agreement_no",
				"section": ".section",
			},
			ui: {
				'agreement_no': '#agreement_no',
				'order_day_from': '#order_day_from',
				'order_day_to': '#order_day_to',
				'section': '#section',
				"search": '.search',
				'datepicker': '.datepicker',
				'timepicker': '.timepicker'
			},
			bindings: {
				'#agreement_no': 'agreement_no',
				'#order_day_from': 'order_day_from',
				'#order_day_to': 'order_day_to',
				'#section': 'section',
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
			},
			events: {
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					var agreement_no = $("select[name='agreement_no']").val();
					this.model.set('agreement_no', agreement_no);
					var section = $("select[name='section']").val();
					this.model.set('section', section);
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
					// セレクトボックス連動--ここまで
				},
				'change @ui.section': function(){
					this.ui.section = $('#section');
				},
			},
		});
	});
});
