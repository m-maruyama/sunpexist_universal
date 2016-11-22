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
		Views.WearerDetailModal = Marionette.ItemView.extend({
			defaults: {
				agreement_no: null,
				wearer_cd: null,
				cster_emply_cd: null,
			},
			initialize: function(options) {
					this.options = options || {};
					this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),

			template: App.Admin.Templates.wearerDetailModal,
			emptyView: Backbone.Marionette.ItemView.extend({
				tagName: "tr",
				template: App.Admin.Templates.deliveryEmpty,
			}),
			ui: {
				'modal': '#wearer-detail-modal'
			},
			onRender: function() {
			},
			fetchDetail: function(options) {
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });

				var agreement_no = this.options.agreement_no;
				var wearer_cd = this.options.wearer_cd;
				var cster_emply_cd = this.options.cster_emply_cd;
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WE0020;
				var cond = {
					"scr": '着用者詳細',
					"agreement_no": agreement_no,
					"wearer_cd": wearer_cd,
					"cster_emply_cd": cster_emply_cd,
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
						//console.log(res_list);

						that.templateHelpers(res_list);
						that.triggerMethod('fetched');
						if (res_list["individual_flg"] == true) {
							$('.individual_flg').css('display', '');
						}
						$.unblockUI();
					}
				});
			},
			templateHelpers: function(res_list) {
				// 着用者個人情報、貸与情報
				return res_list;
			}
		});
	});
});
