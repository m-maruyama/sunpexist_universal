define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'typeahead',
	'bloodhound'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var mode ='';
		var search_flg ='';
		Views.UnreturnedCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.unreturnedCondition,
			regions: {
				"job_type": ".job_type"
			},
			ui: {
				'no': '#no',
				'member_no': '#member_no',
				'office': '#office',
				'job_type': '#job_type',
				'order_day_from': '#order_day_from',
				'order_day_to': '#order_day_to',
				'return_day_from': '#return_day_from',
				'return_day_to': '#return_day_to',
				'status0': '#status0',
				'status1': '#status1',
				'order_kbn0': '#order_kbn0',
				'order_kbn1': '#order_kbn1',
				'order_kbn2': '#order_kbn2',
				'order_kbn3': '#order_kbn3',
				"search": '.search',
				"download": '.download',
				'datepicker': '.datepicker',
				'timepicker': '.timepicker'
			},
			bindings: {
				'#no': 'no',
				'#member_no': 'member_no',
				'#office': 'office',
				'#job_type': 'job_type',
				'#order_day_from': 'order_day_from',
				'#order_day_to': 'order_day_to',
				'#return_day_from': 'return_day_from',
				'#return_day_to': 'return_day_to',
				'#status0': 'status0',
				'#status1': 'status1',
				'#order_kbn0': 'order_kbn0',
				'#order_kbn1': 'order_kbn1',
				'#order_kbn2': 'order_kbn2',
				'#order_kbn4': 'order_kbn3',
				'#search': 'search',
				'#download': 'download',
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
					//type: null,
					suggestFields: ['office_cd','office_name'],
					displayKey:'office_name',
					fuzziness:0,
					// limit: 5
				};
				//options.transform = function(res){
				//	return res[options.type][0].options;
				//};
				options = $.extend({}, options);
		
				var url = App.api.CM0020;
				// url = './suggest.php';
				// url = options.url;
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
				var target = this.ui.office;
				target.typeahead({
					//highlight:false,
					//hint:true,
					//minLength:1
				}, {
					name: 'office',
					display: 'office_name',
					source: suggester,
					limit:options.limit
				});
				
				var $suggestRank = $('<input type="hidden" name="suggest_rank" value="" class="suggest_rank">');
				target.after($suggestRank);
				target.on('typeahead:select',function(e, item){
					$suggestRank.val(htmlentities(item.office_cd));
				});
			},
		events: {
			'click @ui.search': function(e){
				e.preventDefault();
				this.triggerMethod('hideAlerts');
				this.model.set('no', this.ui.no.val());
				this.model.set('member_no', this.ui.member_no.val());
				this.model.set('office', this.ui.office.val());
				this.model.set('job_type', this.ui.job_type.val());
				this.model.set('order_day_from', this.ui.order_day_from.val());
				this.model.set('order_day_to', this.ui.order_day_to.val());
				this.model.set('return_day_from', this.ui.return_day_from.val());
				this.model.set('return_day_to', this.ui.return_day_to.val());
				this.model.set('status0', this.ui.status0.prop('checked'));
				this.model.set('status1', this.ui.status1.prop('checked'));
				this.model.set('order_kbn0', this.ui.order_kbn0.prop('checked'));
				this.model.set('order_kbn1', this.ui.order_kbn1.prop('checked'));
				this.model.set('order_kbn2', this.ui.order_kbn2.prop('checked'));
				this.model.set('order_kbn3', this.ui.order_kbn3.prop('checked'));
				this.model.set('sort_key', 'order_req_no');
				this.model.set('order','asc');
				this.model.set('datepicker', this.ui.datepicker.val());
				this.model.set('timepicker', this.ui.timepicker.val());
				var errors = this.model.validate();
				if(errors) {
					this.triggerMethod('showAlerts', errors);
					return;
				}
				search_flg = 'on';
				this.triggerMethod('click:search',this.model.get('sort_key'),this.model.get('order'));
			},
				'click @ui.download': function(e){
					if(!search_flg){
						alert('実行ボタンをクリックして検索を行ってください。');
						return;
					}
					//$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
					var that = this;
					var cond = {
						"scr": '返却実績照会ダウンロード',
						"page":this.options.pagerModel.getPageRequest(),
						"cond": this.model.getReq()
					};
					var form = $('<form action="' + App.api.UD0020 + '" method="post"></form>');
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
			'change @ui.job_type': function(){
				this.ui.job_type = $('#job_type');
			}
		}
		});
	});
});