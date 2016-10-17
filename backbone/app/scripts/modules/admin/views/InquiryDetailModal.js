define([
	'app',
	'handlebars',
	'../Templates',
	'blockUI',
	'bootstrap',
	'bootstrap-datetimepicker'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InquiryDetailModal = Marionette.ItemView.extend({
			defaults: {
				agreement_no: null,
				rntl_sect_cd: null,
				rntl_sect_name: null,
				yyyymm: null,
				staff_total: null
			},
			initialize: function(options) {
					this.options = options || {};
					this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),

			template: App.Admin.Templates.inquiryDetailModal,
			emptyView: Backbone.Marionette.ItemView.extend({
				tagName: "tr",
				template: App.Admin.Templates.deliveryEmpty,
			}),
			ui: {
				'modal': '#manpower-detail-modal'
			},
			onRender: function() {
			},
			fetchDetail: function(options) {
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });

				var agreement_no = this.options.agreement_no;
				var rntl_sect_cd = this.options.rntl_sect_cd;
				var rntl_sect_name = this.options.rntl_sect_name;
				var yyyymm = this.options.yyyymm;
				var staff_total = this.options.staff_total;
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.MI0020;
				var cond = {
					"scr": '請求書データ詳細',
					"agreement_no": agreement_no,
					"rntl_sect_cd": rntl_sect_cd,
					"rntl_sect_name": rntl_sect_name,
					"yyyymm": yyyymm,
					"staff_total": staff_total
				};

				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var errors = res.get('errors');
						if(errors) {
							var errorMessages = errors.map(function(v){
								return v.error_message;
							});
							that.triggerMethod('showAlerts', errorMessages);
						}
						var res_list = res.attributes;
						that.templateHelpers(res_list);
						that.triggerMethod('fetched');
						$.unblockUI();
					}
				});
			},
			templateHelpers: function(res_list) {
				// 人員明細詳細リスト
				return res_list;
			}
		});
	});
});
