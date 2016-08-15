define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminImportCsvListCondition = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					// no : null,
					// member_no : null,
					// office : null,
					// job_type : null,
					// order_day_from : null,
					file_input : null,
					// status0: this.get('status0'),
					// status1: this.get('status1'),
					// order_kbn0: this.get('order_kbn0'),
					// order_kbn1: this.get('order_kbn1'),
					// order_kbn2: this.get('order_kbn2'),
					// order_kbn3: this.get('order_kbn3')
				};
				// if(this.get('no')) {
					// result.no = this.get('no');
				// }
				// if(this.get('member_no')) {
					// result.member_no = this.get('member_no');
				// }
				// if(this.get('office')) {
					// result.office = this.get('office');
				// }
				// if(this.get('job_type')) {
					// result.job_type = this.get('job_type');
				// }
				// if(this.get('order_day_from')) {
					// result.order_day_from = this.get('order_day_from');
				// }
				if(this.get('file_input')) {
					result.file_input = this.get('file_input');
				}
				// // if(this.get('time_from')) {
					// // result.time_from = parseInt(this.get('time_from').replace(/:/g, ''), 10);
				// // }
				// // if(this.get('time_to')) {
					// // result.time_to = parseInt(this.get('time_to').replace(/:/g, ''), 10);
				// // }
				// return result;
			}
		});
	});
});
