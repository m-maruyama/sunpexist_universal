define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.AccountModal = Marionette.LayoutView.extend({
			template: App.Admin.Templates.accountModal,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"corporate_id": ".corporate_id",
			},
			ui: {
				'display': '.display',
				'pass': '.pass',
				'modal': '#account_modal',
				'upBtn': '.update',
				'close': '.close',
				'cancel': '.cancel',
				'corporate_id_modal': '#corporate_id_modal',
				'accnt_no_group' : '.accnt_no_group',
				'accnt_no':'#accnt_no',
				'user_id': '#user_id',
				'user_name': '#user_name',
				'login_disp_name': '#login_disp_name',
				'position_name': '#position_name',
				'mail_address': '#mail_address',
				'user_type': '#user_type',
				'password': '#password',
				'password_confirm': '#password_confirm',
				'display_type': '#display_type',
				'datetimepicker': '.datetimepicker',
			},
			bindings: {
				'#corporate_id_modal': 'corporate_id_modal',
				'#agreement_no': 'agreement_no',
				'#user_id': 'user_id',
				'#user_name': 'user_name',
				'#mail_address': 'mail_address',

			},
			onRender: function() {
				var that = this;
			},
			onShow: function() {

			},

			events: {
				"click @ui.close": function(e){
					var model = new this.collection.model();
					this.collection.unshift(model);
					this.ui.modal.modal('hide');
					this.reset();
				},
				"click @ui.cancel": function(e){
					var model = new this.collection.model();
					this.collection.unshift(model);
					this.ui.modal.modal('hide');
					$("#password-group").removeClass("hidden");
					$("#corporate_id_modal").attr('disabled',false);
					this.reset();
				},
				"click @ui.upBtn": function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					var model = new this.collection.model();
					var corporate_id = $("select[name='corporate_id_modal']").val();//モーダル用のコーポレートid
					model.set('corporate_id', corporate_id);
					model.set('user_id', this.ui.user_id.val());
					model.set('user_name', this.ui.user_name.val());
					model.set('login_disp_name', this.ui.login_disp_name.val());
					model.set('position_name', this.ui.position_name.val());
					model.set('user_type', this.ui.user_type.val());
					model.set('mail_address', this.ui.mail_address.val());
					model.set('password', this.ui.password.val());
					model.set('password_confirm', this.ui.password_confirm.val());
					$("#corporate_id_modal").attr('disabled',true);
					$(".errors").css("display", "block");
					//if(this.ui.password.val() == this.ui.password_confirm.val()){
					//	console.log('same');
					//}else{
					//	console.log('diffrent');
					//}

					var that = this;
					var errors = model.validate();
					if (errors){
						this.triggerMethod('showAlerts', errors);
						return;
					}
					model.url = App.api.AC0020;
					var cond = {
						"scr": this.ui.display.text(),
						"type": this.ui.display_type.val(),
						"cond": model.getReq()
					};
					model.fetchMx({
						data:cond,
						success:function(res){
							var errors = res.get('errors');
							if(errors) {
								that.triggerMethod('showAlerts', errors);
								return;
							}
							that.collection.unshift(model);
							that.ui.modal.modal('hide');
							var type = that.ui.display_type.val();
							if( type == '1'){
								alert('アカウントを編集しました。');
							}else if(type == '2'){
								alert('アカウントを削除しました。');
							}else if(type == '3'){
								alert('アカウントロックを解除しました。');
							}else {
								alert('アカウントを登録しました。');
								$(".listTable").css('display', 'block');
							}
							that.reset();
							that.triggerMethod('reload');
						}

					});

				}
			},
			reset: function(){
				//console.log(this);
				this.triggerMethod('hideAlerts');
				$('#corporate_id_modal').val('1');
				this.ui.accnt_no.val('');
				this.ui.user_id.val('');
				this.ui.password.val('');
				this.ui.password_confirm.val('');
				this.ui.user_name.val('');
				this.ui.login_disp_name.val('');
				this.ui.position_name.val('');
				this.ui.user_type.val('1');
				this.ui.mail_address.val('');
				this.ui.display.text('アカウント追加');
				this.ui.upBtn.text('追加');
				this.ui.display_type.val('');
				$('#corporate_id_modal').attr('disabled',false);
				this.ui.accnt_no.attr('readonly',true);
				this.ui.user_id.attr('readonly',false);
				this.ui.password.attr('readonly',false);
				this.ui.user_name.attr('readonly',false);
				this.ui.login_disp_name.attr('readonly',false);
				this.ui.position_name.attr('readonly',false);
				this.ui.user_type.attr('disabled',false);
				this.ui.mail_address.attr('readonly',false);
			},
			showMessage: function(model, display_type) {
				this.ui.modal.modal('show');
				$('#corporate_id_modal').val(model.get('corporate_id'));//モデルのコーポレートidを取得し、modalのvalに設定
				this.ui.user_id.val(model.get('user_id'));
				this.ui.accnt_no.val(model.get('accnt_no'));
				this.ui.user_name.val(model.get('user_name'));
				this.ui.login_disp_name.val(model.get('login_disp_name'));
				this.ui.position_name.val(model.get('position_name'));
				this.ui.user_type.val(model.get('user_type'));
				this.ui.mail_address.val(model.get('mail_address'));

				this.ui.display_type.val(display_type);
				if (display_type === '1') {

					var display_str = 'アカウント編集';
					var button_str = '更新';
					this.ui.user_id.attr('readonly',true);
					this.ui.accnt_no.attr('readonly',true);
					this.ui.login_disp_name.attr('readonly',false);
					this.ui.position_name.attr('readonly',false);
					this.ui.user_type.attr('disabled',false);
					this.ui.mail_address.attr('readonly',false);
					this.ui.pass.text('※パスワードを変更する場合は入力して下さい。未入力の場合は変更されません。');
					$("#password-group").addClass("hidden");
					$("#corporate_id_modal").attr('disabled',true);
				} else if (display_type === '2') {
					var display_str = 'アカウント削除';
					var button_str = '削除';
					this.ui.user_id.attr('readonly',true);
					this.ui.user_name.attr('readonly',true);
					this.ui.accnt_no.attr('readonly',true);
					this.ui.login_disp_name.attr('readonly',true);
					this.ui.position_name.attr('readonly',true);
					this.ui.user_type.attr('disabled',true);
					this.ui.mail_address.attr('readonly',true);
					$("#password-group").addClass("hidden");
					$("#corporate_id_modal").attr('disabled',true);
				} else if (display_type === '3') {
					var display_str = 'アカウントロック解除';
					var button_str = '解除';
					//this.ui.password.val('********');
					this.ui.user_id.attr('readonly',true);
					//this.ui.password.attr('readonly',true);
					this.ui.accnt_no.attr('readonly',true);
					this.ui.login_disp_name.attr('readonly',true);
					this.ui.user_name.attr('readonly',true);
					this.ui.position_name.attr('readonly',true);
					this.ui.user_type.attr('disabled',true);
					this.ui.mail_address.attr('readonly',true);
					$("#password-group").addClass("hidden");
					$("#corporate_id_modal").attr('disabled',true);
				} else {
					var display_str = 'アカウント追加';
					var button_str = '追加';
					

				}
				this.ui.display.text(display_str);
				this.ui.upBtn.text(button_str);
			},
		});
	});
});
