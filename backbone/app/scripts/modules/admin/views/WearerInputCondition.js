define([
    'app',
    '../Templates',
    'backbone.stickit',
    'bootstrap-datetimepicker',
    '../views/WearerInput',
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
                "werer_cd": "#werer_cd",
                "werer_name": "#werer_name",
                "werer_name_kana": "#werer_name_kana",
                "section_modal": ".section_modal",
                "sex_kbn": "#sex_kbn",
                "appointment_ymd": "#appointment_ymd",
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
            onRender: function () {
                var that = this;
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
                if(window.sessionStorage.getItem('referrer')=='wearer_search'||
                    window.sessionStorage.getItem('referrer')=='wearer_order'||
                    window.sessionStorage.getItem('referrer')=='wearer_order_search'){
                    var referrer = 1;
                }else{
                    var referrer = -1;

                }
                var cond = {
                    "scr": '着用者入力',
                    "cond": {"agreement_no": agreement_no,
                        "referrer" : referrer
                    }
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
                        var res_list = res.attributes;
                        $('#agreement_no').prop("disabled", true);
                        that.render();
                        that.ui.cster_emply_cd_chk.prop('checked',false);
                        that.ui.cster_emply_cd.prop('disabled',true);
                        $('#input_item').val(res_list['param']);
                        if(res_list['rntl_cont_no']&&(referrer > -1)){
                            if(res_list['wearer_info'][0]['cster_emply_cd']){
                                that.ui.cster_emply_cd_chk.prop('checked',true);
                                that.ui.cster_emply_cd.prop('disabled',false);
                                that.ui.cster_emply_cd.val(res_list['wearer_info'][0]['cster_emply_cd']);
                            }
                            that.ui.werer_name.val(res_list['wearer_info'][0]['werer_name']);
                            that.ui.werer_name_kana.val(res_list['wearer_info'][0]['werer_name_kana']);
                            that.ui.appointment_ymd.val(res_list['appointment_ymd']);
                            that.ui.resfl_ymd.val(res_list['resfl_ymd']);
                            that.ui.zip_no.val(res_list['zip_no']);
                            that.ui.address.val(res_list['address1']+res_list['address2']+res_list['address3']+res_list['address4']);
                            $('#input_item').val(res_list['param']);
                            that.ui.zip_no.val(res_list['zip_no']);
                            return;
                        }
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
                model.set('appointment_ymd', this.ui.appointment_ymd.val());
                model.set('resfl_ymd', this.ui.resfl_ymd.val());
                model.set('rntl_sect_cd', this.ui.section.val());
                model.set('job_type', this.ui.job_type.val());
                if(this.ui.m_shipment_to.val()){
                    var m_shipment_to_array = this.ui.m_shipment_to.val().split(',');
                    model.set('ship_to_cd', m_shipment_to_array[0]);
                    model.set('ship_to_brnch_cd', m_shipment_to_array[1]);
                }else{
                    model.set('ship_to_cd', null);
                    model.set('ship_to_brnch_cd', null);
                }
                model.set('zip_no', this.ui.zip_no.val());
                model.set('address', this.ui.address.val());
                var errors = model.validator(model);
                if(errors) {
                    return errors;
                }
                var cond = {
                    "scr": '着用者登録',
                    "mode": 'insert',
                    "cond": model.getReq()
                };
                model.url = App.api.WI0012;

                model.fetchMx({
                    data:cond,
                    success:function(res){
                        var res_val = res.attributes;
                        if(res_val["errors"]) {
                            var er = res_val["errors"]
                            res.attributes["errors"] = null;
                            that.triggerMethod('error_msg', er);
                        }else{
                            alert('着用者を登録しました。');
                            location.href = './wearer_input_complete.html';
                        }
                    }
                });
                return errors;
            },
            input_item: function (rntl_cont_no) {

                var that = this;
                var model = this.model;
                model.set('agreement_no', rntl_cont_no);
                model.set('cster_emply_cd_chk', this.ui.cster_emply_cd_chk.prop('checked'));
                model.set('cster_emply_cd', this.ui.cster_emply_cd.val());
                model.set('werer_name', this.ui.werer_name.val());
                model.set('werer_name_kana', this.ui.werer_name_kana.val());
                model.set('sex_kbn', this.ui.sex_kbn.val());
                model.set('appointment_ymd', this.ui.appointment_ymd.val());
                model.set('resfl_ymd', this.ui.resfl_ymd.val());
                model.set('rntl_sect_cd', this.ui.section.val());
                var job_type = this.ui.job_type.val().split(',');
                model.set('job_type', job_type[0]);
                if(this.ui.m_shipment_to.val()){
                    var m_shipment_to_array = this.ui.m_shipment_to.val().split(',');
                    model.set('ship_to_cd', m_shipment_to_array[0]);
                    model.set('ship_to_brnch_cd', m_shipment_to_array[1]);
                }else{
                    model.set('ship_to_cd', null);
                    model.set('ship_to_brnch_cd', null);
                }
                model.set('zip_no', this.ui.zip_no.val());
                model.set('address', this.ui.address.val());
                var errors = model.validator(model);
                if(errors) {
                    return errors;
                }
                var cond = {
                    "scr": '着用者登録',
                    "mode": 'check',
                    "cond": model.getReq()
                };
                model.url = App.api.WI0012;

                model.fetchMx({
                    data:cond,
                    success:function(res){
                        var res_val = res.attributes;
                        if(res_val["errors"]) {
                            var er = res_val["errors"]
                            res.attributes["errors"] = null;
                            that.triggerMethod('error_msg', er);
                        }else{
                            model.set('rntl_cont_no', rntl_cont_no);
                            var cond = {
                                "scr": '商品詳細入力へ',
                                "data": model.getReq()
                            };
                            model.url = App.api.WS0011;
                            model.fetchMx({
                                data:cond,
                                success:function(res){
                                    window.sessionStorage.setItem('referrer', 'wearer_input');
                                    location.href = './wearer_order.html';
                                }
                            });
                            return errors;
                        }
                    }
                });
            },
            input_delete: function () {
                var that = this;
                var model = this.model;
                var cond = {
                    "scr": '着用者取消チェック',
                };
                model.url = App.api.WI0013;

                model.fetchMx({
                    data:cond,
                    success:function(res){
                        var res_val = res.attributes;
                        if(res_val["error_msg"]) {
                            that.triggerMethod('error_msg', res_val["error_msg"]);
                        }else{
                            if(confirm("着用者入力を削除しますが、よろしいですか？")){
                                var cond = {
                                    "scr": '着用者取消',
                                };
                                model.url = App.api.WI0014;

                                model.fetchMx({
                                    data:cond,
                                    success:function(res){
                                        var res_val = res.attributes;
                                        if(res_val["error_msg"]) {
                                            var er = res_val["errors"]
                                            res.attributes["errors"] = null;
                                            that.triggerMethod('error_msg', er);
                                        }else{
                                            window.sessionStorage.setItem('referrer', 'wearer_input');
                                            location.href = './wearer_input.html';
                                        }
                                    }
                                });
                            };
                        }
                    }
                });
            },
            go_change: function () {
                change_select(this.model, $('#agreement_no').val(), this.ui.section.val(), this.ui.m_shipment_to.val(), this.ui.m_shipment_to.children(':selected').text());
            },

            events: {
                'change @ui.cster_emply_cd_chk': function(e){
                    if(e.target.checked){
                        this.ui.cster_emply_cd.prop('disabled',false);
                    }else{
                        this.ui.cster_emply_cd.prop('disabled',true);
                    }
                },
                'change @ui.section': function (e) {
                    e.preventDefault();
                    change_select(this.model, $('#agreement_no').val(), this.ui.section.val(), this.ui.m_shipment_to.val(), this.ui.m_shipment_to.children(':selected').text());
                },
                'select @ui.section': function (e) {
                    e.preventDefault();
                    change_select(this.model, $('#agreement_no').val(), this.ui.section.val(), this.ui.m_shipment_to.val(), this.ui.m_shipment_to.children(':selected').text());
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
                $('#address').val(res.attributes.change_m_shipment_to_list[0].address);
            },
            complete: function (res) {
            }
        });

    }
});
