define([
	'app',
	'./Abstract',
	'../views/OrderSend',
	'../views/OrderSendCondition',
	'../views/OrderSendListList',
	'../views/OrderSendListItem',
	'../views/AgreementNoCondition',
	'../views/SectionCondition',
	'../views/SectionModalCondition',
	'../views/SectionModalListList',
	'../views/SectionModalListItem',
	'../views/JobTypeCondition',
	'../views/SectionModal',
	'../views/SectionModalListList',
	'../views/Pagination',
	'../views/SexKbnCondition',
	'../views/SndKbnCondition',
	"entities/models/Pager",
	"entities/models/AdminOrderSend",
	"entities/models/AdminOrderSendListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/models/AdminCsvDownloadCondition",
	"entities/collections/AdminOrderSendListList",
	"entities/collections/AdminSectionModalListList",
	"entities/collections/AdminCsvDownload",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.OrderSend = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('orderSend');
				var pagerModel = new App.Entities.Models.Pager();
				var pagerModel2 = new App.Entities.Models.Pager();
				var modal = false;
				var orderSendModel = null;
				var orderSendView = new App.Admin.Views.OrderSend();
				var orderSendListListCollection = new App.Entities.Collections.AdminOrderSendListList();

				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();
				var sectionConditionView = new App.Admin.Views.SectionCondition();
				var jobTypeConditionView = new App.Admin.Views.JobTypeCondition();
				var sexKbnConditionView = new App.Admin.Views.SexKbnCondition();
				var sndKbnConditionView = new App.Admin.Views.SndKbnCondition();


				var orderSendListConditionModel = new App.Entities.Models.AdminOrderSendListCondition();
				var orderSendConditionView = new App.Admin.Views.OrderSendCondition({
					model:orderSendListConditionModel
				});
				var orderSendListListView = new App.Admin.Views.OrderSendListList({
					collection: orderSendListListCollection,
					pagerModel: pagerModel
				});


				var orderSendListItemView = new App.Admin.Views.OrderSendListItem();

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
					orderSendListListView.fetch(orderSendListConditionModel);
					orderSendView.listTable.show(orderSendListListView);
				};
				//拠点絞り込み--ここから
				var sectionListListCollection = new App.Entities.Collections.AdminSectionModalListList();
				var sectionModalListListView = new App.Admin.Views.SectionModalListList({
					collection: sectionListListCollection,
					pagerModel: pagerModel2
				});
				var sectionModalListCondition = new App.Entities.Models.AdminSectionModalListCondition();
				var sectionModalConditionView = new App.Admin.Views.SectionModalCondition({
					model:sectionModalListCondition
				});
				var sectionModalView = new App.Admin.Views.SectionModal({
					model:sectionModalListCondition
				});
				this.listenTo(sectionConditionView, 'click:section_btn', function(view, model){
					// sectionModalView.page.reset();
					// sectionModalView.listTable.reset();
					sectionModalView.ui.modal.modal('show');
				});
				var fetchList_section = function(pageNumber,sortKey,order){
					if(pageNumber){
						pagerModel2.set('page_number', pageNumber);
					}
					if(sortKey){
						pagerModel2.set('sort_key', sortKey);
						pagerModel2.set('order', order);
					}
					sectionModalListListView.fetch(sectionModalListCondition);
					sectionModalView.listTable.show(sectionModalListListView);
				};
				this.listenTo(sectionModalConditionView, 'click:section_search', function(sortKey, order){
					modal = true;
					fetchList_section(1,sortKey,order);
				});
				this.listenTo(sectionModalView, 'fetched', function(){
					sectionModalView.ui.modal.modal('show');
				});
				var sectionModalListItemView = new App.Admin.Views.SectionModalListItem();
				this.listenTo(sectionModalListListView, 'childview:click:section_select', function(model){
					sectionConditionView.ui.section[0].value = model.model.attributes.rntl_sect_cd;
					sectionModalView.ui.modal.modal('hide');
				});
				//拠点絞り込み--ここまで

				this.listenTo(orderSendListListView, 'sort', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(orderSendConditionView, 'click:search', function(sortKey,order){
					modal = false;
					fetchList(1,sortKey,order);
				});
				//発注送信ボタン
				this.listenTo(orderSendListListView, 'click:updBtn', function(sortKey,order){
					fetchList(null,sortKey,order);
				});
				this.listenTo(orderSendListListView, 'reload', function(){
					fetchList();
				});
				// 人員明細詳細モーダル --ここから
				this.listenTo(orderSendListListView, 'childview:reload2', function(){
					fetchList();
				});

				App.main.show(orderSendView);
				orderSendView.condition.show(orderSendConditionView);
				orderSendConditionView.agreement_no.show(agreementNoConditionView);
				orderSendConditionView.section.show(sectionConditionView);
				orderSendConditionView.job_type.show(jobTypeConditionView);
				orderSendConditionView.sex_kbn.show(sexKbnConditionView);
				orderSendConditionView.snd_kbn.show(sndKbnConditionView);
				orderSendView.sectionModal.show(sectionModalView.render());
				sectionModalView.page.show(paginationView);
				sectionModalView.condition.show(sectionModalConditionView);
			}
		});
	});

	return App.Admin.Controllers.OrderSend;
});
