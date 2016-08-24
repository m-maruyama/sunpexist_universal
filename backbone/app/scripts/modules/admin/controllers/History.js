define([
	'app',
	'./Abstract',
	'../views/History',
	'../views/HistoryCondition',
	'../views/HistoryListList',
	'../views/AgreementNoCondition',
	'../views/SectionCondition',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/JobTypeCondition',
	'../views/InputItemCondition',
	'../views/ItemColorCondition',
	'../views/ItemColorCondition',
	'../views/IndividualNumberCondition',
	'../views/DetailModal',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminHistory",
	"entities/models/AdminHistoryListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminHistoryListList",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.History = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('history');
				var pagerModel = new App.Entities.Models.Pager();


				var historyModel = null;
				var detailModalView = new App.Admin.Views.DetailModal();
				var historyView = new App.Admin.Views.History();
				var historyListListCollection = new App.Entities.Collections.AdminHistoryListList();
				var sectionListListCollection = new App.Entities.Collections.AdminSectionModalListList();

				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();
				var sectionConditionView = new App.Admin.Views.SectionCondition();
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				var inputItemConditionView = new App.Admin.Views.InputItemCondition();
				var itemColorConditionView = new App.Admin.Views.ItemColorCondition();
				var individualNumberConditionView = new App.Admin.Views.IndividualNumberCondition();

				var historyListConditionModel = new App.Entities.Models.AdminHistoryListCondition();
				var historyConditionView = new App.Admin.Views.HistoryCondition({
					model:historyListConditionModel
				});
				var historyListListView = new App.Admin.Views.HistoryListList({
					collection: historyListListCollection,
					pagerModel: pagerModel
				});
				var sectionModalListListView = new App.Admin.Views.SectionModalListList({
					collection: sectionListListCollection,
					pagerModel: pagerModel
				});
				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});
				var fetchList = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					historyListListView.fetch(historyListConditionModel);
					historyView.listTable.show(historyListListView);
				};
				this.listenTo(historyListListView, 'childview:click:a', function(view, model){
					historyModel = new App.Entities.Models.AdminHistory({no:model.get('order_req_no')});
					detailModalView.fetchDetail(historyModel);
				});
				this.listenTo(detailModalView, 'fetched', function(){
					historyView.detailModal.show(detailModalView.render());
					detailModalView.ui.modal.modal('show');
				});

				var sectionListCondition = new App.Entities.Models.AdminSectionModalListCondition();
				var sectionModalView = new App.Admin.Views.SectionModal({
					model:sectionListCondition
				});
				var sectionModalConditionView = new App.Admin.Views.SectionModalCondition({
					model:sectionListCondition
				});
				this.listenTo(sectionConditionView, 'click:section_btn', function(view, model){
					historyView.sectionModal.show(sectionModalView.render());
					sectionModalView.page.show(paginationView);
					sectionModalView.condition.show(sectionModalConditionView);
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
					sectionModalListListView.fetch(sectionListCondition);
					sectionModalView.listTable.show(sectionModalListListView);
				};
				this.listenTo(sectionModalConditionView, 'click:section_search', function(sortKey, order){
					fetchList_section(1,sortKey,order);
				});

				this.listenTo(sectionModalView, 'fetched', function(){
					// historyView.detailModal.show();
					// sectionModalView.render();
					historyView.detailModal.show(sectionModalView.render());
					sectionModalView.ui.modal.modal('show');
				});

				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});
				this.listenTo(historyListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(historyConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});
				App.main.show(historyView);
				historyView.page.show(paginationView);
				historyView.condition.show(historyConditionView);
				historyConditionView.agreement_no.show(agreementNoConditionView);
				historyConditionView.job_type.show(jobTypeConditionView);
				historyConditionView.section.show(sectionConditionView);
				historyConditionView.input_item.show(inputItemConditionView);
				historyConditionView.item_color.show(itemColorConditionView);
				historyConditionView.individual_number.show(individualNumberConditionView);
			}
		});
	});
	return App.Admin.Controllers.History;
});
