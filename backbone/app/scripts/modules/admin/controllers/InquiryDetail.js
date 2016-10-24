define([
	'app',
	'./Abstract',
	'../views/InquiryDetail',
	'../views/InquiryDetailCondition',
	"entities/models/AdminInquiryDetail",
	"entities/models/AdminInquiryDetailListCondition",
	"entities/collections/AdminInquiryDetailListList",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.InquiryDetail = App.Admin.Controllers.Abstract.extend({
			_sync : function(){
				var that = this;
				this.setNav('inquiryDetail');
				var modal = false;
				var inquiryDetailModel = null;
				var inquiryDetailView = new App.Admin.Views.InquiryDetail();
				var inquiryDetailListListCollection = new App.Entities.Collections.AdminInquiryDetailListList();

				var inquiryDetailListConditionModel = new App.Entities.Models.AdminInquiryDetailListCondition();
				var inquiryDetailConditionView = new App.Admin.Views.InquiryDetailCondition({
					model:inquiryDetailListConditionModel
				});

				App.main.show(inquiryDetailView);
				inquiryDetailView.condition.show(inquiryDetailConditionView);
			}
		});
	});

	return App.Admin.Controllers.InquiryDetail;
});
