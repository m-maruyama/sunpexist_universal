define([
	'app',
	'./Abstract',
	'../views/WearerExchangeOrder',
	'../views/WearerExchangeOrderCondition',
	'../views/WearerExchangeOrderListList',
	'../views/WearerExchangeOrderComplete',
	'../views/WearerExchangeOrderSendComplete',
	'../views/Pagination',
  '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminWearerExchangeOrder",
	"entities/models/AdminWearerExchangeOrderListCondition",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerExchangeOrder = App.Admin.Controllers.Abstract.extend({
			behaviors: {
					"Alerts": {
							behaviorClass: App.Admin.Behaviors.Alerts
					}
			},
			_sync : function(){
				var that = this;
				this.setNav('wearerExchangeOrder');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var wearerExchangeOrderModel = new App.Entities.Models.AdminWearerExchangeOrder();
				var wearerExchangeOrderView = new App.Admin.Views.WearerExchangeOrder({
					model:wearerExchangeOrderModel
				});

				var wearerExchangeOrderListConditionModel = new App.Entities.Models.AdminWearerExchangeOrderListCondition();
				var wearerExchangeOrderConditionView = new App.Admin.Views.WearerExchangeOrderCondition({
					model:wearerExchangeOrderListConditionModel
				});
				var wearerExchangeOrderListListView = new App.Admin.Views.WearerExchangeOrderListList();

				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});

				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});

				// 入力完了
				this.listenTo(wearerExchangeOrderConditionView, 'inputComplete', function(data){
					wearerExchangeOrderView.condition.reset();
					wearerExchangeOrderView.listTable.reset();

					var wearerExchangeOrderComplete = new App.Admin.Views.WearerExchangeOrderComplete({
						data: data,
					});
					 wearerExchangeOrderView.complete.show(wearerExchangeOrderComplete);
				});
				// 発注送信完了
				this.listenTo(wearerExchangeOrderConditionView, 'sendComplete', function(data){
					wearerExchangeOrderView.condition.reset();
					wearerExchangeOrderView.listTable.reset();

					var wearerExchangeOrderSendComplete = new App.Admin.Views.WearerExchangeOrderSendComplete({
						data: data,
					});
					wearerExchangeOrderView.complete.show(wearerExchangeOrderSendComplete);
				});

				App.main.show(wearerExchangeOrderView);
				wearerExchangeOrderView.condition.show(wearerExchangeOrderConditionView);
				wearerExchangeOrderView.listTable.show(wearerExchangeOrderListListView);
			}
		});
	});

	return App.Admin.Controllers.WearerExchangeOrder;
});
