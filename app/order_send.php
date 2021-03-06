<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * 発注送信処理
 * 検索
 */
$app->post('/order_send/search', function () use ($app) {
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    $cond = $params['cond'];
    $page = $params['page'];

    $json_list = array();

    //（前処理）契約リソースマスタ参照、拠点コード「0」埋めデータ確認
    $query_list = array();
    $list = array();
    $all_list = array();
    $query_list[] = "corporate_id = '".$auth["corporate_id"]."'";
    $query_list[] = "rntl_cont_no = '".$cond['agreement_no']."'";
    $query_list[] = "accnt_no = '".$auth["accnt_no"]."'";
    $query = implode(' AND ', $query_list);

    $arg_str = '';
    $arg_str .= 'SELECT ';
    $arg_str .= ' distinct on (rntl_sect_cd) *';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_contract_resource';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $m_contract_resource = new MContractResource();
    $results = new Resultset(null, $m_contract_resource, $m_contract_resource->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    if ($results_cnt > 0) {
      $paginator_model = new PaginatorModel(
        array(
          "data"  => $results,
          "limit" => $results_cnt,
          "page" => 1
        )
      );
      $paginator = $paginator_model->getPaginate();
      $results = $paginator->items;
      foreach ($results as $result) {
        $all_list[] = $result->rntl_sect_cd;
      }
    }
    if (in_array("0000000000", $all_list)) {
      $section_all_zero_flg = true;
    } else {
      $section_all_zero_flg = false;
    }


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
      $query_list[] = "(m_wearer_std_tran.rntl_sect_cd = '".$cond['section']."' OR t_order_tran.order_rntl_sect_cd = '".$cond['section']."')";
    }
    if (!empty($cond['job_type'])) {
      $query_list[] = "m_wearer_std_tran.job_type_cd = '".$cond['job_type']."'";
    }
    if (isset($cond['snd_kbn'])) {
      $query_list[] = "m_wearer_std_tran.snd_kbn = '".$cond['snd_kbn']."'";
    }
    if (!$section_all_zero_flg) {
        //ゼロ埋めがない場合、ログインアカウントの条件追加
            if(empty($cond['section'])) {
                if ($all_list > 0) {
                    $order_section = array();
                    $all_list_count = count($all_list);
                    for ($i = 0; $i < $all_list_count; $i++) {
                        //着用者区分
                        array_push($order_section, $all_list[$i]);
                    }
                    if (!empty($order_section)) {
                        $order_section_str = implode("','", $order_section);
                        $order_section_query = "t_order_tran.order_rntl_sect_cd IN ('" . $order_section_str . "')";
                    }
                    $rntl_accnt_no = "(m_contract_resource.accnt_no = '".$auth['accnt_no']."'";
                    $accnt_no_and_order_section = $rntl_accnt_no . " AND " . $order_section_query. ")";
                }
                $query_list[] = $accnt_no_and_order_section;
            }
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
    $arg_str .= "t_order_tran.order_rntl_sect_cd as as_order_rntl_sect_cd,";

    if (!$section_all_zero_flg) {
        $arg_str .= "m_contract_resource.update_ok_flg as as_update_ok_flg,";
    }
    $arg_str .= "t_order_tran.snd_kbn as as_snd_kbn,";
    $arg_str .= "t_returned_plan_info_tran.order_req_no as as_rtn_order_req_no";

    $arg_str .= " FROM ";
    $arg_str .= "(m_wearer_std_tran";
    if ($section_all_zero_flg) {
      $arg_str .= " LEFT JOIN ";
      $arg_str .= "m_section";
      $arg_str .= " ON (m_wearer_std_tran.corporate_id = m_section.corporate_id";
      $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = m_section.rntl_cont_no";
      $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = m_section.rntl_sect_cd)";
    } else {
      $arg_str .= " LEFT JOIN m_section";
      $arg_str .= " ON m_wearer_std_tran.corporate_id = m_section.corporate_id";
      $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = m_section.rntl_cont_no";
      $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = m_section.rntl_sect_cd";
      $arg_str .= " LEFT JOIN m_contract_resource";
      $arg_str .= " ON m_wearer_std_tran.corporate_id = m_contract_resource.corporate_id";
      $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = m_contract_resource.rntl_cont_no";
      $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = m_contract_resource.rntl_sect_cd";
      $arg_str .= " AND '".$auth['accnt_no']."' = m_contract_resource.accnt_no";
    }
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
    if(!empty($results_cnt)) {
      $paginator_model = new PaginatorModel(
        array(
          "data" => $results,
          "limit" => $results_cnt,
          "page" => 1
        )
      );
      $paginator = $paginator_model->getPaginate();
      $results = $paginator->items;
    }
    $list = array();
    $all_list = array();
    if (!empty($results_cnt)) {
      foreach ($results as $result) {

        // 発注区分=貸与で発注情報トランのデータが存在しない場合は対象外とする
        if ($result->as_wst_order_sts_kbn == "1" && empty($result->as_order_req_no)) {
          continue;
        }

          //発注種別に関わらず、着用者情報が編集されていたらそれを表示する
          $arg_str = "";
          $arg_str .= "SELECT ";
          $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
          $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";
          $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
          $arg_str .= "m_wearer_std_tran.rntl_sect_cd as as_rntl_sect_cd,";
          $arg_str .= "m_wearer_std_tran.job_type_cd as as_job_type_cd,";
          $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
          $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
          $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn";
          $arg_str .= " FROM m_wearer_std_tran";
          $arg_str .= " WHERE ";
          $arg_str .= " corporate_id = '".$result->as_corporate_id."'";
          $arg_str .= " AND werer_cd = '".$result->as_werer_cd."'";
          $arg_str .= " AND rntl_cont_no = '".$result->as_rntl_cont_no."'";
          $arg_str .= " AND order_sts_kbn = '6'";
          $m_weare_std_tran = new MWearerStdTran();
          $tran_results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
          $tran_result_obj = (array)$tran_results;
          $tran_results_cnt = $tran_result_obj["\0*\0_count"];

          // 着用者基本マスタトラン情報に重複データがある場合、優先させて着用者基本マスタ情報リストを上書きする
          if (!empty($tran_results_cnt)) {

              $paginator_model = new PaginatorModel(
                  array(
                      "data" => $tran_results,
                      "limit" => 1,
                      "page" => 1
                  )
              );
              $paginator = $paginator_model->getPaginate();
              $tran_results = $paginator->items;

              foreach ($tran_results as $tran_result) {
                  $result->as_cster_emply_cd = $tran_result->as_cster_emply_cd;
                  $result->as_sex_kbn = $tran_result->as_sex_kbn;
                  $result->as_werer_name = $tran_result->as_werer_name;
              }
          }
          //職種変更または異動されていた場合に着用者情報(拠点、貸与パターン)上書き
          $arg_str = "";
          $arg_str .= "SELECT ";
          $arg_str .= "wst.rntl_sect_name as wst_rntl_sect_name,";
          $arg_str .= "wjt.job_type_name as wjt_job_type_name,";
          $arg_str .= "m_wearer_std_tran.ship_to_cd as as_ship_to_cd,";
          $arg_str .= "m_wearer_std_tran.ship_to_brnch_cd as as_ship_to_brnch_cd";
          $arg_str .= " FROM ";
          if ($section_all_zero_flg) {
              $arg_str .= "m_wearer_std_tran INNER JOIN m_section as wst";
              $arg_str .= " ON m_wearer_std_tran.corporate_id = wst.corporate_id";
              $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wst.rntl_cont_no";
              $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = wst.rntl_sect_cd";
              $arg_str .= " INNER JOIN m_job_type as wjt";
              $arg_str .= " ON m_wearer_std_tran.corporate_id = wjt.corporate_id";
              $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wjt.rntl_cont_no";
              $arg_str .= " AND m_wearer_std_tran.job_type_cd = wjt.job_type_cd";
          } else {
              $arg_str .= "m_wearer_std_tran INNER JOIN (m_section as wst";
              $arg_str .= " INNER JOIN m_contract_resource as wcr";
              $arg_str .= " ON wst.corporate_id = wcr.corporate_id";
              $arg_str .= " AND wst.rntl_cont_no = wcr.rntl_cont_no";
              $arg_str .= " AND wst.rntl_sect_cd = wcr.rntl_sect_cd)";
              $arg_str .= " ON m_wearer_std_tran.corporate_id = wst.corporate_id";
              $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wst.rntl_cont_no";
              $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = wst.rntl_sect_cd";
              $arg_str .= " INNER JOIN m_job_type as wjt";
              $arg_str .= " ON m_wearer_std_tran.corporate_id = wjt.corporate_id";
              $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wjt.rntl_cont_no";
              $arg_str .= " AND m_wearer_std_tran.job_type_cd = wjt.job_type_cd";
          }
          $arg_str .= " WHERE ";
          $arg_str .= " m_wearer_std_tran.corporate_id = '".$result->as_corporate_id."'";
          $arg_str .= " AND m_wearer_std_tran.werer_cd = '".$result->as_werer_cd."'";
          $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = '".$result->as_rntl_cont_no."'";
          $arg_str .= " AND m_wearer_std_tran.order_sts_kbn = '5'";
          $m_weare_std_tran = new MWearerStdTran();
          $tran_results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
          $tran_result_obj = (array)$tran_results;
          $tran_results_cnt = $tran_result_obj["\0*\0_count"];

          // 着用者基本マスタトラン情報に重複データがある場合、優先させて着用者基本マスタ情報リストを上書きする
          if (!empty($tran_results_cnt)) {

              $paginator_model = new PaginatorModel(
                  array(
                      "data" => $tran_results,
                      "limit" => 1,
                      "page" => 1
                  )
              );
              $paginator = $paginator_model->getPaginate();
              $tran_results = $paginator->items;

              foreach ($tran_results as $tran_result) {
                  $result->as_ship_to_cd = $tran_result->as_ship_to_cd;
                  $result->as_ship_to_brnch_cd = $tran_result->as_ship_to_brnch_cd;
                  $result->as_rntl_sect_name = $tran_result->wst_rntl_sect_name;
                  $result->as_job_type_name = $tran_result->wjt_job_type_name;
              }
          }
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
            $list['cster_emply_cd'] = "-";
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

          //契約リソースマスターにゼロ埋めの拠点があれば、そのゼロ埋めのupdate_okフラグを適用する。
          //ゼロ埋めが複数あり、それらのupdate_okフラグが異なることは想定しない。
          if($section_all_zero_flg){
              $arg_str = "";
              $arg_str .= "SELECT ";
              $arg_str .= " * ";
              $arg_str .= " FROM ";
              $arg_str .= "m_contract_resource";
              $arg_str .= " WHERE ";
              $arg_str .= "corporate_id = '".$auth['corporate_id']."'";
              $arg_str .= " AND rntl_cont_no = '".$cond['agreement_no']."'";
              $arg_str .= " AND accnt_no = '".$auth['accnt_no']."'";
              $arg_str .= " AND rntl_sect_cd = '0000000000'";

              $m_contract_resource = new MContractResource();
              $zero_results = new Resultset(null, $m_contract_resource, $m_contract_resource->getReadConnection()->query($arg_str));
              $zero_result_obj = (array)$zero_results;
              $zero_results_cnt = $zero_result_obj["\0*\0_count"];
              if ($results_cnt > 0) {
                  $paginator_model = new PaginatorModel(
                  array(
                  "data" => $zero_results,
                  "limit" => $zero_results_cnt,
                  "page" => 1
                  )
                  );
              }
              $zero_paginator = $paginator_model->getPaginate();
              $zero_results = $zero_paginator->items;
              $i = 0;
              foreach ($zero_results as $zero_result) {
                  $zero_all_list[$i]['rntl_sect_cd'] = $zero_result->rntl_sect_cd;
                  $zero_all_list[$i]['update_ok_flg'] = $zero_result->update_ok_flg;
                  $i++;
              }
              if (count($zero_all_list) > 0) {
                  $update_ok_flg = $zero_all_list[0]['update_ok_flg'];
              }
              if($update_ok_flg == '1'){
                  $list['update_ok_flg'] = true;
              }else{
                  $list['update_ok_flg'] = false;
              }
          }else{

              if($result->as_order_sts_kbn == '5'){
                  //拠点ゼロ埋めなしで、発注が職種変更または異動の場合、発注情報トランの発注時拠点を見る
                  $arg_str = "";
                  $arg_str .= "SELECT ";
                  $arg_str .= " * ";
                  $arg_str .= " FROM ";
                  $arg_str .= "m_contract_resource";
                  $arg_str .= " WHERE ";
                  $arg_str .= "corporate_id = '".$auth['corporate_id']."'";
                  $arg_str .= " AND rntl_cont_no = '".$cond['agreement_no']."'";
                  $arg_str .= " AND accnt_no = '".$auth['accnt_no']."'";
                  $arg_str .= " AND rntl_sect_cd = '".$result->as_order_rntl_sect_cd."'";
                  $m_contract_resource = new MContractResource();
                  $section_result = new Resultset(null, $m_contract_resource, $m_contract_resource->getReadConnection()->query($arg_str));
                  $section_result_obj = (array)$section_result;
                  $section_list = array();
                  foreach ($section_result as $section_result_value) {
                      //ChromePhp::log($section_result_value);
                      $section_list['rntl_sect_cd'] = $section_result_value->rntl_sect_cd;
                      $section_list['update_ok_flg'] = $section_result_value->update_ok_flg;
                  }
                  if($section_list['update_ok_flg'] == '1'){
                      $list['update_ok_flg'] = true;
                  }else{
                      $list['update_ok_flg'] = false;
                  }

              }else{
                  //ゼロ埋め拠点がない場合は、それぞれのレコードのupdate_okフラグを確認する
                  if($result->as_update_ok_flg == '1'){
                      $list['update_ok_flg'] = true;
                  }else{
                      $list['update_ok_flg'] = false;
                  }
              }
          }
          // 発注入力へのパラメータ設定
        $list['param'] = '';
        $list['param'] .= $list['corporate_id'].':';
        $list['param'] .= $list['rntl_cont_no'].':';
        $list['param'] .= $list['werer_cd'].':';
        $list['param'] .= $list['rntl_sect_cd'].':';
        $list['param'] .= $list['job_type_cd'].':';
        $list['param'] .= $list['order_sts_kbn'].':';
        $list['param'] .= $list['order_reason_kbn'].':';
        $list['param'] .= $list['wst_order_req_no'].':';
        $list['param'] .= $list['order_req_no'].':';
        $list['param'] .= $list['rtn_order_req_no'];

        $all_list[] = $list;
      }
    }
//    $page_list['records_per_page'] = $page['records_per_page'];
//    $page_list['page_number'] = $page['page_number'];
//    $page_list['total_records'] = $results_cnt;
//    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;
    //ChromePhp::LOG($json_list);

    echo json_encode($json_list);
});

/**
 * 発注送信処理
 * 発注送信
 */
$app->post('/order_send/send', function () use ($app) {
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション
  $auth = $app->session->get("auth");
  //ChromePhp::LOG($auth);

  // フロントパラメータ
  $order_data = $params['data'];
  //ChromePhp::LOG($order_data);

  $json_list = array();

  $json_list["error_code"] = "0";

  // 各トラン情報更新処理
  if (!empty($order_data)) {
    $wst_query_list = array();
    $ord_query_list = array();
    $rtn_query_list = array();
    foreach ($order_data as $data) {
      if (!empty($data["wst_order_req_no"])) {
        $wst_query_list[] = "'".$data["wst_order_req_no"]."'";
      }
      if (!empty($data["order_req_no"])) {
        $ord_query_list[] = "'".$data["order_req_no"]."'";
      }
      if (!empty($data["rtn_order_req_no"])) {
        $rtn_query_list[] = "'".$data["rtn_order_req_no"]."'";
      }
    }
    // 着用者基本マスタトラン発注No
    if (!empty($wst_query_list)) {
      $wst_query = implode(',', $wst_query_list);
      $wst_query = "order_req_no IN (".$wst_query.")";
    } else {
      $wst_query = "";
    }
    // 発注情報トラン発注No
    if (!empty($ord_query_list)) {
      $ord_query = implode(',', $ord_query_list);
      $ord_query = "order_req_no IN (".$ord_query.")";
    } else {
      $ord_query = "";
    }
    // 返却予定情報トラン発注No
    if (!empty($rtn_query_list)) {
      $rtn_query = implode(',', $rtn_query_list);
      $rtn_query = "order_req_no IN (".$rtn_query.")";
    } else {
      $rtn_query = "";
    }
    //ChromePhp::LOG("着".$wst_query);
    //ChromePhp::LOG("発".$ord_query);
    //ChromePhp::LOG("返".$rtn_query);

    $m_wearer_std_tran = new MWearerStd();
    $transaction = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("begin"));
    try {
      // 着用者基本マスタトラン更新
      if (!empty($wst_query)) {
        $arg_str = "";
        $arg_str .= "UPDATE m_wearer_std_tran SET ";
        $arg_str .= "snd_kbn = '1'";
        $arg_str .= " WHERE ";
        $arg_str .= $wst_query;
        //ChromePhp::LOG($arg_str);
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
      }
      // 発注情報トラン更新
      if (!empty($ord_query)) {
        $arg_str = "";
        $arg_str .= "UPDATE t_order_tran SET ";
        $arg_str .= "snd_kbn = '1'";
        $arg_str .= " WHERE ";
        $arg_str .= $ord_query;
        //ChromePhp::LOG($arg_str);
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
      }
      // 返却予定情報トラン更新
      if (!empty($rtn_query)) {
        $arg_str = "";
        $arg_str .= "UPDATE t_returned_plan_info_tran SET ";
        $arg_str .= "snd_kbn = '1'";
        $arg_str .= " WHERE ";
        $arg_str .= $rtn_query;
        //ChromePhp::LOG($arg_str);
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
      }

      $transaction = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("commit"));
    } catch (Exception $e) {
      $transaction = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("rollback"));
      //ChromePhp::LOG($e);

      $json_list["error_code"] = "1";
      echo json_encode($json_list);
      return;
    }
  }

  echo json_encode($json_list);
});

/**
 * 発注送信処理
 * 発注送信キャンセル
 */
$app->post('/order_send/cancel', function () use ($app) {
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション
  $auth = $app->session->get("auth");

  // フロントパラメータ
  $order_data = $params['data'];
  //ChromePhp::LOG($order_data);

  $json_list = array();

  $json_list["error_code"] = "0";

  // 各トラン情報更新処理
  // 着用者基本マスタトラン発注No
  if (!empty($order_data["wst_order_req_no"])) {
    $wst_query = "order_req_no = '".$order_data["wst_order_req_no"]."'";
  } else {
    $wst_query = "";
  }
  // 発注情報トラン発注No
  if (!empty($order_data["order_req_no"])) {
    $ord_query = "order_req_no = '".$order_data["order_req_no"]."'";
  } else {
    $ord_query = "";
  }
  // 返却予定情報トラン発注No
  if (!empty($order_data["rtn_order_req_no"])) {
    $rtn_query = "order_req_no = '".$order_data["rtn_order_req_no"]."'";
  } else {
    $rtn_query = "";
  }
  //ChromePhp::LOG("着".$wst_query);
  //ChromePhp::LOG("発".$ord_query);
  //ChromePhp::LOG("返".$rtn_query);

  $m_wearer_std_tran = new MWearerStd();
  $transaction = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("begin"));
  try {
    // 着用者基本マスタトラン更新
    if (!empty($wst_query)) {
      $arg_str = "";
      $arg_str .= "UPDATE m_wearer_std_tran SET ";
      $arg_str .= "snd_kbn = '0'";
      $arg_str .= " WHERE ";
      $arg_str .= $wst_query;
      //ChromePhp::LOG($arg_str);
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
      $result_obj = (array)$results;
      $results_cnt = $result_obj["\0*\0_count"];
    }
    // 発注情報トラン更新
    if (!empty($ord_query)) {
      $arg_str = "";
      $arg_str .= "UPDATE t_order_tran SET ";
      $arg_str .= "snd_kbn = '0'";
      $arg_str .= " WHERE ";
      $arg_str .= $ord_query;
      //ChromePhp::LOG($arg_str);
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
      $result_obj = (array)$results;
      $results_cnt = $result_obj["\0*\0_count"];
    }
    // 返却予定情報トラン更新
    if (!empty($rtn_query)) {
      $arg_str = "";
      $arg_str .= "UPDATE t_returned_plan_info_tran SET ";
      $arg_str .= "snd_kbn = '0'";
      $arg_str .= " WHERE ";
      $arg_str .= $rtn_query;
      //ChromePhp::LOG($arg_str);
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
      $result_obj = (array)$results;
      $results_cnt = $result_obj["\0*\0_count"];
    }

    $transaction = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("commit"));
  } catch (Exception $e) {
    $transaction = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("rollback"));
    //ChromePhp::LOG($e);

    $json_list["error_code"] = "1";
    echo json_encode($json_list);
    return;
  }

  echo json_encode($json_list);
});
