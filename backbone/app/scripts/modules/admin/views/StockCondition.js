define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
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
				"job_type": ".job_type"
			},
			ui: {
				'job_type': '#job_type',
				'item_cd': '#item_cd',
				'color_cd': '#color_cd',
				'size': '#size',
				'zk_status_cd1': '#zk_status_cd1',
				'zk_status_cd2': '#zk_status_cd2',
				'zk_status_cd3': '#zk_status_cd3',
				"search": '.search',
				"download": '.download',
			},
			bindings: {
				'#job_type': 'job_type',
				'#item_cd': 'item_cd',
				'#color_cd': 'color_cd',
				'#size': 'size',
				'#zk_status_cd1': 'zk_status_cd1',
				'#zk_status_cd2': 'zk_status_cd2',
				'#zk_status_cd3': 'zk_status_cd3',
				'#search': 'search',
				'#download': 'download',
			},
			onRender: function() {
				var that = this;
			},
			events: {
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					this.model.set('job_type', this.ui.job_type.val());
					this.model.set('item_cd', this.ui.item_cd.val());
					this.model.set('color_cd', this.ui.color_cd.val());
					this.model.set('size', this.ui.size.val());
					this.model.set('zk_status_cd1', this.ui.zk_status_cd1.prop('checked'));
					this.model.set('zk_status_cd2', this.ui.zk_status_cd2.prop('checked'));
					this.model.set('zk_status_cd3', this.ui.zk_status_cd3.prop('checked'));
					this.model.set('sort_key', 'TSdmzk.rent_pattern_data, zkprcd, zkclor, zksize_display_order, zksize');
					this.model.set('order','asc');
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
				'change @ui.job_type': function(){
					this.ui.job_type = $('#job_type');
				}
			}
		});
	});
});