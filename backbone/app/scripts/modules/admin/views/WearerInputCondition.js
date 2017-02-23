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
              //"section_modal": ".section_modal",
              "individual_number": ".individual_number"
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
                var that = this;

                if(window.sessionStorage.getItem('referrer') == "wearer_search" ||
                  window.sessionStorage.getItem('referrer') == "wearer_input"||
                  window.sessionStorage.getItem('referrer') == "wearer_order" ||
                    window.sessionStorage.getItem('referrer') == "wearer_delete" ||
                  window.sessionStorage.getItem('referrer') == "wearer_order_search"
                )
                {
/*
                  if (window.sessionStorage.getItem('wearer_search')) {
                    var disp_type = window.sessionStorage.getItem('wearer_search');
                  }
                  if (window.sessionStorage.getItem('wearer_input')) {
                    var disp_type = window.sessionStorage.getItem('wearer_input');
                  }
                  if (window.sessionStorage.getItem('wearer_order_search')) {
                    var disp_type = window.sessionStorage.getItem('wearer_order_search');
                    window.sessionStorage.removeItem('wearer_search');
                    window.sessionStorage.removeItem('wearer_order');
                  }
*/
                  //console.log(disp_type);

                  var disp_type = window.sessionStorage.getItem('referrer');
                  var referrer = 1;
                }else{
                  var disp_type = "";
                  var referrer = -1;
                }
                var cond = {
                    "scr": '着用者入力',
                    "cond": {
                      "agreement_no": agreement_no,
                      "referrer" : referrer,
                      "disp_type" : disp_type
                    }
                };
                //console.log(cond);
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
                        if(res_list['order_flg']){
                            //着用者情報編集時(商品情報登録済み)は「着用者のみ登録して終了」ボタンを隠す
                            $('#input_insert').hide();
                            $('#input_insert_button').hide();
                            $('#input_insert').remove();
                            $('#input_insert_button').remove();
                        }

                      if (res_list['cster_emply_cd']) {
                        that.ui.cster_emply_cd_chk.prop('checked', true);
                      } else {
                        that.ui.cster_emply_cd_chk.prop('checked', false);
                        that.ui.cster_emply_cd.prop('disabled', true);
                      }
                      that.ui.cster_emply_cd.val(res_list['cster_emply_cd']);
                      that.ui.werer_name.val(res_list['werer_name']);
                      that.ui.werer_name_kana.val(res_list['werer_name_kana']);
                      that.ui.appointment_ymd.val(res_list['appointment_ymd']);
                      that.ui.resfl_ymd.val(res_list['resfl_ymd']);
                      that.ui.zip_no.val(res_list['zip_no']);
                      that.ui.address.val(res_list['address']);
                      $('#input_item').val(res_list['param']);
                      return;
                    },
                    complete: function (res) {
                        //拠点と出荷先が同じだったら、拠点と同じに変更
                        var section_name = $('[name=section] option:selected').text();
                        var m_shipment_to = $('[name=m_shipment_to] option:selected').text();
                        if(section_name == m_shipment_to){
                            $('#m_shipment_to').prop('selectedIndex',0);
                        }
                        if(that.ui.section.val()){
                            change_select(that.model, $('#agreement_no').val(), that.ui.section.val(), that.ui.m_shipment_to.val(), that.ui.m_shipment_to.children(':selected').text());

                        }
                    }
                });
            },
            onshow: function(){
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
                if(window.sessionStorage.getItem('referrer')=='wearer_search'||
                    window.sessionStorage.getItem('referrer')=='wearer_order'||
                    window.sessionStorage.getItem('referrer')=='wearer_order_search'){
                    var referrer = 1;
                }else{
                    var referrer = -1;

                }
                var cond = {
                    "scr": '着用者登録-check',
                    "mode": 'check',
                    "referrer" : referrer,
                    "cond": model.getReq()
                };
                model.url = App.api.WI0012;

                model.fetchMx({
                    data: cond,
                    success: function (res) {
                        var res_val = res.attributes;
                        if (res_val["errors"]) {
                            var er = res_val["errors"]
                            res.attributes["errors"] = null;
                            that.triggerMethod('error_msg', er);
                        } else {
                            // JavaScript モーダルで表示
                            $('#myModal').modal('show'); //追加
                            //メッセージの修正
                            document.getElementById("confirm_txt").innerHTML = App.wearer_input_msg; //追加　このメッセージはapp.jsで定義
                            $("#btn_ok").off();
                            $("#btn_ok").on('click', function () { //追加
                                hideModal();
                                var cond = {
                                    "scr": '着用者登録-insert',
                                    "mode": 'insert',
                                    "referrer" : referrer,
                                    "cond": model.getReq()
                                };
                                model.url = App.api.WI0012;
                                model.fetchMx({
                                    data: cond,
                                    success: function (res) {
                                        var res_val = res.attributes;
                                        if (res_val["errors"]) {
                                            var er = res_val["errors"]
                                            res.attributes["errors"] = null;
                                            that.triggerMethod('error_msg', er);
                                        }else{
                                            window.sessionStorage.removeItem('wearer_input_ref');
                                            location.href = './wearer_input_complete.html';
                                        }
                                    }
                                })
                            });
                        }
                    }
                });
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
                    var ship_to_cd = m_shipment_to_array[0];
                    var ship_to_brnch_cd = m_shipment_to_array[1];
                }else{
                    var ship_to_cd = "";
                    var ship_to_brnch_cd = "";
                }
                model.set('ship_to_cd', ship_to_cd);
                model.set('ship_to_brnch_cd', ship_to_brnch_cd);
                model.set('zip_no', this.ui.zip_no.val());
                model.set('address', this.ui.address.val());
                //console.log(model.getReq());

                var cond = {
                    "scr": '着用者登録',
                    "mode": 'check',
                    "cond": model.getReq()
                };

                //商品詳細入力へ
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
                                    location.href = './wearer_order.html';
                                }
                            });
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
                            // JavaScript モーダルで表示
                            $('#myModal').modal('show'); //追加
                            //メッセージの修正
                            document.getElementById("confirm_txt").innerHTML=App.wearer_delete_msg; //追加　このメッセージはapp.jsで定義
                            $("#btn_ok").off();
                            $("#btn_ok").on('click',function() { //追加
                                hideModal();
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
                                            window.sessionStorage.setItem('referrer', 'wearer_input_delete');
                                            window.sessionStorage.removeItem('wearer_input_ref');
                                            location.href = './wearer_input.html';
                                        }
                                    }
                                });
                            });
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
                        // JavaScript モーダルで表示
                        $('#myModalAlert').modal(); //追加
                        //メッセージの修正
                        document.getElementById("alert_txt").innerHTML=App.apply_msg;
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
