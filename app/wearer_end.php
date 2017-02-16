<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * 貸与終了検索
 */
$app->post('/wearer_end/search', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    $auth = $app->session->get("auth");
    //ChromePhp::LOG($auth);

    $cond = $params['cond'];
    $page = $params['page'];
    //ChromePhp::LOG($cond);

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
            $all_list['a'][] = $result->rntl_sect_cd;
        }
    }
    if (in_array("0000000000", $all_list['a'])) {
        $section_all_zero_flg = true;
    } else {
        $section_all_zero_flg = false;
    }

    //---既存着用者基本マスタ情報リスト取得---//
    $query_list = array();
    $query_list[] = "m_wearer_std.corporate_id = '".$auth['corporate_id']."'";
    if(!empty($cond['agreement_no'])){
      $query_list[] = "m_wearer_std.rntl_cont_no = '".$cond['agreement_no']."'";
    }
    if(!empty($cond['cster_emply_cd'])){
      //$query_list[] = "m_wearer_std.cster_emply_cd LIKE '".$cond['cster_emply_cd']."%'";
      $query_list[] = "CASE
             WHEN m_wearer_std_tran.cster_emply_cd IS NULL THEN m_wearer_std.cster_emply_cd
             ELSE m_wearer_std_tran.cster_emply_cd
             END LIKE '%".$cond['cster_emply_cd']."%'";
    }

    if(!empty($cond['werer_name'])){
      //$query_list[] = "m_wearer_std.werer_name LIKE '%".$cond['werer_name']."%'";
      $query_list[] = "CASE
             WHEN m_wearer_std_tran.werer_name IS NULL THEN m_wearer_std.werer_name
             ELSE m_wearer_std_tran.werer_name
             END LIKE '%".$cond['werer_name']."%'";
    }
    if(!empty($cond['sex_kbn'])){
      //$query_list[] = "m_wearer_std.sex_kbn = '".$cond['sex_kbn']."'";
      $query_list[] = "CASE
             WHEN m_wearer_std_tran.sex_kbn IS NULL THEN m_wearer_std.sex_kbn
             ELSE m_wearer_std_tran.sex_kbn
             END = '".$cond['sex_kbn']."'";
    }
    if(!empty($cond['section'])){
      //$query_list[] = "m_wearer_std.rntl_sect_cd = '".$cond['section']."'";
      $query_list[] = "CASE
             WHEN m_wearer_std_tran.rntl_sect_cd IS NULL THEN m_wearer_std.rntl_sect_cd
             ELSE m_wearer_std_tran.rntl_sect_cd
             END = '".$cond['section']."'";
    }
    if(!empty($cond['job_type'])){
      //$query_list[] = "m_wearer_std.job_type_cd = '".$cond['job_type']."'";
      $query_list[] = "CASE
             WHEN m_wearer_std_tran.job_type_cd IS NULL THEN m_wearer_std.job_type_cd
             ELSE m_wearer_std_tran.job_type_cd
             END = '".$cond['job_type']."'";
    }
    $query_list[] = "m_wearer_std.werer_sts_kbn = '1'";

    if (!$section_all_zero_flg) {
      $query_list[] = "wcr.corporate_id = '".$auth['corporate_id']."'";
      $query_list[] = "wcr.rntl_cont_no = '".$cond['agreement_no']."'";
      $query_list[] = "wcr.accnt_no = '".$auth['accnt_no']."'";
    }
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (m_wearer_std.werer_cd) ";
    $arg_str .= "m_wearer_std.corporate_id as as_corporate_id,";
    $arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
    $arg_str .= "m_wearer_std.rntl_cont_no as as_rntl_cont_no,";
    $arg_str .= "m_wearer_std.rntl_sect_cd as as_rntl_sect_cd,";
    $arg_str .= "m_wearer_std.job_type_cd as as_job_type_cd,";
    $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name_kana,";
    $arg_str .= "m_wearer_std.sex_kbn as as_sex_kbn,";
    $arg_str .= "m_wearer_std.ship_to_cd as as_ship_to_cd,";
    $arg_str .= "m_wearer_std.ship_to_brnch_cd as as_ship_to_brnch_cd,";
    $arg_str .= "m_wearer_std_tran.werer_name as as_tran_werer_name,";
    $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_tran_cster_emply_cd,";
    $arg_str .= "m_wearer_std_tran.sex_kbn as as_tran_sex_kbn,";
    $arg_str .= "m_wearer_std_tran.job_type_cd as as_tran_job_type_cd,";
    $arg_str .= "m_wearer_std_tran.rntl_sect_cd as as_tran_rntl_sect_cd,";
    $arg_str .= "wst.rntl_sect_name as wst_rntl_sect_name,";
    $arg_str .= "wjt.job_type_name as wjt_job_type_name";
    $arg_str .= " FROM ";
    if ($section_all_zero_flg) {
      $arg_str .= "(m_wearer_std INNER JOIN m_section as wst";
      $arg_str .= " ON m_wearer_std.corporate_id = wst.corporate_id";
      $arg_str .= " AND m_wearer_std.rntl_cont_no = wst.rntl_cont_no";
      $arg_str .= " AND m_wearer_std.rntl_sect_cd = wst.rntl_sect_cd";
      $arg_str .= " INNER JOIN m_job_type as wjt";
      $arg_str .= " ON m_wearer_std.corporate_id = wjt.corporate_id";
      $arg_str .= " AND m_wearer_std.rntl_cont_no = wjt.rntl_cont_no";
      $arg_str .= " AND m_wearer_std.job_type_cd = wjt.job_type_cd)";
    } else {
      $arg_str .= "(m_wearer_std INNER JOIN (m_section as wst";
      $arg_str .= " INNER JOIN m_contract_resource as wcr";
      $arg_str .= " ON wst.corporate_id = wcr.corporate_id";
      $arg_str .= " AND wst.rntl_cont_no = wcr.rntl_cont_no";
      $arg_str .= " AND wst.rntl_sect_cd = wcr.rntl_sect_cd)";
      $arg_str .= " ON m_wearer_std.corporate_id = wst.corporate_id";
      $arg_str .= " AND m_wearer_std.rntl_cont_no = wst.rntl_cont_no";
      $arg_str .= " AND m_wearer_std.rntl_sect_cd = wst.rntl_sect_cd";
      $arg_str .= " INNER JOIN m_job_type as wjt";
      $arg_str .= " ON m_wearer_std.corporate_id = wjt.corporate_id";
      $arg_str .= " AND m_wearer_std.rntl_cont_no = wjt.rntl_cont_no";
      $arg_str .= " AND m_wearer_std.job_type_cd = wjt.job_type_cd)";
    }
    $arg_str .= " LEFT JOIN m_wearer_std_tran";
    $arg_str .= " ON m_wearer_std.corporate_id = m_wearer_std_tran.corporate_id";
    $arg_str .= " AND m_wearer_std.rntl_cont_no = m_wearer_std_tran.rntl_cont_no";
    $arg_str .= " AND m_wearer_std.werer_cd = m_wearer_std_tran.werer_cd";
    $arg_str .= " AND m_wearer_std.werer_sts_kbn = m_wearer_std_tran.werer_sts_kbn";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_cster_emply_cd ASC";
    //ChromePhp::LOG($arg_str);
    $m_weare_std = new MWearerStd();
    $results = new Resultset(null, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    $paginator_model = new PaginatorModel(
        array(
            "data"  => $results,
            "limit" => $page['records_per_page'],
            "page" => $page['page_number']
        )
    );

    $all_list = array();
    $json_list = array();
    $list = array();
    if(!empty($results_cnt)){
      $paginator = $paginator_model->getPaginate();
      $results = $paginator->items;
      foreach($results as $result) {
        //---着用者基本マスタトラン情報の既存データ重複参照---//
        $query_list = array();
        $tran_query_list = array();
        $query_list[] = "m_wearer_std_tran.corporate_id = '".$result->as_corporate_id."'";
        $query_list[] = "m_wearer_std_tran.rntl_cont_no = '".$result->as_rntl_cont_no."'";
        $query_list[] = "m_wearer_std_tran.werer_cd = '".$result->as_werer_cd."'";
        /*
        if (!$section_all_zero_flg) {
          $query_list[] = "wcr.corporate_id = '".$result->as_corporate_id."'";
          $query_list[] = "wcr.rntl_cont_no = '".$result->as_rntl_cont_no."'";
          $query_list[] = "wcr.accnt_no = '".$auth['accnt_no']."'";
        }
        */
        $tran_query_list[] = "(t_order_tran.order_sts_kbn = '2' AND t_order_tran.order_reason_kbn <> '07')";
        $tran_query_list[] = "(t_returned_plan_info_tran.order_sts_kbn = '2' AND t_returned_plan_info_tran.order_reason_kbn <> '07')";
        $tran_query = implode(' OR ', $tran_query_list);
        $query_list[] = "(".$tran_query.")";
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "m_wearer_std_tran.corporate_id as as_corporate_id,";
        $arg_str .= "m_wearer_std_tran.werer_cd as as_werer_cd,";
        $arg_str .= "m_wearer_std_tran.rntl_cont_no as as_rntl_cont_no,";
        $arg_str .= "m_wearer_std_tran.rntl_sect_cd as as_rntl_sect_cd,";
        $arg_str .= "m_wearer_std_tran.job_type_cd as as_job_type_cd,";
        $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
        $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
        $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana,";
        $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";
        $arg_str .= "m_wearer_std_tran.snd_kbn as as_wearer_snd_kbn,";
        $arg_str .= "m_wearer_std_tran.ship_to_cd as as_ship_to_cd,";
        $arg_str .= "m_wearer_std_tran.ship_to_brnch_cd as as_ship_to_brnch_cd,";
        $arg_str .= "m_wearer_std_tran.order_req_no as as_wearer_order_req_no,";
        $arg_str .= "wst.rntl_sect_name as wst_rntl_sect_name,";
        $arg_str .= "wjt.job_type_name as wjt_job_type_name,";
        $arg_str .= "t_order_tran.order_req_no as as_order_req_no,";
        $arg_str .= "t_order_tran.order_sts_kbn as as_order_sts_kbn,";
        $arg_str .= "t_order_tran.snd_kbn as as_order_snd_kbn,";
        $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";
        $arg_str .= "t_returned_plan_info_tran.order_req_no as as_return_req_no";
        $arg_str .= " FROM ";
        if ($section_all_zero_flg) {
          $arg_str .= "(m_wearer_std_tran INNER JOIN m_section as wst";
          $arg_str .= " ON m_wearer_std_tran.corporate_id = wst.corporate_id";
          $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wst.rntl_cont_no";
          $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = wst.rntl_sect_cd";
          $arg_str .= " INNER JOIN m_job_type as wjt";
          $arg_str .= " ON m_wearer_std_tran.corporate_id = wjt.corporate_id";
          $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wjt.rntl_cont_no";
          $arg_str .= " AND m_wearer_std_tran.job_type_cd = wjt.job_type_cd)";
        } else {
          $arg_str .= "(m_wearer_std_tran INNER JOIN m_section as wst";
          $arg_str .= " ON m_wearer_std_tran.corporate_id = wst.corporate_id";
          $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wst.rntl_cont_no";
          $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = wst.rntl_sect_cd";
          $arg_str .= " INNER JOIN m_job_type as wjt";
          $arg_str .= " ON m_wearer_std_tran.corporate_id = wjt.corporate_id";
          $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wjt.rntl_cont_no";
          $arg_str .= " AND m_wearer_std_tran.job_type_cd = wjt.job_type_cd)";
        }
        $arg_str .= " LEFT JOIN ";
        $arg_str .= "(t_order_tran INNER JOIN m_section as os";
        $arg_str .= " ON t_order_tran.corporate_id = os.corporate_id";
        $arg_str .= " AND t_order_tran.rntl_cont_no = os.rntl_cont_no";
        $arg_str .= " AND t_order_tran.rntl_sect_cd = os.rntl_sect_cd";
        $arg_str .= " INNER JOIN m_job_type as ojt";
        $arg_str .= " ON t_order_tran.corporate_id = ojt.corporate_id";
        $arg_str .= " AND t_order_tran.rntl_cont_no = ojt.rntl_cont_no";
        $arg_str .= " AND t_order_tran.job_type_cd = ojt.job_type_cd)";
        $arg_str .= " ON m_wearer_std_tran.corporate_id = t_order_tran.corporate_id";
        $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = t_order_tran.rntl_cont_no";
        $arg_str .= " AND m_wearer_std_tran.werer_cd = t_order_tran.werer_cd";
        $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = t_order_tran.rntl_sect_cd";
        $arg_str .= " AND m_wearer_std_tran.job_type_cd = t_order_tran.job_type_cd";
        $arg_str .= " LEFT JOIN ";
        $arg_str .= "(t_returned_plan_info_tran INNER JOIN m_section as rs";
        $arg_str .= " ON t_returned_plan_info_tran.corporate_id = rs.corporate_id";
        $arg_str .= " AND t_returned_plan_info_tran.rntl_cont_no = rs.rntl_cont_no";
        $arg_str .= " AND t_returned_plan_info_tran.rntl_sect_cd = rs.rntl_sect_cd";
        $arg_str .= " INNER JOIN m_job_type as rjt";
        $arg_str .= " ON t_returned_plan_info_tran.corporate_id = rjt.corporate_id";
        $arg_str .= " AND t_returned_plan_info_tran.rntl_cont_no = rjt.rntl_cont_no";
        $arg_str .= " AND t_returned_plan_info_tran.job_type_cd = rjt.job_type_cd)";
        $arg_str .= " ON m_wearer_std_tran.corporate_id = t_returned_plan_info_tran.corporate_id";
        $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = t_returned_plan_info_tran.rntl_cont_no";
        $arg_str .= " AND m_wearer_std_tran.werer_cd = t_returned_plan_info_tran.werer_cd";
        $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = t_returned_plan_info_tran.rntl_sect_cd";
        $arg_str .= " AND m_wearer_std_tran.job_type_cd = t_returned_plan_info_tran.job_type_cd";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= " ORDER BY m_wearer_std_tran.upd_date DESC";
        //ChromePhp::LOG($arg_str);
        $m_weare_std_tran = new MWearerStdTran();
        $tran_results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
        $tran_result_obj = (array)$tran_results;
        $tran_results_cnt = $tran_result_obj["\0*\0_count"];

        if (!empty($tran_results_cnt)) {
          $list['wearer_tran_flg'] = '1';
          $paginator_model = new PaginatorModel(
              array(
                  "data"  => $tran_results,
                  "limit" => 1,
                  "page" => 1
              )
          );
          $paginator = $paginator_model->getPaginate();
          $tran_results = $paginator->items;
          foreach($tran_results as $tran_result) {
            $result->as_rntl_sect_cd = $tran_result->as_rntl_sect_cd;
            $result->as_job_type_cd = $tran_result->as_job_type_cd;
            $result->as_sex_kbn = $tran_result->as_sex_kbn;
            $result->as_wearer_snd_kbn = $tran_result->as_wearer_snd_kbn;
            $result->as_ship_to_cd = $tran_result->as_ship_to_cd;
            $result->as_ship_to_brnch_cd = $tran_result->as_ship_to_brnch_cd;
            $result->as_order_req_no = $tran_result->as_order_req_no;
            $result->as_order_sts_kbn = $tran_result->as_order_sts_kbn;
            $result->as_order_snd_kbn = $tran_result->as_order_snd_kbn;
            $result->as_order_reason_kbn = $tran_result->as_order_reason_kbn;
            $result->as_return_req_no = $tran_result->as_return_req_no;
          }
        } else {
          $list['wearer_tran_flg'] = '0';
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
            $arg_str .= "m_wearer_std_tran INNER JOIN m_section as wst";
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
                  $result->wst_rntl_sect_name = $tran_result->wst_rntl_sect_name;
                  $result->wjt_job_type_name = $tran_result->wjt_job_type_name;
              }
          }
        // 発注No(発注情報トラン)
        if (!empty($result->as_order_req_no)) {
          $list['order_req_no'] = $result->as_order_req_no;
        } else {
          $list['order_req_no'] = "";
        }
        // 発注No(返却予定情報トラン)
        if (!empty($result->as_return_req_no)) {
          $list['return_req_no'] = $result->as_return_req_no;
        } else {
          $list['return_req_no'] = "";
        }
        // 着用者コード
        $list['werer_cd'] = $result->as_werer_cd;
        // 企業ID
        $list['corporate_id'] = $result->as_corporate_id;
        // 契約No
        $list['rntl_cont_no'] = $result->as_rntl_cont_no;
        // レンタル部門コード
        $list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
        // 職種コード
        $list['job_type_cd'] = $result->as_job_type_cd;
        // 社員番号
        if (!empty($result->as_cster_emply_cd)) {
          $list['cster_emply_cd'] = $result->as_cster_emply_cd;
        } else {
          $list['cster_emply_cd'] = "-";
        }
        // 出荷先コード
        $list['ship_to_cd'] = $result->as_ship_to_cd;
        // 出荷先支店コード
        $list['ship_to_brnch_cd'] = $result->as_ship_to_brnch_cd;
        // 着用者名
        if (!empty($result->as_werer_name)) {
          $list['werer_name'] = $result->as_werer_name;
        } else {
          $list['werer_name'] = "-";
        }
        // 着用者名
        if (!empty($result->as_werer_name_kana)) {
          $list['werer_name_kana'] = $result->as_werer_name_kana;
        } else {
          $list['werer_name_kana'] = "";
        }
        // 理由区分
        if (isset($result->as_order_reason_kbn)) {
          $list['order_reason_kbn'] = $result->as_order_reason_kbn;
        } else {
          $list['order_reason_kbn'] = "";
        }
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
          $list['sex_kbn'] = $gencode_map->gen_name;
        }
        // 発注、発注情報トラン有無フラグ
        if (isset($result->as_order_sts_kbn)) {
          $list['order_kbn'] = "<font color='red'>済</font>";
          $list['order_tran_flg'] = '1';
        }else{
          $list['order_kbn'] = "未";
          $list['order_tran_flg'] = '0';
        }
        // 状態
        $list['snd_kbn'] = "-";
        if (isset($result->as_order_snd_kbn)) {
          if($result->as_order_snd_kbn == '0'){
            $list['snd_kbn'] = "未送信";
          }elseif($result->as_order_snd_kbn == '1'){
            $list['snd_kbn'] = "送信済";
          }elseif($result->as_order_snd_kbn == '9'){
            $list['snd_kbn'] = "処理中";
          }
        }
        // 拠点
        if (!empty($result->wst_rntl_sect_name)) {
            $list['rntl_sect_name'] = $result->wst_rntl_sect_name;
        } else {
            $list['rntl_sect_name'] = "-";
        }
        // 貸与パターン
        if (!empty($result->wjt_job_type_name)) {
            $list['job_type_name'] = $result->wjt_job_type_name;
        } else {
            $list['job_type_name'] = "-";
        }

        //---「貸与終了」ボタンの生成---//
        // 発注情報トラン参照
        $query_list = array();
        $query_list[] = "corporate_id = '".$auth['corporate_id']."'";
        $query_list[] = "rntl_cont_no = '".$list['rntl_cont_no']."'";
        $query_list[] = "werer_cd = '".$list['werer_cd']."'";
        $query = implode(' AND ', $query_list);
        $arg_str = "";
        $arg_str .= "SELECT distinct on (order_req_no) ";
        $arg_str .= "*";
        $arg_str .= " FROM ";
        $arg_str .= "t_order_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $t_order_tran = new TOrderTran();
        $t_order_tran_results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$t_order_tran_results;
        $t_order_tran_cnt = $result_obj["\0*\0_count"];

        // 「貸与終了」パターンチェックスタート
        $list['btnPattern'] = "";
        $patarn_flg = true;
        if (!empty($t_order_tran_cnt)) {
          $paginator_model = new PaginatorModel(
              array(
                  "data"  => $t_order_tran_results,
                  "limit" => $t_order_tran_cnt,
                  "page" => 1
              )
          );
          $paginator = $paginator_model->getPaginate();
          $t_order_tran_results = $paginator->items;

          if ($list['btnPattern'] == "") {
            //パターンB： 発注情報トラン．発注状況区分 = 貸与終了 かつ、発注情報トラン．理由区分 = 不要品返却以外のデータがある場合、かつ、発注情報トラン．送信区分 = 未送信の場合、ボタンの文言は「貸与終了[済]」で表示する。
            $patarn_flg = true;
            foreach ($t_order_tran_results as $t_order_tran_result) {
              $order_req_no = $t_order_tran_result->order_req_no;
              $order_sts_kbn = $t_order_tran_result->order_sts_kbn;
              $order_reason_kbn = $t_order_tran_result->order_reason_kbn;
              $snd_kbn = $t_order_tran_result->snd_kbn;
              if ($order_sts_kbn == '2' && $snd_kbn == '0' && $order_reason_kbn != '07') {
                $patarn_flg = false;
                break;
              }
            }
            if (!$patarn_flg) {
              $list['wearer_end_button'] = "貸与終了";
              $list['wearer_end_red'] = "[済]";
              $list['disabled'] = "";
              $list['order_reason_kbn'] = $order_reason_kbn;
              $list['order_req_no'] = $order_req_no;
              $list['btnPattern'] = "B";
            }
          }
          if ($list['btnPattern'] == "") {
            //パターンC： 発注情報トラン．発注状況区分 = 貸与終了 かつ、発注情報トラン．理由区分 = 不要品返却以外のデータがある場合、かつ、発注情報トラン．送信区分 = 送信済の場合、ボタンの文言は「貸与終了[済]」で非活性表示する。
            $patarn_flg = true;
            foreach ($t_order_tran_results as $t_order_tran_result) {
              $order_req_no = $t_order_tran_result->order_req_no;
              $order_sts_kbn = $t_order_tran_result->order_sts_kbn;
              $order_reason_kbn = $t_order_tran_result->order_reason_kbn;
              $snd_kbn = $t_order_tran_result->snd_kbn;
              if ($order_sts_kbn == '2' && $snd_kbn == '1' && ($order_reason_kbn != '07' && $order_reason_kbn != '28')) {
                $patarn_flg = false;
                break;
              }
            }
            if (!$patarn_flg) {
              $list['wearer_end_button'] = "貸与終了";
              $list['wearer_end_red'] = "[済]";
              $list['disabled'] = "disabled";
              $list['order_req_no'] = $order_req_no;
              $list['order_reason_kbn'] = $order_reason_kbn;
              $list['btnPattern'] = "C";
            }
          }
          if ($list['btnPattern'] == "") {
            //パターンA： 発注情報トラン．発注状況区分 = 貸与終了 かつ、発注情報トラン．理由区分 = 不要品返却以外のデータが無い場合、ボタンの文言は「貸与終了」で表示する。
            $patarn_flg = true;
            foreach ($t_order_tran_results as $t_order_tran_result) {
              $order_req_no = $t_order_tran_result->order_req_no;
              $order_sts_kbn = $t_order_tran_result->order_sts_kbn;
              $order_reason_kbn = $t_order_tran_result->order_reason_kbn;
              $snd_kbn = $t_order_tran_result->snd_kbn;
              if ($order_sts_kbn == '2' && $order_reason_kbn == '07' && ($order_reason_kbn != '07' && $order_reason_kbn != '28')) {
                $patarn_flg = false;
                break;
              }
            }
            if (!$patarn_flg) {
              $list['wearer_end_button'] = "貸与終了";
              $list['wearer_end_red'] = "";
              $list['disabled'] = "";
              $list['btnPattern'] = "A";
              $list['order_req_no'] = $order_req_no;
              $list['order_reason_kbn'] = $order_reason_kbn;
            }
          }
        }
        //パターンD：着用者基本マスタトラン．送信区分 = 処理中のデータがある場合、ボタンの文言は「貸与終了」で非活性表示する。
        // 着用者基本マスタトラン参照
        $query_list = array();
        $query_list[] = "corporate_id = '".$auth['corporate_id']."'";
        $query_list[] = "rntl_cont_no = '".$list['rntl_cont_no']."'";
        $query_list[] = "werer_cd = '".$list['werer_cd']."'";
        $query = implode(' AND ', $query_list);
        $arg_str = "";
        $arg_str .= "SELECT distinct on (order_req_no) ";
        $arg_str .= "*";
        $arg_str .= " FROM ";
        $arg_str .= "m_wearer_std_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $m_wearer_std_tran = new MWearerStdTran();
        $m_wearer_std_tran_results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$m_wearer_std_tran_results;
        $m_wearer_std_tran_cnt = $result_obj["\0*\0_count"];
        if ($m_wearer_std_tran_cnt > 0) {
          $paginator_model = new PaginatorModel(
              array(
                  "data"  => $m_wearer_std_tran_results,
                  "limit" => $m_wearer_std_tran_cnt,
                  "page" => 1
              )
          );
          $paginator = $paginator_model->getPaginate();
          $m_wearer_std_tran_results = $paginator->items;
          $patarn_flg = true;
          foreach ($m_wearer_std_tran_results as $m_wearer_std_tran_result) {
            if ($m_wearer_std_tran_result->snd_kbn == '9') {
              $patarn_flg = false;
              break;
            }
          }
          if (!$patarn_flg) {
            $list['wearer_end_button'] = "貸与終了";
            $list['wearer_end_red'] = "";
            $list['disabled'] = "disabled";
            $list['btnPattern'] = "D";
          }
        }
        if ($list['btnPattern'] == "") {
          $list['wearer_end_button'] = "貸与終了";
          $list['wearer_end_red'] = "";
          $list['disabled'] = "";
          $list['btnPattern'] = "A";
          $list['order_req_no'] = '';
          $list['order_reason_kbn'] = '';
        }
        //「返却伝票ダウンロード」ボタン生成
        if ($list['btnPattern'] == "B" || $list['btnPattern'] == "C") {
          $list['return_reciept_button'] = true;
          $list['return_reciept_param'] = "";
          $list['return_reciept_param'] .= $list['rntl_cont_no'].":";
          $list['return_reciept_param'] .= $list['order_req_no'];
        } else {
          $list['return_reciept_button'] = false;
        }

        // 発注入力へのパラメータ設定
        $list['param'] = '';
        $list['param'] .= $list['rntl_cont_no'].':';
        $list['param'] .= $list['werer_cd'].':';
        $list['param'] .= $list['cster_emply_cd'].':';
        $list['param'] .= $result->as_sex_kbn.':';
        $list['param'] .= $list['rntl_sect_cd'].':';
        $list['param'] .= $list['job_type_cd'].':';
        $list['param'] .= $list['order_reason_kbn'].':';
        $list['param'] .= $list['ship_to_cd'].':';
        $list['param'] .= $list['ship_to_brnch_cd'].':';
        $list['param'] .= $list['order_tran_flg'].':';
        $list['param'] .= $list['wearer_tran_flg'].':';
        $list['param'] .= $list['order_req_no'].':';
        $list['param'] .= $list['return_req_no'].':';
        $list['param'] .= $list['werer_name_kana'];
        array_push($all_list,$list);
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
 * 貸与終了
 * 発注パターンNGチェック＆セッション保持
 */
$app->post('/wearer_end/order_check', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // パラメータ取得
    $cond = $params['data'];

    $json_list = array();

    $json_list = $cond;
    // エラーメッセージ、エラーコード 0:正常 その他:要因エラー
    $json_list["err_cd"] = '0';
    $json_list["err_msg"] = '';

    //--発注パターンNGチェック ここから--//
    // ※着用者基本マスタトラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$cond['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$cond['werer_cd']."'");
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "m_wearer_std_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY upd_date DESC";
    //ChromePhp::LOG($arg_str);
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);
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
      //ChromePhp::LOG($results);
      foreach ($results as $result) {
        $order_sts_kbn = $result->order_sts_kbn;
      }

      // 着用者基本マスタトラン.発注状況区分 = 「着用者編集」の情報がある際は発注NG
      if ($order_sts_kbn == "6") {
        $json_list["err_cd"] = "1";
        $error_msg = "着用者編集の発注が入力されています。".PHP_EOL."貸与終了を行う場合は着用者編集の発注をキャンセルしてください。";
        $json_list["err_msg"] = $error_msg;
        echo json_encode($json_list);
        return;
      }
    }

    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$cond['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$cond['werer_cd']."'");
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY upd_date DESC";
    //ChromePhp::LOG($arg_str);
    $t_order_tran = new TOrderTran();
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);
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
      //ChromePhp::LOG($results);
      foreach ($results as $result) {
        $order_sts_kbn = $result->order_sts_kbn;
        $order_reason_kbn = $result->order_reason_kbn;
      }

      // 発注情報トラン.発注状況区分 = 「終了(貸与終了)」以外の発注情報がある際は発注NG
      if ($order_sts_kbn == "1" && $order_reason_kbn == "03") {
        $json_list["err_cd"] = "1";
        $error_msg = "追加貸与の発注が入力されています。".PHP_EOL."貸与終了を行う場合は追加貸与の発注をキャンセルしてください。";
        $json_list["err_msg"] = $error_msg;
      }
      if ($order_sts_kbn == "5") {
        $json_list["err_cd"] = "1";
        $error_msg = "職種変更または異動の発注が入力されています。".PHP_EOL."貸与終了を行う場合は職種変更または異動の発注をキャンセルしてください。";
        $json_list["err_msg"] = $error_msg;
      }
      if ($order_sts_kbn == "2" && $order_reason_kbn == "07") {
        $json_list["err_cd"] = "1";
        $error_msg = "不要品返却の発注が入力されています。".PHP_EOL."貸与終了を行う場合は不要品返却の発注をキャンセルしてください。";
        $json_list["err_msg"] = $error_msg;
      }
      if ($order_sts_kbn == "3" || $order_sts_kbn == "4") {
        $json_list["err_cd"] = "1";
        $error_msg = "交換の発注が入力されています。".PHP_EOL."貸与終了を行う場合は交換の発注をキャンセルしてください。";
        $json_list["err_msg"] = $error_msg;
      }
    }
    // ※発注情報状況の商品レコード取得
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$cond['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$cond['werer_cd']."'");
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (t_order_state.item_cd, t_order_state.color_cd, t_order_state.size_cd) ";
    $arg_str .= "t_order_state.ship_qty as as_ship_qty,";
    $arg_str .= "t_order_state.ship_ymd as as_ship_ymd,";
    $arg_str .= "t_order_state.item_cd as as_item_cd,";
    $arg_str .= "t_order_state.color_cd as as_color_cd,";
    $arg_str .= "t_order_state.size_cd as as_size_cd,";
    $arg_str .= "t_order_state.werer_cd as as_werer_cd";
    $arg_str .= " FROM ";
    $arg_str .= "t_order_state";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    //ChromePhp::LOG($arg_str);
    $t_order_state = new TOrderState();
    $results = new Resultset(NULL, $t_order_state, $t_order_state->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt_ship_item = $result_obj["\0*\0_count"];
    if ($results_cnt_ship_item > 0) {
      $list = array();
      $ship_item_list = array();
      foreach ($results as $result) {
        $list['ship_ymd'] = $result->as_ship_ymd;
        $list['item_cd'] = $result->as_item_cd;
        $list['color_cd'] = $result->as_color_cd;
        $list['size_cd'] = $result->as_size_cd;

        //商品ごとの発注数合計を計算
        $parameter = array(
        "corporate_id" => $auth['corporate_id'],
        "rntl_cont_no" => $cond['rntl_cont_no'],
        "werer_cd" => $result->as_werer_cd,
        "item_cd" => $result->as_item_cd,
        "color_cd" => $result->as_color_cd,
        "size_cd" => $result->as_size_cd);
        $TOrderState = TOrderState::find(array(
        'conditions'  => "corporate_id = :corporate_id: AND rntl_cont_no = :rntl_cont_no:  AND werer_cd = :werer_cd: AND item_cd = :item_cd: AND color_cd = :color_cd: AND size_cd = :size_cd:",
        "bind" => $parameter
        ));
        //商品数
        $each_item_count = $TOrderState->count();
        //商品ごとの発注数サマリ
        $each_item_ship = 0;
        for($i = 0; $i < $each_item_count; $i++){
          $each_item_ship = $each_item_ship + $TOrderState[$i]->ship_qty;
          //ChromePhp::log($TOrderState[$i]->item_cd);
          //ChromePhp::log($each_item_ship);
        }
        $list['ship_qty'] = $each_item_ship;

        array_push($ship_item_list, $list);
      }
    }

    // ※発注情報の商品レコード数を取得
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$cond['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$cond['werer_cd']."'");
    array_push($query_list, "item_cd IS NOT null");
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (t_order.item_cd, t_order.color_cd, t_order.size_cd) ";
    $arg_str .= "t_order.order_qty as as_order_qty,";
    $arg_str .= "t_order.item_cd as as_item_cd,";
    $arg_str .= "t_order.color_cd as as_color_cd,";
    $arg_str .= "t_order.size_cd as as_size_cd,";
    $arg_str .= "t_order.werer_cd as as_werer_cd";
    $arg_str .= " FROM ";
    $arg_str .= "t_order";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    //ChromePhp::LOG($arg_str);
    $t_order = new TOrder();
    $results = new Resultset(NULL, $t_order, $t_order->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if ($results_cnt > 0) {
      $list = array();
      $order_item_list = array();
      foreach ($results as $result) {
        $list['item_cd'] = $result->as_item_cd;
        $list['color_cd'] = $result->as_color_cd;
        $list['size_cd'] = $result->as_size_cd;

        //商品ごとの発注数合計を計算
        $parameter = array(
        "corporate_id" => $auth['corporate_id'],
        "rntl_cont_no" => $cond['rntl_cont_no'],
        "werer_cd" => $result->as_werer_cd,
        "item_cd" => $result->as_item_cd,
        "color_cd" => $result->as_color_cd,
        "size_cd" => $result->as_size_cd);
        $TOrder = TOrder::find(array(
        'conditions'  => "corporate_id = :corporate_id: AND rntl_cont_no = :rntl_cont_no:  AND werer_cd = :werer_cd: AND item_cd = :item_cd: AND color_cd = :color_cd: AND size_cd = :size_cd:",
        "bind" => $parameter
        ));
        //商品数
        $each_item_count = $TOrder->count();
        //商品ごとの発注数サマリ
        $each_item_order = 0;
        for($i = 0; $i < $each_item_count; $i++){
          $each_item_order = $each_item_order + $TOrder[$i]->order_qty;
          //ChromePhp::log($TOrder[$i]->item_cd);
          //hromePhp::log($each_item_order);
        }
        $list['order_qty'] = $each_item_order;
        $list['unshipped_qty'] = null;

        array_push($order_item_list, $list);
      }
    }
  //出荷情報が0な時点で未出荷があるとみなし、未出荷エラー
  if($results_cnt_ship_item == 0){
    $json_list["err_cd"] = "1";
    $error_msg = "対象の方は未出荷の商品がある為、貸与終了の発注はできません。";
    $json_list["err_msg"] = $error_msg;
    echo json_encode($json_list);
    return;
  }
  //出荷情報が1以上あった場合に、下記の処理に移行
  //ChromePhp::log($order_item_list);
  //ChromePhp::log($ship_item_list);
  if($results_cnt_ship_item > 0) {
    $count_ship = count($ship_item_list);
    $count_order = count($order_item_list);
    //発注情報と、出荷商品の比較 同じ商品cd,色cd,サイズcdだったらお互いのサマリ数を比較
    for($i = 0; $i < $count_order; $i++){
      for($s = 0; $s < $count_ship; $s++){
        if($order_item_list[$i]['item_cd'] == $ship_item_list[$s]['item_cd']
        && $order_item_list[$i]['color_cd'] == $ship_item_list[$s]['color_cd']
        && $order_item_list[$i]['size_cd'] == $ship_item_list[$s]['size_cd'])
        {
          $order_item_list[$i]['unshipped_qty'] = $order_item_list[$i]['order_qty'] - $ship_item_list[$s]['ship_qty'];
        }
      }
      //未出荷商品が0以上または、発注情報があるのに、出荷情報（発注状況）がない場合はエラー
      if($order_item_list[$i]['unshipped_qty'] > 0 || is_null($order_item_list[$i]['unshipped_qty'])){
        $json_list["err_cd"] = "1";
        $error_msg = "対象の方は未出荷の商品がある為、貸与終了の発注はできません。";
        $json_list["err_msg"] = $error_msg;
        echo json_encode($json_list);
        return;
      }
    }
  }


  //未受領の確認
  $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$cond['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$cond['werer_cd']."'");
    array_push($query_list, "receipt_status = '1'");
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str .= "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "t_delivery_goods_state_details";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    //ChromePhp::LOG($arg_str);
    $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
    $results = new Resultset(NULL, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if ($results_cnt > 0) {
        $json_list["err_cd"] = "1";
        $error_msg = "対象の方は未受領の商品がある為、貸与終了の発注はできません。";
        $json_list["err_msg"] = $error_msg;
        echo json_encode($json_list);
        return;
    }
    //--発注パターンNGチェック ここまで--//

    $wearer_end_post = $app->session->get("wearer_end_post");
    if(isset($cond["order_reason_kbn"])){
        $order_reason_kbn = $cond["order_reason_kbn"];
    }elseif(isset($wearer_end_post["order_reason_kbn"])){
        $order_reason_kbn = $wearer_end_post["order_reason_kbn"];
    }else{
        $order_reason_kbn = '05';
    }
    if(isset($cond["order_tran_flg"])){
        $order_tran_flg = $cond["order_tran_flg"];
    }elseif(isset($wearer_end_post['order_tran_flg'])){
        $order_tran_flg = $wearer_end_post["order_tran_flg"];
    }else{
        $order_tran_flg = '0';
    }
    if(isset($cond["wearer_tran_flg"])) {
        $wearer_tran_flg = $cond["wearer_tran_flg"];
    }elseif(isset($wearer_end_post['wearer_tran_flg'])){
        $wearer_tran_flg = $wearer_end_post["wearer_tran_flg"];
    }else{
        $wearer_tran_flg = '0';
    }

    // POSTパラメータのセッション格納
    $app->session->set("wearer_end_post", array(
        'rntl_cont_no' => $cond["rntl_cont_no"],
        'werer_cd' => $cond["werer_cd"],
        'cster_emply_cd' => $cond["cster_emply_cd"],
        'sex_kbn' => $cond["sex_kbn"],
        'rntl_sect_cd' => $cond["rntl_sect_cd"],
        'job_type_cd' => $cond["job_type_cd"],
        'ship_to_cd' => $cond["ship_to_cd"],
        'ship_to_brnch_cd' => $cond["ship_to_brnch_cd"],
        'order_reason_kbn' => $order_reason_kbn,
        'order_tran_flg' => $order_tran_flg,
        'wearer_tran_flg' => $wearer_tran_flg,
        'order_req_no' => $cond["order_req_no"],
        'return_req_no' => $cond["return_req_no"],
        'werer_name' => $cond["werer_name"],
        'werer_name_kana' => $cond["werer_name_kana"],
    ));

    echo json_encode($json_list);
});
