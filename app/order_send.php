<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * 発注送信処理
 */
$app->post('/order_send', function () use ($app) {
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    $cond = $params['cond'];
    $page = $params['page'];

    $json_list = array();

    $query_list = array();
    $query_list[] = "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'";
    if (!empty($cond['agreement_no'])) {
      $query_list[] = "m_wearer_std_tran.rntl_cont_no = '".$cond['agreement_no']."'";
    }
    if (!empty($cond['cster_emply_cd'])) {
      $query_list[] = "m_wearer_std_tran.cster_emply_cd LIKE '".$cond['cster_emply_cd']."%'";
    }
    if (!empty($cond['werer_name'])) {
      $query_list[] = "m_wearer_std_tran.werer_name LIKE '%".$cond['werer_name']."%'";
    }
    if (!empty($cond['sex_kbn'])) {
      $query_list[] = "m_wearer_std_tran.sex_kbn = '".$cond['sex_kbn']."'";
    }
    if (!empty($cond['section'])) {
      $query_list[] = "m_wearer_std_tran.rntl_sect_cd = '".$cond['section']."'";
    }
    if (!empty($cond['job_type'])) {
      $query_list[] = "m_wearer_std_tran.job_type_cd = '".$cond['job_type']."'";
    }
    if (isset($cond['snd_kbn'])) {
      $query_list[] = "m_wearer_std_tran.snd_kbn = '".$cond['snd_kbn']."'";
    }
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str .= "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (m_wearer_std_tran.order_req_no) ";
    $arg_str .= "m_wearer_std_tran.order_req_no as as_wst_order_req_no,";
    $arg_str .= "m_wearer_std_tran.werer_cd as as_werer_cd,";
    $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";
    $arg_str .= "m_wearer_std_tran.order_sts_kbn as as_wst_order_sts_kbn,";
    $arg_str .= "m_wearer_std_tran.snd_kbn as as_wst_snd_kbn,";
    $arg_str .= "m_wearer_std_tran.corporate_id as as_corporate_id,";
    $arg_str .= "m_wearer_std_tran.rntl_cont_no as as_rntl_cont_no,";
    $arg_str .= "m_wearer_std_tran.rntl_sect_cd as as_rntl_sect_cd,";
    $arg_str .= "m_wearer_std_tran.job_type_cd as as_job_type_cd,";
    $arg_str .= "m_wearer_std_tran.rgst_date as as_rgst_date,";
    $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
    $arg_str .= "m_job_type.job_type_name as as_job_type_name,";
    $arg_str .= "t_order_tran.order_req_no as as_order_req_no,";
    $arg_str .= "t_order_tran.order_req_ymd as as_order_req_ymd,";
    $arg_str .= "t_order_tran.order_sts_kbn as as_order_sts_kbn,";
    $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";
    $arg_str .= "t_order_tran.snd_kbn as as_snd_kbn,";
    $arg_str .= "t_returned_plan_info_tran.order_req_no as as_rtn_order_req_no";
    $arg_str .= " FROM ";
    $arg_str .= "(m_wearer_std_tran";
    $arg_str .= " INNER JOIN m_section";
    $arg_str .= " ON (m_wearer_std_tran.corporate_id = m_section.corporate_id";
    $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = m_section.rntl_cont_no";
    $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = m_section.rntl_sect_cd)";
    $arg_str .= " INNER JOIN m_job_type";
    $arg_str .= " ON (m_wearer_std_tran.corporate_id = m_job_type.corporate_id";
    $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = m_job_type.rntl_cont_no";
    $arg_str .= " AND m_wearer_std_tran.job_type_cd = m_job_type.job_type_cd))";
    $arg_str .= " LEFT JOIN ";
    $arg_str .= "t_order_tran";
    $arg_str .= " ON m_wearer_std_tran.order_req_no = t_order_tran.order_req_no";
    $arg_str .= " LEFT JOIN ";
    $arg_str .= "t_returned_plan_info_tran";
    $arg_str .= " ON m_wearer_std_tran.order_req_no = t_returned_plan_info_tran.order_req_no";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_wst_order_req_no ASC";
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if(!empty($results_cnt)) {
      $paginator_model = new PaginatorModel(
        array(
          "data" => $results,
          "limit" => $page['records_per_page'],
          "page" => $page['page_number']
        )
      );
      $paginator = $paginator_model->getPaginate();
      $results = $paginator->items;
    }

    $list = array();
    $all_list = array();
    if (!empty($results_cnt)) {
      foreach ($results as $result) {
        // 発注No-着用者基本マスタトラン
        if (!empty($result->as_wst_order_req_no)) {
          $list['wst_order_req_no'] = $result->as_wst_order_req_no;
        } else {
          $list['wst_order_req_no'] = "";
        }
        // 発注No-発注情報トラン
        if (!empty($result->as_order_req_no)) {
          $list['order_req_no'] = $result->as_order_req_no;
        } else {
          $list['order_req_no'] = "";
        }
        // 発注No-返却予定情報トラン
        if (!empty($result->as_order_req_no)) {
          $list['rtn_order_req_no'] = $result->as_rtn_order_req_no;
        } else {
          $list['rtn_order_req_no'] = "";
        }
        // 発注No-返却予定情報トラン
        if (!empty($result->as_order_req_no)) {
          $list['rtn_order_req_no'] = $result->as_rtn_order_req_no;
        } else {
          $list['rtn_order_req_no'] = "";
        }
        // 表示用発注No
        $list['disp_order_req_no'] = $result->as_wst_order_req_no;
        // 発注依頼日
        if (!empty($result->as_order_req_ymd)) {
          $list['order_req_ymd'] = date("Y/m/d", strtotime($result->as_order_req_ymd));
        } else {
          $list['order_req_ymd'] = date("Y/m/d", strtotime($result->as_rgst_date));
        }
        // 企業ID
        $list['corporate_id'] = $result->as_corporate_id;
        // レンタル契約no
        $list['rntl_cont_no'] = $result->as_rntl_cont_no;
        // レンタル部門コード
        $list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
        // 職種コード
        $list['job_type_cd'] = $result->as_job_type_cd;
        // 着用者コード
        $list['werer_cd'] = $result->as_werer_cd;
        // 発注状況区分
        $list['order_sts_kbn'] = $result->as_wst_order_sts_kbn;
        // 理由区分
        if (isset($result->as_order_reason_kbn)) {
            $list['order_reason_kbn'] = $result->as_order_reason_kbn;
        } else {
            $list['order_reason_kbn'] = "";
        }
        // 送信区分
        if (!empty($result->as_order_snd_kbn)) {
          $list['order_snd_kbn'] = $result->as_order_snd_kbn;
        } else {
          $list['order_snd_kbn'] = $result->as_wst_snd_kbn;
        }
        // 社員番号
        if (isset($result->as_cster_emply_cd)) {
            $list['cster_emply_cd'] = $result->as_cster_emply_cd;
        } else {
            $list['cster_emply_cd'] = "";
        }
        // 着用者名
        if (!empty($result->as_werer_name)) {
            $list['werer_name'] = $result->as_werer_name;
        } else {
            $list['werer_name'] = "";
        }
        // 性別区分
        $list['sex_kbn'] = $result->as_sex_kbn;
        //---性別名称---//
        $query_list = array();
        $query_list[] = "cls_cd = '004'";
        $query_list[] = "gen_cd = '".$result->as_sex_kbn."'";
        $query = implode(' AND ', $query_list);
        $gencode = MGencode::query()
            ->where($query)
            ->columns('*')
            ->execute();
        foreach ($gencode as $gencode_map) {
          $list['sex_kbn_name'] = $gencode_map->gen_name;
        }
        //---発注区分---//
        $query_list = array();
        $query_list[] = "cls_cd = '001'";
        if (!empty($result->as_order_sts_kbn)) {
          $query_list[] = "gen_cd = '".$result->as_order_sts_kbn."'";
        } else {
          $query_list[] = "gen_cd = '".$result->as_wst_order_sts_kbn."'";
        }
        $query = implode(' AND ', $query_list);
        $gencode = MGencode::query()
            ->where($query)
            ->columns('*')
            ->execute();
        foreach ($gencode as $gencode_map) {
            $list['order_sts_kbn_name'] = $gencode_map->gen_name;
        }
        //---理由区分---//
        if (!empty($result->as_order_reason_kbn)) {
          $query_list = array();
          $query_list[] = "cls_cd = '002'";
          $query_list[] = "gen_cd = '".$result->as_order_reason_kbn."'";
          $query = implode(' AND ', $query_list);
          $gencode = MGencode::query()
              ->where($query)
              ->columns('*')
              ->execute();
          foreach ($gencode as $gencode_map) {
            $list['order_reason_kbn_name'] = "(".$gencode_map->gen_name.")";
            $list['br'] = "<br/>";
          }
        } else {
          $list['order_reason_kbn_name'] = "";
          $list['br'] = "";
        }
        // 状態
        $query_list = array();
        $query_list[] = "cls_cd = '026'";
        if (!empty($result->as_snd_kbn)) {
          $query_list[] = "gen_cd = '".$result->as_snd_kbn."'";
        } else {
          $query_list[] = "gen_cd = '".$result->as_wst_snd_kbn."'";
        }
        $query = implode(' AND ', $query_list);
        $gencode = MGencode::query()
            ->where($query)
            ->columns('*')
            ->execute();
        foreach ($gencode as $gencode_map) {
            $list['snd_kbn'] = $gencode_map->gen_name;
        }
        // 拠点
        if (!empty($result->as_rntl_sect_name)) {
            $list['rntl_sect_name'] = $result->as_rntl_sect_name;
        } else {
            $list['rntl_sect_name'] = "-";
        }
        // 貸与パターン
        if (!empty($result->as_job_type_name)) {
            $list['job_type_name'] = $result->as_job_type_name;
        } else {
            $list['job_type_name'] = "-";
        }
        // 選択チェックボックス表示
        if ($list['order_snd_kbn'] == '0') {
          $list['send_choice_chk'] = true;
        } else {
          $list['send_choice_chk'] = false;
        }
        //「発注送信キャンセル」ボタン表示
        if ($list['order_snd_kbn'] == '1') {
          $list['send_cancel_bottom'] = true;
        } else {
          $list['send_cancel_bottom'] = false;
        }
        //「発注取消」ボタン表示
        if ($list['order_snd_kbn'] == '0') {
          $list['order_delete_bottom'] = true;
        } else {
          $list['order_delete_bottom'] = false;
        }

        $all_list[] = $list;
      }
    }

    $page_list['records_per_page'] = $page['records_per_page'];
    $page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = $results_cnt;
    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;
    //ChromePhp::LOG($json_list);

    echo json_encode($json_list);
});

/**
 * 発注送信ボタン押した時の
 *
 */
$app->post('/order_change', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // パラメータ取得
    $order_data = $params['data'];
    //リスト作成
    $json_list = array();
    $error_list = array();

    if ($error_list) {
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);

        return true;
    }

    $transaction = $app->transactionManager->get();

    foreach ($order_data as $order) {

        $corporate_id = $order['corporate_id'];
        $werer_cd = $order['werer_cd'];
        $rntl_cont_no = $order['rntl_cont_no'];
        $job_type_cd = $order['job_type_cd'];
        $order_req_no = $order['order_req_no'];


        //着用者情報マスタトランの更新設定
        $m_wearer_std_tran_results = MWearerStdTran::find(array(
            'conditions' => "corporate_id = '$corporate_id' AND werer_cd = '$werer_cd' AND rntl_cont_no = '$rntl_cont_no' AND job_type_cd = '$job_type_cd'"
        ));
        $m_wearer_std_tran_results[0]->snd_kbn = 1;//送信済
        $m_wearer_std_tran_results[0]->upd_date = date('Y/m/d H:i:s.sss', time()); //更新日時

        $m_wearer_std_tran = $m_wearer_std_tran_results[0];


        //発注者情報トランの更新設定
        $t_order_tran_results = TOrderTran::find(array(
            'conditions' => "corporate_id = '$corporate_id' AND werer_cd = '$werer_cd' AND rntl_cont_no = '$rntl_cont_no' AND job_type_cd = '$job_type_cd' AND order_req_no = '$order_req_no'"
        ));


        if ($m_wearer_std_tran->save() == false) {
            $error_list['order'] = '送信区分の変更が失敗しました。';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            $transaction->rollBack();
            return;
        }

        foreach ($t_order_tran_results as $t_order_tran_value) {
            $t_order_tran_value->snd_kbn = 1;//送信済
            $t_order_tran_value->upd_date = date('Y/m/d H:i:s.sss', time()); //更新日時
            $t_order_tran = $t_order_tran_value;
            if ($t_order_tran->save() == false) {
                $error_list['order'] = '送信区分の変更が失敗しました。';
                $json_list['errors'] = $error_list;
                echo json_encode($json_list);
                $transaction->rollBack();
                return;
            }
        }
        //ChromePhp::LOG($t_order_tran_results);
        //ChromePhp::LOG($t_order_tran);

        //if ($t_order_tran->save() == false) {
        //    $error_list['order'] = '送信区分の変更が失敗しました。';
        //   $json_list['errors'] = $error_list;
        //   echo json_encode($json_list);
        //   $transaction->rollBack();
        //   return;
        // }

    }

    $transaction->commit();

    echo json_encode($json_list);
    return true;

});

/**
 * 発注送信キャンセルボタン押した時
 *
 */
$app->post('/order_send/cancel', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // パラメータ取得
    $order_data = $params['data'];
    //リスト作成
    $json_list = array();
    $error_list = array();

    if ($error_list) {
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);

        return true;
    }

    $transaction = $app->transactionManager->get();


        $corporate_id = $order_data['corporate_id'];
        $werer_cd = $order_data['werer_cd'];
        $rntl_cont_no = $order_data['rntl_cont_no'];
        $job_type_cd = $order_data['job_type_cd'];
        $order_req_no = $order_data['order_req_no'];


        //着用者情報マスタトランの更新設定
        $m_wearer_std_tran_results = MWearerStdTran::find(array(
            'conditions' => "corporate_id = '$corporate_id' AND werer_cd = '$werer_cd' AND rntl_cont_no = '$rntl_cont_no' AND job_type_cd = '$job_type_cd'"
        ));
        $m_wearer_std_tran_results[0]->snd_kbn = 0;//送信済
        $m_wearer_std_tran_results[0]->upd_date = date('Y/m/d H:i:s.sss', time()); //更新日時

        $m_wearer_std_tran = $m_wearer_std_tran_results[0];


        //発注者情報トランの更新設定
        $t_order_tran_results = TOrderTran::find(array(
            'conditions' => "corporate_id = '$corporate_id' AND werer_cd = '$werer_cd' AND rntl_cont_no = '$rntl_cont_no' AND job_type_cd = '$job_type_cd' AND order_req_no = '$order_req_no'"
        ));


        if ($m_wearer_std_tran->save() == false) {
            $error_list['order'] = '送信区分の変更が失敗しました。';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            $transaction->rollBack();
            return;
        }

        foreach ($t_order_tran_results as $t_order_tran_value) {
            //ChromePhp::LOG($t_order_tran_value);
            $t_order_tran_value->snd_kbn = 0;//送信済
            $t_order_tran_value->upd_date = date('Y/m/d H:i:s.sss', time()); //更新日時
            $t_order_tran = $t_order_tran_value;
            if ($t_order_tran->save() == false) {
                $error_list['order'] = '送信区分の変更が失敗しました。';
                $json_list['errors'] = $error_list;
                echo json_encode($json_list);
                $transaction->rollBack();
                return;
            }
        }
        //ChromePhp::LOG($t_order_tran_results);
        //ChromePhp::LOG($t_order_tran);

        //if ($t_order_tran->save() == false) {
        //    $error_list['order'] = '送信区分の変更が失敗しました。';
        //   $json_list['errors'] = $error_list;
        //   echo json_encode($json_list);
        //   $transaction->rollBack();
        //   return;
        // }


    $transaction->commit();

    echo json_encode($json_list);
    return true;

});
