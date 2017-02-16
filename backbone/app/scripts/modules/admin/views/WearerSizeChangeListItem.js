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
				"wearer_size_change": "#wearer_size_change",
				"wearer_other_change": "#wearer_other_change",
				"download": "#download",
				"download2": "#download2"
			},
			events: {
				// サイズ交換ボタン
				'click @ui.wearer_size_change': function(e){
					var that = this;

					e.preventDefault();
					var we_vals = this.ui.wearer_size_change.val();
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
					modelForUpdate.url = App.api.WSC0012;
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
								var transition = "size_change";
								var data = cond["data"];
								that.onShow(res_val, type, transition, data, 'size');
							} else {
								// JavaScript モーダルで表示
								$('#myModalAlert').modal(); //追加
								//メッセージの修正
								document.getElementById("alert_txt").innerHTML=res_val["err_msg"];
							}
						}
					});
				},
				// その他交換ボタン
				'click @ui.wearer_other_change': function(e){
					var that = this;

					e.preventDefault();
					var we_vals = this.ui.wearer_other_change.val();
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
					modelForUpdate.url = App.api.WSC0013;
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
								var transition = "other_change";
								var data = cond["data"];
								that.onShow(res_val, type, transition, data,'other');
							} else {
								// JavaScript モーダルで表示
								$('#myModalAlert').modal(); //追加
								//メッセージの修正
								document.getElementById("alert_txt").innerHTML=res_val["err_msg"];
							}
						}
					});
				},
				// 返却伝票ダウンロードボタン
				'click @ui.download': function(e){
					e.preventDefault();
					//var printData = [];
					var pdf_vals = e.target.value;

					var pdf_val = pdf_vals.split(':');
					//console.log(pdf_val);
					var printData = new Object();
					printData["rntl_cont_no"] = pdf_val[0];
					printData["order_req_no"] = pdf_val[1]
					// JavaScript モーダルで表示
					$('#myModal').modal('show'); //追加
					//メッセージの修正
					document.getElementById("confirm_txt").innerHTML=App.dl_msg; //追加　このメッセージはapp.jsで定義
					$("#btn_ok").off();
					$("#btn_ok").on('click',function() { //追加
						var cond = {
							"scr": 'PDFダウンロード',
							"cond": printData
						};
						var form = $('<form action="' + App.api.PR0012 + '" method="post"></form>');
						var data = $('<input type="hidden" name="data" />');
						data.val(JSON.stringify(cond));
						form.append(data);
						$('body').append(form);
						form.submit();
						data.remove();
						form.remove();
						form=null;
                        hideModal();
					});
				},
				// 返却伝票ダウンロードボタン
				'click @ui.download2': function(e){
					e.preventDefault();
					//var printData = [];
					var pdf_vals = e.target.value;

					var pdf_val = pdf_vals.split(':');
					//console.log(pdf_val);
					var printData = new Object();
					printData["rntl_cont_no"] = pdf_val[0];
					printData["order_req_no"] = pdf_val[1];
					// JavaScript モーダルで表示
					$('#myModal').modal('show'); //追加
					//メッセージの修正
					document.getElementById("confirm_txt").innerHTML=App.dl_msg; //追加　このメッセージはapp.jsで定義
					$("#btn_ok").off();
					$("#btn_ok").on('click',function() { //追加
						var cond = {
							"scr": 'PDFダウンロード',
							"cond": printData
						};
						var form = $('<form action="' + App.api.PR0012 + '" method="post"></form>');
						var data = $('<input type="hidden" name="data" />');
						data.val(JSON.stringify(cond));
						form.append(data);
						$('body').append(form);
						form.submit();
						data.remove();
						form.remove();
						form = null;
                        hideModal();
					});
				}
			},

			onShow: function(val, type, transition, data, change) {
				var that = this;
				// 交換発注入力遷移
				if (type == "WSC0010_req") {
					// 遷移時のPOSTパラメータ代行処理
					var modelForUpdate = this.model;
					if(change == 'size'){
						//サイズ交換の場合
						modelForUpdate.url = App.api.WSC0011;
					}else{
						//その他交換の場合
						modelForUpdate.url = App.api.WSC0014;
					}
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
							window.sessionStorage.setItem("wearer_size_change_cond", arr_str);
							window.sessionStorage.setItem("referrer","wearer_size_change");
							if (transition == "size_change") {
								// サイズ交換の発注入力画面へ遷移
								var $form = $('<form/>', {'action': '/universal/wearer_exchange_order.html', 'method': 'post'});
								$form.appendTo(document.body);
								$form.submit();
							} else if (transition == "other_change") {
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
