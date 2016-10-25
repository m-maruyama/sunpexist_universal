define([
	'app',
	'./Abstract',
	'../views/WearerEndOrder',
	'../views/WearerEndOrderCondition',
	'../views/WearerEndOrderList',
	'../views/AgreementNoConditionOrder',
	'../views/ReasonKbnConditionOrder',
	'../views/SectionConditionOrder',
	'../views/JobTypeConditionOrder',
	'../views/Pagination',
    '../behaviors/Alerts',
	"entities/models/Pager",
	"entities/models/AdminWearerEndOrder",
	"entities/models/AdminWearerEndOrderListCondition",
	"entities/models/AdminSectionModalListCondition",
	"entities/collections/AdminSectionModalListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.WearerEndOrder = App.Admin.Controllers.Abstract.extend({
            behaviors: {
                "Alerts": {
                    behaviorClass: App.Admin.Behaviors.Alerts
                }
            },
			_sync : function(){
				var that = this;
				this.setNav('wearerEndOrder');
				var wearerEndOrderModel = new App.Entities.Models.AdminWearerEndOrder();
				var wearerEndOrderView = new App.Admin.Views.WearerEndOrder({
					model:wearerEndOrderModel
				});
				var wearerEndListListView = new App.Admin.Views.WearerEndOrderList();

				var agreementNoConditionView = new App.Admin.Views.AgreementNoConditionOrder();
				var reasonKbnConditionView = new App.Admin.Views.ReasonKbnConditionOrder();
				var sectionConditionView = new App.Admin.Views.SectionConditionOrder();
				var jobTypeConditionView = new App.Admin.Views.JobTypeConditionOrder();

				var wearerEndOrderListConditionModel = new App.Entities.Models.AdminWearerEndOrderListCondition();
				var wearerEndOrderConditionView = new App.Admin.Views.WearerEndOrderCondition({
					model:wearerEndOrderListConditionModel
				});

				//着用者のみ登録して終了
				this.listenTo(wearerEndOrderView, 'click:input_insert', function(agreement_no){
					var errors = wearerEndOrderConditionView.insert_wearer(agreement_no);
					if(errors){
						wearerEndOrderView.triggerMethod('showAlerts', errors);
					}
				});
				App.main.show(wearerEndOrderView);
				wearerEndOrderView.condition.show(wearerEndOrderConditionView);
				wearerEndOrderView.listTable.show(wearerEndListListView);
				wearerEndOrderConditionView.agreement_no.show(agreementNoConditionView);
				wearerEndOrderConditionView.reason_kbn.show(reasonKbnConditionView);
				wearerEndOrderConditionView.section.show(sectionConditionView);
				wearerEndOrderConditionView.job_type.show(jobTypeConditionView);
			}
		});
	});

	return App.Admin.Controllers.WearerEndOrder;
});
