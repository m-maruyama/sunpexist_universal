define([
    'app',
    '../Templates',
    'blockUI',
    './OrderSendListItem'
], function (App) {
    'use strict';
    App.module('Admin.Views', function (Views, App, Backbone, Marionette, $, _) {
        var order = 'asc';
        var sort_key = 'order_req_no';
        Views.OrderSendListList = Marionette.CompositeView.extend({
            model: new Backbone.Model(),
            template: App.Admin.Templates.orderSendListList,
            emptyView: Backbone.Marionette.ItemView.extend({
                tagName: "tr",
                template: App.Admin.Templates.orderSendEmpty,
            }),
            childView: Views.OrderSendListItem,
            childViewContainer: "tbody",
            ui: {
                "updBtn": '.updBtn',
                "checkall": '#checkall',
            },
            onRender: function () {
                //	this.listenTo(this.collection, 'parsed', function(res){
                //		this.options.pagerModel.set(res.page);
                //	});
            },
            events: {
                "click .sort": function (e) {
                    e.preventDefault();
                    var that = this;
                    //同じソートキーの場合は昇順降順切り替え
                    if (sort_key == e.target.id) {
                        if (order == 'asc') {
                            order = 'desc';
                        } else {
                            order = 'asc';
                        }
                    } else {
                        //ソートキーが変更された場合は昇順
                        order = 'asc';
                    }
                    sort_key = e.target.id;
                    this.triggerMethod('sort', e.target.id, order);
                },
                'change @ui.checkall': function (e) {
                    e.preventDefault();

                    if ($("tbody .order_check.snd_kbn0").prop('checked')) {
                        $("tbody .order_check.snd_kbn0").prop("checked", false);
                    }
                    else {
                        $("tbody .order_check.snd_kbn0").prop("checked", true);
                    }


                },
                'click @ui.updBtn': function (e) {
                    e.preventDefault();
                    //var model = new this.collection.model();

                    if (window.confirm('発注送信をしますか？')) {

                        var we_array = [];
                        $('[name="order_check"]:checked').each(function () {
                            we_array.push($(this).val());
                        });
                        window.sessionStorage.setItem('we_array', we_array);
                        var we_val = window.sessionStorage.getItem('we_array').split(',');

                        if (!we_val[0]) {
                            alert('チェックボックスが選択されていません。');
                            return;
                        }

                        //var we_vals = we_val.split(',');
                        var we_length = we_val.length;
                        var item = new Object();
                        var data = new Object();

                        for (var i = 0; i < we_length; i++) {
                            item[i] = new Object();
                            item[i] = we_val[i].split(':');
                            data[i] = {
                                "corporate_id": item[i][0],//企業id
                                "werer_cd": item[i][1],//着用者コード
                                "rntl_cont_no": item[i][2],//レンタル企業no
                                "job_type_cd": item[i][3],
                                "order_req_no": item[i][4],
                            };
                        }

                        var that = this;
                       // console.log(that);
                       // console.log(OrderSendListList);
                        var modelForUpdate = this.model;
                        modelForUpdate.url = App.api.OC0010;
                        var cond = {
                            "scr": '発注送信',
                            "data": data
                        };
                        modelForUpdate.fetchMx({
                            data: cond,
                            success: function (res) {
                                var errors = res.get('errors');
                                if (errors) {
                                    var errorMessages = errors.map(function (v) {
                                        return v.error_message;
                                    });
                                    this.triggerMethod('showAlerts', errorMessages);
                                }
                                sessionStorage.clear();
                                that.triggerMethod('reload');
                            }
                        });
                    }
                    else {
                        console.log('no');
                        return;
                    }
                }
            },
            fetch: function (orderSendListConditionModel) {
                var cond = {
                    "scr": '検索',
                    "page": this.options.pagerModel.getPageRequest(),
                    "cond": orderSendListConditionModel.getReq()
                };
                $.blockUI({message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>'});
                this.collection.fetchMx({
                    data: cond,
                    success: function (model) {
                        $.unblockUI();
                    },
                    complete: function (res) {
                        $.unblockUI();
                    }
                });
            }
        });
    });
});
