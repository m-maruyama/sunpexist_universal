define([
	'app',
	'../Templates',
	'../behaviors/Alerts'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.LoginPassword = Marionette.ItemView.extend({
			template: App.Admin.Templates.loginPassword,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				"login_id": "#login_id",
				"corporate_id": "#corporate_id",
				"submit": ".submit",
				"errors": ".errors"
			},
			onRender: function() {
				var that = this;
			},
			events: {
				"click @ui.submit": function(e){
					e.preventDefault();
					var that = this;
					that.triggerMethod('hideAlerts');
					this.model.set('login_id', this.ui.login_id.val());
					this.model.set('corporate_id', this.ui.corporate_id.val());


					//this.model.set('password_c', this.ui.password_c.val());
					var errors = this.model.validate();

					if (errors){
						this.triggerMethod('showAlerts', errors);
						return;
					}
					var cond = {
						"scr": 'パスワード変更',
						"corporate_id": this.ui.corporate_id.val(),
						"user_id": this.ui.login_id.val(),

						//"password": this.ui.password.val(),
						//"password_c": this.ui.password_c.val()
					};
					this.model.fetchMx({
						data: cond,
						success: function(model){
							if(model.get('status') === 0){
								alert('対象アカウントへのパスワード変更手続きメールの送信が完了しました。');
								//that.triggerMethod('success');
							} else if(model.get('status') === 1){
								that.triggerMethod('showAlerts', ['対象アカウントへのパスワード変更手続きメールの送信が失敗しました。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 2){
								that.triggerMethod('showAlerts', ['パスワード桁数は8文字以上で入力してください。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 3){
								that.triggerMethod('showAlerts', ['パスワードは半角英数字、半角記号(!#$%&*+@?)3種以上混合で入力してください。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 4){
								that.triggerMethod('showAlerts', ['該当するアカウントが見つかりませんでした']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 5){
								that.triggerMethod('showAlerts', ['ログインIDは半角英数字混合、8文字以上で入力してください。']);
								that.triggerMethod('failed');
							}

				}
					});
				}
			}
		});
	});
});