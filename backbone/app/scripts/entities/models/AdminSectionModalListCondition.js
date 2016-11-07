define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminSectionModalListCondition = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					rntl_sect_cd : null,
					rntl_sect_name : null,
					agreement_no : null,
					corporate : null,
					corporate_flg : false,
					// sort_key : null,
					// order : null,
				};
				if(this.get('rntl_sect_cd')) {
					result.rntl_sect_cd = this.get('rntl_sect_cd');
				}
				if(this.get('rntl_sect_name')) {
					result.rntl_sect_name = this.get('rntl_sect_name');
				}
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
				}
				if(this.get('corporate')) {
					result.corporate = this.get('corporate');
				}
				if(this.get('corporate_flg')) {
					result.corporate_flg = true;
				}
				// if(this.get('sort_key')) {
				// 	result.sort_key = this.get('sort_key');
				// }
				// if(this.get('order')) {
				// 	result.order = this.get('order');
				// }
				return result;
			}
		});
	});
});
