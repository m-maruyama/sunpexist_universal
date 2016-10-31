define([
	'app',
	'./Abstract',
	'../views/Info',
	'../views/InfoListList',
	'../views/InfoAddModal',
	'../views/InfoEditModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminInfo",
	"entities/models/AdminInfoListCondition",
	"entities/models/AdminInfoAddModal",
	"entities/models/AdminInfoEditModal",
	"entities/collections/AdminInfoListList",
	"entities/collections/AdminInfoAddModal",
	"entities/collections/AdminInfoEditModal",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Info = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('info');
				var addFlag = true;
				var pagerModel = new App.Entities.Models.Pager();

				var infoListListCollection = new App.Entities.Collections.AdminInfoListList();
				var AdminInfoAddModal = new App.Entities.Collections.AdminInfoAddModal();
				var AdminInfoEditModal = new App.Entities.Collections.AdminInfoEditModal();

				var infoAddModalView = new App.Admin.Views.InfoAddModal({
					collection: AdminInfoAddModal
				});
				var infoEditModalView = new App.Admin.Views.InfoEditModal({
					collection: AdminInfoEditModal
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
				var paginationView2 = new App.Admin.Views.Pagination({model: pagerModel});

				var fetchList = function(pageNumber){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					infoListListView.fetch(infoListConditionModel);
				};

				this.listenTo(paginationView, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});
				this.listenTo(paginationView2, 'selected', function(pageNumber){
					fetchList(pageNumber);
				});

				// お知らせ追加モーダル処理 ここから
				this.listenTo(infoView, 'click:addBtn', function(){
					infoAddModalView.addShow();
					infoView.infoAddModal.show(infoAddModalView);
					infoAddModalView.ui.modal.modal('show');
				});
				this.listenTo(infoAddModalView, 'complete', function(){
					fetchList();
				});
				// お知らせ追加モーダル処理 ここまで

				// お知らせ編集モーダル処理 ここから
				this.listenTo(infoListListView, 'childview:click:editBtn', function(id){
					var infoEditModalView = new App.Admin.Views.InfoEditModal({
						id: id
					});
					infoView.infoEditModal.show(infoEditModalView);
					infoEditModalView.ui.modal.modal('show');

					this.listenTo(infoEditModalView, 'complete', function(){
						fetchList();
					});
				});
				// お知らせ編集モーダル処理 ここまで

				this.listenTo(infoView, 'updated', function(){
					addFlag = false;
					fetchList();
					infoView = new App.Admin.Views.Info({
						collection: infoListListCollection
					});
				});

				this.listenTo(infoListListCollection, 'sync', function(){
					addFlag = false;
				});
				App.main.show(infoView);
				infoView.page.show(paginationView);
				infoView.page_2.show(paginationView2);
				infoView.listTable.show(infoListListView);
				fetchList();
			}
		});
	});
	return App.Admin.Controllers.Info;
});
