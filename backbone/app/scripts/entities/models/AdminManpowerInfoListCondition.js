define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminManpowerInfoListCondition = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					agreement_no : null,
					no : null,
					member_name : null,
					wearer_name_src1 : this.get('wearer_name_src1'),
					wearer_name_src2 : this.get('wearer_name_src2'),
					section : null,
					job_type : null,
					input_item : null,
					item_color : null,
					item_size : null,
					individual_number : null,
					wearer_kbn0: this.get('wearer_kbn0'),
					wearer_kbn1: this.get('wearer_kbn1'),
					wearer_kbn2: this.get('wearer_kbn2'),
					wearer_kbn3: this.get('wearer_kbn3'),
					wearer_kbn4: this.get('wearer_kbn4'),
					td_no : null,
				};
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
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
