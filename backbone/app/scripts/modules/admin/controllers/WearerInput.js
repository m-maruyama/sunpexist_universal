define([
	'app',
	'./Abstract',
	'../views/WearerInput',
	'../views/WearerInputCondition',
	'../views/AgreementNoConditionInput',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/InputItemCondition',
	'../views/ItemColorCondition',
	'../views/IndividualNumberCondition',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
    '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminWearerInput",
	"entities/models/AdminWearerInputListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerInput = App.Admin.Controllers.Abstract.extend({
            behaviors: {
                "Alerts": {
                    behaviorClass: App.Admin.Behaviors.Alerts
                }
            },
			_sync : function(){
				var that = this;
				this.setNav('wearerInput');
				var pagerModel = new App.Entities.Models.Pager();
				var modal = false;
				var wearerInputModel = new App.Entities.Models.AdminWearerInput();
				var wearerInputView = new App.Admin.Views.WearerInput({
					model:wearerInputModel
				});

				var agreementNoConditionView = new App.Admin.Views.AgreementNoConditionInput();
				var individualNumberConditionView = new App.Admin.Views.IndividualNumberCondition();

				var wearerInputListConditionModel = new App.Entities.Models.AdminWearerInputListCondition();
				var wearerInputConditionView = new App.Admin.Views.WearerInputCondition({
					model:wearerInputListConditionModel
				});
				var paginationView = new App.Admin.Views.Pagination({model: pagerModel});

				//拠点絞り込み--ここから
				var sectionListListCollection = new App.Entities.Collections.AdminSectionModalListList();
				var sectionModalListListView = new App.Admin.Views.SectionModalListList({
					collection: sectionListListCollection,
					pagerModel: pagerModel
				});
				var sectionModalListCondition = new App.Entities.Models.AdminSectionModalListCondition();
				var sectionModalConditionView = new App.Admin.Views.SectionModalCondition({
					model:sectionModalListCondition
				});
				var sectionModalView = new App.Admin.Views.SectionModal({
					model:sectionModalListCondition
				});
				this.listenTo(wearerInputConditionView, 'click:section_btn', function(view, model){
					// sectionModalView.page.reset();
					// sectionModalView.listTable.reset();
					sectionModalView.ui.modal.modal('show');
				});
				var fetchList_section = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel.set('sort_key', sortKey);
						pagerModel.set('order', order);
					}
					sectionModalListListView.fetch(sectionModalListCondition);
					sectionModalView.listTable.show(sectionModalListListView);
				};
				this.listenTo(sectionModalConditionView, 'click:section_search', function(sortKey, order){
					modal = true;
					fetchList_section(1,sortKey,order);
				});
				this.listenTo(sectionModalView, 'fetched', function(){
					wearerInputView.detailModal.show(sectionModalView.render());
					sectionModalView.ui.modal.modal('show');
				});
				var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
				this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
					wearerInputConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
					sectionModalView.ui.modal.modal('hide');
				});
				//拠点絞り込み--ここまで

				//契約No選択イベント--ここから
				this.listenTo(wearerInputView, 'change:agreement_no', function(agreement_no){
					wearerInputConditionView.fetch(agreement_no);
					wearerInputView.condition.show(wearerInputConditionView);
					wearerInputView.ui.input_insert_button.show();
					wearerInputView.ui.input_item_button.show();
				});
				//契約No選択イベント--ここまで

				//着用者のみ登録して終了
				this.listenTo(wearerInputView, 'click:input_insert', function(agreement_no){
					var errors = wearerInputConditionView.insert_wearer(agreement_no);
					if(errors){
						wearerInputView.triggerMethod('showAlerts', errors);
					}
				});
				//エラーメッセージ
				this.listenTo(wearerInputConditionView, 'error_msg', function(errors){
					if(errors){
						wearerInputView.triggerMethod('showAlerts', errors);
					}
				});

				//商品明細入力へ
				this.listenTo(wearerInputView, 'click:input_item', function(data){
					var errors = wearerInputConditionView.input_item(data);
					if(errors){
						wearerInputView.triggerMethod('showAlerts', errors);
					}
				});
				//着用者取消
				this.listenTo(wearerInputView, 'click:input_delete', function(){
					var errors = wearerInputConditionView.input_delete();
					if(errors){
						wearerInputView.triggerMethod('showAlerts', errors);
					}
				});
				this.listenTo(paginationView, 'selected', function(pageNumber){
						fetchList_section(pageNumber);
				});
				//着用者検索画面からきた場合
				this.listenTo(agreementNoConditionView, 'input_form', function(agreement_no){
					wearerInputConditionView.fetch(agreement_no);
					wearerInputView.condition.show(wearerInputConditionView);
					wearerInputView.ui.input_insert_button.show();
					wearerInputView.ui.input_item_button.show();
				});
				App.main.show(wearerInputView);
				wearerInputView.agreement_no.show(agreementNoConditionView);
				wearerInputView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerInput;
});
