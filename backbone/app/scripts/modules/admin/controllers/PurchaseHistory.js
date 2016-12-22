define([
	'app',
	'./Abstract',
	'../views/PurchaseHistory',
	'../views/PurchaseHistoryCondition',
	'../views/PurchaseHistoryListList',
	'../views/PurchaseAgreementNoCondition',
	'../views/SectionCondition',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/JobTypeCondition',
	'../views/PurchaseInputItemCondition',
	'../views/PurchaseItemColorCondition',
	'../views/DetailModal',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminPurchaseHistory",
	"entities/models/AdminPurchaseHistoryListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminPurchaseHistoryListList",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.PurchaseHistory = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;

				this.setNav('purchaseInput');


				var pagerModel = new App.Entities.Models.Pager();
				var pagerModel2 = new App.Entities.Models.Pager();
				var pagerModel3 = new App.Entities.Models.Pager();
				var modal = false;
				var purchaseHistoryModel = null;
				var detailModalView = new App.Admin.Views.DetailModal();
				var purchaseHistoryView = new App.Admin.Views.PurchaseHistory();

				var purchaseHistoryListListCollection = new App.Entities.Collections.AdminPurchaseHistoryListList();
				var agreementNoConditionView = new App.Admin.Views.PurchaseAgreementNoCondition();
				var sectionConditionView = new App.Admin.Views.SectionCondition();
				//var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				var purchaseInputItemConditionView = new App.Admin.Views.PurchaseInputItemCondition();




				var purchaseItemColorConditionView = new App.Admin.Views.PurchaseItemColorCondition();

				var purchaseHistoryListConditionModel = new App.Entities.Models.AdminPurchaseHistoryListCondition();
				var purchaseHistoryConditionView = new App.Admin.Views.PurchaseHistoryCondition({
					model:purchaseHistoryListConditionModel
				});
				var purchaseHistoryListListView = new App.Admin.Views.PurchaseHistoryListList({
					collection: purchaseHistoryListListCollection,
					pagerModel: pagerModel
				});
				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationView2 = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationSectionView = new App.Admin.Views.Pagination({model: pagerModel2});

				var fetchList = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					purchaseHistoryListListView.fetch(purchaseHistoryListConditionModel);
					purchaseHistoryView.listTable.show(purchaseHistoryListListView);
					purchaseHistoryView.page.show(paginationView);
					purchaseHistoryView.page_2.show(paginationView2);
				};
				//this.listenTo(purchaseHistoryListListView, 'childview:click:a', function(view, model){
				//	purchaseHistoryModel = new App.Entities.Models.AdminPurchaseHistory({no:model.get('order_req_no')});
				//	detailModalView.fetchDetail(puchaseHistoryModel);
				//});
				this.listenTo(detailModalView, 'fetched', function(){
					purchaseHistoryView.detailModal.show(detailModalView.render());
					detailModalView.ui.modal.modal('show');
				});

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
					// historyView.detailModal.show();
					// sectionModalView.render();
					purchaseHistoryView.detailModal.show(sectionModalView.render());
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
				this.listenTo(paginationSectionView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});

				this.listenTo(purchaseHistoryListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(purchaseHistoryConditionView, 'click:search', function(sortKey,order){
					modal = false;
					fetchList(1,sortKey,order);
				});
				//this.listenTo(purchaseHistoryConditionView, 'click:delete', function(sortKey,order){
				//	modal = false;
				//	fetchList(1,sortKey,order);
				//});

				// 契約No変更時の絞り込み処理 --ここから
				this.listenTo(purchaseHistoryConditionView, 'change:section_select', function(agreement_no){
					var sectionConditionView2 = new App.Admin.Views.SectionCondition({
						agreement_no:agreement_no,
					});
					purchaseHistoryConditionView.section.show(sectionConditionView2);

					var sectionModalListListView2 = new App.Admin.Views.SectionModalListList({
						collection: sectionListListCollection,
						pagerModel: pagerModel3
					});
					var sectionModalListCondition2 = new App.Entities.Models.AdminSectionModalListCondition();
					var sectionModalConditionView2 = new App.Admin.Views.SectionModalCondition({
						model:sectionModalListCondition2
					});
					var sectionModalView2 = new App.Admin.Views.SectionModal({
						model:sectionModalListCondition2
					});
					this.listenTo(sectionConditionView2, 'click:section_btn', function(view, model){
						sectionModalView2.ui.modal.modal('show');
					});
					var fetchList_section_2 = function(pageNumber,sortKey,order){
						if(pageNumber){
							pagerModel3.set('page_number', pageNumber);
						}
						if(sortKey){
							pagerModel3.set('sort_key', sortKey);
							pagerModel3.set('order', order);
						}
						sectionModalListListView2.fetch(sectionModalListCondition2);
						sectionModalView2.listTable.show(sectionModalListListView2);
						sectionModalView2.page.show(paginationSectionView2);
					};
					var paginationSectionView2 = new App.Admin.Views.Pagination({model: pagerModel3});
					this.listenTo(paginationSectionView2, 'selected', function(pageNumber){
							fetchList_section_2(pageNumber);
					});
					this.listenTo(sectionModalConditionView2, 'click:section_search', function(sortKey, order){
						modal = true;
						fetchList_section_2(1,sortKey,order);
					});
					this.listenTo(sectionModalView2, 'fetched', function(){
						purchaseHistoryView.detailModal.show(sectionModalView2.render());
						sectionModalView2.ui.modal.modal('show');
					});
					var sectionModalListItemView2 = new App.Admin.Views.SectionModalListItem();
					this.listenTo(sectionModalListListView2, 'childview:click:section_select', function(model){
						sectionConditionView2.ui.section[0].value = model.model.attributes.rntl_sect_cd;
						sectionModalView2.ui.modal.modal('hide');
					});

					purchaseHistoryView.sectionModal_2.show(sectionModalView2.render());
					sectionModalView2.condition.show(sectionModalConditionView2);
				});

				// 契約No変更時の絞り込み処理 --ここまで
				App.main.show(purchaseHistoryView);
				purchaseHistoryView.condition.show(purchaseHistoryConditionView);
				purchaseHistoryConditionView.agreement_no.show(agreementNoConditionView);
				Sleep(0.04);
				purchaseHistoryConditionView.section.show(sectionConditionView);
				Sleep(0.02);
				purchaseHistoryView.sectionModal.show(sectionModalView.render());
				Sleep(0.02);
				purchaseHistoryConditionView.input_item.show(purchaseInputItemConditionView);
				Sleep(0.02);
				purchaseHistoryConditionView.item_color.show(purchaseItemColorConditionView);
				sectionModalView.condition.show(sectionModalConditionView);
				sectionModalView.page.show(paginationSectionView);

				purchaseHistoryListListView.fetch(purchaseHistoryListConditionModel);
				//fetchList();
				//console.log(purchaseHistoryView);

			}
		});
	});

	return App.Admin.Controllers.PurchaseHistory;
});
