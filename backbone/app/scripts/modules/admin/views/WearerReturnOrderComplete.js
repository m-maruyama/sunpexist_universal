define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'../behaviors/Alerts',
	'bootstrap',
	'typeahead',
	'bloodhound',
	'blockUI',
	'../controllers/WearerReturnOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerReturnOrderComplete = Marionette.LayoutView.extend({
			defaults: {
				data: "",
			},
			initialize: function(options) {
				this.options = options || {};
				this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerReturnOrderComplete,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'continueInput': '.continueInput',
				'backHome': '.backHome',
				'returnSlipDownload': '.returnSlipDownload',
			},
			bindings: {
			},


			onShow: function() {
				this.triggerMethod('hideAlerts');
				if (window.sessionStorage.getItem("referrer")=='wearer_return_order_complete') {
					location.href = './wearer_other.html';
				}else{
					window.sessionStorage.setItem("referrer","wearer_return_order_complete");
				}

				var that = this;
				var data = this.options.data;
				var scr = data["scr"];
				var mode = data["mode"];
				var wearer_data = data["wearer_data"];
				var item = data["item"];
				var check = {
					"rntl_cont_no": data["wearer_data"]["agreement_no"],
					"werer_cd": data["wearer_data"]["werer_cd"]
				};

				// 発注入力遷移前に発注NGパターンチェック実施
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WR0013;
				var cond = {
					"scr": 'その他貸与/返却(不要品返却)-発注NGパターンチェック',
					"log_type": '3',
					"data": check
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res) {
						var res_val = res.attributes;
						if (res_val["err_cd"] == "0") {
							var modelForUpdate = that.model;
							modelForUpdate.url = App.api.WR0022;
							var cond = {
								"scr": scr,
								"mode": mode,
								"wearer_data": wearer_data,
								"item": item
							};
							modelForUpdate.fetchMx({
								data:cond,
								success:function(res){
									var res_val = res.attributes;
									if (res_val["error_code"] == "1") {
										$("#h").text('');
										$(".explanation").text('');
										that.triggerMethod('showAlerts', res_val["error_msg"]);
									} else {
										$('.returnSlipDownload').css('display', '');
										$('#return_slip_dl').val(res_val["param"]);
									}
								}
							});
						} else {
							//職種変更または異動、貸与終了があった時のエラー出力
							$("#h").text('');
							$(".explanation").text('');
							that.triggerMethod('showAlerts', res_val.err_msg);
							$(".list-group").append('<li class="list-group-item list-group-item-danger"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span><span class="text"></span></li>');
							$(".text").text(res_val.err_msg);
							return true;
						}
					}
				});
			},
			events: {
				'click @ui.continueInput': function(){
					var cond = window.sessionStorage.getItem("wearer_other_cond");
					window.sessionStorage.setItem("back_wearer_other_cond", cond);
					location.href="wearer_other.html";
				},
				'click @ui.backHome': function(){
					location.href="home.html";
				},
				'click @ui.returnSlipDownload': function (e) {
					e.preventDefault();
					var pdf_vals = e.target.value;

					var pdf_val = pdf_vals.split(':');
					var printData = new Object();
					printData["rntl_cont_no"] = pdf_val[0];
					printData["order_req_no"] = pdf_val[1];

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
						form = null;
						$('#myModal').modal('hide'); //追加
					});
				}
			}
		});
	});
});
