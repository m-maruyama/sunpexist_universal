<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * 発注入力（着用者編集）
 * 入力項目：初期値情報、前画面セッション取得
 *
 */
$app->post('/wearer_edit_info', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
  $auth = $app->session->get("auth");
  //ChromePhp::LOG($auth);

  // 前画面セッション取得
  $wearer_edit_post = $app->session->get("wearer_edit_post");
  //ChromePhp::LOG($wearer_edit_post);

  $json_list = array();

  //--着用者入力項目情報--//
  $all_list = array();
  $json_list['wearer_info'] = "";
  // 着用者基本マスタトラン参照
  $list = array();
  $query_list = array();
  array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
  array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_edit_post['werer_cd']."'");
  array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
  array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
  // 発注状況区分(着用者編集)
  array_push($query_list,"m_wearer_std_tran.order_sts_kbn = '6'");
  $query = implode(' AND ', $query_list);

  $arg_str = "";
  $arg_str = "SELECT ";
  $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
  $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
  $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana,";
  $arg_str .= "m_wearer_std_tran.appointment_ymd as as_appointment_ymd,";
  $arg_str .= "m_wearer_std_tran.resfl_ymd as as_resfl_ymd";
  $arg_str .= " FROM ";
  $arg_str .= "m_wearer_std_tran";
  $arg_str .= " WHERE ";
  $arg_str .= $query;
  $arg_str .= " ORDER BY m_wearer_std_tran.upd_date DESC";
  //ChromePhp::LOG($arg_str);
  $m_weare_std_tran = new MWearerStdTran();
  $results = new Resultset(NULL, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
  $result_obj = (array)$results;
  $results_cnt = $result_obj["\0*\0_count"];
  //ChromePhp::LOG($results_cnt);
  if (!empty($results_cnt)) {
    // 着用者基本マスタトラン（着用者編集）有り
    $json_list['tran_flg'] = "1";

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
      // 社員コード
      $list['cster_emply_cd'] = $result->as_cster_emply_cd;
      // 着用者名
      $list['werer_name'] = $result->as_werer_name;
      // 着用者名（読み仮名）
      $list['werer_name_kana'] = $result->as_werer_name_kana;
      // 異動日
      $list['resfl_ymd'] = $result->as_resfl_ymd;
      if (!empty($list['resfl_ymd'])) {
        $list['resfl_ymd'] = date('Y/m/d', strtotime($list['resfl_ymd']));
      } else {
        $list['resfl_ymd'] = '';
      }
      // 発令日
      $list['appointment_ymd'] = $result->as_appointment_ymd;
      if (!empty($list['appointment_ymd'])) {
        $list['appointment_ymd'] = date('Y/m/d', strtotime($list['appointment_ymd']));
      } else {
        $list['appointment_ymd'] = '';
      }
    }

    array_push($all_list, $list);
  }

  // 上記参照のトラン情報がない場合、着用者基本マスタ情報を参照する
  if (empty($all_list)) {
    // 着用者基本マスタトラン（着用者編集）無し
    $json_list['tran_flg'] = "0";

    $query_list = array();
    array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "m_wearer_std.rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
    array_push($query_list, "m_wearer_std.werer_cd = '".$wearer_edit_post['werer_cd']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std.werer_name_kana as as_werer_name_kana,";
    $arg_str .= "m_wearer_std.appointment_ymd as as_appointment_ymd,";
    $arg_str .= "m_wearer_std.resfl_ymd as as_resfl_ymd";
    $arg_str .= " FROM ";
    $arg_str .= "m_wearer_std";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY m_wearer_std.upd_date DESC";
    //ChromePhp::LOG($arg_str);
    $m_weare_std = new MWearerStd();
    $results = new Resultset(NULL, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    if (!empty($results_cnt)) {
      $list = array();
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
        // 社員コード
        $list['cster_emply_cd'] = $result->as_cster_emply_cd;
        // 着用者名
        $list['werer_name'] = $result->as_werer_name;
        // 着用者名（読み仮名）
        $list['werer_name_kana'] = $result->as_werer_name_kana;
        // 異動日
        $list['resfl_ymd'] = $result->as_resfl_ymd;
        if (!empty($list['resfl_ymd'])) {
          $list['resfl_ymd'] = date('Y/m/d', strtotime($list['resfl_ymd']));
        } else {
          $list['resfl_ymd'] = '';
        }
        // 発令日
        $list['appointment_ymd'] = $result->as_appointment_ymd;
        if (!empty($list['appointment_ymd'])) {
          $list['appointment_ymd'] = date('Y/m/d', strtotime($list['appointment_ymd']));
        } else {
          $list['appointment_ymd'] = '';
        }
      }

      array_push($all_list, $list);
    }
  }
  $json_list['wearer_info'] = $all_list;
  //ChromePhp::LOG($json_list['wearer_info']);

  //--契約No--//
  $all_list = array();
  $json_list['agreement_no_list'] = "";
  $query_list = array();
  $list = array();
  array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
  $query = implode(' AND ', $query_list);

  $arg_str = '';
  $arg_str = 'SELECT ';
  $arg_str .= '*';
  $arg_str .= ' FROM ';
  $arg_str .= 'm_contract';
  $arg_str .= ' WHERE ';
  $arg_str .= $query;
  $arg_str .= ' ORDER BY rntl_cont_no ASC';
  $m_contract = new MContract();
  $results = new Resultset(NULL, $m_contract, $m_contract->getReadConnection()->query($arg_str));
  $results_array = (array) $results;
  $results_cnt = $results_array["\0*\0_count"];
  //ChromePhp::LOG($results_cnt);
  if ($results_cnt > 0) {
    $list = array();
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
      $list['rntl_cont_no'] = $result->rntl_cont_no;
      $list['rntl_cont_name'] = $result->rntl_cont_name;
      array_push($all_list, $list);
    }
  } else {
    $list['rntl_cont_no'] = null;
    $list['rntl_cont_name'] = '';
    array_push($all_list, $list);
  }
  $json_list['agreement_no_list'] = $all_list;

  //--性別区分--//
  $query_list = array();
  $list = array();
  $all_list = array();
  array_push($query_list, "cls_cd = '004'");
  $query = implode(' AND ', $query_list);

  $arg_str = '';
  $arg_str = 'SELECT ';
  $arg_str .= ' * ';
  $arg_str .= ' FROM ';
  $arg_str .= 'm_gencode';
  $arg_str .= ' WHERE ';
  $arg_str .= $query;

  $m_gencode = new MGencode();
  $results = new Resultset(NULL, $m_gencode, $m_gencode->getReadConnection()->query($arg_str));
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
      $list['sex_kbn'] = $result->gen_cd;
      $list['sex_kbn_name'] = $result->gen_name;

      // 初期選択状態版を生成
      if ($list['sex_kbn'] == $wearer_edit_post['sex_kbn']) {
        $list['selected'] = 'selected';
      } else {
        $list['selected'] = '';
      }

      array_push($all_list, $list);
    }
  } else {
    $list['sex_kbn'] = NULL;
    $list['sex_kbn_name'] = '';
    $list['selected'] = '';
    array_push($all_list, $list);
  }
  $json_list['sex_kbn_list'] = $all_list;
  //ChromePhp::LOG($json_list['sex_kbn_list']);

  //--拠点--//
  $all_list = array();
  $json_list['section_list'] = "";
  $query_list = array();
  $list = array();
  array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
  array_push($query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
  $query = implode(' AND ', $query_list);

  $arg_str = '';
  $arg_str = 'SELECT ';
  $arg_str .= '*';
  $arg_str .= ' FROM ';
  $arg_str .= 'm_section';
  $arg_str .= ' WHERE ';
  $arg_str .= $query;
  $arg_str .= ' ORDER BY rntl_sect_cd asc';
  $m_section = new MSection();
  $results = new Resultset(NULL, $m_section, $m_section->getReadConnection()->query($arg_str));
  $results_array = (array) $results;
  $results_cnt = $results_array["\0*\0_count"];
  //ChromePhp::LOG($results_cnt);
  if ($results_cnt > 0) {
    $list = array();
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
      $list['rntl_sect_cd'] = $result->rntl_sect_cd;
      $list['rntl_sect_name'] = $result->rntl_sect_name;
      array_push($all_list, $list);
    }
  } else {
    $list['rntl_sect_cd'] = null;
    $list['rntl_sect_name'] = '';
    array_push($all_list, $list);
  }
  $json_list['section_list'] = $all_list;
  //ChromePhp::LOG($json_list['section_list']);

  //--貸与パターン--//
  $all_list = array();
  $json_list['job_type_list'] = "";
  $query_list = array();
  $list = array();

  array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
  array_push($query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
  $query = implode(' AND ', $query_list);

  $arg_str = '';
  $arg_str = 'SELECT ';
  $arg_str .= '*';
  $arg_str .= ' FROM ';
  $arg_str .= 'm_job_type';
  $arg_str .= ' WHERE ';
  $arg_str .= $query;
  $arg_str .= ' ORDER BY job_type_cd asc';
  $m_job_type = new MJobType();
  $results = new Resultset(NULL, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
  $results_array = (array) $results;
  $results_cnt = $results_array["\0*\0_count"];
  //ChromePhp::LOG($results_cnt);
  if ($results_cnt > 0) {
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
      $list['job_type_cd'] = $result->job_type_cd;
      $list['job_type_name'] = $result->job_type_name;
      $list['sp_job_type_flg'] = $result->sp_job_type_flg;
      array_push($all_list, $list);
    }
  } else {
    $list['job_type_cd'] = null;
    $list['job_type_name'] = '';
    $list['sp_job_type_flg'] = '0';
    array_push($all_list, $list);
  }
  $json_list['job_type_list'] = $all_list;
  //ChromePhp::LOG($json_list['job_type_list']);

  //--出荷先、郵便番号、住所--//
  $all_list = array();
  $json_list['shipment_list'] = "";
  $query_list = array();
  $list = array();

  array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
  array_push($query_list, "ship_to_cd = '".$wearer_edit_post['ship_to_cd']."'");
  array_push($query_list, "ship_to_brnch_cd = '".$wearer_edit_post['ship_to_brnch_cd']."'");
  $query = implode(' AND ', $query_list);

  $arg_str = '';
  $arg_str = 'SELECT ';
  $arg_str .= '*';
  $arg_str .= ' FROM ';
  $arg_str .= 'm_shipment_to';
  $arg_str .= ' WHERE ';
  $arg_str .= $query;
  $arg_str .= ' ORDER BY ship_to_cd asc,ship_to_brnch_cd asc';
  $m_shipment_to = new MShipmentTo();
  $results = new Resultset(NULL, $m_shipment_to, $m_shipment_to->getReadConnection()->query($arg_str));
  $results_array = (array) $results;
  $results_cnt = $results_array["\0*\0_count"];
  //ChromePhp::LOG($results_cnt);
  if ($results_cnt > 0) {
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
      $list['ship_to_cd'] = $result->ship_to_cd;
      $list['ship_to_brnch_cd'] = $result->ship_to_brnch_cd;
      $list['cust_to_brnch_name1'] = $result->cust_to_brnch_name1;
      if (empty($list['cust_to_brnch_name1'])) {
        $list['cust_to_brnch_name1'] = '';
      }
      $list['cust_to_brnch_name2'] = $result->cust_to_brnch_name2;
      if (empty($list['cust_to_brnch_name2'])) {
        $list['cust_to_brnch_name2'] = '';
      }
      $list['zip_no'] = $result->zip_no;
      if (!empty($list['zip_no'])) {
        $list['zip_no'] = preg_replace('/^(\d{3})(\d{4})$/', '$1-$2', $list['zip_no']);
      } else {
        $list['zip_no'] = '';
      }
      $list['address1'] = $result->address1;
      if (empty($list['address1'])) {
        $list['address1'] = '';
      }
      $list['address2'] = $result->address2;
      if (empty($list['address2'])) {
        $list['address2'] = '';
      }
      $list['address3'] = $result->address3;
      if (empty($list['address3'])) {
        $list['address3'] = '';
      }
      $list['address4'] = $result->address4;
      if (empty($list['address4'])) {
        $list['address4'] = '';
      }
      $list['address'] = $list['address1'].$list['address2'].$list['address3'].$list['address4'];

      array_push($all_list, $list);
    }
  } else {
    $list['ship_to_cd'] = '';
    $list['ship_to_brnch_cd'] = '';
    $list['cust_to_brnch_name1'] = '';
    $list['cust_to_brnch_name2'] = '';
    $list['zip_no'] = '';
    $list['address1'] = '';
    $list['address2'] = '';
    $list['address3'] = '';
    $list['address4'] = '';
    $list['address'] = '';
    array_push($all_list, $list);
  }

  $json_list['shipment_list'] = $all_list;
  //ChromePhp::LOG($json_list['shipment_list']);

  //--前画面セッション情報--//
  // レンタル契約No
  $json_list['rntl_cont_no'] = $wearer_edit_post["rntl_cont_no"];
  // 部門コード
  $json_list['rntl_sect_cd'] = $wearer_edit_post["rntl_sect_cd"];
  // 貸与パターン
  $json_list['job_type_cd'] = $wearer_edit_post["job_type_cd"];
  // 着用者コード
  $json_list['werer_cd'] = $wearer_edit_post["werer_cd"];
  // 着用者基本情報トランフラグ
  $json_list['wearer_tran_flg'] = $wearer_edit_post["wearer_tran_flg"];

  echo json_encode($json_list);
});

/**
 * 発注入力（着用者編集）
 * 発注取消処理
 */
$app->post('/wearer_edit_delete', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
  $auth = $app->session->get("auth");
  //ChromePhp::LOG($auth);
  // 前画面セッション取得
  $wearer_edit_post = $app->session->get("wearer_edit_post");
  //ChromePhp::LOG($wearer_edit_post);
  // フロントパラメータ取得
  $cond = $params['data'];
  //ChromePhp::LOG("フロント側パラメータ");
  //ChromePhp::LOG($cond);

  $json_list = array();
  // DB更新エラーコード 0:正常 1:更新エラー
  $json_list["error_code"] = "0";

  try {
    //--着用者基本マスタトラン削除--//
    // 発注情報トランを参照
    //ChromePhp::LOG("発注情報トラン参照");
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$cond['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$cond['werer_cd']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT distinct on (order_req_no) ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY order_req_no DESC";
    //ChromePhp::LOG($arg_str);
    $t_order_tran = new TOrderTran();
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    if ($results_cnt == 0) {
      // 上記発注情報トラン件数が0の場合に着用者基本マスタトランのデータを削除する
      //ChromePhp::LOG("着用者基本マスタトラン削除");
      $query_list = array();
      array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "m_wearer_std_tran.werer_cd = '".$cond['werer_cd']."'");
      array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$cond['rntl_cont_no']."'");
      array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$cond['rntl_sect_cd']."'");
      array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$cond['job_type_cd']."'");
      // 発注区分「着用者編集」
      array_push($query_list, "m_wearer_std_tran.order_sts_kbn = '6'");
      $query = implode(' AND ', $query_list);

      $arg_str = "";
      $arg_str = "DELETE FROM ";
      $arg_str .= "m_wearer_std_tran";
      $arg_str .= " WHERE ";
      $arg_str .= $query;
      //ChromePhp::LOG($arg_str);
      $m_wearer_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
      $result_obj = (array)$results;
      $results_cnt = $result_obj["\0*\0_count"];
      //ChromePhp::LOG($results_cnt);
    } else {
      // 上記発注情報トラン件数が1件以上の場合、着用者基本マスタトラン情報を更新
      $list = array();
      $all_list = array();
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
        $order_sts_kbn = $result->order_sts_kbn;
      }

      // 着用者マスタトラン（発注状況区分）更新
      $query_list = array();
      array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "m_wearer_std_tran.werer_cd = '".$cond['werer_cd']."'");
      array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$cond['rntl_cont_no']."'");
      array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$cond['rntl_sect_cd']."'");
      array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$cond['job_type_cd']."'");
      // 発注区分「着用者編集」
      array_push($query_list, "m_wearer_std_tran.order_sts_kbn = '6'");
      $src_query = implode(' AND ', $query_list);

      $up_query_list = array();
      // 発注状況区分
      array_push($up_query_list, "order_sts_kbn = '".$order_sts_kbn."'");
      $up_query = implode(',', $up_query_list);

      $arg_str = "";
      $arg_str = "UPDATE m_wearer_std_tran SET ";
      $arg_str .= $up_query;
      $arg_str .= " WHERE ";
      $arg_str .= $src_query;
      //ChromePhp::LOG($arg_str);
      $m_wearer_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
      $result_obj = (array)$results;
      $results_cnt = $result_obj["\0*\0_count"];
      //ChromePhp::LOG($results_cnt);
    }
  } catch (Exception $e) {
    $json_list["error_code"] = "1";
    ChromePhp::LOG("発注取消処理エラー");
    ChromePhp::LOG($e);

    echo json_encode($json_list);
    return;
  }

  //ChromePhp::LOG("発注取消処理コード");
  //ChromePhp::LOG($json_list["error_code"]);
  echo json_encode($json_list);
});

/**
 * 発注入力（着用者編集）
 * 入力完了処理
 */
$app->post('/wearer_edit_complete', function ()use($app){
   $params = json_decode(file_get_contents("php://input"), true);

   // アカウントセッション取得
   $auth = $app->session->get("auth");
   //ChromePhp::LOG($auth);

   // 前画面セッション取得
   $wearer_edit_post = $app->session->get("wearer_edit_post");
   //ChromePhp::LOG($wearer_edit_post);

   // フロントパラメータ取得
   $mode = $params["mode"];
   $wearer_data_input = $params["wearer_data"];

   $json_list = array();
   // DB更新エラーコード 0:正常 その他:要因エラー
   $json_list["error_code"] = "0";
   //$json_list["error_msg"] = array();

   if ($mode == "check") {
     //--入力内容確認--//
     // 社員コード
     if ($wearer_data_input['emply_cd_flg']) {
       if (mb_strlen($wearer_data_input['member_no']) == 0) {
         $json_list["error_code"] = "1";
         $error_msg = "社員コードありにチェックしている場合、社員コードを入力してください。";
         array_push($json_list["error_msg"], $error_msg);
       }
     }
     if (!$wearer_data_input['emply_cd_flg']) {
       if (mb_strlen($wearer_data_input['member_no']) > 0) {
         $json_list["error_code"] = "1";
         $error_msg = "社員コードありにチェックしていない場合、社員コードの入力は不要です。";
         array_push($json_list["error_msg"], $error_msg);
       }
     }
     // 着用者名
     if (empty($wearer_data_input["member_name"])) {
       $json_list["error_code"] = "1";
       $error_msg = "着用者名を入力してください。";
       array_push($json_list["error_msg"], $error_msg);
     }

     echo json_encode($json_list);
   } else if ($mode == "update") {
     //--発注NGパターンチェック--//
     //※発注情報トラン参照
     $query_list = array();
     array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
     array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
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

       // 着用者編集の場合、何かしらの発注区分の情報がある際は発注NGとする
       $json_list["error_code"] = "1";
       if ($order_sts_kbn == "1" && $order_reason_kbn == "03") {
         $error_msg = "追加貸与の発注が登録されていた為、操作を完了できませんでした。追加貸与の発注を削除してから再度登録して下さい。";
         $json_list["error_msg"][] = $error_msg;
       }
       if ($order_sts_kbn == "2" && ($order_reason_kbn == "05" || $order_reason_kbn == "06" || $order_reason_kbn == "08" || $order_reason_kbn == "20")) {
         $error_msg = "貸与終了の発注が登録されていた為、操作を完了できませんでした。貸与終了の発注を削除してから再度登録して下さい。";
         $json_list["error_msg"][] = $error_msg;
       }
       if ($order_sts_kbn == "2" && $order_reason_kbn == "07") {
         $error_msg = "不要品返却の発注が登録されていた為、操作を完了できませんでした。不要品返却の発注を削除してから再度登録して下さい。";
         $json_list["error_msg"][] = $error_msg;
       }
       if ($order_sts_kbn == "3" || $order_sts_kbn == "4") {
         $error_msg = "交換の発注が登録されていた為、操作を完了できませんでした。交換の発注を削除してから再度登録して下さい。";
         $json_list["error_msg"][] = $error_msg;
       }
       if ($order_sts_kbn == "5" && ($order_reason_kbn == "09" || $order_reason_kbn == "10" || $order_reason_kbn == "11" || $order_reason_kbn == "24")) {
         $error_msg = "職種変更または異動の発注が登録されていた為、操作を完了できませんでした。職種変更または異動の発注を削除してから再度登録して下さい。";
         $json_list["error_msg"][] = $error_msg;
       }

       echo json_encode($json_list);
       return;
     }

     // トランザクション開始
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
     try {
       // 発注依頼No.生成
       //※シーケンス取得
       $arg_str = "";
       $arg_str = "SELECT NEXTVAL('t_order_seq')";
       $t_order_tran = new TOrderTran();
       $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
       $result_obj = (array)$results;
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
         //ChromePhp::LOG($results);
         foreach ($results as $result) {
           $order_no_seq = $result->nextval;
         }
         //※次シーケンスをセット
         $arg_str = "";
         $arg_str = "SELECT SETVAL('t_order_seq',".$order_no_seq.")";
         $t_order_tran = new TOrderTran();
         $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
         $result_obj = (array)$results;
         $results_cnt = $result_obj["\0*\0_count"];
         //ChromePhp::LOG($result_obj);
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
             $order_no_seq = $result->setval;
           }
         }
       }
       $shin_order_req_no = "WB".str_pad($order_no_seq, 8, '0', STR_PAD_LEFT);
       //ChromePhp::LOG("発注依頼No採番");
       //ChromePhp::LOG($shin_order_req_no);

       if ($wearer_edit_post['wearer_tran_flg'] == "1") {
         //--着用者基本マスタトラン情報がある場合、更新処理--//
         $src_query_list = array();
         array_push($src_query_list, "corporate_id = '".$auth['corporate_id']."'");
         array_push($src_query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
         array_push($src_query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
         array_push($src_query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
         $src_query = implode(' AND ', $src_query_list);

         $up_query_list = array();
         // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
         $m_wearer_std_comb_hkey = md5(
           $auth['corporate_id']
           .$wearer_edit_post["werer_cd"]
           .$wearer_data_input['agreement_no']
           .$wearer_data_input['section']
           .$wearer_data_input['job_type']
         );
         array_push($up_query_list, "m_wearer_std_comb_hkey = '".$m_wearer_std_comb_hkey."'");
         // 発注No
         array_push($up_query_list, "order_req_no = '".$shin_order_req_no."'");
         // 企業ID
         array_push($up_query_list, "corporate_id = '".$auth['corporate_id']."'");
         // 着用者コード
         array_push($up_query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
         // 契約No
         array_push($up_query_list, "rntl_cont_no = '".$wearer_data_input['agreement_no']."'");
         // 部門コード
         array_push($up_query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
         // 貸与パターン
         $job_type_cd = explode(':', $wearer_data_input['job_type']);
         $job_type_cd = $job_type_cd[0];
         array_push($up_query_list, "job_type_cd = '".$job_type_cd."'");
         // 客先社員コード
         array_push($up_query_list, "cster_emply_cd = '".$wearer_data_input['member_no']."'");
         // 着用者名
         array_push($up_query_list, "werer_name = '".$wearer_data_input['member_name']."'");
         // 着用者名かな
         array_push($up_query_list, "werer_name_kana = '".$wearer_data_input['member_name_kana']."'");
         // 性別区分
         array_push($up_query_list, "sex_kbn = '".$wearer_data_input['sex_kbn']."'");
         // 着用者区分
         array_push($up_query_list, "werer_sts_kbn = '1'");
         // 異動日
         if (!empty($wearer_data_input['resfl_ymd'])) {
           $resfl_ymd = date('Ymd', strtotime($wearer_data_input['resfl_ymd']));
           array_push($up_query_list, "resfl_ymd = '".$resfl_ymd."'");
         } else {
           array_push($up_query_list, "resfl_ymd = NULL");
         }
         // 出荷先、出荷先支店コード
         if (!empty($wearer_data_input['shipment'])) {
           $shipment = explode(':', $wearer_data_input['shipment']);
           $ship_to_cd = $shipment[0];
           $ship_to_brnch_cd = $shipment[1];

           // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
           if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
             $query_list = array();
             array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
             array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
             array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
             $query = implode(' AND ', $query_list);

             $arg_str = '';
             $arg_str = 'SELECT ';
             $arg_str .= 'std_ship_to_cd,';
             $arg_str .= 'std_ship_to_brnch_cd';
             $arg_str .= ' FROM ';
             $arg_str .= 'm_section';
             $arg_str .= ' WHERE ';
             $arg_str .= $query;
             $m_section = new MSection();
             $results = new Resultset(NULL, $m_section, $m_section->getReadConnection()->query($arg_str));
             $results_array = (array) $results;
             $results_cnt = $results_array["\0*\0_count"];
             //ChromePhp::LOG($results_cnt);
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
               $ship_to_cd = $result->std_ship_to_cd;
               $ship_to_brnch_cd = $result->std_ship_to_brnch_cd;
             }
           }
           array_push($up_query_list, "ship_to_cd = '".$ship_to_cd."'");
           array_push($up_query_list, "ship_to_brnch_cd = '".$ship_to_brnch_cd."'");
         }
         // 発注状況区分
         array_push($up_query_list, "order_sts_kbn = '6'");
         // 更新区分(WEB発注システム(着用者変更）)
         array_push($up_query_list, "upd_kbn = '6'");
         // Web更新日時
         array_push($up_query_list, "web_upd_date = '".date("Y-m-d H:i:s", time())."'");
         // 送信区分(未送信)
         array_push($up_query_list, "snd_kbn = '0'");
         // 削除区分
         array_push($up_query_list, "del_kbn = '0'");
         // 更新日時
         array_push($up_query_list, "upd_date = '".date("Y-m-d H:i:s", time())."'");
         // 更新ユーザーID
         array_push($up_query_list, "upd_user_id = '".$auth['accnt_no']."'");
         // 更新PGID
         array_push($up_query_list, "upd_pg_id = '".$auth['accnt_no']."'");
         // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
         $m_job_type_comb_hkey = md5(
           $auth['corporate_id']
           .$wearer_data_input['agreement_no']
           .$wearer_data_input['job_type']
         );
         array_push($up_query_list, "m_job_type_comb_hkey = '".$m_job_type_comb_hkey."'");
         // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
         $m_section_comb_hkey = md5(
           $auth['corporate_id']
           .$wearer_data_input['agreement_no']
           .$wearer_data_input['section']
         );
         array_push($up_query_list, "m_section_comb_hkey = '".$m_section_comb_hkey."'");
         $up_query = implode(',', $up_query_list);

         $arg_str = "";
         $arg_str = "UPDATE m_wearer_std_tran SET ";
         $arg_str .= $up_query;
         $arg_str .= " WHERE ";
         $arg_str .= $src_query;
         //ChromePhp::LOG($arg_str);
         $m_wearer_std_tran = new MWearerStdTran();
         $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
         $result_obj = (array)$results;
         $results_cnt = $result_obj["\0*\0_count"];
         //ChromePhp::LOG($results_cnt);
       } else {
         //--着用者基本マスタトランに情報がない場合、登録処理--//
         //ChromePhp::LOG("着用者基本マスタトラン登録");
         $calum_list = array();
         $values_list = array();

         // 貸与パターン
         $job_type_cd = explode(':', $wearer_data_input['job_type']);
         $job_type_cd = $job_type_cd[0];
         array_push($calum_list, "job_type_cd");
         array_push($values_list, "'".$job_type_cd."'");
         // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
         $m_wearer_std_comb_hkey = md5(
           $auth['corporate_id']
           .$wearer_edit_post["werer_cd"]
           .$wearer_data_input['agreement_no']
           .$wearer_data_input['section']
           .$job_type_cd
         );
         array_push($calum_list, "m_wearer_std_comb_hkey");
         array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
         // 発注No
         array_push($calum_list, "order_req_no");
         array_push($values_list, "'".$shin_order_req_no."'");
         // 企業ID
         array_push($calum_list, "corporate_id");
         array_push($values_list, "'".$auth['corporate_id']."'");
         // 着用者コード
         array_push($calum_list, "werer_cd");
         array_push($values_list, "'".$wearer_edit_post['werer_cd']."'");
         // レンタル契約No
         array_push($calum_list, "rntl_cont_no");
         array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
         // レンタル部門コード
         array_push($calum_list, "rntl_sect_cd");
         array_push($values_list, "'".$wearer_data_input['section']."'");
         // 客先社員コード
         if (!empty($wearer_data_input['member_no'])) {
           array_push($calum_list, "cster_emply_cd");
           array_push($values_list, "'".$wearer_data_input['member_no']."'");
         }
         // 着用者名
         if (!empty($wearer_data_input['member_name'])) {
           array_push($calum_list, "werer_name");
           array_push($values_list, "'".$wearer_data_input['member_name']."'");
         }
         // 着用者名（かな）
         if (!empty($wearer_data_input['member_name_kana'])) {
           array_push($calum_list, "werer_name_kana");
           array_push($values_list, "'".$wearer_data_input['member_name_kana']."'");
         }
         // 性別区分
         array_push($calum_list, "sex_kbn");
         array_push($values_list, "'".$wearer_data_input['sex_kbn']."'");
         // 着用者状況区分(稼働)
         array_push($calum_list, "werer_sts_kbn");
         array_push($values_list, "'1'");
         // 異動日
         if (!empty($wearer_data_input['resfl_ymd'])) {
           $resfl_ymd = date('Ymd', strtotime($wearer_data_input['resfl_ymd']));
           array_push($calum_list, "resfl_ymd");
           array_push($values_list, "'".$resfl_ymd."'");
         } else {
           array_push($calum_list, "resfl_ymd");
           array_push($values_list, "NULL");
         }
         // 出荷先、出荷先支店コード
         if (!empty($wearer_data_input['shipment'])) {
           $shipment = explode(':', $wearer_data_input['shipment']);
           $ship_to_cd = $shipment[0];
           $ship_to_brnch_cd = $shipment[1];

           // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
           if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
             $query_list = array();
             array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
             array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
             array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
             $query = implode(' AND ', $query_list);

             $arg_str = '';
             $arg_str = 'SELECT ';
             $arg_str .= 'std_ship_to_cd,';
             $arg_str .= 'std_ship_to_brnch_cd';
             $arg_str .= ' FROM ';
             $arg_str .= 'm_section';
             $arg_str .= ' WHERE ';
             $arg_str .= $query;
             $m_section = new MSection();
             $results = new Resultset(NULL, $m_section, $m_section->getReadConnection()->query($arg_str));
             $results_array = (array) $results;
             $results_cnt = $results_array["\0*\0_count"];
             //ChromePhp::LOG($results_cnt);
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
               $ship_to_cd = $result->std_ship_to_cd;
               $ship_to_brnch_cd = $result->std_ship_to_brnch_cd;
             }
           }
           array_push($calum_list, "ship_to_cd");
           array_push($values_list, "'".$ship_to_cd."'");
           array_push($calum_list, "ship_to_brnch_cd");
           array_push($values_list, "'".$ship_to_brnch_cd."'");
         }
         // 発注状況区分(着用者編集)
         array_push($calum_list, "order_sts_kbn");
         array_push($values_list, "'6'");
         // 更新区分(WEB発注システム(着用者変更）)
         array_push($calum_list, "upd_kbn");
         array_push($values_list, "'6'");
         // Web更新日時
         array_push($calum_list, "web_upd_date");
         array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
         // 送信区分(未送信)
         array_push($calum_list, "snd_kbn");
         array_push($values_list, "'0'");
         // 削除区分
         array_push($calum_list, "del_kbn");
         array_push($values_list, "'0'");
         // 登録日時
         array_push($calum_list, "rgst_date");
         array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
         // 登録ユーザーID
         array_push($calum_list, "rgst_user_id");
         array_push($values_list, "'".$auth['accnt_no']."'");
         // 更新日時
         array_push($calum_list, "upd_date");
         array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
         // 更新ユーザーID
         array_push($calum_list, "upd_user_id");
         array_push($values_list, "'".$auth['accnt_no']."'");
         // 更新PGID
         array_push($calum_list, "upd_pg_id");
         array_push($values_list, "'".$auth['accnt_no']."'");
         // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
         $m_job_type_comb_hkey = md5(
           $auth['corporate_id']
           .$wearer_data_input['agreement_no']
           .$job_type_cd
         );
         array_push($calum_list, "m_job_type_comb_hkey");
         array_push($values_list, "'".$m_job_type_comb_hkey."'");
         // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
         $m_section_comb_hkey = md5(
           $auth['corporate_id']
           .$wearer_data_input['agreement_no']
           .$wearer_data_input['section']
         );
         array_push($calum_list, "m_section_comb_hkey");
         array_push($values_list, "'".$m_section_comb_hkey."'");
         $calum_query = implode(',', $calum_list);
         $values_query = implode(',', $values_list);

         $arg_str = "";
         $arg_str = "INSERT INTO m_wearer_std_tran";
         $arg_str .= "(".$calum_query.")";
         $arg_str .= " VALUES ";
         $arg_str .= "(".$values_query.")";
         //ChromePhp::LOG($arg_str);
         $m_wearer_std_tran = new MWearerStdTran();
         $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
         $results_cnt = $result_obj["\0*\0_count"];
         //ChromePhp::LOG($results_cnt);
       }

      // トランザクションコミット
      $m_wearer_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
    } catch (Exception $e) {
      // トランザクションロールバック
      $m_wearer_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));
      //ChromePhp::LOG($e);

      $json_list["error_code"] = "1";
      $error_msg = "発注登録処理において、データ更新エラーが発生しました。";
      array_push($json_list["error_msg"], $error_msg);

      echo json_encode($json_list);
      return;
    }

    echo json_encode($json_list);
  }
});

/**
 * 発注入力（着用者編集）
 * 発注送信処理
 */
$app->post('/wearer_edit_send', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
  $auth = $app->session->get("auth");
  //ChromePhp::LOG($auth);

  // 前画面セッション取得
  $wearer_edit_post = $app->session->get("wearer_edit_post");
  //ChromePhp::LOG($wearer_edit_post);

  // フロントパラメータ取得
  $mode = $params["mode"];
  $wearer_data_input = $params["wearer_data"];

  $json_list = array();
  // DB更新エラーコード 0:正常 その他:要因エラー
  $json_list["error_code"] = "0";
  $json_list["error_msg"] = array();

  if ($mode == "check") {
/*
    ChromePhp::LOG("着用者入力");
    ChromePhp::LOG($wearer_data_input);
*/
    //--入力内容確認--//
    // 社員コード
    if ($wearer_data_input['emply_cd_flg']) {
      if (mb_strlen($wearer_data_input['member_no']) == 0) {
        $json_list["error_code"] = "1";
        $error_msg = "社員コードありにチェックしている場合、社員コードを入力してください。";
        array_push($json_list["error_msg"], $error_msg);
      }
    }
    if (!$wearer_data_input['emply_cd_flg']) {
      if (mb_strlen($wearer_data_input['member_no']) > 0) {
        $json_list["error_code"] = "1";
        $error_msg = "社員コードありにチェックしていない場合、社員コードの入力は不要です。";
        array_push($json_list["error_msg"], $error_msg);
      }
    }
    // 着用者名
    if (empty($wearer_data_input["member_name"])) {
      $json_list["error_code"] = "1";
      $error_msg = "着用者名を入力してください。";
      array_push($json_list["error_msg"], $error_msg);
    }

    echo json_encode($json_list);
  } else if ($mode == "update") {
    //ChromePhp::LOG("着用者入力");
    //ChromePhp::LOG($wearer_data_input);

    //--発注NGパターンチェック--//
    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
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

      // 着用者編集の場合、何かしらの発注区分の情報がある際は発注NGとする
      $json_list["error_code"] = "1";
      if ($order_sts_kbn == "1" && $order_reason_kbn == "03") {
        $error_msg = "追加貸与の発注が登録されていた為、操作を完了できませんでした。追加貸与の発注を削除してから再度登録して下さい。";
        $json_list["error_msg"] = $error_msg;
      }
      if ($order_sts_kbn == "2" && ($order_reason_kbn == "05" || $order_reason_kbn == "06" || $order_reason_kbn == "08" || $order_reason_kbn == "20")) {
        $error_msg = "貸与終了の発注が登録されていた為、操作を完了できませんでした。貸与終了の発注を削除してから再度登録して下さい。";
        $json_list["error_msg"] = $error_msg;
      }
      if ($order_sts_kbn == "2" && $order_reason_kbn == "07") {
        $error_msg = "不要品返却の発注が登録されていた為、操作を完了できませんでした。不要品返却の発注を削除してから再度登録して下さい。";
        $json_list["error_msg"] = $error_msg;
      }
      if ($order_sts_kbn == "3" || $order_sts_kbn == "4") {
        $error_msg = "交換の発注が登録されていた為、操作を完了できませんでした。交換の発注を削除してから再度登録して下さい。";
        $json_list["error_msg"] = $error_msg;
      }
      if ($order_sts_kbn == "5" && ($order_reason_kbn == "09" || $order_reason_kbn == "10" || $order_reason_kbn == "11" || $order_reason_kbn == "24")) {
        $error_msg = "職種変更または異動の発注が登録されていた為、操作を完了できませんでした。職種変更または異動の発注を削除してから再度登録して下さい。";
        $json_list["error_msg"] = $error_msg;
      }

      echo json_encode($json_list);
      return;
    }

    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
    try {
      // 発注依頼No.生成
      //※シーケンス取得
      $arg_str = "";
      $arg_str = "SELECT NEXTVAL('t_order_seq')";
      $t_order_tran = new TOrderTran();
      $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
      $result_obj = (array)$results;
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
        //ChromePhp::LOG($results);
        foreach ($results as $result) {
          $order_no_seq = $result->nextval;
        }
        //※次シーケンスをセット
        $arg_str = "";
        $arg_str = "SELECT SETVAL('t_order_seq',".$order_no_seq.")";
        $t_order_tran = new TOrderTran();
        $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
        //ChromePhp::LOG($result_obj);
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
            $order_no_seq = $result->setval;
          }
        }
      }
      $shin_order_req_no = "WB".str_pad($order_no_seq, 8, '0', STR_PAD_LEFT);
      //ChromePhp::LOG("発注依頼No採番");
      //ChromePhp::LOG($shin_order_req_no);

      if ($wearer_edit_post['wearer_tran_flg'] == "1") {
        //--着用者基本マスタトラン情報がある場合、更新処理--//
        $src_query_list = array();
        array_push($src_query_list, "corporate_id = '".$auth['corporate_id']."'");
        array_push($src_query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
        array_push($src_query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
        array_push($src_query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
        $src_query = implode(' AND ', $src_query_list);

        $up_query_list = array();
        // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
        $m_wearer_std_comb_hkey = md5(
          $auth['corporate_id']
          .$wearer_edit_post["werer_cd"]
          .$wearer_data_input['agreement_no']
          .$wearer_data_input['section']
          .$wearer_data_input['job_type']
        );
        array_push($up_query_list, "m_wearer_std_comb_hkey = '".$m_wearer_std_comb_hkey."'");
        // 発注No
        array_push($up_query_list, "order_req_no = '".$shin_order_req_no."'");
        // 企業ID
        array_push($up_query_list, "corporate_id = '".$auth['corporate_id']."'");
        // 着用者コード
        array_push($up_query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
        // 契約No
        array_push($up_query_list, "rntl_cont_no = '".$wearer_data_input['agreement_no']."'");
        // 部門コード
        array_push($up_query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
        // 貸与パターン
        $job_type_cd = explode(':', $wearer_data_input['job_type']);
        $job_type_cd = $job_type_cd[0];
        array_push($up_query_list, "job_type_cd = '".$job_type_cd."'");
        // 客先社員コード
        array_push($up_query_list, "cster_emply_cd = '".$wearer_data_input['member_no']."'");
        // 着用者名
        array_push($up_query_list, "werer_name = '".$wearer_data_input['member_name']."'");
        // 着用者名かな
        array_push($up_query_list, "werer_name_kana = '".$wearer_data_input['member_name_kana']."'");
        // 性別区分
        array_push($up_query_list, "sex_kbn = '".$wearer_data_input['sex_kbn']."'");
        // 着用者区分
        array_push($up_query_list, "werer_sts_kbn = '1'");
        // 異動日
        if (!empty($wearer_data_input['resfl_ymd'])) {
          $resfl_ymd = date('Ymd', strtotime($wearer_data_input['resfl_ymd']));
          array_push($up_query_list, "resfl_ymd = '".$resfl_ymd."'");
        } else {
          array_push($up_query_list, "resfl_ymd = NULL");
        }
        // 出荷先、出荷先支店コード
        if (!empty($wearer_data_input['shipment'])) {
          $shipment = explode(':', $wearer_data_input['shipment']);
          $ship_to_cd = $shipment[0];
          $ship_to_brnch_cd = $shipment[1];

          // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
          if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
            array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
            $query = implode(' AND ', $query_list);

            $arg_str = '';
            $arg_str = 'SELECT ';
            $arg_str .= 'std_ship_to_cd,';
            $arg_str .= 'std_ship_to_brnch_cd';
            $arg_str .= ' FROM ';
            $arg_str .= 'm_section';
            $arg_str .= ' WHERE ';
            $arg_str .= $query;
            $m_section = new MSection();
            $results = new Resultset(NULL, $m_section, $m_section->getReadConnection()->query($arg_str));
            $results_array = (array) $results;
            $results_cnt = $results_array["\0*\0_count"];
            //ChromePhp::LOG($results_cnt);
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
              $ship_to_cd = $result->std_ship_to_cd;
              $ship_to_brnch_cd = $result->std_ship_to_brnch_cd;
            }
          }
          array_push($up_query_list, "ship_to_cd = '".$ship_to_cd."'");
          array_push($up_query_list, "ship_to_brnch_cd = '".$ship_to_brnch_cd."'");
        }
        // 発注状況区分
        array_push($up_query_list, "order_sts_kbn = '6'");
        // 更新区分(WEB発注システム(着用者変更）)
        array_push($up_query_list, "upd_kbn = '6'");
        // Web更新日時
        array_push($up_query_list, "web_upd_date = '".date("Y-m-d H:i:s", time())."'");
        // 送信区分(送信済み)
        array_push($up_query_list, "snd_kbn = '1'");
        // 削除区分
        array_push($up_query_list, "del_kbn = '0'");
        // 更新日時
        array_push($up_query_list, "upd_date = '".date("Y-m-d H:i:s", time())."'");
        // 更新ユーザーID
        array_push($up_query_list, "upd_user_id = '".$auth['accnt_no']."'");
        // 更新PGID
        array_push($up_query_list, "upd_pg_id = '".$auth['accnt_no']."'");
        // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
        $m_job_type_comb_hkey = md5(
          $auth['corporate_id']
          .$wearer_data_input['agreement_no']
          .$wearer_data_input['job_type']
        );
        array_push($up_query_list, "m_job_type_comb_hkey = '".$m_job_type_comb_hkey."'");
        // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
        $m_section_comb_hkey = md5(
          $auth['corporate_id']
          .$wearer_data_input['agreement_no']
          .$wearer_data_input['section']
        );
        array_push($up_query_list, "m_section_comb_hkey = '".$m_section_comb_hkey."'");
        $up_query = implode(',', $up_query_list);

        $arg_str = "";
        $arg_str = "UPDATE m_wearer_std_tran SET ";
        $arg_str .= $up_query;
        $arg_str .= " WHERE ";
        $arg_str .= $src_query;
        //ChromePhp::LOG($arg_str);
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
        //ChromePhp::LOG($results_cnt);
      } else {
        //--着用者基本マスタトランに情報がない場合、登録処理--//
        //ChromePhp::LOG("着用者基本マスタトラン登録");
        $calum_list = array();
        $values_list = array();

        // 貸与パターン
        $job_type_cd = explode(':', $wearer_data_input['job_type']);
        $job_type_cd = $job_type_cd[0];
        array_push($calum_list, "job_type_cd");
        array_push($values_list, "'".$job_type_cd."'");
        // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
        $m_wearer_std_comb_hkey = md5(
          $auth['corporate_id']
          .$wearer_edit_post["werer_cd"]
          .$wearer_data_input['agreement_no']
          .$wearer_data_input['section']
          .$job_type_cd
        );
        array_push($calum_list, "m_wearer_std_comb_hkey");
        array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
        // 発注No
        array_push($calum_list, "order_req_no");
        array_push($values_list, "'".$shin_order_req_no."'");
        // 企業ID
        array_push($calum_list, "corporate_id");
        array_push($values_list, "'".$auth['corporate_id']."'");
        // 着用者コード
        array_push($calum_list, "werer_cd");
        array_push($values_list, "'".$wearer_edit_post['werer_cd']."'");
        // レンタル契約No
        array_push($calum_list, "rntl_cont_no");
        array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
        // レンタル部門コード
        array_push($calum_list, "rntl_sect_cd");
        array_push($values_list, "'".$wearer_data_input['section']."'");
        // 客先社員コード
        if (!empty($wearer_data_input['member_no'])) {
          array_push($calum_list, "cster_emply_cd");
          array_push($values_list, "'".$wearer_data_input['member_no']."'");
        }
        // 着用者名
        if (!empty($wearer_data_input['member_name'])) {
          array_push($calum_list, "werer_name");
          array_push($values_list, "'".$wearer_data_input['member_name']."'");
        }
        // 着用者名（かな）
        if (!empty($wearer_data_input['member_name_kana'])) {
          array_push($calum_list, "werer_name_kana");
          array_push($values_list, "'".$wearer_data_input['member_name_kana']."'");
        }
        // 性別区分
        array_push($calum_list, "sex_kbn");
        array_push($values_list, "'".$wearer_data_input['sex_kbn']."'");
        // 着用者状況区分(稼働)
        array_push($calum_list, "werer_sts_kbn");
        array_push($values_list, "'1'");
        // 異動日
        if (!empty($wearer_data_input['resfl_ymd'])) {
          $resfl_ymd = date('Ymd', strtotime($wearer_data_input['resfl_ymd']));
          array_push($calum_list, "resfl_ymd");
          array_push($values_list, "'".$resfl_ymd."'");
        } else {
          array_push($calum_list, "resfl_ymd");
          array_push($values_list, "NULL");
        }
        // 出荷先、出荷先支店コード
        if (!empty($wearer_data_input['shipment'])) {
          $shipment = explode(':', $wearer_data_input['shipment']);
          $ship_to_cd = $shipment[0];
          $ship_to_brnch_cd = $shipment[1];

          // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
          if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
            array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
            $query = implode(' AND ', $query_list);

            $arg_str = '';
            $arg_str = 'SELECT ';
            $arg_str .= 'std_ship_to_cd,';
            $arg_str .= 'std_ship_to_brnch_cd';
            $arg_str .= ' FROM ';
            $arg_str .= 'm_section';
            $arg_str .= ' WHERE ';
            $arg_str .= $query;
            $m_section = new MSection();
            $results = new Resultset(NULL, $m_section, $m_section->getReadConnection()->query($arg_str));
            $results_array = (array) $results;
            $results_cnt = $results_array["\0*\0_count"];
            //ChromePhp::LOG($results_cnt);
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
              $ship_to_cd = $result->std_ship_to_cd;
              $ship_to_brnch_cd = $result->std_ship_to_brnch_cd;
            }
          }
          array_push($calum_list, "ship_to_cd");
          array_push($values_list, "'".$ship_to_cd."'");
          array_push($calum_list, "ship_to_brnch_cd");
          array_push($values_list, "'".$ship_to_brnch_cd."'");
        }
        // 発注状況区分(着用者編集)
        array_push($calum_list, "order_sts_kbn");
        array_push($values_list, "'6'");
        // 更新区分(WEB発注システム(着用者変更）)
        array_push($calum_list, "upd_kbn");
        array_push($values_list, "'6'");
        // Web更新日時
        array_push($calum_list, "web_upd_date");
        array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
        // 送信区分(送信済み)
        array_push($calum_list, "snd_kbn");
        array_push($values_list, "'1'");
        // 削除区分
        array_push($calum_list, "del_kbn");
        array_push($values_list, "'0'");
        // 登録日時
        array_push($calum_list, "rgst_date");
        array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
        // 登録ユーザーID
        array_push($calum_list, "rgst_user_id");
        array_push($values_list, "'".$auth['accnt_no']."'");
        // 更新日時
        array_push($calum_list, "upd_date");
        array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
        // 更新ユーザーID
        array_push($calum_list, "upd_user_id");
        array_push($values_list, "'".$auth['accnt_no']."'");
        // 更新PGID
        array_push($calum_list, "upd_pg_id");
        array_push($values_list, "'".$auth['accnt_no']."'");
        // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
        $m_job_type_comb_hkey = md5(
          $auth['corporate_id']
          .$wearer_data_input['agreement_no']
          .$job_type_cd
        );
        array_push($calum_list, "m_job_type_comb_hkey");
        array_push($values_list, "'".$m_job_type_comb_hkey."'");
        // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
        $m_section_comb_hkey = md5(
          $auth['corporate_id']
          .$wearer_data_input['agreement_no']
          .$wearer_data_input['section']
        );
        array_push($calum_list, "m_section_comb_hkey");
        array_push($values_list, "'".$m_section_comb_hkey."'");
        $calum_query = implode(',', $calum_list);
        $values_query = implode(',', $values_list);

        $arg_str = "";
        $arg_str = "INSERT INTO m_wearer_std_tran";
        $arg_str .= "(".$calum_query.")";
        $arg_str .= " VALUES ";
        $arg_str .= "(".$values_query.")";
        //ChromePhp::LOG($arg_str);
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
        $results_cnt = $result_obj["\0*\0_count"];
        //ChromePhp::LOG($results_cnt);
      }

     // トランザクションコミット
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
   } catch (Exception $e) {
     // トランザクションロールバック
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));
     ChromePhp::LOG($e);

     $json_list["error_code"] = "1";
     $error_msg = "発注送信処理において、データ更新エラーが発生しました。";
     array_push($json_list["error_msg"], $error_msg);

     echo json_encode($json_list);
     return;
   }

   echo json_encode($json_list);
 }
});
