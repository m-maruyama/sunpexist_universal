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
					this.triggerMethod('click:a', agreement_no, wearer_cd, cster_emply_cd);
				}
			}
		});
	});
});
