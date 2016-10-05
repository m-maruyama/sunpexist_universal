define([
	'app',
	'handlebars',
	'../Templates',
	'./ManpowerInfoListItem',
	"entities/models/ManpowerInfoAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ManpowerInfoListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.manpowerInfoListItem,
			tagName: "tr",
			ui: {
				"manpowerDetail": ".manpower_detail_btn",
				"manpowerDownload": ".manpower_detail_download_btn"
			},
			onRender: function() {
			},
			events: {
				'click @ui.manpowerDetail': function(e){
					e.preventDefault();
					var target_ids = e.target.id;
					var target_id = target_ids.split(':');
					var agreement_no = target_id[0];
					var rntl_sect_cd = target_id[1];
					var rntl_sect_name = target_id[2];
					var yyyymm = target_id[3];
					var staff_total = target_id[4];
					this.triggerMethod('click:Detail', agreement_no, rntl_sect_cd, rntl_sect_name, yyyymm, staff_total);
				},
				'click @ui.manpowerDownload': function(e){
					e.preventDefault();
					var target_ids = e.target.id;
					var target_id = target_ids.split(':');

					var cond_map = new Object();
					cond_map["agreement_no"] = target_id[0];
					cond_map["rntl_sect_cd"] = target_id[1];
					cond_map["rntl_sect_name"] = target_id[2];
					cond_map["yyyymm"] = target_id[3];
					cond_map["staff_total"] = target_id[4];

					var msg = "データ量により、ダウンロード処理に時間がかかる可能性があります。ダウンロードを実施してよろしいですか？";
					if (window.confirm(msg)) {
						var cond = {
							"scr": '請求書データ詳細ダウンロード',
							"cond": cond_map
						};
						var form = $('<form action="' + App.api.MI0030 + '" method="post"></form>');
						var data = $('<input type="hidden" name="data" />');
						data.val(JSON.stringify(cond));
						form.append(data);
						$('body').append(form);
						form.submit();
						data.remove();
						form.remove();
						form=null;
						return;
					}
				}
			}
		});
	});
});
