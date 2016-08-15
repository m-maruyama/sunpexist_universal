define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminInfoModal = Backbone.Model.extend({
			initialize: function() {
				_.extend(this, Backbone.Validation.mixin);
			},
			defaults: {
				index: null,
				display_order: null,
				open_date: null,
				close_date: null,
				message: null
			},
			getReq: function(){
				return {
					index: this.get('index'),
					display_order: this.get('display_order'),
					open_date: this.get('open_date'),
					close_date: this.get('close_date'),
					message: this.get('message')
				};
			},
			validation:  {
				"display_order": [
					{
						required:true,
						msg: "表示順を入力して下さい。"
					}
				],
				"open_date": [
					{
						required:true,
						msg: "公開開始日時を入力して下さい。"
					},
				],
				"close_date": [
					{
						required:true,
						msg: "公開終了日時を入力して下さい。"
					}
				],
				"message": [
					{
						required:true,
						msg: "表示メッセージを入力して下さい。"
					},
				],
			},
		});
	});
});
