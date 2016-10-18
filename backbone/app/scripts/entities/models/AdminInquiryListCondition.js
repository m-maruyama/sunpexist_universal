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
					corporate : null,
					agreement_no : null,
					answer_kbn0 : this.get('answer_kbn0'),
					answer_kbn1 : this.get('answer_kbn1'),
					contact_day_from : null,
					contact_day_to : null,
					answer_day_from : null,
					answer_day_to : null,
					section : null,
					interrogator_name : null,
					genre : null,
					interrogator_info : null
				};
				if(this.get('corporate')) {
					result.corporate = this.get('corporate');
				}
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
				}
				if(this.get('contact_day_from')) {
					result.contact_day_from = this.get('contact_day_from');
				}
				if(this.get('contact_day_to')) {
					result.contact_day_to = this.get('contact_day_to');
				}
				if(this.get('answer_day_from')) {
					result.answer_day_from = this.get('answer_day_from');
				}
				if(this.get('answer_day_to')) {
					result.answer_day_to = this.get('answer_day_to');
				}
				if(this.get('section')) {
					result.section = this.get('section');
				}
				if(this.get('interrogator_name')) {
					result.interrogator_name = this.get('interrogator_name');
				}
				if(this.get('genre')) {
					result.genre = this.get('genre');
				}
				if(this.get('interrogator_info')) {
					result.interrogator_info = this.get('interrogator_info');
				}
//console.log(result);
				return result;
			}
		});
	});
});
