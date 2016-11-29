define([
	'app',
	'./Abstract',
	'../views/WearerReturnOrder',
	'../views/WearerReturnOrderCondition',
	'../views/WearerReturnOrderListList',
	'../views/WearerReturnOrderComplete',
	'../views/WearerReturnOrderSendComplete',
	'../views/Pagination',
  '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminWearerReturnOrder",
	"entities/models/AdminWearerReturnOrderListCondition",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerReturnOrder = App.Admin.Controllers.Abstract.extend({
			behaviors: {
					"Alerts": {
							behaviorClass: App.Admin.Behaviors.Alerts
					}
			},
			_sync : function(){
				var that = this;
				this.setNav('wearerReturnOrder');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var wearerReturnOrderModel = new App.Entities.Models.AdminWearerReturnOrder();
				var wearerReturnOrderView = new App.Admin.Views.WearerReturnOrder({
					model:wearerReturnOrderModel
				});

				var wearerReturnOrderListConditionModel = new App.Entities.Models.AdminWearerReturnOrderListCondition();
				var wearerReturnOrderConditionView = new App.Admin.Views.WearerReturnOrderCondition({
					model:wearerReturnOrderListConditionModel
				});
				var wearerReturnOrderListListView = new App.Admin.Views.WearerReturnOrderListList();

				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});

				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});

				this.listenTo(wearerReturnOrderConditionView, 'inputComplete', function(data){
					wearerReturnOrderView.condition.reset();
					wearerReturnOrderView.listTable.reset();

					var wearerReturnOrderComplete = new App.Admin.Views.WearerReturnOrderComplete({
						data: data,
					});
					wearerReturnOrderView.complete.show(wearerReturnOrderComplete);
				});

				this.listenTo(wearerReturnOrderConditionView, 'sendComplete', function(data){
					wearerReturnOrderView.condition.reset();
					wearerReturnOrderView.listTable.reset();

					var wearerReturnOrderSendComplete = new App.Admin.Views.WearerReturnOrderSendComplete({
						data: data,
					});
					wearerReturnOrderView.complete.show(wearerReturnOrderSendComplete);
				});

				App.main.show(wearerReturnOrderView);
				wearerReturnOrderView.condition.show(wearerReturnOrderConditionView);
				wearerReturnOrderView.listTable.show(wearerReturnOrderListListView);
			}
		});
	});

	return App.Admin.Controllers.WearerReturnOrder;
});
