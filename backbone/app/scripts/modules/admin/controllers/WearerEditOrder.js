define([
	'app',
	'./Abstract',
	'../views/WearerEditOrder',
	'../views/WearerEditOrderCondition',
	'../views/WearerEditOrderComplete',
	'../views/WearerEditOrderSendComplete',
	'../views/AgreementNoConditionChange',
	'../views/SexKbnConditionChange',
	'../views/Pagination',
  '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminWearerEditOrder",
	"entities/models/AdminWearerEditOrderListCondition",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerEditOrder = App.Admin.Controllers.Abstract.extend({
			behaviors: {
					"Alerts": {
							behaviorClass: App.Admin.Behaviors.Alerts
					}
			},
			_sync : function(){
				var that = this;
				this.setNav('wearerEditOrder');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var wearerEditOrderModel = new App.Entities.Models.AdminWearerEditOrder();
				var wearerEditOrderView = new App.Admin.Views.WearerEditOrder({
					model:wearerEditOrderModel
				});

				var agreementNoConditionChangeView = new App.Admin.Views.AgreementNoConditionChange();
				var sexKbnConditionChangeView = new App.Admin.Views.SexKbnConditionChange();

				var wearerEditOrderListConditionModel = new App.Entities.Models.AdminWearerEditOrderListCondition();
				var wearerEditOrderConditionView = new App.Admin.Views.WearerEditOrderCondition({
					model:wearerEditOrderListConditionModel
				});

				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});

				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});

				// 入力完了
				this.listenTo(wearerEditOrderConditionView, 'inputComplete', function(data){
					wearerEditOrderView.condition.reset();
					wearerEditOrderView.listTable.reset();

					var wearerEditOrderComplete = new App.Admin.Views.WearerEditOrderComplete({
						data: data,
					});
					wearerEditOrderView.complete.show(wearerEditOrderComplete);
				});
				// 発注送信完了
				this.listenTo(wearerEditOrderConditionView, 'sendComplete', function(data){
					wearerEditOrderView.condition.reset();
					wearerEditOrderView.listTable.reset();

					var wearerEditOrderSendComplete = new App.Admin.Views.WearerEditOrderSendComplete({
						data: data,
					});
					wearerEditOrderView.complete.show(wearerEditOrderSendComplete);
				});

				App.main.show(wearerEditOrderView);
				wearerEditOrderView.condition.show(wearerEditOrderConditionView);
				wearerEditOrderConditionView.agreement_no.show(agreementNoConditionChangeView);
				wearerEditOrderConditionView.sex_kbn.show(sexKbnConditionChangeView);
			}
		});
	});

	return App.Admin.Controllers.WearerEditOrder;
});
