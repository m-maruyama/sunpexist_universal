<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 職種変更または異動
 * 検索一覧
 */
$app->post('/wearer_change/search', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
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
      $all_list[] = $result->rntl_sect_cd;
    }
  }
  if (in_array("0000000000", $all_list)) {
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
    $query_list[] = "m_wearer_std.cster_emply_cd LIKE '".$cond['cster_emply_cd']."%'";
  }
  if(!empty($cond['werer_name'])){
    $query_list[] = "m_wearer_std.werer_name LIKE '%".$cond['werer_name']."%'";
  }
  if(!empty($cond['sex_kbn'])){
    $query_list[] = "m_wearer_std.sex_kbn = '".$cond['sex_kbn']."'";
  }
  if(!empty($cond['section'])){
    $query_list[] = "m_wearer_std.rntl_sect_cd = '".$cond['section']."'";
  }
  if(!empty($cond['job_type'])){
    $query_list[] = "m_wearer_std.job_type_cd = '".$cond['job_type']."'";
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
  $arg_str .= "m_wearer_std.sex_kbn as as_sex_kbn,";
  $arg_str .= "m_wearer_std.ship_to_cd as as_ship_to_cd,";
  $arg_str .= "m_wearer_std.ship_to_brnch_cd as as_ship_to_brnch_cd,";
  $arg_str .= "wst.rntl_sect_name as wst_rntl_sect_name,";
  $arg_str .= "wjt.job_type_name as wjt_job_type_name";
  $arg_str .= " FROM ";
  if ($section_all_zero_flg) {
    $arg_str .= "m_wearer_std INNER JOIN m_section as wst";
    $arg_str .= " ON m_wearer_std.corporate_id = wst.corporate_id";
    $arg_str .= " AND m_wearer_std.rntl_cont_no = wst.rntl_cont_no";
    $arg_str .= " AND m_wearer_std.rntl_sect_cd = wst.rntl_sect_cd";
    $arg_str .= " INNER JOIN m_job_type as wjt";
    $arg_str .= " ON m_wearer_std.corporate_id = wjt.corporate_id";
    $arg_str .= " AND m_wearer_std.rntl_cont_no = wjt.rntl_cont_no";
    $arg_str .= " AND m_wearer_std.job_type_cd = wjt.job_type_cd";
  } else {
    $arg_str .= "m_wearer_std INNER JOIN (m_section as wst";
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
    $arg_str .= " AND m_wearer_std.job_type_cd = wjt.job_type_cd";
  }
  $arg_str .= " WHERE ";
  $arg_str .= $query;
  $arg_str .= ") as distinct_table";
  $arg_str .= " ORDER BY as_cster_emply_cd ASC";
  //ChromePhp::LOG($arg_str);
  $m_weare_std = new MWearerStd();
  $results = new Resultset(null, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
  $result_obj = (array)$results;
  $results_cnt = $result_obj["\0*\0_count"];
  //ChromePhp::LOG("着用者基本マスタ件数");
  //ChromePhp::LOG($results_cnt);

  $paginator_model = new PaginatorModel(
      array(
          "data"  => $results,
          "limit" => $page['records_per_page'],
          "page" => $page['page_number']
      )
  );

  $list = array();
  $all_list = array();
  $json_list = array();

  if(!empty($results_cnt)){
      $paginator = $paginator_model->getPaginate();
      $results = $paginator->items;
      //ChromePhp::LOG("着用者基本マスタリスト");
      //ChromePhp::LOG($results);

      foreach($results as $result) {
        //---着用者基本マスタトラン情報の既存データ重複参照---//
        $query_list = array();
        $tran_query_list = array();
        $query_list[] = "m_wearer_std_tran.corporate_id = '".$result->as_corporate_id."'";
        $query_list[] = "m_wearer_std_tran.rntl_cont_no = '".$result->as_rntl_cont_no."'";
        $query_list[] = "m_wearer_std_tran.werer_cd = '".$result->as_werer_cd."'";
        $query_list[] = "t_order_tran.werer_cd = '".$result->as_werer_cd."'";
        if (!$section_all_zero_flg) {
          $query_list[] = "wcr.corporate_id = '".$result->as_corporate_id."'";
          $query_list[] = "wcr.rntl_cont_no = '".$result->as_rntl_cont_no."'";
          $query_list[] = "wcr.accnt_no = '".$auth['accnt_no']."'";
        }
        $tran_query_list[] = "t_order_tran.order_sts_kbn = '5'";
        $tran_query_list[] = "t_returned_plan_info_tran.order_sts_kbn = '5'";
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
        $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";
        $arg_str .= "m_wearer_std_tran.snd_kbn as as_wearer_snd_kbn,";
        $arg_str .= "m_wearer_std_tran.ship_to_cd as as_ship_to_cd,";
        $arg_str .= "m_wearer_std_tran.ship_to_brnch_cd as as_ship_to_brnch_cd,";
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
          $arg_str .= "(m_wearer_std_tran INNER JOIN (m_section as wst";
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
        $m_weare_std_tran = new MWearerStdTran();
        $tran_results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
        $tran_result_obj = (array)$tran_results;
        $tran_results_cnt = $tran_result_obj["\0*\0_count"];

        // 着用者基本マスタトラン情報に重複データがある場合、優先させて着用者基本マスタ情報リストを上書きする
        if (!empty($tran_results_cnt)) {
          // 着用者マスタトラン有フラグ
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
          //ChromePhp::LOG("着用者基本マスタトラン重複情報");
          //ChromePhp::LOG($tran_results);

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
          // 着用者マスタトラン無
          $list['wearer_tran_flg'] = '0';
        }
        //ChromePhp::LOG("チェック後の着用者リスト情報");
        //ChromePhp::LOG($result);

        // レンタル契約No
        $list['rntl_cont_no'] = $result->as_rntl_cont_no;
        // レンタル部門コード
        $list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
        // 職種コード
        $list['job_type_cd'] = $result->as_job_type_cd;
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
        // 理由区分
        if (isset($result->as_order_reason_kbn)) {
          $list['order_reason_kbn'] = $result->as_order_reason_kbn;
        } else {
          $list['order_reason_kbn'] = null;
        }
        // 着用者コード
        $list['werer_cd'] = $result->as_werer_cd;
        // 社員番号
        if (isset($result->as_cster_emply_cd)) {
            $list['cster_emply_cd'] = $result->as_cster_emply_cd;
        } else {
            $list['cster_emply_cd'] = null;
        }
        // 性別区分
        $list['sex_kbn'] = $result->as_sex_kbn;
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
        //---性別名称---//
        $query_list = array();
        array_push($query_list, "cls_cd = '004'");
        array_push($query_list, "gen_cd = '".$result->as_sex_kbn."'");
        $query = implode(' AND ', $query_list);
        $gencode = MGencode::query()
            ->where($query)
            ->columns('*')
            ->execute();
        foreach ($gencode as $gencode_map) {
            $list['sex_kbn_name'] = $gencode_map->gen_name;
        }
        // 発注、発注情報トラン有無フラグ
        if (isset($result->as_order_sts_kbn)) {
            $list['order_kbn'] = "<font color='red'>済</font>";
            // 発注情報トラン有
            $list['order_tran_flg'] = '1';
        }else{
            $list['order_kbn'] = "未";
            // 発注情報トラン無
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

        //---「職種変更または異動」ボタンの生成---//
        // 発注情報トラン参照
        $query_list = array();
        array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "rntl_cont_no = '".$list['rntl_cont_no']."'");
        array_push($query_list, "werer_cd = '".$list['werer_cd']."'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str .= "SELECT distinct on (order_req_no) ";
        $arg_str .= "*";
        $arg_str .= " FROM ";
        $arg_str .= "t_order_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        //ChromePhp::LOG($arg_str);
        $t_order_tran = new TOrderTran();
        $t_order_tran_results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$t_order_tran_results;
        $t_order_tran_cnt = $result_obj["\0*\0_count"];
        //ChromePhp::LOG($results_cnt);

        // パターンチェックスタート
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
          //ChromePhp::LOG($results);

          // パターンE: 着用者基本マスタトラン.送信区分 = 処理中の場合、ボタンの文言は「職種変更または異動」で非活性表示する。
          if ($list['wearer_tran_flg'] == "1" && $list['snd_kbn'] == "処理中") {
            $list['wearer_change_button'] = "職種変更または異動";
            $list['wearer_change_red'] = "";
            $list['disabled'] = "disabled";
            $list['btnPattern'] = "E";
          }
          if ($list['btnPattern'] == "") {
            //パターンD： その発注の発注情報トラン．送信区分 = 処理中の場合、ボタンの文言は「職種変更または異動」で非活性表示する。
            foreach ($t_order_tran_results as $t_order_tran_result) {
              $order_req_no = $t_order_tran_result->order_req_no;
              $order_sts_kbn = $t_order_tran_result->order_sts_kbn;
              $order_reason_kbn = $t_order_tran_result->order_reason_kbn;
              $snd_kbn = $t_order_tran_result->snd_kbn;
              if ($snd_kbn == '9')
              {
                $patarn_flg = false;
                break;
              }
            }
            if (!$patarn_flg) {
              $list['order_req_no'] = $order_req_no;
              $list['order_reason_kbn'] = $order_reason_kbn;
              $list['wearer_change_button'] = "職種変更または異動";
              $list['wearer_change_red'] = "";
              $list['disabled'] = "disabled";
              $list['btnPattern'] = "D";
            }
          }
          if ($list['btnPattern'] == "") {
            //パターンA： 発注情報トラン．発注状況区分 = 異動 かつ、発注情報トラン．理由区分 = 職種変更または異動のデータが無い
            $patarn_flg = false;
            foreach ($t_order_tran_results as $t_order_tran_result) {
              $order_req_no = $t_order_tran_result->order_req_no;
              $order_sts_kbn = $t_order_tran_result->order_sts_kbn;
              $order_reason_kbn = $t_order_tran_result->order_reason_kbn;
              $snd_kbn = $t_order_tran_result->snd_kbn;
              if (
                $order_sts_kbn == '5'
                && ($order_reason_kbn == '09' || $order_reason_kbn == '10' || $order_reason_kbn == '11' || $order_reason_kbn == '24')
              )
              {
                $patarn_flg = true;
                break;
              }
            }
            if (!$patarn_flg) {
              $list['order_req_no'] = $order_req_no;
              $list['order_reason_kbn'] = $order_reason_kbn;
              $list['wearer_change_button'] = '職種変更または異動';
              $list['wearer_change_red'] = "";
              $list['disabled'] = "";
              $list['btnPattern'] = "A";
            }
          }
          if ($list['btnPattern'] == "") {
            //パターンB： 発注情報トラン．発注状況区分 = 異動 かつ、発注情報トラン．理由区分 = 職種変更または異動のデータがある場合、かつ、
            //発注情報トラン．送信区分 = 未送信の場合、ボタンの文言は「職種変更または異動[済]」で表示する。
            $patarn_flg = true;
            foreach ($t_order_tran_results as $t_order_tran_result) {
              $order_req_no = $t_order_tran_result->order_req_no;
              $order_sts_kbn = $t_order_tran_result->order_sts_kbn;
              $order_reason_kbn = $t_order_tran_result->order_reason_kbn;
              $snd_kbn = $t_order_tran_result->snd_kbn;
              if (
                $order_sts_kbn == '5'
                && ($order_reason_kbn == '09' || $order_reason_kbn == '10' || $order_reason_kbn == '11' || $order_reason_kbn == '24')
                && $snd_kbn == '0')
              {
                $patarn_flg = false;
                break;
              }
            }
            if (!$patarn_flg) {
              $list['order_req_no'] = $order_req_no;
              $list['order_reason_kbn'] = $order_reason_kbn;
              $list['wearer_change_button'] = "職種変更または異動";
              $list['wearer_change_red'] = "[済]";
              $list['disabled'] = "";
              $list['btnPattern'] = "B";
            }
          }
          if ($list['btnPattern'] == "") {
            //パターンC： 発注情報トラン．発注状況区分 = 異動 かつ、発注情報トラン．理由区分 = 職種変更または異動のデータがある場合、かつ、
            //発注情報トラン．送信区分 = 送信済の場合、ボタンの文言は「職種変更または異動[済]」で非活性表示する。
            $patarn_flg = true;
            foreach ($t_order_tran_results as $t_order_tran_result) {
              $order_req_no = $t_order_tran_result->order_req_no;
              $order_sts_kbn = $t_order_tran_result->order_sts_kbn;
              $order_reason_kbn = $t_order_tran_result->order_reason_kbn;
              $snd_kbn = $t_order_tran_result->snd_kbn;
              if (
                $order_sts_kbn == '5'
                && ($order_reason_kbn == '09' || $order_reason_kbn == '10' || $order_reason_kbn == '11' || $order_reason_kbn == '24')
                && $snd_kbn == '1')
              {
                $patarn_flg = false;
                break;
              }
            }
            if (!$patarn_flg) {
              $list['order_req_no'] = $order_req_no;
              $list['order_reason_kbn'] = $order_reason_kbn;
              $list['wearer_change_button'] = "職種変更または異動";
              $list['wearer_change_red'] = "[済]";
              $list['disabled'] = "disabled";
              $list['btnPattern'] = "C";
            }
          }
        }
        // パターンE: 着用者基本マスタトラン.送信区分 = 処理中の場合、ボタンの文言は「職種変更または異動」で非活性表示する。
        if ($list['btnPattern'] == "") {
          if ($list['wearer_tran_flg'] == "1" && $list['snd_kbn'] == "処理中") {
            $list['wearer_change_button'] = "職種変更または異動";
            $list['wearer_change_red'] = "";
            $list['disabled'] = "disabled";
            $list['btnPattern'] = "E";
          }
        }
        if ($list['btnPattern'] == "") {
          //上記パターンに引っかからない場合はデフォ表示
          $list['wearer_change_button'] = '職種変更または異動';
          $list['wearer_change_red'] = "";
          $list['disabled'] = "";
          $list['return_reciept_button'] = false;
          $list['btnPattern'] = "no_pattern";
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
        $list['param'] .= $list['sex_kbn'].':';
        $list['param'] .= $list['rntl_sect_cd'].':';
        $list['param'] .= $list['job_type_cd'].':';
        $list['param'] .= $list['order_reason_kbn'].':';
        $list['param'] .= $list['ship_to_cd'].':';
        $list['param'] .= $list['ship_to_brnch_cd'].':';
        $list['param'] .= $list['order_tran_flg'].':';
        $list['param'] .= $list['wearer_tran_flg'].':';
        $list['param'] .= $list['order_req_no'].':';
        $list['param'] .= $list['return_req_no'];

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
* 職種変更または異動
 * 「職種変更または異動」ボタンの押下時のパラメータのセッション保持
 * →発注入力（職種変更または異動）にてパラメータ利用
 */
$app->post('/wearer_change/req_param', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
  $auth = $app->session->get("auth");

  // パラメータ取得
  $cond = $params['data'];
  //ChromePhp::LOG($cond);

  // POSTパラメータのセッション格納
  $app->session->set("wearer_chg_post", array(
    'rntl_cont_no' => $cond["rntl_cont_no"],
    'werer_cd' => $cond["werer_cd"],
    'cster_emply_cd' => $cond["cster_emply_cd"],
    'sex_kbn' => $cond["sex_kbn"],
    'rntl_sect_cd' => $cond["rntl_sect_cd"],
    'job_type_cd' => $cond["job_type_cd"],
    'order_reason_kbn' => $cond["order_reason_kbn"],
    'ship_to_cd' => $cond["ship_to_cd"],
    'ship_to_brnch_cd' => $cond["ship_to_brnch_cd"],
    'order_tran_flg' => $cond["order_tran_flg"],
    'wearer_tran_flg' => $cond["wearer_tran_flg"],
    'order_req_no' => $cond["order_req_no"],
    'return_req_no' => $cond["return_req_no"],
  ));

  $json_list = array();
  $json_list = $app->session->get("wearer_chg_post");

  echo json_encode($json_list);
});

/**
 * 職種変更または異動
 * 発注パターンNGチェック
 */
$app->post('/wearer_change/order_check', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
  $auth = $app->session->get("auth");

  // パラメータ取得
  $cond = $params['data'];
  //ChromePhp::LOG($cond);

  $json_list = array();

  // エラーメッセージ、エラーコード 0:正常 その他:要因エラー
  $json_list["err_cd"] = '0';
  $json_list["err_msg"] = '';

  //※着用者基本マスタトラン参照
  $query_list = array();
  array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "rntl_cont_no = '".$cond['rntl_cont_no']."'");
  array_push($query_list, "werer_cd = '".$cond['werer_cd']."'");
//  array_push($query_list, "rntl_sect_cd = '".$cond['rntl_sect_cd']."'");
//  array_push($query_list, "job_type_cd = '".$cond['job_type_cd']."'");
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
      $error_msg = "着用者編集の発注が入力されています。".PHP_EOL."職種変更または異動を行う場合は着用者編集の発注をキャンセルしてください。";
      $json_list["err_msg"] = $error_msg;

      //ChromePhp::LOG($json_list);
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

    // 発注情報トラン.発注状況区分 = 「異動」以外の情報がある際は発注NG
    if ($order_sts_kbn !== "5") {
      $json_list["err_cd"] = "1";
      if ($order_sts_kbn == "1" && $order_reason_kbn == "03") {
        $error_msg = "追加貸与の発注が入力されています。".PHP_EOL."職種変更または異動を行う場合は追加貸与の発注をキャンセルしてください。";
        $json_list["err_msg"] = $error_msg;
      }
      if ($order_sts_kbn == "2" && ($order_reason_kbn == "05" || $order_reason_kbn == "06" || $order_reason_kbn == "08" || $order_reason_kbn == "20")) {
        $error_msg = "貸与終了の発注が入力されています。".PHP_EOL."職種変更または異動を行う場合は貸与終了の発注をキャンセルしてください。";
        $json_list["err_msg"] = $error_msg;
      }
      if ($order_sts_kbn == "2" && $order_reason_kbn == "07") {
        $error_msg = "不要品返却の発注が入力されています。".PHP_EOL."職種変更または異動を行う場合は不要品返却の発注をキャンセルしてください。";
        $json_list["err_msg"] = $error_msg;
      }
      if ($order_sts_kbn == "3" || $order_sts_kbn == "4") {
        $error_msg = "交換の発注が入力されています。".PHP_EOL."職種変更または異動を行う場合は交換の発注をキャンセルしてください。";
        $json_list["err_msg"] = $error_msg;
      }
    }
  }

  //ChromePhp::LOG($json_list);
  echo json_encode($json_list);
});
