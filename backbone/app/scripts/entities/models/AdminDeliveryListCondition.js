define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminDeliveryListCondition = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					no : null,
					member_no : null,
					office : null,
					job_type : null,
					order_day_from : null,
					order_day_to : null,
					send_day_from : null,
					send_day_to : null,
					status0: this.get('status0'),
					status1: this.get('status1'),
					status2: this.get('status2'),
					status3: this.get('status3'),
					order_kbn0: this.get('order_kbn0'),
					order_kbn1: this.get('order_kbn1'),
					order_kbn2: this.get('order_kbn2'),
					order_kbn3: this.get('order_kbn3'),
					order : null,
					mode : null,
					sort_key : null
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
				if(this.get('job_type')) {
					result.job_type = this.get('job_type');
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
				if(this.get('order')) {
					result.order = this.get('order');
				}
				if(this.get('mode')) {
					result.mode = this.get('mode');
				}
				if(this.get('sort_key')) {
					result.sort_key = this.get('sort_key');
				}
				return result;
			}
		});
	});
});
