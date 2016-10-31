define([
	'app',
	'../Templates',
	'../behaviors/Alerts',
	'bootstrap',
	'bootstrap-datetimepicker'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InfoAddModal = Marionette.ItemView.extend({
			template: App.Admin.Templates.infoAddModal,
			model: new Backbone.Model(),
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'modal': '#info_add_modal',
				'close': '.close',
				'corporate': '#corporate',
				'display_order': '#display_order',
				'open_date': '#open_date',
				'close_date': '#close_date',
				'message': '#message',
				'cancel': '.cancel',
				'add': '.add',
				'datetimepicker': '.datetimepicker'
			},
			bindings: {
			},
			onRender: function() {
				var that = this;

				// 入力項目
				var data = {
					"mode": "input"
				};
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.IN0020;
				var cond = {
					"scr": 'お問い合わせ追加-入力表示',
					"log_type": "2",
					"data": data
				};
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res) {
						var res_list = res.attributes;

						// 企業名
						for (var i=0; i<res_list['corporate_list'].length; i++) {
							var option = document.createElement('option');
							var str = res_list['corporate_list'][i]['corporate_id'] + ' ' + res_list['corporate_list'][i]['corporate_name'];
							var text = document.createTextNode(str);
							option.setAttribute('value', res_list['corporate_list'][i]['corporate_id']);
							option.appendChild(text);
							document.getElementById('corporate').appendChild(option);
						}
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
			addShow: function() {
				var that = this;

			},
			events: {
				// モーダル☓ボタン
				"click @ui.close": function(e){
					var that = this;
					$(".errors").text('');
					this.ui.display_order.val('');
					this.ui.open_date.val('');
					this.ui.close_date.val('');
					this.ui.message.val('');
					this.ui.modal.modal('hide');
				},
				// キャンセルボタン
				"click @ui.cancel": function(e){
					var that = this;
					$(".errors").text('');
					this.ui.display_order.val('');
					this.ui.open_date.val('');
					this.ui.close_date.val('');
					this.ui.message.val('');
					this.ui.modal.modal('hide');
				},
				// 追加ボタン
				"click @ui.add": function(e){
					var that = this;

					// 入力値取得
					var data = {
						"mode": "update",
						"corporate": this.ui.corporate.val(),
						"display_order": this.ui.display_order.val(),
						"open_date": this.ui.open_date.val(),
						"close_date": this.ui.close_date.val(),
						"message": this.ui.message.val()
					};

					// 入力値チェック、登録処理
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.IN0020;
					var cond = {
						"scr": 'お問い合わせ追加-新規登録',
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
