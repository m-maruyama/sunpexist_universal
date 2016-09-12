define([
	'app',
	'./Abstract',
	'../views/PurchaseInput',
	'../views/PurchaseInputCondition',
	'../views/PurchaseInputListList',
	'../views/AgreementNoConditionInput',
	'../views/InputItemCondition',
	'../views/ItemColorCondition',
	'../views/IndividualNumberCondition',
	'../views/Pagination',
    '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminPurchaseInput",
	"entities/models/AdminPurchaseInputListCondition",
	"entities/collections/AdminPurchaseInputListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.PurchaseInput = App.Admin.Controllers.Abstract.extend({
            behaviors: {
                "Alerts": {
                    behaviorClass: App.Admin.Behaviors.Alerts
                }
            },
			_sync : function(){
				var that = this;
				this.setNav('purchaseInput');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var purchaseInputModel = new App.Entities.Models.AdminPurchaseInput();
				var purchaseInputListListCollection = new App.Entities.Collections.AdminPurchaseInputListList();

				var purchaseInputView = new App.Admin.Views.PurchaseInput({
					model:purchaseInputModel
				});
				var purchaseInputListListView = new App.Admin.Views.PurchaseInputListList({
					collection: purchaseInputListListCollection,
					model:purchaseInputListConditionModel,//追加
				});


				var agreementNoConditionView = new App.Admin.Views.AgreementNoConditionInput();
				var individualNumberConditionView = new App.Admin.Views.IndividualNumberCondition();

				var purchaseInputListConditionModel = new App.Entities.Models.AdminPurchaseInputListCondition();
				var purchaseInputConditionView = new App.Admin.Views.PurchaseInputCondition({
					model:purchaseInputListConditionModel
				});
				//var paginationView = new App.Admin.Views.Pagination({model: pagerModel});

				//拠点絞り込み--ここから
				//var sectionListListCollection = new App.Entities.Collections.AdminSectionModalListList();

				//this.listenTo(purchaseInputConditionView, 'click:section_btn', function(view, model){
					// sectionModalView.page.reset();
					// sectionModalView.listTable.reset();
					//sectionModalView.ui.modal.modal('show');
				//});

				var fetchList = function(pageNumber,sortKey,order){
					//if(pageNumber){
					//	pagerModel.set('page_number', pageNumber);
					//}
					if(sortKey){
						//pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					purchaseInputListListView.fetch(purchaseInputListConditionModel);
					purchaseInputView.listTable.show(purchaseInputListListView);
					//accountView.page.show(paginationView);
					//accountView.page_2.show(paginationView2);
				};


				var fetchList_section = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					//sectionModalListListView.fetch(sectionModalListCondition);
					//sectionModalView.listTable.show(sectionModalListListView);
				};
				//契約No選択イベント--ここから
				this.listenTo(purchaseInputView, 'change:agreement_no', function(agreement_no){
					purchaseInputConditionView.fetch(agreement_no);
					purchaseInputView.condition.show(purchaseInputConditionView);
					purchaseInputView.ui.input_insert_button.show();
				});
				//契約No選択イベント--ここまで

				App.main.show(purchaseInputView);
				//purchaseInputView.listTable.show(purchaseInputListListView);
				// wearerInputView.condition.show(wearerInputConditionView);
				//purchaseInputView.agreement_no.show(agreementNoConditionView);
				//purchaseInputView.sectionModal.show(sectionModalView.render());
				//sectionModalView.page.show(paginationView);
				//sectionModalView.condition.show(sectionModalConditionView);
				fetchList();
			}
		});
	});

	return App.Admin.Controllers.PurchaseInput;
});
