define([
	'app',
	'../Templates',
    '../behaviors/Alerts',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerInput = Marionette.LayoutView.extend({
			template: App.Admin.Templates.wearerInput,
			behaviors: {
					"Alerts": {
							behaviorClass: App.Admin.Behaviors.Alerts
					}
			},
			ui: {
				"agreement_no": ".agreement_no",
				"cancel": "#cancel",
				"input_delete": "#input_delete",
				"input_delete_button": "#input_delete_button",
				"input_insert": "#input_insert",
				"input_insert_button": "#input_insert_button",
				"input_item_button": "#input_item_button",
				"input_item" : "#input_item"
			},
			regions: {
				"agreement_no": ".agreement_no",
				"page": ".page",
				"page_2": ".page_2",
				"condition": ".condition",
				"listTable": ".listTable",
				"csv_download": ".csv_download",
				"sectionModal": ".section_modal",
				"detailModal": '.detail_modal',
				"errors": '.errors'
			},
			binding: {
				"#agreement_no": "agreement_no",
				"#input_insert": "input_insert"
			},
			model: new Backbone.Model(),
			onRender: function() {
				var that = this;
				// 検索画面以外から遷移してきた場合は「着用者入力取消」ボタンを隠す
				if(
					!window.sessionStorage.getItem('wearer_search') &&
					!window.sessionStorage.getItem('wearer_order_search')
				)
				{
					this.ui.input_delete_button.hide();
				}
				// 初期表示時は「着用者のみ登録して終了」「商品明細入力へ」ボタンを隠す
				if (!$('#agreement_no').val()) {
						this.ui.input_insert_button.hide();
						this.ui.input_item_button.hide();
				}
			},
			events: {
				'change @ui.agreement_no': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					this.ui.agreement_no = $('#agreement_no');
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}
					this.triggerMethod('change:agreement_no',this.ui.agreement_no.val());
				},
				'click @ui.cancel': function(){
					//・ホーム画面の「貸与開始」ボタン、「着用者入力」から遷移した場合はホーム画面へ戻る。
					// ・発注管理の検索結果画面から遷移した場合は、発注管理の検索結果画面へ戻る。
					//window.sessionStorage.setItem('referrer', 'wearer_input');
					//console.log(window.sessionStorage.getItem('wearer_input_ref'));

					// ホームから遷移してきた場合
					if(window.sessionStorage.getItem('wearer_input_ref') == 'home'){
						window.sessionStorage.removeItem('wearer_input_ref');
						location.href = './home.html';
						return;
					}
					// 検索画面から遷移してきた場合
					if(window.sessionStorage.getItem('wearer_input_ref') == 'wearer_search'){
						window.sessionStorage.removeItem('wearer_input_ref');
						location.href = './wearer_search.html';
						return;
					}

					location.href = './home.html';
					return;
				},
				'click @ui.input_insert': function(){
					var that = this;
					var rntl_cont_no = $("select[name='agreement_no']").val();
					var rntl_sect_cd = $("select[name='section']").val();
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '貸与開始-着用者を登録して終了-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							if(res_val.chk_flg == false){
								alert(res_val["error_msg"]);
								return true;
							}else{
								that.triggerMethod('click:input_insert',rntl_cont_no);
							}
						}
					});
				},
				'click @ui.input_item': function(e) {
					e.preventDefault();
					var that = this;
					var rntl_cont_no = $("select[name='agreement_no']").val();
					var rntl_sect_cd = $("select[name='section']").val();
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '貸与開始-商品明細入力へ-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": rntl_sect_cd,
						"rntl_cont_no": rntl_cont_no
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							if(res_val.chk_flg == false){
								alert(res_val["error_msg"]);
								return true;
							}else{
								that.triggerMethod('click:input_item',rntl_cont_no);
							}
						}
					});
					//this.ui.agreement_no = $('#agreement_no');
					//this.triggerMethod('click:input_item', this.ui.agreement_no.val());
				},
				'click @ui.input_delete': function(e) {
					e.preventDefault();
					this.triggerMethod('click:input_delete');
				}
			}
		});
	});
});
