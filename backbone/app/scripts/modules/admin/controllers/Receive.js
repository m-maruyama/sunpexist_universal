define([
	'app',
	'./Abstract',
	'../views/Receive',
	'../views/ReceiveCondition',
	'../views/ReceiveListList',
	'../views/ReceiveButton',
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
	"entities/models/AdminReceive",
	"entities/models/AdminReceiveListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/models/AdminCsvDownloadCondition",
	"entities/collections/AdminReceiveListList",
	"entities/collections/AdminSectionModalListList",
	"entities/collections/AdminCsvDownload",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Receive = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('receive');
				var pagerModel = new App.Entities.Models.Pager();

				var modal = false;
				var receiveModel = null;
				var detailModalView = new App.Admin.Views.DetailModal();
				var receiveView = new App.Admin.Views.Receive();
				var receiveListListCollection = new App.Entities.Collections.AdminReceiveListList();

				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();
				var sectionConditionView = new App.Admin.Views.SectionCondition();
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				var inputItemConditionView = new App.Admin.Views.InputItemCondition();
				var itemColorConditionView = new App.Admin.Views.ItemColorCondition();
				var individualNumberConditionView = new App.Admin.Views.IndividualNumberCondition();

				var receiveListConditionModel = new App.Entities.Models.AdminReceiveListCondition();
				var receiveConditionView = new App.Admin.Views.ReceiveCondition({
					model:receiveListConditionModel
				});
				var receiveListListView = new App.Admin.Views.ReceiveListList({
					collection: receiveListListCollection,
					model:receiveListConditionModel,
					pagerModel: pagerModel
				});

				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationView2 = new App.Admin.Views.Pagination({model: pagerModel});
				var csvDownloadView = new App.Admin.Views.CsvDownload();

				var receiveListItemModel = new App.Entities.Models.AdminReceiveListItem();
				var receiveButtonView = new App.Admin.Views.ReceiveButton({
					collection: receiveListListCollection,
					model:receiveListConditionModel,
					pagerModel: pagerModel
				});
				var fetchList = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					receiveListListView.fetch(receiveListConditionModel);
					receiveView.listTable.show(receiveListListView);
					receiveView.receive_button.show(receiveButtonView);
					receiveView.page.show(paginationView);
					receiveView.page_2.show(paginationView2);
					receiveView.csv_download.show(csvDownloadView);
				};

				this.listenTo(receiveListListView, 'childview:click:a', function(view, model){
					receiveModel = new App.Entities.Models.AdminReceive({no:model.get('order_req_no')});
					detailModalView.fetchDetail(receiveModel);
				});

				this.listenTo(detailModalView, 'fetched', function(){
					receiveView.detailModal.show(detailModalView.render());
					detailModalView.ui.modal.modal('show');
				});

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
					// receiveView.detailModal.show();
					// sectionModalView.render();
					receiveView.detailModal.show(sectionModalView.render());
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

				this.listenTo(receiveListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});

				this.listenTo(receiveButtonView, 'research', function(sortKey,order,pageNumber){
					fetchList(pageNumber,sortKey,order);
				});
				this.listenTo(receiveConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});
				this.listenTo(receiveView, 'updated', function(){
					addFlag = false;
					fetchList();
					receiveView = new App.Admin.Views.Receive({
						collection: receiveListListCollection
					});
				});
				//遷移するときのアラート
				var addFlag = false;

				this.listenTo(receiveListListCollection, 'sync', function(){
					addFlag = false;
				});
				$(window).on('beforeunload', function() {
					if (addFlag){
						return '更新が完了していません。ページ遷移をしますか？';
					}
					return;
				});
				this.listenTo(csvDownloadView, 'click:download_btn', function(cond_map){
					csvDownloadView.fetch(cond_map);
				});

				App.main.show(receiveView);
				receiveView.condition.show(receiveConditionView);
				receiveConditionView.agreement_no.show(agreementNoConditionView);
				receiveConditionView.job_type.show(jobTypeConditionView);
				receiveConditionView.section.show(sectionConditionView);
				receiveConditionView.input_item.show(inputItemConditionView);
				receiveConditionView.item_color.show(itemColorConditionView);
				receiveConditionView.individual_number.show(individualNumberConditionView);
				receiveView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});
	return App.Admin.Controllers.Receive;
});
