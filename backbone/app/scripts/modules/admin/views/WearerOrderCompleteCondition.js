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
            },
            onRender: function () {
            },

            events: {
                'click @ui.next_button': function (e) {
                    var referrer = window.sessionStorage.getItem('referrer');
                    window.sessionStorage.setItem('referrer', 'wearer_order_complete');
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
                    location.href = './home.html';
                },
            },
        });
    });
});
