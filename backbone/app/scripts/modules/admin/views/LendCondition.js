define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'typeahead',
	'blockUI',
	'bloodhound'
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
				'#search': 'search',
//				'#datepicker': 'datepicker',
//				'#timepicker': 'timepicker'
			},
			onRender: function() {
				var that = this;
/*
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
*/
			},
			events: {
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					var agreement_no = $("select[name='agreement_no']").val();
					this.model.set('agreement_no', agreement_no);
					this.model.set('member_no', this.ui.member_no.val());
					this.model.set('member_name', this.ui.member_name.val());
					this.model.set('section', this.ui.section.val());
					this.model.set('job_type', this.ui.job_type.val());
					this.model.set('input_item', this.ui.input_item.val());
					this.model.set('item_color', this.ui.item_color.val());
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
				'change @ui.agreement_no': function(){
					this.ui.agreement_no = $('#agreement_no');
				},
				'change @ui.section': function(){
					this.ui.section = $('#section');
				},
				'change @ui.job_type': function(){
					this.ui.job_type = $('#job_type');
				},
				'change @ui.input_item': function(){
					this.ui.input_item = $('#input_item');
				},
				'change @ui.item_color': function(){
					this.ui.item_color = $('#item_color');
				},
				'change @ui.individual_number': function(){
					this.ui.individual_number = $('#individual_number');
				}
			}
		});
	});
});
