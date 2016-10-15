define([
	'app',
	'./Abstract',
	'../views/WearerAddOrder',
	'../views/WearerAddOrderCondition',
	'../views/WearerAddOrderListList',
	'../views/WearerAddOrderComplete',
	'../views/WearerAddOrderSendComplete',
	'../views/Pagination',
  '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminWearerAddOrder",
	"entities/models/AdminWearerAddOrderListCondition",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerAddOrder = App.Admin.Controllers.Abstract.extend({
			behaviors: {
					"Alerts": {
							behaviorClass: App.Admin.Behaviors.Alerts
					}
			},
			_sync : function(){
				var that = this;
				this.setNav('wearerAddOrder');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var wearerAddOrderModel = new App.Entities.Models.AdminWearerAddOrder();
				var wearerAddOrderView = new App.Admin.Views.WearerAddOrder({
					model:wearerAddOrderModel
				});

				var wearerAddOrderListConditionModel = new App.Entities.Models.AdminWearerAddOrderListCondition();
				var wearerAddOrderConditionView = new App.Admin.Views.WearerAddOrderCondition({
					model:wearerAddOrderListConditionModel
				});
				var wearerAddOrderListListView = new App.Admin.Views.WearerAddOrderListList();

				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});

				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});

				// 入力完了
				this.listenTo(wearerAddOrderConditionView, 'inputComplete', function(data){
					wearerAddOrderView.condition.reset();
					wearerAddOrderView.listTable.reset();
					//console.log(data);

					var wearerAddOrderComplete = new App.Admin.Views.WearerAddOrderComplete({
						data: data,
					});
					wearerAddOrderView.complete.show(wearerAddOrderComplete);
				});
				// 発注送信完了
				this.listenTo(wearerAddOrderConditionView, 'sendComplete', function(data){
					wearerAddOrderView.condition.reset();
					wearerAddOrderView.listTable.reset();
					//console.log(data);

					var wearerAddOrderSendComplete = new App.Admin.Views.WearerAddOrderSendComplete({
						data: data,
					});
					wearerAddOrderView.complete.show(wearerAddOrderSendComplete);
				});

				App.main.show(wearerAddOrderView);
				wearerAddOrderView.condition.show(wearerAddOrderConditionView);
				wearerAddOrderView.listTable.show(wearerAddOrderListListView);
			}
		});
	});

	return App.Admin.Controllers.WearerAddOrder;
});
