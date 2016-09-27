define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminPurchaseHistoryListCondition = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					corporate_id : null,
					rntl_cont_no : null,
					order_day_from : null,
					order_day_to : null,
					section : null,
					item_cd : null,
					item_color : null,
					item_size : null,
				};
				if(this.get('corporate_id')) {
					result.corporate_id = this.get('corporate_id');
				}
				if(this.get('rntl_cont_no')) {
					result.rntl_cont_no = this.get('rntl_cont_no');
				}
				if(this.get('order_day_from')) {
					result.order_day_from = this.get('order_day_from');
				}
				if(this.get('order_day_to')) {
					result.order_day_to = this.get('order_day_to');
				}
				if(this.get('section')) {
					result.section = this.get('section');
				}
				if(this.get('input_item')) {
					result.item_cd = this.get('input_item');
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
