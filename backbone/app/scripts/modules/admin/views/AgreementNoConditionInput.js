define([
    'app',
    '../Templates',
    'backbone.stickit',
], function(App) {
    'use strict';
    App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
        Views.AgreementNoConditionInput = Marionette.ItemView.extend({
            template: App.Admin.Templates.agreementNoConditionInput,
            model: new Backbone.Model(),
            ui: {
                'agreement_no': '.agreement_no'
            },
            bindings: {
                '.agreement_no': 'agreement_no'
            },
            onShow: function() {
                var that = this;
                var modelForUpdate = this.model;
                modelForUpdate.url = App.api.CM0061;
                if(window.sessionStorage.getItem('referrer')=='wearer_search'||
                    window.sessionStorage.getItem('referrer')=='wearer_order'||
                    window.sessionStorage.getItem('referrer')=='wearer_order_search'){
                    var referrer = 1;
                }else{
                    var referrer = -1;

                }
                var cond = {
                    "scr": '契約No',
                    "referrer" : referrer
                };
                modelForUpdate.fetchMx({
                    data:cond,
                    success:function(res){
                        var errors = res.get('errors');
                        if(errors) {
                            var errorMessages = errors.map(function(v){
                                return v.error_message;
                            });
                            that.triggerMethod('showAlerts', errorMessages);
                        }
                        that.render();
                        var res_list = res.attributes;
                        if(res_list['rntl_cont_no']&&(referrer > -1)){
                            $('#agreement_no').prop("disabled", true);
                            that.triggerMethod('input_form', res_list['rntl_cont_no']);
                        }
                    }
                });
            },
            events: {
            }
        });
    });
});
