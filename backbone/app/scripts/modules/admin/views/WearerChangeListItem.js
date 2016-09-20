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
				"wearer_end": "#wearer_end"
			},
			onRender: function() {
			},
			events: {
				'click @ui.wearer_end': function(e){
					e.preventDefault();
					var we_val = this.ui.wearer_end.val();
					var data = {'id': we_val};
					postForm('/universal/wearer_end_order.html', data);
					// this.triggerMethod('click:wearer_end', this.model);
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
