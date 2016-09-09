define([
	'app',
	'./Abstract',
	'../views/WearerInputComplete',
	'../views/WearerInputCompleteCondition',
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerInput = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('wearerInputComplete');
				var wearerInputCompleteView = new App.Admin.Views.WearerInputComplete();

				var wearerInputCompleteConditionView = new App.Admin.Views.WearerInputCompleteCondition();

				this.listenTo(wearerInputCompleteConditionView, 'click:next_button', function(){
					// var errors = wearerInputConditionView.insert_wearer(agreement_no);
					// if(errors){
					// 	wearerInputView.triggerMethod('showAlerts', errors);
					// }
				});

				// this.listenTo(paginationView, 'selected', function(pageNumber){
				// 		fetchList_section(pageNumber);
				// });
				App.main.show(wearerInputCompleteView);
				wearerInputCompleteView.condition.show(wearerInputCompleteConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerInput;
});
