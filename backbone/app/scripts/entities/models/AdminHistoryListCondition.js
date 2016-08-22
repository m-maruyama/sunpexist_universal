define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminHistoryListCondition = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					agreement_no : null,
					no : null,
					member_no : null,
					office : null,
					sectione : null,
					job_type : null,
					input_item : null,
					item_color : null,
					item_size : null,
					office_cd : null,
					order_day_from : null,
					order_day_to : null,
					status0: this.get('status0'),
					status1: this.get('status1'),
					status2: this.get('status2'),
					status3: this.get('status3'),
					status4: this.get('status4'),
					order_kbn0: this.get('order_kbn0'),
					order_kbn1: this.get('order_kbn1'),
					order_kbn2: this.get('order_kbn2'),
					order_kbn3: this.get('order_kbn3'),
					order_kbn4: this.get('order_kbn4'),
					order_reason_kbn0: this.get('order_reason_kbn0'),
					order_reason_kbn1: this.get('order_reason_kbn1'),
					order_reason_kbn2: this.get('order_reason_kbn2'),
					order_reason_kbn3: this.get('order_reason_kbn3'),
					order_reason_kbn4: this.get('order_reason_kbn4'),
					order_reason_kbn5: this.get('order_reason_kbn5'),
					order_reason_kbn6: this.get('order_reason_kbn6'),
					order_reason_kbn7: this.get('order_reason_kbn7'),
					order_reason_kbn8: this.get('order_reason_kbn8'),
					order_reason_kbn9: this.get('order_reason_kbn9'),
					order_reason_kbn10: this.get('order_reason_kbn10'),
					order_reason_kbn11: this.get('order_reason_kbn11'),
					order_reason_kbn12: this.get('order_reason_kbn12'),
					order_reason_kbn13: this.get('order_reason_kbn13'),
					order_reason_kbn14: this.get('order_reason_kbn14'),
					order_reason_kbn15: this.get('order_reason_kbn15'),
					order_reason_kbn16: this.get('order_reason_kbn16'),
					order_reason_kbn17: this.get('order_reason_kbn17'),
					order_reason_kbn18: this.get('order_reason_kbn18'),
					order_reason_kbn19: this.get('order_reason_kbn19'),
					individual_number : null,
					td_no : null,
				};
				if(this.get('no')) {
					result.no = this.get('no');
				}
				if(this.get('member_no')) {
					result.member_no = this.get('member_no');
				}
				if(this.get('office')) {
					result.office = this.get('office');
				}
				if(this.get('office_cd')) {
					result.office_cd = this.get('office_cd');
				}
				if(this.get('job_type')) {
					result.job_type = this.get('job_type');
				}
				if(this.get('order_day_from')) {
					result.order_day_from = this.get('order_day_from');
				}
				if(this.get('order_day_to')) {
					result.order_day_to = this.get('order_day_to');
				}
				// if(this.get('time_from')) {
					// result.time_from = parseInt(this.get('time_from').replace(/:/g, ''), 10);
				// }
				// if(this.get('time_to')) {
					// result.time_to = parseInt(this.get('time_to').replace(/:/g, ''), 10);
				// }
				if(this.get('td_no')) {
					result.td_no = this.get('td_no');
				}
				return result;
			}
		});
	});
});
