define([
	'app',
	'../Templates',
    '../behaviors/Alerts',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerOrderComplete = Marionette.LayoutView.extend({
			template: App.Admin.Templates.wearerOrderComplete,
			model: new Backbone.Model(),
			regions: {
				"condition": ".condition",
			},
			onShow: function() {
				if(window.sessionStorage.getItem('referrer')=='wearer_order_send'){
					$('#title').text('発注送信完了');
					$('#text').text('発注を受け付けました。商品は一週間程度でお届けになります。');
				};
			},
			events: {

			},

		});
	});
});
