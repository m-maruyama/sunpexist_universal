define([
	'app',
	'../Templates',
	'blockUI',
	'./PurchaseInputListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var order = 'asc';
		var sort_key = 'order_req_no';
		Views.PurchaseInputListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.purchaseInputListList,
			emptyView: Backbone.Marionette.ItemView.extend({
                tagName: "tr",
				template: App.Admin.Templates.lendEmpty,
            }),
			childView: Views.PurchaseInputListItem,
			childViewContainer: "tbody",
			ui: {
			},
			onRender: function() {
				this.listenTo(this.collection, 'parsed', function(res){
					//this.options.pagerModel.set(res.page);
				});
			},
			events: {

			},
			fetch:function(purchaseInputListConditionModel){
				var cond = {
					"scr": '商品注文入力',
					//"page":this.options.pagerModel.getPageRequest(),
					"cond": purchaseInputListConditionModel.getReq()
				};
				var that = this;
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });

				this.collection.fetchMx({
					data: cond,
					success: function(model,res,req){
						//that.model.set('mode',null);
						//$.unblockUI();
					},
					complete:function(res){
						$.unblockUI();
						// 個体管理番号表示/非表示制御
						//if (res.responseJSON.individual_flag.valueOf()) {
						//	$('.tb_individual_num').css('display','');
						//}
					},


				});
			}


		});
	});
});
