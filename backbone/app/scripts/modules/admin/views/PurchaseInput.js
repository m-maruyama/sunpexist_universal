define([
	'app',
	'../Templates',
    '../behaviors/Alerts',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var search_flg ='';

		Views.PurchaseInput = Marionette.LayoutView.extend({
			template: App.Admin.Templates.purchaseInput,
            behaviors: {
                "Alerts": {
                    behaviorClass: App.Admin.Behaviors.Alerts
                }
            },
			ui: {
				"agreement_no": ".agreement_no",
			},
			regions: {
				"condition": ".condition",
				"listTable": ".listTable",
			},
			binding: {
                "#input_insert": "input_insert",
			},
			model: new Backbone.Model(),
			onRender: function() {
				// 検索画面以外から遷移してきた場合は「着用者入力取消」ボタンを隠す
        //        if (document.referrer.indexOf("prchase_search") == -1) {
        //            this.ui.input_cancel_button.hide();
        //        }
        //        // 初期表示時は「着用者のみ登録して終了」ボタンを隠す
        //        if (!$('#agreement_no').val()) {
          //          　this.ui.input_insert_button.hide();
          //      }
			},
			events: {
				'change @ui.agreement_no': function(e){
					e.preventDefault();
					alert('契約noが変更された！！！');
				},
				//'click @ui.cancel': function(){
//
				//},
        //'click @ui.input_insert': function(){
        //      this.triggerMethod('click:input_insert',this.ui.agreement_no.val());
        //},
			},

		});
	});
});
