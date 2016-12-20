define([
	'app',
	'./Abstract',
	'../views/ManpowerInfo',
	'../views/ManpowerInfoCondition',
	'../views/ManpowerInfoListList',
	'../views/AgreementNoCondition',
	'../views/SectionCondition',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/DetailModal',
	'../views/ManpowerInfoDetailModal',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminManpowerInfo",
	"entities/models/AdminManpowerInfoListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminManpowerInfoListList",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.ManpowerInfo = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('manpowerInfo');
				var pagerModel = new App.Entities.Models.Pager();
				var pagerModel2 = new App.Entities.Models.Pager();
				var pagerModel3 = new App.Entities.Models.Pager();

				var modal = false;
				var manpowerInfoModel = null;
				var detailModalView = new App.Admin.Views.DetailModal();
				var manpowerInfoView = new App.Admin.Views.ManpowerInfo();
				var manpowerInfoListListCollection = new App.Entities.Collections.AdminManpowerInfoListList();

				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();
				var sectionConditionView = new App.Admin.Views.SectionCondition();

				var manpowerInfoListConditionModel = new App.Entities.Models.AdminManpowerInfoListCondition();
				var manpowerInfoConditionView = new App.Admin.Views.ManpowerInfoCondition({
					model:manpowerInfoListConditionModel
				});
				var manpowerInfoListListView = new App.Admin.Views.ManpowerInfoListList({
					collection: manpowerInfoListListCollection,
					model:manpowerInfoListConditionModel,//追加
					pagerModel: pagerModel
				});

				// 人員明細詳細モーダル --ここから
				this.listenTo(manpowerInfoListListView, 'childview:click:Detail', function(view, agreement_no, rntl_sect_cd, rntl_sect_name, yyyymm, staff_total){
					var manpowerInfoDetailModalView = new App.Admin.Views.ManpowerInfoDetailModal({
						agreement_no: agreement_no,
						rntl_sect_cd: rntl_sect_cd,
						rntl_sect_name: rntl_sect_name,
						yyyymm: yyyymm,
						staff_total: staff_total
					});
					manpowerInfoDetailModalView.fetchDetail();

					this.listenTo(manpowerInfoDetailModalView, 'fetched', function(){
						manpowerInfoView.manpower_detail_modal.show(manpowerInfoDetailModalView.render());
						manpowerInfoDetailModalView.ui.modal.modal('show');
					});
				});
				// 人員明細詳細モーダル --ここまで

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
					manpowerInfoListListView.fetch(manpowerInfoListConditionModel);
					manpowerInfoView.listTable.show(manpowerInfoListListView);
					manpowerInfoView.page.show(paginationView);
					manpowerInfoView.page_2.show(paginationView2);
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
					// manpowerInfoView.detailModal.show();
					// sectionModalView.render();
					manpowerInfoView.detailModal.show(sectionModalView.render());
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

				this.listenTo(manpowerInfoListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(manpowerInfoConditionView, 'click:search', function(sortKey,order){
					modal = false;
					fetchList(1,sortKey,order);
				});

				// 契約No変更時の絞り込み処理 --ここから
				this.listenTo(manpowerInfoConditionView, 'change:section_select', function(agreement_no){
					var sectionConditionView2 = new App.Admin.Views.SectionCondition({
						agreement_no:agreement_no,
					});
					manpowerInfoConditionView.section.show(sectionConditionView2);

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
						manpowerInfoView.detailModal.show(sectionModalView2.render());
						sectionModalView2.ui.modal.modal('show');
					});
					var sectionModalListItemView2 = new App.Admin.Views.SectionModalListItem();
					this.listenTo(sectionModalListListView2, 'childview:click:section_select', function(model){
						sectionConditionView2.ui.section[0].value = model.model.attributes.rntl_sect_cd;
						sectionModalView2.ui.modal.modal('hide');
					});

					manpowerInfoView.sectionModal_2.show(sectionModalView2.render());
					sectionModalView2.condition.show(sectionModalConditionView2);
				});
				// 契約No変更時の絞り込み処理 --ここまで

				App.main.show(manpowerInfoView);
				manpowerInfoView.condition.show(manpowerInfoConditionView);
				manpowerInfoConditionView.agreement_no.show(agreementNoConditionView);
				Sleep(0.02);
				manpowerInfoConditionView.section.show(sectionConditionView);
				Sleep(0.01);
				manpowerInfoView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationSectionView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});

	return App.Admin.Controllers.ManpowerInfo;
});
