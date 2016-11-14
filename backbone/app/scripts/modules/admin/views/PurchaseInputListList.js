define([
	'app',
	'../Templates',
	'blockUI',
	'./PurchaseInputListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var order = 'asc';
		var sort_key = 'order_req_no';
		Views.PurchaseInputListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.purchaseInputListList,
			emptyView: Backbone.Marionette.ItemView.extend({
    		tagName: "tr",
				template: App.Admin.Templates.lendEmpty,

            }),
			childView: Views.PurchaseInputListItem,
			childViewContainer: "tbody",
			ui: {
				'updBtn' : '.updBtn',
				'confBtn' : '.confBtn',
				'bckBtn' : '.bckBtn',
			},
			onRender: function() {
				this.listenTo(this.collection, 'parsed', function(res){
				});
				this.ui.updBtn.addClass('hidden');
				this.ui.bckBtn.addClass('hidden');




			},
			events: {
				"click @ui.confBtn": function(e) {
					e.preventDefault();
					if ($("#total_price").text() == 0){
						alert('合計金額が１円以下のため、注文ができません。');
						return;
					}
					$("select").attr("disabled", "disabled");
					this.ui.updBtn.removeClass('hidden');
					this.ui.bckBtn.removeClass('hidden');
					this.ui.confBtn.addClass('hidden');
					$("h1").text("注文入力確認");
				},
				"click @ui.bckBtn": function() {
					//e.preventDefault();
					$("select").removeAttr('disabled');
					this.ui.updBtn.addClass('hidden');
					this.ui.bckBtn.addClass('hidden');
					this.ui.confBtn.removeClass('hidden');
					$("h1").text("注文入力");
				},



				"click @ui.updBtn": function(e){
					e.preventDefault();
					if ($("#total_price").text() == 0){
						alert('合計金額が１円以下のため、注文ができません。');
						return;
					}
					var itemLength = $('.quantity:visible').length;//数量セレクトボックスの数
					//console.log(itemLength);
					var total_records = $("#total_records").val();
					var i;
					var item = new Object();

					for (i = 1; i <= total_records; i = i + 1){
						item[i] = new Object();
					item[i]['corporate_id'] = null;
					item[i]['rntl_cont_no'] = $("#agreement_no").val();
					item[i]['rntl_sect_cd'] = $("#section").val();
					item[i]['sale_order_date'] = null;
					item[i]['item_cd'] = $("#item_cd" + i).val();
					item[i]['color_cd'] = $(".color_cd" + i).text();
					item[i]['size_cd'] = $(".size_cd" + i).text();
					item[i]['item_name'] = $("#item_name" + i).val();
					item[i]['piece_rate'] = $(".td_piece_rate" + i).text();
					item[i]['quantity'] = $(".quantity" + i).val();
					item[i]['total_amount'] = $("#total_price").text();
					item[i]['accnt_no'] = null;
					item[i]['snd_kbn'] = 0;
					item[i]['rgst_user_id'] = null;
					item[i]['upd_user_id'] = null;
					item[i]['upd_pg_id'] = null;
					}

					var item_count = Object.keys(item).length;//配列の数を数える

					var model = this.model;

					var that = this;
					var errors = model.validate();
					if (errors){
						this.triggerMethod('showAlerts', errors);
						return;
					}

					model.url = App.api.PU0010;
					var cond = {
						"cond": model.getReq(),
						"item": item,
						"total_record": total_records
					};

					model.fetchMx({
						data:cond,
						success:function(res){
							var errors = res.get('errors');

							if(errors) {
								that.triggerMethod('showAlerts', errors);
								alert('注文登録が失敗しました。');
								return;
							}
							//that.collection.unshift(model);

							var lastval = res.get('seq');
							lastval = lastval.join(['-']);

							alert('注文登録が完了しました。');
							$(".form-horizontal").hide();
							$(".listTable").hide();
							$("h1").text("注文入力完了");
							$("h1").after("<p class='text-complete'>注文を受け付けました。</p>");
							$(".text-complete").after("<h2 class='number-history'>注文番号</h2>");
							$(".number-history").after("<p class='order-number'></p>");
							$(".order-number").text(lastval);


							return;
							//that.reset();
							//that.triggerMethod('reload');
						}
					});

				}//upd

			},

			reset: function(){

			},



			_sync : function(){


			},
			onShow:   function() {

			},


			fetch:function(purchaseInputListConditionModel){
				var cond = {
					"scr": '商品注文入力',
					"cond": purchaseInputListConditionModel.getReq()
				};
				var that = this;
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });

				this.collection.fetchMx({
					data: cond,
					success: function(model,res,req){
						$.unblockUI();

					},
					complete:function(res){
						$.unblockUI();

						//数量セレクトに数量追加
						var setSelectQuantity = function()
						{
							var select = $('.quantity');
							var i = 0;
							for (i = 0; i <= 99; i = i + 1){
								var option = document.createElement('option');
								option.setAttribute('value', i);
								option.innerHTML = i;
								$('.quantity').append(option);
							}
							$('.quantity').value = 0;
						}
						setSelectQuantity();//注文入力セレクトoption生成

					},
				});
			}

		});

	});
});
