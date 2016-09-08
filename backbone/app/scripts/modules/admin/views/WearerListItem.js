define([
	'app',
	'../Templates',
	'./WearerListItem',
	"entities/models/WearerAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.wearerListItem,
			tagName: "tr",
			ui: {
				"detailLink": "a.detail"
			},
			onRender: function() {
			},
			events: {
				'click @ui.detailLink': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model);
				}
			},
			templateHelpers: {
				//ステータス
				statusText: function(){
					var data = this.order_status;
					var retunr_str = '';
					if (data == 1) {
						retunr_str = retunr_str + "未出荷";
					} else if (data == 2) {
						retunr_str = retunr_str + "出荷済";
					} else if (data == 9) {
						retunr_str = retunr_str + "キャンセル";
					}
					var data2 = this.receipt_status;
						if (data2 == 1) {
							retunr_str = retunr_str + " 未受領";
						} else if (data2 == 2) {
							retunr_str = retunr_str + " 受領済";
						}
					return retunr_str;

				},
				//よろず発注区分
				kubunText: function(){
					var data = this.order_sts_kbn;
					if (data == 1) {
						return "貸与";
					} else if (data == 3) {
						return "サイズ交換";
					} else if (data == 4) {
						return "消耗交換";
					} else if (data == 5) {
						return "異動";
					}
					//throw "invalid Data";
					return 'invalid';

				},
			}

		});
	});
});
