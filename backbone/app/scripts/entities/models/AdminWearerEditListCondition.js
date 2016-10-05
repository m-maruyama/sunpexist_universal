define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminWearerEditListCondition = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					agreement_no : null,
					cster_emply_cd : null,
					werer_name : null,
					sex_kbn : null,
					section : null,
					job_type : null,
				};
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
				}
				if(this.get('cster_emply_cd')) {
					result.cster_emply_cd = this.get('cster_emply_cd');
				}
				if(this.get('werer_name')) {
					result.werer_name = this.get('werer_name');
				}
				if(this.get('sex_kbn')) {
					result.sex_kbn = this.get('sex_kbn');
				}
				if(this.get('section')) {
					result.section = this.get('section');
				}
				if(this.get('job_type')) {
					result.job_type = this.get('job_type');
				}
				return result;
			},
		});
	});
});
