define([
	'app',
	'./Abstract',
	'../views/WearerOtherChangeOrder',
	'../views/WearerOtherChangeOrderCondition',
	'../views/WearerOtherChangeOrderListList',
	'../views/WearerOtherChangeOrderComplete',
	'../views/WearerOtherChangeOrderSendComplete',
	'../views/Pagination',
  '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminWearerOtherChangeOrder",
	"entities/models/AdminWearerOtherChangeOrderListCondition",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerOtherChangeOrder = App.Admin.Controllers.Abstract.extend({
			behaviors: {
					"Alerts": {
							behaviorClass: App.Admin.Behaviors.Alerts
					}
			},
			_sync : function(){
				var that = this;
				this.setNav('wearerOtherChangeOrder');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var wearerOtherChangeOrderModel = new App.Entities.Models.AdminWearerOtherChangeOrder();
				var wearerOtherChangeOrderView = new App.Admin.Views.WearerOtherChangeOrder({
					model:wearerOtherChangeOrderModel
				});

				var wearerOtherChangeOrderListConditionModel = new App.Entities.Models.AdminWearerOtherChangeOrderListCondition();
				var wearerOtherChangeOrderConditionView = new App.Admin.Views.WearerOtherChangeOrderCondition({
					model:wearerOtherChangeOrderListConditionModel
				});
				var wearerOtherChangeOrderListListView = new App.Admin.Views.WearerOtherChangeOrderListList();

				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});

				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});

				// 入力完了
				this.listenTo(wearerOtherChangeOrderConditionView, 'inputComplete', function(data){
					wearerOtherChangeOrderView.condition.reset();
					wearerOtherChangeOrderView.listTable.reset();

					var wearerOtherChangeOrderComplete = new App.Admin.Views.WearerOtherChangeOrderComplete({
						data: data,
					});
					wearerOtherChangeOrderView.complete.show(wearerOtherChangeOrderComplete);
				});
				// 発注送信完了
				this.listenTo(wearerOtherChangeOrderConditionView, 'sendComplete', function(data){
					wearerOtherChangeOrderView.condition.reset();
					wearerOtherChangeOrderView.listTable.reset();

					var wearerOtherChangeOrderSendComplete = new App.Admin.Views.WearerOtherChangeOrderSendComplete({
						data: data,
					});
					wearerOtherChangeOrderView.complete.show(wearerOtherChangeOrderSendComplete);
				});

				App.main.show(wearerOtherChangeOrderView);
				wearerOtherChangeOrderView.condition.show(wearerOtherChangeOrderConditionView);
				wearerOtherChangeOrderView.listTable.show(wearerOtherChangeOrderListListView);
			}
		});
	});

	return App.Admin.Controllers.WearerOtherChangeOrder;
});
