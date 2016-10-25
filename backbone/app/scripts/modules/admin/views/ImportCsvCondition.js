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
            },

            onShow: function () {

                    // ------------------------------------------------------------
                    // インラインフレーム内の読み込み完了を監視するコンストラクタ
                    // ------------------------------------------------------------
                    function IFrameElementObserverContentLoaded (iframe,callback){

                        // ------------------------------------------------------------
                        // 初期化
                        // ------------------------------------------------------------
                        var handler;
                        var iframe_window = iframe.contentWindow;
                        if(iframe.addEventListener){
                            handler = function (e){
                                callback();
                            };
                            iframe.addEventListener("load" , handler);
                        }else if(iframe.attachEvent){
                            handler = function (e){
                                try {
                                    if(iframe_window.document.readyState === "complete"){
                                        callback();
                                    }
                                }catch(e){

                                }
                            };
                            iframe.attachEvent("onreadystatechange" , handler);
                        }

                        // ------------------------------------------------------------
                        // 開放する
                        // ------------------------------------------------------------
                        this.release = function(){
                            if(!handler) return;
                            if(iframe.removeEventListener){
                                iframe.removeEventListener("load" , handler);
                            }else if(iframe.detachEvent){
                                iframe.detachEvent("onreadystatechange" , handler);
                            }
                            handler = null;
                        };
                    }

                // ------------------------------------------------------------
                // エレメントの内容を文字列として取得する関数
                // ------------------------------------------------------------
                function ElementGetTextContent(element){
                    if(element.textContent !== undefined){
                        return element.textContent;
                    }
                    if(element.innerText !== undefined){
                        return element.innerText;
                    }

                    return "";
                }


                // ------------------------------------------------------------
                // 各要素を取得する
                // ------------------------------------------------------------
                // "my_form" という ID 属性のエレメントを取得する
                var form = document.getElementById("csv");

                // "my_iframe" という ID 属性のエレメントを取得する
                var iframe = document.getElementById("my_iframe");

                // ------------------------------------------------------------
                // フォームの設定を変更する
                // ------------------------------------------------------------
                // アクセス先を変更する
                form.action = "/import_csv";

                // ターゲットウィンドウを設定する
                form.target = "form_response";

                // ------------------------------------------------------------
                // サブミット直前に実行されるイベント
                // ------------------------------------------------------------
                form.onsubmit = function (e){

                    // ------------------------------------------------------------
                    // インラインフレーム内の読み込み完了を監視する
                    // ------------------------------------------------------------
                    var observer = new IFrameElementObserverContentLoaded (iframe , function(){

                        // ------------------------------------------------------------
                        // 監視を終了する（ここでは１回だけ監視する）
                        // ------------------------------------------------------------
                        observer.release();
                        observer = null;

                        // ------------------------------------------------------------
                        // インラインフレーム内のコンテンツを取得する
                        // ------------------------------------------------------------
                        try{

                            console.log("success");
                            window.location.reload() ;
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

                            // 出力テスト
                            //console.log(str_text);

                        }catch(e){

                            // 出力テスト（セキュリティエラーなど）
                            document.getElementById('my_iframe')[0].contentDocument.location.reload(true);
                            console.log(e);
                            //iframe.contentDocument.location.reload(true);
                        }

                    });

                };

                //-->

                //e.preventDefault();
                var that = this;

                    console.log(window.sessionStorage.getItem('status'));
                    if(window.sessionStorage.getItem('status') === 'import_start' && sessionStorage.length !== 0  ){
                    console.log('import_start');

                    var url = App.api.IM0011;
                    var postData = {
                        type : "POST",
                        data : {
                            'parms': 'error_check'
                        },
                        dataType: "json",
                        beforeSend: function(jqXHR) {
                            // falseを返すと処理を中断
                            return true;
                        },
                    };

                    // ajax送信
                    $.ajax( url, postData ).done(function( res ){
                        //console.log(res);
                        //console.log('done');

                        var errors = res['errors'];
                        if(errors) {
                            var errorMessages = new Array();
                            that.triggerMethod('showAlerts', errors.slice(0,20));
                            alert('CSVデータの登録に失敗しました。');
                            $("#open").prop("disabled", false);
                            $("#import_csv").prop("disabled", false);
                            window.sessionStorage.removeItem('status');
                            $.unblockUI();
                        } else {
                            alert('CSVデータを登録しました。');
                            $("#open").prop("disabled", false);
                            $("#import_csv").prop("disabled", false);
                            window.sessionStorage.removeItem('status');
                            $.unblockUI();
                        }
                    });
                    //that.render();
                }else{
                    $("#open").prop("disabled", false);
                    $("#import_csv").prop("disabled", false);
                }

            },
            events: {
                'click @ui.import_csv': function() {
                    //e.preventDefault();
                    if(confirm($("#file_input").prop("files")[0].name + 'を取り込んでもよろしいですか？')) {
                        $.blockUI({message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>'});
                        $('#csv').submit();
                    }
                    //
                    }

                    /*
                    window.sessionStorage.setItem('status', 'import_start');
                    this.ui.import_csv.parents('form').attr('action', this.ui.import_csv.data('action'));
                    $('#csv').submit();
                       */
                    /*
                    console.log($("#file_input").prop("files")[0]);
                    var cond_map = new Object();
                    cond_map["agreement_no"] = $("select[name='agreement_no']").val();

                    var fd = new FormData();
                    if ( $("#file_input").val() !== '' ) {
                        fd.append( "file", $("#file_input").prop("files")[0] );
                    }
                    if ( $("#file_input").val() !== '' ) {
                        fd.append( "file", $("#file_input").prop("files")[0] );
                    }

                    var msg = "データ量により、ダウンロード処理に時間がかかる可能性があります。ダウンロードを実施してよろしいですか？";
                    if (window.confirm(msg)) {
                        var cond = {
                            "scr": 'CSVダウンロード',
                            "fd": $("#file_input").prop("files")[0],
                            "cond": cond_map,
                        };
                        var file = {
                            "fd": fd
                        };


                        var form = $('<form action="' + App.api.IM0010 + '" method="post"></form>');
                        var data = $('<input type="hidden" name="data" />');
                        var datafile = $('<input type="hidden" id="send_file" name="file" />');
                        data.val(JSON.stringify(cond));
                        //
                        datafile.val(JSON.stringify(file));

                        form.append(data);
                        form.append(datafile);

                        $('body').append(form);


                        form.submit();
                        data.remove();
                        form.remove();
                        form=null;
                        return;
                    }
                    */
                //}
            },


            /*'click @ui.import_csv': function (e) {

                    /*
                     if(!$("#file_input").prop("files")[0]){
                     this.triggerMethod('showAlerts', new Array('取込ファイル名が未入力です。'));
                     return;
                     }
                     var fileName = ($("#file_input").prop("files")[0]);
                     var type = fileName.name.split('.');
                     var fileExt = type[type.length - 1].toLowerCase();
                     if (( fileExt == 'csv') || (fileExt == 'xlsx' )) {

                     $("#open").prop("disabled", true);
                     $("#import_csv").prop("disabled", true);

                     if(confirm($("#file_input").prop("files")[0].name + 'を取り込んでもよろしいですか？')){
                     $.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });

                     var fd = new FormData();
                     var agreement_no = $("#agreement_no").val();

                     if ( $("#file_input").val() !== '' ) {
                     fd.append( "file", $("#file_input").prop("files")[0] );
                     fd.append( "agreement_no", agreement_no);
                     }
                     var url = App.api.IM0010;
                     var postData = {
                     type : "POST",
                     data : fd,
                     processData : false,
                     contentType : false,
                     dataType: "json",
                     };
                     $.ajaxSetup({
                     timeout: 1000000,
                     });
                     // ajax送信
                     $.ajax( url, postData ).done(function( res ){
                     console.log(res);
                     console.log('done');

                     var errors = res['errors'];
                     if(errors) {
                     var errorMessages = new Array();
                     that.triggerMethod('showAlerts', errors.slice(0,20));
                     alert('CSVデータの登録に失敗しました。');
                     $("#open").prop("disabled", false);
                     $("#import_csv").prop("disabled", false);
                     $.unblockUI();
                     } else {
                     alert('CSVデータを登録しました。');
                     $("#open").prop("disabled", false);
                     $("#import_csv").prop("disabled", false);
                     $.unblockUI();
                     }
                     });
                     //that.render();
                     }else{
                     $("#open").prop("disabled", false);
                     $("#import_csv").prop("disabled", false);
                     }

                     }else{
                     alert('拡張子がcsvまたはxlsxではありません。');


                     }






                     }//click ui.import_csv
                     */
                //event                }

        });
    });
});