define([
	'app',
	'./Abstract',
	'../views/Stock',
	'../views/StockCondition',
	'../views/StockListList',
	'../views/AgreementNoCondition',
	'../views/JobTypeZaikoCondition',
	'../views/ItemZaikoCondition',
	'../views/ItemColorZaikoCondition',
	'../views/DetailModal',
	'../views/Pagination',
	'../views/CsvDownload',
	"entities/models/Pager",
	"entities/models/AdminStock",
	"entities/models/AdminStockListCondition",
	"entities/models/AdminCsvDownloadCondition",
	"entities/collections/AdminStockListList",
	"entities/collections/AdminCsvDownload",
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

				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();
				var jobTypeZaikoConditionView = new App.Admin.Views.JobTypeZaikoCondition();
				var itemZaikoConditionView = new App.Admin.Views.ItemZaikoCondition();
				var itemColorZaikoConditionView = new App.Admin.Views.ItemColorZaikoCondition();

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
				var paginationView2 = new App.Admin.Views.Pagination({model: pagerModel});
				var csvDownloadView = new App.Admin.Views.CsvDownload();

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
					stockView.page.show(paginationView);
					stockView.page_2.show(paginationView2);
					stockView.csv_download.show(csvDownloadView);
				};

				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList(pageNumber);
				});
				this.listenTo(paginationView2, 'selected', function(pageNumber){
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
				this.listenTo(csvDownloadView, 'click:download_btn', function(cond_map){
					csvDownloadView.fetch(cond_map);
				});

				App.main.show(stockView);
				stockView.page.show(paginationView);
				stockView.condition.show(stockConditionView);
				stockConditionView.agreement_no.show(agreementNoConditionView);
				Sleep(0.02);
				stockConditionView.job_type_zaiko.show(jobTypeZaikoConditionView);
				Sleep(0.01);
				stockConditionView.item.show(itemZaikoConditionView);
				Sleep(0.01);
				stockConditionView.item_color.show(itemColorZaikoConditionView);
			}
		});
	});
	return App.Admin.Controllers.Stock;
});
