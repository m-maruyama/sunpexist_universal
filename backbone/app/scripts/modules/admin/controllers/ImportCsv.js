define([
	'app',
	'./Abstract',
	'../views/ImportCsv',
	'../views/ImportCsvCondition',
	'../views/AgreementNoCondition',
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
				var importCsvListConditionModel = new App.Entities.Models.AdminImportCsvListCondition();
				var importCsvConditionView = new App.Admin.Views.ImportCsvCondition({
					collection: importCsvCollection,
					model:importCsvListConditionModel
				});
				var agreementNoConditionView = new App.Admin.Views.AgreementNoCondition();

				var fetchList = function(){
					importCsvConditionView.fetch(importCsvListConditionModel);
				};

				App.main.show(importCsvView);
				importCsvView.condition.show(importCsvConditionView);
				importCsvConditionView.agreement_no.show(agreementNoConditionView);


			}
		});
	});
	return App.Admin.Controllers.ImportCsv;
});
