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
	'../controllers/WearerChangeOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerChangeOrderSendComplete = Marionette.LayoutView.extend({
			defaults: {
				data: "",
			},
			initialize: function(options) {
				this.options = options || {};
				this.options = _.extend(this.defaults, this.options);
			},
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerChangeOrderSendComplete,
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
				if (window.sessionStorage.getItem("referrer")=='change_order_complete') {
					location.href = './wearer_change.html';
				}else{
					window.sessionStorage.setItem("referrer","change_order_complete");
				}
				var that = this;
				var data = this.options.data;
				var scr = data["scr"];
				var mode = data["mode"];
				var wearer_data = data["wearer_data"];
				var now_item = data["now_item"];
				var add_item = data["add_item"];
				var add_item_length = Object.keys(data["add_item"]).length;
				//メッセージ出しわけ

				if(data["wearer_data"].reason_kbn == '09'){
					if(add_item_length > 0){
						//職種変更のみ 発注あり
						$(".explanation2").css('display', 'none');
					}else{
						//職種変更のみ 発注なし
						$(".explanation1").css('display', 'none');
					}
				}
				if(data["wearer_data"].reason_kbn == '10'){
					//職種のみ異動
					$(".explanation1").css('display', 'none');
				}
				if(data["wearer_data"].reason_kbn == '11'){
					//職種変更及び拠点異動
					$(".explanation2").css('display', 'none');
				}

				// 入力内容登録処理
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0022;
				var cond = {
					"scr": scr,
					"mode": mode,
					"wearer_data": wearer_data,
					"now_item": now_item,
					"add_item": add_item,
				};
				//console.log(cond);

				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var res_val = res.attributes;
						if (res_val["error_code"] == "0") {
							//「返却伝票ダウンロード」ボタン表示
							if(res_val["return_item_num"] > 0) {
								$('.returnSlipDownload').css('display', '');
							}
							$('#return_slip_dl').val(res_val["param"]);
						} else {
							// 登録処理にエラーがある場合
							$("#h").text('');
							$(".explanation1").text('');
							$(".explanation2").text('');
							that.triggerMethod('showAlerts', res_val["error_msg"]);
						}
					}
				});
			},
			events: {
				// 「続けて入力する」ボタン
				'click @ui.continueInput': function(){
					// 検索画面の条件項目を取得
					var cond = window.sessionStorage.getItem("wearer_change_cond");
					window.sessionStorage.setItem("back_wearer_change_cond", cond);
					// 検索画面へ遷移
					location.href="wearer_change.html";
				},
				// 「ホーム画面へ戻る」ボタン
				'click @ui.backHome': function(){
					// ホーム画面へ遷移
					location.href="home.html";
				},
				// 「返却伝票ダウンロード」ボタン
				'click @ui.returnSlipDownload': function (e) {
					e.preventDefault();
					var pdf_vals = e.target.value;

					var pdf_val = pdf_vals.split(':');
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
						$('#myModal').modal('hide');
					});
				},
			}
		});
	});
});
