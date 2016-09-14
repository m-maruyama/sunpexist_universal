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
			tagName: "tr",//trを生成
			ui: {
				//"lockBtn": ".lock",
				//"editBtn": ".edit",
				//"deleteBtn": ".delete"
			},
			onRender: function() {

			},
			events: {

			},
			templateHelpers: {
				// アカウントロック

				//編集、削除の可否
				//editDel: function(){
				//	var type = this.user_type;
				//	if (type == 1) {
				//		return "一般";
				//	} else if (type == 2) {
				//		return "管理者";
				//	} else if (type == 3) {
				//		return '';
				//	}
				//	return;
				//},
			},
		});
	});
});
