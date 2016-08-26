define([
	'app',
	'./Abstract',
	'../views/WearerInput',
	'../views/WearerInputCondition',
	'../views/AgreementNoConditionInput',
	'../views/SectionConditionInput',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/JobTypeConditionInput',
	'../views/InputItemCondition',
	'../views/ItemColorCondition',
	'../views/IndividualNumberCondition',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminWearerInputListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminWearerInputListList",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerInput = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('wearerInput');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var wearerInputModel = null;
				var wearerInputView = new App.Admin.Views.WearerInput();
				var wearerInputListListCollection = new App.Entities.Collections.AdminWearerInputListList();

				var agreementNoConditionView = new App.Admin.Views.AgreementNoConditionInput();
				var sectionConditionView = new App.Admin.Views.SectionConditionInput();
				var jobTypeConditionView = new App.Admin.Views.JobTypeConditionInput();
				var inputItemConditionView = new App.Admin.Views.InputItemCondition();
				var itemColorConditionView = new App.Admin.Views.ItemColorCondition();
				var individualNumberConditionView = new App.Admin.Views.IndividualNumberCondition();

				var wearerInputListConditionModel = new App.Entities.Models.AdminWearerInputListCondition();
				var wearerInputConditionView = new App.Admin.Views.WearerInputCondition({
					model:wearerInputListConditionModel
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
				this.listenTo(sectionConditionView, 'click:section_btn', function(view, model){
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
					wearerInputView.detailModal.show(sectionModalView.render());
					sectionModalView.ui.modal.modal('show');
				});
				var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
				this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
					sectionConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
					sectionModalView.ui.modal.modal('hide');
				});
				//拠点絞り込み--ここまで

				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});
				App.main.show(wearerInputView);
				wearerInputView.condition.show(wearerInputConditionView);
				wearerInputConditionView.agreement_no.show(agreementNoConditionView);
				wearerInputConditionView.job_type.show(jobTypeConditionView);
				wearerInputConditionView.section.show(sectionConditionView);
				wearerInputView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerInput;
});
