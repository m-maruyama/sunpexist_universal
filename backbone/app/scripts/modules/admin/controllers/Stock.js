define([
	'app',
	'./Abstract',
	'../views/Stock',
	'../views/StockCondition',
	'../views/StockListList',
	'../views/JobTypeZaikoCondition',
	'../views/DetailModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminStock",
	"entities/models/AdminStockListCondition",
	"entities/collections/AdminStockListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Stock = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('stock');
				var pagerModel = new App.Entities.Models.Pager();

				var stockModel = null;
				var stockView = new App.Admin.Views.Stock();
				var stockListListCollection = new App.Entities.Collections.AdminStockListList();
				
				var jobTypeConditionView = new App.Admin.Views.JobTypeZaikoCondition();
				
				var stockListConditionModel = new App.Entities.Models.AdminStockListCondition();
				var stockConditionView = new App.Admin.Views.StockCondition({
					collection: stockListListCollection,
					model:stockListConditionModel,
					pagerModel: pagerModel
				});
				var stockListListView = new App.Admin.Views.StockListList({
					collection: stockListListCollection,
					model:stockListConditionModel,
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
					stockListListView.fetch(stockListConditionModel);
					stockView.listTable.show(stockListListView);
				};
				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});

				this.listenTo(stockListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(stockConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});
				this.listenTo(stockConditionView, 'click:download', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				App.main.show(stockView);
				stockView.page.show(paginationView);
				stockView.condition.show(stockConditionView);
				stockConditionView.job_type.show(jobTypeConditionView);
			}
		});
	});
	return App.Admin.Controllers.Stock;
});
