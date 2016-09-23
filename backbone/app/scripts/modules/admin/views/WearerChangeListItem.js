define([
	'app',
	'../Templates',
	'./WearerChangeListItem',
	"entities/models/WearerChangeAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerChangeListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.wearerChangeListItem,
			tagName: "tr",
			ui: {
				"wearer_change": "#wearer_change"
			},
			onRender: function() {
			},
			events: {
				'click @ui.wearer_change': function(e){
					e.preventDefault();
					var we_vals = this.ui.wearer_change.val();
					var we_val = we_vals.split(':');
					var data = {
						'rntl_cont_no': we_val[0],
						'rntl_sect_cd': we_val[1],
						'werer_cd': we_val[2]
					};
					//console.log(data);
					postForm('/universal/wearer_change_order.html', data);
				}
			},
		});

		var postForm = function(url, data) {
			var $form = $('<form/>', {'action': url, 'method': 'post'});
			for(var key in data) {
				$form.append($('<input/>', {'type': 'hidden', 'name': key, 'value': data[key]}));
			}
			$form.appendTo(document.body);
			$form.submit();
		};
	});
});
