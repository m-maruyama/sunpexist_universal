define([
	'app',
	'./Abstract',
	'../views/WearerEdit',
	'../views/WearerEditCondition',
	'../views/WearerEditListList',
	'../views/AgreementNoCondition',
	'../views/SectionCondition',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/JobTypeCondition',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
	'../views/SexKbnCondition',
	"entities/models/Pager",
	"entities/models/AdminWearerEdit",
	"entities/models/AdminWearerEditListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/models/AdminCsvDownloadCondition",
	"entities/collections/AdminWearerEditListList",
	"entities/collections/AdminSectionModalListList",
	"entities/collections/AdminCsvDownload",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerEdit = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('wearerEdit');
				var pagerModel = new App.Entities.Models.Pager();
				var pagerModel2 = new App.Entities.Models.Pager();
				var modal = false;
				var wearerEditModel = null;
				var wearerEditView = new App.Admin.Views.WearerEdit();
				var wearerEditListListCollection = new App.Entities.Collections.AdminWearerEditListList();

				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();
				var sectionConditionView = new App.Admin.Views.SectionCondition();
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				var sexKbnConditionView = new App.Admin.Views.SexKbnCondition();

				var wearerEditListConditionModel = new App.Entities.Models.AdminWearerEditListCondition();
				var wearerEditConditionView = new App.Admin.Views.WearerEditCondition({
					model:wearerEditListConditionModel
				});
				var wearerEditListListView = new App.Admin.Views.WearerEditListList({
					collection: wearerEditListListCollection,
					pagerModel: pagerModel
				});
				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationView2 = new App.Admin.Views.Pagination({model: pagerModel});

				var fetchList = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					wearerEditListListView.fetch(wearerEditListConditionModel);
					wearerEditView.listTable.show(wearerEditListListView);
					wearerEditView.page.show(paginationView);
					wearerEditView.page_2.show(paginationView2);
				};
				//拠点絞り込み--ここから
				var sectionListListCollection = new App.Entities.Collections.AdminSectionModalListList();
				var sectionModalListListView = new App.Admin.Views.SectionModalListList({
					collection: sectionListListCollection,
					pagerModel: pagerModel2
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
						pagerModel2.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel2.set('sort_key', sortKey);
						pagerModel2.set('order', order);
					}
					sectionModalListListView.fetch(sectionModalListCondition);
					sectionModalView.listTable.show(sectionModalListListView);
				};
				this.listenTo(sectionModalConditionView, 'click:section_search', function(sortKey, order){
					modal = true;
					fetchList_section(1,sortKey,order);
				});
				this.listenTo(sectionModalView, 'fetched', function(){
					// wearerEditView.detailModal.show();
					// sectionModalView.render();
					sectionModalView.ui.modal.modal('show');
				});
				var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
				this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
					sectionConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
					sectionModalView.ui.modal.modal('hide');
				});
				//拠点絞り込み--ここまで

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
				this.listenTo(wearerEditListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(wearerEditConditionView, 'click:search', function(sortKey,order){
					modal = false;
					fetchList(1,sortKey,order);
				});
				//貸与終了ボタン
				this.listenTo(wearerEditListListView, 'click:wearer_end', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				// this.listenTo(csvDownloadView, 'click:download_btn', function(cond_map){
				// 	csvDownloadView.fetch(cond_map);
				// });


				App.main.show(wearerEditView);
				wearerEditView.condition.show(wearerEditConditionView);
				wearerEditConditionView.agreement_no.show(agreementNoConditionView);
				wearerEditConditionView.section.show(sectionConditionView);
				wearerEditConditionView.job_type.show(jobTypeConditionView);
				wearerEditConditionView.sex_kbn.show(sexKbnConditionView);
				wearerEditView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerEdit;
});
