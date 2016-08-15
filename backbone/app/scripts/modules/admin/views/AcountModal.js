define([
	'app',
	'../Templates',
	'../behaviors/Alerts',
	'bootstrap',
	'bootstrap-datetimepicker'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.AcountModal = Marionette.ItemView.extend({
			template: App.Admin.Templates.acountModal,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'display': '.display',
				'pass': '.pass',
				'modal': '#acount_modal',
				'upBtn': '.update',
				'close': '.close',
				'cancel': '.cancel',
				'user_id': '#user_id',
				'user_name': '#user_name',
				'position_name': '#position_name',
				'user_type': '#user_type',
				'password': '#password',
				'display_type': '#display_type',
				'datetimepicker': '.datetimepicker',
			},
			bindings: {
			},
			onRender: function() {
				// this.ui.datetimepicker.datetimepicker({
					// format: 'YYYY/MM/DD HH:mm',
					// //useCurrent: false,
					// minDate: new Date(),
					// sideBySide:true
				// });
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
					this.reset();
				},
				"click @ui.upBtn": function(e){
					e.preventDefault();
					var model = new this.collection.model();
					model.set('user_id', this.ui.user_id.val());
					model.set('user_name', this.ui.user_name.val());
					model.set('position_name', this.ui.position_name.val());
					model.set('user_type', this.ui.user_type.val());
					model.set('password', this.ui.password.val());
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
							}
							that.reset();
							that.triggerMethod('reload');
						}
						
					});
					
				}
			},
			reset: function(){
				this.triggerMethod('hideAlerts');
				this.ui.user_id.val('');
				this.ui.password.val('');
				this.ui.user_name.val('');
				this.ui.position_name.val('');
				this.ui.user_type.val('1');
				this.ui.display.text('アカウント追加');
				this.ui.upBtn.text('追加');
				this.ui.display_type.val('');
				this.ui.user_id.attr('readonly',false);
				this.ui.password.attr('readonly',false);
				this.ui.user_name.attr('readonly',false);
				this.ui.position_name.attr('readonly',false);
				this.ui.user_type.attr('readonly',false);
			},
			showMessage: function(model, display_type) {
				this.ui.modal.modal('show');
				this.ui.user_id.val(model.get('user_id'));
				this.ui.user_name.val(model.get('user_name'));
				this.ui.position_name.val(model.get('position_name'));
				this.ui.user_type.val(model.get('user_type'));
				this.ui.display_type.val(display_type);
				if (display_type === '1') {
					this.ui.password.val('');
					var display_str = 'アカウント編集';
					var button_str = '更新';
					this.ui.user_id.attr('readonly',true);
					this.ui.pass.text('※パスワードを変更する場合は入力して下さい。未入力の場合は変更されません。');
				} else if (display_type === '2') {
					var display_str = 'アカウント削除';
					var button_str = '削除';
					this.ui.password.val('********');
					this.ui.user_id.attr('readonly',true);
					this.ui.password.attr('readonly',true);
					this.ui.user_name.attr('readonly',true);
					this.ui.position_name.attr('readonly',true);
					this.ui.user_type.attr('readonly',true);
				} else if (display_type === '3') {
					var display_str = 'アカウントロック解除';
					var button_str = '解除';
					this.ui.password.val('********');
					this.ui.user_id.attr('readonly',true);
					this.ui.password.attr('readonly',true);
					this.ui.user_name.attr('readonly',true);
					this.ui.position_name.attr('readonly',true);
					this.ui.user_type.attr('readonly',true);
				} else { 
					var display_str = 'アカウント追加';
					var button_str = '追加';
				}
				this.ui.display.text(display_str);
				this.ui.upBtn.text(button_str);
			}
		});
	});
});