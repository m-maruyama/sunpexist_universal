define([
	'app',
	'./Abstract',
	'../views/ImportCsv',
	'../views/ImportCsvCondition',
	"entities/models/AdminImportCsvListCondition",
	"entities/collections/AdminImportCsv",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.ImportCsv = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('importCsv');

				var importCsvModel = null;
				var importCsvCollection = new App.Entities.Collections.AdminImportCsv();
				var importCsvView = new App.Admin.Views.ImportCsv();
				var csvListConditionModel = new App.Entities.Models.AdminImportCsvListCondition();
				var importCsvConditionView = new App.Admin.Views.ImportCsvCondition({
					collection: importCsvCollection,
					model:csvListConditionModel
				});
				var fetchList = function(){
					importCsvConditionView.fetch(csvListConditionModel);
				};

				App.main.show(importCsvView);
				importCsvView.condition.show(importCsvConditionView);
			}
		});
	});
	return App.Admin.Controllers.ImportCsv;
});
