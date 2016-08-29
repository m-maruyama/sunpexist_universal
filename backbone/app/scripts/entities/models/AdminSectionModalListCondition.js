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
				};
				if(this.get('rntl_sect_cd')) {
					result.rntl_sect_cd = this.get('rntl_sect_cd');
				}
				if(this.get('rntl_sect_name')) {
					result.rntl_sect_name = this.get('rntl_sect_name');
				}
				return result;
			}
		});
	});
});
