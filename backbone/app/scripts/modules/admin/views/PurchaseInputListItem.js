define([
	'app',
	'handlebars',
	'../Templates',
	'./PurchaseInputListItem',
	"entities/models/PurchaseAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.PurchaseInputListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.purchaseInputListItem,
			tagName: "tr",
			ui: {
				//"lockBtn": ".lock",
				//"editBtn": ".edit",
				//"deleteBtn": ".delete"
			},
			onRender: function() {
			},
			events: {
				//ロックボタンがクリックされた時の動作
				'click @ui.lockBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model, '3');
					$(".accnt_no_group").removeClass("hidden");
				},
				//編集ボタンがクリックされた時の動作
				'click @ui.editBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model, '1');
					$(".accnt_no_group").removeClass("hidden");
				},
				//削除ボタンがクリックされた時の動作
				'click @ui.deleteBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model, '2');
					$(".accnt_no_group").removeClass("hidden");
				},
			},
			templateHelpers: {
				// アカウントロック
		
				//編集、削除の可否
				editDel: function(){
					var type = this.user_type;
					if (type == 1) {
						return "一般";
					} else if (type == 2) {
						return "管理者";
					} else if (type == 3) {
						return '';
					}
					return;
				},
			},
		});
	});
});
