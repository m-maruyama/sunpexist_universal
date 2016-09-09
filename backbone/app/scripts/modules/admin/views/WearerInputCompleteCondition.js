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
            },

            events: {
                'click @ui.next_button': function (e) {
                    location.href = './wearer_input.html';
                },
                'click @ui.home_button': function (e) {
                    location.href = './home.html';
                },
            },
        });
    });
});
