define([
	'app',
	'./Abstract',
	'../views/WearerChange',
	'../views/WearerChangeCondition',
	'../views/WearerChangeListList',
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
	"entities/models/AdminWearerChange",
	"entities/models/AdminWearerChangeListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/models/AdminCsvDownloadCondition",
	"entities/collections/AdminWearerChangeListList",
	"entities/collections/AdminSectionModalListList",
	"entities/collections/AdminCsvDownload",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerChange = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('wearerChange');
				var pagerModel = new App.Entities.Models.Pager();
				var pagerModel2 = new App.Entities.Models.Pager();
				var pagerModel3 = new App.Entities.Models.Pager();
				var pagerModel4 = new App.Entities.Models.Pager();
				var modal = false;
				var wearerChangeModel = null;
				var wearerChangeView = new App.Admin.Views.WearerChange();
				var wearerChangeListListCollection = new App.Entities.Collections.AdminWearerChangeListList();
				var sectionListListCollection = new App.Entities.Collections.AdminSectionModalListList();

				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();
				var sectionConditionView = new App.Admin.Views.SectionCondition();
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				var sexKbnConditionView = new App.Admin.Views.SexKbnCondition();

				var wearerChangeListConditionModel = new App.Entities.Models.AdminWearerChangeListCondition();
				var wearerChangeConditionView = new App.Admin.Views.WearerChangeCondition({
					model:wearerChangeListConditionModel
				});
				var wearerChangeListListView = new App.Admin.Views.WearerChangeListList({
					collection: wearerChangeListListCollection,
					pagerModel: pagerModel
				});
				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationView2 = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationView3 = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationView4 = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationSectionView = new App.Admin.Views.Pagination({model: pagerModel2});

				var fetchList = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					wearerChangeListListView.fetch(wearerChangeListConditionModel);
					wearerChangeView.listTable.show(wearerChangeListListView);
					wearerChangeView.page.show(paginationView);
					wearerChangeView.page_2.show(paginationView2);
				};
				var fetchList_2 = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					wearerChangeListListView.fetch(wearerChangeListConditionModel);
					wearerChangeView.listTable.show(wearerChangeListListView);
					wearerChangeView.page.show(paginationView3);
					wearerChangeView.page_2.show(paginationView4);
				};

				this.listenTo(wearerChangeConditionView, 'first:section', function() {
					var sectionConditionView = new App.Admin.Views.SectionCondition();
					wearerChangeConditionView.section.show(sectionConditionView);

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
						// wearerChangeView.detailModal.show();
						// sectionModalView.render();
						sectionModalView.ui.modal.modal('show');
					});
					var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
					this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
						sectionConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
						sectionModalView.ui.modal.modal('hide');
					});

					wearerChangeView.sectionModal.show(sectionModalView.render());
					sectionModalView.condition.show(sectionModalConditionView);
					sectionModalView.page.show(paginationSectionView);
					//拠点絞り込み--ここまで
				});

				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});
				this.listenTo(paginationView2, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});
				this.listenTo(paginationView3, 'selected', function(pageNumber){
					fetchList_2(pageNumber);
				});
				this.listenTo(paginationView4, 'selected', function(pageNumber){
					fetchList_2(pageNumber);
				});
				this.listenTo(paginationSectionView, 'selected', function(pageNumber){
					fetchList_section(pageNumber);
				});

				this.listenTo(wearerChangeListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(wearerChangeConditionView, 'click:search', function(sortKey,order){
					modal = false;
					fetchList(1,sortKey,order);
				});

				// 前検索結果表示処理　---ここから
				// 契約No
				this.listenTo(wearerChangeConditionView, 'research:agreement_no', function(data){
					var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition({
						data: data
					});
				});
				// 性別
				this.listenTo(wearerChangeConditionView, 'research:sex', function(data){
					var sexKbnConditionView = new App.Admin.Views.SexKbnCondition({
						data: data
					});
				});
				// 拠点
				this.listenTo(wearerChangeConditionView, 'research:section', function(data){
					var sectionConditionView2 = new App.Admin.Views.SectionCondition({
						agreement_no: data["agreement_no"],
						section: data["section"]
					});
					wearerChangeConditionView.section.show(sectionConditionView2);

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
						wearerChangeView.detailModal.show(sectionModalView2.render());
						sectionModalView2.ui.modal.modal('show');
					});
					var sectionModalListItemView2 = new App.Admin.Views.SectionModalListItem();
					this.listenTo(sectionModalListListView2, 'childview:click:section_select', function(model){
						sectionConditionView2.ui.section[0].value = model.model.attributes.rntl_sect_cd;
						sectionModalView2.ui.modal.modal('hide');
					});

					wearerChangeView.sectionModal_2.show(sectionModalView2.render());
					sectionModalView2.condition.show(sectionModalConditionView2);
				});
				// 貸与パターン
				this.listenTo(wearerChangeConditionView, 'research:job_type', function(data){
					modal = false;
					var jobTypeConditionView = new App.Admin.Views.JobTypeCondition({
						agreement_no: data["agreement_no"],
						job_type: data["job_type"]
					});
				});
				// 検索結果一覧
				this.listenTo(wearerChangeConditionView, 'back:research', function(sortKey,order,page){
					fetchList_2(page,sortKey,order);
				});
				// 前検索結果表示処理　---ここまで

				// 契約No変更時の絞り込み処理 --ここから
				this.listenTo(wearerChangeConditionView, 'change:section_select', function(agreement_no){
					var sectionConditionView2 = new App.Admin.Views.SectionCondition({
						agreement_no:agreement_no,
					});
					wearerChangeConditionView.section.show(sectionConditionView2);

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
						wearerChangeView.detailModal.show(sectionModalView2.render());
						sectionModalView2.ui.modal.modal('show');
					});
					var sectionModalListItemView2 = new App.Admin.Views.SectionModalListItem();
					this.listenTo(sectionModalListListView2, 'childview:click:section_select', function(model){
						sectionConditionView2.ui.section[0].value = model.model.attributes.rntl_sect_cd;
						sectionModalView2.ui.modal.modal('hide');
					});

					wearerChangeView.sectionModal_2.show(sectionModalView2.render());
					sectionModalView2.condition.show(sectionModalConditionView2);
				});
				// 契約No変更時の絞り込み処理 --ここまで

				App.main.show(wearerChangeView);
				wearerChangeView.condition.show(wearerChangeConditionView);
				wearerChangeConditionView.agreement_no.show(agreementNoConditionView);
				//wearerChangeConditionView.section.show(sectionConditionView);
				wearerChangeConditionView.job_type.show(jobTypeConditionView);
				wearerChangeConditionView.sex_kbn.show(sexKbnConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerChange;
});
