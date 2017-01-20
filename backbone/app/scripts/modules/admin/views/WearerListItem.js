define([
	'app',
	'handlebars',
	'../Templates',
	'./WearerListItem',
	"entities/models/WearerAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.wearerListItem,
			tagName: "tr",
			ui: {
				"wearerDetail": ".wearer_detail_btn"
			},
			onRender: function() {
			},
			events: {
				'click @ui.wearerDetail': function(e){
					e.preventDefault();
					var target_ids = e.target.id;
					var target_id = target_ids.split(':');
					var agreement_no = target_id[0];
					var wearer_cd = target_id[1];
					var cster_emply_cd = target_id[2];
					var rntl_sect_cd = target_id[3];
					var job_type_cd = target_id[4];
					var werer_sts_kbn = target_id[5];
					this.triggerMethod('click:a', agreement_no, wearer_cd, cster_emply_cd, rntl_sect_cd, job_type_cd, werer_sts_kbn);
				}
			}
		});
	});
});
