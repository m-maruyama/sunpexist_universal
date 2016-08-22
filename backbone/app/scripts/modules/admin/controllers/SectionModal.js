define([
	'app',
	'./Abstract',
	'../views/SectionModal',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminSectionModal",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Section = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('section');
				var pagerModel = new App.Entities.Models.Pager();

				var sectionModel = null;
				var sectionModalView = new App.Admin.Views.SectionModal();
				var sectionModalListListCollection = new App.Entities.Collections.AdminSectionModalListList();

				var sectionModalListConditionModel = new App.Entities.Models.AdminSectionModalListCondition();
				var sectionConditionView = new App.Admin.Views.SectionCondition({
					model:sectionModalListConditionModel,
					pagerModel: pagerModel
				});
				var sectionModalListListView = new App.Admin.Views.SectionModalListList({
					collection: sectionModalListListCollection,
					model:sectionModalListConditionModel,
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
					sectionView.listTable.show(sectionModalListListView);
				};

				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});

				this.listenTo(sectionModalListListView, 'sort', function(sortKey,order){
					sectionModalListConditionModel.set('mode',null);
					fetchList(null,sortKey,order);
				});

				this.listenTo(sectionConditionView, 'click:search', function(sortKey,order){
					sectionModalListConditionModel.set('mode',null);
					fetchList(1,sortKey,order);
				});

				App.main.show(sectionView);
				sectionView.page.show(paginationView);
				sectionView.condition.show(sectionConditionView);
			}
		});
	});
	return App.Admin.Controllers.Section;
});
