define([
	'app',
	'./Abstract',
	'../views/WearerExchangeOrder',
	'../views/WearerExchangeOrderCondition',
	'../views/WearerExchangeOrderListList',
	'../views/WearerExchangeOrderComplete',
	'../views/WearerExchangeOrderSendComplete',
	'../views/AgreementNoConditionChange',
	'../views/ReasonKbnConditionChange',
	'../views/SexKbnConditionChange',
	'../views/SectionConditionChange',
	'../views/JobTypeConditionChange',
	'../views/ShipmentConditionChange',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
  '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminWearerExchangeOrder",
	"entities/models/AdminWearerExchangeOrderListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminSectionModalListList",
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

				var agreementNoConditionChangeView = new App.Admin.Views.AgreementNoConditionChange();
				var reasonKbnConditionChangeView = new App.Admin.Views.ReasonKbnConditionChange();
				var sexKbnConditionChangeView = new App.Admin.Views.SexKbnConditionChange();
				var sectionConditionChangeView = new App.Admin.Views.SectionConditionChange();
				var jobTypeConditionChangeView = new App.Admin.Views.JobTypeConditionChange();
				var shipmentConditionChangeView = new App.Admin.Views.ShipmentConditionChange();

				var wearerExchangeOrderListConditionModel = new App.Entities.Models.AdminWearerExchangeOrderListCondition();
				var wearerExchangeOrderConditionView = new App.Admin.Views.WearerExchangeOrderCondition({
					model:wearerExchangeOrderListConditionModel
				});
				var wearerExchangeOrderListListView = new App.Admin.Views.WearerExchangeOrderListList();

				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});

				//拠点絞り込み--ここから
				var sectionListListCollection = new App.Entities.Collections.AdminSectionModalListList();
				var sectionModalListListView = new App.Admin.Views.SectionModalListList({
					collection: sectionListListCollection,
					pagerModel: pagerModel
				});
				var sectionModalListCondition = new App.Entities.Models.AdminSectionModalListCondition();
				var sectionModalConditionView = new App.Admin.Views.SectionModalCondition({
					model:sectionModalListCondition
				});
				var sectionModalView = new App.Admin.Views.SectionModal({
					model:sectionModalListCondition
				});
				this.listenTo(wearerExchangeOrderConditionView, 'click:section_btn', function(view, model){
					// sectionModalView.page.reset();
					// sectionModalView.listTable.reset();
					sectionModalView.ui.modal.modal('show');
				});
				var fetchList_section = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					sectionModalListListView.fetch(sectionModalListCondition);
					sectionModalView.listTable.show(sectionModalListListView);
				};
				this.listenTo(sectionModalConditionView, 'click:section_search', function(sortKey, order){
					modal = true;
					fetchList_section(1,sortKey,order);
				});
				this.listenTo(sectionModalView, 'fetched', function(){
					wearerExchangeOrderView.detailModal.show(sectionModalView.render());
					sectionModalView.ui.modal.modal('show');
				});
				var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
				this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
					wearerExchangeOrderConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
					sectionModalView.ui.modal.modal('hide');
				});
				//拠点絞り込み--ここまで
				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});

				//貸与パターンセレクト変更時動作
				this.listenTo(wearerExchangeOrderConditionView, 'change:job_type', function(data){
					var wearerExchangeOrderListListView2 = new App.Admin.Views.WearerExchangeOrderListList({
						data: data,
					});
					wearerExchangeOrderView.listTable.show(wearerExchangeOrderListListView2);
				});
				// 入力完了
				this.listenTo(wearerExchangeOrderConditionView, 'inputComplete', function(data){
					wearerExchangeOrderView.condition.reset();
					wearerExchangeOrderView.listTable.reset();
					//console.log(data);

					var wearerExchangeOrderComplete = new App.Admin.Views.WearerExchangeOrderComplete({
						data: data,
					});
					wearerExchangeOrderView.complete.show(wearerExchangeOrderComplete);
				});
				// 発注送信完了
				this.listenTo(wearerExchangeOrderConditionView, 'sendComplete', function(data){
					wearerExchangeOrderView.condition.reset();
					wearerExchangeOrderView.listTable.reset();
					//console.log(data);

					var wearerExchangeOrderSendComplete = new App.Admin.Views.WearerExchangeOrderSendComplete({
						data: data,
					});
					wearerExchangeOrderView.complete.show(wearerExchangeOrderSendComplete);
				});

				App.main.show(wearerExchangeOrderView);
				wearerExchangeOrderView.condition.show(wearerExchangeOrderConditionView);
				wearerExchangeOrderView.listTable.show(wearerExchangeOrderListListView);
				wearerExchangeOrderConditionView.agreement_no.show(agreementNoConditionChangeView);
				wearerExchangeOrderConditionView.reason_kbn.show(reasonKbnConditionChangeView);
				wearerExchangeOrderConditionView.sex_kbn.show(sexKbnConditionChangeView);
				wearerExchangeOrderConditionView.section.show(sectionConditionChangeView);
				wearerExchangeOrderConditionView.job_type.show(jobTypeConditionChangeView);
				wearerExchangeOrderConditionView.shipment.show(shipmentConditionChangeView);
				wearerExchangeOrderView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerExchangeOrder;
});