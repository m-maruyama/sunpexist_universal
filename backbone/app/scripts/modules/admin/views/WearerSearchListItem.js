define([
	'app',
	'../Templates',
	'./WearerSearchListItem',
	"entities/models/WearerAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerSearchListItem = Marionette.ItemView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerSearchListItem,
			tagName: "tr",
			ui: {
				"wearer_order": "#wearer_order"
			},
			onRender: function() {
			},
			events: {
				'click @ui.wearer_order': function(e){
					e.preventDefault();
					var we_vals = this.ui.wearer_order.val();
					var we_val = we_vals.split(':');
					var data = {
						'rntl_cont_no': we_val[0],
						'werer_cd': we_val[1],
						'cster_emply_cd': we_val[2],
						'sex_kbn': we_val[3],
						'rntl_sect_cd': we_val[4],
						'job_type': we_val[5],
						'ship_to_cd': we_val[6],
						'ship_to_brnch_cd': we_val[7],
						'order_reason_kbn': we_val[8],
						'order_tran_flg': we_val[9],
						'wearer_tran_flg': we_val[10],
						'appointment_ymd': we_val[11],
						'resfl_ymd': we_val[12],
						'm_wearer_std_comb_hkey': we_val[13],
						'order_req_no': we_val[14],
						'm_job_type_comb_hkey': we_val[15],
						'm_section_comb_hkey': we_val[16],
					};
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WS0011;
					var cond = {
						"scr": '貸与開始ボタン',
						"data": data,
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
							var $form = $('<form/>', {'action': '/universal/wearer_input.html', 'method': 'post'});

							window.sessionStorage.setItem('referrer', 'wearer_search');
							$form.appendTo(document.body);
							$form.submit();
						}
					});
				}
			},
		});
	});
});
