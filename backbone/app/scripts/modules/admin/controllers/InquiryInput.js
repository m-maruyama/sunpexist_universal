define([
	'app',
	'./Abstract',
	'../views/InquiryInput',
	'../views/InquiryInputCondition',
	'../views/AgreementNoCondition',
	'../views/SectionCondition',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminInquiryInput",
	"entities/models/AdminInquiryInputListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminInquiryInputListList",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.InquiryInput = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('inquiryInput');
				var pagerModel = new App.Entities.Models.Pager();
				var pagerModel2 = new App.Entities.Models.Pager();
				var pagerModel3 = new App.Entities.Models.Pager();

				var modal = false;
				var inquiryInputModel = null;
				var inquiryInputView = new App.Admin.Views.InquiryInput();
				var inquiryInputListListCollection = new App.Entities.Collections.AdminInquiryInputListList();

				var sectionConditionView = new App.Admin.Views.SectionCondition({
					agreement_no: "",
					section: "",
					not_all_flg: "1"
				});

				var inquiryInputListConditionModel = new App.Entities.Models.AdminInquiryInputListCondition();
				var inquiryInputConditionView = new App.Admin.Views.InquiryInputCondition({
					model:inquiryInputListConditionModel
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

					inquiryInputListListView.fetch(inquiryInputListConditionModel);
					inquiryInputView.listTable.show(inquiryInputListListView);
					inquiryInputView.page.show(paginationView);
					inquiryInputView.page_2.show(paginationView2);
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
					// inquiryInputView.detailModal.show();
					// sectionModalView.render();
					inquiryInputView.detailModal.show(sectionModalView.render());
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

				this.listenTo(inquiryInputConditionView, 'click:search', function(sortKey, order, page){
					modal = false;
					fetchList(page, sortKey, order);
				});

				// 契約No変更時の絞り込み処理 --ここから
				this.listenTo(inquiryInputConditionView, 'change:section_select', function(agreement_no){
					var sectionConditionView2 = new App.Admin.Views.SectionCondition({
						agreement_no:agreement_no,
					});
					inquiryInputConditionView.section.show(sectionConditionView2);

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
						inquiryInputView.detailModal.show(sectionModalView2.render());
						sectionModalView2.ui.modal.modal('show');
					});
					var sectionModalListItemView2 = new App.Admin.Views.SectionModalListItem();
					this.listenTo(sectionModalListListView2, 'childview:click:section_select', function(model){
						sectionConditionView2.ui.section[0].value = model.model.attributes.rntl_sect_cd;
						sectionModalView2.ui.modal.modal('hide');
					});

					inquiryInputView.sectionModal_2.show(sectionModalView2.render());
					sectionModalView2.condition.show(sectionModalConditionView2);
				});
				// 契約No変更時の絞り込み処理 --ここまで

				App.main.show(inquiryInputView);
				inquiryInputView.condition.show(inquiryInputConditionView);
				Sleep(0.01);
				inquiryInputConditionView.section.show(sectionConditionView);
				Sleep(0.01);
				inquiryInputView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationSectionView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});

	return App.Admin.Controllers.InquiryInput;
});
