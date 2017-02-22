define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.CsvDownload = Marionette.ItemView.extend({
			template: App.Admin.Templates.csvDownload,
			model: new Backbone.Model(),
			ui: {
				'download_btn': '#download_btn',
			},
			events: {
				'click @ui.download_btn': function(e){
					e.preventDefault();

					var cond_map = new Object();
					cond_map["ui_type"] = $("input[name='ui_type']").val();
					cond_map["agreement_no"] = $("select[name='agreement_no']").val();
					cond_map["no"] = $("input[name='no']").val();
					cond_map["emply_order_no"] = $("input[name='emply_order_no']").val();
					cond_map["member_no"] = $("input[name='member_no']").val();
					cond_map["member_name"] = $("input[name='member_name']").val();
					cond_map["section"] = $("select[name='section']").val();
					cond_map["job_type"] = $("select[name='job_type']").val();
					cond_map["input_item"] = $("select[name='input_item']").val();
					cond_map["item_color"] = $("select[name='item_color']").val();
					cond_map["item_size"] = $("input[name='item_size']").val();
					cond_map["order_day_from"] = $("input[name='order_day_from']").val();
					cond_map["order_day_to"] = $("input[name='order_day_to']").val();
					cond_map["send_day_from"] = $("input[name='send_day_from']").val();
					cond_map["send_day_to"] = $("input[name='send_day_to']").val();
					cond_map["return_day_from"] = $("input[name='return_day_from']").val();
					cond_map["return_day_to"] = $("input[name='return_day_to']").val();
					cond_map["receipt_day_from"] = $("input[name='receipt_day_from']").val();
					cond_map["receipt_day_to"] = $("input[name='receipt_day_to']").val();
					cond_map["maker_send_no"] = $("input[name='maker_send_no']").val();
					cond_map["status0"] = $("#status0").prop("checked");
					cond_map["status1"] = $("#status1").prop("checked");
					cond_map["order_kbn0"] = $("#order_kbn0").prop("checked");
					cond_map["order_kbn1"] = $("#order_kbn1").prop("checked");
					cond_map["order_kbn2"] = $("#order_kbn2").prop("checked");
					cond_map["order_kbn3"] = $("#order_kbn3").prop("checked");
					cond_map["order_kbn4"] = $("#order_kbn4").prop("checked");
					cond_map["reason_kbn0"] = $("#reason_kbn0").prop("checked");
					cond_map["reason_kbn1"] = $("#reason_kbn1").prop("checked");
					cond_map["reason_kbn2"] = $("#reason_kbn2").prop("checked");
					cond_map["reason_kbn3"] = $("#reason_kbn3").prop("checked");
					cond_map["reason_kbn4"] = $("#reason_kbn4").prop("checked");
					cond_map["reason_kbn5"] = $("#reason_kbn5").prop("checked");
					cond_map["reason_kbn6"] = $("#reason_kbn6").prop("checked");
					cond_map["reason_kbn7"] = $("#reason_kbn7").prop("checked");
					cond_map["reason_kbn8"] = $("#reason_kbn8").prop("checked");
					cond_map["reason_kbn9"] = $("#reason_kbn9").prop("checked");
					cond_map["reason_kbn10"] = $("#reason_kbn10").prop("checked");
					cond_map["reason_kbn11"] = $("#reason_kbn11").prop("checked");
					cond_map["reason_kbn12"] = $("#reason_kbn12").prop("checked");
					cond_map["reason_kbn13"] = $("#reason_kbn13").prop("checked");
					cond_map["reason_kbn14"] = $("#reason_kbn14").prop("checked");
					cond_map["reason_kbn15"] = $("#reason_kbn15").prop("checked");
					cond_map["reason_kbn16"] = $("#reason_kbn16").prop("checked");
					cond_map["reason_kbn17"] = $("#reason_kbn17").prop("checked");
					cond_map["reason_kbn18"] = $("#reason_kbn18").prop("checked");
					cond_map["reason_kbn19"] = $("#reason_kbn19").prop("checked");
					cond_map["individual_number"] = $("input[name='individual_number']").val();
					cond_map["job_type_zaiko"] = $("select[name='job_type_zaiko']").val();
					cond_map["item"] = $("select[name='item']").val();

					//ソート条件
					var page = new Object();
					page["sort_key"] = this.options.model.attributes.sort_key;
					page["order"] = this.options.model.attributes.order;


					// JavaScript モーダルで表示
					$('#DownloadModal').modal('show'); //追加
					//メッセージの修正
					document.getElementById("confirm_txt").innerHTML=App.dl_msg; //追加　このメッセージはapp.jsで定義
					$("#btn_ok").off();
					$("#btn_ok").on('click',function() { //追加
						var cond = {
							"scr": 'CSVダウンロード',
							"cond": cond_map,
							"page": page
						};
						var form = $('<form action="' + App.api.DL0010 + '" method="post"></form>');
						var data = $('<input type="hidden" name="data" />');
						data.val(JSON.stringify(cond));
						form.append(data);
						$('body').append(form);
						form.submit();
						data.remove();
						form.remove();
						form=null;
						$('#DownloadModal').modal('hide'); //追加
					});
				}
			},
/*
			fetch:function(cond_map){
				var modelForUpdate = this.model;
				var cond = {
					"scr": 'CSVダウンロード',
					"cond": cond_map
				};
				modelForUpdate.url = App.api.DL0010;
				modelForUpdate.fetchMx({
					data:cond,
					success:function(res){
						var errors = res.get('errors');
						if(errors) {
							var errorMessages = errors.map(function(v){
								return v.error_message;
							});
							that.triggerMethod('showAlerts', errorMessages);
						}
					}
				});
			}
*/
		});
	});
});
