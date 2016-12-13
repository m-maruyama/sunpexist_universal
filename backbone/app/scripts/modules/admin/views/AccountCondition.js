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
		var mode ='';
		var search_flg ='';
		Views.AccountCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.accountCondition,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"corporate_id": ".corporate_id",
				"agreement_no": ".agreement_no",
				"user_id": ".user_id",
				"user_name": ".user_name",
				"mail_address": ".mail_address"
			},
			ui: {
				'corporate_id': '#corporate_id',
				'agreement_no': '#agreement_no',
				'user_id': '#user_id',
				'user_name': '#user_name',
				'mail_address': '#mail_address',
				"search": '.search'
			},
			bindings: {
				'#corporate_id': 'corporate_id',
				'#agreement_no': 'agreement_no',
				'#user_id': 'user_id',
				'#user_name': 'user_name',
				'#mail_address': 'mail_address',
				'#search': 'search'
			},
			onRender: function() {
				var that = this;
				this.triggerMethod('hideAlerts');
				var page_from = window.sessionStorage.getItem('page_from');
				var account_param = JSON.parse(window.sessionStorage.getItem('account_param'));
				if(page_from == 'password'){
					$(".listTable").css('display', 'block');
					this.ui.user_id.val(account_param.login_id);
					this.ui.user_name.val(account_param.user_name);
					this.ui.mail_address.val(account_param.mail_address);
					var corporate_id = $("select[name='corporate_id']").val();
					var agreement_no = $("select[name='agreement_no']").val();
					this.model.set('corporate_id', corporate_id);
					this.model.set('agreement_no', agreement_no);
					this.model.set('user_id', this.ui.user_id.val());
					this.model.set('user_name', this.ui.user_name.val());
					this.model.set('mail_address', this.ui.mail_address.val());
					this.model.set('search', this.ui.search.val());
					this.model.set('page_no', account_param.page_no);
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}
					search_flg = 'on';
					this.triggerMethod('click:search',this.model.get('sort_key'),this.model.get('account'));
				}
			},
			onShow: function() {
			},
			events: {
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					$(".listTable").css('display', 'block');
					var corporate_id = $("select[name='corporate_id']").val();
					var agreement_no = $("select[name='agreement_no']").val();
					this.model.set('page_no', '');
					this.model.set('corporate_id', corporate_id);
					this.model.set('agreement_no', agreement_no);
					this.model.set('user_id', this.ui.user_id.val());
					this.model.set('user_name', this.ui.user_name.val());
					this.model.set('mail_address', this.ui.mail_address.val());
					this.model.set('search', this.ui.search.val());
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}
					search_flg = 'on';
					this.triggerMethod('click:search',this.model.get('sort_key'),this.model.get('account'));
				},
				'change @ui.corporate_id': function(){
					this.ui.corporate_id = $('#corporate_id');
				},
				'change @ui.section': function(){
					this.ui.user_id = $('#user_id');
				},
				'change @ui.job_type': function(){
					this.ui.user_name = $('#user_name');
				},
				'change @ui.input_item': function(){
					this.ui.mail_address = $('#mail_address');
				}
			}
		});
	});
});
