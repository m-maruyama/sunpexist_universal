define([
	'app',
	'handlebars',
	'../Templates',
	'./PurchaseInputListItem',
	"entities/models/PurchaseAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.PurchaseInputListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.purchaseInputListItem,
			tagName: "tr",//trを生成
			ui: {
				"td_piece_rate": ".td_piece_rate",
				"quantity": ".quantity",
			},
			bindings: {
				'.td_piece_rate': 'td_piece_rate',
				'.quantity': 'quantity',
			},

			onRender: function() {

			},
			onShow:   function() {
				$("#total_price").text('0');
				var total_records = $("#total_records").val();//商品数を代入
				var i = total_records;//商品数
				//各trに商品契約idを付加
				for (i = 1; i <= total_records; i = i + 1){
					$("#rntl_cont_no_val" + i).closest("tr").addClass($("#rntl_cont_no_val" + i).val());
				}



				//タイマー処理
				var huga = 0;
				var hoge = setInterval(function() {
					huga++;
					//console.log(huga);

					//終了条件
					if (huga == 20) {
						clearInterval(hoge);

						var agreement_no = $("#agreement_no").val();
						//$(".table tbody tr").css('display' , 'none');
						//$(".table tbody ."+agreement_no).css('display' , 'table-row');

						$('#testTable tbody').each(function () {
								var pre_element = null;
								var col_num = 0;
								$(this).find('tr').each(function () {
									var now_th = $(this).find('th').eq( col_num );
									if (pre_element == null) {
										pre_element = now_th;
									} else if (now_th.text() == pre_element.text()) {
										now_th.remove();
										if (pre_element.attr('rowspan') == null) pre_element.attr('rowspan', 1);
										pre_element.attr('rowspan', parseInt(pre_element.attr('rowspan'),10) + 1);
									} else {
										pre_element = now_th;
									}
								});
							});
					}

				}, 0);
			},



			events: {
				'change @ui.quantity': function(){
					var total_records = $("#total_records").val();//商品数を代入
					var quantity, piece_rate;//数量と単価
					var i = total_records;//商品数
					var total_array = new Array();//それぞれの金額を入れる配列を用意

					for (i = 1; i <= total_records; i = i + 1){
						quantity = $(".quantity" + i).val();
						piece_rate = $(".td_piece_rate" + i).text();
						total_array.push(quantity * piece_rate);
					}
                    //console.log(total_array);//それぞれの金額を配列に入れる

					function sumElements( $previousValue, $currentValue) {
						return $previousValue + $currentValue;
					}
					var total_sum = total_array.reduce( sumElements );

					$("#total_price").text(total_sum);
				},





			},
			templateHelpers: {
				// アカウントロック

				//編集、削除の可否
				//editDel: function(){
				//	var type = this.user_type;
				//	if (type == 1) {
				//		return "一般";
				//	} else if (type == 2) {
				//		return "管理者";
				//	} else if (type == 3) {
				//		return '';
				//	}
				//	return;
				//},
			},
		});

	});

});
