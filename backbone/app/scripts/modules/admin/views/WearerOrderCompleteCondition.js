define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
    'use strict';
    App.module('Admin.Views', function (Views, App, Backbone, Marionette, $, _) {
        Views.WearerOrderCompleteCondition = Marionette.LayoutView.extend({
            template: App.Admin.Templates.wearerOrderCompleteCondition,

            ui: {
                "next_button": "#next_button",
                "home_button": "#home_button",
                'returnSlipDownload': '.returnSlipDownload'
            },
            onRender: function () {

            },
            onShow: function () {
                if(window.sessionStorage.getItem('param')){
                    $('.returnSlipDownload').css('display', 'block');
                    $('.returnSlipDownload').val(window.sessionStorage.getItem('param'));
                };
            },

            events: {
                'click @ui.next_button': function (e) {
                    var referrer = window.sessionStorage.getItem('referrer');
                    window.sessionStorage.setItem('referrer', 'wearer_order_complete');
                    window.sessionStorage.removeItem('referrer_complete');
                    if(referrer=='wearer_end_order'){
                        location.href = './wearer_end.html';
                    }else if(referrer=='wearer_order'){
                        location.href = './wearer_input.html';
                    }else if(referrer=='wearer_end_order_err'){
                        window.sessionStorage.getItem('error_msg');
                        location.href = './wearer_end.html';
                    }
                },
                'click @ui.home_button': function (e) {
                    window.sessionStorage.setItem('referrer', 'wearer_order_complete');
                    window.sessionStorage.removeItem('referrer_complete');
                    location.href = './home.html';
                },
                'click @ui.returnSlipDownload': function(e){
                    e.preventDefault();
                    var pdf_vals = e.target.value;

                    var pdf_val = pdf_vals.split(':');
                    var printData = new Object();
                    printData["rntl_cont_no"] = pdf_val[0];
                    printData["order_req_no"] = pdf_val[1];

                    var msg = "データ量により、ダウンロード処理に時間がかかる可能性があります。ダウンロードを実施してよろしいですか？";
                    if (window.confirm(msg)) {
                        var cond = {
                            "scr": 'PDFダウンロード',
                            "cond": printData
                        };
                        var form = $('<form action="' + App.api.PR0012 + '" method="post"></form>');
                        var data = $('<input type="hidden" name="data" />');
                        data.val(JSON.stringify(cond));
                        form.append(data);
                        $('body').append(form);
                        form.submit();
                        data.remove();
                        form.remove();
                        form = null;
                        return;
                    }
                }
            },
        });
    });
});
