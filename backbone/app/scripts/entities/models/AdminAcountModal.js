define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminAcountModal = Backbone.Model.extend({
			initialize: function() {
				_.extend(this, Backbone.Validation.mixin);
			},
			defaults: {
				user_id: null,
				user_name: null,
				position_name: null,
				user_type: null,
				password: null,
				deleteFlag: false
			},
			getReq: function(){
				return {
					user_id: this.get('user_id'),
					user_name: this.get('user_name'),
					position_name: this.get('position_name'),
					user_type: this.get('user_type'),
					password: this.get('password')
				};
			},
			validation:  {
				"user_id": [
					{
						required:true,
						msg: "ログインIDを入力して下さい。"
					}
				],
				// "password": [
					// {
						// required:true,
						// msg: "パスワードを入力して下さい。"
					// },
					// {
						// rangeLength: [8, 9999],
						// msg: "パスワードは8文字以上で入力して下さい。"
					// }
				// ],
				"user_name": [
					{
						required:true,
						msg: "ユーザ名称を入力して下さい。"
					}
				],
				"position_name": [
					{
						required:true,
						msg: "所属を入力して下さい。"
					},
				],
				"user_type": [
					{
						required:true,
						msg: "管理権限を入力して下さい。"
					}
				],
			},
			// reset: function(){
				// this.set('oldpassword', null);
				// this.set('newpassword', null);
				// this.set('confirm', null);
			// }
		});
	});
});
