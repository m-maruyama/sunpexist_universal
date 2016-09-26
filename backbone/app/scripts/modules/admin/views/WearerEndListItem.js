define([
	'app',
	'../Templates',
	'./WearerEndListItem',
	"entities/models/WearerEndAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerEndListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.wearerEndListItem,
			tagName: "tr",
			ui: {
				"wearer_end": "#wearer_end"
			},
			onRender: function() {
			},
			events: {
				'click @ui.wearer_end': function(e){
					e.preventDefault();
					var data = this.ui.wearer_end.val();
					var modelForUpdate = this.model;
					modelForUpdate.url = App.api.WN0020;
					var cond = {
						"scr": '貸与終了ボタン',
						"cond": data,
					};
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
							location.href = './wearer_end_order.html';
							return;
						}
					});
					// postForm('/universal/wearer_end_order.html', data);
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