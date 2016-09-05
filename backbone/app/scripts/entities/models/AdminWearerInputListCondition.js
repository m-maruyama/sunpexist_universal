define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminWearerInputListCondition = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					agreement_no : null,
					no : null,
					emply_order_no : null,
					member_no : null,
					member_name : null,
					section : null,
					job_type : null,
					input_item : null,
					item_color : null,
					item_size : null,
//					office_cd : null,
					order_day_from : null,
					order_day_to : null,
					send_day_from : null,
					send_day_to : null,
					individual_number : null,
					td_no : null,
				};
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
				}
				if(this.get('no')) {
					result.no = this.get('no');
				}
				if(this.get('emply_order_no')) {
					result.emply_order_no = this.get('emply_order_no');
				}
				if(this.get('member_no')) {
					result.member_no = this.get('member_no');
				}
				if(this.get('member_name')) {
					result.member_name = this.get('member_name');
				}
//				if(this.get('office')) {
//					result.office = this.get('office');
//				}
//				if(this.get('office_cd')) {
//					result.office_cd = this.get('office_cd');
//				}
				if(this.get('section')) {
					result.section = this.get('section');
				}
				if(this.get('job_type')) {
					result.job_type = this.get('job_type');
				}
				if(this.get('input_item')) {
					result.input_item = this.get('input_item');
				}
				if(this.get('item_color')) {
					result.item_color = this.get('item_color');
				}
				if(this.get('item_size')) {
					result.item_size = this.get('item_size');
				}
				if(this.get('order_day_from')) {
					result.order_day_from = this.get('order_day_from');
				}
				if(this.get('order_day_to')) {
					result.order_day_to = this.get('order_day_to');
				}
				if(this.get('send_day_from')) {
					result.send_day_from = this.get('send_day_from');
				}
				if(this.get('send_day_to')) {
					result.send_day_to = this.get('send_day_to');
				}
				// if(this.get('time_from')) {
					// result.time_from = parseInt(this.get('time_from').replace(/:/g, ''), 10);
				// }
				// if(this.get('time_to')) {
					// result.time_to = parseInt(this.get('time_to').replace(/:/g, ''), 10);
				// }
				if(this.get('individual_number')) {
					result.individual_number = this.get('individual_number');
				}
				if(this.get('td_no')) {
					result.td_no = this.get('td_no');
				}

				return result;
			}
		});
	});
});
