define([
	'app',
	'./Abstract',
	'../views/Unreturn',
	'../views/UnreturnCondition',
	'../views/UnreturnListList',
	'../views/JobTypeCondition',
	'../views/DetailModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminUnreturn",
	"entities/models/AdminUnreturnListCondition",
	"entities/collections/AdminUnreturnListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Unreturn = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('unreturn');
				var pagerModel = new App.Entities.Models.Pager();


				var unreturnModel = null;
				var detailModalView = new App.Admin.Views.DetailModal();
				var unreturnView = new App.Admin.Views.Unreturn();
				var unreturnListListCollection = new App.Entities.Collections.AdminUnreturnListList();
				
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				
				var unreturnListConditionModel = new App.Entities.Models.AdminUnreturnListCondition();
				var unreturnConditionView = new App.Admin.Views.UnreturnCondition({
					model:unreturnListConditionModel
				});
				var unreturnListListView = new App.Admin.Views.UnreturnListList({
					collection: unreturnListListCollection,
					model:unreturnListConditionModel,
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
					unreturnListListView.fetch(unreturnListConditionModel);
					unreturnView.listTable.show(unreturnListListView);
				};
				this.listenTo(unreturnListListView, 'childview:click:a', function(view, model){
					unreturnModel = new App.Entities.Models.AdminUnreturn({no:model.get('order_req_no')});
					detailModalView.fetchDetail(unreturnModel);
				});
				this.listenTo(detailModalView, 'fetched', function(){
					unreturnView.detailModal.show(detailModalView.render());
					detailModalView.ui.modal.modal('show');
				});				
				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});
				this.listenTo(unreturnListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(unreturnConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});
				App.main.show(unreturnView);
				unreturnView.page.show(paginationView);
				unreturnView.condition.show(unreturnConditionView);
				unreturnConditionView.job_type.show(jobTypeConditionView);
			}
		});
	});
	return App.Admin.Controllers.Unreturn;
});
