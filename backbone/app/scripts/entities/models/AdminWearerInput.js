define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminWearerInput = Backbone.Model.extend({
			url: App.api.WI0010,
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					agreement_no : null,
					// emply_order_no : null,
					// section : null,
					// job_type : null,
					// individual_number : null,
				};
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
				}
				// if(this.get('emply_order_no')) {
				// 	result.emply_order_no = this.get('emply_order_no');
				// }
				// if(this.get('section')) {
				// 	result.section = this.get('section');
				// }
				// if(this.get('job_type')) {
				// 	result.job_type = this.get('job_type');
				// }
				// if(this.get('individual_number')) {
				// 	result.individual_number = this.get('individual_number');
				// }
				return result;
			}
		});
	});
});
