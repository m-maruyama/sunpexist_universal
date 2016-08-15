define([
	'app',
	'./Abstract',
	'../views/Unreturned',
	'../views/UnreturnedCondition',
	'../views/UnreturnedListList',
	'../views/JobTypeCondition',
	'../views/DetailModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminUnreturned",
	"entities/models/AdminUnreturnedListCondition",
	"entities/collections/AdminUnreturnedListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Unreturned = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('unreturned');
				var pagerModel = new App.Entities.Models.Pager();

				var unreturnedModel = null;
				var detailModalView = new App.Admin.Views.DetailModal();
				var unreturnedView = new App.Admin.Views.Unreturned();
				var unreturnedListListCollection = new App.Entities.Collections.AdminUnreturnedListList();
				
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				
				var unreturnedListConditionModel = new App.Entities.Models.AdminUnreturnedListCondition();
				var unreturnedConditionView = new App.Admin.Views.UnreturnedCondition({
					model:unreturnedListConditionModel,
					pagerModel: pagerModel
				});
				var unreturnedListListView = new App.Admin.Views.UnreturnedListList({
					collection: unreturnedListListCollection,
					model:unreturnedListConditionModel,
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
					unreturnedListListView.fetch(unreturnedListConditionModel);
					unreturnedView.listTable.show(unreturnedListListView);
				};
				this.listenTo(unreturnedListListView, 'childview:click:a', function(view, model){
					unreturnedModel = new App.Entities.Models.AdminUnreturned({no:model.get('order_req_no')});
					detailModalView.fetchDetail(unreturnedModel);
				});
				this.listenTo(detailModalView, 'fetched', function(){
					unreturnedView.detailModal.show(detailModalView.render());
					detailModalView.ui.modal.modal('show');
				});				
				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});
				this.listenTo(unreturnedListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(unreturnedConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});
				this.listenTo(unreturnedConditionView, 'click:download', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				App.main.show(unreturnedView);
				unreturnedView.page.show(paginationView);
				unreturnedView.condition.show(unreturnedConditionView);
				unreturnedConditionView.job_type.show(jobTypeConditionView);
			}
		});
	});
	return App.Admin.Controllers.Unreturned;
});
