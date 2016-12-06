define([
	'app',
	'../Templates',
	'./OrderSendListItem',
	"entities/models/Pager",
	"entities/models/OrderSendAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.OrderSendListItem = Marionette.ItemView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.orderSendListItem,
			tagName: "tr",
			ui: {
				"wearer_change": "#wearer_change",
				"order_check": ".order_check",
				"orderSend_cancel": ".orderSend_cancel",
				"order_cancel": ".order_cancel",
				"updBtn": ".updBtn",
			},
			onRender: function() {
				var that = this;
			},
			events: {
				'click @ui.orderSend_cancel': function(e){
					e.preventDefault();
					var that = this;
					var osc_vals = this.ui.orderSend_cancel.val();
					var osc_val = osc_vals.split(':');
					var data = {
						"corporate_id": osc_val[0],
						"rntl_cont_no": osc_val[1],
						"werer_cd": osc_val[2],
						"rntl_sect_cd": osc_val[3],
						"job_type_cd": osc_val[4],
						"order_sts_kbn": osc_val[5],
						"order_reason_kbn": osc_val[6],
						"wst_order_req_no": osc_val[7],
						"order_req_no": osc_val[8],
						"rtn_order_req_no": osc_val[9]
					};
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '発注送信処理-発注取消-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": osc_val[3],
						"rntl_cont_no": osc_val[1]
					};
					modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var type = "cm0130_res";
								var res_val = res.attributes;
								if(res_val.chk_flg == false){
									alert(res_val["error_msg"]);
									return true;
								}else{
									if(window.confirm('発注No.' + data["wst_order_req_no"] + 'の発注送信キャンセルを実行します。\nよろしいですか？')) {
										var modelForUpdate = that.model;
										modelForUpdate.url = App.api.OS0012;
										var cond = {
											"scr": '発注送信処理-発注送信キャンセル',
											"log_type": '2',
											"data": data
										};
										modelForUpdate.fetchMx({
											data:cond,
											success:function(res){
												var res_list = res.attributes;
												if (res_list["error_code"] == "0") {
													that.triggerMethod('reload');
													$.unblockUI();
												}else {
													$.unblockUI();
													alert("更新処理中にエラーが発生しました。");
												}
											}
										});
									}
								}
							}
					});
				},
				'click @ui.order_cancel': function(e){
					e.preventDefault();
					var that = this;
					var osc_vals = this.ui.order_cancel.val();
					var osc_val = osc_vals.split(':');
					var data = {
						"corporate_id": osc_val[0],
						"rntl_cont_no": osc_val[1],
						"werer_cd": osc_val[2],
						"rntl_sect_cd": osc_val[3],
						"job_type_cd": osc_val[4],
						"order_sts_kbn": osc_val[5],
						"order_reason_kbn": osc_val[6],
						"wst_order_req_no": osc_val[7],
						"order_req_no": osc_val[8],
						"rtn_order_req_no": osc_val[9]
					};
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '発注送信処理-発注取消-更新可否チェック',
						"log_type": '1',
						"rntl_sect_cd": osc_val[3],
						"rntl_cont_no": osc_val[1]
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var transition = "";
							var res_val = res.attributes;
							// 種別：貸与開始
							if (
								data["order_sts_kbn"] == '1' &&
								(data["order_reason_kbn"] == '01' ||
								data["order_reason_kbn"] == '02' ||
								data["order_reason_kbn"] == '04' ||
								data["order_reason_kbn"] == '19')
						 	)
							{
								transition = "WO0015_req";
							}
							// 種別：追加貸与
							if (data["order_sts_kbn"] == '1' && data["order_reason_kbn"] == '03') {
								transition = "WR0016_req";
							}
							// 種別：貸与終了
							if (
								data["order_sts_kbn"] == '2' &&
								(data["order_reason_kbn"] == '05' ||
								data["order_reason_kbn"] == '06' ||
								data["order_reason_kbn"] == '08' ||
								data["order_reason_kbn"] == '20')
						 	)
							{
								transition = "WN0018_req";
							}
							// 種別：不要品返却
							if (data["order_sts_kbn"] == '2' && data["order_reason_kbn"] == '07') {
								transition = "WR0021_req";
							}
							// 種別：職種変更または異動
							if (data["order_sts_kbn"] == '5') {
								transition = "WC0020_req";
							}
							// 種別：サイズ交換
							if (data["order_sts_kbn"] == '3') {
								transition = "WX0012_req";
							}
							// 種別：その他交換
							if (data["order_sts_kbn"] == '4') {
								transition = "WC0030_req";
							}
							// 種別：着用者編集
							if (data["order_sts_kbn"] == '6') {
								transition = "WU0013_req";
							}

							that.onShow(res_val, type, transition, data);
						}
					});
				}
			},
			onShow: function(val, type, transition, data) {
				var that = this;

				if (type == "cm0130_res") {
					if (!val["chk_flg"]) {
						alert(val["error_msg"]);
					} else {
						var type = transition;
						var res_val = "";
					}
				}
				// 貸与開始-発注取消
				if (type == "WO0015_req") {
					if(window.confirm('発注No.' + data["wst_order_req_no"] + 'の発注取消を実行します。\nよろしいですか？')) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var data = {
							"rntl_cont_no": data["rntl_cont_no"],
							"werer_cd": data["werer_cd"],
							"rntl_sect_cd": data["rntl_sect_cd"],
							"job_type_cd": data["job_type_cd"],
							"order_req_no": data["order_req_no"]
						};

						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WO0015;
						var cond = {
							"scr": '貸与開始-発注取消',
							"log_type": '2',
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var res_list = res.attributes;
								if (res_list["error_code"] == "0") {
									that.triggerMethod('reload');
									$.unblockUI();
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました。');
								}
							}
						});
					}
				}
				// 追加貸与-発注取消
				if (type == "WR0016_req") {
					if(window.confirm('発注No.' + data["wst_order_req_no"] + 'の発注取消を実行します。\nよろしいですか？')) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var data = {
							"rntl_cont_no": data["rntl_cont_no"],
							"werer_cd": data["werer_cd"],
							"rntl_sect_cd": data["rntl_sect_cd"],
							"job_type_cd": data["job_type_cd"],
							"order_req_no": data["order_req_no"]
						};

						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WR0016;
						var cond = {
							"scr": '追加貸与-発注取消',
							"log_type": '2',
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var res_list = res.attributes;
								if (res_list["error_code"] == "0") {
									that.triggerMethod('reload');
									$.unblockUI();
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました。');
								}
							}
						});
					}
				}
				// 貸与終了-発注取消
				if (type == "WN0018_req") {
					if(window.confirm('発注No.' + data["wst_order_req_no"] + 'の発注取消を実行します。\nよろしいですか？')) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var data = {
							"rntl_cont_no": data["rntl_cont_no"],
							"werer_cd": data["werer_cd"],
							"rntl_sect_cd": data["rntl_sect_cd"],
							"job_type_cd": data["job_type_cd"],
							"order_req_no": data["order_req_no"]
						};

						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WN0018;
						var cond = {
							"scr": '貸与終了-発注取消',
							"log_type": '2',
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var res_list = res.attributes;
								if (res_list["error_code"] == "0") {
									that.triggerMethod('reload');
									$.unblockUI();
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました。');
								}
							}
						});
					}
				}
				// 不要品返却-発注取消
				if (type == "WR0021_req") {
					if(window.confirm('発注No.' + data["wst_order_req_no"] + 'の発注取消を実行します。\nよろしいですか？')) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var data = {
							"rntl_cont_no": data["rntl_cont_no"],
							"werer_cd": data["werer_cd"],
							"rntl_sect_cd": data["rntl_sect_cd"],
							"job_type_cd": data["job_type_cd"],
							"order_req_no": data["order_req_no"],
							"return_req_no": data["rtn_order_req_no"]
						};

						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WR0021;
						var cond = {
							"scr": '不要品返却-発注取消',
							"log_type": '2',
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var res_list = res.attributes;
								if (res_list["error_code"] == "0") {
									that.triggerMethod('reload');
									$.unblockUI();
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました。');
								}
							}
						});
					}
				}
				// 職種変更または異動-発注取消
				if (type == "WC0020_req") {
					if(window.confirm('発注No.' + data["wst_order_req_no"] + 'の発注取消を実行します。\nよろしいですか？')) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var data = {
							"rntl_cont_no": data["rntl_cont_no"],
							"werer_cd": data["werer_cd"],
							"rntl_sect_cd": data["rntl_sect_cd"],
							"job_type_cd": data["job_type_cd"],
							"order_req_no": data["order_req_no"],
							"return_req_no": data["rtn_order_req_no"]
						};

						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WC0020;
						var cond = {
							"scr": '職種変更または異動-発注取消',
							"log_type": '2',
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var res_list = res.attributes;
								if (res_list["error_code"] == "0") {
									that.triggerMethod('reload');
									$.unblockUI();
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました。');
								}
							}
						});
					}
				}
				// サイズ交換-発注取消
				if (type == "WX0012_req") {
					if(window.confirm('発注No.' + data["wst_order_req_no"] + 'の発注取消を実行します。\nよろしいですか？')) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var data = {
							"rntl_cont_no": data["rntl_cont_no"],
							"werer_cd": data["werer_cd"],
							"rntl_sect_cd": data["rntl_sect_cd"],
							"job_type_cd": data["job_type_cd"],
							"order_req_no": data["order_req_no"],
							"return_req_no": data["rtn_order_req_no"]
						};

						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WX0012;
						var cond = {
							"scr": 'サイズ交換-発注取消',
							"log_type": '2',
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var res_list = res.attributes;
								if (res_list["error_code"] == "0") {
									that.triggerMethod('reload');
									$.unblockUI();
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました。');
								}
							}
						});
					}
				}
				// その他 交換-発注取消
				if (type == "WC0030_req") {
					if(window.confirm('発注No.' + data["wst_order_req_no"] + 'の発注取消を実行します。\nよろしいですか？')) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var data = {
							"rntl_cont_no": data["rntl_cont_no"],
							"werer_cd": data["werer_cd"],
							"rntl_sect_cd": data["rntl_sect_cd"],
							"job_type_cd": data["job_type_cd"],
							"order_req_no": data["order_req_no"],
							"return_req_no": data["rtn_order_req_no"]
						};

						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WX0030;
						var cond = {
							"scr": 'その他交換-発注取消',
							"log_type": '2',
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var res_list = res.attributes;
								if (res_list["error_code"] == "0") {
									that.triggerMethod('reload');
									$.unblockUI();
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました。');
								}
							}
						});
					}
				}
				// 着用者編集-発注取消
				if (type == "WU0013_req") {
					if(window.confirm('発注No.' + data["wst_order_req_no"] + 'の発注取消を実行します。\nよろしいですか？')) {
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 発注取消中...</p>' });
						var data = {
							"rntl_cont_no": data["rntl_cont_no"],
							"werer_cd": data["werer_cd"],
							"rntl_sect_cd": data["rntl_sect_cd"],
							"job_type_cd": data["job_type_cd"],
							"order_req_no": data["wst_order_req_no"],
							"return_req_no": data["rtn_order_req_no"]
						};

						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.WU0013;
						var cond = {
							"scr": '着用者編集-発注取消',
							"log_type": '2',
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var res_list = res.attributes;
								if (res_list["error_code"] == "0") {
									that.triggerMethod('reload');
									$.unblockUI();
								} else {
									$.unblockUI();
									alert('発注取消中にエラーが発生しました。');
								}
							}
						});
					}
				}
			}
		});
	});
});
