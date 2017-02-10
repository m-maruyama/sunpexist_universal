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
					rntl_cont_no : null,
					cster_emply_cd_chk : null,
					cster_emply_cd : null,
					werer_name : null,
					werer_cd : null,
					werer_name_kana : null,
					sex_kbn : null,
					resfl_ymd : null,
					appointment_ymd : null,
					rntl_sect_cd : null,
					job_type : null,
					ship_to_cd : null,
					ship_to_brnch_cd : null,
					zip_no : null,
					address : null,
					m_job_type_comb_hkey : null,
					m_section_comb_hkey : null,
				};

				if(this.get('m_section_comb_hkey')) {
					result.m_section_comb_hkey = this.get('m_section_comb_hkey');
				}
				if(this.get('m_job_type_comb_hkey')) {
					result.m_job_type_comb_hkey = this.get('m_job_type_comb_hkey');
				}
				if(this.get('rntl_cont_no')) {
					result.rntl_cont_no = this.get('rntl_cont_no');
				}
				if(this.get('agreement_no')) {
					result.agreement_no = this.get('agreement_no');
				}
				if(this.get('cster_emply_cd_chk')) {
					result.cster_emply_cd_chk = this.get('cster_emply_cd_chk');
				}
				if(this.get('cster_emply_cd')) {
					result.cster_emply_cd = this.get('cster_emply_cd');
				}
				if(this.get('werer_cd')) {
					result.werer_cd = this.get('werer_cd');
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
				if(this.get('appointment_ymd')) {
					result.appointment_ymd = this.get('appointment_ymd');
				}
				if(this.get('rntl_sect_cd')) {
					result.rntl_sect_cd = this.get('rntl_sect_cd');
				}
				if(this.get('job_type')) {
					result.job_type = this.get('job_type');
				}
				if(this.get('ship_to_cd')) {
					result.ship_to_cd = this.get('ship_to_cd');
				}
				if(this.get('ship_to_brnch_cd')) {
					result.ship_to_brnch_cd = this.get('ship_to_brnch_cd');
				}
				if(this.get('zip_no')) {
					result.zip_no = this.get('zip_no');
				}
				if(this.get('address')) {
					result.address = this.get('address');
				}
				//console.log(result);

				return result;
			},
        });

	});
});
