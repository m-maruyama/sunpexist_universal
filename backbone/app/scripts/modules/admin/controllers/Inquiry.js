define([
	'app',
	'./Abstract',
	'../views/Inquiry',
	'../views/InquiryCondition',
	'../views/InquiryListList',
	'../views/AgreementNoCondition',
	'../views/SectionCondition',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminInquiry",
	"entities/models/AdminInquiryListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminInquiryListList",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Inquiry = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('inquiry');
				var pagerModel = new App.Entities.Models.Pager();
				var pagerModel2 = new App.Entities.Models.Pager();
				var pagerModel3 = new App.Entities.Models.Pager();

				var modal = false;
				var inquiryModel = null;
				var inquiryView = new App.Admin.Views.Inquiry();
				var inquiryListListCollection = new App.Entities.Collections.AdminInquiryListList();
				var sectionListListCollection = new App.Entities.Collections.AdminSectionModalListList();

				var sectionConditionView = new App.Admin.Views.SectionCondition({
					not_all_flg: "1",
					window_name: "inquiry"
				});

				var inquiryListConditionModel = new App.Entities.Models.AdminInquiryListCondition();
				var inquiryConditionView = new App.Admin.Views.InquiryCondition({
					model:inquiryListConditionModel
				});
				var inquiryListListView = new App.Admin.Views.InquiryListList({
					collection: inquiryListListCollection,
					model:inquiryListConditionModel,
					pagerModel: pagerModel
				});

				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationView2 = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationSectionView = new App.Admin.Views.Pagination({model: pagerModel2});

				var fetchList = function(pageNumber, sortKey, order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}

					inquiryListListView.fetch(inquiryListConditionModel);
					inquiryView.listTable.show(inquiryListListView);
					inquiryView.page.show(paginationView);
					inquiryView.page_2.show(paginationView2);
				};
				var fetchList_2 = function(pageNumber, sortKey, order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}

					inquiryListListView.fetch(inquiryListConditionModel);
					inquiryView.listTable.show(inquiryListListView);
					inquiryView.page.show(paginationView);
					inquiryView.page_2.show(paginationView2);
				};

				this.listenTo(inquiryConditionView, 'first:section', function() {
					var sectionConditionView = new App.Admin.Views.SectionCondition();
					inquiryConditionView.section.show(sectionConditionView);

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
						// inquiryView.detailModal.show();
						// sectionModalView.render();
						sectionModalView.ui.modal.modal('show');
					});
					var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
					this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
						sectionConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
						sectionModalView.ui.modal.modal('hide');
					});

					inquiryView.sectionModal.show(sectionModalView.render());
					sectionModalView.condition.show(sectionModalConditionView);
					sectionModalView.page.show(paginationSectionView);
					//拠点絞り込み--ここまで
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
				this.listenTo(paginationSectionView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});

				this.listenTo(inquiryListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(inquiryConditionView, 'click:search', function(sortKey, order, page){
					modal = false;
					fetchList(page, sortKey, order);
				});

				// 前検索結果表示処理　---ここから
				// 拠点
				this.listenTo(inquiryConditionView, 'research:section', function(data){
					var sectionConditionView2 = new App.Admin.Views.SectionCondition({
						agreement_no: data["agreement_no"],
						section: data["section"]
					});
					inquiryConditionView.section.show(sectionConditionView2);

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
						inquiryView.detailModal.show(sectionModalView2.render());
						sectionModalView2.ui.modal.modal('show');
					});
					var sectionModalListItemView2 = new App.Admin.Views.SectionModalListItem();
					this.listenTo(sectionModalListListView2, 'childview:click:section_select', function(model){
						sectionConditionView2.ui.section[0].value = model.model.attributes.rntl_sect_cd;
						sectionModalView2.ui.modal.modal('hide');
					});

					inquiryView.sectionModal_2.show(sectionModalView2.render());
					sectionModalView2.condition.show(sectionModalConditionView2);
				});
				// 検索結果一覧
				this.listenTo(inquiryConditionView, 'back:research', function(sortKey,order,page){
					fetchList_2(page,sortKey,order);
				});
				// 前検索結果表示処理　---ここまで

				// 契約No変更時の絞り込み処理 --ここから
				this.listenTo(inquiryConditionView, 'change:section_select', function(agreement_no){
					var sectionConditionView2 = new App.Admin.Views.SectionCondition({
						agreement_no:agreement_no,
					});
					inquiryConditionView.section.show(sectionConditionView2);

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
						inquiryView.detailModal.show(sectionModalView2.render());
						sectionModalView2.ui.modal.modal('show');
					});
					var sectionModalListItemView2 = new App.Admin.Views.SectionModalListItem();
					this.listenTo(sectionModalListListView2, 'childview:click:section_select', function(model){
						sectionConditionView2.ui.section[0].value = model.model.attributes.rntl_sect_cd;
						sectionModalView2.ui.modal.modal('hide');
					});

					inquiryView.sectionModal_2.show(sectionModalView2.render());
					sectionModalView2.condition.show(sectionModalConditionView2);
				});
				// 契約No変更時の絞り込み処理 --ここまで

				App.main.show(inquiryView);
				inquiryView.condition.show(inquiryConditionView);
			}
		});
	});

	return App.Admin.Controllers.Inquiry;
});
