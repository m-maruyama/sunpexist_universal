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
			_sync : function(){


			},
			onShow:   function() {

			},


			fetch:function(purchaseInputListConditionModel){
				var cond = {
					"scr": '商品注文入力',
					//"page":this.options.pagerModel.getPageRequest(),
					"cond": purchaseInputListConditionModel.getReq()
				};
				var that = this;
				//$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });

				this.collection.fetchMx({
					data: cond,
					success: function(model,res,req){
						//that.model.set('mode',null);
						//$.unblockUI();

					},
					complete:function(res){
						///$.unblockUI();
						//数量セレクトに数量追加

						var setSelectQuantity = function()
						{
							var select = $('.quantity');
							var i = 0;
							for (i = 0; i <= 99; i = i + 1){
								var option = document.createElement('option');
								option.setAttribute('value', i);
								option.innerHTML = i;
								$('.quantity').append(option);
							}
							$('.quantity').value = 0;
						}
						setSelectQuantity();//注文入力セレクトoption生成

					},
				});
			}

		});

	});
});
