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
				'updateBtn': 'button.update_button',
			},
			bindings: {
			},
			onShow: function() {
			},
			onRender: function() {
			},
			events: {
				'click @ui.updateBtn': function(e){
					if(confirm('受領ステータスを更新しまします。')){
						e.preventDefault();
						var that = this;
						this.triggerMethod('hideAlerts');
						var m;
						var reqData = {
							on:[],
							off:[]
						};
						for(var i=0;i<this.collection.length;i++){
							m = this.collection.models[i];
							if (m.get('updateFlag')) {
								if (m.get('receipt_id')&&!m.get('disabled')) {
									reqData.on.push(m.get('receipt_id'));
								}
							}else{
								if (m.get('receipt_id')) {
									reqData.off.push(m.get('receipt_id'));
								}
							}
						}
						var cond = {
							"scr": '受領更新',
							"page":this.options.pagerModel.getPageRequest(),
							"cond": reqData
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