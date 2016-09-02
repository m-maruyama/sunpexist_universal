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
			template: App.Admin.Templates.receiveButton,
			regions: {
			},
			ui: {
				'receive_button': '#receive_button',
			},
			bindings: {
			},
			onShow: function() {
			},
			onRender: function() {
			},
			events: {
				'click @ui.receive_button': function(e){
					var msg = "受領ステータスを更新してよろしいですか？";
					if (window.confirm(msg)) {
						var that = this;
						var receive_chk_box = 'receive_check[]';
						var receive_chk_arr = new Array();

						// 個体管理番号毎のチェック(受領済み)、未チェック(未受領)
						for (var i=0; i<document.receive_list.elements[receive_chk_box].length; i++ ){
						    if(document.receive_list.elements[receive_chk_box][i].checked == false){
						        receive_chk_arr.push(document.receive_list.elements[receive_chk_box][i].value + ',1');
						    }
								if(document.receive_list.elements[receive_chk_box][i].checked == true){
						        receive_chk_arr.push(document.receive_list.elements[receive_chk_box][i].value + ',2');
						    }
						}

						var cond = {
							"scr": '受領更新',
							"page":this.options.pagerModel.getPageRequest(),
							"cond": receive_chk_arr
						};
						var modelForUpdate = new Backbone.Model();
						modelForUpdate.url = App.api.RE0020;
						modelForUpdate.fetchMx({
							data: cond,
							success:function(model){
								var errors = model.get('errors');
								if(errors) {
									alert(errors);
								}else{
									var page = model.get('page');
									that.triggerMethod('research',that.model.get('sort_key'),that.model.get('order'),page['page_number']);
								}
							}
						});
					}
				}
			},
		});
	});
});
