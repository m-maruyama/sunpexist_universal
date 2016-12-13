define([
	'app',
	'./Abstract',
	'../views/Account',
	//コンディション追加
	'../views/AccountCondition',
	'../views/CorporateIdCondition',
	'../views/CorporateIdAllCondition',
	//'../views/AgreementNoCondition',
	'../views/AccountListList',
	'../views/AccountModal',
	'../views/Pagination',
	"entities/models/Pager",
	"entities/models/AdminAccount",
	"entities/models/AdminAccountListCondition",
	"entities/collections/AdminAccountListList",
	"entities/collections/AdminAccountModal",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Account = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('account');
				var pagerModel = new App.Entities.Models.Pager();

				var accountModel = null;
				var accountListListCollection = new App.Entities.Collections.AdminAccountListList();
				var AdminAccountModal = new App.Entities.Collections.AdminAccountModal();

				//追加
				var corporateIdConditionView = new App.Admin.Views.CorporateIdCondition();
				//追加
				var corporateIdAllConditionView = new App.Admin.Views.CorporateIdAllCondition();
				//追加

				//var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();

				var accountListConditionModel = new App.Entities.Models.AdminAccountListCondition();
				//コンディション追加
				//console.log(accountListConditionModel.toJSON());

				var accountConditionView = new App.Admin.Views.AccountCondition({
					model:accountListConditionModel,//追加
					pagerModel: pagerModel
				});
 				var accountModalView = new App.Admin.Views.AccountModal({
					collection: AdminAccountModal
				});

				var accountView = new App.Admin.Views.Account({
					model:accountListConditionModel
				});
				var accountListListView = new App.Admin.Views.AccountListList({
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
					fetchList(1,sortKey,order);
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
					accountView = new App.Admin.Views.Account({
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
				//this.listenTo(accountModalView, 'delete', function(){
				//	addFlag = false;
				//});
				this.listenTo(accountListListCollection, 'sync', function(){
					addFlag = false;
				});


				App.main.show(accountView);
				//コンディション追加
				accountView.condition.show(accountConditionView);
				accountConditionView.corporate_id.show(corporateIdAllConditionView);
				//accountConditionView.corporate_id.show(corporateIdConditionView);
				//accountConditionView.agreement_no.show(agreementNoConditionView);
				accountView.page.show(paginationView);
				accountView.listTable.show(accountListListView);
				accountView.accountModal.show(accountModalView);
				accountModalView.corporate_id.show(corporateIdConditionView);
				//モーダルにコーポレートidを追加

				//fetchList(); 実行
			}
		});
	});
	return App.Admin.Controllers.Account;
});
