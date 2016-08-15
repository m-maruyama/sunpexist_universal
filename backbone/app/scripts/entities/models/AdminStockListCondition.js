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
					job_type : null,
					item_cd : null,
					color_cd : null,
					size : null,
					zk_status_cd1 : null,
					zk_status_cd2 : null,
					zk_status_cd3 : null,
					order : null,
					mode : null,
					sort_key : null
				};
				if(this.get('job_type')) {
					result.job_type = this.get('job_type');
				}
				if(this.get('item_cd')) {
					result.item_cd = this.get('item_cd');
				}
				if(this.get('color_cd')) {
					result.color_cd = this.get('color_cd');
				}
				if(this.get('size')) {
					result.size = this.get('size');
				}
				if(this.get('zk_status_cd1')) {
					result.zk_status_cd1 = this.get('zk_status_cd1');
				}
				if(this.get('zk_status_cd2')) {
					result.zk_status_cd2 = this.get('zk_status_cd2');
				}
				if(this.get('zk_status_cd3')) {
					result.zk_status_cd3 = this.get('zk_status_cd3');
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
