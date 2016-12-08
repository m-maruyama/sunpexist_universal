define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminReceiveListCondition = Backbone.Model.extend({
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
					order_day_from : null,
					order_day_to : null,
					receipt_day_from : null,
					receipt_day_to : null,
					individual_number : null,
					status0: this.get('status0'),
					status1: this.get('status1'),
					order_kbn0: this.get('order_kbn0'),
					order_kbn1: this.get('order_kbn1'),
					order_kbn2: this.get('order_kbn2'),
					order_kbn3: this.get('order_kbn3'),
					order_kbn4: this.get('order_kbn4'),
					reason_kbn0: this.get('reason_kbn0'),
					reason_kbn1: this.get('reason_kbn1'),
					reason_kbn2: this.get('reason_kbn2'),
					reason_kbn3: this.get('reason_kbn3'),
					reason_kbn4: this.get('reason_kbn4'),
					reason_kbn5: this.get('reason_kbn5'),
					reason_kbn6: this.get('reason_kbn6'),
					reason_kbn7: this.get('reason_kbn7'),
					reason_kbn8: this.get('reason_kbn8'),
					reason_kbn9: this.get('reason_kbn9'),
					reason_kbn10: this.get('reason_kbn10'),
					reason_kbn11: this.get('reason_kbn11'),
					reason_kbn12: this.get('reason_kbn12'),
					reason_kbn13: this.get('reason_kbn13'),
					reason_kbn14: this.get('reason_kbn14'),
					reason_kbn15: this.get('reason_kbn15'),
					reason_kbn16: this.get('reason_kbn16'),
					reason_kbn17: this.get('reason_kbn17'),
					reason_kbn18: this.get('reason_kbn18'),
					reason_kbn19: this.get('reason_kbn19'),
					updateFlag: this.get('updateFlag'),
					disabled: this.get('disabled'),
					alert_id: null,
					order : null,
					mode : null,
					sort_key : null,
					td_no : null
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
				if(this.get('receipt_day_from')) {
					result.receipt_day_from = this.get('receipt_day_from');
				}
				if(this.get('receipt_day_to')) {
					result.receipt_day_to = this.get('receipt_day_to');
				}
				if(this.get('individual_number')) {
					result.individual_number = this.get('individual_number');
				}
				if(this.get('alert_id')) {
					result.alert_id = this.get('alert_id');
				}
				if(this.get('td_no')) {
					result.td_no = this.get('td_no');
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
