define([
	'app',
	'../Templates',
	'./UnreturnedListItem',
	"entities/models/UnreturnedAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.UnreturnedListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.unreturnedListItem,
			tagName: "tr",
			ui: {
				"detailLink": "a.detail"
			},
			onRender: function() {
			},
			events: {
				'click @ui.detailLink': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model);
				}
			},
			templateHelpers: {
				//ステータス
				statusText: function(){
					var data = this.status;
					if (data == 1) {
						return "未返却";
					} else if (data == 2) {
						return "返却済";
					}
					return '-';
					
				},
				//よろず発注区分
				kubunText: function(){
					var data = this.kubun;
					if (data == 2) {
						return "返却";
					} else if (data == 3) {
						return "サイズ交換";
					} else if (data == 4) {
						return "消耗交換";
					} else if (data == 5) {
						return "異動";
					}
					return '-';

				},
			}

		});
	});
});