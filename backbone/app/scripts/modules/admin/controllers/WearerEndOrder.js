define([
	'app',
	'./Abstract',
	'../views/WearerEndOrder',
	'../views/WearerEndOrderCondition',
	'../views/AgreementNoConditionInput',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/SectionModal',
	'../views/SectionModalListList',
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
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var wearerEndOrderModel = new App.Entities.Models.AdminWearerEndOrder();
				var wearerEndOrderView = new App.Admin.Views.WearerEndOrder({
					model:wearerEndOrderModel
				});

				var agreementNoConditionView = new App.Admin.Views.AgreementNoConditionInput();

				var wearerEndOrderListConditionModel = new App.Entities.Models.AdminWearerEndOrderListCondition();
				var wearerEndOrderConditionView = new App.Admin.Views.WearerEndOrderCondition({
					model:wearerEndOrderListConditionModel
				});
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
				this.listenTo(wearerEndOrderConditionView, 'click:section_btn', function(view, model){
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
					wearerEndOrderView.detailModal.show(sectionModalView.render());
					sectionModalView.ui.modal.modal('show');
				});
				var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
				this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
					wearerEndOrderConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
					sectionModalView.ui.modal.modal('hide');
				});
				//拠点絞り込み--ここまで

				//着用者のみ登録して終了
				this.listenTo(wearerEndOrderView, 'click:input_insert', function(agreement_no){
					var errors = wearerEndOrderConditionView.insert_wearer(agreement_no);
					if(errors){
						wearerEndOrderView.triggerMethod('showAlerts', errors);
					}
				});

				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});
				App.main.show(wearerEndOrderView);
				wearerEndOrderView.condition.show(wearerEndOrderConditionView);
				wearerEndOrderView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerEndOrder;
});
