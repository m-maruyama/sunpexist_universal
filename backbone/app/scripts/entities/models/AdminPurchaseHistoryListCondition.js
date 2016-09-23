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
					rntl_sect_cd : null,
					line_no : null,
					sale_order_date : null,
					item_cd : null,
					color_cd : null,
					size_cd : null,
					item_name : null,
					piece_rate : null,
					quantity : null,
					total_amount : null,
					accnt_no : null,
					rgst_date : null,
					rgst_user_id : null,
					upd_date : null,
					upd_user_id : null,
					upd_pg_id : null,
				};
				if(this.get('corporate_id')) {
					result.corporate_id = this.get('corporate_id');
				}
				if(this.get('rntl_cont_no')) {
					result.rntl_cont_no = this.get('rntl_cont_no');
				}
				if(this.get('rntl_sect_cd')) {
					result.rntl_sect_cd = this.get('rntl_sect_cd');
				}
				if(this.get('line_no')) {
					result.line_no = this.get('line_no');
				}
				if(this.get('sale_order_date')) {
					result.sale_order_date = this.get('sale_order_date');
				}
				if(this.get('item_cd')) {
					result.item_cd = this.get('item_cd');
				}
				if(this.get('color_cd')) {
					result.color_cd = this.get('color_cd');
				}
				if(this.get('size_cd')) {
					result.size_cd = this.get('size_cd');
				}
				if(this.get('item_name')) {
					result.item_name = this.get('item_name');
				}
				if(this.get('piece_rate')) {
					result.piece_rate = this.get('piece_rate');
				}
				if(this.get('quantity')) {
					result.quantity = this.get('quantity');
				}
				if(this.get('total_amount')) {
					result.total_amount = this.get('total_amount');
				}
				if(this.get('accnt_no')) {
					result.accnt_no = this.get('accnt_no');
				}
				if(this.get('rgst_date')) {
					result.rgst_date = this.get('rgst_date');
				}
				if(this.get('rgst_user_id')) {
					result.rgst_user_id = this.get('rgst_user_id');
				}
				if(this.get('upd_date')) {
					result.upd_date = this.get('upd_date');
				}
				if(this.get('upd_user_id')) {
					result.upd_user_id = this.get('upd_user_id');
				}
				if(this.get('upd_pg_id')) {
					result.upd_pg_id = this.get('upd_pg_id');
				}

				return result;
			}
		});
	});
});
