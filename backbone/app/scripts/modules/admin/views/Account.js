define([
	'app',
	'../Templates',
	'../behaviors/Alerts'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Account = Marionette.LayoutView.extend({
			template: App.Admin.Templates.account,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'modal': '#myModal',
				'addBtn': '.add',
				'message': '#message',
				'updateBtn': 'button.update'
			},
			regions: {
				"page": ".page",
				"page_2": ".page_2",
				"condition": ".condition",
				"accountModal": '.account_modal',
				"listTable": '.listTable'
			},
			bindings: {
			},
			onRender: function() {
				var that = this;
			},
			events: {
				'click @ui.addBtn': function(e){
					e.preventDefault();
				},
				// 'click @ui.updateBtn': function(e){
					// e.preventDefault();
					// var that = this;
					// this.triggerMethod('hideAlerts');
					// var m;
					// var reqData = {
						// add:[],
						// delete:[]
					// };
					// for(var i=0;i<this.collection.length;i++){
						// m = this.collection.models[i];
						// if (m.get('deleteFlag')) {
							// if (m.get('account_id')) {
								// reqData.delete.push(m.get('account_id'));
							// }
						// } else {
							// if (!m.get('account_id')) {
								// reqData.add.push(m.getReq());
							// }
						// }
					// }
					// if(reqData.delete.length === 0 && reqData.add.length === 0) {
						// that.triggerMethod('updated');
						// return;
					// }
					// var modelForUpdate = new Backbone.Model();
					// modelForUpdate.url = App.api.AD0100;
					// modelForUpdate.fetchMx({
						// data: reqData,
						// success:function(model){
							// var errors = model.get('errors');
							// if(errors.length === 0) {
								// that.triggerMethod('updated');
							// } else {
								// var errorMessages = errors.map(function(v){
									// return v.error_message;
								// });
								// that.triggerMethod('showAlerts', errorMessages);
							// }
						// }
					// });
				// }
			},
		});
	});

});
