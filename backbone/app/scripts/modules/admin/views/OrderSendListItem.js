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

			},
			onShow:   function() {


			},
			events: {
				'click @ui.orderSend_cancel': function(e){
					e.preventDefault();
					// 「OK」時の処理開始 ＋ 確認ダイアログの表示
					if(window.confirm('発注送信キャンセルを実行しますか？')) {
					var osc_vals = this.ui.orderSend_cancel.val();
					var osc_val = osc_vals.split(':');
						console.log(osc_val);
						var data = {
							"corporate_id" : osc_val[0],//企業id
							"werer_cd" : osc_val[1],//着用者コード
							"rntl_cont_no" : osc_val[2],//レンタル企業no
							"job_type_cd" : osc_val[3],//部門コード
							"order_req_no" : osc_val[4],//オーダーリクエストno
						};


						var that = this;
						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.OS0011;
						var cond = {
							"scr": '発注送信キャンセル',
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var errors = res.get('errors');
								if(errors) {
									var errorMessages = errors.map(function(v){
										return v.error_message;
									});
									this.triggerMethod('showAlerts', errorMessages);
								}
								sessionStorage.clear();
								that.triggerMethod('reload2');
							}
						});

					} else{
						console.log('no');
						return;
					}
					// 「キャンセル」時の処理終了
				},
				'click @ui.order_cancel': function(e){
					e.preventDefault();
					// 「OK」時の処理開始 ＋ 確認ダイアログの表示
					if(window.confirm('発注取り消しを実行しますか？')) {
						var ocb_vals = this.ui.order_cancel.val();
						var ocb_val = ocb_vals.split(':');
						console.log(ocb_val);
						//["000063", "RJ186952", "RB000131", "RB000131", "2"]
						var data = {
							"werer_cd" : ocb_val[0],//着用者コード
							"order_req_no" : ocb_val[1],//注文番号
							"rntl_cont_no" : ocb_val[2],//レンタル契約no
							"rntl_sect_cd" : ocb_val[3],//レンタル部門コード
							"job_type_cd" : ocb_val[4],//部門コード
						};

						var that = this;
						var modelForUpdate = this.model;


						modelForUpdate.url = App.api.WC0020;
						var cond = {
							"scr": '職種変更または異動-発注取消-更新可否チェック',
							"log_type": '3',
							"data": data,
						};





						modelForUpdate.fetchMx({
							data:cond,
							success:function(res){
								var errors = res.get('errors');
								if(errors) {
									var errorMessages = errors.map(function(v){
										return v.error_message;
									});
									this.triggerMethod('showAlerts', errorMessages);
								}
								sessionStorage.clear();
								that.triggerMethod('reload2');
							}
						});

					} else{
						console.log('no');
						return;
					}
					// 「キャンセル」時の処理終了
				},

			},
		});
	});
});
