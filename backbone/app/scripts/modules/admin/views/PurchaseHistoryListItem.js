define([
	'app',
	'../Templates',
	'./PurchaseHistoryListItem',
	"entities/models/PurchaseHistoryAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.PurchaseHistoryListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates. purchaseHistoryListItem,
			tagName: "tr",
			ui: {
				"deleteBtn": ".delete",
			},
			onRender: function() {
			},
			events: {
				"click @ui.deleteBtn": function(e) {

					e.preventDefault();
					var model = new App.Entities.Models.AdminPurchaseHistoryListCondition();
					var that = this;
					var errors = model.validate();
					if (errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}
					model.url = App.api.PH0010;

					//trに注文番号のクラスをつける
					this.ui.deleteBtn.parents("tr").addClass(this.ui.deleteBtn.attr('id'));

					if(window.confirm('この注文をキャンセルしますか？')){
						var line_no = this.ui.deleteBtn.attr('id');
						var cond = {
							"cond": model.getReq(),
							"del": 'del',
							"line_no": line_no
						};
						//console.log(cond);
						model.fetchMx({
							data: cond,
							success: function (res) {
								var errors = res.get('errors');
								if (errors) {
									that.triggerMethod('showAlerts', errors);
									return;
								}
								//that.collection.unshift(model);

								//that.reset();
								//trに注文番号のクラスをつける
								//console.log(line_no);
								$("." + line_no).remove();
								return;

							}

						});
					}
					else{
						//window.alert('キャンセルされました'); // 警告ダイアログを表示
						return;
					}


				}
			},
			templateHelpers: {
				//ステータス
				statusText: function(){
					var data = this.order_status;
					var retunr_str = '';
					if (data == 1) {
						retunr_str = retunr_str + "未出荷";
					} else if (data == 2) {
						retunr_str = retunr_str + "出荷済";
					} else if (data == 9) {
						retunr_str = retunr_str + "キャンセル";
					}
					var data2 = this.receipt_status;
						if (data2 == 1) {
							retunr_str = retunr_str + " 未受領";
						} else if (data2 == 2) {
							retunr_str = retunr_str + " 受領済";
						}
					return retunr_str;

				},
				//よろず発注区分
				kubunText: function(){
					var data = this.order_sts_kbn;
					if (data == 1) {
						return "貸与";
					} else if (data == 3) {
						return "サイズ交換";
					} else if (data == 4) {
						return "消耗交換";
					} else if (data == 5) {
						return "異動";
					}
					//throw "invalid Data";
					return 'invalid';

				},
			}

		});
	});
});