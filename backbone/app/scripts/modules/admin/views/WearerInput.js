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
                "input_next" : "#input_next"
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
				// 検索画面以外から遷移してきた場合は「着用者入力取消」ボタンを隠す
                if (document.referrer.indexOf("wearer_search") == -1) {
                    this.ui.input_cancel_button.hide();
                }
                // 初期表示時は「着用者のみ登録して終了」ボタンを隠す
                if (!$('#agreement_no').val()) {
                    this.ui.input_insert_button.hide();
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

                    // ホームから遷移してきた場合
                    if (document.referrer.indexOf("home") != -1) {
                        location.href = './home.html';
                        return;
                    }
                    // 検索画面から遷移してきた場合
                    if (document.referrer.indexOf("wearer_search") != -1) {
                        location.href = './wearer_search.html';
                        return;
                    }
                    location.href = './home.html';
                    return;
				},
                'click @ui.input_insert': function(){
                    this.triggerMethod('click:input_insert',this.ui.agreement_no.val());
                },
                'click @ui.input_next': function(){
                    this.triggerMethod('click:input_next',this.ui.input_next.val());
                },
			},

		});
	});
});
