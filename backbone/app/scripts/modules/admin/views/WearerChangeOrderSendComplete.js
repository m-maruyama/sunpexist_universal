define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'../behaviors/Alerts',
	'bootstrap',
	'typeahead',
	'bloodhound',
	'blockUI',
	'../controllers/WearerChangeOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerChangeOrderSendComplete = Marionette.LayoutView.extend({
			defaults: {
			},
			initialize: function() {
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerChangeOrderSendComplete,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'continueInput': '.continueInput',
				'backHome': '.backHome',
				'returnSlipDownload': '.returnSlipDownload',
			},
			bindings: {
			},
			onRender: function() {
			},
			events: {
				// 「続けて入力する」ボタン
				'click @ui.continueInput': function(){
						alert("どこへ遷移？");
				},
				// 「ホーム画面へ戻る」ボタン
				'click @ui.backHome': function(){
					// ホーム画面へ遷移
					location.href="home.html";
				},
				// 「返却伝票ダウンロード」ボタン
				'click @ui.returnSlipDownload': function(){
					alert("てっちゃん、機能の実装よろしくお願いします。");
				},
			}
		});
	});
});
