define([
	'app',
	'./Abstract',
	'../views/SectionModal',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionCondition',
	'../views/JobTypeCondition',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminSectionModal",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.SectionModal = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('SectionModal');
				var pagerModel = new App.Entities.Models.Pager();


				var sectionModalModel = null;
				var sectionModalView = new App.Admin.Views.SectionModal();
				var sectionListListCollection = new App.Entities.Collections.AdminSectionModalListList();

				var sectionModalListConditionModel = new App.Entities.Models.AdminSectionModalListCondition();
				var sectionModalConditionView = new App.Admin.Views.SectionModalCondition({
					model:sectionModalListConditionModel
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
					sectionModalListListView.fetch(sectionModalListConditionModel);
					sectionModalView.listTable.show(sectionModalListListView);
				};
				this.listenTo(sectionModalListListView, 'childview:click:a', function(view, model){
					sectionModalModel = new App.Entities.Models.AdminSectionModal({no:model.get('order_req_no')});
					detailModalView.fetchDetail(sectionModalModel);
				});

				this.listenTo(sectionModalView, 'click:search', function(sortKey, order){
					fetchList_section(1,sortKey,order);
				});

				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});
				this.listenTo(sectionModalListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(sectionModalConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});
				App.main.show(sectionModalView);
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});
	return App.Admin.Controllers.SectionModal;
});
