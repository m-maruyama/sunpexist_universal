define([
	'app',
	'./Abstract',
	'../views/Receive',
	'../views/ReceiveCondition',
	'../views/ReceiveListList',
	'../views/ReceiveButton',
	'../views/JobTypeCondition',
	'../views/DetailModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminReceive",
	"entities/models/AdminReceiveListCondition",
	"entities/collections/AdminReceiveListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Receive = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('receive');
				var pagerModel = new App.Entities.Models.Pager();


				var receiveModel = null;
				var detailModalView = new App.Admin.Views.DetailModal();
				var receiveView = new App.Admin.Views.Receive();
				var receiveListListCollection = new App.Entities.Collections.AdminReceiveListList();
				
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				
				var receiveListConditionModel = new App.Entities.Models.AdminReceiveListCondition();
				var receiveConditionView = new App.Admin.Views.ReceiveCondition({
					model:receiveListConditionModel
				});
				var receiveListListView = new App.Admin.Views.ReceiveListList({
					collection: receiveListListCollection,
					model:receiveListConditionModel,
					pagerModel: pagerModel
				});
				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});

				var receiveListItemModel = new App.Entities.Models.AdminReceiveListItem();
				var receiveButtonView = new App.Admin.Views.ReceiveButton({
					collection: receiveListListCollection,
					model:receiveListConditionModel,
					pagerModel: pagerModel
				});
				var fetchList = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					receiveListListView.fetch(receiveListConditionModel);
					receiveView.listTable.show(receiveListListView);
					receiveView.receive_button.show(receiveButtonView);
				};

				this.listenTo(receiveListListView, 'childview:click:a', function(view, model){
					receiveModel = new App.Entities.Models.AdminReceive({no:model.get('order_req_no')});
					detailModalView.fetchDetail(receiveModel);
				});
				
				this.listenTo(detailModalView, 'fetched', function(){
					receiveView.detailModal.show(detailModalView.render());
					detailModalView.ui.modal.modal('show');
				});
				
				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});

				this.listenTo(receiveListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});

				this.listenTo(receiveButtonView, 'research', function(sortKey,order,pageNumber){
					fetchList(pageNumber,sortKey,order);
				});
				this.listenTo(receiveConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});
				this.listenTo(receiveView, 'updated', function(){
					addFlag = false;
					fetchList();
					receiveView = new App.Admin.Views.Receive({
						collection: receiveListListCollection
					});
				});
				//遷移するときのアラート
				var addFlag = false;

				this.listenTo(receiveListListCollection, 'sync', function(){
					addFlag = false;
				});
				$(window).on('beforeunload', function() {
					if (addFlag){
						return '更新が完了していません。ページ遷移をしますか？';
					}
					return;
				});
				
				App.main.show(receiveView);
				receiveView.page.show(paginationView);
				receiveView.condition.show(receiveConditionView);
				receiveConditionView.job_type.show(jobTypeConditionView);
			}
		});
	});
	return App.Admin.Controllers.Receive;
});
