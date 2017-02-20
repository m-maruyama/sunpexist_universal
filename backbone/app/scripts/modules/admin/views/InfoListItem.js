define([
	'app',
	'../Templates',
	'./InfoListItem',
	"entities/models/InfoAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InfoListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.infoListItem,
			tagName: "tr",
			ui: {
				"editBtn": ".edit",
				"deleteBtn": ".delete"
			},
			onRender: function() {
			},
			events: {
				'click @ui.editBtn': function(e){
					e.preventDefault();
					var that = this;
					var id = e.target.id;
					console.log(id);
					this.triggerMethod('click:editBtn', id);
				},
				'click @ui.deleteBtn': function(e){
					e.preventDefault();
					var that = this;
					var id = e.target.id;

					var msg = "ID:" + id + "のお知らせを削除しますが、よろしいですか？";
					if (window.confirm(msg)) {
						var data = {
							"info_id": id
						};
						// 削除処理
						var modelForUpdate = this.model;
						modelForUpdate.url = App.api.IN0040;
						var cond = {
							"scr": 'お問い合わせ削除',
							"log_type": "2",
							"data": data
						};
						modelForUpdate.fetchMx({
							data:cond,
							success:function(res) {
								var res_list = res.attributes;
								//console.log(res_list);
								if (res_list["error_code"] == "0") {
									that.triggerMethod('complete');
								} else {
									// 異常終了の場合、アラート表示
									alert(res_list["error_msg"]);
								}
							}
						});
					}
				},
			},
			templateHelpers: {
			}

		});
	});
});
