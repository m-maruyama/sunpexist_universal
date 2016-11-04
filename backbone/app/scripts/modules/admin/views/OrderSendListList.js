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
              this.listenTo(this.collection, 'parsed', function(res){
      					this.options.pagerModel.set(res.page);
      				});
            },
            events: {
              "click .sort": function (e) {
                  e.preventDefault();
                  var that = this;
                  if (sort_key == e.target.id) {
                      if (order == 'asc') {
                          order = 'desc';
                      } else {
                          order = 'asc';
                      }
                  } else {
                      order = 'asc';
                  }
                  sort_key = e.target.id;
                  this.triggerMethod('sort', e.target.id, order);
              },
              'change @ui.checkall': function (e) {
                e.preventDefault();
                if ($("#checkall").prop('checked')) {
                  $("tbody .order_check.snd_kbn0").prop("checked", true);
                } else {
                  $("tbody .order_check.snd_kbn0").prop("checked", false);
                }
              },
              'click @ui.updBtn': function (e) {
                e.preventDefault();
                var that = this;

                var we_array = [];
                $('[name="order_check"]:checked').each(function () {
                    we_array.push($(this).val());
                });
                window.sessionStorage.setItem('we_array', we_array);
                var we_val = window.sessionStorage.getItem('we_array').split(',');
                  if (!we_val[0]) {
                  alert('発注送信を行う場合は選択欄の何れかにチェックを入れてください。');
                  return;
                }

                if (window.confirm('選択されているデータの発注送信を行います。\nよろしいですか？')) {
                  $.blockUI({message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" />発注送信処理中...</p>'});
                  var we_length = we_val.length;
                  var item = new Object();
                  var data = new Object();
                  for (var i = 0; i < we_length; i++) {
                    item[i] = new Object();
                    item[i] = we_val[i].split(':');
                    data[i] = {
                      "corporate_id": item[i][0],
                      "rntl_cont_no": item[i][1],
                      "werer_cd": item[i][2],
                      "rntl_sect_cd": item[i][3],
                      "job_type_cd": item[i][4],
                      "order_sts_kbn": item[i][5],
                      "order_reason_kbn": item[i][6],
                      "wst_order_req_no": item[i][7],
                      "order_req_no": item[i][8],
                      "rtn_order_req_no": item[i][9]
                    };
                  }
                  //console.log(data);
                  var modelForUpdate = this.model;
                  modelForUpdate.url = App.api.OS0011;
                  var cond = {
                      "scr": '発注送信処理-発注送信',
                      "log_type": '2',
                      "data": data
                  };
                  modelForUpdate.fetchMx({
                    data: cond,
                    success: function (res) {
                      var res_list = res.attributes;
                      sessionStorage.clear();
                      if (res_list["error_code"] == "0") {
                        that.triggerMethod('reload');
                        $.unblockUI();
                      } else {
                        $.unblockUI();
                        alert("更新処理中にエラーが発生しました。");
                      }
                    }
                  });
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
