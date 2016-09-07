define([
	'app',
	'./Abstract',
	'../views/account',
	//コンディション追加
	'../views/accountCondition',
	'../views/CorporateIdCondition',
	'../views/AgreementNoCondition',
	'../views/accountListList',
	'../views/accountModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/Adminaccount",
	"entities/models/AdminaccountListCondition",
	"entities/collections/AdminaccountListList",
	"entities/collections/AdminaccountModal",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.account = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('account');
				var pagerModel = new App.Entities.Models.Pager();

				var accountModel = null;
				var accountListListCollection = new App.Entities.Collections.AdminaccountListList();
				var AdminaccountModal = new App.Entities.Collections.AdminaccountModal();

				var accountModalView = new App.Admin.Views.accountModal({
					collection: AdminaccountModal
				});

				//追加
				var corporateIdConditionView = new App.Admin.Views.CorporateIdCondition();
				//追加
				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();




				var accountListConditionModel = new App.Entities.Models.AdminaccountListCondition();
				//コンディション追加
				console.log(accountListConditionModel.toJSON());
				var accountConditionView = new App.Admin.Views.accountCondition({
					collection: accountListListCollection,
					model:accountListConditionModel,//追加
					pagerModel: pagerModel
				});

				var accountView = new App.Admin.Views.account({
					model:accountListConditionModel
				});
				var accountListListView = new App.Admin.Views.accountListList({
					collection: accountListListCollection,
					model:accountListConditionModel,//追加
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
					accountListListView.fetch(accountListConditionModel);
					accountView.listTable.show(accountListListView);
					accountView.page.show(paginationView);
					accountView.page_2.show(paginationView2);
				};





				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList(pageNumber);
				});
				this.listenTo(paginationView2, 'selected', function(pageNumber){
						fetchList(pageNumber);
				});

				this.listenTo(accountListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});

				//検索ボタンを押したらfetchwを追加
				this.listenTo(accountConditionView, 'click:search', function(sortKey,order){
					fetchList(1,sortKey,order);
				});

				this.listenTo(accountModalView, 'reload', function(){
					fetchList();
				});

				this.listenTo(accountView, 'updated', function(){
					addFlag = false;
					fetchList();
					accountView = new App.Admin.Views.account({
						collection: accountListListCollection
					});
				});

				this.listenTo(accountListListView, 'childview:click:a', function(view, model, display){
					accountModalView.showMessage(model,display);
				});


				//遷移するときのアラート
				var addFlag = false;

				this.listenTo(accountModalView, 'add', function(){
					addFlag = true;
				});
				this.listenTo(accountModalView, 'delete', function(){
					addFlag = false;
				});
				this.listenTo(accountListListCollection, 'sync', function(){
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
					// //accountListListView.empty();
					// if(pageNumber){
						// pagerModel.set('page_number', pageNumber);
					// }
					// accountListListView.fetch(accountListConditionModel);
				// };

				App.main.show(accountView);
				//コンディション追加
				accountView.condition.show(accountConditionView);
				accountConditionView.corporate_id.show(corporateIdConditionView);
				accountConditionView.agreement_no.show(agreementNoConditionView);
				accountView.page.show(paginationView);
				accountView.listTable.show(accountListListView);
				accountView.accountModal.show(accountModalView);
				//fetchList(); 実行
			}
		});
	});
	return App.Admin.Controllers.account;
});
