define([
	'app',
	'../Templates',
	'../behaviors/Alerts'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Password = Marionette.ItemView.extend({
			template: App.Admin.Templates.password,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				"user_id": "#user_id",
				"password": "#password",
				"password_c": "#password_c",
				"backBtn": ".backBtn",
				"submit": ".password",
				"errors": ".errors",
				"content": "#passwordContent",
			},
			onRender: function() {
				var that = this;
				that.triggerMethod('hideAlerts');
				var getHash = location.search.replace(/^\?(.*)$/, '$1');

				if(getHash){
					var getHash = getHash.split( '=' );
						if(getHash[0] == 'dp') {
							$("#passwordContent").addClass("none");
							if($('#passwordContent').length){
							}
							var cond = {
								"hashcheck": 'ハッシュチェック',
								"hashid": getHash[1],
							};
							this.model.fetchMx({
								data: cond,
								success: function (model) {
									//console.log(model);
									//ハッシュタグが違う場合
									if (model.attributes.errors) {
										// if(window.confirm('不正なURLです')){
                                            window.alert('不正なURLです');
											location.href = "login.html";
										// }
										return;
									}
									$("#passwordContent").removeClass("none");

									var tmp_corporate_id = model.attributes.list.corporate_id;
									var tmp_user_id = model.attributes.list.user_id;
									$("#tmp_corporate_id").val(tmp_corporate_id);
									$("#tmp_user_id").val(tmp_user_id);
								}
							});
						}else if(getHash[1] == 'account') {
							this.ui.content.removeClass("none");
							this.ui.backBtn.removeClass("none");

						}else if(getHash[1] == 'login') {
							this.ui.content.removeClass("none");
							var corporate_id = window.sessionStorage.getItem('corporate_id');
							if(corporate_id == null){
								location.href = "login.html";
							}
						}
					}else{

					}

			},
			onShow: function(){
			},
			events: {
				"click @ui.submit": function(e){
					e.preventDefault();
					var that = this;
					that.triggerMethod('hideAlerts');
					var getHash = location.search.replace(/^\?(.*)$/, '$1');
					var getHash = getHash.split( '=' );

					if(getHash){
						if(getHash[0] == 'dp') {
							//console.log('dp');
							var tmp_corporate_id = $("#tmp_corporate_id").val();
							var tmp_user_id = ($("#tmp_user_id").val());
							if (tmp_corporate_id) {
								this.model.set('corporate_id', tmp_corporate_id);
							}
							if (tmp_user_id) {
								this.model.set('user_id', tmp_user_id);
							}
							this.model.set('user_id', this.ui.user_id.val());
							this.model.set('password', this.ui.password.val());
							this.model.set('password_c', this.ui.password_c.val());
							var errors = this.model.validate();
							if (errors) {
								this.triggerMethod('showAlerts', errors);
								return;
							}
							var cond = {
								"scr": 'パスワード変更',
								"from": 'mail',
								"tmp_corporate_id": tmp_corporate_id,
								"tmp_user_id": tmp_user_id,
								"user_id": this.ui.user_id.val(),
								"password": this.ui.password.val(),
								"password_c": this.ui.password_c.val()
							};

						}else if(getHash[1] == 'account') {
							var account_param = JSON.parse(window.sessionStorage.getItem('account_param'));
							this.model.set('accnt_no', account_param.accnt_no);
							this.model.set('password', this.ui.password.val());
							this.model.set('password_c', this.ui.password_c.val());
							var errors = this.model.validate();

							if (errors) {
								this.triggerMethod('showAlerts', errors);
								return;
							}
							var cond = {
								"scr": 'パスワード変更',
								"from": 'account',
								"accn_no": account_param.accnt_no,
								"password": this.ui.password.val(),
								"password_c": this.ui.password_c.val()
							};

						}else if(getHash[1] == 'login') {

							var corporate_id = window.sessionStorage.getItem('corporate_id');
							var user_id = window.sessionStorage.getItem('user_id');
							this.model.set('corporate_id', corporate_id);
							this.model.set('user_id', user_id);
							this.model.set('password', this.ui.password.val());
							this.model.set('password_c', this.ui.password_c.val());
							var errors = this.model.validate();
							if (errors) {
								this.triggerMethod('showAlerts', errors);
								return;
							}
							var cond = {
								"scr": 'パスワード変更',
								"from": 'login90day',
								"corporate_id": corporate_id,
								"user_id": user_id,
								"password": this.ui.password.val(),
								"password_c": this.ui.password_c.val()
							};

						}
					}

					this.model.fetchMx({
						data: cond,
						success: function(model){
							if(model.get('status') === 0){
								alert('パスワードを変更しました。新規に設定したパスワードでログインしてください。');
								that.triggerMethod('success');
							} else if(model.get('status') === 1){
								that.triggerMethod('showAlerts', ['新規パスワード入力欄、新規パスワード確認入力欄の値が不一致です。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 2){
								that.triggerMethod('showAlerts', ['パスワード桁数は8文字以上16文字以下で入力してください。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 3){
								that.triggerMethod('showAlerts', ['パスワードは半角英数字、半角記号(!#$%&*+@?)3種以上混合で入力してください。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 4){
								that.triggerMethod('showAlerts', ['前回と同じパスワードは使用出来ません。']);
								that.triggerMethod('failed');
							} else if(model.get('status') === 5){
								that.triggerMethod('showAlerts', ['過去に設定したことのあるパスワードは使用出来ません。']);
								that.triggerMethod('failed');
							}
						}
					});
				},
				"click @ui.backBtn": function(){
					window.sessionStorage.setItem('page_from', 'password');
					location.href = './account.html';

				}
			}
		});
	});
});