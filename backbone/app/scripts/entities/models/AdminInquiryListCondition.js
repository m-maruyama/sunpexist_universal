define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminInquiryListCondition = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					agreement_no : null,
					target_ym : null,
					section : null,
					td_no : null,
				};
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
				}
				if(this.get('target_ym')) {
					result.target_ym = this.get('target_ym');
				}
				if(this.get('section')) {
					result.section = this.get('section');
				}
				if(this.get('td_no')) {
					result.td_no = this.get('td_no');
				}
//console.log(result);
				return result;
			}
		});
	});
});
