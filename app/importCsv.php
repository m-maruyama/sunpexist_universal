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
  ini_set('max_execution_time', 0);
  ini_set('memory_limit', '500M');
  //$start = microtime(true);

  // アカウントセッション情報取得
  $auth = $app->session->get("auth");

  $json_list = array();
  $error_list = array();
  $query_list = array();

  // エラーコード 0:正常 1:何かしらの異常
  $json_list["error_code"] = "0";

  // 前回使用のセッションを削除
  $app->session->remove("chk_cster_emply_cd_1");
  $app->session->remove("chk_cster_emply_cd_2");
  $app->session->remove("chk_order_kbn");
  $app->session->remove("chk_order_req_no_1");
  $app->session->remove("chk_order_req_no_2");
  $app->session->remove("chk_werer_cd");

  // 画面で選択された契約No、ファイル、処理番号生成
  $agreement_no = $_POST["agreement_no"];
  $getFileExt = new SplFileInfo($_FILES['file']['name']);
  $job_no = $auth["corporate_id"].$auth["user_id"];

  //--CSV or Excel形式毎のバリデーション--ここから//
  if ($getFileExt->getExtension() == 'csv') {
    try {
      $file = file($_FILES['file']['tmp_name']);
      mb_convert_variables("UTF-8", "SJIS-win", $file);
      $chk_file = $file;
      unset($chk_file[0]); //チェック時はヘッダーを無視する
    } catch (Exception $e) {
      $error_list[] = '取り込んだファイルの形式が不正です。';
      $json_list['errors'] = $error_list;
      $json_list["error_code"] = "1";
      echo json_encode($json_list);
      return;
    }

    $new_list = array();
    $no_chk_list = array();
    $no_list = array();

    $line_no = 2;
    $line_cnt = 2;
    foreach ($chk_file as $line) {
      //csvの１行を配列に変換する
      $line_list = str_getcsv($line, ',', '"');
      // 項目数チェック: 行単位の項目数が、仕様通りの項目数(15)かをチェックする。
      //ChromePhp::log($line_list);
      if (count($line_list) != 15) {
        $cnt_list = array();
        //項目数が不正な場合、エラーメッセージを配列に格納
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の項目数が不正です';
          $line_cnt++;
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
        continue;
      }
      //必須チェック
      if (empty($line_list[0])) {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の社員番号を入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if (empty($line_list[1])) {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の着用者名(漢字)を入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if (empty($line_list[2])) {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の着用者名(カナ)を入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if ($line_list[3] == '') {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の性別区分を入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if (empty($line_list[4])) {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の支店コードを入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if (empty($line_list[5])) {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の貸与パターンを入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if ($line_list[7] == '') {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の発注区分を入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      //発注区分が貸与、異動の場合に必須
      if ($line_list[7] == '1' || $line_list[7] == '5') {
        if (empty($line_list[6])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目の着用開始日を入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
        if (empty($line_list[8])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目の発注コードを入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
        if (empty($line_list[9])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目のサイズコードを入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
        if (empty($line_list[10])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目の色コードを入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
        if (empty($line_list[11])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目の数量を入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
        if (empty($line_list[13])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目の理由区分を入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
      } elseif ($line_list[7] == '0') {

      }
      //日本語文字数チェック
      //着用者漢字
      if (byte_cnv($line_list[1]) > 100) {
        if (count($error_list) < 20) {
          $error_list[] = '着用者名(漢字)の文字数が多すぎます。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      //着用者カナ
      if (byte_cnv($line_list[2]) > 100) {
        if (count($error_list) < 20) {
          $error_list[] = '着用者名(カナ)の文字数が多すぎます。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      //伝言
      if (byte_cnv($line_list[14]) > 100) {
        if (count($error_list) < 20) {
          $error_list[] = '伝言欄の文字数が多すぎます。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      //フォーマットチェック: 行単位の各項目のフォーマット形式が、それぞれ仕様通りのフォーマットであるかチェックする。
      $error_list = chk_format($error_list, $line_list, $line_cnt);
      $line_cnt++;

      $line_list[] = $line_no++;
      $new_list[] = $line_list;
    }
  } elseif ($getFileExt->getExtension() == 'xlsx' || $getFileExt->getExtension() == 'xls') {
    $line_cnt = 1; //行数
    $new_list = array();
    $no_chk_list = array();
    $no_list = array(); //発注Noリスト
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
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の項目数が不正です';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
        $line_cnt++;
        continue;
      }
      //必須チェック
      if (empty($line_list[0])) {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の社員番号を入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if (empty($line_list[1])) {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の着用者名(漢字)を入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if (empty($line_list[2])) {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の着用者名(カナ)を入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if ($line_list[3] == '') {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の性別区分を入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if (empty($line_list[4])) {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の支店コードを入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if (empty($line_list[5])) {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の貸与パターンを入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      if ($line_list[7] == '') {
        if (count($error_list) < 20) {
          $error_list[] = $line_cnt . '行目の発注区分を入力してください。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      //発注区分が貸与、異動の場合に必須
      if ($line_list[7] == '1' || $line_list[7] == '5') {
        if (empty($line_list[6])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目の着用開始日を入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
        if (empty($line_list[8])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目の発注コードを入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
        if (empty($line_list[9])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目のサイズコードを入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
        if (empty($line_list[10])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目の色コードを入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
        if (empty($line_list[11])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目の数量を入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
        if (empty($line_list[13])) {
          if (count($error_list) < 20) {
            $error_list[] = $line_cnt . '行目の理由区分を入力してください。';
          } else {
            $json_list['errors'] = $error_list;
            $json_list["error_code"] = "1";
            echo json_encode($json_list);
            exit;
          }
        }
      } elseif ($line_list[7] == '0') {

      }
      //日本語文字数チェック
      //着用者漢字
      if (byte_cnv($line_list[1]) > 100) {
        if (count($error_list) < 20) {
          $error_list[] = '着用者名(漢字)の文字数が多すぎます。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      //着用者カナ
      if (byte_cnv($line_list[2]) > 100) {
        if (count($error_list) < 20) {
          $error_list[] = '着用者名(カナ)の文字数が多すぎます。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }
      //伝言
      if (byte_cnv($line_list[14]) > 100) {
        if (count($error_list) < 20) {
          $error_list[] = '伝言欄の文字数が多すぎます。';
        } else {
          $json_list['errors'] = $error_list;
          $json_list["error_code"] = "1";
          echo json_encode($json_list);
          exit;
        }
      }

      //フォーマットチェック: 行単位の各項目のフォーマット形式が、それぞれ仕様通りのフォーマットであるかチェックする。
      $error_list = chk_format($error_list, $line_list, $line_cnt);
      $line_cnt++;

      $line_list[] = $line_no++;
      $new_list[] = $line_list;
    }
  } else {
    // 拡張子csv,xlsxではない場合はエラー
    if (count($error_list) < 20) {
      $error_list[] = "対応していないファイル形式です。csv、xls、xlsxの拡張子のみ利用可能です。";
    } else {
      $json_list['errors'] = $error_list;
      $json_list["error_code"] = "1";
      echo json_encode($json_list);
      exit;
    }
  }
  // バリデーション処理で異常が発生した場合、以降処理せず終了
  if (!empty($error_list)) {
    $json_list['errors'] = $error_list;
    $json_list["error_code"] = "1";
    echo json_encode($json_list);
    return;
  }
  // リストを社員番号、発注区分単位で整頓しておく
  array_multisort(array_column($new_list, 0), SORT_ASC, array_column($new_list, 7), SORT_ASC, $new_list);
  //echo json_encode($json_list);
  //ChromePhp::LOG($new_list);
  //exit;
  //--CSV or Excel形式毎のバリデーション--ここまで//

  //--インポートログテーブル登録処理--ここから//
  $t_import_job = new TImportJob();
  $t_order = new TOrder();
  $m_wearer_std = new MWearerStd();
  $transaction = new Resultset(NULL, $t_import_job, $t_import_job->getReadConnection()->query("begin"));
  try {
    // 既存インポートログテーブル内、指定の処理番号のデータをクリーン
    $query_list = array();
    $query_list[] = "job_no = '".$job_no."'";
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str = "DELETE FROM ";
    $arg_str .= "t_import_job";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    //ChromePhp::LOG($arg_str);
    $results = new Resultset(NULL, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    // 新規インポートログテーブルインサート処理
    // 現日時
    $date_time = date("Y-m-d H:i:s.sss", time());
    // CALUM値の設定
    $calum_list = array(
      "job_no",
      "line_no",
      "order_req_no",
      "order_req_line_no",
      "cster_emply_cd",
      "werer_name",
      "werer_name_kana",
      "sex_kbn",
      "rntl_sect_cd",
      "rent_pattern_code",
      "wear_start",
      "order_kbn",
      "order_reason_kbn",
      "item_cd",
      "color_cd",
      "size_cd",
      "werer_cd",
      "quantity",
      "message",
      "emply_order_req_no",
      "user_id",
      "import_time",
      "rgst_date",
      "rgst_user_id",
      "upd_date",
      "upd_user_id"
    );
    $calum_query = implode(",", $calum_list);

    // VALUES値の設定
    $values_query = array();
    $no_line = 2;
    $app->session->set("chk_cster_emply_cd_1", "empty");
    $app->session->set("chk_cster_emply_cd_2", "empty");
    $app->session->set("chk_order_kbn", "empty");
    foreach ($new_list as $line_new) {
      $values_list = array();

      // 処理番号
      $values_list[] = "'".$job_no."'";
      // 行番号(CSVまたはExcelの行数)
      $values_list[] = $line_new[15];
      // 発注No、発注行No
      if (
        $app->session->get("chk_cster_emply_cd_1") == $line_new[0] &&
        $app->session->get("chk_order_kbn") == $line_new[7]
      )
      {
        $values_list[] = "'".$app->session->get("chk_order_req_no_1")."'";
        $order_line_no = $app->session->get("chk_order_line_no");
        $values_list[] = $order_line_no + 1;
        $order_line_no = $order_line_no + 1;
        $app->session->set("chk_order_line_no", $order_line_no);
      } else {
        $app->session->set("chk_cster_emply_cd_1", $line_new[0]);
        $app->session->set("chk_order_kbn", $line_new[7]);
        // 新規の発注No発行
        $results = new Resultset(
          null,
          $t_order,
          $t_order->getReadConnection()->query("select nextval('t_order_seq')")
        );
        $shin_order_req_no = "WB".str_pad($results[0]->nextval, 8, '0', STR_PAD_LEFT);
        $order_line_no = 1;
        $values_list[] = "'".$shin_order_req_no."'";
        $values_list[] = $order_line_no;
        $app->session->set("chk_order_req_no_1", $shin_order_req_no);
        $app->session->set("chk_order_line_no", $order_line_no);
      }
      // 社員番号
      $values_list[] = "'".$line_new[0]."'";
      // 着用者名
      $values_list[] = "'".$line_new[1]."'";
      // 着用者名（かな）
      $values_list[] = "'".$line_new[2]."'";
      // 性別区分
      $values_list[] = "'".$line_new[3]."'";
      // 支店コード
      $values_list[] = "'".$line_new[4]."'";
      // 貸与パターン
      $values_list[] = "'".$line_new[5]."'";
      // 着用開始日
      $values_list[] = "'".$line_new[6]."'";
      // 発注区分
      $values_list[] = "'".$line_new[7]."'";
      // 理由区分
      $values_list[] = "'".$line_new[13]."'";
      // 商品コード
      $values_list[] = "'".$line_new[8]."'";
      // 色コード
      $values_list[] = "'".$line_new[10]."'";
      // サイズコード
      $values_list[] = "'".$line_new[9]."'";
      // 着用者コード
      if ($line_new[7] == "0" || $line_new[7] == "1") {
        if ($app->session->get("chk_cster_emply_cd_2") == $line_new[0]) {
          $values_list[] = "'".$app->session->get("chk_werer_cd")."'";
        } else {
          $app->session->set("chk_cster_emply_cd_2", $line_new[0]);
          // 新規着用者コード発行
          $results = new Resultset(
            null,
            $m_wearer_std,
            $m_wearer_std->getReadConnection()->query("select nextval('werer_cd_seq')")
          );
          $werer_cd = str_pad($results[0]->nextval, 10, '0', STR_PAD_LEFT);
          $app->session->set("chk_werer_cd", $werer_cd);
          $values_list[] = "'".$werer_cd."'";
        }
      } else {
        if ($app->session->get("chk_cster_emply_cd_2") == $line_new[0]) {
          $values_list[] = "'".$app->session->get("chk_werer_cd")."'";
        } else {
          $app->session->set("chk_cster_emply_cd_2", $line_new[0]);
          // 既存着用者コード使用
          $query_list = array();
          $query_list[] = "cster_emply_cd = '".$line_new[0]."'";
          $query = implode(' AND ', $query_list);
          $arg_str = "";
          $arg_str = "SELECT ";
          $arg_str .= "werer_cd";
          $arg_str .= " FROM ";
          $arg_str .= "m_wearer_std";
          $arg_str .= " WHERE ";
          $arg_str .= $query;
          //ChromePhp::LOG($arg_str);
          $results = new Resultset(NULL, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
          $results_cnt = $result_obj["\0*\0_count"];
          if (!empty($results_cnt)) {
            $paginator_model = new PaginatorModel(
              array(
                "data"  => $results,
                "limit" => 1,
                "page" => 1
              )
            );
            $paginator = $paginator_model->getPaginate();
            $results = $paginator->items;
            foreach ($results as $result) {
              $werer_cd = $result->werer_cd;
            }
            $app->session->set("chk_werer_cd", $werer_cd);
            $values_list[] = "'".$werer_cd."'";
          }
        }
      }
      // 数量
      $values_list[] = "'".$line_new[11]."'";
      // 伝言欄
      $values_list[] = "'".$line_new[14]."'";
      // お客様発注No
      $values_list[] = "'".$line_new[12]."'";
      // インポートユーザーID
      $values_list[] = "'".$auth['user_id']."'";
      // インポート日時
      $values_list[] = "'".$date_time."'";
      // 登録日時
      $values_list[] = "'".$date_time."'";
      // 登録ユーザーID
      $values_list[] = "'".$auth['user_id']."'";
      // 更新日時
      $values_list[] = "'".$date_time."'";
      // 更新ユーザーID
      $values_list[] = "'".$auth['user_id']."'";

      $query_str = "";
      $query_str = implode(",", $values_list);
      $query_str = "(".$query_str.")";
      $values_query[] = $query_str;

      $no_line++;
    }
    $values_query = implode(",", $values_query);
    $arg_str = "";
    $arg_str = "INSERT INTO t_import_job";
    $arg_str .= "(".$calum_query.")";
    $arg_str .= " VALUES ";
    $arg_str .= $values_query;
    //ChromePhp::LOG("インポートログ登録クエリー");
    //ChromePhp::LOG($arg_str);
    $results = new Resultset(NULL, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));

    // トランザクション-コミット
    $transaction = new Resultset(NULL, $t_import_job, $t_import_job->getReadConnection()->query("commit"));
  } catch (Exception $e) {
    // トランザクション-ロールバック
    $transaction = new Resultset(NULL, $t_import_job, $t_import_job->getReadConnection()->query("rollback"));

    //ChromePhp::log($e);
    $error_list[] = 'E001 取込処理中に予期せぬエラーが発生しました。';
    $json_list['errors'] = $error_list;
    $json_list["error_code"] = "1";
    echo json_encode($json_list);
    exit;
  }
  //$end = microtime(true);
  //$time = $end - $start;
  //$json_list["time"] = $time;
  //echo json_encode($json_list);
  //exit;
  //--インポートログテーブル登録処理--ここまで//

  //--マスターチェック処理--ここから//
  $corporate_id = $auth["corporate_id"];
  $t_import_job = new TImportJob();

  //社員番号マスターチェック  発注区分：着用者登録のみ、貸与の場合　条件：着用者基本マスタに同じ客先社員コードがある場合、稼働である事。
  $arg_str = "SELECT ";
  $arg_str .= " * ";
  $arg_str .= " FROM ";
  $arg_str .= "(SELECT * FROM t_import_job WHERE job_no = '".$job_no."' AND (order_kbn = '1' OR order_kbn = '0')) AS T1";
  $arg_str .= " WHERE EXISTS ";
  $arg_str .= "(SELECT * FROM (SELECT * FROM m_wearer_std WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no'  AND rntl_sect_cd = T1.rntl_sect_cd AND job_type_cd = T1.rent_pattern_code ) AS T2 ";
  $arg_str .= "WHERE T1.cster_emply_cd = T2.cster_emply_cd AND T2.werer_sts_kbn = '1') ";
  $arg_str .= "UNION ALL ";
  $arg_str .= "SELECT ";
  $arg_str .= " * ";
  $arg_str .= " FROM ";
  $arg_str .= "(SELECT * FROM t_import_job WHERE order_kbn = '5') AS T1 ";
  $arg_str .= "WHERE NOT EXISTS ";
  $arg_str .= "( SELECT * FROM (SELECT * FROM m_wearer_std WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no' AND job_type_cd = T1.rent_pattern_code) AS T2 ";
  $arg_str .= "WHERE T1.cster_emply_cd = T2.cster_emply_cd AND T2.werer_sts_kbn = '1') ";
  $arg_str .= "ORDER BY line_no ";
  //ChromePhp::log($arg_str);
  $results = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
  $result_obj = (array)$results;
  $results_cnt = $result_obj["\0*\0_count"];
  if (!empty($results_cnt)) {
    $results_count = (count($results));
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
      if (count($error_list) < 20) {
        $error_list[] = $result->line_no . '行目の社員番号が不正です。';
      } else {
        $json_list['errors'] = $error_list;
        $json_list["error_code"] = "1";
        echo json_encode($json_list);
        return;
      }
    }
  }
  //マスターチェック2 部門マスタの検索条件
  $arg_str2 = "SELECT ";
  $arg_str2 .= " * ";
  $arg_str2 .= " FROM ";
  $arg_str2 .= "(SELECT * FROM t_import_job WHERE job_no = '".$job_no."') AS T1";
  $arg_str2 .= " WHERE NOT EXISTS ";
  $arg_str2 .= "(SELECT ";
  $arg_str2 .= " * ";
  $arg_str2 .= " FROM (SELECT * FROM m_section WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no') AS T2 ";
  $arg_str2 .= " WHERE T2.rntl_sect_cd = T1.rntl_sect_cd ) ";
  $results2 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str2));
  $result_obj2 = (array)$results2;
  $results_cnt2 = $result_obj2["\0*\0_count"];
  if (!empty($results_cnt2)) {
    $results_count = (count($results2));
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
      if (count($error_list) < 20) {
        $error_list[] =$result->line_no . '行目の支店コードが不正です。';
      } else {
        $json_list['errors'] = $error_list;
        $json_list["error_code"] = "1";
        echo json_encode($json_list);
        return;
      }
    }
  }
  //マスターチェック3 職種マスタの検索条件
  $arg_str3 = "SELECT ";
  $arg_str3 .= " * ";
  $arg_str3 .= " FROM ";
  $arg_str3 .= "(SELECT * FROM t_import_job WHERE job_no = '".$job_no."') AS T1";
  $arg_str3 .= " WHERE NOT EXISTS ";
  $arg_str3 .= "(SELECT ";
  $arg_str3 .= " * ";
  $arg_str3 .= " FROM (SELECT * FROM m_job_type WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no') AS T2 ";
  $arg_str3 .= " WHERE T2.job_type_cd = T1.rent_pattern_code ) ";
  $results3 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str3));
  $result_obj3 = (array)$results3;
  $results_cnt3 = $result_obj3["\0*\0_count"];
  if (!empty($results_cnt3)) {
    $results_count = (count($results3));
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
      if (count($error_list) < 20) {
        $error_list[] =$result->line_no . '行目の貸与パターンが不正です。';
      } else {
        $json_list['errors'] = $error_list;
        $json_list["error_code"] = "1";
        echo json_encode($json_list);
        return;
      }
    }
  }
  //マスターチェック4 発注区分はフォーマットチェックで実施済み
  //マスターチェック5 商品マスタの検索条件
  $arg_str5 = "SELECT ";
  $arg_str5 .= " * ";
  $arg_str5 .= " FROM ";
  $arg_str5 .= "(SELECT * FROM t_import_job WHERE job_no = '".$job_no."') AS T1";
  $arg_str5 .= " WHERE NOT EXISTS ";
  $arg_str5 .= "(SELECT ";
  $arg_str5 .= " * ";
  $arg_str5 .= " FROM (SELECT * FROM m_input_item WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no' AND job_type_cd = T1.rent_pattern_code ) AS T2 ";
  $arg_str5 .= " WHERE item_cd = T1.item_cd ) ";
  $results5 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str5));
  $result_obj5 = (array)$results5;
  $results_cnt5 = $result_obj5["\0*\0_count"];
  if (!empty($results_cnt5)) {
    $results_count = (count($results5));
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
      if (count($error_list) < 20) {
        $error_list[] =$result->line_no . '行目の商品コードが不正です。';
      } else {
        $json_list['errors'] = $error_list;
        $json_list["error_code"] = "1";
        echo json_encode($json_list);
        return;
      }
    }
  }
  //マスターチェック6  商品マスタのカラーコード検索条件
  $arg_str6 = "SELECT ";
  $arg_str6 .= " * ";
  $arg_str6 .= " FROM ";
  $arg_str6 .= "(SELECT * FROM t_import_job WHERE job_no = '".$job_no."') AS T1";
  $arg_str6 .= " WHERE NOT EXISTS ";
  $arg_str6 .= "(SELECT ";
  $arg_str6 .= " * ";
  $arg_str6 .= " FROM (SELECT * FROM m_input_item WHERE corporate_id = '$corporate_id' AND rntl_cont_no = '$agreement_no' AND job_type_cd = T1.rent_pattern_code ) AS T2 ";
  $arg_str6 .= " WHERE color_cd = T1.color_cd ) ";
  $results6 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str6));
  $result_obj6 = (array)$results6;
  $results_cnt6 = $result_obj6["\0*\0_count"];
  if (!empty($results_cnt6)) {
    $results_count = (count($results6));
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
      if (count($error_list) < 20) {
        $error_list[] =$result->line_no . '行目の色コードが不正です。';
      } else {
        $json_list['errors'] = $error_list;
        $json_list["error_code"] = "1";
        echo json_encode($json_list);
        return;
      }
    }
  }
  //C-3-5 社員コードごとの客先商品id(emply_order_req_no)の重複 検索条件
  $arg_str8 = "SELECT ";
  $arg_str8 .= " * ";
  $arg_str8 .= " FROM t_import_job";
  $arg_str8 .= " WHERE job_no = '".$job_no."' AND (cster_emply_cd,emply_order_req_no) IN(";
  $arg_str8 .= "SELECT cster_emply_cd,emply_order_req_no ";
  $arg_str8 .= "FROM t_import_job ";
  $arg_str8 .= "GROUP by cster_emply_cd,emply_order_req_no ";
  $arg_str8 .= "HAVING count(*) > 1 ) ";
  $results8 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str8));
  $result_obj8 = (array)$results8;
  $results_cnt8 = $result_obj8["\0*\0_count"];
  if (!empty($results_cnt8)) {
    $results_count = (count($results8));
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
      if (count($error_list) < 20) {
        $error_list[] =$result->line_no . '行目のお客様発注Noが、重複されて使用されています。';
      } else {
        $json_list['errors'] = $error_list;
        $json_list["error_code"] = "1";
        echo json_encode($json_list);
        return;
      }
    }
  }
  //C-3-9 お客様発注Noが発注情報、発注情報トランに存在しないこと
  $arg_str9 = "SELECT ";
  $arg_str9 .= " * ";
  $arg_str9 .= " FROM ";
  $arg_str9 .= "(SELECT * FROM t_import_job WHERE job_no = '".$job_no."') AS T1";
  $arg_str9 .= " WHERE EXISTS ";
  $arg_str9 .= "(SELECT * FROM t_order AS T2 ";
  $arg_str9 .= "WHERE emply_order_req_no = T1.emply_order_req_no) ";
  $arg_str9 .= " OR EXISTS ";
  $arg_str9 .= "(SELECT * FROM t_order_tran AS T3 ";
  $arg_str9 .= "WHERE emply_order_req_no = T1.emply_order_req_no)";
  $results9 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str9));
  $result_obj9 = (array)$results9;
  $results_cnt9 = $result_obj9["\0*\0_count"];
  if (!empty($results_cnt9)) {
    $results_count = (count($results9));
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
      if (count($error_list) < 20) {
        $error_list[] =$result->line_no . '行目の発注Noは、既に発注で使用されています。';
      } else {
        $json_list['errors'] = $error_list;
        $json_list["error_code"] = "1";
        echo json_encode($json_list);
        return;
      }
    }
  }
  //C-3-7 貸与パターン別商品チェック　発注区分が貸与（１）の場合、社員番号単位に貸与パターンで指定された商品がファイル内に指定されていること。
  $arg_str10 = "SELECT ";
  $arg_str10 .= " * ";
  $arg_str10 .= " FROM (SELECT * FROM t_import_job WHERE job_no = '".$job_no."' AND order_kbn = '1') as T1 ";
  $arg_str10 .= " WHERE NOT EXISTS ";
  $arg_str10 .= "(SELECT * FROM ( SELECT m_input_item.*,size_cd FROM m_input_item ";
  $arg_str10 .= "INNER JOIN m_item ON m_input_item.item_cd = m_item.item_cd AND m_input_item.color_cd = m_item.color_cd AND m_input_item.corporate_id = m_item.corporate_id ";
  $arg_str10 .= "WHERE m_input_item.corporate_id = '$corporate_id' AND m_input_item.rntl_cont_no = '$agreement_no' ) as T2 ";
  $arg_str10 .= "WHERE T2.item_cd = T1.item_cd AND T2.color_cd = T1.color_cd AND T2.std_input_qty = CAST(T1.quantity as integer) AND T2.size_cd = T1.size_cd )";
  $results10 = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str10));
  $result_obj10 = (array)$results10;
  $results_cnt10 = $result_obj10["\0*\0_count"];
  if (!empty($results_cnt10)) {
    $results_count = (count($results10));
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
      if (count($error_list) < 20) {
        $error_list[] = $result->line_no . '行目の社員番号' . $result->cster_emply_cd . 'の貸与パターンの商品指定が不足しています。';
      } else {
        $json_list['errors'] = $error_list;
        $json_list["error_code"] = "1";
        echo json_encode($json_list);
        return;
      }
    }
  }
  // マスターチェック処理で異常が発生した場合、以降処理せず終了
  if (!empty($error_list)) {
    $json_list['errors'] = $error_list;
    $json_list["error_code"] = "1";
    echo json_encode($json_list);
    return;
  }
  //--マスターチェック処理--ここまで//

  //--発注NGパターンチェック--ここから//
  $import_job_list = array();
  $list = array();
  $arg_str = "";
  $arg_str = "SELECT ";
  $arg_str .= " * ";
  $arg_str .= " FROM ";
  $arg_str .= "(SELECT DISTINCT ON (werer_cd)";
  $arg_str .= " * ";
  $arg_str .= " FROM t_import_job";
  $arg_str .= " WHERE ";
  $arg_str .= " job_no = '".$job_no."'";
  $arg_str .= ") as distinct_table";
  $arg_str .= " ORDER BY line_no ASC";
  $results = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
  $result_obj = (array)$results;
  $results_cnt = $result_obj["\0*\0_count"];
  if (!empty($results_cnt)) {
    $paginator_model = new PaginatorModel(
      array(
        "data" => $results,
        "limit" => $results_cnt,
        "page" => 1
      )
    );
    $paginator = $paginator_model->getPaginate();
    $results = $paginator->items;
    foreach ($results as $result) {
      // 発注情報トラン情報参照
      $query_list = array();
      $query_list[] =  "corporate_id = '".$auth['corporate_id']."'";
      $query_list[] =  "rntl_cont_no = '".$agreement_no."'";
      $query_list[] =  "werer_cd = '".$result->werer_cd."'";
      // 発注状況区分(終了、異動)
      $query_list[] = "order_sts_kbn IN ('2','5')";
      // 理由区分(貸与終了、職種変更または異動)
      $query_list[] = "order_reason_kbn IN ('05','06','08','09','10','11','20','24')";
      $query = implode(' AND ', $query_list);
      $arg_str = "";
      $arg_str = "SELECT ";
      $arg_str .= "*";
      $arg_str .= " FROM ";
      $arg_str .= "t_order_tran";
      $arg_str .= " WHERE ";
      $arg_str .= $query;
      $t_order_results = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
      $result_obj = (array)$t_order_results;
      $t_order_cnt = $result_obj["\0*\0_count"];
      if ($t_order_cnt > 0) {
        // 発注情報トランに貸与終了、職種変更または異動のデータが存在する場合
        $paginator_model = new PaginatorModel(
            array(
                "data" => $t_order_results,
                "limit" => 1,
                "page" => 1
            )
        );
        $paginator = $paginator_model->getPaginate();
        $t_order_results = $paginator->items;
        foreach ($t_order_results as $t_order_result) {
          // 貸与終了の場合
          if ($t_order_result->order_sts_kbn == "2") {
            if (count($error_list) < 20) {
              $err_msg = $result->line_no.'行目の社員番号'.$result->cster_emply_cd.'は、貸与終了の発注がされています。'.PHP_EOL;
              $err_msg .= '他行の社員番号'.$result->cster_emply_cd.'がある場合も同じくご確認ください。';
              $error_list[] = $err_msg;
            } else {
              $json_list['errors'] = $error_list;
              $json_list["error_code"] = "1";
              echo json_encode($json_list);
              exit;
            }
          }
          // 職種変更または異動の場合
          if ($t_order_result->order_sts_kbn == "5") {
            if (count($error_list) < 20) {
              $err_msg = $result->line_no.'行目の社員番号'.$result->cster_emply_cd.'は、職種変更または異動の発注がされています。'.PHP_EOL;
              $err_msg .= '他行の社員番号'.$result->cster_emply_cd.'がある場合も同じくご確認ください。';
              $error_list[] = $err_msg;
            } else {
              $json_list['errors'] = $error_list;
              $json_list["error_code"] = "1";
              echo json_encode($json_list);
              exit;
            }
          }
        }
      }
    }
  } else {
    $error_list[] = "登録するデータが存在しなかったため、取込処理が中断されました。";
    $json_list['errors'] = $error_list;
    $json_list["error_code"] = "1";
    echo json_encode($json_list);
    return;
  }
  //--発注NGパターンチェック--ここまで//
  //$end = microtime(true);
  //$time = $end - $start;
  //$json_list["time"] = $time;
  //echo json_encode($json_list);
  //exit;

  //--各トラン情報登録処理--ここから//
  $t_import_job = new TImportJob();
  $transaction = new Resultset(NULL, $t_import_job, $t_import_job->getReadConnection()->query("begin"));
  try {
    // 着用者基本マスタトラン登録 ここから
    $arg_str = "";
    $arg_str = "SELECT DISTINCT ON (order_req_no) ";
    $arg_str .= "*";
    $arg_str .= " FROM t_import_job";
    $arg_str .= " WHERE ";
    $arg_str .= "job_no = '".$job_no."'";
    $arg_str .= " ORDER BY order_req_no ASC";
    $results = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if (!empty($results_cnt)) {
      $paginator_model = new PaginatorModel(
        array(
          "data" => $results,
          "limit" => $results_cnt,
          "page" => 1
        )
      );
      $paginator = $paginator_model->getPaginate();
      $results = $paginator->items;
      $values_querys = array();
      foreach ($results as $result) {
        // 着用者基本マスタトラン登録用VALUES設定
        $values_list = array();

        // 出荷先、出荷先支店コード取得
        if ($result->order_kbn == "1" || $result->order_kbn == "5") {
          $query_list = array();
          $query_list[] = "corporate_id = '".$auth['corporate_id']."'";
          $query_list[] = "rntl_cont_no = '".$agreement_no."'";
          $query_list[] = "rntl_sect_cd = '".$result->rntl_sect_cd."'";
          $query = implode(' AND ', $query_list);
          $arg_str = "";
          $arg_str = "SELECT ";
          $arg_str .= "std_ship_to_cd,";
          $arg_str .= "std_ship_to_brnch_cd";
          $arg_str .= " FROM ";
          $arg_str .= "m_section";
          $arg_str .= " WHERE ";
          $arg_str .= $query;
          $m_section_results = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
          $result_obj = (array)$m_section_results;
          $results_cnt = $result_obj["\0*\0_count"];
          if (!empty($results_cnt)) {
            $paginator_model = new PaginatorModel(
              array(
                "data" => $m_section_results,
                "limit" => 1,
                "page" => 1
              )
            );
            $paginator = $paginator_model->getPaginate();
            $m_section_results = $paginator->items;
            foreach ($m_section_results as $m_section_result) {
              $result->ship_to_cd = $m_section_result->std_ship_to_cd;
              $result->ship_to_brnch_cd = $m_section_result->std_ship_to_brnch_cd;
            }
          } else {
            $result->ship_to_cd = ' ';
            $result->ship_to_brnch_cd = ' ';
          }
        } else {
          $result->ship_to_cd = ' ';
          $result->ship_to_brnch_cd = ' ';
        }

        $m_wearer_std_comb_hkey = md5(
          $auth['corporate_id']
          .$result->werer_cd
          .$agreement_no
          .$result->rntl_sect_cd
          .$result->rent_pattern_code
        );
        $values_list[] = "'".$m_wearer_std_comb_hkey."'";
        $values_list[] = "'".$auth['corporate_id']."'";
        $values_list[] = "'".$result->werer_cd."'";
        $values_list[] = "'".$agreement_no."'";
        $values_list[] = "'".$result->rntl_sect_cd."'";
        $values_list[] = "'".$result->rent_pattern_code."'";
        $values_list[] = "'".$result->cster_emply_cd."'";
        $values_list[] = "'".$result->werer_name."'";
        $values_list[] = "'".$result->werer_name_kana."'";
        $values_list[] = "'".$result->sex_kbn."'";
        $values_list[] = "'7'";
        $values_list[] = "'".$result->wear_start."'";
        $values_list[] = "'".$result->ship_to_cd."'";
        $values_list[] = "'".$result->ship_to_brnch_cd."'";
        $values_list[] = "'".$result->order_kbn."'";
        $values_list[] = "'7'";
        $values_list[] = "'".date("Y-m-d H:i:s", time())."'";
        $values_list[] = "'0'";
        $values_list[] = "'".date("Y-m-d H:i:s", time())."'";
        $values_list[] = "'0'";
        $values_list[] = "'".date("Y-m-d H:i:s", time())."'";
        $values_list[] = "'".$result->user_id."'";
        $values_list[] = "'".date("Y-m-d H:i:s", time())."'";
        $values_list[] = "'".$result->user_id."'";
        $values_list[] = "'".$result->user_id."'";
        $m_job_type_comb_hkey = md5(
          $auth['corporate_id']
          .$agreement_no
          .$result->rent_pattern_code
        );
        $values_list[] = "'".$m_job_type_comb_hkey."'";
        $m_section_comb_hkey = md5(
          $auth['corporate_id']
          .$agreement_no
          .$result->rntl_sect_cd
        );
        $values_list[] = "'".$m_section_comb_hkey."'";
        $values_list[] = "'".date("Y-m-d H:i:s", time())."'";
        $values_list[] = "'".$result->order_req_no."'";
        $values = implode(",", $values_list);
        $values = "(".$values.")";
        $values_querys[] = $values;
      }

      $values_query = implode(",", $values_querys);
    } else {
      $error_list[] = "登録するデータが存在しなかったため、取込処理が中断されました。";
      $json_list['errors'] = $error_list;
      $json_list["error_code"] = "1";
      echo json_encode($json_list);
      return;
    }

    // CALUME設定
    $calum_list = array(
      "m_wearer_std_comb_hkey",
      "corporate_id",
      "werer_cd",
      "rntl_cont_no",
      "rntl_sect_cd",
      "job_type_cd",
      "cster_emply_cd",
      "werer_name",
      "werer_name_kana",
      "sex_kbn",
      "werer_sts_kbn",
      "resfl_ymd",
      "ship_to_cd",
      "ship_to_brnch_cd",
      "order_sts_kbn",
      "upd_kbn",
      "web_upd_date",
      "snd_kbn",
      "snd_date",
      "del_kbn",
      "rgst_date",
      "rgst_user_id",
      "upd_date",
      "upd_user_id",
      "upd_pg_id",
      "m_job_type_comb_hkey",
      "m_section_comb_hkey",
      "appointment_ymd",
      "order_req_no",
    );
    $calum_query = implode(",", $calum_list);

    $arg_str = "";
    $arg_str = "INSERT INTO m_wearer_std_tran";
    $arg_str .= "(".$calum_query.")";
    $arg_str .= " VALUES ";
    $arg_str .= $values_query;
    //ChromePhp::LOG($arg_str);
    $results = new Resultset(NULL, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
    // 着用者基本マスタトラン登録 ここまで

    // 発注情報トラン登録 ここから
    $query_list = array();
    $kbn_list = array();
    $query_list[] = "job_no = '".$job_no."'";
    $kbn_list[] = "order_kbn = '1'";
    $kbn_list[] = "order_kbn = '5'";
    $or_query = implode(' OR ', $kbn_list);
    $query_list[] = "(".$or_query.")";
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "t_import_job";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY order_req_no, order_req_line_no ASC";
    $results = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if (!empty($results_cnt)) {
      $paginator_model = new PaginatorModel(
        array(
          "data" => $results,
          "limit" => $results_cnt,
          "page" => 1
        )
      );
      $paginator = $paginator_model->getPaginate();
      $results = $paginator->items;
      $values_querys = array();
      foreach ($results as $result) {
        // 発注情報トラン登録用VALUES設定
        $values_list = array();

        // 出荷先、出荷先支店コード取得
        $query_list = array();
        $query_list[] = "corporate_id = '".$auth['corporate_id']."'";
        $query_list[] = "rntl_cont_no = '".$agreement_no."'";
        $query_list[] = "rntl_sect_cd = '".$result->rntl_sect_cd."'";
        $query = implode(' AND ', $query_list);
        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "std_ship_to_cd,";
        $arg_str .= "std_ship_to_brnch_cd";
        $arg_str .= " FROM ";
        $arg_str .= "m_section";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $m_section_results = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
        $result_obj = (array)$m_section_results;
        $results_cnt = $result_obj["\0*\0_count"];
        if (!empty($results_cnt)) {
          $paginator_model = new PaginatorModel(
            array(
              "data" => $m_section_results,
              "limit" => 1,
              "page" => 1
            )
          );
          $paginator = $paginator_model->getPaginate();
          $m_section_results = $paginator->items;
          foreach ($m_section_results as $m_section_result) {
            $result->ship_to_cd = $m_section_result->std_ship_to_cd;
            $result->ship_to_brnch_cd = $m_section_result->std_ship_to_brnch_cd;
          }
        } else {
          $result->ship_to_cd = ' ';
          $result->ship_to_brnch_cd = ' ';
        }
        // 職種アイテムコード取得
        $query_list = array();
        $query_list[] = "corporate_id = '".$auth['corporate_id']."'";
        $query_list[] = "rntl_cont_no = '".$agreement_no."'";
        $query_list[] = "job_type_cd = '".$result->rent_pattern_code."'";
        $query_list[] = "item_cd = '".$result->item_cd."'";
        $query_list[] = "color_cd = '".$result->color_cd."'";
        $query = implode(' AND ', $query_list);
        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "job_type_item_cd";
        $arg_str .= " FROM ";
        $arg_str .= "m_input_item";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $m_input_item_results = new Resultset(null, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));
        $result_obj = (array)$m_input_item_results;
        $results_cnt = $result_obj["\0*\0_count"];
        if (!empty($results_cnt)) {
          $paginator_model = new PaginatorModel(
            array(
              "data" => $m_input_item_results,
              "limit" => 1,
              "page" => 1
            )
          );
          $paginator = $paginator_model->getPaginate();
          $m_input_item_results = $paginator->items;
          foreach ($m_input_item_results as $m_input_item_result) {
            $result->job_type_item_cd = $m_input_item_result->job_type_item_cd;
          }
        } else {
          $result->job_type_item_cd = '';
        }

        $t_order_comb_hkey = md5(
          $auth['corporate_id']
          .$result->order_req_no
          .$result->order_req_line_no
        );
        $values_list[] = "'".$t_order_comb_hkey."'";
        $values_list[] = "'".$auth['corporate_id']."'";
        $values_list[] = "'".$result->order_req_no."'";
        $values_list[] = "'".$result->order_req_line_no."'";
        $values_list[] = "'".date("Y-m-d H:i:s", time())."'";
        $values_list[] = "'".$result->order_kbn."'";
        $values_list[] = "'".$agreement_no."'";
        $values_list[] = "'".$result->rntl_sect_cd."'";
        $values_list[] = "'".$result->rent_pattern_code."'";
        $values_list[] = "'".$result->job_type_item_cd."'";
        $values_list[] = "'".$result->werer_cd."'";
        $values_list[] = "'".$result->item_cd."'";
        $values_list[] = "'".$result->color_cd."'";
        $values_list[] = "'".$result->size_cd."'";
        $values_list[] = "' '";
        $values_list[] = "' '";
        $values_list[] = "' '";
        $values_list[] = "' '";
        $values_list[] = "'".$result->ship_to_cd."'";
        $values_list[] = "'".$result->ship_to_brnch_cd."'";
        $values_list[] = $result->quantity;
        $values_list[] = "'".$result->message."'";
        $values_list[] = "'".$result->werer_name."'";
        $values_list[] = "'".$result->cster_emply_cd."'";
        $values_list[] = "'7'";
        $values_list[] = "'".$result->wear_start."'";
        $values_list[] = "'0'";
        $values_list[] = "'".date("Y-m-d H:i:s", time())."'";
        $values_list[] = "'0'";
        $values_list[] = "'".date("Y-m-d H:i:s", time())."'";
        $values_list[] = "'".$result->user_id."'";
        $values_list[] = "'".date("Y-m-d H:i:s", time())."'";
        $values_list[] = "'".$result->user_id."'";
        $values_list[] = "'".$result->user_id."'";
        $values_list[] = "'1'";
        $values_list[] = "'".$result->emply_order_req_no."'";
        $values_list[] = "'".$result->order_reason_kbn."'";
        $m_item_comb_hkey = md5(
          $auth['corporate_id']
          .$result->item_cd
          .$result->color_cd
          .$result->size_cd
        );
        $values_list[] = "'".$m_item_comb_hkey."'";
        $m_job_type_comb_hkey = md5(
          $auth['corporate_id']
          .$agreement_no
          .$result->rent_pattern_code
        );
        $values_list[] = "'".$m_job_type_comb_hkey."'";
        $m_section_comb_hkey = md5(
          $auth['corporate_id']
          .$agreement_no
          .$result->rntl_sect_cd
        );
        $values_list[] = "'".$m_section_comb_hkey."'";
        $m_wearer_std_comb_hkey = md5(
          $auth['corporate_id']
          .$result->werer_cd
          .$agreement_no
          .$result->rntl_sect_cd
          .$result->rent_pattern_code
        );
        $values_list[] = "'".$m_wearer_std_comb_hkey."'";
        $m_wearer_item_comb_hkey = md5(
          $auth['corporate_id']
          .$result->werer_cd
          .$agreement_no
          .$result->rntl_sect_cd
          .$result->rent_pattern_code
          .$result->job_type_item_cd
          .$result->item_cd
          .$result->color_cd
          .$result->size_cd
        );
        $values_list[] = "'".$m_wearer_item_comb_hkey."'";
        $values_list[] = "'".date("Y-m-d H:i:s", time())."'";
        $values = implode(",", $values_list);
        $values = "(".$values.")";
        $values_querys[] = $values;
      }

      $values_query = implode(",", $values_querys);
    } else {
      $error_list[] = "登録するデータが存在しなかったため、取込処理が中断されました。";
      $json_list['errors'] = $error_list;
      $json_list["error_code"] = "1";
      echo json_encode($json_list);
      return;
    }

    // 発注情報トラン登録
    // CALUME設定
    $calum_list = array(
      "t_order_comb_hkey",
      "corporate_id",
      "order_req_no",
      "order_req_line_no",
      "order_req_ymd",
      "order_sts_kbn",
      "rntl_cont_no",
      "rntl_sect_cd",
      "job_type_cd",
      "job_type_item_cd",
      "werer_cd",
      "item_cd",
      "color_cd",
      "size_cd",
      "size_two_cd",
      "whse_cd",
      "stk_usr_cd",
      "stk_usr_brnch_cd",
      "ship_to_cd",
      "ship_to_brnch_cd",
      "order_qty",
      "memo",
      "werer_name",
      "cster_emply_cd",
      "werer_sts_kbn",
      "resfl_ymd",
      "snd_kbn",
      "snd_date",
      "del_kbn",
      "rgst_date",
      "rgst_user_id",
      "upd_date",
      "upd_user_id",
      "upd_pg_id",
      "order_status",
      "emply_order_req_no",
      "order_reason_kbn",
      "m_item_comb_hkey",
      "m_job_type_comb_hkey",
      "m_section_comb_hkey",
      "m_wearer_std_comb_hkey",
      "m_wearer_item_comb_hkey",
      "appointment_ymd"
    );
    $calum_query = implode(",", $calum_list);

    // 発注情報トラン登録 ここまで
    $arg_str = "";
    $arg_str = "INSERT INTO t_order_tran";
    $arg_str .= "(".$calum_query.")";
    $arg_str .= " VALUES ";
    $arg_str .= $values_query;
    //ChromePhp::LOG($arg_str);
    $results = new Resultset(NULL, $t_import_job, $t_import_job->getReadConnection()->query($arg_str));

    // トランザクション-コミット
    $transaction = new Resultset(NULL, $t_import_job, $t_import_job->getReadConnection()->query("commit"));
  } catch (Exception $e) {
    // トランザクション-ロールバック
    $transaction = new Resultset(NULL, $t_import_job, $t_import_job->getReadConnection()->query("rollback"));

    //ChromePhp::log($e);
    $error_list[] = 'E002 取込処理中に予期せぬエラーが発生しました。';
    $json_list['errors'] = $error_list;
    $json_list["error_code"] = "1";
    echo json_encode($json_list);
    exit;
  }
  //--各トラン情報登録処理--ここまで//
  //$end = microtime(true);
  //$time = $end - $start;
  //$json_list["time"] = $time;

  echo json_encode($json_list);
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
function error_msg_format($line_cnt, $item_name) {
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
function error_msg_master($line_cnt, $item_name) {
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
function chk_format($error_list, $line_list, $line_cnt) {
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
  }
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
function chk_pattern($val, $pattaern) {
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

function byte_cnv($data) {
  //変換前文字コード
  $bf = 'UTF-8';
  //変換後文字コード
  $af = 'Shift-JIS';

  return strlen(bin2hex(mb_convert_encoding($data, $af, $bf))) / 2;
}
