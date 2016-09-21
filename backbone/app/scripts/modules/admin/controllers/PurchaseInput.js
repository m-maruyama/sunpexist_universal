define([
	'app',
	'./Abstract',
	'../views/PurchaseInput',
	'../views/PurchaseInputCondition',
	'../views/PurchaseInputListList',
	'../views/AgreementNoCondition',
	'../views/SectionPurchaseCondition',
	'../views/SectionCondition',

	'../views/InputItemCondition',
	'../views/ItemColorCondition',
	'../views/IndividualNumberCondition',
	'../views/Pagination',
    '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminPurchaseInput",
	"entities/models/AdminPurchaseUpdate",

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


				var AdminPurchaseUpdate = new App.Entities.Models.AdminPurchaseUpdate();
				var purchaseInputListListView = new App.Admin.Views.PurchaseInputListList({
					collection: purchaseInputListListCollection,
					model:AdminPurchaseUpdate,//追加
				});


				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();
				var sectionPurchaseConditionView = new App.Admin.Views.SectionPurchaseCondition();


				var purchaseInputListConditionModel = new App.Entities.Models.AdminPurchaseInputListCondition();
				var purchaseInputConditionView = new App.Admin.Views.PurchaseInputCondition({
					model:purchaseInputListConditionModel
				});

				var fetchList = function(pageNumber,sortKey,order){
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					purchaseInputListListView.fetch(purchaseInputListConditionModel);
					purchaseInputView.listTable.show(purchaseInputListListView);

				};


				//契約No選択イベント--ここから
				this.listenTo(purchaseInputView, 'change:agreement_no', function(agreement_no){

					purchaseInputConditionView.fetch(agreement_no);
					purchaseInputView.condition.show(purchaseInputConditionView);
					purchaseInputView.ui.input_insert_button.show();
					//fetchList();
				});
				//契約No選択イベント--ここまで

				// 契約No変更時の絞り込み処理 --ここから
				this.listenTo(purchaseInputConditionView, 'change:section_select', function(agreement_no){
					var sectionConditionView2 = new App.Admin.Views.SectionPurchaseCondition({
						agreement_no:agreement_no,
					});
					purchaseInputConditionView.section.show(sectionConditionView2);
					fetchList();

				});
				// 契約No変更時の絞り込み処理 --ここまで
				//console.log(sectionConditionView);
				App.main.show(purchaseInputView);

				purchaseInputView.condition.show(purchaseInputConditionView);
				purchaseInputConditionView.agreement_no.show(agreementNoConditionView);
				//purchaseInputListListView.fetch(purchaseInputListConditionModel);//fetchで実施しているのでコメントアウト
				//purchaseInputView.listTable.show(purchaseInputListListView);//fetchで実施しているのでコメントアウト
				purchaseInputConditionView.section.show(sectionPurchaseConditionView);

				fetchList();
			}
		});
	});

	return App.Admin.Controllers.PurchaseInput;
});
