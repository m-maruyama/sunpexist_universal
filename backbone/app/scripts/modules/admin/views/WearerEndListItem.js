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
				"wearer_end": "#wearer_end",
				"werer_name": "#werer_name",
				"download": "#download"
			},
			onRender: function() {
			},
			events: {
				'click @ui.wearer_end': function(e){
					e.preventDefault();
					var that = this;
					var we_vals = this.ui.wearer_end.val();
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
						'werer_name_kana': we_val[13],
						'werer_name': this.ui.werer_name.val(),
					};

					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WN0016;
					var cond = {
						"scr": '貸与終了-発注NGパターンチェック',
						"log_type": '3',
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var res_val = res.attributes;
							if (res_val["err_cd"] == "0") {
								var type = "WN0016_req";
								var transition = "";
								var data = cond["data"];
								that.onShow(res_val, type, transition, data);

							} else {
								// JavaScript モーダルで表示
								$('#myModalAlert').modal('show'); //追加
								//メッセージの修正
								document.getElementById("alert_txt").innerHTML=res_val["err_msg"];
							}
						}
					});
				},
				'click @ui.download': function(e){
					e.preventDefault();
					//var printData = [];
					var pdf_vals = e.target.value;

					var pdf_val = pdf_vals.split(':');
					//console.log(pdf_val);
					var printData = new Object();
					printData["rntl_cont_no"] = pdf_val[0];
					printData["order_req_no"] = pdf_val[1];
					//console.log(printData);
					// JavaScript モーダルで表示
					$('#myModal').modal(); //追加
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
                        $('#myModal').modal('hide');
					});
				}
			},
			onShow: function(val, type, transition, data) {
				var that = this;

				if (type == "WN0016_req") {
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WC0011;
					var cond = {
						"scr": '発注入力（貸与終了）POST値保持',
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
							window.sessionStorage.setItem("wearer_end_cond", arr_str);
							window.sessionStorage.setItem('referrer', 'wearer_end');

							var $form = $('<form/>', {'action': '/universal/wearer_end_order.html', 'method': 'post'});
							$form.appendTo(document.body);
							$form.submit();
						}
					});
				}

			}
		});
	});
});
