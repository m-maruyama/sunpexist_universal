define([
	'app',
	'../Templates',
	'./WearerEndListItem',
	"entities/models/WearerEndAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerEndListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.wearerEndListItem,
			tagName: "tr",
			ui: {
				"wearer_end": "#wearer_end"
			},
			onRender: function() {
			},
			events: {
				'click @ui.wearer_end': function(e){
					e.preventDefault();
					var we_vals = this.ui.wearer_end.val();
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
					};
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WS0011;
					var cond = {
						"scr": '貸与終了ボタン',
						"cond": data,
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
							location.href = './wearer_end_order.html';
							return;
						}
					});

					var $form = $('<form/>', {'action': '/universal/wearer_end_order.html', 'method': 'post'});

					window.sessionStorage.setItem('referrer', 'wearer_end');
					$form.appendTo(document.body);
					$form.submit();
				}
			},


		});
	});
});