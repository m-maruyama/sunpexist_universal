define([
	'app',
	'../Templates',
	'blockUI',
	'bootstrap',
	'bootstrap-datetimepicker'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.DetailModal = Marionette.ItemView.extend({
			template: App.Admin.Templates.detailModal,
			emptyView: Backbone.Marionette.ItemView.extend({
                tagName: "tr",
				template: App.Admin.Templates.deliveryEmpty,
            }),
			ui: {
				'modal': '#detail_modal'
			},
			bindings: {
			},
			onRender: function() {
			},
			events: {
			},
			fetchDetail: function(model) {
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				var that = this;
				this.model = model;
				this.model.fetchMx({
					data:this.model.getReq(),
					success: function(){
						//that.render();
						//that.ui.modal.modal('show');
						that.triggerMethod('fetched');
						$.unblockUI();
					}
				});
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
					}
					var data2 = this.receipt_status;
					if(data2){
					$.each(data2,function(index,val){
						if (val == 1) {
							retunr_str = retunr_str + "\n未受領";
						} else if (val == 2) {
							retunr_str = retunr_str + "\n受領済";
						}
					});
					}
					return retunr_str;

				},
				//よろず発注区分
				kubunText: function(){
					var data = this.order_sts_kbn;
					if (data == 1) {
						return "貸与";
					} else if (data == 2) {
						return "サイズ交換";
					} else if (data == 3) {
						return "消耗交換";
					} else if (data == 4) {
						return "異動";
					}
					//throw "invalid Data";
					return 'invalid';
				},
			}
		});
	});
});
