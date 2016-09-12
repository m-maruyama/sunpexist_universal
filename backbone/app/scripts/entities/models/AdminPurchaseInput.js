define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminPurchaseInput = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					agreement_no : null,
					item_name : null,
					color_cd : null,
					size_cd : null,
					price_rate : null
				};
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
				}
				if(this.get('item_name')) {
					result.item_name = this.get('item_name');
				}
				if(this.get('color_cd')) {
					result.color_cd = this.get('color_cd');
				}
				if(this.get('size_cd')) {
					result.size_cd = this.get('size_cd');
				}
				if(this.get('price_rate')) {
					result.price_rate = this.get('price_rate');
				}
				return result;
			}
		});
	});
});
