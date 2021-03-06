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
				var referrer = window.sessionStorage.getItem('referrer_complete');
				if(referrer=='wearer_order_complete'){
					window.sessionStorage.removeItem('referrer_complete');
					location.href = './wearer_search.html';
				}else{
					//初期表示時
					window.sessionStorage.setItem('referrer_complete', 'wearer_order_complete');
				}
				if(window.sessionStorage.getItem('referrer')=='wearer_order_send'){
					$('#title').text('発注送信完了');
					$('#text').text('発注を受け付けました。商品は一週間程度でお届けになります。');
				}else if(referrer=='wearer_end_order_err') {
					window.sessionStorage.getItem('error_msg');
					$('#title').text('');
					$('#text').text(window.sessionStorage.getItem('error_msg'));
				};


			},
			events: {

			},

		});
	});
});
