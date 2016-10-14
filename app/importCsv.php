<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;


/**
 * CSV取込
 */
$app->post('/import_csv', function () use ($app) {
    ChromePhp::log('ここ');

    $json_list = array();
    $error_list = array();
    $getFileExt = new SplFileInfo($_FILES['file']['name']);

    if ($getFileExt->getExtension() == 'csv') {
        try {
            $file = file($_FILES['file']['tmp_name']);
            mb_convert_variables("UTF-8", "SJIS-win", $file);
            $chk_file = $file;
            unset($chk_file[0]); //チェック時はヘッダーを無視する


        } catch (Exception $e) {
            array_push($error_list, '取り込んだファイルの形式が不正です。');
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            return true;
        }

        $new_list = array();
        $no_chk_list = array();
        $no_list = array(); //よろず発注Noリスト
        $auth = $app->session->get("auth");


        try {
            $line_no = 1;
            $line_cnt = 1; //行数

            foreach ($chk_file as $line) {
                $upflg = false;
                //csvの１行を配列に変換する
                $line_list = str_getcsv($line, ',', '"');
                // 項目数チェック: 行単位の項目数が、仕様通りの項目数(15)かをチェックする。
                if (count($line_list) != 15) {

                    $cnt_list = array();
                    //項目数が不正な場合、エラーメッセージを配列に格納
                    array_push($error_list, $line_cnt . '行目の項目数が不正です');
                    continue;
                }

                //必須チェック
                if(empty($line_list[0])){
                    array_push($error_list, $line_cnt . '行目の社員番号を入力してください。');
                }
                if(empty($line_list[1])){
                    array_push($error_list, $line_cnt . '行目の着用者名(漢字)を入力してください。');
                }
                if(empty($line_list[2])){
                    array_push($error_list, $line_cnt . '行目の着用者名(カナ)を入力してください。');
                }
                if($line_list[3] == ''){
                    array_push($error_list, $line_cnt . '行目の性別区分を入力してください。');
                }
                if(empty($line_list[4])){
                    array_push($error_list, $line_cnt . '行目の支店コードを入力してください。');
                }
                if(empty($line_list[5])){
                    array_push($error_list, $line_cnt . '行目の貸与パターンを入力してください。');
                }
                if($line_list[7] == ''){
                    array_push($error_list, $line_cnt . '行目の発注区分を入力してください。');
                }

                //発注区分が貸与、異動の場合に必須
                if($line_list[7] == '1' || $line_list[7] == '5'){
                    if(empty($line_list[6])){
                        array_push($error_list, $line_cnt . '行目の着用開始日を入力してください。');
                    }
                    if(empty($line_list[8])){
                        array_push($error_list, $line_cnt . '行目の発注コードを入力してください。');
                    }
                    if(empty($line_list[9])){
                        array_push($error_list, $line_cnt . '行目のサイズコードを入力してください。');
                    }
                    if(empty($line_list[10])){
                        array_push($error_list, $line_cnt . '行目の色コードを入力してください。');
                    }
                    if(empty($line_list[11])){
                        array_push($error_list, $line_cnt . '行目の数量を入力してください。');
                    }
                    if(empty($line_list[13])){
                        array_push($error_list, $line_cnt . '行目の理由区分を入力してください。');
                    }
                }elseif($line_list[7] == '0'){

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
                $error_list = chk_format($error_list,$line_list,$line_cnt);
                $line_cnt++;





                // 必須チェック: 行単位の発注区分毎の必須値が、それぞれ仕様通り設定されているかをチェックする。



                //}
                //発注Noファイル内重複チェック
                //同じ発注Noがあるか
                //if(array_search($line_list[0],$no_list)){
                //あったら発注No+社員番号でチェック、社員番号が違う場合、よろず発注Noが重複
                //    if(!array_search(strval($line_list[0]).strval($line_list[1]),$no_chk_list)){
                //        array_push($error_list , $line_cnt.'行目のよろず発注Noが、重複して使用されています。');
                //    }
                //}else{
                //発注Noが見つからない場合、チェック用の配列につめる
                //    array_push($no_list,strval($line_list[0]));
                //    array_push($no_chk_list,strval($line_list[0]).strval($line_list[1]));
                //}

                //発注No DB重複チェック
                //$result = TOrder::find(array('conditions' => 'order_req_no = '."'".$line_list[0]."'"));
                //if(count($result) > 0){
                //    array_push($error_list , $line_cnt.'行目のよろず発注Noは、発注で既に使用されています。');
                //}

                // インポートログテーブル重複チェック
                // CSVファイル内の「発注No」が、インポートログテーブルの送信フラグ＝1：送信済のレコードで存在しない事。
                //$result = TImportJob::find(array('conditions' => 'order_req_no = '."'".$line_list[0]."'".' AND send_flg = 1'));
                //if(count($result) > 0){
                //    array_push($error_list , $line_cnt.'行目のよろず発注Noは、過去のCSV取込で既に使用されています。');
                //}
                //DB登録用のリストに格納

                array_push($line_list, $line_no++);
                array_push($new_list, $line_list);

            }

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
                    $t_i_job->werer_cd = $result[0]->werer_cd; //着用者コード
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

                    if ($t_i_job->create() == false) {
                        $json_list['errors'] = array('csvファイルの登録に失敗しました。');
                        $transaction->rollBack();
                        echo json_encode($json_list);
                        return true;
                    }
                    $i++;
                    $no_line++;
                }


                $end = microtime(true);
                ChromePhp::log($end - $start);
            } else {
                //エラーがあったら画面にエラーメッセージ
                ChromePhp::log("エラーがあったら画面にエラーメッセージ");

                $json_list['errors'] = $error_list;
                echo json_encode($json_list);
                return true;
            }


            // エラーがなければコミット
            if (!empty($error_list)) {
                //エラーがあったら画面にエラーメッセージ
                $json_list['errors'] = $error_list;
                $transaction->rollback();
                echo json_encode($json_list);
                return true;
            }

            $json_list['ok'] = 'ok';
            $transaction->commit();

            //ここからマスターチェック


            //テーブルから検索
            $results = TImportJob::find(array(
                'conditions' => "job_no = '$job_no_check'",
            ));
            ChromePhp::log($results);
            //リスト作成
            $list = array();
            $all_list = array();

            $results_count = (count($results));//ページング処理２０個制限の前に数を数える
            ChromePhp::log($results_count);

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
                $result->cster_emply_cd;





            }

            /*
            if($line_list[7]=='1'){//設計書の①
            //貸与の場合
            //マスタ存在チェック(社員番号)
                $result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'" . ' AND werer_sts_kbn = '."'1'" ));
                if(count($result) > 0){
                    array_push($error_list ,error_msg_master($line_cnt,'社員番号'));
                }
            }elseif($line_list[7]=='1'){//設計書の③
            //マスタ存在チェック(社員番号)
                $result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'"));
                if(count($result) > 0){
            //存在した場合は、稼働中である事
                    $result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'" . ' AND werer_sts_kbn = '."'1'" ));
                    if(count($result) == 0){
                        array_push($error_list ,error_msg_master($line_cnt,'社員番号'));
                    }
                }
            } else {//設計書の②
            //マスタ存在チェック(社員番号)
                $result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'" . ' AND werer_sts_kbn = '."'1'" ));
                if(count($result) == 0){
                    array_push($error_list ,error_msg_master($line_cnt,'社員番号'));
                }
             }
            */

            //マスタ存在チェック(支店コード)
            // $result = MSection::find(array('conditions' => 'rntl_sect_cd = '."'".$line_list[2]."'"));
            // if(count($result) <= 0){
            // array_push($error_list ,error_msg_master($line_cnt,'支店コード'));
            //}
            //マスタ存在チェック(貸与パターン)
            // $result = MJobType::find(array('conditions' => 'job_type_cd = '."'".$line_list[3]."'"));
            // if(count($result) <= 0){
            //     array_push($error_list ,error_msg_master($line_cnt,'貸与パターン'));
            // }
            //マスタ存在チェック(商品コード)
            //if($line_list[6]!='2'){
            //   $result = MItem::find(array('conditions' => 'item_cd = '."'".$line_list[7]."'"));
            //   if(count($result) <= 0){
            //       array_push($error_list ,error_msg_master($line_cnt,'商品コード'));
            //   }
            //マスタ存在チェック(サイズコード)
            //    $result = MItem::find(array('conditions' => 'size_cd = '."'".$line_list[8]."'"));
            //    if(count($result) <= 0){
            //        array_push($error_list ,error_msg_master($line_cnt,'サイズコード'));
            //    }
            //マスタ存在チェック(色コード)
            //    $result = MItem::find(array('conditions' => 'color_cd = '."'".$line_list[9]."'"));
            //    if(count($result) <= 0){
            //        array_push($error_list ,error_msg_master($line_cnt,'色コード'));
            //    }


















            echo json_encode($json_list);
            return true;

        } catch (Exception $e) {
            ChromePhp::log($e);
            array_push($error_list, 'プログラム内でエラーが発生しました。(' . $e->getMessage() . ')');
            $transaction->rollBack();
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            return true;
        }


        //$json_list['ok'] = 'ok';
        //$transaction->commit();
        //フォーマットチェック: 行単位の各項目のフォーマット形式が、それぞれ仕様通りのフォーマットであるかチェックする。
        /*
        $error_list = chk_format($error_list,$line_list,$line_cnt);
        if($line_list[6]=='1'&&$line_list[3]!='13'){//設計書の①
            //貸与の場合
            //マスタ存在チェック(社員番号)
            $result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'" . ' AND werer_sts_kbn = '."'1'" ));
            if(count($result) > 0){
                array_push($error_list ,error_msg_master($line_cnt,'社員番号'));
            }
        }elseif($line_list[6]=='1'&&$line_list[3]=='13'){//設計書の③
            //マスタ存在チェック(社員番号)
            $result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'"));
            if(count($result) > 0){
                //存在した場合は、稼働中である事
                $result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'" . ' AND werer_sts_kbn = '."'1'" ));
                if(count($result) == 0){
                    array_push($error_list ,error_msg_master($line_cnt,'社員番号'));
                }
            }
        } else {//設計書の②
            //マスタ存在チェック(社員番号)
            $result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'" . ' AND werer_sts_kbn = '."'1'" ));
            if(count($result) == 0){
                array_push($error_list ,error_msg_master($line_cnt,'社員番号'));
            }
        }
        //マスタ存在チェック(支店コード)
        // $result = MSection::find(array('conditions' => 'rntl_sect_cd = '."'".$line_list[2]."'"));
        // if(count($result) <= 0){
        // array_push($error_list ,error_msg_master($line_cnt,'支店コード'));
        // }
        //マスタ存在チェック(貸与パターン)
        $result = MJobType::find(array('conditions' => 'job_type_cd = '."'".$line_list[3]."'"));
        if(count($result) <= 0){
            array_push($error_list ,error_msg_master($line_cnt,'貸与パターン'));
        }
        //マスタ存在チェック(商品コード)
        if($line_list[6]!='2'){
            $result = MItem::find(array('conditions' => 'item_cd = '."'".$line_list[7]."'"));
            if(count($result) <= 0){
                array_push($error_list ,error_msg_master($line_cnt,'商品コード'));
            }
            //マスタ存在チェック(サイズコード)
            $result = MItem::find(array('conditions' => 'size_cd = '."'".$line_list[8]."'"));
            if(count($result) <= 0){
                array_push($error_list ,error_msg_master($line_cnt,'サイズコード'));
            }
            //マスタ存在チェック(色コード)
            $result = MItem::find(array('conditions' => 'color_cd = '."'".$line_list[9]."'"));
            if(count($result) <= 0){
                array_push($error_list ,error_msg_master($line_cnt,'色コード'));
            }
        }
        //よろず発注Noファイル内重複チェック
        //同じよろず発注Noがあるか
        if(array_search($line_list[0],$no_list)){
            //あったら発注No+社員番号でチェック、社員番号が違う場合、よろず発注Noが重複
            if(!array_search(strval($line_list[0]).strval($line_list[1]),$no_chk_list)){
                array_push($error_list , $line_cnt.'行目のよろず発注Noが、重複して使用されています。');
            }
        }else{
            //よろず発注Noが見つからない場合、チェック用の配列につめる
            array_push($no_list,strval($line_list[0]));
            array_push($no_chk_list,strval($line_list[0]).strval($line_list[1]));
        }

        //よろず発注No DB重複チェック
        $result = TOrder::find(array('conditions' => 'order_req_no = '."'".$line_list[0]."'"));
        if(count($result) > 0){
            array_push($error_list , $line_cnt.'行目のよろず発注Noは、発注で既に使用されています。');
        }

        // インポートログテーブル重複チェック
        // CSVファイル内の「よろず発注No」が、インポートログテーブルの送信フラグ＝1：送信済のレコードで存在しない事。
        //$result = TImportJob::find(array('conditions' => 'order_req_no = '."'".$line_list[0]."'".' AND send_flg = 1'));
        ////if(count($result) > 0){
        //    array_push($error_list , $line_cnt.'行目のよろず発注Noは、過去のCSV取込で既に使用されています。');
        //}

        if ($error_list) {
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);

            return true;
        }

*/


    } elseif ($getFileExt->getExtension() == 'xlsx') {

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

        //配列を初期化
        $new_list = array();


        try {

            $line_no = 1;//行no追加

            //存在する行数の最初の行を除き、連想配列にする
            for ($i = 1; $i < $lastRow; $i++) {
                $line_list = $sheet->readRow($i, 0);

                if (count($line_list) != 15) {
                    $cnt_list = array();
                    //項目数が不正な場合、エラーメッセージを配列に格納
                    array_push($error_list, $line_cnt . '行目の項目数が不正です');
                    continue;
                }
                array_push($line_list, $line_no++);
                array_push($new_list, $line_list);
            }
            // 登録処理
            if (empty($error_list)) {
                //新規データと既存データをよろず発注Noで並べ替え
                array_multisort($new_list, array_column($new_list, 0));

                $before_no = '';
                $no_line = 1;
                $transaction = $app->transactionManager->get();


                $auth = $app->session->get("auth");
                $cnt = 1;
                $order_no_list = array();
                $start = microtime(true);

                foreach ($new_list as $line_new) {
                    //行番号
                    if ($before_no == $line_new[0]) {
                        $no_line++;
                    } else {
                        $no_line = 1;
                    }
                    $before_no = $line_new[0];
                    //同じよろず発注Noが、インポートログテーブルに存在しない場合、よろず発注No単位にインポートログテーブルへ新規登録を行う。


                    data_save($line_new, $cnt, $no_line, $auth, $order_no_list);
                }
                $end = microtime(true);

                ChromePhp::log($end - $start);

            } else {
                //エラーがあったら画面にエラーメッセージ
                $json_list['errors'] = $error_list;
                echo json_encode($json_list);
                return true;
            }
        } catch (Exception $e) {
            array_push($error_list, 'プログラム内でエラーが発生しました。(' . $e->getMessage() . ')');

            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            return true;
        }
        // エラーがなければコミット
        if (!empty($error_list)) {
            //エラーがあったら画面にエラーメッセージ
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            return true;
        } else {
            $json_list['ok'] = 'ok';
            $transaction->commit();
        }
        echo json_encode($json_list);
        return true;


    }


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

//存在しない場合NG
/*
if($m_shipment_to_cnt == 0){
    array_push($error_list,'出荷先の値が不正です。');
}

if (byte_cnv($cond['cster_emply_cd']) > 10) {
    array_push($error_list, '社員コードの文字数が多すぎます。');
}

if (byte_cnv($cond['werer_name']) > 22) {
    array_push($error_list, '着用者名の文字数が多すぎます。');
}

if (byte_cnv($cond['werer_name_kana']) > 22) {
    array_push($error_list, '着用者名(カナ)の文字数が多すぎます。');
}
*/