define([
	'app',
	'handlebars',
	'../Templates',
	'./AcountListItem',
	"entities/models/AcountAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.AcountListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.acountListItem,
			tagName: "tr",
			ui: {
				"lockBtn": ".lock",
				"editBtn": ".edit",
				"deleteBtn": ".delete"
			},
			onRender: function() {
			},
			events: {
				'click @ui.lockBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model, '3');
				},
				'click @ui.editBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model, '1');
				},
				'click @ui.deleteBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:a', this.model, '2');
				},
			},
			templateHelpers: {
				// アカウントロック
				lock: function(){
					var login_err_count = this.login_err_count;
					if (login_err_count >= 5) {
						return 'lock';
					} else {
						return null;
					}

				},
				//管理権限
				userType: function(){
					var data = this.user_type;
					if (data == 1) {
						return "一般";
					} else if (data == 2) {
						return "管理者";
					} else if (data == 3) {
						return "システム管理者";
					}
					return 'invalid';
				},
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
