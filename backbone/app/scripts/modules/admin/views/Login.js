define([
	'app',
	'../Templates',
	'../views/Password',
	"entities/collections/AdminPassword",
	'../behaviors/Alerts',
	'cookie'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Login = Marionette.ItemView.extend({
			template: App.Admin.Templates.login,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				"corporate_id": "#corporate_id",
				"login_id": "#login_id",
				"password": "#password",
				"submit": "button",
				"errors": ".errors"
			},
			events: {
				"click @ui.submit": function(e){
					e.preventDefault();
					var that = this;
					this.model.set('corporate_id', this.ui.corporate_id.val());
					this.model.set('login_id', this.ui.login_id.val());
					this.model.set('password', this.ui.password.val());
					var errors = this.model.validate();
					if (errors){
						this.triggerMethod('showAlerts', errors);
						return;
					}
					var cond = {
						"scr": 'ログイン',
						"corporate_id": this.ui.corporate_id.val(),
						"login_id": this.ui.login_id.val(),
						"password": this.ui.password.val()
					};
					this.model.fetchMx({
						data: cond,
						success: function(model){
							if(model.get('status') === 0){
								that.triggerMethod('success');
							} else if(model.get('status') === 1){
								that.triggerMethod('showAlerts', ['企業名、ログイン名、パスワードのいづれかが正しくありません。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 2){
								that.triggerMethod('showAlerts', ['このアカウントはロックされています。サイト管理者にお問い合わせください。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 3){
								//パスワードの変更から９０日以上経過しているため、パスワード変更画面に遷移
								alert('パスワードの変更から９０日以上経過しています。パスワードを変更してください。');
								that.triggerMethod('password');
							}
						}
					});
				}
			}
		});
	});
});