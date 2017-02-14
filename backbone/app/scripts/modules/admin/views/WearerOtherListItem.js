define([
	'app',
	'../Templates',
	'./WearerOtherListItem',
	"entities/models/WearerOtherAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerOtherListItem = Marionette.ItemView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerOtherListItem,
			tagName: "tr",
			ui: {
				"wearer_add": "#wearer_add",
				"wearer_return": "#wearer_return",
				"download": "#download"
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
						"scr": 'その他貸与/返却(追加貸与)-発注NGパターンチェック',
						"log_type": '3',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["err_cd"] == "0") {
								var type = "WR0011_req";
								var transition = "add";
								var data = cond["data"];
								that.onShow(res_val, type, transition, data);
							} else {
								// JavaScript モーダルで表示
								$('#myModal_alert').modal('show');
								document.getElementById("alert_txt").innerHTML=res_val["err_msg"];
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
						"scr": 'その他貸与/返却(不要品返却)-発注NGパターンチェック',
						"log_type": '3',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["err_cd"] == "0") {
								var type = "WR0011_req";
								var transition = "return";
								var data = cond["data"];
								that.onShow(res_val, type, transition, data);
							} else {
								// JavaScript モーダルで表示
								$('#myModal_alert').modal('show');
								document.getElementById("alert_txt").innerHTML=res_val["err_msg"];
								// NGエラーアラート表示
								//alert(res_val["err_msg"]);
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
					printData["order_req_no"] = pdf_val[1];

					$('#myModal').modal();
					document.getElementById("confirm_txt").innerHTML=App.dl_msg;
					$("#btn_ok").off();
					$("#btn_ok").on('click',function() {
					//var msg = "データ量により、ダウンロード処理に時間がかかる可能性があります。ダウンロードを実施してよろしいですか？";
					//if (window.confirm(msg)) {
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
						$('#myModal').modal('hide'); //追加
					});
				}
			},
			onShow: function(val, type, transition, data) {
				var that = this;
				// 追加貸与発注入力遷移
				if (type == "WR0011_req") {
					// 遷移時のPOSTパラメータ代行処理
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WR0011;
					var cond = {
						"scr": '発注入力（追加貸与）POST値保持',
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
							window.sessionStorage.setItem("referrer","wearer_other");
							if (transition == "add") {
								// 追加貸与の発注入力画面へ遷移
								var $form = $('<form/>', {'action': '/universal/wearer_add_order.html', 'method': 'post'});
								$form.appendTo(document.body);
								$form.submit();
							} else if (transition == "return") {
								// 追加貸与の発注入力画面へ遷移
								var $form = $('<form/>', {'action': '/universal/wearer_return_order.html', 'method': 'post'});
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
