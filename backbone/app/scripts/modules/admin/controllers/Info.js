define([
	'app',
	'./Abstract',
	'../views/Info',
	'../views/InfoListList',
	'../views/InfoModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminInfo",
	"entities/models/AdminInfoListCondition",
	"entities/collections/AdminInfoListList",
	"entities/collections/AdminInfoModal",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Info = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('info');
				var pagerModel = new App.Entities.Models.Pager();

				var infoModel = null;
				var infoListListCollection = new App.Entities.Collections.AdminInfoListList();
				var AdminInfoModal = new App.Entities.Collections.AdminInfoModal();
				
				var infoModalView = new App.Admin.Views.InfoModal({
					collection: AdminInfoModal
				});
				var infoListConditionModel = new App.Entities.Models.AdminInfoListCondition();
				var infoView = new App.Admin.Views.Info({
					model:infoListConditionModel
				});
				var infoListListView = new App.Admin.Views.InfoListList({
					collection: infoListListCollection,
					pagerModel: pagerModel
				});
				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});

				var fetchList = function(pageNumber){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					infoListListView.fetch(infoListConditionModel);
				};

				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});
				
				this.listenTo(infoModalView, 'reload', function(){
					fetchList();
				});
				
				this.listenTo(infoView, 'updated', function(){
					addFlag = false;
					fetchList();
					infoView = new App.Admin.Views.Info({
						collection: infoListListCollection
					});
				});

				this.listenTo(infoListListView, 'childview:click:a', function(view, model, display){
					infoModalView.showMessage(model,display);
				});
				
				//遷移するときのアラート
				var addFlag = false;

				this.listenTo(infoModalView, 'add', function(){
					addFlag = true;
				});
				this.listenTo(infoListListCollection, 'sync', function(){
					addFlag = false;
				});
				App.main.show(infoView);
				infoView.page.show(paginationView);
				infoView.listTable.show(infoListListView);
				infoView.infoModal.show(infoModalView);
				fetchList();
			}
		});
	});
	return App.Admin.Controllers.Info;
});
