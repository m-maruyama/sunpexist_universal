define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminWearerInputListCondition = Backbone.Model.extend({
			url: App.api.WI0011,
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			},
			getReq: function() {
				var result = {
					agreement_no : null,
					cster_emply_cd : null,
					werer_name : null,
					werer_name_kana : null,
					sex_kbn : null,
					resfl_ymd : null,
					section : null,
					job_type : null,
					m_shipment_to : null,
					zip_no : null,
					address : null,
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
				if(this.get('werer_name_kana')) {
					result.werer_name_kana = this.get('werer_name_kana');
				}
				if(this.get('sex_kbn')) {
					result.sex_kbn = this.get('sex_kbn');
				}
				if(this.get('resfl_ymd')) {
					result.resfl_ymd = this.get('resfl_ymd');
				}
				if(this.get('section')) {
					result.section = this.get('section');
				}
				if(this.get('job_type')) {
					result.job_type = this.get('job_type');
				}
				if(this.get('m_shipment_to')) {
					result.m_shipment_to = this.get('m_shipment_to');
				}
				if(this.get('zip_no')) {
					result.zip_no = this.get('zip_no');
				}
				if(this.get('address')) {
					result.address = this.get('address');
				}
				return result;
			},
            validator: function(model){
			    var error_len = 0;
                var errors = {};
                if (this.get('cster_emply_cd')&&this.get('cster_emply_cd').match(/[^A-Za-z0-9]+/)) {
                    errors['ng'] = '社員コードは半角英数字で入力してください。';
                    return errors;
                }
                return null;


                if(error_len > 0){
                    return errors;
                }
            }
        });

	});
});
