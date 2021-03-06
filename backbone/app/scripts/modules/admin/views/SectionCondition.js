define([
	'app',
	'../Templates',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SectionCondition = Marionette.LayoutView.extend({
			defaults: {
				agreement_no: '',
				section: '',
				not_all_flg: ''
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.sectionCondition,
			model: new Backbone.Model(),
			ui: {
				'section': '#section',
				'section_btn': '#section_btn'
			},
			bindings: {
				'.section': 'section',
				'.section_btn': 'section_btn'
			},
			/*
			onShow: function() {
				var that = this;
				var agreement_no = this.options.agreement_no;
				var section = this.options.section;
				var window_name = this.options.window_name;
				var not_all_flg = this.options.not_all_flg;
				if (not_all_flg != '') {
					var corporate_flg = true;
					var corporate = $('#corporate').val();
				} else {
					var corporate_flg = false;
					var corporate = "";
				}
				var modelForUpdate = this.model;
				if(window_name == 'inquiry'){
					modelForUpdate.url = App.api.CU0013;//お問い合わせの拠点用
				}else{
					modelForUpdate.url = App.api.CM0020;
				}
				var cond = {
					"scr": '拠点',
					"agreement_no": agreement_no,
					"section": section,
					"not_all_flg": not_all_flg,
					"corporate_flg": corporate_flg,
					"corporate": corporate,
				};
				console.log(cond);
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						console.log(res);
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
			*/
			onShow: function() {
				var that = this;
				var agreement_no = this.options.agreement_no;
				var section = this.options.section;
				var window_name = this.options.window_name;
				var not_all_flg = this.options.not_all_flg;
				if (not_all_flg != '') {
					var corporate_flg = true;
					var corporate = $('#corporate').val();
				} else {
					var corporate_flg = false;
					var corporate = "";
				}
				var modelForUpdate = this.model;
				if(window_name == 'inquiry'){
					modelForUpdate.url = App.api.CU0013;//お問い合わせの拠点用
				}else{
					modelForUpdate.url = App.api.CM0020;
				}
				var cond = {
					"scr": '拠点',
					"agreement_no": agreement_no,
					"section": section,
					"not_all_flg": not_all_flg,
					"corporate_flg": corporate_flg,
					"corporate": corporate,
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res) {
						var errors = res.get('errors');
						if (errors) {
							var errorMessages = errors.map(function (v) {
								return v.error_message;
							});
							that.triggerMethod('showAlerts', errorMessages);
						}
						//職種コードが1以上だったらそのまま表示
						if (res.attributes.section_list.length > 1) {
								that.render();
								$(".search").css("display", "block");
							} else {
								//拠点のリトライ
								modelForUpdate.fetchMx({
									data: cond,
									success: function (res) {
										that.render();
										$(".search").css("display", "block");
									}
								});
							}
						}
				});
			},
			events: {
				'click @ui.section_btn': function(e){
					e.preventDefault();
					this.triggerMethod('click:section_btn', this.model);

				}
			}
		});
	});
});
