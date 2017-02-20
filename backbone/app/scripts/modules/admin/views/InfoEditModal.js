define([
	'app',
	'../Templates',
	'../behaviors/Alerts',
	'bootstrap',
	'bootstrap-datetimepicker'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InfoEditModal = Marionette.ItemView.extend({
			defaults: {
				id: ''
			},
			initialize: function(options) {
					this.options = options || {};
					this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.infoEditModal,
			model: new Backbone.Model(),
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'modal': '#info_edit_modal',
				'close': '.close',
				'info_id': '#info_id',
				'corporate': '#corporate',
				'display_order': '#display_order',
				'open_date': '#open_date',
				'close_date': '#close_date',
				'message': '#message',
				'cancel': '.cancel',
				'update': '.update',
				'datetimepicker': '.datetimepicker'
			},
			bindings: {
			},
			onRender: function() {
				var that = this;
				var id = this.options.id;

				// 入力項目
				var data = {
					"mode": "input",
					"id": id
				};
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.IN0030;
				var cond = {
					"scr": 'お問い合わせ更新-編集表示',
					"log_type": "2",
					"data": data
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res) {
						var res_list = res.attributes;
						// 異常があった場合
						if (res_list["error_code"] !== "0") {
							// JavaScript モーダルで表示
							$('#myModalAlert').modal(); //追加
							//メッセージの修正
							document.getElementById("alert_txt").innerHTML=App.info_err_msg;
							location.href = "info.html";
						}
						// ID
						that.ui.info_id.val(res_list["info_id"]);
						// 企業名
						that.ui.corporate.val(res_list["corporate"]);
						// 表示順
						that.ui.display_order.val(res_list["display_order"]);
						// 公開開始日
						that.ui.open_date.val(res_list["open_date"]);
						// 公開終了日
						that.ui.close_date.val(res_list["close_date"]);
						// 表示メッセージ
						that.ui.message.val(res_list["message"]);
					}
				});
				// 公開日、終了日
				this.ui.datetimepicker.datetimepicker({
					format: 'YYYY/MM/DD HH:mm',
					//useCurrent: false,
					minDate: new Date(),
					locale: 'ja',
					sideBySide:true
				});
			},
			events: {
				// モーダル☓ボタン
				"click @ui.close": function(){
					var that = this;
					$(".errors").text('');
					this.ui.display_order.val('');
					this.ui.open_date.val('');
					this.ui.close_date.val('');
					this.ui.message.val('');
					this.ui.modal.modal('hide');
				},
				// キャンセルボタン
				"click @ui.cancel": function(){
					var that = this;
					$(".errors").text('');
					this.ui.display_order.val('');
					this.ui.open_date.val('');
					this.ui.close_date.val('');
					this.ui.message.val('');
					this.ui.modal.modal('hide');
				},
				// 更新ボタン
				"click @ui.update": function(){
					var that = this;
					// 入力値取得
					var data = {
						"mode": "update",
						"info_id": this.ui.info_id.val(),
						"corporate": this.ui.corporate.val(),
						"display_order": this.ui.display_order.val(),
						"open_date": this.ui.open_date.val(),
						"close_date": this.ui.close_date.val(),
						"message": this.ui.message.val()
					};
					// 入力値チェック、登録処理
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.IN0030;
					var cond = {
						"scr": 'お問い合わせ編集-更新',
						"log_type": "2",
						"data": data
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res) {
							var res_list = res.attributes;
							//console.log(res_list);
							if (res_list["error_code"] == "0") {
								$(".errors").text('');
								that.ui.display_order.val('');
								that.ui.open_date.val('');
								that.ui.close_date.val('');
								that.ui.message.val('');
								that.ui.modal.modal('hide');
								that.triggerMethod('complete');
							} else {
								// 異常終了の場合、エラーメッセージ表示
								that.triggerMethod('showAlerts', res_list["error_msg"]);
							}
						}
					});
				}
			}
		});
	});
});
