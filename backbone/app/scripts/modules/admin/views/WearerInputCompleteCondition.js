define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
    'use strict';
    App.module('Admin.Views', function (Views, App, Backbone, Marionette, $, _) {
        Views.WearerInputCompleteCondition = Marionette.LayoutView.extend({
            template: App.Admin.Templates.wearerInputCompleteCondition,

            ui: {
                "next_button": "#next_button",
                "home_button": "#home_button",
            },
            onRender: function () {
                if(window.sessionStorage.getItem('referrer')=='wearer_complete_reload'){
                    //リロード時
                    window.sessionStorage.removeItem('referrer', 'wearer_complete_reload');
                    location.href = './wearer_input.html';
                }else{
                    //初期表示時
                    window.sessionStorage.setItem('referrer', 'wearer_complete_reload');
                }

            },

            events: {
                'click @ui.next_button': function (e) {
                    window.sessionStorage.setItem('referrer', 'wearer_complete');
                    location.href = './wearer_input.html';
                },
                'click @ui.home_button': function (e) {
                    window.sessionStorage.setItem('referrer', 'wearer_complete');
                    location.href = './home.html';
                },
            },
        });
    });
});
