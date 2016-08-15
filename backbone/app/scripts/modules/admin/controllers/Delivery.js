define([
	'app',
	'./Abstract',
	'../views/Delivery',
	'../views/DeliveryCondition',
	'../views/DeliveryListList',
	'../views/JobTypeCondition',
	'../views/DetailModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminDelivery",
	"entities/models/AdminDeliveryListCondition",
	"entities/collections/AdminDeliveryListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Delivery = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('delivery');
				var pagerModel = new App.Entities.Models.Pager();

				var deliveryModel = null;
				var detailModalView = new App.Admin.Views.DetailModal();
				var deliveryView = new App.Admin.Views.Delivery();
				var deliveryListListCollection = new App.Entities.Collections.AdminDeliveryListList();
				
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				
				var deliveryListConditionModel = new App.Entities.Models.AdminDeliveryListCondition();
				var deliveryConditionView = new App.Admin.Views.DeliveryCondition({
					model:deliveryListConditionModel,
					pagerModel: pagerModel
				});
				var deliveryListListView = new App.Admin.Views.DeliveryListList({
					collection: deliveryListListCollection,
					model:deliveryListConditionModel,
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
					deliveryListListView.fetch(deliveryListConditionModel);
					deliveryView.listTable.show(deliveryListListView);
				};

				this.listenTo(deliveryListListView, 'childview:click:a', function(view, model){
					deliveryModel = new App.Entities.Models.AdminDelivery({no:model.get('order_req_no')});
					detailModalView.fetchDetail(deliveryModel);
				});
				
				this.listenTo(detailModalView, 'fetched', function(){
					deliveryView.detailModal.show(detailModalView.render());
					detailModalView.ui.modal.modal('show');
				});
				
				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});

				this.listenTo(deliveryListListView, 'sort', function(sortKey,order){
					deliveryListConditionModel.set('mode',null);
					fetchList(null,sortKey,order);
				});

				this.listenTo(deliveryConditionView, 'click:search', function(sortKey,order){
					deliveryListConditionModel.set('mode',null);
					fetchList(1,sortKey,order);
				});
				
				App.main.show(deliveryView);
				deliveryView.page.show(paginationView);
				deliveryView.condition.show(deliveryConditionView);
				deliveryConditionView.job_type.show(jobTypeConditionView);
			}
		});
	});
	return App.Admin.Controllers.Delivery;
});
