define([
	'app',
	'../Templates',
	'cookie'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Nav = Marionette.ItemView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.nav,
			ui: {
				"home": "li.nav_home",
				"login": "li.nav_login",
				"importCsv": "li.nav_importCsv",
				"inquiry": "li.nav_inquiry",
				"inquiryInput": "li.nav_inquiryInput",
				"inquiryDetail": "li.nav_inquiryDetail",
				"history": "li.nav_history",
				"delivery": "li.nav_delivery",
				"print": "li.nav_print",
				"unreturn": "li.nav_unreturn",
				"unreturned": "li.nav_unreturned",
				"stock": "li.nav_stock",
				"lend": "li.nav_lend",
				"receive": "li.nav_receive",
				"manpowerInfo": "li.nav_manpowerInfo",
				"wearer": "li.nav_wearer",
				"account": "li.nav_account",
				"orderSend": "li.nav_orderSend",
				"info": "li.nav_info",
				"qa": "li.nav_qa",
				"qaInput": "li.nav_qaInput",
				"purchaseInput": "li.nav_purchaseInput",
				"wearerEdit": "li.nav_wearerEdit",
				"wearerEditOrder": "li.nav_wearerEditOrder",
				"wearerInput": "li.nav_wearerInput",
				"wearerEnd": "li.nav_wearerEnd",
				"wearerEndOrder": "li.nav_wearerEndOrder",
				"wearerChange": "li.nav_wearerChange",
				"wearerChangeOrder": "li.nav_wearerChangeOrder",
				"wearerOther": "li.nav_wearerOther",
				"wearerSizeChange": "li.nav_wearerSizeChange",
				"wearerOtherChangeOrder": "li.nav_wearerOtherChangeOrder",
				"wearerExchangeOrder": "li.nav_wearerExchangeOrder",
				"wearerAddOrder": "li.nav_wearerAddOrder",
				"wearerReturnOrder": "li.nav_wearerReturnOrder",
				"wearerInputComplete": "li.nav_wearerInputComplete",
				"wearerSearch": "li.nav_wearerSearch",
				"wearerOrder": "li.nav_wearerOrder",
				"wearerOrderComplete": "li.nav_wearerOrderComplete",
				"logout": "li.logout a"
			},
			onShow: function() {
				var that = this;
				var cond = {
					"scr": 'グローバルメニュー',
					"log_type": '2'
				};
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.GL0010;
				modelForUpdate.fetchMx({
					data: cond,
					success:function(res){
						var errors = res.get('errors');
						if(errors) {
							var errorMessages = errors.map(function(v){
								return v.error_message;
							});
							that.triggerMethod('showAlerts', errorMessages);
						}
						that.render();
					},
					complete:function () {
						//ボタンの数を数えてゼロ個だったら、ホーム画面の１〜５の大項目を非表示にする。
						for(var i=1;i<=5; i++){
							var counter = 0;
							$('#list-nav-0'+i+' li').each(function () {
								counter++;
							});
							if(counter == 0){
								$('#nav-0'+i).css('display', 'none');
							}
						}
					}
				});
			},
			onRender: function(){
				if(this.options.active || typeof this.ui[this.options.active] !== 'undefined') {
					this.ui[this.options.active].addClass('active');
				}
			},
			events: {
				"click @ui.logout": function(e){
					e.preventDefault();
					var that = this;
					var logout = this.model;
					logout.url = App.api.OU0010;
					logout.fetchMx({
						success:function(res){
							var errors = res.get('errors');
							if(errors) {
								var errorMessages = errors.map(function(v){
									return v.error_message;
								});
								that.triggerMethod('showAlerts', errorMessages);
								return;
							}
							App.container.logout = true;//これがtrueだったら403で飛ばないようにしてある
							location.href = './login.html';
						}
					});
				}
			}

		});
	});
});
