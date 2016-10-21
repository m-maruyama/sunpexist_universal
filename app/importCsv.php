<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Http\Response;


/**
 * CSV取込
 */
$app->post('/import_csv', function () use ($app) {


    $query_list = array();
    $error_list = array();
    $auth = $app->session->get("auth");
    ChromePhp::log($auth);

    //更新可否チェック（更新可否チェック仕様書）

    //  入力された内容を元に、着用者基本マスタトラン、着用者商品マスタトラン、発注情報トランに登録を行う。
    //--- 検索条件 ---//
    //  アカウントマスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MAccount.corporate_id = '".$auth['corporate_id']."'");

    //  アカウントマスタ．ユーザーID　＝　ログインしているアカウントのユーザーID　AND
    array_push($query_list, "MAccount.user_id = '".$auth['user_id']."'");
    //　契約マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MContract.corporate_id = '".$auth['corporate_id']."'");
    //　契約マスタ．レンタル契約フラグ　＝　契約対象 AND
    array_push($query_list, "MContract.rntl_cont_flg = '1'");
    array_push($query_list, "MContractResource.rntl_cont_no = '".$_POST["agreement_no"]."'");
    //  契約リソースマスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MContractResource.corporate_id = '".$auth['corporate_id']."'");



    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    ChromePhp::log($query);
    ChromePhp::log($query_list);

    //--- クエリー実行・取得 ---//
    $results = MContract::query()
        ->where($query)
        ->columns(array('MContractResource.*'))
        ->leftJoin('MContractResource','MContract.corporate_id = MContractResource.corporate_id')
        ->join('MAccount','MAccount.accnt_no = MContractResource.accnt_no')
        ->execute();

    //ChromePhp::log($results);
    //exit;
    if($results[0]->update_ok_flg == '0'){
        array_push($error_list,'こちらの契約リソースは更新出来ません。');
//        $json_list['errors'] = $error_list;
        $app->session->set('error', $error_list);

        return;
    }
    //汎用コードマスタから更新不可時間を取得
    // 汎用コードマスタ．分類コード　＝　更新不可時間

    //--- クエリー実行・取得 ---//
    /*//本番はここを有効
    $m_gencode_results = MGencode::query()
        ->where("cls_cd = '015'")
        ->columns('*')
        ->execute();
    foreach ($m_gencode_results as $m_gencode_result) {
        if($m_gencode_result->gen_cd =='1'){
            //更新不可開始時間
            $start = $m_gencode_result->gen_name;
        }elseif($m_gencode_result->gen_cd =='2'){
            //経過時間
            $hour = $m_gencode_result->gen_name;

        }
    }
    $now_datetime = date("YmdHis");
    $now_date = date("Ymd");
    $start_datetime = $now_date.$start;
    $end_datetime = date("YmdHis", strtotime($start_datetime." + ".$hour." hour"));
    if(strtotime($start_datetime) <= strtotime($now_datetime)||strtotime($now_datetime) >= strtotime($end_datetime)){
        array_push($error_list,'現在の時間は更新出来ません。');
        //$json_list['errors'] = $error_list;
        $app->session->set('error', $error_list);
        //echo json_encode($json_list);
        return;
    }*///本番はここを有効
    //ChromePhp::log($_POST);//契約ナンバーはここに入ってるよ
   //
    // ChromePhp::log($_FILES["file"]);//アップロードファイルはここに入って流よ



    //社員番号、着用者番号のセッションを削除
    $app->session->remove("cster_emply_cd");
    $app->session->remove("cster_emply_cd1");
    $app->session->remove("werer_cd");
    $app->session->remove("error");

    //画面で選択した
    $agreement_no = $_POST["agreement_no"];

    $json_list = array();
    $error_list = array();
    $getFileExt = new SplFileInfo($_FILES['file']['name']);

    if ($getFileExt->getExtension() == 'csv') {//ここからcsv前処理
        try {
            $file = file($_FILES['file']['tmp_name']);
            mb_convert_variables("UTF-8", "SJIS-win", $file);
            $chk_file = $file;
            unset($chk_file[0]); //チェック時はヘッダーを無視する
            //ChromePhp::log($chk_file);

        } catch (Exception $e) {
            array_push($error_list, '取り込んだファイルの形式が不正です。');
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            return true;
        }

        $new_list = array();
        $no_chk_list = array();
        $no_list = array();
        $auth = $app->session->get("auth");

        $line_no = 1;
        $line_cnt = 1; //行数

        foreach ($chk_file as $line) {

            //csvの１行を配列に変換する
            $line_list = str_getcsv($line, ',', '"');

            // 項目数チェック: 行単位の項目数が、仕様通りの項目数(15)かをチェックする。
            //ChromePhp::log($line_list);

            if (count($line_list) != 15) {
                $cnt_list = array();
                //項目数が不正な場合、エラーメッセージを配列に格納
                array_push($error_list, $line_cnt . '行目の項目数が不正です');
                $line_cnt++;
                continue;
            }
            //必須チェック
            if (empty($line_list[0])) {
                array_push($error_list, $line_cnt . '行目の社員番号を入力してください。');
            }
            if (empty($line_list[1])) {
                array_push($error_list, $line_cnt . '行目の着用者名(漢字)を入力してください。');
            }
            if (empty($line_list[2])) {
                array_push($error_list, $line_cnt . '行目の着用者名(カナ)を入力してください。');
            }
            if ($line_list[3] == '') {
                array_push($error_list, $line_cnt . '行目の性別区分を入力してください。');
            }
            if (empty($line_list[4])) {
                array_push($error_list, $line_cnt . '行目の支店コードを入力してください。');
            }
            if (empty($line_list[5])) {
                array_push($error_list, $line_cnt . '行目の貸与パターンを入力してください。');
            }
            if ($line_list[7] == '') {
                array_push($error_list, $line_cnt . '行目の発注区分を入力してください。');
            }
            //発注区分が貸与、異動の場合に必須
            if ($line_list[7] == '1' || $line_list[7] == '5') {
                if (empty($line_list[6])) {
                    array_push($error_list, $line_cnt . '行目の着用開始日を入力してください。');
                }
                if (empty($line_list[8])) {
                    array_push($error_list, $line_cnt . '行目の発注コードを入力してください。');
                }
                if (empty($line_list[9])) {
                    array_push($error_list, $line_cnt . '行目のサイズコードを入力してください。');
                }
                if (empty($line_list[10])) {
                    array_push($error_list, $line_cnt . '行目の色コードを入力してください。');
                }
                if (empty($line_list[11])) {
                    array_push($error_list, $line_cnt . '行目の数量を入力してください。');
                }
                if (empty($line_list[13])) {
                    array_push($error_list, $line_cnt . '行目の理由区分を入力してください。');
                }
            } elseif ($line_list[7] == '0') {

            }
            //日本語文字数チェック
            //着用者漢字
            if (byte_cnv($line_list[1]) > 100) {
                array_push($error_list, '着用者名(漢字)の文字数が多すぎます。');
            }
            //着用者カナ
            if (byte_cnv($line_list[2]) > 100) {
                array_push($error_list, '着用者名(カナ)の文字数が多すぎます。');
            }
            //伝言
            if (byte_cnv($line_list[14]) > 100) {
                array_push($error_list, '伝言欄の文字数が多すぎます。');
            }

            //フォーマットチェック: 行単位の各項目のフォーマット形式が、それぞれ仕様通りのフォーマットであるかチェックする。
            $error_list = chk_format($error_list, $line_list, $line_cnt);
            $line_cnt++;

            array_push($line_list, $line_no++);
            array_push($new_list, $line_list);
        }//ここからまでcsv

    } elseif ($getFileExt->getExtension() == 'xlsx') {//ここからエクセル前処理

        $line_cnt = 1; //行数
        $new_list = array();
        $no_chk_list = array();
        $no_list = array(); //よろず発注Noリスト
        $auth = $app->session->get("auth");

        // init excel work book as xlsx
        $useXlsxFormat = true;
        setlocale(LC_ALL, 'ja_JP.UTF-8');

        $xlBook = new ExcelBook('Taichi Nakamura', 'linux-e4d4157290acad17020f2f384ei1c3od', $useXlsxFormat);
        $xlBook->setLocale('ja_JP.UTF-8');

        // add sheet to work book
        $xlBook->loadfile($_FILES['file']['tmp_name']);
        //エクセルの一番左のシートを取得
        $sheet = $xlBook->getSheet(0);
        //エクセルの行数を取得
        $lastRow = $sheet->lastRow();
        //ChromePhp::log($lastRow);
        //配列を初期化
        $new_list = array();
        $line_no = 1;//行no追加

        //存在する行数の最初の行を除き、連想配列にする
        for ($i = 1; $i < $lastRow; $i++) {
            $line_list = $sheet->readRow($i, 0);

            if (count($line_list) != 15) {
                $cnt_list = array();
                //項目数が不正な場合、エラーメッセージを配列に格納
                array_push($error_list, $line_cnt . '行目の項目数が不正です');
                $line_cnt++;
                continue;
            }
            //必須チェック
            if (empty($line_list[0])) {
                array_push($error_list, $line_cnt . '行目の社員番号を入力してください。');
            }
            if (empty($line_list[1])) {
                array_push($error_list, $line_cnt . '行目の着用者名(漢字)を入力してください。');
            }
            if (empty($line_list[2])) {
                array_push($error_list, $line_cnt . '行目の着用者名(カナ)を入力してください。');
            }
            if ($line_list[3] == '') {
                array_push($error_list, $line_cnt . '行目の性別区分を入力してください。');
            }
            if (empty($line_list[4])) {
                array_push($error_list, $line_cnt . '行目の支店コードを入力してください。');
            }
            if (empty($line_list[5])) {
                array_push($error_list, $line_cnt . '行目の貸与パターンを入力してください。');
            }
            if ($line_list[7] == '') {
                array_push($error_list, $line_cnt . '行目の発注区分を入力してください。');
            }
            //発注区分が貸与、異動の場合に必須
            if ($line_list[7] == '1' || $line_list[7] == '5') {
                if (empty($line_list[6])) {
                    array_push($error_list, $line_cnt . '行目の着用開始日を入力してください。');
                }
                if (empty($line_list[8])) {
                    array_push($error_list, $line_cnt . '行目の発注コードを入力してください。');
                }
                if (empty($line_list[9])) {
                    array_push($error_list, $line_cnt . '行目のサイズコードを入力してください。');
                }
                if (empty($line_list[10])) {
                    array_push($error_list, $line_cnt . '行目の色コードを入力してください。');
                }
                if (empty($line_list[11])) {
                    array_push($error_list, $line_cnt . '行目の数量を入力してください。');
                }
                if (empty($line_list[13])) {
                    array_push($error_list, $line_cnt . '行目の理由区分を入力してください。');
                }
            } elseif ($line_list[7] == '0') {

            }
            //日本語文字数チェック
            //着用者漢字
            if (byte_cnv($line_list[1]) > 100) {
                array_push($error_list, '着用者名(漢字)の文字数が多すぎます。');
            }
            //着用者カナ
            if (byte_cnv($line_list[2]) > 100) {
                array_push($error_list, '着用者名(カナ)の文字数が多すぎます。');
            }
            //伝言
            if (byte_cnv($line_list[14]) > 100) {
                array_push($error_list, '伝言欄の文字数が多すぎます。');
            }

            //フォーマットチェック: 行単位の各項目のフォーマット形式が、それぞれ仕様通りのフォーマットであるかチェックする。
            $error_list = chk_format($error_list, $line_list, $line_cnt);
            $line_cnt++;

            array_push($line_list, $line_no++);
            array_push($new_list, $line_list);
        }
    }//ここまでインポートジョブ投入前エクセルのバリデーション

    //ここからcsvとxlsxの共通処理
    try {
        // 登録処理
        if (empty($error_list)) {

            //新規データと既存データをよろず発注Noで並べ替え
            array_multisort($new_list, array_column($new_list, 0));

            $before_no = '';
            $no_line = 1;

            $job_no_check = $auth["corporate_id"] . $auth["user_id"];

            //削除するインポートテーブルの条件
            $i_log = TImportJob::find(array(
                'conditions' => "job_no = '$job_no_check'"
            ));
            foreach ($i_log as $del) {
                if ($del->delete() == false) {
                    $json_list['errors'] = '発注情報が登録出来ませんでした' . $e;
                    echo json_encode($json_list);
                    return true;
                }
            }//削除ここまで

            $auth = $app->session->get("auth");
            $cnt = 1;
            $order_no_list = array();


            $start = microtime(true);


            $transaction = $app->transactionManager->get();
            $i = 1;
            $no_line = 1;
            foreach ($new_list as $line_new) {
                $t_i_job = new TImportJob();
                $t_i_job->setTransaction($transaction);

                //同じよろず発注Noが、インポートログテーブルに存在しない場合、よろず発注No単位にインポートログテーブルへ新規登録を行う。
                //data_save($line_new, $cnt, $no_line, $auth, $order_no_list);

                $result = MWearerStd::find(array('conditions' => "cster_emply_cd = '$line_list[0]'"));
                //社員番号を着用者コードに
                //}
                $t_i_job->job_no = $auth["corporate_id"] . $auth["user_id"]; //処理番号
                $t_i_job->line_no = $line_new[15]; // 行番号
                $t_i_job->cster_emply_cd = $line_new[0]; //社員番号
                $t_i_job->werer_name = $line_new[1]; //着用者（漢字）
                $t_i_job->werer_name_kana = $line_new[2]; //着用者（カナ）
                $t_i_job->sex_kbn = $line_new[3]; //性別区分
                $t_i_job->rntl_sect_cd = $line_new[4]; //支店コード
                $t_i_job->rent_pattern_code = $line_new[5]; //貸与パターン
                $t_i_job->wear_start = $line_new[6]; //着用開始日
                $t_i_job->order_kbn = $line_new[7]; //発注区分
                $t_i_job->item_cd = $line_new[8]; //商品コード
                $t_i_job->size_cd = $line_new[9]; //サイズコード
                $t_i_job->color_cd = $line_new[10]; //色コード
                if ($line_new[7] == 5) {
                    $t_i_job->werer_cd = $result[0]->werer_cd; //着用者コード //発注区分(5)異動の場合のみ
                }
                if ($line_new[11]) {
                    $t_i_job->quantity = $line_new[11]; //数量
                }
                $t_i_job->emply_order_req_no = $line_new[12]; //お客様発注No
                $t_i_job->order_reason_kbn = $line_new[13]; //理由区分
                $t_i_job->message = $line_new[14]; //伝言欄
                $t_i_job->user_id = $auth['user_id']; //インポートユーザーID
                $t_i_job->import_time = date("Y/m/d H:i:s.sss", time()); //インポート日時
                $t_i_job->upd_user_id = $auth['user_id']; //更新ユーザー
                $t_i_job->upd_date = date("Y/m/d H:i:s.sss", time()); //更新日時
                $t_i_job->rgst_user_id = $auth['user_id']; //登録ユーザーID
                $t_i_job->rgst_date = date("Y/m/d H:i:s.sss", time()); //登録日時

                /*if ($i % 500 == 0 ){
                    ob_start();
                    //echo 'hoge';
                    ob_flush();
                    flush();
                }*/

                if ($t_i_job->create() == false) {
                    $json_list['errors'] = array('csvファイルの登録に失敗しました。');
                    $transaction->rollBack();

                    $app->session->set('error', $error_list);
                    //header("location: http://sunpex_universal_local.pm1932.jp/universal/importCsv.html");
                    //$rel = $_GET['reload'];
                    //if ($rel == 'true') {
                     //   header("Location: " . $_SERVER['PHP_SELF']);
                    //    exit();

                    //}
                    //echo json_encode($json_list);
                    //return true;
                }
                $i++;
                $no_line++;
            }


            $end = microtime(true);
            ChromePhp::log($end - $start);
        } else {
            //エラーがあったら画面にエラーメッセージ
           // ChromePhp::log("エラーがあったら画面にエラーメッセージ");

            //$json_list['errors'] = $error_list;
            //echo json_encode($json_list);
            $app->session->set('error', $error_list);
            //header("location: http://sunpex_universal_local.pm1932.jp/universal/importCsv.html");


            return;
        }


        // エラーがなければコミット
        if (!empty($error_list)) {
            //エラーがあったら画面にエラーメッセージ
            $json_list['errors'] = $error_list;
            $transaction->rollback();

            $app->session->set('error', $error_list);

            //echo json_encode($json_list);
            //return true;
        }

    } catch (Exception $e) {
        //ChromePhp::log($e);
        array_push($error_list, 'プログラム内でエラーが発生しました。(' . $e->getMessage() . ')');
        $transaction->rollBack();

        $app->session->set('error', $error_list);

        //$json_list['errors'] = $error_list;
        //echo json_encode($json_list);
        return true;
    }
    $json_list['import_job'] = 'ok';
    $transaction->commit();

    $start = microtime(true);

    $corporate_id = $auth["corporate_id"];
    //---SQLクエリー実行---//

    //社員番号マスターチェック  発注区分：着用者登録のみ、貸与の場合　条件：着用者基本マスタに同じ客先社員コードがある場合、稼働である事。
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT * FROM t_import_job WHERE order_kbn = '1' OR order_kbn = '0') AS T1 ";
    $arg_str .= "WHERE EXISTS";
    $arg_str .= "(SELECT * FROM (SELECT * FROM m_wearer_std WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no'  AND rntl_sect_cd = T1.rntl_sect_cd AND job_type_cd = T1.rent_pattern_code ) AS T2 ";
    $arg_str .= "WHERE T1.cster_emply_cd = T2.cster_emply_cd AND T2.werer_sts_kbn = '1') ";
//            $arg_str .= "OR NOT EXISTS";
//            $arg_str .= "(SELECT * FROM (SELECT * FROM m_wearer_std WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no' AND rntl_sect_cd = T1.rntl_sect_cd AND job_type_cd = T1.rent_pattern_code ) AS T2 ";
//            $arg_str .= "WHERE T1.cster_emply_cd = T2.cster_emply_cd) ";
    $arg_str .= "UNION ALL ";
    $arg_str .= "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT * FROM t_import_job WHERE order_kbn = '5') AS T1 ";
    $arg_str .= "WHERE NOT EXISTS ";
    $arg_str .= "( SELECT * FROM (SELECT * FROM m_wearer_std WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no'  AND rntl_sect_cd = T1.rntl_sect_cd AND job_type_cd = T1.rent_pattern_code) AS T2 ";
    $arg_str .= "WHERE T1.cster_emply_cd = T2.cster_emply_cd AND T2.werer_sts_kbn = '1') ";
    $arg_str .= "ORDER BY line_no ";

    //ChromePhp::log($arg_str);
    //ここからマスターチェック
    //テーブルから検索
    $t_import_job = new TImportJob();
    $results = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];

    //エラー行が見つかればエラーメッセージを表示
    if (!empty($results_cnt)) {
        $results_count = (count($results));//ページング処理２０個制限の前に数を数える
        $paginator_model = new PaginatorModel(
            array(
                'data' => $results,
                'limit' => $results_count,
                "page" => 1
            )
        );
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach ($results as $result) {
            array_push($error_list, $result->line_no . '行目の社員番号が不正です。');
        }
        $json_list['errors'] = $error_list;
    }


    //マスターチェック2 部門マスタの検索条件
    $arg_str2 = "SELECT ";
    $arg_str2 .= " * ";
    $arg_str2 .= " FROM t_import_job AS T1 ";
    $arg_str2 .= " WHERE NOT EXISTS ";
    $arg_str2 .= "(SELECT ";
    $arg_str2 .= " * ";
    $arg_str2 .= " FROM (SELECT * FROM m_section WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no') AS T2 ";
    $arg_str2 .= " WHERE T2.rntl_sect_cd = T1.rntl_sect_cd ) ";


    //$t_import_job2 = new TImportJob();
    $results2 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str2));
    $result_obj2 = (array)$results2;
    $results_cnt2 = $result_obj2["\0*\0_count"];

    //エラー行が見つかればエラーメッセージを表示
    if (!empty($results_cnt2)) {
        $results_count = (count($results2));//ページング処理２０個制限の前に数を数える
        $paginator_model = new PaginatorModel(
            array(
                'data' => $results2,
                'limit' => $results_count,
                "page" => 1
            )
        );
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach ($results as $result) {
            array_push($error_list, $result->line_no . '行目の支店コードが不正です。');
        }
        $json_list['errors'] = $error_list;
    }


    //マスターチェック3 職種マスタの検索条件
    $arg_str3 = "SELECT ";
    $arg_str3 .= " * ";
    $arg_str3 .= " FROM t_import_job AS T1 ";
    $arg_str3 .= " WHERE NOT EXISTS ";
    $arg_str3 .= "(SELECT ";
    $arg_str3 .= " * ";
    $arg_str3 .= " FROM (SELECT * FROM m_job_type WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no') AS T2 ";
    $arg_str3 .= " WHERE T2.job_type_cd = T1.rent_pattern_code ) ";

    //ChromePhp::log($arg_str3);

    $results3 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str3));
    $result_obj3 = (array)$results3;
    $results_cnt3 = $result_obj3["\0*\0_count"];

    //エラー行が見つかればエラーメッセージを表示
    if (!empty($results_cnt3)) {
        $results_count = (count($results3));//ページング処理２０個制限の前に数を数える
        $paginator_model = new PaginatorModel(
            array(
                'data' => $results3,
                'limit' => $results_count,
                "page" => 1
            )
        );

        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach ($results as $result) {
            array_push($error_list, $result->line_no . '行目の貸与パターンが不正です。');
        }
        $json_list['errors'] = $error_list;
    }

    //マスターチェック4 発注区分はフォーマットチェックで実施済み
    //マスターチェック5 商品マスタの検索条件
    $arg_str5 = "SELECT ";
    $arg_str5 .= " * ";
    $arg_str5 .= " FROM t_import_job AS T1 ";
    $arg_str5 .= " WHERE NOT EXISTS ";
    $arg_str5 .= "(SELECT ";
    $arg_str5 .= " * ";
    $arg_str5 .= " FROM (SELECT * FROM m_input_item WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no' AND job_type_cd = T1.rent_pattern_code ) AS T2 ";
    $arg_str5 .= " WHERE item_cd = T1.item_cd ) ";

    $results5 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str5));
    $result_obj5 = (array)$results5;
    $results_cnt5 = $result_obj5["\0*\0_count"];

    //エラー行が見つかればエラーメッセージを表示
    if (!empty($results_cnt5)) {
        $results_count = (count($results5));//ページング処理２０個制限の前に数を数える
        $paginator_model = new PaginatorModel(
            array(
                'data' => $results5,
                'limit' => $results_count,
                "page" => 1
            )
        );

        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach ($results as $result) {
            array_push($error_list, $result->line_no . '行目の商品コードが不正です。');
        }
        $json_list['errors'] = $error_list;
    }


    //マスターチェック6  商品マスタのカラーコード検索条件
    $arg_str6 = "SELECT ";
    $arg_str6 .= " * ";
    $arg_str6 .= " FROM t_import_job AS T1 ";
    $arg_str6 .= " WHERE NOT EXISTS ";
    $arg_str6 .= "(SELECT ";
    $arg_str6 .= " * ";
    $arg_str6 .= " FROM (SELECT * FROM m_input_item WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no' AND job_type_cd = T1.rent_pattern_code ) AS T2 ";
    $arg_str6 .= " WHERE color_cd = T1.color_cd ) ";

    $results6 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str6));
    $result_obj6 = (array)$results6;
    $results_cnt6 = $result_obj6["\0*\0_count"];

    //エラー行が見つかればエラーメッセージを表示
    if (!empty($results_cnt6)) {
        $results_count = (count($results6));//ページング処理２０個制限の前に数を数える
        $paginator_model = new PaginatorModel(
            array(
                'data' => $results6,
                'limit' => $results_count,
                "page" => 1
            )
        );

        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach ($results as $result) {
            array_push($error_list, $result->line_no . '行目の色コードが不正です。');
        }
        $json_list['errors'] = $error_list;
    }


    //C-3-5 社員コードごとの客先商品id(emply_order_req_no)の重複 検索条件
    $arg_str8 = "SELECT ";
    $arg_str8 .= " * ";
    $arg_str8 .= " FROM t_import_job";
    $arg_str8 .= " WHERE (cster_emply_cd,emply_order_req_no) IN(";
    $arg_str8 .= "SELECT cster_emply_cd,emply_order_req_no ";
    $arg_str8 .= "FROM t_import_job ";
    $arg_str8 .= "GROUP by cster_emply_cd,emply_order_req_no ";
    $arg_str8 .= "HAVING count(*) > 1 ) ";

    $results8 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str8));
    $result_obj8 = (array)$results8;
    $results_cnt8 = $result_obj8["\0*\0_count"];

    //エラー行が見つかればエラーメッセージを表示
    if (!empty($results_cnt8)) {
        $results_count = (count($results8));//ページング処理２０個制限の前に数を数える
        $paginator_model = new PaginatorModel(
            array(
                'data' => $results8,
                'limit' => $results_count,
                "page" => 1
            )
        );

        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach ($results as $result) {
            array_push($error_list, $result->line_no . '行目のお客様発注Noが、重複されて使用されています。');
        }
        $json_list['errors'] = $error_list;
    }


    //C-3-9 お客様発注Noが発注情報、発注情報トランに存在しないこと
    $arg_str9 = "SELECT ";
    $arg_str9 .= " * ";
    $arg_str9 .= " FROM t_import_job AS T1 ";
    $arg_str9 .= " WHERE EXISTS ";
    $arg_str9 .= "(SELECT * FROM t_order AS T2 ";
    $arg_str9 .= "WHERE emply_order_req_no = T1.emply_order_req_no) ";
    $arg_str9 .= " OR EXISTS ";
    $arg_str9 .= "(SELECT * FROM t_order_tran AS T3 ";
    $arg_str9 .= "WHERE emply_order_req_no = T1.emply_order_req_no)";


    $results9 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str9));
    $result_obj9 = (array)$results9;
    $results_cnt9 = $result_obj9["\0*\0_count"];

    //エラー行が見つかればエラーメッセージを表示
    if (!empty($results_cnt9)) {
        $results_count = (count($results9));//ページング処理２０個制限の前に数を数える
        $paginator_model = new PaginatorModel(
            array(
                'data' => $results9,
                'limit' => $results_count,
                "page" => 1
            )
        );

        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach ($results as $result) {
            array_push($error_list, $result->line_no . '行目の発注Noは、既に発注で使用されています。');
        }
        $json_list['errors'] = $error_list;
    }

    //C-3-7 貸与パターン別商品チェック　発注区分が貸与（１）の場合、社員番号単位に貸与パターンで指定された商品がファイル内に指定されていること。

    $arg_str10 = "SELECT ";
    $arg_str10 .= " * ";
    $arg_str10 .= " FROM ( SELECT * FROM t_import_job WHERE order_kbn = '1' ) as T1 ";
    $arg_str10 .= " WHERE NOT EXISTS ";
    $arg_str10 .= "(SELECT * FROM ( SELECT m_input_item.*,size_cd FROM m_input_item ";
    $arg_str10 .= "INNER JOIN m_item ON m_input_item.item_cd = m_item.item_cd AND m_input_item.color_cd = m_item.color_cd AND m_input_item.corporate_id = m_item.corporate_id ";
    $arg_str10 .= "WHERE m_input_item.corporate_id = '$corporate_id' AND m_input_item.rntl_cont_no = '$agreement_no' ) as T2 ";
    $arg_str10 .= "WHERE T2.item_cd = T1.item_cd AND T2.color_cd = T1.color_cd AND T2.std_input_qty = CAST(T1.quantity as integer) AND T2.size_cd = T1.size_cd )";
    //ChromePhp::log($arg_str10);


    $results10 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str10));
    $result_obj10 = (array)$results10;
    $results_cnt10 = $result_obj10["\0*\0_count"];

    //エラー行が見つかればエラーメッセージを表示
    if (!empty($results_cnt10)) {
        $results_count = (count($results10));//ページング処理２０個制限の前に数を数える
        $paginator_model = new PaginatorModel(
            array(
                'data' => $results10,
                'limit' => $results_count,
                "page" => 1
            )
        );

        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach ($results as $result) {
            array_push($error_list, $result->line_no . '行目の社員番号' . $result->cster_emply_cd . 'の貸与パターンの商品指定が不足しています。');
        }
        $json_list['errors'] = $error_list;
    }

    // エラーがなければ各データベースの登録に進む
    if (!empty($error_list)) {
        //エラーがあったら画面にエラーメッセージ
        //$json_list['errors'] = $error_list;
        //ChromePhp::log('error3');

        $app->session->set('error', $error_list);
       // ob_start();
       // header("location: http://sunpex_universal_local.pm1932.jp/universal/importCsv.html");
        //echo 'finish';
        //ob_flush();
        //flush();
        //header("location: http://sunpex_universal_local.pm1932.jp/universal/importCsv.html");
        //$rel = $_GET['reload'];
        //if ($rel == 'true') {
            //header("Location: " . $_SERVER['PHP_SELF']);
            //exit();
        //}
        //echo json_encode($json_list);
        return;
    }
    $end = microtime(true);
   // ChromePhp::log($end - $start);
    $start = microtime(true);

    //着用者基本マスタトラン
    try {
        $transaction = $app->transactionManager->get();

        //インポートジョブテーブルからデータの取得
        $arg_str13 = "SELECT ";
        $arg_str13 .= " * ";
        $arg_str13 .= " FROM t_import_job";

        $results = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query("$arg_str13"));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
        if (!empty($results_cnt)) {
            $paginator_model = new PaginatorModel(
                array(
                    "data" => $results,
                    "limit" => 1,
                    "page" => 1
                )
            );
            $paginator = $paginator_model->getPaginate();
        }

        $i = 1;
        $order_req_line_no = 1;//注文行用 数字セット
        //ChromePhp::log("着用者基本マスタトランへ各データ格納");
        $app->session->remove('order_cster_emply_cd1');
        //$checkordercd = $app->session->get('order_cster_emply_cd1');
        //ChromePhp::log($checkordercd);
        foreach ($results as $import_value) {
            //ChromePhp::log("foeach開始");
            //着用者基本マスタトランへ各データ格納 ここから
            $t_order_tran = new TOrderTran();
            $t_order_tran->setTransaction($transaction);


            //社員番号ごとの着用者コード採番 発注区分 = 貸与、着用者のみ
            if ($import_value->order_kbn == '0' || $import_value->order_kbn == '1') {
                if ($app->session->get('cster_emply_cd') == $import_value->cster_emply_cd) {

                    //同じ場合は、前行と同じシーケンス番号をセット。
                    //$m_wearer_std_tran->werer_cd = $app->session->get('werer_cd');
                    $t_order_tran->werer_cd = $app->session->get('werer_cd');
                    //ChromePhp::log('社員コード一緒');
                } else {//社員番号が前の行と違う場合に、nextvalにて新しいシーケンス番号(werer_cd_seq)を発行。
                    $m_wearer_std_tran = new MWearerStdTran();
                    $m_wearer_std_tran->setTransaction($transaction);
                    //ChromePhp::log('社員コード違う');
                    $werer_cd_seq_results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("select nextval('werer_cd_seq')"));
                    $m_wearer_std_tran->werer_cd = str_pad($werer_cd_seq_results[0]->nextval, 10, '0', STR_PAD_LEFT);
                    $t_order_tran->werer_cd = str_pad($werer_cd_seq_results[0]->nextval, 10, '0', STR_PAD_LEFT);
                    $app->session->set('werer_cd', str_pad($werer_cd_seq_results[0]->nextval, 10, '0', STR_PAD_LEFT));
                    $app->session->set('cster_emply_cd', $import_value->cster_emply_cd);
                    $m_wearer_std_tran->corporate_id = $auth["corporate_id"]; //コーポレートid
                    $m_wearer_std_tran->rntl_cont_no = $agreement_no; //レンタル契約No
                    $m_wearer_std_tran->cster_emply_cd = $import_value->cster_emply_cd; //社員番号パターン
                    $m_wearer_std_tran->rntl_sect_cd = $import_value->rntl_sect_cd; //レンタル部門コード
                    $m_wearer_std_tran->job_type_cd = $import_value->rent_pattern_code; //貸与パターン
                    $m_wearer_std_tran->werer_name = $import_value->werer_name; // 着用者名（漢字）
                    $m_wearer_std_tran->werer_name_kana = $import_value->werer_name_kana; // 着用者名（カナ）
                    $m_wearer_std_tran->sex_kbn = $import_value->sex_kbn; // 性別区分
                    $m_wearer_std_tran->werer_sts_kbn = '7'; // 着用者状況区分 その他着用開始
                    $m_wearer_std_tran->resfl_ymd = $import_value->wear_start; //着用開始日
                    //着用者統合ハッシュキー
                    $m_wearer_std_tran->m_wearer_std_comb_hkey = md5($auth['corporate_id'] . $m_wearer_std_tran->werer_cd . $agreement_no . $m_wearer_std_tran->rntl_sect_cd . $m_wearer_std_tran->job_type_cd);

                    //出荷先コード、出荷先支店コードを部門テーブルから企業id,契約no,拠点noごとに取得
                    $arg_str12 = "SELECT ";
                    $arg_str12 .= " std_ship_to_cd,std_ship_to_brnch_cd ";
                    $arg_str12 .= " FROM m_section";
                    $arg_str12 .= " WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no' AND rntl_sect_cd = '$import_value->rntl_sect_cd'";
                    $m_section = new MSection();
                    $ship_results = new Resultset(null, $m_section, $m_section->getReadConnection()->query("$arg_str12"));
                    $ship_result_obj = (array)$ship_results;
                    $ship_results_cnt = $ship_result_obj["\0*\0_count"];
                    if (!empty($ship_results_cnt)) {
                        $m_wearer_std_tran->ship_to_cd = $ship_results[0]->std_ship_to_cd; //出荷先コード
                        $m_wearer_std_tran->ship_to_brnch_cd = $ship_results[0]->std_ship_to_brnch_cd; //出荷先支店コード
                    }//出荷先コード、出荷先支店コードここまで

                    $m_wearer_std_tran->order_sts_kbn = $import_value->order_kbn; // 発注状況区分
                    $m_wearer_std_tran->upd_kbn = '7'; // 更新区分
                    $m_wearer_std_tran->web_upd_date = date("Y/m/d H:i:s.sss", time()); //WEB更新日時
                    $m_wearer_std_tran->snd_kbn = '0'; //送信区分　保存（後で送信）
                    $m_wearer_std_tran->snd_date = date("Y/m/d H:i:s.sss", time()); //送信日時
                    $m_wearer_std_tran->del_kbn = '0'; //削除区分値 削除対象外
                    $m_wearer_std_tran->rgst_date = date("Y/m/d H:i:s.sss", time()); //登録日時
                    $m_wearer_std_tran->rgst_user_id = $import_value->rgst_user_id; //登録ユーザーID
                    $m_wearer_std_tran->upd_date = date("Y/m/d H:i:s.sss", time()); //更新日時
                    $m_wearer_std_tran->upd_user_id = $import_value->rgst_user_id; //ユーザーID
                    // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
                    $m_wearer_std_tran->m_job_type_comb_hkey = md5($auth['corporate_id'] . $agreement_no . $import_value->rent_pattern_code);
                    //部門マスタ_統合ハッシュキー	 企業ID、レンタル契約No.、レンタル部門コード...
                    $m_wearer_std_tran->m_section_comb_hkey = md5($auth['corporate_id'] . $agreement_no . $import_value->rntl_sect_cd);
                    //着用者基本マスタトランへ各データ格納 ここまで
                    /*if ($i % 500 == 0 ){
                        ob_start();
                        //echo 'fuga';
                        ob_flush();
                        flush();
                    }*/
                    ChromePhp::log("");
                    if ($m_wearer_std_tran->create() == false) {
                        ChromePhp::log('error1');
                        array_push($error_list, '着用者の登録に失敗しました。');
                        $transaction->rollBack();

                        $app->session->set('error', $error_list);
                        //("location: http://sunpex_universal_local.pm1932.jp/universal/importCsv.html");
                        //headerexit();

                        //$json_list['errors'] = $error_list;
                        //echo json_encode($json_list);
                        //throw new \Exception('');
                        return;
                    }
                }
            }
            //ChromePhp::log("発注開始");
            //発注情報トランへ各データ格納 ここから 貸与、異動のみ
            if ($import_value->order_kbn == '1' || $import_value->order_kbn == '5') {
                //ChromePhp::log($app->session->get('cster_emply_cd1'));
                //社員番号ごとの発注noを採番$

                if ($app->session->get('order_cster_emply_cd1') == $import_value->cster_emply_cd) {
                    //ChromePhp::log("社員番号同じ発注");
                    $t_order_tran->order_req_no = $app->session->get('order_req_no'); //社員番号が前と同じ場合にセッションに入ってる、注文番号を設定
                    //ChromePhp::log($import_value->cster_emply_cd);
                    $order_req_line_no++;
                    $t_order_tran->order_req_line_no = $order_req_line_no;
                } else {//社員番号が違う場合
                    //ChromePhp::log("社員番号違う発注");
                    $t_order_tran_results = new Resultset(null, $t_order_tran, $t_order_tran->getReadConnection()->query("select nextval('t_order_seq')"));
                    $t_order_tran->order_req_no = str_pad($t_order_tran_results[0]->nextval, 10, '0', STR_PAD_LEFT);
                    $app->session->set('order_req_no', str_pad($t_order_tran_results[0]->nextval, 10, '0', STR_PAD_LEFT));
                    $app->session->set('order_cster_emply_cd1', $import_value->cster_emply_cd);
                    $order_req_line_no = 1;
                    $t_order_tran->order_req_line_no = 1;//行番号

                }
                //$t_order_tran->t_order_comb_hkey = $auth['corporate_id'].$import_value->order_req_no.$import_value->order_req_line_no;//発注情報_統合ハッシュキー	 (企業ID、発注依頼No、発注依頼行No)
                $t_order_tran->t_order_comb_hkey = md5(
                    $auth['corporate_id']
                    . $t_order_tran->order_req_no
                    . $t_order_tran->order_req_line_no
                );
                //ChromePhp::log($import_value->order_req_no);

                $t_order_tran->corporate_id = $auth["corporate_id"]; //コーポレートid
                //$t_order_tran->order_req_no = $import_value->order_req_no; //発注情報の発注依頼No
                //$t_order_tran->order_req_line_no = $import_value->order_req_line_no; //発注依頼Noの行No
                $t_order_tran->order_req_ymd = date("Y/m/d H:i:s.sss", time()); //発注依頼日
                $t_order_tran->order_sts_kbn = $import_value->order_kbn; //発注状況区分
                $t_order_tran->rntl_cont_no = $agreement_no; //レンタル契約No.
                $t_order_tran->rntl_sect_cd = $import_value->rntl_sect_cd; //レンタル契約No.
                $t_order_tran->job_type_cd = $import_value->rent_pattern_code; //貸与パターン

                //ここから投入マスタ、職種アイテムコード
                $arg_str13 = "SELECT ";
                $arg_str13 .= " job_type_item_cd ";
                $arg_str13 .= " FROM (SELECT * FROM m_input_item";
                $arg_str13 .= " WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no') as T1";
                $arg_str13 .= " WHERE job_type_cd = '$import_value->rent_pattern_code' AND item_cd = '$import_value->item_cd' AND color_cd = '$import_value->color_cd'";

                $m_input_item = new MInputItem();
                $item_results = new Resultset(null, $m_input_item, $m_input_item->getReadConnection()->query("$arg_str13"));
                $item_result_obj = (array)$item_results;
                $item_results_cnt = $item_result_obj["\0*\0_count"];
                if (!empty($item_results_cnt)) {
                    $t_order_tran->job_type_item_cd = $item_results[0]->job_type_item_cd;
                }
                //ここまで投入マスタ、職種アイテムコード
                if ($import_value->order_kbn == '5') {
                    $t_order_tran->werer_cd = $import_value->werer_cd; // 着用者コード
                }
                //$t_order_tran->werer_cd = $import_value->werer_cd; //着用者コード
                $t_order_tran->item_cd = $import_value->item_cd; //商品コード
                $t_order_tran->color_cd = $import_value->color_cd; //色コード
                $t_order_tran->size_cd = $import_value->size_cd; //size_cd
                $t_order_tran->size_two_cd = " ";
                $t_order_tran->whse_cd = " ";
                $t_order_tran->stk_usr_cd = " ";
                $t_order_tran->stk_usr_brnch_cd = " ";

                //出荷先コード、出荷先支店コードを部門テーブルから企業id,契約no,拠点noごとに取得　ここから
                $arg_str14 = "SELECT ";
                $arg_str14 .= " std_ship_to_cd,std_ship_to_brnch_cd ";
                $arg_str14 .= " FROM m_section";
                $arg_str14 .= " WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no' AND rntl_sect_cd = '$import_value->rntl_sect_cd'";
                $m_section = new MSection();
                $ship_results = new Resultset(null, $m_section, $m_section->getReadConnection()->query("$arg_str14"));
                $ship_result_obj = (array)$ship_results;
                $ship_results_cnt = $ship_result_obj["\0*\0_count"];
                if (!empty($ship_results_cnt)) {
                    $t_order_tran->ship_to_cd = $ship_results[0]->std_ship_to_cd; //出荷先コード
                    $t_order_tran->ship_to_brnch_cd = $ship_results[0]->std_ship_to_brnch_cd; //出荷先支店コード
                }//出荷先コード、出荷先支店コードここまで

                $t_order_tran->appointment_ymd = date("Y/m/d H:i:s.sss", time()); //発令日
                $t_order_tran->order_qty = (int)$import_value->quantity; //発注数
                $t_order_tran->memo = $import_value->message;
                $t_order_tran->werer_name = $import_value->werer_name; // 着用者名（漢字）
                $t_order_tran->cster_emply_cd = $import_value->cster_emply_cd; //社員番号パターン
                $t_order_tran->werer_sts_kbn = '7'; // 着用者状況区分 その他着用開始
                $t_order_tran->resfl_ymd = $import_value->wear_start; //異動日->着用開始日
                $t_order_tran->snd_kbn = '0'; //送信区分	 0：未送信
                $t_order_tran->snd_date = date("Y/m/d H:i:s.sss", time()); //送信日時
                $t_order_tran->del_kbn = '0'; //削除区分値 削除対象外
                $t_order_tran->rgst_date = date("Y/m/d H:i:s.sss", time()); //登録日時
                $t_order_tran->rgst_user_id = $import_value->rgst_user_id; //登録ユーザーID
                $t_order_tran->upd_date = date("Y/m/d H:i:s.sss", time()); //更新日時
                $t_order_tran->upd_user_id = $import_value->rgst_user_id; //更新ユーザーID
                $t_order_tran->upd_pg_id = $import_value->rgst_user_id; //更新プログラムID
                $t_order_tran->order_status = '1'; //発注ステータス 1:未出荷を設置
                $t_order_tran->emply_order_req_no = $import_value->emply_order_req_no; //お客様発注No
                $t_order_tran->order_reason_kbn = $import_value->order_reason_kbn;//理由区分
                //ログインしている企業ID、インポート処理テーブル. 商品コード、インポート処理テーブル. 色コード、インポート処理テーブル. サイズコード
                $t_order_tran->m_item_comb_hkey = md5($auth['corporate_id'] . $import_value->item_cd . $import_value->size_cd);
                // 職種マスタ_統合ハッシュキー(ログインしている企業ID、画面選択された契約Noのレンタル契約No、インポート処理テーブル. 貸与パターン)
                $t_order_tran->m_job_type_comb_hkey = md5($auth['corporate_id'] . $agreement_no . $import_value->rent_pattern_code);
                //部門マスタ_統合ハッシュキー	ログインしている企業ID、画面選択された契約Noのレンタル契約No、インポート処理テーブル. 支店コード
                $t_order_tran->m_section_comb_hkey = md5($auth['corporate_id'] . $agreement_no . $import_value->rntl_sect_cd);
                //着用者基本マスタ_統合ハッシュキー ログインしている企業ID、インポート処理テーブル. 着用者コード、画面選択された契約Noのレンタル契約No、インポート処理テーブル. 支店コード、インポート処理テーブル. 貸与パターン
                $t_order_tran->m_wearer_std_comb_hkey = md5($auth['corporate_id'] . $import_value->werer_cd . $agreement_no . $import_value->rntl_sect_cd . $import_value->rent_pattern_code);
                //着用者商品マスタ_統合ハッシュキー ログインしている企業ID、インポート処理テーブル. 着用者コード、画面選択された契約Noのレンタル契約No.、インポート処理テーブル. 支店コード、インポート処理テーブル. 貸与パターン、職種アイテムコード、商品コード、色コード、サイズコード、サイズ２コード
                $t_order_tran->m_wearer_item_comb_hkey = md5($auth['corporate_id'] . $t_order_tran->werer_cd . $agreement_no . $import_value->rntl_sect_cd . $import_value->rent_pattern_code . $t_order_tran->job_type_item_cd . $t_order_tran->item_cd . $t_order_tran->color_cd . $t_order_tran->size_cd . $t_order_tran->size_two_cd);
                /*if ($i % 500 == 0 ){
                    ob_start();
                    //echo 'hige';
                    ob_flush();
                    flush();
                }*/
                //ChromePhp::log($t_order_tran);
                if ($t_order_tran->create() == false) {
                    //ChromePhp::log('error2');
                    //ChromePhp::log('オーダーコミットエラー');
                    array_push($error_list, '注文情報の登録に失敗しました。');
                    $transaction->rollBack();

                    $app->session->set('error', $error_list);
                    //header("location: http://sunpex_universal_local.pm1932.jp/universal/importCsv.html");
                    //exit();

                    //$json_list['errors'] = $error_list;
                    //echo json_encode($json_list);
                    //throw new \Exception('');
                    return;
                }
            }//貸与、異動のみ
            //ChromePhp::log($m_wearer_std_tran);
            //ChromePhp::log("foreach");
            $i++;
        }//t_import_jobのforeachはここまで

        //社員番号、着用者番号のセッションを削除
        $app->session->remove("cster_emply_cd");
        $app->session->remove("werer_cd");
        $app->session->remove("order_cster_emply_cd1");

        $json_list['wererorder'] = 'ok';
        ChromePhp::log('commit');
        $transaction->commit();
        $end = microtime(true);
        // ChromePhp::log($end - $start);

        $app->session->set('commit', 'success');
        return true;


    } catch (Exception $e) {
        ///ChromePhp::log($e);
        ChromePhp::log("プログラムでエラー");
        array_push($error_list, 'プログラム内でエラーが発生しました。(' . $e->getMessage() . ')');
        //$transaction->rollBack();

        $app->session->set('error', $error_list);
        //header("location: http://sunpex_universal_local.pm1932.jp/universal/importCsv.html");
        //exit();

        //$json_list['errors'] = $error_list;
        //echo json_encode($json_list);
        return true;
    }

});


$app->post('/import_csv_return', function () use ($app) {
//ChromePhp::log($app->session->get('error'));


    $json_list['errors'] = $app->session->get('error');
    echo json_encode($json_list);
    return true;

});

/**
 *  ・フォーマットエラーメッセージ生成
 *
 *  関数の詳細:
 *  行数と項目名を渡して、
 *  フォーマットエラー時のメッセージを生成する。
 *
 * @param integer $line_cnt 行数
 * @param string $item_name 項目名
 * @return string エラーメッセージ
 */
function error_msg_format($line_cnt, $item_name)
{
    return $line_cnt . '行目の' . $item_name . 'のフォーマットが不正です';
}

/**
 *  ・マスタエラーメッセージ生成
 *
 *  関数の詳細:
 *  行数と項目名を渡して、
 *  フォーマットエラー時のメッセージを生成する。
 *
 * @param integer $line_cnt 行数
 * @param string $item_name 項目名
 * @return string エラーメッセージ
 */
function error_msg_master($line_cnt, $item_name)
{
    return $line_cnt . '行目の' . $item_name . 'が不正です';
}

/**
 * ・フォーマットチェッカー
 *
 * インポートされたCSVのデータが正しいフォーマットで作成されているかのチェックを行う
 *
 * @param array $error_list エラーメッセージを格納した配列
 * @param array $line_list １行データ
 * @param integer $line_cnt 行数
 * @return array エラーメッセージを格納した配列
 */
function chk_format($error_list, $line_list, $line_cnt)
{
    if ($line_list[0]) {
        //社員番号
        if (!chk_pattern($line_list[0], 1)) {
            array_push($error_list, error_msg_format($line_cnt, '社員番号'));
        }
    }
    /*
    if ($line_list[1]) {
        //着用者名（漢字）
       if (!chk_pattern($line_list[1], 1)) {
            array_push($error_list, error_msg_format($line_cnt, '着用者名（漢字）'));
          }
   }*/
    /*
    if ($line_list[2]) {
        //着用者名（カナ）
        if (!chk_pattern($line_list[2], 1)) {
            array_push($error_list, error_msg_format($line_cnt, '着用者名（カナ）'));
        }
    }
    */
    if ($line_list[3]) {
        //性別区分
        if (!chk_pattern($line_list[3], 2)) {
            array_push($error_list, error_msg_format($line_cnt, '性別区分'));
        }
    }
    if ($line_list[4]) {
        //支店コード
        if (!chk_pattern($line_list[4], 1)) {
            array_push($error_list, error_msg_format($line_cnt, '支店コード'));
        }
    }
    if ($line_list[5]) {
        //貸与パターン
        if (!chk_pattern($line_list[5], 4)) {
            array_push($error_list, error_msg_format($line_cnt, '貸与パターン'));
        }
    }
    if ($line_list[6]) {
        //着用開始日
        if (!chk_pattern($line_list[6], 3)) {
            array_push($error_list, error_msg_format($line_cnt, '着用開始日'));
        }
    }
    if ($line_list[7]) {
        //発注区分
        if (!chk_pattern($line_list[7], 5)) {
            array_push($error_list, error_msg_format($line_cnt, '発注区分'));
        }
    }
    if ($line_list[8]) {
        //商品コード
        if (!chk_pattern($line_list[8], 6)) {
            array_push($error_list, error_msg_format($line_cnt, '商品コード'));
        }
    }
    if ($line_list[9]) {
        //サイズコード
        if (!chk_pattern($line_list[9], 7)) {
            array_push($error_list, error_msg_format($line_cnt, 'サイズコード'));
        }
    }
    if ($line_list[10]) {
        //色コード
        if (!chk_pattern($line_list[10], 8)) {
            array_push($error_list, error_msg_format($line_cnt, '色コード'));
        }
    }
    if ($line_list[11]) {
        //数量
        if (!chk_pattern($line_list[11], 9)) {
            array_push($error_list, error_msg_format($line_cnt, '数量'));
        }
    }
    if ($line_list[12]) {
        //伝言欄
        if (!chk_pattern($line_list[12], 10)) {
            array_push($error_list, error_msg_format($line_cnt, 'お客様発注No'));
        }
    }
    if ($line_list[13]) {
        //伝言欄
        if (!chk_pattern($line_list[13], 11)) {
            array_push($error_list, error_msg_format($line_cnt, '理由区分'));
        }
    }


    if ($line_list[14]) {
        //伝言欄
        if (!chk_pattern($line_list[14], 12)) {
            array_push($error_list, error_msg_format($line_cnt, '伝言欄'));
        }
    }
    return $error_list;
}

/**
 * ・パターンチェッカー
 *
 * ステータスパターンに応じてフォーマットのチェックを行う
 *
 * @param string $val チェックする値
 * @param integer $pattaern チェックパターン
 * @return boolean チェック結果
 */
function chk_pattern($val, $pattaern)
{
    switch ($pattaern) {
        case 1:
            //パターン1(半角英数10桁)　//社員番号
            // if(!mb_strlen($val) >= 1 || !mb_strlen($val) <= 10 || !preg_match("/^[a-zA-Z0-9]+$/", $val)){
            if (!preg_match("/^[a-zA-Z0-9]{1,10}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 2:
            //パターン2(半角数1桁)1か2  //性別区分
            if (!preg_match("/^[1-2]{1,1}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 3:
            //パターン3(半角数字8桁 年月日:yyyymmddの厳密チェックはしない)
            if (!preg_match("/^[0-9]{1,8}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 4:
            //パターン4(半角数字3桁) 貸与パターン
            if (!preg_match("/^[0-9]{1,3}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 5:
            //パターン5(半数1桁)1か2  //発注区分
            if (!preg_match("/^[0-1,5]{1,1}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 6:
            //パターン6(半角英数15桁)//商品コード
            if (!preg_match("/^[-a-zA-Z0-9]{1,15}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 7:
            //パターン7(半角英数5桁) //サイズコード
            if (!preg_match("/^[-a-zA-Z0-9]{1,5}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 8:
            //パターン8(半角英数5桁) //色コード
            if (!preg_match("/^[-a-zA-Z0-9]{1,5}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 9:
            //パターン9(半角数9桁) //数量
            if (!preg_match("/^[0-9]{1,9}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 10:
            //パターン10(半角英数20桁) //お客様発注No
            if (!preg_match("/^[a-zA-Z0-9]{1,20}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 11:
            //パターン11(半数2桁)1か2  //理由区分
            if (!preg_match("/^[0-9]|1[0-9]|2[0-6]{1,2}$/", $val)) {
                return false;
            } else {
                return true;
            }
            break;
        case 12:
            //パターン12(200文字)
            if (mb_strlen($val) > 100) {
                return false;
            } else {
                return true;
            }
            break;
        default:

            break;
    }
}

function byte_cnv($data)
{
    //変換前文字コード
    $bf = 'UTF-8';
    //変換後文字コード
    $af = 'Shift-JIS';

    return strlen(bin2hex(mb_convert_encoding($data, $af, $bf))) / 2;
}
