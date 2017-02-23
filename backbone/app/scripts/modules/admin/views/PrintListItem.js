define([
	'app',
	'../Templates',
	'./PrintListItem',
	"entities/models/PrintAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.PrintListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.printListItem,
			tagName: "tr",
			ui: {
				"detailLink": "a.detail",
				"print_download": ".print_download"
			},
			onRender: function() {
			},
			events: {
				'click @ui.detailLink': function (e) {
					e.preventDefault();
					//this.triggerMethod('click:a', this.model);
				},
				'click @ui.print_download': function(e){
					e.preventDefault();
					//var printData = [];
					var pdf_vals = e.target.id;
					var pdf_val = pdf_vals.split(':');
					//console.log(pdf_val);
					var printData = new Object();
					printData["order_req_no"] = pdf_val[0];
					printData["rntl_cont_no"] = pdf_val[1];

					var individual_number_block = $(".individual_number").css('display');

					if(individual_number_block == 'block'){
						printData["individual_number"] = '1';
					}
					if(individual_number_block !== 'block'){
						printData["individual_number"] = '0';
					}

					//var msg = "データ量により、ダウンロード処理に時間がかかる可能性があります。ダウンロードを実施してよろしいですか？";
					$("#btn_ok").off();
					$('#myModal').modal();
					document.getElementById("confirm_txt").innerHTML=App.dl_msg;
					$("#btn_ok").on('click',function() {
						$('#myModal').modal('hide');
						// if (window.confirm(msg)) {
						var cond = {
							"scr": 'PDFダウンロード',
							"cond": printData
						};
						var form = $('<form action="' + App.api.PR0011 + '" method="post"></form>');
						var data = $('<input type="hidden" name="data" />');
						data.val(JSON.stringify(cond));
						form.append(data);
						$('body').append(form);
						form.submit();
						data.remove();
						form.remove();
						form=null;
						return;
					});
				}

				/*"click @ui.print_download": function () {
					console.log('click');
					var that = this;
					$.ajax({
						url: App.api.PR0011,
						type: 'POST',
					}).done(function (data) {
						alert('success!!');
					}).fail(function(data){
						alert('error!!');
					});

				}*/
			},
			templateHelpers: {
				//ステータス
				statusText: function(){
					var data = this.status;
					if (data == 1) {
						return "未返却";
					} else if (data == 2) {
						return "返却済";
					}
					return '-';

				},
				//よろず発注区分
				kubunText: function(){
					var data = this.kubun;
					if (data == 2) {
						return "返却";
					} else if (data == 3) {
						return "サイズ交換";
					} else if (data == 4) {
						return "消耗交換";
					} else if (data == 5) {
						return "異動";
					}
					return '-';

				},
			}

		});
	});
});
