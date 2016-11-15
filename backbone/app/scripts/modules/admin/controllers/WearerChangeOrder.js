define([
	'app',
	'./Abstract',
	'../views/WearerChangeOrder',
	'../views/WearerChangeOrderCondition',
	'../views/WearerChangeOrderListList',
	'../views/WearerChangeOrderComplete',
	'../views/WearerChangeOrderSendComplete',
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
	"entities/models/AdminWearerChangeOrder",
	"entities/models/AdminWearerChangeOrderListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerChangeOrder = App.Admin.Controllers.Abstract.extend({
			behaviors: {
					"Alerts": {
							behaviorClass: App.Admin.Behaviors.Alerts
					}
			},
			_sync : function(){
				var that = this;
				this.setNav('wearerChangeOrder');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var wearerChangeOrderModel = new App.Entities.Models.AdminWearerChangeOrder();
				var wearerChangeOrderView = new App.Admin.Views.WearerChangeOrder({
					model:wearerChangeOrderModel
				});

				var agreementNoConditionChangeView = new App.Admin.Views.AgreementNoConditionChange();
				var reasonKbnConditionChangeView = new App.Admin.Views.ReasonKbnConditionChange();
				var sexKbnConditionChangeView = new App.Admin.Views.SexKbnConditionChange();
				var sectionConditionChangeView = new App.Admin.Views.SectionConditionChange();
				var jobTypeConditionChangeView = new App.Admin.Views.JobTypeConditionChange();
				var shipmentConditionChangeView = new App.Admin.Views.ShipmentConditionChange();

				var wearerChangeOrderListConditionModel = new App.Entities.Models.AdminWearerChangeOrderListCondition();
				var wearerChangeOrderConditionView = new App.Admin.Views.WearerChangeOrderCondition({
					model:wearerChangeOrderListConditionModel
				});
				var wearerChangeOrderListListView = new App.Admin.Views.WearerChangeOrderListList();

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
				this.listenTo(wearerChangeOrderConditionView, 'click:section_btn', function(view, model){
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
					wearerChangeOrderView.detailModal.show(sectionModalView.render());
					sectionModalView.ui.modal.modal('show');
				});
				var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
				this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
					wearerChangeOrderConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
					sectionModalView.ui.modal.modal('hide');
				});
				//拠点絞り込み--ここまで
				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});

				//貸与パターンセレクト変更時動作
				this.listenTo(wearerChangeOrderConditionView, 'change:job_type', function(data){
					var wearerChangeOrderListListView2 = new App.Admin.Views.WearerChangeOrderListList({
						data: data,
					});
					wearerChangeOrderView.listTable.show(wearerChangeOrderListListView2);
				});
				// 入力完了
				this.listenTo(wearerChangeOrderConditionView, 'inputComplete', function(data){
					wearerChangeOrderView.condition.reset();
					wearerChangeOrderView.listTable.reset();
					//console.log(data);

					var wearerChangeOrderComplete = new App.Admin.Views.WearerChangeOrderComplete({
						data: data,
					});
					wearerChangeOrderView.complete.show(wearerChangeOrderComplete);
				});
				// 発注送信完了
				this.listenTo(wearerChangeOrderConditionView, 'sendComplete', function(data){
					wearerChangeOrderView.condition.reset();
					wearerChangeOrderView.listTable.reset();
					//console.log(data);

					var wearerChangeOrderSendComplete = new App.Admin.Views.WearerChangeOrderSendComplete({
						data: data,
					});
					wearerChangeOrderView.complete.show(wearerChangeOrderSendComplete);
				});

				App.main.show(wearerChangeOrderView);
				wearerChangeOrderView.condition.show(wearerChangeOrderConditionView);
				wearerChangeOrderView.listTable.show(wearerChangeOrderListListView);
				wearerChangeOrderConditionView.agreement_no.show(agreementNoConditionChangeView);
				wearerChangeOrderConditionView.section.show(sectionConditionChangeView);
				wearerChangeOrderConditionView.job_type.show(jobTypeConditionChangeView);
				wearerChangeOrderConditionView.reason_kbn.show(reasonKbnConditionChangeView);
				wearerChangeOrderConditionView.sex_kbn.show(sexKbnConditionChangeView);
				wearerChangeOrderConditionView.shipment.show(shipmentConditionChangeView);
				wearerChangeOrderView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerChangeOrder;
});
