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
					appointment_ymd : null,
					rntl_sect_cd : null,
					job_type : null,
					ship_to_cd : null,
					ship_to_brnch_cd : null,
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
				return result;
			},
            validator: function(model){
			    var error_len = 0;
                var errors = {};
                if (this.get('cster_emply_cd')&&this.get('cster_emply_cd').match(/[^A-Za-z0-9]+/)) {
                    errors['cster_emply_cd'] = '社員コードは半角英数字で入力してください。';
                }else{
                	//バイト数チェック
					if(CountLength(this.get('cster_emply_cd')) > 10){
						errors['cster_emply_cd'] = '社員コードの文字数が多すぎます。';

					}
				}

                if (!this.get('werer_name')) {
					errors['werer_name'] = '着用者名が未入力です。';
                }else{
					//バイト数チェック
					if(CountLength(this.get('werer_name')) > 22){
						errors['werer_name'] = '着用者名の文字数が多すぎます。';

					}
				}
				if (this.get('werer_name_kana')) {
					//バイト数チェック
					if(CountLength(this.get('werer_name_kana')) > 22){
						errors['werer_name'] = '着用者名(カナ)の文字数が多すぎます。';

					}
				}
                if (!this.get('sex_kbn')) {
					errors['sex_kbn'] = '性別が未選択です。';
                }
                if (!this.get('resfl_ymd')) {
					errors['resfl_ymd'] = '異動日が未入力です。';
                }
                if (!this.get('rntl_sect_cd')) {
					errors['rntl_sect_cd'] = '拠点が未選択です。';
                }
                if (!this.get('job_type')) {
					errors['job_type'] = '貸与パターンが未選択です。';
                }
				if(Object.keys(errors).length > 0){
					var error_array = {};
					error_array['errors'] = errors;
					return error_array;
				}else{
					return false;
				}

                // if(error_len > 0){
                //     return errors;
                // }
            }
        });
		/****************************************************************
		 * バイト数を数える
		 *
		 * 引数 ： str 文字列
		 * 戻り値： バイト数
		 *
		 ****************************************************************/
		function CountLength(str) {
			var r = 0;
			for (var i = 0; i < str.length; i++) {
				var c = str.charCodeAt(i);
				// Shift_JIS: 0x0 ～ 0x80, 0xa0 , 0xa1 ～ 0xdf , 0xfd ～ 0xff
				// Unicode : 0x0 ～ 0x80, 0xf8f0, 0xff61 ～ 0xff9f, 0xf8f1 ～ 0xf8f3
				if ( (c >= 0x0 && c < 0x81) || (c == 0xf8f0) || (c >= 0xff61 && c < 0xffa0) || (c >= 0xf8f1 && c < 0xf8f4)) {
					r += 1;
				} else {
					r += 2;
				}
			}
			return r;
		}

	});
});
