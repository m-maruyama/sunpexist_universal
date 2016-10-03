define([
	'app',
	'./Abstract',
	'../views/WearerOrder',
	'../views/WearerOrderCondition',
	'../views/WearerOrderListList',
	'../views/AgreementNoCondition',
	'../views/ReasonKbnCondition',
	'../views/SexKbnCondition',
	'../views/SectionCondition',
	'../views/JobTypeCondition',
	'../views/ShipmentCondition',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
  '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminWearerOrder",
	"entities/models/AdminWearerOrderListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerOrder = App.Admin.Controllers.Abstract.extend({
			behaviors: {
					"Alerts": {
							behaviorClass: App.Admin.Behaviors.Alerts
					}
			},
			_sync : function(){
				var that = this;
				this.setNav('wearerOrder');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var wearerOrderModel = new App.Entities.Models.AdminWearerOrder();
				var wearerOrderView = new App.Admin.Views.WearerOrder({
					model:wearerOrderModel
				});

				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();
				var reasonKbnConditionView = new App.Admin.Views.ReasonKbnCondition();
				var sexKbnConditionView = new App.Admin.Views.SexKbnCondition();
				var sectionConditionView = new App.Admin.Views.SectionCondition();
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				var shipmentConditionView = new App.Admin.Views.ShipmentCondition();

				var wearerOrderListConditionModel = new App.Entities.Models.AdminWearerOrderListCondition();
				var wearerOrderConditionView = new App.Admin.Views.WearerOrderCondition({
					model:wearerOrderListConditionModel
				});
				var wearerOrderListListView = new App.Admin.Views.WearerOrderListList();

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
				this.listenTo(wearerOrderConditionView, 'click:section_btn', function(view, model){
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
					wearerOrderView.detailModal.show(sectionModalView.render());
					sectionModalView.ui.modal.modal('show');
				});
				var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
				this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
					wearerOrderConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
					sectionModalView.ui.modal.modal('hide');
				});
				//拠点絞り込み--ここまで

				//着用者のみ登録して終了
				this.listenTo(wearerOrderView, 'click:input_insert', function(agreement_no){
					var errors = wearerOrderConditionView.insert_wearer(agreement_no);
					if(errors){
						wearerOrderView.triggerMethod('showAlerts', errors);
					}
				});

				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});

				//貸与パターンセレクト変更時動作
				this.listenTo(wearerOrderConditionView, ':job_type', function(job_type){
					//console.log(job_type);
					var wearerOrderListListView2 = new App.Admin.Views.WearerOrderListList({
						job_type:job_type,
					});
					wearerOrderView.listTable.show(wearerOrderListListView2);
				});

				App.main.show(wearerOrderView);
				wearerOrderView.condition.show(wearerOrderConditionView);
				wearerOrderView.listTable.show(wearerOrderListListView);
				wearerOrderConditionView.agreement_no.show(agreementNoConditionView);
				wearerOrderConditionView.reason_kbn.show(reasonKbnConditionView);
				wearerOrderConditionView.sex_kbn.show(sexKbnConditionView);
				wearerOrderConditionView.section.show(sectionConditionView);
				wearerOrderConditionView.job_type.show(jobTypeConditionView);
				wearerOrderConditionView.shipment.show(shipmentConditionView);
				wearerOrderView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerOrder;
});
