define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminStockListCondition = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					agreement_no : null,
					job_type_zaiko : null,
					item : null,
					item_color : null,
					item_size : null,
				};
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
				}
				if(this.get('job_type_zaiko')) {
					result.job_type_zaiko = this.get('job_type_zaiko');
				}
				if(this.get('item')) {
					result.item = this.get('item');
				}
				if(this.get('item_color')) {
					result.item_color = this.get('item_color');
				}
				if(this.get('item_size')) {
					result.item_size = this.get('item_size');
				}

				return result;
			}
		});
	});
});
