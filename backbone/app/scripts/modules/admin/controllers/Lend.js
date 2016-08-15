define([
	'app',
	'./Abstract',
	'../views/Lend',
	'../views/LendCondition',
	'../views/LendListList',
	'../views/JobTypeCondition',
	'../views/DetailModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminLend",
	"entities/models/AdminLendListCondition",
	"entities/collections/AdminLendListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Lend = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('lend');
				var pagerModel = new App.Entities.Models.Pager();

				var lendModel = null;
				var detailModalView = new App.Admin.Views.DetailModal();
				var lendView = new App.Admin.Views.Lend();
				var lendListListCollection = new App.Entities.Collections.AdminLendListList();
				
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				
				var lendListConditionModel = new App.Entities.Models.AdminLendListCondition();
				var lendConditionView = new App.Admin.Views.LendCondition({
					model:lendListConditionModel,
					pagerModel: pagerModel
				});
				var lendListListView = new App.Admin.Views.LendListList({
					collection: lendListListCollection,
					model:lendListConditionModel,
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
					lendListListView.fetch(lendListConditionModel);
					lendView.listTable.show(lendListListView);
				};

				this.listenTo(lendListListView, 'childview:click:a', function(view, model){
					lendModel = new App.Entities.Models.AdminLend({no:model.get('order_req_no')});
					detailModalView.fetchDetail(lendModel);
				});
				
				this.listenTo(detailModalView, 'fetched', function(){
					lendView.detailModal.show(detailModalView.render());
					detailModalView.ui.modal.modal('show');
				});
				
				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});

				this.listenTo(lendListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});

				this.listenTo(lendConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});
				
				// this.listenTo(lendConditionView, 'click:download', function(sortKey,order){
					// fetchList(null,sortKey,order);
				// });
				
				App.main.show(lendView);
				lendView.page.show(paginationView);
				lendView.condition.show(lendConditionView);
				lendConditionView.job_type.show(jobTypeConditionView);
			}
		});
	});
	return App.Admin.Controllers.Lend;
});
