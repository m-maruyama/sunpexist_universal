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
			onRender: function() {
				//前回ログインした企業IDをデフォルト表示
				var corporate_id=getCookie("corporate_id");
				if (corporate_id!=""){
					this.ui.corporate_id.val(corporate_id);
				}
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
								that.triggerMethod('showAlerts', ['企業名、ログイン名、パスワードのいずれかが正しくありません。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 2){
								that.triggerMethod('showAlerts', ['このアカウントはロックされています。サイト管理者にお問い合わせください。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 3){
								//パスワードの変更から９０日以上経過しているため、パスワード変更画面に遷移
								alert('パスワードの変更から９０日以上経過しています。パスワードを変更してください。');
								that.triggerMethod('password');
							} else if(model.get('status') === 4){
								//パスワードの変更から９０日以上経過しているため、パスワード変更画面に遷移
								alert('仮パスワードを確認いたしました。パスワードを変更してください。');
								that.triggerMethod('password');
							}
							// ログインデータの保存(有効期限約10年に設定)
							setCookie('corporate_id',that.ui.corporate_id.val(),3662);
						}
					});
				}
			}
		});
		/* 保存されているクッキーから、指定したクッキー名の値を取得
		 * getCookie(クッキー名)
		 */
		function getCookie(c_name){
			var st="";
			var ed="";
			if (document.cookie.length>0){
				st=document.cookie.indexOf(c_name + "=");
				if (st!=-1){
					st=st+c_name.length+1;
					ed=document.cookie.indexOf(";",st);
					if (ed==-1) ed=document.cookie.length;
					return unescape(document.cookie.substring(st,ed));
				}
			}
			return "";
		}
		/*
		 * クッキー保存
		 * setCookie(クッキー名, クッキーの値, クッキーの有効日数);
		 */
		function setCookie(c_name,value,expiredays){
			// 有効期限の日付
			var exdate=new Date();
			exdate.setDate(expiredays);
			// クッキーに保存する文字列を生成
			var s="";
			s+=c_name+"="+escape(value);
			alert(exdate);
			s+=(expiredays==null)?"":"; expires="+exdate;
			// クッキーに保存
			document.cookie=s;
		}
	});
});