define([
	'app',
	'../Templates',
	'./WearerChangeListItem',
	"entities/models/WearerChangeAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerChangeListItem = Marionette.ItemView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerChangeListItem,
			tagName: "tr",
			ui: {
				"wearer_change": "#wearer_change"
			},
			events: {
				'click @ui.wearer_change': function(e){
					var that = this;

					e.preventDefault();
					var we_vals = this.ui.wearer_change.val();
					var we_val = we_vals.split(':');
					var data = {
						'rntl_cont_no': we_val[0],
						'werer_cd': we_val[1],
						'cster_emply_cd': we_val[2],
						'sex_kbn': we_val[3],
						'rntl_sect_cd': we_val[4],
						'job_type_cd': we_val[5],
						'order_reason_kbn': we_val[6],
						'ship_to_cd': we_val[7],
						'ship_to_brnch_cd': we_val[8],
						'order_tran_flg': we_val[9],
						'wearer_tran_flg': we_val[10],
						'order_req_no': we_val[11],
					};

					// 発注入力遷移前に発注NGパターンチェック実施
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WC0023;
					var cond = {
						"scr": '職種変更または異動-発注NGパターンチェック',
						"log_type": '3',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["err_cd"] == "0") {
								var type = "WC0011_req";
								var transition = "";
								var data = cond["data"];
								that.onShow(res_val, type, transition, data);
							} else {
								// エラーアラート表示
								alert(res_val["err_msg"]);
							}
						}
					});
				}
			},
			onShow: function(val, type, transition, data) {
				var that = this;

				if (type == "WC0011_req") {
					// 遷移時のPOSTパラメータ代行処理
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WC0011;
					var cond = {
						"scr": '発注入力（職種変更または異動）POST値保持',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var $form = $('<form/>', {'action': '/universal/wearer_change_order.html', 'method': 'post'});
							$form.appendTo(document.body);
							$form.submit();
						}
					});
				}
			},
		});
	});
});
