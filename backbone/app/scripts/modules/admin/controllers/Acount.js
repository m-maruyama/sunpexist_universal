define([
	'app',
	'./Abstract',
	'../views/Acount',
	//コンディション追加
	'../views/AcountCondition',
	'../views/CorporateIdCondition',
	'../views/AgreementNoCondition',
	'../views/AcountListList',
	'../views/AcountModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminAcount",
	"entities/models/AdminAcountListCondition",
	"entities/collections/AdminAcountListList",
	"entities/collections/AdminAcountModal",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Acount = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('acount');
				var pagerModel = new App.Entities.Models.Pager();

				var acountModel = null;
				var acountListListCollection = new App.Entities.Collections.AdminAcountListList();
				var AdminAcountModal = new App.Entities.Collections.AdminAcountModal();

				var acountModalView = new App.Admin.Views.AcountModal({
					collection: AdminAcountModal
				});

				//追加
				var corporateIdConditionView = new App.Admin.Views.CorporateIdCondition();
				//追加
				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();




				var acountListConditionModel = new App.Entities.Models.AdminAcountListCondition();
				//コンディション追加
				console.log(acountListConditionModel.toJSON());
				var acountConditionView = new App.Admin.Views.AcountCondition({
					collection: acountListListCollection,
					model:acountListConditionModel,//追加
					pagerModel: pagerModel
				});

				var acountView = new App.Admin.Views.Acount({
					model:acountListConditionModel
				});
				var acountListListView = new App.Admin.Views.AcountListList({
					collection: acountListListCollection,
					model:acountListConditionModel,//追加
					pagerModel: pagerModel
				});
				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});
				var paginationView2 = new App.Admin.Views.Pagination({model: pagerModel});

				var fetchList = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					acountListListView.fetch(acountListConditionModel);
					acountView.listTable.show(acountListListView);
					acountView.page.show(paginationView);
					acountView.page_2.show(paginationView2);
				};





				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList(pageNumber);
				});
				this.listenTo(paginationView2, 'selected', function(pageNumber){
						fetchList(pageNumber);
				});

				this.listenTo(acountListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});

				//検索ボタンを押したらfetchwを追加
				this.listenTo(acountConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});

				this.listenTo(acountModalView, 'reload', function(){
					fetchList();
				});

				this.listenTo(acountView, 'updated', function(){
					addFlag = false;
					fetchList();
					acountView = new App.Admin.Views.Acount({
						collection: acountListListCollection
					});
				});

				this.listenTo(acountListListView, 'childview:click:a', function(view, model, display){
					acountModalView.showMessage(model,display);
				});


				//遷移するときのアラート
				var addFlag = false;

				this.listenTo(acountModalView, 'add', function(){
					addFlag = true;
				});
				this.listenTo(acountModalView, 'delete', function(){
					addFlag = false;
				});
				this.listenTo(acountListListCollection, 'sync', function(){
					addFlag = false;
				});
				// $(window).on('beforeunload', function() {
					// if (addFlag){
						// return '更新が完了していません。ページ遷移をしますか？';
					// }
					// return;
				// });
				// var fetchList = function(pageNumber){
					// if(addFlag && !window.confirm('更新が完了していません。ページ遷移をしますか？')){
						// return;
					// }
					// //acountListListView.empty();
					// if(pageNumber){
						// pagerModel.set('page_number', pageNumber);
					// }
					// acountListListView.fetch(acountListConditionModel);
				// };

				App.main.show(acountView);
				//コンディション追加
				acountView.condition.show(acountConditionView);
				acountConditionView.corporate_id.show(corporateIdConditionView);
				acountConditionView.agreement_no.show(agreementNoConditionView);
				acountView.page.show(paginationView);
				acountView.listTable.show(acountListListView);
				acountView.acountModal.show(acountModalView);
				//fetchList(); 実行
			}
		});
	});
	return App.Admin.Controllers.Acount;
});
