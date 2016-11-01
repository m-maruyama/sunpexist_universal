define([
	'app',
	'./Abstract',
	'../views/Qa',
	'../views/QaCondition',
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Qa = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('qa');
				var qaView = new App.Admin.Views.Qa();
				var qaConditionView = new App.Admin.Views.QaCondition();
				App.main.show(qaView);
				qaView.condition.show(qaConditionView);
			}
		});
	});

	return App.Admin.Controllers.Qa;
});
