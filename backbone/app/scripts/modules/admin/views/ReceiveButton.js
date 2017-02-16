define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'typeahead',
	'bloodhound'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ReceiveButton = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.receiveButton,
			regions: {
			},
			ui: {
				'receive_button': '#receive_button',
			},
			bindings: {
			},
			onRender: function() {
			},
			events: {
				'click @ui.receive_button': function(){
					var that = this;
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0130;
					var cond = {
						"scr": '更新可否チェック',
						"update_skip_flg": 'receive',//更新可否フラグをrecieve.phpでチェックしているためskip
					};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var type = "cm0130_res";
							var res_val = res.attributes;
							that.onShow(res_val, type);
						}
					});
				}
			},
			onShow: function(val, type) {
				var that = this;

				if (type == "cm0130_res") {
					if (!val["chk_flg"]) {
						alert(val["error_msg"]);
					} else {
						// JavaScript モーダルで表示
						$('#myModal').modal('show'); //追加
						//メッセージの修正
						document.getElementById("confirm_txt").innerHTML=App.receipt_msg; //追加　このメッセージはapp.jsで定義
						$("#btn_ok").off();
						$("#btn_ok").on('click',function() { //追加
							hideModal();
							var receive_chk_box = 'receive_check[]';
							var receive_chk_arr = new Array();

							$(".update_check").each(function () {
								if ($(this).prop("checked")) {
									receive_chk_arr.push($(this).val() + ',2');
								} else {
									receive_chk_arr.push($(this).val() + ',1');
								}
							});
/*
							for (var i=0; i<document.receive_list.elements[receive_chk_box].length; i++){
							    if(document.receive_list.elements[receive_chk_box][i].checked == false){
							        receive_chk_arr.push(document.receive_list.elements[receive_chk_box][i].value + ',1');
							    }
									if(document.receive_list.elements[receive_chk_box][i].checked == true){
							        receive_chk_arr.push(document.receive_list.elements[receive_chk_box][i].value + ',2');
							    }
							}
*/
							var cond = {
								"scr": '受領更新',
								"page":that.options.pagerModel.getPageRequest(),
								"cond": receive_chk_arr
							};
							var modelForUpdate = new Backbone.Model();
							modelForUpdate.url = App.api.RE0020;
							modelForUpdate.fetchMx({
								data: cond,
								success:function(res){
									var res_val = res.attributes;
									//console.log(res_val);
									if(res_val["error_code"] == "0") {
										var page = res_val["page"];
										that.triggerMethod('research',that.model.get('sort_key'),that.model.get('order'),page['page_number']);
									}else{
										// JavaScript モーダルで表示
										$('#myModalAlert').modal('show'); //追加
										//メッセージの修正
										document.getElementById("alert_txt").innerHTML=App.receipt_alt_msg;
									}
								}
							});
						});
					}
				}
			}
		});
	});
});
