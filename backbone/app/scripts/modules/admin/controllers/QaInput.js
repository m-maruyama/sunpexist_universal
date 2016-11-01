define([
	'app',
	'./Abstract',
	'../views/QaInput',
	'../views/QaInputCondition',
	"entities/collections/AdminQaInputListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.QaInput = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('qaInput');
				var qaInputView = new App.Admin.Views.QaInput();
				var qaInputListListCollection = new App.Entities.Collections.AdminQaInputListList();
				var qaInputConditionView = new App.Admin.Views.QaInputCondition();

				App.main.show(qaInputView);
				qaInputView.condition.show(qaInputConditionView);
			}
		});
	});

	return App.Admin.Controllers.QaInput;
});
