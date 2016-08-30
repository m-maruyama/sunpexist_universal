define([
	'app',
	'./Abstract',
	'../views/Lend',
	'../views/LendCondition',
	'../views/LendListList',
	'../views/AgreementNoCondition',
	'../views/SectionCondition',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/JobTypeCondition',
	'../views/InputItemCondition',
	'../views/ItemColorCondition',
	'../views/IndividualNumberCondition',
	'../views/DetailModal',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
	'../views/CsvDownload',
	"entities/models/Pager",
	"entities/models/AdminLend",
	"entities/models/AdminLendListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/models/AdminCsvDownloadCondition",
	"entities/collections/AdminLendListList",
	"entities/collections/AdminSectionModalListList",
	"entities/collections/AdminCsvDownload",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Lend = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('lend');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var lendModel = null;
				var detailModalView = new App.Admin.Views.DetailModal();
				var lendView = new App.Admin.Views.Lend();
				var lendListListCollection = new App.Entities.Collections.AdminLendListList();

				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();
				var sectionConditionView = new App.Admin.Views.SectionCondition();
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				var inputItemConditionView = new App.Admin.Views.InputItemCondition();
				var itemColorConditionView = new App.Admin.Views.ItemColorCondition();
				var individualNumberConditionView = new App.Admin.Views.IndividualNumberCondition();

				var lendListConditionModel = new App.Entities.Models.AdminLendListCondition();
				var lendConditionView = new App.Admin.Views.LendCondition({
					model:lendListConditionModel,
					pagerModel: pagerModel
				});
				var lendListListView = new App.Admin.Views.LendListList({
					collection: lendListListCollection,
					model:lendListConditionModel,
					pagerModel: pagerModel
				});

				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationView2 = new App.Admin.Views.Pagination({model: pagerModel});
				var csvDownloadView = new App.Admin.Views.CsvDownload();

				var fetchList = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					lendListListView.fetch(lendListConditionModel);
					lendView.listTable.show(lendListListView);
					lendView.page.show(paginationView);
					lendView.page_2.show(paginationView2);
					lendView.csv_download.show(csvDownloadView);
				};

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
					// historyView.detailModal.show();
					// sectionModalView.render();
					lendView.detailModal.show(sectionModalView.render());
					sectionModalView.ui.modal.modal('show');
				});
				var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
				this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
					sectionConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
					sectionModalView.ui.modal.modal('hide');
				});
				//拠点絞り込み--ここまで

				this.listenTo(lendListListView, 'childview:click:a', function(view, model){
					lendModel = new App.Entities.Models.AdminLend({no:model.get('order_req_no')});
					detailModalView.fetchDetail(lendModel);
				});

				this.listenTo(detailModalView, 'fetched', function(){
					lendView.detailModal.show(detailModalView.render());
					detailModalView.ui.modal.modal('show');
				});

				this.listenTo(paginationView, 'selected', function(pageNumber){
					if(modal){
						fetchList_section(pageNumber);
					}else{
						fetchList(pageNumber);
					}
				});
				this.listenTo(paginationView2, 'selected', function(pageNumber){
					if(modal){
						fetchList_section(pageNumber);
					}else{
						fetchList(pageNumber);
					}
				});

				this.listenTo(lendListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});

				this.listenTo(lendConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});

				this.listenTo(csvDownloadView, 'click:download_btn', function(cond_map){
					csvDownloadView.fetch(cond_map);
				});

				// this.listenTo(lendConditionView, 'click:download', function(sortKey,order){
					// fetchList(null,sortKey,order);
				// });

				App.main.show(lendView);
				lendView.condition.show(lendConditionView);
				lendConditionView.agreement_no.show(agreementNoConditionView);
				lendConditionView.job_type.show(jobTypeConditionView);
				lendConditionView.section.show(sectionConditionView);
				lendConditionView.input_item.show(inputItemConditionView);
				lendConditionView.item_color.show(itemColorConditionView);
				lendConditionView.individual_number.show(individualNumberConditionView);
				lendView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});
	return App.Admin.Controllers.Lend;
});
