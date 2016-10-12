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
                "input_cancel": "#input_cancel",
                "input_cancel_button": "#input_cancel_button",
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
				"errors": '.errors',
			},
			binding: {
				"#agreement_no": "agreement_no",
                "#input_insert": "input_insert",
			},
			model: new Backbone.Model(),
			onRender: function() {
				var that = this;
				// 検索画面以外から遷移してきた場合は「着用者入力取消」ボタンを隠す
				if(window.sessionStorage.getItem('referrer')!='wearer_search'){
                    this.ui.input_cancel_button.hide();
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
					window.sessionStorage.setItem('referrer', 'wearer_input');

					// ホームから遷移してきた場合
					if(window.sessionStorage.getItem('referrer')=='home'){
						location.href = './home.html';
					}
                    // 検索画面から遷移してきた場合
					if(window.sessionStorage.getItem('referrer')=='wearer_search'){
						location.href = './wearer_search.html';
					}
                    location.href = './home.html';
                    return;
				},
                'click @ui.input_insert': function(){
					this.ui.agreement_no = $('#agreement_no');
                    this.triggerMethod('click:input_insert',this.ui.agreement_no.val());
                },
				'click @ui.input_item': function(e){
					e.preventDefault();
					var we_vals = this.ui.input_item.val();
					var we_val = we_vals.split(':');
					var data = {
						'rntl_cont_no': we_val[0],
						'werer_cd': we_val[1],
						'cster_emply_cd': we_val[2],
						'sex_kbn': we_val[3],
						'rntl_sect_cd': we_val[4],
						'job_type_cd': we_val[5],
						'ship_to_cd': we_val[6],
						'ship_to_brnch_cd': we_val[7],
						'order_reason_kbn': we_val[8],
						'order_tran_flg': we_val[9],
						'wearer_tran_flg': we_val[10],
						'appointment_ymd': we_val[11],
						'resfl_ymd': we_val[12],
					};

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WS0011;
					var cond = {
						"scr": '着用開始検索',
						"data": data,
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var errors = res.get('errors');
							if(errors) {
								var errorMessages = errors.map(function(v){
									return v.error_message;
								});
								that.triggerMethod('showAlerts', errorMessages);
							}
							var $form = $('<form/>', {'action': '/universal/wearer_order.html', 'method': 'post'});

							window.sessionStorage.setItem('referrer', 'wearer_input');
							$form.appendTo(document.body);
							$form.submit();
						}
					});
				}
			},

		});
	});
});
