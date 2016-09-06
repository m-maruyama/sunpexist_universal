define(["app"],function(App) {
	'use strict';

	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminAcount = Backbone.Model.extend({
			url: App.api.AC0010,

			getReq: function() {
				var result = {
					accnt_no : null,
					corporate_id : null,
					agreement_no : null,
					user_id : null,
					user_name : null,
					login_disp_name : null,
					position_name : null,
					user_type : null,
					password : null,
					mail_address : null,
					last_pass_word_upd_date : null,
					lock : null,
					edit : null,
					del : null

				};
				if(this.get('accnt_no')) {
					result.accnt_no = this.get('accnt_no');
				}
				if(this.get('corporate_id')) {
					result.corporate_id = this.get('corporate_id');
				}
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
				}
				if(this.get('user_id')) {
					result.user_id = this.get('user_id');
				}
				if(this.get('user_name')) {
					result.user_name = this.get('user_name');
				}
				if(this.get('login_disp_name')) {
					result.login_disp_name = this.get('login_disp_name');
				}
				if(this.get('position_name')) {
					result.position_name = this.get('position_name');
				}
				if(this.get('user_type')) {
					result.user_type = this.get('user_type');
				}
				if(this.get('password')) {
					result.password = this.get('password');
				}
				if(this.get('mail_address')) {
					result.mail_address = this.get('mail_address');
				}
				if(this.get('last_pass_word_upd_date')) {
					result.last_pass_word_upd_date = this.get('last_pass_word_upd_date');
				}
				if(this.get('lock')) {
					result.lock = this.get('lock');
				}
				if(this.get('edit')) {
					result.edit = this.get('edit');
				}
				if(this.get('del')) {
					result.del = this.get('del');
				}
				return result;
			}
		});
	});
});
