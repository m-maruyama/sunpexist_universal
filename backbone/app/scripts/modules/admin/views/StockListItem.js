define([
	'app',
	'../Templates',
	'./StockListItem',
	"entities/models/StockAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.StockListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.stockListItem,
			tagName: "tr",
			ui: {
				"detailLink": "a.detail"
			},
			onRender: function() {
			},
			events: {
			},
			templateHelpers: {
				//よろず発注区分
				zaikoText: function(){
					var data = this.zk_status_cd;
					if (data == 1) {
						return "新品";
					} else if (data == 2) {
						return "中古A";
					} else if (data == 3) {
						return "中古B";
					}

				},
			}

		});
	});
});