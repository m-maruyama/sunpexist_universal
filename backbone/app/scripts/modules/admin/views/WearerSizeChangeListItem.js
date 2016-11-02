define([
	'app',
	'../Templates',
	'./WearerChangeListItem',
	"entities/models/WearerSizeChangeAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerSizeChangeListItem = Marionette.ItemView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerSizeChangeListItem,
			tagName: "tr",
			ui: {
				"wearer_add": "#wearer_add",
				"wearer_return": "#wearer_return"
			},
			events: {
				// 追加貸与ボタン
				'click @ui.wearer_add': function(e){
					var that = this;

					e.preventDefault();
					var we_vals = this.ui.wearer_add.val();
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
						'return_req_no': we_val[12],
					};

					// 発注入力遷移前に発注NGパターンチェック実施
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0012;
					var cond = {
						"scr": 'サイズ交換/その他交換(サイズ交換)-発注NGパターンチェック',
						"log_type": '3',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["err_cd"] == "0") {
								var type = "WSC0010_req";
								var transition = "add";
								var data = cond["data"];
								that.onShow(res_val, type, transition, data);
							} else {
								// NGエラーアラート表示
								alert(res_val["err_msg"]);
							}
						}
					});
				},
				// 不要品返却ボタン
				'click @ui.wearer_return': function(e){
					var that = this;

					e.preventDefault();
					var we_vals = this.ui.wearer_return.val();
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
						'return_req_no': we_val[12],
					};

					// 発注入力遷移前に発注NGパターンチェック実施
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0013;
					var cond = {
						"scr": 'サイズ交換/その他交換(その他交換)-発注NGパターンチェック',
						"log_type": '3',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["err_cd"] == "0") {
								var type = "WSC0010_req";
								var transition = "return";
								var data = cond["data"];
								that.onShow(res_val, type, transition, data);
							} else {
								// NGエラーアラート表示
								alert(res_val["err_msg"]);
							}
						}
					});
				}
			},
			onShow: function(val, type, transition, data) {
				var that = this;
				// 交換発注入力遷移
				if (type == "WSC0010_req") {
					// 遷移時のPOSTパラメータ代行処理
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0011;
					var cond = {
						"scr": '発注入力（サイズ交換/その他交換）POST値保持',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var cond = new Array(
								$("select[name='agreement_no']").val(),
								$("input[name='cster_emply_cd']").val(),
								$("input[name='werer_name']").val(),
								$("select[name='sex_kbn']").val(),
								$("select[name='section']").val(),
								$("select[name='job_type']").val(),
								document.getElementsByClassName("active")[0].getElementsByTagName("a")[0].text
							);
							var arr_str = cond.toString();
							// 検索項目値、ページ数のセッション保持
							window.sessionStorage.setItem("wearer_other_cond", arr_str);
							if (transition == "add") {
								// サイズ交換の発注入力画面へ遷移
								var $form = $('<form/>', {'action': '/universal/wearer_size_change_order.html', 'method': 'post'});
								$form.appendTo(document.body);
								$form.submit();
							} else if (transition == "return") {
								// その他交換の発注入力画面へ遷移
								var $form = $('<form/>', {'action': '/universal/wearer_other_change_order.html', 'method': 'post'});
								$form.appendTo(document.body);
								$form.submit();
							}
						}
					});
				}
			}
		});
	});
});
