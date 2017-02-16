define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminAccountModal = Backbone.Model.extend({
			initialize: function() {
				_.extend(this, Backbone.Validation.mixin);
			},
			defaults: {
				corporate_id: null,
				accnt_no : null,
				user_id: null,
				user_name: null,
				login_disp_name: null,
				position_name: null,
				user_type: null,
				mail_address: null,
				password: null,
				password_confirm: null,
				deleteFlag: false
			},
			getReq: function(){
				return {
					corporate_id: this.get('corporate_id'),
					//accnt_no = this.get('accnt_no'),
					user_id: this.get('user_id'),
					user_name: this.get('user_name'),
					login_disp_name: this.get('login_disp_name'),
					position_name: this.get('position_name'),
					mail_address: this.get('mail_address'),
					user_type: this.get('user_type'),
					password: this.get('password'),
					password_confirm: this.get('password_confirm')
				};
				//console.log(password);
			},
			validation:  {

					// {
						// rangeLength: [8, 9999],
						// msg: "パスワードは8文字以上で入力して下さい。"
					// }
				// ],

				"password_confirm": [
					{
			      equalTo: 'password',
						 msg: "パスワード確認がパスワードと一致していません。"
			     }
				]

			},

			// reset: function(){
				// this.set('oldpassword', null);
				// this.set('newpassword', null);
				// this.set('confirm', null);
			// }
		});
	});
});
