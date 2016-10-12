define([
	'app',
	'../Templates',
	'backbone.stickit'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Home = Marionette.ItemView.extend({
			template: App.Admin.Templates.home,
			model: new Backbone.Model(),
			ui: {
				"text_1": ".text_1",
					"wearer_input": "#wearer_input"
			},
			bindings: {
				'.text_1': 'text_1'
			},
			onShow: function() {
				var that = this;
				var cond = {
					"scr": 'トップページ'
				};
				var modelForUpdate = this.model;
					modelForUpdate.url = App.api.HM0010;
					modelForUpdate.fetchMx({
					 data: cond,
					 success:function(res){
						 var errors = res.get('errors');
						 if(errors) {
							 var errorMessages = errors.map(function(v){
								 return v.error_message;
							 });
							 that.triggerMethod('showAlerts', errorMessages);
						 }
						 that.render();
					 }
					});
			},

			events: {
				'click @ui.wearer_input': function(e){
							var $form = $('<form/>', {'action': '/universal/wearer_input.html', 'method': 'post'});

							window.sessionStorage.setItem('referrer', 'home');
							$form.appendTo(document.body);
							$form.submit();
						}
				}
		});
	});
});