define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound'
], function(App) {
    'use strict';
    App.module('Admin.Views', function (Views, App, Backbone, Marionette, $, _) {
        Views.WearerInputCondition = Marionette.LayoutView.extend({
            template: App.Admin.Templates.wearerInputCondition,
            behaviors: {
                "Alerts": {
                    behaviorClass: App.Admin.Behaviors.Alerts
                }
            },
            regions: {
                "agreement_no": ".agreement_no",
                "section_modal": ".section_modal",
                "individual_number": ".individual_number",
            },
            ui: {
                "agreement_no": ".agreement_no",
                "cster_emply_cd": "#cster_emply_cd",
                "cster_emply_cd_chk": "#cster_emply_cd_chk",
                "werer_name": "#werer_name",
                "werer_name_kana": "#werer_name_kana",
                "section_modal": ".section_modal",
                "sex_kbn": "#sex_kbn",
                "resfl_ymd": "#resfl_ymd",
                'section_btn': '#section_btn',
                'section': '#section',
                'job_type': '#job_type',
                "m_shipment_to": "#m_shipment_to",
                'zip_no': '#zip_no',
                "address": "#address",
                'datepicker': '.datepicker',
                'timepicker': '.timepicker'
            },
            // bindings: {
            //     "#agreement_no": "agreement_no",
            //     "#cster_emply_cd": "cster_emply_cd",
            //     "section_modal": ".section_modal",
            //     'section_btn': '#section_btn',
            //     'zip_no': '#zip_no',
            //     'section': '#section',
            //     'job_type': '#job_type',
            //     "m_shipment_to": "#m_shipment_to",
            //     "address": "#address",
            //     "resfl_ymd": "#resfl_ymd",
            //     "individual_number": "#individual_number",
            //     "search": '.search',
            //     'datepicker': '.datepicker',
            //     'timepicker': '.timepicker'
            // },
            onRender: function () {
                var maxTime = new Date();
                maxTime.setHours(15);
                maxTime.setMinutes(59);
                maxTime.setSeconds(59);
                var minTime = new Date();
                minTime.setHours(9);
                minTime.setMinutes(0);
                this.ui.datepicker.datetimepicker({
                    format: 'YYYY/MM/DD',
                    //useCurrent: 'day',
                    //defaultDate: yesterday,
                    //maxDate: yesterday,
                    locale: 'ja',
                    sideBySide: true,
                    useCurrent: false,
                    // daysOfWeekDisabled:[0,6]
                });
                this.ui.datepicker.on('dp.change', function () {
                    $(this).data('DateTimePicker').hide();
                    //$(this).find('input').trigger('input');
                });
            },

            fetch: function (agreement_no) {
                var cond = {
                    "scr": '着用者入力',
                    "cond": {"agreement_no": agreement_no}
                };
                var that = this;
                var modelForUpdate = this.model;
                modelForUpdate.url = App.api.WI0010;
                modelForUpdate.fetchMx({
                    data: cond,
                    success: function (res) {
                        $.unblockUI();
                        var errors = res.get('errors');
                        if (errors) {
                            var errorMessages = errors.map(function (v) {
                                return v.error_message;
                            });
                            that.triggerMethod('showAlerts', errorMessages);
                        }
                        $('#agreement_no').prop("disabled", true);
                        that.render();
                    },
                    complete: function (res) {
                    }
                });
            },
            insert_wearer: function (agreement_no) {
                var that = this;
                var model = this.model;
                model.set('agreement_no', agreement_no);
                model.set('cster_emply_cd_chk', this.ui.cster_emply_cd_chk.prop('checked'));
                model.set('cster_emply_cd', this.ui.cster_emply_cd.val());
                model.set('werer_name', this.ui.werer_name.val());
                model.set('werer_name_kana', this.ui.werer_name_kana.val());
                model.set('sex_kbn', this.ui.sex_kbn.val());
                model.set('resfl_ymd', this.ui.resfl_ymd.val());
                model.set('rntl_sect_cd', this.ui.section.val());
                model.set('job_type', this.ui.job_type.val());
                if(this.ui.m_shipment_to.val()){
                    var m_shipment_to_array = this.ui.m_shipment_to.val().split(',');
                    model.set('ship_to_cd', m_shipment_to_array[0]);
                    model.set('ship_to_brnch_cd', m_shipment_to_array[1]);
                }
                model.set('zip_no', this.ui.zip_no.val());
                model.set('address', this.ui.address.val());
                var errors = model.validator(model);
                if(errors) {
                    return errors;
                }
                var cond = {
                    "scr": '着用者登録',
                    "cond": model.getReq()
                };
                model.url = App.api.WI0012;



                var fd = new FormData();
                var data = $('<input type="hidden" name="data" />');
                fd.append('data',JSON.stringify(cond));


                var url = App.api.WI0012;
                var postData = {
                    type : "POST",
                    data : fd,
                    processData : false,
                    contentType : false,
                    dataType: "json"
                };
                errors = $.ajax( url, postData ).done(function (res) {
                    errors = res['errors'];
                    if(errors) {
                        return errors;
                    }else{
                        alert('着用者を登録しました。');
                        location.href = './wearer_input_complete.html';
                    }

                });
                if(errors){
                    return errors;
                }else{
                    return;
                }
            },

            events: {
                'change @ui.section': function (e) {
                    e.preventDefault();
                    if (this.ui.section.val()) {
                        change_select(this.model, $('#agreement_no').val(), this.ui.section.val(), this.ui.m_shipment_to.val(), this.ui.m_shipment_to.children(':selected').text());
                    } else {
                        $('#zip_no').val('');
                        $('#address').text('');
                    }

                },
                'change @ui.m_shipment_to': function (e) {
                    e.preventDefault();
                    change_select(this.model, $('#agreement_no').val(), this.ui.section.val(), this.ui.m_shipment_to.val(), this.ui.m_shipment_to.children(':selected').text());
                },
                'click @ui.section_btn': function (e) {
                    e.preventDefault();
                    this.triggerMethod('click:section_btn', this.model);
                },
                'change @ui.job_type': function () {
                    //貸与パターン」のセレクトボックス変更時に、職種マスタ．特別職種フラグ＝ありの貸与パターンだった場合、アラートメッセージを表示する。
                    var sp_flg = this.ui.job_type.val().split(',');
                    if (sp_flg[1] === '1') {
                        alert('社内申請手続きを踏んでますか？');
                        return;
                    }
                },
            },
        });
    });

    function change_select(modelForUpdate, agreement_no, section, m_shipment_to, m_shipment_to_name) {
        var cond = {
            "scr": '拠点変更',
            "cond": {
                "agreement_no": agreement_no,
                "rntl_sect_cd": section,
                "m_shipment_to": m_shipment_to,
                "m_shipment_to_name": m_shipment_to_name,
            }
        };
        modelForUpdate.url = App.api.WI0011;
        modelForUpdate.fetchMx({
            data: cond,
            success: function (res) {
                $('#zip_no').val(res.attributes.change_m_shipment_to_list[0].zip_no);
                $('#address').text(res.attributes.change_m_shipment_to_list[0].address);
            },
            complete: function (res) {
            }
        });

    }
});
