define([
	'app',
	'./Abstract',
	'../views/WearerEndOrder',
	'../views/WearerEndOrderCondition',
	'../views/WearerEndOrderList',
	'../views/WearerEndOrderComplete',
	'../views/WearerEndOrderSendComplete',
	'../views/ReasonKbnConditionOrder',
	'../views/Pagination',
	'../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminWearerEndOrder",
	"entities/models/AdminWearerEndOrderListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerEndOrder = App.Admin.Controllers.Abstract.extend({
			behaviors: {
					"Alerts": {
							behaviorClass: App.Admin.Behaviors.Alerts
					}
			},
			_sync : function(){
				var that = this;
				this.setNav('wearerEndOrder');
				var wearerEndOrderModel = new App.Entities.Models.AdminWearerEndOrder();
				var wearerEndOrderView = new App.Admin.Views.WearerEndOrder({
					model:wearerEndOrderModel
				});
				var wearerEndListListView = new App.Admin.Views.WearerEndOrderList();

				var reasonKbnConditionView = new App.Admin.Views.ReasonKbnConditionOrder();

				var wearerEndOrderListConditionModel = new App.Entities.Models.AdminWearerEndOrderListCondition();
				var wearerEndOrderConditionView = new App.Admin.Views.WearerEndOrderCondition({
					model:wearerEndOrderListConditionModel
				});

				this.listenTo(wearerEndOrderConditionView, 'inputComplete', function(data){
					wearerEndOrderView.condition.reset();
					wearerEndOrderView.listTable.reset();

					var wearerEndOrderComplete = new App.Admin.Views.WearerEndOrderComplete({
						data: data,
					});
					wearerEndOrderView.complete.show(wearerEndOrderComplete);
				});

				this.listenTo(wearerEndOrderConditionView, 'sendComplete', function(data){
					wearerEndOrderView.condition.reset();
					wearerEndOrderView.listTable.reset();

					var wearerEndOrderSendComplete = new App.Admin.Views.WearerEndOrderSendComplete({
						data: data,
					});
					wearerEndOrderView.complete.show(wearerEndOrderSendComplete);
				});

				App.main.show(wearerEndOrderView);
				wearerEndOrderView.condition.show(wearerEndOrderConditionView);
				wearerEndOrderView.listTable.show(wearerEndListListView);
				Sleep(0.02);
				wearerEndOrderConditionView.reason_kbn.show(reasonKbnConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerEndOrder;
});
