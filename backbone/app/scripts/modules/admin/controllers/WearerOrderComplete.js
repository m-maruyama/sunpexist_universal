define([
	'app',
	'./Abstract',
	'../views/WearerOrderComplete',
	'../views/WearerOrderCompleteCondition',
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerOrderComplete = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('wearerOrderComplete');
				var wearerOrderCompleteView = new App.Admin.Views.WearerOrderComplete();

				var wearerOrderCompleteConditionView = new App.Admin.Views.WearerOrderCompleteCondition();

				this.listenTo(wearerOrderCompleteConditionView, 'click:next_button', function(){
					// var errors = wearerOrderConditionView.insert_wearer(agreement_no);
					// if(errors){
					// 	wearerOrderView.triggerMethod('showAlerts', errors);
					// }
				});

				// this.listenTo(paginationView, 'selected', function(pageNumber){
				// 		fetchList_section(pageNumber);
				// });
				App.main.show(wearerOrderCompleteView);
				wearerOrderCompleteView.condition.show(wearerOrderCompleteConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerOrder;
});
