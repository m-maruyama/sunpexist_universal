define([
  'app',
  '../Templates',
  'backbone.stickit',
  'bootstrap-datetimepicker',
  '../behaviors/Alerts',
  '../controllers/ImportCsv',
  'typeahead',
  'blockUI',
  'bloodhound'
], function (App) {
  'use strict';
  App.module('Admin.Views', function (Views, App, Backbone, Marionette, $, _) {
    Views.ImportCsvCondition = Marionette.LayoutView.extend({
      template: App.Admin.Templates.importCsvCondition,
      model: new Backbone.Model(),
      initialize : function(options) {
          this.model = options.model;
      },
      behaviors: {
        "Alerts": {
          behaviorClass: App.Admin.Behaviors.Alerts
        }
      },
      regions: {
        "agreement_no": ".agreement_no",
      },
      ui: {
        'agreement_no': '#agreement_no',
        'csv_form': '#csv_form',
        'import_csv': '.import_csv',
        'file_input': '#file_input',
        'sample_download': '#sample_download'
      },
      onShow: function () {
        var that = this;
        $("#btn_ok").off();
        // インラインフレーム内の読み込み完了を監視するコンストラクタ
        function IFrameElementObserverContentLoaded (iframe,callback){
          // 初期化
          var handler;
          var iframe_window = iframe.contentWindow;
          if(iframe.addEventListener){
            handler = function (e){
              callback();
            };
            iframe.addEventListener("load" , handler);
          } else if(iframe.attachEvent) {
            handler = function (e){
              try {
                if(iframe_window.document.readyState === "complete") {
                  callback();
                }
              }catch(e){

              }
            };
            iframe.attachEvent("onreadystatechange" , handler);
          }
          // 開放する
          this.release = function(){
            if(!handler) return;
            if(iframe.removeEventListener){
              iframe.removeEventListener("load", handler);
            }else if(iframe.detachEvent){
              iframe.detachEvent("onreadystatechange", handler);
            }
            handler = null;
          };
        }
        // エレメントの内容を文字列として取得する関数
        function ElementGetTextContent(element){
          if(element.textContent !== undefined){
            return element.textContent;
          }
          if(element.innerText !== undefined){
            return element.innerText;
          }
          return "";
        }
        // 各要素を取得する
        // "my_form" という ID 属性のエレメントを取得する
        var form = document.getElementById("csv");
        // "my_iframe" という ID 属性のエレメントを取得する
        var iframe = document.getElementById("my_iframe");
        // フォームの設定を変更する
        // アクセス先を変更する
        form.action = "/import_csv";
        // ターゲットウィンドウを設定する
        form.target = "form_response";
        // サブミット直前に実行されるイベント
        form.onsubmit = function (e) {
          // インラインフレーム内の読み込み完了を監視する
          var observer = new IFrameElementObserverContentLoaded (iframe , function(){
            // 監視を終了する（ここでは１回だけ監視する）
            observer.release();
            observer = null;
            // インラインフレーム内のコンテンツを取得する
            try{
              //console.log("success");
              //window.location.reload() ;
              // インラインフレーム内の Window オブジェクトを取得する
              var iframe_window = iframe.contentWindow;
              // Document オブジェクトを取得する
              var iframe_document = iframe_window.document;
              // HTMLHtmlElement オブジェクトを取得する
              var html_element = iframe_document.documentElement;
              // 自身を含む HTML 文字列を取得する
              var str_html = html_element.outerHTML;
              // エレメントの内容をテキストとして取得する
              var str_text = ElementGetTextContent(html_element);
              // 処理結果内容を取得(JSONデコード)
              var response = JSON.parse(str_text);

              if (response["error_code"] == "1") {
                // 処理結果=異常終了の場合
                $('.errorMessage').css('display', '');
                that.triggerMethod('showAlerts', response["errors"].slice(0,20));
              } else {
                $('#fake_input_file').val('');
                $('#ImportModal').modal();
                document.getElementById("confirm_txt").innerHTML=App.import_csv_complete_msg;//一括データ取込みの処理が正常に完了しました。
                $("#btn_ok").off();
                $("#btn_ok").on('click',function() {
                  location.href = "importCsv.html";
                });
              }
              $.unblockUI();
            }catch(e){
              $('#ImportModal_alert').modal();
              document.getElementById("alert_txt").innerHTML=App.import_csv_import_error_msg;//予期せぬエラーが発生しました。
              $.unblockUI();
            }
          });
        };
      },
      events: {
        'click @ui.sample_download': function() {
          var that = this;

          // サンプルダウンロード
          var data = {
            "dl_type": "1"
          };
          var cond = {
            "scr": '一括データ取込-サンプルダウンロード',
            "log_type": '1',
            "data": data
          };
          var form = $('<form action="' + App.api.DL0020 + '" method="post"></form>');
          var data = $('<input type="hidden" name="data" />');
          data.val(JSON.stringify(cond));
          form.append(data);
          $('body').append(form);
          form.submit();
          data.remove();
          form.remove();
          form=null;
          return;
        },
        'click @ui.import_csv': function() {
          var that = this;
          if ($("#file_input").prop("files")[0]) {
                    // 更新可否チェック
  					var modelForUpdate = this.model;
  					modelForUpdate.url = App.api.CM0130;
  					var cond = {
  						"scr": '一括データ取込-更新可否チェック',
  						"log_type": '1',
                        "update_skip_flg": 'importCsv'
  					};
  					modelForUpdate.fetchMx({
  						data:cond,
  						success:function(res){
  							var res_val = res.attributes;
                            if (!res_val["chk_flg"]) {
                                $('#ImportModal').modal();
                                document.getElementById("confirm_txt").innerHTML=res_val["error_msg"];// 更新可否フラグ=更新不可の場合はアラートメッセージ表示
                            } else {
                                $('#ImportModal').modal();
                                document.getElementById("confirm_txt").innerHTML='データ量により、処理に時間がかかる場合があります。\n' + $("#file_input").prop("files")[0].name + 'を取り込んでもよろしいですか？';
                                $("#btn_ok").off();
                                $("#btn_ok").on('click',function() {
                                  console.log('aaa');
                                hideModal();
                                $.blockUI({message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /><br/> データ取込み中です。<br/>完了するまではこのままでお待ちください...</p>'});
                                $('#csv').submit();
                              });
                            }
                        }
  					});
          } else {
              $('#ImportModal').modal();
              document.getElementById("confirm_txt").innerHTML=App.import_csv_no_choose_file_msg;//ファイルを選択してください。
          }
        }
      }
    });
  });
});
