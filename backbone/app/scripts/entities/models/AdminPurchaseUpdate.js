define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminPurchaseUpdate = Backbone.Model.extend({
			initialize: function() {
				_.extend(this, Backbone.Validation.mixin);
			},
			defaults: {
				corporate_id: null,
				rntl_cont_no : null,
				rntl_sect_cd : null,
				item_cd: null,
				color_cd: null,
				size_cd: null,
				item_name: null,
				piece_rate: null,
				quantity: null,
				total_amount: null,
				accnt_no: null,
				snd_kbn: null,
				rgst_user_id: null,
				upd_user_id: null,
				upd_pg_id: null,
			},
			getReq: function(){
				return {
					corporate_id: this.get('corporate_id'),
					rntl_cont_no: this.get('rntl_cont_no'),
					rntl_sect_cd: this.get('rntl_sect_cd'),
					item_cd: this.get('item_cd'),
					color_cd: this.get('color_cd'),
					size_cd: this.get('size_cd'),
					item_name: this.get('item_name'),
					piece_rate: this.get('piece_rate'),
					quantity: this.get('quantity'),
					total_amount: this.get('total_amount'),
					accnt_no: this.get('accnt_no'),
					snd_kbn: this.get('snd_kbn'),
					rgst_user_id: this.get('accnt_no'),
					upd_user_id: this.get('accnt_no'),
					upd_pg_id: this.get('accnt_no'),
				};
				return result;

			},

			// reset: function(){
				// this.set('oldpassword', null);
				// this.set('newpassword', null);
				// this.set('confirm', null);
			// }
		});
	});
});
