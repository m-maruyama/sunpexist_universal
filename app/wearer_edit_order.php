<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 発注入力（着用者編集）
 * 入力項目：契約No以外の初期値情報、前画面セッション取得
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
  // 発注No
  $json_list['order_req_no'] = $wearer_edit_post["order_req_no"];

  echo json_encode($json_list);
});

/**
 * 発注入力（着用者編集）
 * 入力項目：現在貸与中のアイテム、新たに追加するアイテム
 */
 $app->post('/wearer_change_list', function ()use($app){
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

   //--一覧生成用の主要部門コード・職種コード取得--//
   // 着用者基本マスタ参照
   $query_list = array();
   array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
   array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
   array_push($query_list, "werer_sts_kbn = '1'");
   $query = implode(' AND ', $query_list);

   $arg_str = "";
   $arg_str = "SELECT ";
   $arg_str .= "rntl_sect_cd,";
   $arg_str .= "job_type_cd";
   $arg_str .= " FROM ";
   $arg_str .= "m_wearer_std";
   $arg_str .= " WHERE ";
   $arg_str .= $query;
   $arg_str .= " ORDER BY upd_date DESC";

   $m_weare_std = new MWearerStd();
   $results = new Resultset(NULL, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   $m_wearer_cnt = $results_cnt;
   $m_wearer_rntl_sect_cd = NULL;
   $m_wearer_job_type_cd = NULL;
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
       //--以降、【発注前】職種コードとして使用--//
       // 着用者基本マスタ.レンタル部門コード
       $m_wearer_rntl_sect_cd = $result->rntl_sect_cd;
       // 着用者基本マスタ.職種コード
       $m_wearer_job_type_cd = $result->job_type_cd;
     }
   }
   //ChromePhp::LOG('【発注前】部門コード、職種コード');
   //ChromePhp::LOG($m_wearer_rntl_sect_cd);
   //ChromePhp::LOG($m_wearer_job_type_cd);

   // 発注情報トラン参照
   $query_list = array();
   array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
   array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
   array_push($query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
   array_push($query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
   array_push($query_list, "order_req_no = '".$wearer_edit_post['order_req_no']."'");
   $query = implode(' AND ', $query_list);

   $arg_str = "";
   $arg_str = "SELECT ";
   $arg_str .= "rntl_sect_cd,";
   $arg_str .= "job_type_cd";
   $arg_str .= " FROM ";
   $arg_str .= "t_order_tran";
   $arg_str .= " WHERE ";
   $arg_str .= $query;
   $arg_str .= " ORDER BY upd_date DESC";

   $t_order_tran = new TOrderTran();
   $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   $t_order_tran_cnt = $results_cnt;
   $t_order_tran_rntl_sect_cd = NULL;
   $t_order_tran_job_type_cd = NULL;
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
       // 発注情報トラン.レンタル部門コード
       $t_order_tran_rntl_sect_cd = $result->rntl_sect_cd;
       // 発注情報トラン.職種コード
       $t_order_tran_job_type_cd = $result->job_type_cd;
     }
   }

   // 返却予定情報トラン参照
   $query_list = array();
   array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
   array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
   array_push($query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
   array_push($query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
   array_push($query_list, "order_req_no = '".$wearer_edit_post['order_req_no']."'");
   $query = implode(' AND ', $query_list);

   $arg_str = "";
   $arg_str = "SELECT ";
   $arg_str .= "rntl_sect_cd,";
   $arg_str .= "job_type_cd";
   $arg_str .= " FROM ";
   $arg_str .= "t_returned_plan_info_tran";
   $arg_str .= " WHERE ";
   $arg_str .= $query;
   $arg_str .= " ORDER BY order_req_no DESC";

   $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
   $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   $t_returned_plan_info_tran_cnt = $results_cnt;
   $t_returned_plan_info_tran_rntl_sect_cd = NULL;
   $t_returned_plan_info_tran_job_type_cd = NULL;
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
       // 発注情報トラン.レンタル部門コード
       $t_returned_plan_info_tran_rntl_sect_cd = $result->rntl_sect_cd;
       // 発注情報トラン.職種コード
       $t_returned_plan_info_tran_job_type_cd = $result->job_type_cd;
     }
   }

   //--【変更後】部門コード、職種コードの設定--//
   // 部門コード
   $chg_wearer_rntl_sect_cd = NULL;
   if (!empty($t_order_tran_rntl_sect_cd)) {
     $chg_wearer_rntl_sect_cd = $t_order_tran_rntl_sect_cd;
   } elseif (!empty($t_returned_plan_info_tran_rntl_sect_cd)) {
     $chg_wearer_rntl_sect_cd = $t_returned_plan_info_tran_rntl_sect_cd;
   }
   // 職種コード
   $chg_wearer_job_type_cd = NULL;
   if (!empty($cond["job_type"])) {
     // 着用者情報項目「貸与パターン」が変更された場合
     $chg_wearer_job_type_cd = $cond["job_type"];
   } else {
     // 初期表示時
     if (!empty($t_order_tran_job_type_cd)) {
       $chg_wearer_job_type_cd = $t_order_tran_job_type_cd;
     } elseif (!empty($t_returned_plan_info_tran_job_type_cd)) {
       $chg_wearer_job_type_cd = $t_returned_plan_info_tran_job_type_cd;
     }
   }
   //ChromePhp::LOG('【発注後】部門コード、職種コード');
   //ChromePhp::LOG($chg_wearer_rntl_sect_cd);
   //ChromePhp::LOG($chg_wearer_job_type_cd);

   //--【変更前】商品の取得--//
   $query_list = array();
   $list = array();
   $now_wearer_list = array();

   array_push($query_list, "mw.corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "mw.rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
   array_push($query_list, "mw.werer_cd = '".$wearer_edit_post['werer_cd']."'");
   array_push($query_list, "mw.rntl_sect_cd = '".$m_wearer_rntl_sect_cd."'");
   array_push($query_list, "mw.job_type_cd = '".$m_wearer_job_type_cd."'");
   array_push($query_list, "mw.werer_sts_kbn = '1'");
   array_push($query_list, "mi.corporate_id = '".$auth['corporate_id']."'");
   $query = implode(' AND ', $query_list);

   $arg_str = "";
   $arg_str = "SELECT ";
   $arg_str .= "*";
   $arg_str .= " FROM ";
   $arg_str .= "(SELECT distinct on (mi.item_cd,mi.color_cd) ";
   $arg_str .= "mw.rntl_cont_no as as_rntl_cont_no,";
   $arg_str .= "mw.rntl_sect_cd as as_rntl_sect_cd,";
   $arg_str .= "mi.item_cd as as_item_cd,";
   $arg_str .= "mi.color_cd as_color_cd,";
   $arg_str .= "mi.size_cd as_size_cd,";
   $arg_str .= "mi.item_name as_item_name,";
   $arg_str .= "mii.job_type_cd as_job_type_cd,";
   $arg_str .= "mii.job_type_item_cd as_job_type_item_cd,";
   $arg_str .= "mii.size_two_cd as_size_two_cd,";
   $arg_str .= "mii.std_input_qty as_std_input_qty,";
   $arg_str .= "mii.input_item_name as as_input_item_name";
   $arg_str .= " FROM ";
   $arg_str .= "(m_input_item as mii INNER JOIN m_item as mi ON (mii.item_cd=mi.item_cd AND mii.color_cd=mi.color_cd))";
   $arg_str .= " INNER JOIN ";
   $arg_str .= "(m_wearer_std as mw INNER JOIN m_job_type as mj ON (mw.corporate_id=mj.corporate_id AND mw.rntl_cont_no=mj.rntl_cont_no AND mw.job_type_cd=mj.job_type_cd))";
   $arg_str .= " ON (mii.corporate_id=mj.corporate_id AND mii.rntl_cont_no=mj.rntl_cont_no AND mii.job_type_cd=mj.job_type_cd)";
   $arg_str .= " WHERE ";
   $arg_str .= $query;
   $arg_str .= ") as distinct_table";
   $arg_str .= " ORDER BY as_item_cd,as_color_cd ASC";

   $m_input_item = new MInputItem();
   $results = new Resultset(NULL, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   if (!empty($results_cnt)) {
     $paginator_model = new PaginatorModel(
         array(
             "data"  => $results,
             "limit" => $results_cnt,
             "page" => 1
         )
     );
     $paginator = $paginator_model->getPaginate();
     $results = $paginator->items;
     //ChromePhp::LOG($results);
     foreach ($results as $result) {
       // レンタル契約No
       $list["rntl_cont_no"] = $result->as_rntl_cont_no;
       // 商品コード
       $list["item_cd"] = $result->as_item_cd;
       // 色コード
       $list["color_cd"] = $result->as_color_cd;
       // サイズコード
       $list["size_cd"] = $result->as_size_cd;
       // 商品名
       $list["item_name"] = $result->as_item_name;
       // 職種コード
       $list["job_type_cd"] = $result->as_job_type_cd;
       // 部門コード
       $list["rntl_sect_cd"] = $result->as_rntl_sect_cd;
       // 職種アイテムコード
       $list["job_type_item_cd"] = $result->as_job_type_item_cd;
       // サイズコード2
       $list["size_two_cd"] = $result->as_size_two_cd;
       // 標準投入数
       $list["std_input_qty"] = $result->as_std_input_qty;
       // 投入商品名
       $list["input_item_name"] = $result->as_input_item_name;

       array_push($now_wearer_list, $list);
     }
   }
   //ChromePhp::LOG('【変更前】商品リスト');
   //ChromePhp::LOG(count($now_wearer_list));
   //ChromePhp::LOG($now_wearer_list);

   //--【変更後】商品の取得--//
   $query_list = array();
   $list = array();
   $chg_wearer_list = array();

   array_push($query_list, "mw.corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "mw.rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
   array_push($query_list, "mw.werer_cd = '".$wearer_edit_post['werer_cd']."'");
   array_push($query_list, "mw.rntl_sect_cd = '".$chg_wearer_rntl_sect_cd."'");
   array_push($query_list, "mw.job_type_cd = '".$chg_wearer_job_type_cd."'");
   array_push($query_list, "mw.werer_sts_kbn = '1'");
   array_push($query_list, "mi.corporate_id = '".$auth['corporate_id']."'");
   $query = implode(' AND ', $query_list);

   $arg_str = "";
   $arg_str = "SELECT ";
   $arg_str .= "*";
   $arg_str .= " FROM ";
   $arg_str .= "(SELECT distinct on (mi.item_cd,mi.color_cd) ";
   $arg_str .= "mw.rntl_cont_no as as_rntl_cont_no,";
   $arg_str .= "mw.rntl_sect_cd as as_rntl_sect_cd,";
   $arg_str .= "mi.item_cd as as_item_cd,";
   $arg_str .= "mi.color_cd as_color_cd,";
   $arg_str .= "mi.size_cd as_size_cd,";
   $arg_str .= "mi.item_name as_item_name,";
   $arg_str .= "mii.job_type_cd as_job_type_cd,";
   $arg_str .= "mii.job_type_item_cd as_job_type_item_cd,";
   $arg_str .= "mii.size_two_cd as_size_two_cd,";
   $arg_str .= "mii.std_input_qty as_std_input_qty,";
   $arg_str .= "mii.input_item_name as as_input_item_name";
   $arg_str .= " FROM ";
   $arg_str .= "(m_input_item as mii INNER JOIN m_item as mi ON (mii.item_cd=mi.item_cd AND mii.color_cd=mi.color_cd))";
   $arg_str .= " INNER JOIN ";
   $arg_str .= "(m_wearer_std as mw INNER JOIN m_job_type as mj ON (mw.corporate_id=mj.corporate_id AND mw.rntl_cont_no=mj.rntl_cont_no AND mw.job_type_cd=mj.job_type_cd))";
   $arg_str .= " ON (mii.corporate_id=mj.corporate_id AND mii.rntl_cont_no=mj.rntl_cont_no AND mii.job_type_cd=mj.job_type_cd)";
   $arg_str .= " WHERE ";
   $arg_str .= $query;
   $arg_str .= ") as distinct_table";
   $arg_str .= " ORDER BY as_item_cd,as_color_cd ASC";

   $m_input_item = new MInputItem();
   $results = new Resultset(NULL, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   if (!empty($results_cnt)) {
     $paginator_model = new PaginatorModel(
         array(
             "data"  => $results,
             "limit" => $results_cnt,
             "page" => 1
         )
     );
     $paginator = $paginator_model->getPaginate();
     $results = $paginator->items;
     //ChromePhp::LOG($results);
     foreach ($results as $result) {
       // レンタル契約No
       $list["rntl_cont_no"] = $result->as_rntl_cont_no;
       // 商品コード
       $list["item_cd"] = $result->as_item_cd;
       // 色コード
       $list["color_cd"] = $result->as_color_cd;
       // サイズコード
       $list["size_cd"] = $result->as_size_cd;
       // 商品名
       $list["item_name"] = $result->as_item_name;
       // 職種コード
       $list["job_type_cd"] = $result->as_job_type_cd;
       // 部門コード
       $list["rntl_sect_cd"] = $result->as_rntl_sect_cd;
       // 職種アイテムコード
       $list["job_type_item_cd"] = $result->as_job_type_item_cd;
       // サイズコード2
       $list["size_two_cd"] = $result->as_size_two_cd;
       // 標準投入数
       $list["std_input_qty"] = $result->as_std_input_qty;
       // 投入商品名
       $list["input_item_name"] = $result->as_input_item_name;

       array_push($chg_wearer_list, $list);
     }
   }
   //ChromePhp::LOG('【変更後】商品リスト');
   //ChromePhp::LOG(count($chg_wearer_list));
   //ChromePhp::LOG($chg_wearer_list);

   //--新たに追加されるアイテム一覧リストの生成--//
   $chk_list = array();
   $add_list = array();

   // 発注前後リストの商品比較処理
   for ($i=0; $i<count($chg_wearer_list); $i++) {
     $list = array();
     $chg_wearer_list[$i]["overlap_flg"] = true;
     for ($j=0; $j<count($now_wearer_list); $j++) {
       if (
        $chg_wearer_list[$i]["item_cd"] == $now_wearer_list[$j]["item_cd"]
        && $chg_wearer_list[$i]["color_cd"] == $now_wearer_list[$j]["color_cd"]
        && $chg_wearer_list[$i]["std_input_qty"] == $now_wearer_list[$i]["std_input_qty"]
       )
       {
         $chg_wearer_list[$i]["overlap_flg"] = false;
       }
     }
     if ($chg_wearer_list[$i]["overlap_flg"]) {
       $list["item_cd"] = $chg_wearer_list[$i]["item_cd"];
       $list["color_cd"] = $chg_wearer_list[$i]["color_cd"];
       $list["size_cd"] = $chg_wearer_list[$i]["size_cd"];
       $list["item_name"] = $chg_wearer_list[$i]["item_name"];
       $list["job_type_cd"] = $chg_wearer_list[$i]["job_type_cd"];
       $list["rntl_sect_cd"] = $chg_wearer_list[$i]["rntl_sect_cd"];
       $list["job_type_item_cd"] = $chg_wearer_list[$i]["job_type_item_cd"];
       $list["size_two_cd"] = $chg_wearer_list[$i]["size_two_cd"];
       $list["std_input_qty"] = $chg_wearer_list[$i]["std_input_qty"];
       $list["input_item_name"] = $chg_wearer_list[$i]["input_item_name"];

       array_push($chk_list, $list);
     }
   }
   //ChromePhp::LOG('発注後のみ商品リスト');
   //ChromePhp::LOG(count($chk_list));
   //ChromePhp::LOG($chk_list);

   // 上記比較リストをベースに、新たに追加されるアイテム一覧リストを生成する
   if (!empty($chk_list)) {
     $arr_cnt = 0;
     $list_cnt = 1;
     foreach ($chk_list as $chk_map) {
       $list = array();
       // name属性用カウント値
       $list["arr_num"] = $arr_cnt++;
       // No
       $list["list_no"] = $list_cnt++;
       // アイテム
       $list["item_name"] = $chk_map["item_name"];
       // 選択方法
       //※着用者の職種マスタ.職種コードに紐づく投入商品マスタの職種アイテムコード単位で単一or複数判断
       $query_list = array();
       array_push($query_list, "m_job_type.corporate_id = '".$auth['corporate_id']."'");
       array_push($query_list, "m_job_type.rntl_cont_no = '".$chk_map['rntl_cont_no']."'");
       array_push($query_list, "m_job_type.job_type_cd = '".$chk_map['job_type_cd']."'");
       array_push($query_list, "m_input_item.job_type_cd = '".$chk_map['job_type_cd']."'");
       array_push($query_list, "m_input_item.item_cd = '".$chk_map['item_cd']."'");
       array_push($query_list, "m_input_item.color_cd = '".$chk_map['color_cd']."'");
       array_push($query_list, "m_input_item.size_two_cd = '".$chk_map['size_two_cd']."'");
       $query = implode(' AND ', $query_list);

       $arg_str = "";
       $arg_str = "SELECT ";
       $arg_str .= "m_input_item.job_type_item_cd";
       $arg_str .= " FROM ";
       $arg_str .= "m_input_item";
       $arg_str .= " INNER JOIN ";
       $arg_str .= "m_job_type";
       $arg_str .= " ON ";
       $arg_str .= "m_input_item.m_job_type_comb_hkey=m_job_type.m_job_type_comb_hkey";
       $arg_str .= " WHERE ";
       $arg_str .= $query;

       $m_input_item = new MInputItem();
       $results = new Resultset(NULL, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
       $result_obj = (array)$results;
       $results_cnt = $result_obj["\0*\0_count"];
       if ($results_cnt > 1) {
         $list["choice"] = "複数選択";
         $list["choice_type"] = "2";
       } else {
         $list["choice"] = "単一選択";
         $list["choice_type"] = "1";
       }
       // 標準枚数
       $list["std_input_qty"] = $chk_map['std_input_qty'];
       // 商品-色
       $list["item_and_color"] = $chk_map['item_cd']."-".$chk_map['color_cd'];
       // 商品名
       $list["input_item_name"] = $chk_map['input_item_name'];
       // サイズ
       $list["size_cd"] = array();
       $query_list = array();
       array_push($query_list, "m_item.item_cd = '".$chk_map['item_cd']."'");
       array_push($query_list, "m_item.color_cd = '".$chk_map['color_cd']."'");
       $query = implode(' AND ', $query_list);

       $arg_str = "";
       $arg_str = "SELECT ";
       $arg_str .= "size_cd";
       $arg_str .= " FROM ";
       $arg_str .= "m_item";
       $arg_str .= " WHERE ";
       $arg_str .= $query;

       $m_item = new MItem();
       $results = new Resultset(NULL, $m_item, $m_item->getReadConnection()->query($arg_str));
       $result_obj = (array)$results;
       $results_cnt = $result_obj["\0*\0_count"];
       if (!empty($results_cnt)) {
         $paginator_model = new PaginatorModel(
             array(
                 "data"  => $results,
                 "limit" => $results_cnt,
                 "page" => 1
             )
         );
         $paginator = $paginator_model->getPaginate();
         $results = $paginator->items;
         //ChromePhp::LOG($results);
         foreach ($results as $result) {
           array_push($list["size_cd"], $result->as_size_cd);
         }
       }
       // 発注数(単一選択=入力不可、複数選択=入力可)
       $list["order_num"] = $chk_map['std_input_qty'];
       if ($list["choice_type"] == "1") {
         $list["order_num_disable"] = "disabled";
       } else {
         $list["order_num_disable"] = "";
       }

       //--その他の必要hiddenパラメータ--//
       // 部門コード
       $list["rntl_sect_cd"] = $chk_map['rntl_sect_cd'];
       // 職種コード
       $list["job_type_cd"] = $chk_map['job_type_cd'];
       // 商品コード
       $list["item_cd"] = $chk_map['item_cd'];
       // 色コード
       $list["color_cd"] = $chk_map['color_cd'];
       // 職種アイテムコード
       $list["job_type_item_cd"] = $chk_map['job_type_item_cd'];

       array_push($add_list, $list);
     }
   }

   $json_list["add_list_disp_flg"] = true;
   if (count($add_list) == 0) {
     $json_list["add_list_disp_flg"] = false;
   }

   $json_list["add_list_cnt"] = count($add_list);
   $json_list["add_list"] = $add_list;
   //ChromePhp::LOG('新たに追加するアイテム一覧リスト');
   //ChromePhp::LOG(count($add_list));
   //ChromePhp::LOG($json_list["add_list"]);

   //--現在貸与中アイテム一覧リストの生成--//
   $chk_list = array();
   $now_list = array();

   if (!empty($now_wearer_list)) {
     $arr_cnt = 0;
     $list_cnt = 1;
     foreach ($now_wearer_list as $now_wearer_map) {
       $list = array();

       // 発注前後商品の比較チェック
       // ※商品の「標準投入数」が前後で=の場合は表示しない
       $overlap = true;
       for ($i=0; $i<count($chg_wearer_list); $i++) {
         if (
          $now_wearer_map['item_cd'] == $chg_wearer_list[$i]['item_cd']
          && $now_wearer_map['color_cd'] == $chg_wearer_list[$i]['color_cd']
          && $now_wearer_map['std_input_qty'] == $chg_wearer_list[$i]['std_input_qty']
         )
         {
           $overlap = false;
         }
       }
       if (!$overlap) {
         // 上記重複の場合は以降の処理をしない
         continue;
       }

       // name属性用カウント値
       $list["arr_num"] = $arr_cnt++;
       // No
       $list["list_no"] = $list_cnt++;
       // アイテム
       $list["item_name"] = $now_wearer_map["item_name"];
       // 選択方法
       //※着用者の職種マスタ.職種コードに紐づく投入商品マスタの職種アイテムコード単位で単一or複数判断
       $query_list = array();
       array_push($query_list, "m_job_type.corporate_id = '".$auth['corporate_id']."'");
       array_push($query_list, "m_job_type.rntl_cont_no = '".$now_wearer_map['rntl_cont_no']."'");
       array_push($query_list, "m_job_type.job_type_cd = '".$now_wearer_map['job_type_cd']."'");
       array_push($query_list, "m_input_item.job_type_cd = '".$now_wearer_map['job_type_cd']."'");
       array_push($query_list, "m_input_item.item_cd = '".$now_wearer_map['item_cd']."'");
       array_push($query_list, "m_input_item.color_cd = '".$now_wearer_map['color_cd']."'");
       array_push($query_list, "m_input_item.size_two_cd = '".$now_wearer_map['size_two_cd']."'");
       $query = implode(' AND ', $query_list);

       $arg_str = "";
       $arg_str = "SELECT ";
       $arg_str .= "m_input_item.job_type_item_cd";
       $arg_str .= " FROM ";
       $arg_str .= "m_input_item";
       $arg_str .= " INNER JOIN ";
       $arg_str .= "m_job_type";
       $arg_str .= " ON ";
       $arg_str .= "m_input_item.m_job_type_comb_hkey=m_job_type.m_job_type_comb_hkey";
       $arg_str .= " WHERE ";
       $arg_str .= $query;

       $m_input_item = new MInputItem();
       $results = new Resultset(NULL, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
       $result_obj = (array)$results;
       $results_cnt = $result_obj["\0*\0_count"];
       if ($results_cnt > 1) {
         $list["choice"] = "複数選択";
         $list["choice_type"] = "2";
       } else {
         $list["choice"] = "単一選択";
         $list["choice_type"] = "1";
       }
       // 標準枚数
       $list["std_input_qty"] = $now_wearer_map['std_input_qty'];
       // 商品-色
       $list["item_and_color"] = $now_wearer_map['item_cd']."-".$now_wearer_map['color_cd'];
       // 商品名
       $list["input_item_name"] = $now_wearer_map['input_item_name'];
       // サイズ
       //※着用者商品マスタのサイズコードを表示
       $query_list = array();
       array_push($query_list, "m_wearer_item.corporate_id = '".$auth['corporate_id']."'");
       array_push($query_list, "m_wearer_item.rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
       array_push($query_list, "m_wearer_item.werer_cd = '".$wearer_edit_post['werer_cd']."'");
       array_push($query_list, "m_wearer_item.rntl_sect_cd = '".$m_wearer_rntl_sect_cd."'");
       array_push($query_list, "m_wearer_item.job_type_cd = '".$m_wearer_job_type_cd."'");
       array_push($query_list, "m_wearer_item.item_cd = '".$now_wearer_map['item_cd']."'");
       array_push($query_list, "m_wearer_item.color_cd = '".$now_wearer_map['color_cd']."'");
       array_push($query_list, "m_wearer_std.werer_sts_kbn = '1'");
       $query = implode(' AND ', $query_list);

       $arg_str = "";
       $arg_str = "SELECT ";
       $arg_str .= "m_wearer_item.size_cd as as_size_cd";
       $arg_str .= " FROM ";
       $arg_str .= "m_wearer_std";
       $arg_str .= " INNER JOIN ";
       $arg_str .= "m_wearer_item";
       $arg_str .= " ON ";
       $arg_str .= "m_wearer_std.m_wearer_std_comb_hkey=m_wearer_item.m_wearer_std_comb_hkey";
       $arg_str .= " WHERE ";
       $arg_str .= $query;

       $m_wearer_std = new MWearerStd();
       $results = new Resultset(NULL, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
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
           $list["size_cd"] = $result->as_size_cd;
         }
       } else {
         $list["size_cd"] = "";
       }
       // 個体管理番号
       // ※発注前商品.標準投入数、発注後商品.標準投入数比較で対象行個体管理番号、対象の表示ON/OFF
       $orverlap = false;
       for ($i=0; $i<count($chg_wearer_list); $i++) {
         if (
          $now_wearer_map['item_cd'] == $chg_wearer_list[$i]['item_cd']
          && $now_wearer_map['color_cd'] == $chg_wearer_list[$i]['color_cd']
         )
         {
           $orverlap = true;
           if ($now_wearer_map['std_input_qty'] > $chg_wearer_list[$i]['std_input_qty']) {
             // 対象チェック、個体管理番号欄表示
             $list["individual_disp"] = true;
           } else {
             // 対象チェック、個体管理番号欄非表示
             $list["individual_disp"] = false;
           }
         }
       }

       // ※個体管理番号リスト、対象チェックボックス値の生成
       $list["individual_ctrl_no"] = "";
       $list["individual_chk"] = array();
       $individual_ctrl_no = array();
       $query_list = array();
       array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
       array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
       array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_edit_post['werer_cd']."'");
       array_push($query_list, "t_delivery_goods_state_details.item_cd = '".$now_wearer_map['item_cd']."'");
       array_push($query_list, "t_delivery_goods_state_details.color_cd = '".$now_wearer_map['color_cd']."'");
       array_push($query_list, "t_delivery_goods_state_details.size_cd = '".$list["size_cd"]."'");
       $query = implode(' AND ', $query_list);

       $arg_str = "";
       $arg_str = "SELECT ";
       $arg_str .= "individual_ctrl_no,";
       $arg_str .= "rtn_ok_flg";
       $arg_str .= " FROM ";
       $arg_str .= "t_delivery_goods_state_details";
       $arg_str .= " WHERE ";
       $arg_str .= $query;

       $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
       $results = new Resultset(NULL, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
       $result_obj = (array)$results;
       $results_cnt = $result_obj["\0*\0_count"];
       //ChromePhp::LOG($t_delivery_goods_state_details->getReadConnection()->query($arg_str));
       //ChromePhp::LOG($results_cnt);
       if (!empty($results_cnt)) {
         $paginator_model = new PaginatorModel(
             array(
                 "data"  => $results,
                 "limit" => $results_cnt,
                 "page" => 1
             )
         );
         $paginator = $paginator_model->getPaginate();
         $results = $paginator->items;
         //ChromePhp::LOG($results);
         foreach ($results as $result) {
           array_push($individual_ctrl_no, $result->individual_ctrl_no);

           // 返却可能フラグによるdisable制御
           $individual = array();
           $individual["individual_ctrl_no"] = $result->individual_ctrl_no;
           if ($result->rtn_ok_flg == '0') {
             $individual["disabled"] = "disabled";
           } else {
             $individual["disabled"] = "";
//             $individual["disabled"] = "disabled";
           }

           // 表示時チェックON/OFF設定
           $query_list = array();
           array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
           array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
           array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_edit_post['werer_cd']."'");
           array_push($query_list, "t_returned_plan_info_tran.order_req_no = '".$wearer_edit_post["order_req_no"]."'");
           array_push($query_list, "t_returned_plan_info_tran.item_cd = '".$now_wearer_map['item_cd']."'");
           array_push($query_list, "t_returned_plan_info_tran.color_cd = '".$now_wearer_map['color_cd']."'");
           array_push($query_list, "t_returned_plan_info_tran.size_cd = '".$list["size_cd"]."'");
           array_push($query_list, "t_returned_plan_info_tran.individual_ctrl_no = '".$individual["individual_ctrl_no"]."'");
           $query = implode(' AND ', $query_list);

           $arg_str = "";
           $arg_str = "SELECT ";
           $arg_str .= "individual_ctrl_no";
           $arg_str .= " FROM ";
           $arg_str .= "t_returned_plan_info_tran";
           $arg_str .= " WHERE ";
           $arg_str .= $query;

           $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
           $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
           $result_obj = (array)$results;
           $results_cnt = $result_obj["\0*\0_count"];
           if ($results_cnt > 0) {
             $individual["checked"] = "checked";
           } else {
             $individual["checked"] = "";
//             $individual["checked"] = "checked";
           }

           // 対象チェックボックス値
           array_push($list["individual_chk"], $individual);
         }

         // 個体管理番号
         $list["individual_ctrl_no"] = implode("<br>", $individual_ctrl_no);
       }
       // 発注数(単一選択=入力不可、複数選択=入力可)
       $list["order_num"] = $list["std_input_qty"];
       if ($list["choice_type"] === "1") {
         $list["order_num_disable"] = "disabled";
       } else {
         $list["order_num_disable"] = "";
       }
       for ($i=0; $i<count($chg_wearer_list); $i++) {
         if (
          $now_wearer_map['item_cd'] == $chg_wearer_list[$i]['item_cd']
          && $now_wearer_map['color_cd'] == $chg_wearer_list[$i]['color_cd']
         )
         {
           if ($chg_wearer_list[$i]['std_input_qty'] > $now_wearer_map['std_input_qty']) {
             $list["order_num"] = $chg_wearer_list[$i]['std_input_qty'] - $now_wearer_map['std_input_qty'];
           }
         }
       }
       // 返却数(単一選択=入力不可、複数選択=入力可)
       $list["return_num"] = "";
       if ($list["choice_type"] == "1") {
         $list["return_num_disable"] = "disabled";
       } else {
         $list["return_num_disable"] = "";
       }
       for ($i=0; $i<count($chg_wearer_list); $i++) {
         if (
          $now_wearer_map['item_cd'] == $chg_wearer_list[$i]['item_cd']
          && $now_wearer_map['color_cd'] == $chg_wearer_list[$i]['color_cd']
         )
         {
           if ($chg_wearer_list[$i]['std_input_qty'] < $now_wearer_map['std_input_qty']) {
             $list["return_num"] = $now_wearer_map['std_input_qty'] - $chg_wearer_list[$i]['std_input_qty'];
           }
         }
       }

       //--その他の必要hiddenパラメータ--//
       // 部門コード
       $list["rntl_sect_cd"] = $now_wearer_map['rntl_sect_cd'];
       // 職種コード
       $list["job_type_cd"] = $now_wearer_map['job_type_cd'];
       // 商品コード
       $list["item_cd"] = $now_wearer_map['item_cd'];
       // 色コード
       $list["color_cd"] = $now_wearer_map['color_cd'];
       // 職種アイテムコード
       $list["job_type_item_cd"] = $now_wearer_map['job_type_item_cd'];

       array_push($now_list, $list);
     }
   }

   // 現在貸与中アイテム一覧内容の表示フラグ
   if (!empty($now_list)) {
     $json_list["now_list_disp_flg"] = true;
   } else {
     $json_list["now_list_disp_flg"] = false;
   }

   $json_list["now_list_cnt"] = count($now_list);
   $json_list["now_list"] = $now_list;
   //ChromePhp::LOG('現在貸与中アイテム一覧リスト');
   //ChromePhp::LOG(count($now_list));
   //ChromePhp::LOG($json_list["now_list"]);

   //--発注総枚数、返却総枚数--//
   $sum_num = array();
   $list = array();

   // 発注総枚数
   $list["sum_order_num"] = '';
   if (!empty($now_list)) {
     $list["sum_order_num"] = 0;
     foreach ($now_list as $now_map) {
       if (!empty($now_map["order_num"])) {
         $list["sum_order_num"] += $now_map["order_num"];
       }
     }
   }
   // 返却総枚数
   $list["sum_return_num"] = '';
   if (!empty($now_list)) {
     $list["sum_return_num"] = 0;
     foreach ($now_list as $now_map) {
       if (!empty($now_map["return_num"])) {
         $list["sum_return_num"] += $now_map["return_num"];
       }
     }
   }

   array_push($sum_num, $list);
   $json_list["sum_num"] = $sum_num;
   //ChromePhp::LOG('発注総枚数/返却総枚数');
   //ChromePhp::LOG($json_list["sum_num"]);

   // 貸与中アイテム一覧の「対象」、「個体管理番号」列の表示/非表示の制御フラグ
   $json_list["individual_flg"] = $auth['individual_flg'];

   echo json_encode($json_list);
   //ChromePhp::LOG('JSON_LIST');
   //ChromePhp::LOG($json_list);
});

/**
 * 発注入力（着用者編集）
 * 発注取消処理
 */
$app->post('/wearer_change_delete', function ()use($app){
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
    //--着用者商品マスタトラン削除--//
    //ChromePhp::LOG("着用者商品マスタトラン削除");
    $query_list = array();
    array_push($query_list, "m_wearer_item_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_order_tran.order_req_no = '".$cond['order_req_no']."'");
    // 発注区分「終了」
    array_push($query_list, "t_order_tran.order_sts_kbn = '2'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "DELETE FROM ";
    $arg_str .= "m_wearer_item_tran";
    $arg_str .= " USING ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= "m_wearer_item_tran.werer_cd = t_order_tran.werer_cd";
    $arg_str .= " AND m_wearer_item_tran.rntl_cont_no = t_order_tran.rntl_cont_no";
    $arg_str .= " AND m_wearer_item_tran.rntl_sect_cd = t_order_tran.rntl_sect_cd";
    $arg_str .= " AND m_wearer_item_tran.job_type_cd = t_order_tran.job_type_cd";
    $arg_str .= " AND m_wearer_item_tran.job_type_item_cd = t_order_tran.job_type_item_cd";
    $arg_str .= " AND m_wearer_item_tran.item_cd = t_order_tran.item_cd";
    $arg_str .= " AND m_wearer_item_tran.color_cd = t_order_tran.color_cd";
    $arg_str .= " AND m_wearer_item_tran.size_cd = t_order_tran.size_cd";
    $arg_str .= " AND m_wearer_item_tran.size_two_cd = t_order_tran.size_two_cd";
    $arg_str .= " AND ";
    $arg_str .= $query;
    //ChromePhp::LOG($arg_str);
    $m_wearer_item_tran = new MWearerItemTran();
    $results = new Resultset(NULL, $m_wearer_item_tran, $m_wearer_item_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    //--着用者基本マスタトラン削除--//
    // 発注情報トランを参照
    //ChromePhp::LOG("発注情報トラン参照");
    $query_list = array();
    array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_order_tran.order_req_no <> '".$cond['order_req_no']."'");
    array_push($query_list, "t_order_tran.werer_cd = '".$cond['werer_cd']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    //ChromePhp::LOG($arg_str);
    $t_order_tran = new TOrderTran();
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    // 上記発注情報トラン件数が0の場合に着用者基本マスタトランのデータを削除する
    if (empty($results_cnt)) {
      //ChromePhp::LOG("着用者基本マスタトラン削除");
      $query_list = array();
      array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "m_wearer_std_tran.werer_cd = '".$cond['werer_cd']."'");
      array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$cond['rntl_cont_no']."'");
      array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$cond['rntl_sect_cd']."'");
      array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$cond['job_type_cd']."'");
      // 発注区分「着用者編集」ではない
      array_push($query_list, "m_wearer_std_tran.order_sts_kbn <> '6'");
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
    }

    //--発注情報トラン削除--//
    //ChromePhp::LOG("発注情報トラン削除");
    $query_list = array();
    array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_order_tran.order_req_no = '".$cond['order_req_no']."'");
    // 発注区分「貸与」
    array_push($query_list, "t_order_tran.order_sts_kbn = '1'");
    // 理由区分「着用者編集」系ステータス
    $reason_kbn = array();
    array_push($reason_kbn, '9');
    array_push($reason_kbn, '10');
    array_push($reason_kbn, '11');
    array_push($reason_kbn, '24');
    if(!empty($reason_kbn)) {
      $reason_kbn_str = implode("','",$reason_kbn);
      $reason_kbn_query = "t_order_tran.order_reason_kbn IN ('".$reason_kbn_str."')";
      array_push($query_list, $reason_kbn_query);
    }
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "DELETE FROM ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    //ChromePhp::LOG($arg_str);

    $t_order_tran = new TOrderTran();
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    //--返却予定情報トラン削除--//
    //ChromePhp::LOG("返却予定情報トラン削除");
    $query_list = array();
    array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_returned_plan_info_tran.order_req_no = '".$cond['order_req_no']."'");
    // 発注区分「貸与」
    array_push($query_list, "t_returned_plan_info_tran.order_sts_kbn = '1'");
    // 理由区分「着用者編集」系ステータス
    $reason_kbn = array();
    array_push($reason_kbn, '9');
    array_push($reason_kbn, '10');
    array_push($reason_kbn, '11');
    array_push($reason_kbn, '24');
    if(!empty($reason_kbn)) {
      $reason_kbn_str = implode("','",$reason_kbn);
      $reason_kbn_query = "t_returned_plan_info_tran.order_reason_kbn IN ('".$reason_kbn_str."')";
      array_push($query_list, $reason_kbn_query);
    }
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "DELETE FROM ";
    $arg_str .= "t_returned_plan_info_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    //ChromePhp::LOG($arg_str);
    $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
    $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);
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
$app->post('/wearer_change_complete', function ()use($app){
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
   $now_item_input = $params["now_item"];
   $add_item_input = $params["add_item"];

   $json_list = array();
   // DB更新エラーコード 0:正常 その他:要因エラー
   $json_list["error_code"] = "0";
   $json_list["error_msg"] = array();

   if ($mode == "check") {
/*
     ChromePhp::LOG("着用者入力");
     ChromePhp::LOG($wearer_data_input);
     ChromePhp::LOG("現在貸与中アイテム");
     ChromePhp::LOG($now_item_input);
     ChromePhp::LOG("追加されるアイテム");
     ChromePhp::LOG($add_item_input);
*/
     //--入力内容確認--//
     // 変更なしエラーチェック
     $query_list = array();
     array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
     array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
     $job_type_cd = explode(':', $wearer_data_input['job_type']);
     $job_type_cd = $job_type_cd[0];
     array_push($query_list, "job_type_cd = '".$job_type_cd."'");
     $query = implode(' AND ', $query_list);

     $arg_str = "";
     $arg_str = "SELECT ";
     $arg_str .= "*";
     $arg_str .= " FROM ";
     $arg_str .= "m_wearer_std";
     $arg_str .= " WHERE ";
     $arg_str .= $query;
     //ChromePhp::LOG($arg_str);
     $m_wearer_std = new MWearerStd();
     $results = new Resultset(NULL, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
     $result_obj = (array)$results;
     $results_cnt = $result_obj["\0*\0_count"];
     //ChromePhp::LOG($results_cnt);
     if (!empty($results_cnt)) {
       $json_list["error_code"] = "1";
       $error_msg = "拠点、又は貸与パターンを変更してください。";
       array_push($json_list["error_msg"], $error_msg);
     }
     // 現在貸与中のアイテム
     foreach ($now_item_input as $now_item_input_map) {
       // 発注枚数フォーマットチェック
       if (empty($now_item_input_map["now_order_num_disable"])) {
         if (!ctype_digit(strval($now_item_input_map["now_order_num"]))) {
           if (empty($now_order_num_format_err)) {
             $now_order_num_format_err = "err";
             $json_list["error_code"] = "1";
             $error_msg = "現在貸与中のアイテムの発注枚数には半角数字を入力してください。";
             array_push($json_list["error_msg"], $error_msg);
           }
         }
       }
       // 返却枚数フォーマットチェック
       if (empty($now_item_input_map["now_return_num_disable"])) {
         if (!ctype_digit(strval($now_item_input_map["now_return_num"]))) {
           if (empty($now_return_num_format_err)) {
             $now_return_num_format_err = "err";
             $json_list["error_code"] = "1";
             $error_msg = "現在貸与中のアイテムの返却枚数には半角数字を入力してください。";
             array_push($json_list["error_msg"], $error_msg);
           }
         }
       }
       // 返却枚数チェック
       if (!empty($now_item_input_map["individual_data"])) {
         $target_cnt = 0;
         for ($i=0; $i<count($now_item_input_map["individual_data"]); $i++) {
           if ($now_item_input_map["individual_data"][$i]["now_target_flg"] == "1") {
             $target_cnt = $target_cnt + 1;
           }
         }
         if ($now_item_input_map["now_return_num"] !== $target_cnt) {
           if (empty($now_return_num_err)) {
             $now_return_num_err = "err";
             $json_list["error_code"] = "1";
             $error_msg = "現在貸与中のアイテムで、返却枚数が足りない商品があります。";
             array_push($json_list["error_msg"], $error_msg);
           }
         }
       }
     }
     // 新たに追加されるアイテム
     foreach ($add_item_input as $add_item_input_map) {
       // 発注枚数フォーマットチェック
       if (empty($add_item_input_map["add_order_num_disable"])) {
         if (!ctype_digit(strval($add_item_input_map["add_order_num"]))) {
           if (empty($add_order_num_format_err)) {
             $add_return_num_format_err = "err";
             $json_list["error_code"] = "1";
             $error_msg = "新たに追加されるアイテムで、発注枚数には半角数字を入力してください。";
             array_push($json_list["error_msg"], $error_msg);
           }
         }
       }
     }

     echo json_encode($json_list);
   } else if ($mode == "update") {
     //ChromePhp::LOG("着用者入力");
     //ChromePhp::LOG($wearer_data_input);
     //ChromePhp::LOG("現在貸与中アイテム");
     //ChromePhp::LOG($now_item_input);
     //ChromePhp::LOG("追加されるアイテム");
     //ChromePhp::LOG($add_item_input);

     //--発注NGパターンチェック--//
     //※発注情報トラン参照
     $query_list = array();
     array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
     array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
     array_push($query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
     array_push($query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
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
       }
       //※汎用コードマスタ参照
       $query_list = array();
       array_push($query_list, "cls_cd = '001'");
       array_push($query_list, "gen_cd = '".$order_sts_kbn."'");
       $query = implode(' AND ', $query_list);
       $gencode = MGencode::query()
           ->where($query)
           ->columns('*')
           ->execute();
       foreach ($gencode as $gencode_map) {
         $order_sts_kbn_name = $gencode_map->gen_name;
       }

       // 着用者編集の場合、何かしらの発注区分の情報がある際は発注NGとする
       $json_list["error_code"] = "1";
       $error_msg = $order_sts_kbn_name."発注が登録されていた為、操作を完了できませんでした。";
       $error_msg .= $order_sts_kbn_name."発注を削除してから再度登録して下さい。";
       array_push($json_list["error_msg"], $error_msg);

       echo json_encode($json_list);
       return;
     }

     //--着用者基本マスタトラン更新--//
     // 着用者基本マスタ参照
     $query_list = array();
     array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
     array_push($query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
     array_push($query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
     $query = implode(' AND ', $query_list);

     $arg_str = "";
     $arg_str = "SELECT ";
     $arg_str .= "order_sts_kbn";
     $arg_str .= " FROM ";
     $arg_str .= "m_wearer_std_tran";
     $arg_str .= " WHERE ";
     $arg_str .= $query;
     //ChromePhp::LOG($arg_str);
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
     $result_obj = (array)$results;
     $results_cnt = $result_obj["\0*\0_count"];
     //ChromePhp::LOG($results_cnt);
     $order_sts_kbn = "";
     if (!empty($results_cnt)) {
       $paginator_model = new PaginatorModel(
           array(
               "data"  => $results,
               "limit" => $results_cnt,
               "page" => 1
           )
       );
       $paginator = $paginator_model->getPaginate();
       $results = $paginator->items;
       //ChromePhp::LOG($results);
       foreach ($results as $result) {
         $order_sts_kbn = $result->order_sts_kbn;
       }
     }

     // トランザクション開始
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
     try {
       // 着用者基本マスタトラン更新処理
       //※検索条件
       $src_query_list = array();
       array_push($src_query_list, "corporate_id = '".$auth['corporate_id']."'");
       array_push($src_query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
       array_push($src_query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
       array_push($src_query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
       $src_query = implode(' AND ', $src_query_list);

       //※更新内容
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
       // 発令日
       if (!empty($wearer_data_input['appointment_ymd'])) {
         $appointment_ymd = date('Ymd', strtotime($wearer_data_input['appointment_ymd']));
         array_push($up_query_list, "appointment_ymd = '".$appointment_ymd."'");
       } else {
         array_push($up_query_list, "appointment_ymd = NULL");
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
       if ($order_sts_kbn !== "6") {
         array_push($up_query_list, "order_sts_kbn = '5'");
       }
       // 更新区分
       array_push($up_query_list, "upd_kbn = '5'");
       // Web更新日時
       array_push($up_query_list, "web_upd_date = '".date("Y/m/d H:i:s", time())."'");
       // 送信区分(未送信)
       array_push($up_query_list, "snd_kbn = '0'");
       // 削除区分
       array_push($up_query_list, "del_kbn = '0'");
       // 更新日時
       array_push($up_query_list, "upd_date = '".date("Y/m/d H:i:s", time())."'");
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

       //--発注情報トラン登録--//
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

       $cnt = 1;
       // 現在貸与中のアイテム内容登録
       if (!empty($now_item_input)) {
         foreach ($now_item_input as $now_item_map) {
           $calum_list = array();
           $values_list = array();

           // 発注依頼行No.生成
           $order_req_line_no = $cnt++;

           // 発注情報_統合ハッシュキー(企業ID、発注依頼No、発注依頼行No)
           $t_order_comb_hkey = md5(
             $auth['corporate_id']
             .$shin_order_req_no
             .$order_req_line_no
           );
           array_push($calum_list, "t_order_comb_hkey");
           array_push($values_list, "'".$t_order_comb_hkey."'");
           // 企業ID
           array_push($calum_list, "corporate_id");
           array_push($values_list, "'".$auth['corporate_id']."'");
           // 発注依頼No.
           array_push($calum_list, "order_req_no");
           array_push($values_list, "'".$shin_order_req_no."'");
           // 発注依頼行No.
           array_push($calum_list, "order_req_line_no");
           array_push($values_list, "'".$order_req_line_no."'");
           // 発注依頼日
           array_push($calum_list, "order_req_ymd");
           array_push($values_list, "'".date('Ymd', time())."'");
           // 発注状況区分(異動)
           array_push($calum_list, "order_sts_kbn");
           array_push($values_list, "'5'");
           // レンタル契約No
           array_push($calum_list, "rntl_cont_no");
           array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
           // レンタル部門コード
           array_push($calum_list, "rntl_sect_cd");
           array_push($values_list, "'".$wearer_data_input['section']."'");
           // 貸与パターン
           $job_type_cd = explode(':', $wearer_data_input['job_type']);
           $job_type_cd = $job_type_cd[0];
           array_push($calum_list, "job_type_cd");
           array_push($values_list, "'".$job_type_cd."'");
           // 職種アイテムコード
           array_push($calum_list, "job_type_item_cd");
           array_push($values_list, "'".$now_item_map['now_job_type_item_cd']."'");
           // 着用者コード
           array_push($calum_list, "werer_cd");
           array_push($values_list, "'".$wearer_edit_post['werer_cd']."'");
           // 商品コード
           array_push($calum_list, "item_cd");
           array_push($values_list, "'".$now_item_map['now_item_cd']."'");
           // 色コード
           array_push($calum_list, "color_cd");
           array_push($values_list, "'".$now_item_map['now_color_cd']."'");
           // サイズコード
           array_push($calum_list, "size_cd");
           array_push($values_list, "'".$now_item_map['now_size_cd']."'");
           // サイズコード2
           array_push($calum_list, "size_two_cd");
           array_push($values_list, "' '");
/*
           // 倉庫コード
           array_push($calum_list, "whse_cd");
           array_push($values_list, "NULL");
           // 在庫USRコード
           array_push($calum_list, "stk_usr_cd");
           array_push($values_list, "NULL");
           // 在庫USR支店コード
           array_push($calum_list, "stk_usr_brnch_cd");
           array_push($values_list, "NULL");
*/
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
           // 発注枚数
           array_push($calum_list, "order_qty");
           array_push($values_list, "'".$now_item_map['now_order_num']."'");
           // 備考欄
           if (!empty($wearer_data_input['comment'])) {
             array_push($calum_list, "memo");
             array_push($values_list, "'".$wearer_data_input['comment']."'");
           }
           // 着用者名
           if (!empty($wearer_data_input['member_name'])) {
             array_push($calum_list, "werer_name");
             array_push($values_list, "'".$wearer_data_input['member_name']."'");
           }
           // 客先社員コード
           if (!empty($wearer_data_input['member_no'])) {
             array_push($calum_list, "cster_emply_cd");
             array_push($values_list, "'".$wearer_data_input['member_no']."'");
           }
           // 着用者状況区分(稼働)
           array_push($calum_list, "werer_sts_kbn");
           array_push($values_list, "'1'");
           // 発令日
           if (!empty($wearer_data_input['appointment_ymd'])) {
             $appointment_ymd = date('Ymd', strtotime($wearer_data_input['appointment_ymd']));
             array_push($calum_list, "appointment_ymd");
             array_push($values_list, "'".$appointment_ymd."'");
           } else {
             array_push($calum_list, "appointment_ymd");
             array_push($values_list, "NULL");
           }
           // 異動日
           if (!empty($wearer_data_input['resfl_ymd'])) {
             $resfl_ymd = date('Ymd', strtotime($wearer_data_input['resfl_ymd']));
             array_push($calum_list, "resfl_ymd");
             array_push($values_list, "'".$resfl_ymd."'");
           } else {
             array_push($calum_list, "resfl_ymd");
             array_push($values_list, "NULL");
           }
           // 送信区分(未送信)
           array_push($calum_list, "snd_kbn");
           array_push($values_list, "'0'");
           // 削除区分
           array_push($calum_list, "del_kbn");
           array_push($values_list, "'0'");
           // 登録日時
           array_push($calum_list, "rgst_date");
           array_push($values_list, "'".date("Y/m/d H:i:s", time())."'");
           // 登録ユーザーID
           array_push($calum_list, "rgst_user_id");
           array_push($values_list, "'".$auth['accnt_no']."'");
           // 更新日時
           array_push($calum_list, "upd_date");
           array_push($values_list, "'".date("Y/m/d H:i:s", time())."'");
           // 更新ユーザーID
           array_push($calum_list, "upd_user_id");
           array_push($values_list, "'".$auth['accnt_no']."'");
           // 更新PGID
           array_push($calum_list, "upd_pg_id");
           array_push($values_list, "'".$auth['accnt_no']."'");
           // 発注ステータス(未出荷)
           array_push($calum_list, "order_status");
           array_push($values_list, "'0'");
           // 理由区分
           array_push($calum_list, "order_reason_kbn");
           array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
           // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
           $m_item_comb_hkey = md5(
             $auth['corporate_id']
             .$now_item_map['now_item_cd']
             .$now_item_map['now_color_cd']
             .$now_item_map['now_size_cd']
           );
           array_push($calum_list, "m_item_comb_hkey");
           array_push($values_list, "'".$m_item_comb_hkey."'");
           // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
           $m_job_type_comb_hkey = md5(
             $auth['corporate_id']
             .$wearer_data_input['agreement_no']
             .$wearer_data_input['job_type']
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
           // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
           $m_wearer_std_comb_hkey = md5(
             $auth['corporate_id']
             .$wearer_edit_post["werer_cd"]
             .$wearer_data_input['agreement_no']
             .$wearer_data_input['section']
             .$wearer_data_input['job_type']
           );
           array_push($calum_list, "m_wearer_std_comb_hkey");
           array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
           // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
           $m_wearer_item_comb_hkey = md5(
             $auth['corporate_id']
             .$wearer_edit_post["werer_cd"]
             .$wearer_data_input['agreement_no']
             .$wearer_data_input['section']
             .$wearer_data_input['job_type']
             .$now_item_map['now_job_type_item_cd']
             .$now_item_map['now_item_cd']
             .$now_item_map['now_color_cd']
             .$now_item_map['now_size_cd']
           );
           array_push($calum_list, "m_wearer_item_comb_hkey");
           array_push($values_list, "'".$m_wearer_item_comb_hkey."'");
           $calum_query = implode(',', $calum_list);
           $values_query = implode(',', $values_list);

           $arg_str = "";
           $arg_str = "INSERT INTO t_order_tran";
           $arg_str .= "(".$calum_query.")";
           $arg_str .= " VALUES ";
           $arg_str .= "(".$values_query.")";
           //ChromePhp::LOG($arg_str);
           $t_order_tran = new TOrderTran();
           $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
           //ChromePhp::LOG($results);
         }
       }
       // 新たに追加されるアイテム内容登録
       if (!empty($add_item_input)) {
         foreach ($add_item_input as $add_item_map) {
           $calum_list = array();
           $values_list = array();

           // 発注依頼行No.生成
           $order_req_line_no = $cnt++;

           // 発注情報_統合ハッシュキー(企業ID、発注依頼No、発注依頼行No)
           $t_order_comb_hkey = md5(
             $auth['corporate_id']
             .$shin_order_req_no
             .$order_req_line_no
           );
           array_push($calum_list, "t_order_comb_hkey");
           array_push($values_list, "'".$t_order_comb_hkey."'");
           // 企業ID
           array_push($calum_list, "corporate_id");
           array_push($values_list, "'".$auth['corporate_id']."'");
           // 発注依頼No.
           array_push($calum_list, "order_req_no");
           array_push($values_list, "'".$shin_order_req_no."'");
           // 発注依頼行No.
           array_push($calum_list, "order_req_line_no");
           array_push($values_list, "'".$order_req_line_no."'");
           // 発注依頼日
           array_push($calum_list, "order_req_ymd");
           array_push($values_list, "'".date('Ymd', time())."'");
           // 発注状況区分(異動)
           array_push($calum_list, "order_sts_kbn");
           array_push($values_list, "'5'");
           // レンタル契約No
           array_push($calum_list, "rntl_cont_no");
           array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
           // レンタル部門コード
           array_push($calum_list, "rntl_sect_cd");
           array_push($values_list, "'".$wearer_data_input['section']."'");
           // 貸与パターン
           $job_type_cd = explode(':', $wearer_data_input['job_type']);
           $job_type_cd = $job_type_cd[0];
           array_push($calum_list, "job_type_cd");
           array_push($values_list, "'".$job_type_cd."'");
           // 職種アイテムコード
           array_push($calum_list, "job_type_item_cd");
           array_push($values_list, "'".$add_item_map['add_job_type_item_cd']."'");
           // 着用者コード
           array_push($calum_list, "werer_cd");
           array_push($values_list, "'".$wearer_edit_post['werer_cd']."'");
           // 商品コード
           array_push($calum_list, "item_cd");
           array_push($values_list, "'".$add_item_map['add_item_cd']."'");
           // 色コード
           array_push($calum_list, "color_cd");
           array_push($values_list, "'".$add_item_map['add_color_cd']."'");
           // サイズコード
           array_push($calum_list, "size_cd");
           array_push($values_list, "'".$add_item_map['add_size_cd']."'");
           // サイズコード2
           array_push($calum_list, "size_two_cd");
           array_push($values_list, "' '");
/*
           // 倉庫コード
           array_push($calum_list, "whse_cd");
           array_push($values_list, "NULL");
           // 在庫USRコード
           array_push($calum_list, "stk_usr_cd");
           array_push($values_list, "NULL");
           // 在庫USR支店コード
           array_push($calum_list, "stk_usr_brnch_cd");
           array_push($values_list, "NULL");
*/
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
           // 発注枚数
           array_push($calum_list, "order_qty");
           array_push($values_list, "'".$add_item_map['add_order_num']."'");
           // 備考欄
           if (!empty($wearer_data_input['comment'])) {
             array_push($calum_list, "memo");
             array_push($values_list, "'".$wearer_data_input['comment']."'");
           }
           // 着用者名
           if (!empty($wearer_data_input['member_name'])) {
             array_push($calum_list, "werer_name");
             array_push($values_list, "'".$wearer_data_input['member_name']."'");
           }
           // 客先社員コード
           if (!empty($wearer_data_input['member_no'])) {
             array_push($calum_list, "cster_emply_cd");
             array_push($values_list, "'".$wearer_data_input['member_no']."'");
           }
           // 着用者状況区分(稼働)
           array_push($calum_list, "werer_sts_kbn");
           array_push($values_list, "'1'");
           // 発令日
           if (!empty($wearer_data_input['appointment_ymd'])) {
             $appointment_ymd = date('Ymd', strtotime($wearer_data_input['appointment_ymd']));
             array_push($calum_list, "appointment_ymd");
             array_push($values_list, "'".$appointment_ymd."'");
           } else {
             array_push($calum_list, "appointment_ymd");
             array_push($values_list, "NULL");
           }
           // 異動日
           if (!empty($wearer_data_input['resfl_ymd'])) {
             $resfl_ymd = date('Ymd', strtotime($wearer_data_input['resfl_ymd']));
             array_push($calum_list, "resfl_ymd");
             array_push($values_list, "'".$resfl_ymd."'");
           } else {
             array_push($calum_list, "resfl_ymd");
             array_push($values_list, "NULL");
           }
           // 送信区分(未送信)
           array_push($calum_list, "snd_kbn");
           array_push($values_list, "'0'");
           // 削除区分
           array_push($calum_list, "del_kbn");
           array_push($values_list, "'0'");
           // 登録日時
           array_push($calum_list, "rgst_date");
           array_push($values_list, "'".date("Y/m/d H:i:s", time())."'");
           // 登録ユーザーID
           array_push($calum_list, "rgst_user_id");
           array_push($values_list, "'".$auth['accnt_no']."'");
           // 更新日時
           array_push($calum_list, "upd_date");
           array_push($values_list, "'".date("Y/m/d H:i:s", time())."'");
           // 更新ユーザーID
           array_push($calum_list, "upd_user_id");
           array_push($values_list, "'".$auth['accnt_no']."'");
           // 更新PGID
           array_push($calum_list, "upd_pg_id");
           array_push($values_list, "'".$auth['accnt_no']."'");
           // 発注ステータス(未出荷)
           array_push($calum_list, "order_status");
           array_push($values_list, "'0'");
           // 理由区分
           array_push($calum_list, "order_reason_kbn");
           array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
           // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
           $m_item_comb_hkey = md5(
             $auth['corporate_id']
             .$add_item_map['add_item_cd']
             .$add_item_map['add_color_cd']
             .$add_item_map['add_size_cd']
           );
           array_push($calum_list, "m_item_comb_hkey");
           array_push($values_list, "'".$m_item_comb_hkey."'");
           // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
           $m_job_type_comb_hkey = md5(
             $auth['corporate_id']
             .$wearer_data_input['agreement_no']
             .$wearer_data_input['job_type']
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
           // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
           $m_wearer_std_comb_hkey = md5(
             $auth['corporate_id']
             .$wearer_edit_post["werer_cd"]
             .$wearer_data_input['agreement_no']
             .$wearer_data_input['section']
             .$wearer_data_input['job_type']
           );
           array_push($calum_list, "m_wearer_std_comb_hkey");
           array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
           // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
           $m_wearer_item_comb_hkey = md5(
             $auth['corporate_id']
             .$wearer_edit_post["werer_cd"]
             .$wearer_data_input['agreement_no']
             .$wearer_data_input['section']
             .$wearer_data_input['job_type']
             .$add_item_map['add_job_type_item_cd']
             .$add_item_map['add_item_cd']
             .$add_item_map['add_color_cd']
             .$add_item_map['add_size_cd']
           );
           array_push($calum_list, "m_wearer_item_comb_hkey");
           array_push($values_list, "'".$m_wearer_item_comb_hkey."'");
           $calum_query = implode(',', $calum_list);
           $values_query = implode(',', $values_list);

           $arg_str = "";
           $arg_str = "INSERT INTO t_order_tran";
           $arg_str .= "(".$calum_query.")";
           $arg_str .= " VALUES ";
           $arg_str .= "(".$values_query.")";
           //ChromePhp::LOG($arg_str);
           $t_order_tran = new TOrderTran();
           $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
           //ChromePhp::LOG($results);
        }
      }

      //--返却予定情報トラン登録--//
      // 発注依頼No.生成
      //※シーケンス取得
      $arg_str = "";
      $arg_str = "SELECT NEXTVAL('t_returned_plan_info_index_seq')";
      $t_returned_plan_info = new TReturnedPlanInfo();
      $results = new Resultset(NULL, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
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
        $arg_str = "SELECT SETVAL('t_returned_plan_info_index_seq',".$order_no_seq.")";
        $t_returned_plan_info = new TReturnedPlanInfo();
        $results = new Resultset(NULL, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
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

      $cnt = 1;
      // 現在貸与中のアイテム内容登録
      if (!empty($now_item_input)) {
        foreach ($now_item_input as $now_item_map) {
          // 個体管理番号が存在する(返却数するものがある)場合
          if ($now_item_map["individual_disp"] === true && !empty($now_item_map["now_return_num"])) {
            if (!empty($now_item_map["individual_data"])) {
              // 個体管理番号、対象単位での登録処理
              foreach ($now_item_map["individual_data"] as $individual_data_map) {
                // 個体管理番号単位で対象にチェックがONのデータのみ登録
                if ($individual_data_map["now_target_flg"] == "1") {
                  $calum_list = array();
                  $values_list = array();

                  // 発注依頼行No.生成
                  $order_req_line_no = $cnt++;

                  // 企業ID
                  array_push($calum_list, "corporate_id");
                  array_push($values_list, "'".$auth['corporate_id']."'");
                  // 発注依頼No.
                  array_push($calum_list, "order_req_no");
                  array_push($values_list, "'".$shin_order_req_no."'");
                  // 発注依頼行No.
                  array_push($calum_list, "order_req_line_no");
                  array_push($values_list, "'".$order_req_line_no."'");
                  // レンタル契約No
                  array_push($calum_list, "rntl_cont_no");
                  array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
                  // 商品コード
                  array_push($calum_list, "item_cd");
                  array_push($values_list, "'".$now_item_map['now_item_cd']."'");
                  // 色コード
                  array_push($calum_list, "color_cd");
                  array_push($values_list, "'".$now_item_map['now_color_cd']."'");
                  // サイズコード
                  array_push($calum_list, "size_cd");
                  array_push($values_list, "'".$now_item_map['now_size_cd']."'");

                  // 個体管理番号
                  array_push($calum_list, "individual_ctrl_no");
                  array_push($values_list, "'".$individual_data_map['individual_ctrl_no']."'");

                  // 着用者コード
                  array_push($calum_list, "werer_cd");
                  array_push($values_list, "'".$wearer_edit_post['werer_cd']."'");
                  // 客先社員コード
                  array_push($calum_list, "cster_emply_cd");
                  array_push($values_list, "'".$wearer_data_input['cster_emply_cd']."'");
                  // レンタル部門コード
                  array_push($calum_list, "rntl_sect_cd");
                  array_push($values_list, "'".$wearer_data_input['section']."'");
                  // 貸与パターン
                  $job_type_cd = explode(':', $wearer_data_input['job_type']);
                  $job_type_cd = $job_type_cd[0];
                  array_push($calum_list, "job_type_cd");
                  array_push($values_list, "'".$job_type_cd."'");
                  // 発注依頼日
                  array_push($calum_list, "order_date");
                  array_push($values_list, "'".date('Y/m/d H:i:s', time())."'");
                  // 返却日
                  array_push($calum_list, "return_date");
                  array_push($values_list, "'".date('Y/m/d H:i:s', time())."'");
                  // 返却ステータス(未返却)
                  array_push($calum_list, "return_status");
                  array_push($values_list, "'1'");
                  // 発注状況区分(異動)
                  array_push($calum_list, "order_sts_kbn");
                  array_push($values_list, "'5'");
                  // 返却予定数
                  array_push($calum_list, "return_plan_qty");
                  array_push($values_list, "'".$now_item_map['now_return_num']."'");
                  // 返却数
                  array_push($calum_list, "return_plan_qty");
                  array_push($values_list, "'0'");
                  // 送信区分(未送信)
                  array_push($calum_list, "snd_kbn");
                  array_push($values_list, "'0'");
                  // 理由区分
                  array_push($calum_list, "order_reason_kbn");
                  array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
                  // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
                  $m_section_comb_hkey = md5(
                    $auth['corporate_id']
                    .$wearer_data_input['agreement_no']
                    .$wearer_data_input['section']
                  );
                  array_push($calum_list, "m_section_comb_hkey");
                  array_push($values_list, "'".$m_section_comb_hkey."'");
                  // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
                  $m_item_comb_hkey = md5(
                    $auth['corporate_id']
                    .$now_item_map['now_item_cd']
                    .$now_item_map['now_color_cd']
                    .$now_item_map['now_size_cd']
                  );
                  array_push($calum_list, "m_item_comb_hkey");
                  array_push($values_list, "'".$m_item_comb_hkey."'");
                  $calum_query = implode(',', $calum_list);
                  $values_query = implode(',', $values_list);

                  $arg_str = "";
                  $arg_str = "INSERT INTO t_returned_plan_info";
                  $arg_str .= "(".$calum_query.")";
                  $arg_str .= " VALUES ";
                  $arg_str .= "(".$values_query.")";
                  //ChromePhp::LOG($arg_str);
                  $t_returned_plan_info = new TReturnedPlanInfo();
                  $results = new Resultset(NULL, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
                  //ChromePhp::LOG($results);
                }
              }
            } else {
              // 個体管理番号非表示につき、商品単位での登録処理
              $calum_list = array();
              $values_list = array();

              // 発注依頼行No.生成
              $order_req_line_no = $cnt++;

              // 企業ID
              array_push($calum_list, "corporate_id");
              array_push($values_list, "'".$auth['corporate_id']."'");
              // 発注依頼No.
              array_push($calum_list, "order_req_no");
              array_push($values_list, "'".$shin_order_req_no."'");
              // 発注依頼行No.
              array_push($calum_list, "order_req_line_no");
              array_push($values_list, "'".$order_req_line_no."'");
              // レンタル契約No
              array_push($calum_list, "rntl_cont_no");
              array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
              // 商品コード
              array_push($calum_list, "item_cd");
              array_push($values_list, "'".$now_item_map['now_item_cd']."'");
              // 色コード
              array_push($calum_list, "color_cd");
              array_push($values_list, "'".$now_item_map['now_color_cd']."'");
              // サイズコード
              array_push($calum_list, "size_cd");
              array_push($values_list, "'".$now_item_map['now_size_cd']."'");
              // 個体管理番号
              array_push($calum_list, "individual_ctrl_no");
              array_push($values_list, "''");
              // 着用者コード
              array_push($calum_list, "werer_cd");
              array_push($values_list, "'".$wearer_edit_post['werer_cd']."'");
              // 客先社員コード
              array_push($calum_list, "cster_emply_cd");
              array_push($values_list, "'".$wearer_data_input['cster_emply_cd']."'");
              // レンタル部門コード
              array_push($calum_list, "rntl_sect_cd");
              array_push($values_list, "'".$wearer_data_input['section']."'");
              // 貸与パターン
              $job_type_cd = explode(':', $wearer_data_input['job_type']);
              $job_type_cd = $job_type_cd[0];
              array_push($calum_list, "job_type_cd");
              array_push($values_list, "'".$job_type_cd."'");
              // 発注依頼日
              array_push($calum_list, "order_date");
              array_push($values_list, "'".date('Y/m/d H:i:s', time())."'");
              // 返却日
              array_push($calum_list, "return_date");
              array_push($values_list, "'".date('Y/m/d H:i:s', time())."'");
              // 返却ステータス(未返却)
              array_push($calum_list, "return_status");
              array_push($values_list, "'1'");
              // 発注状況区分(異動)
              array_push($calum_list, "order_sts_kbn");
              array_push($values_list, "'5'");
              // 返却予定数
              array_push($calum_list, "return_plan_qty");
              array_push($values_list, "'".$now_item_map['now_return_num']."'");
              // 返却数
              array_push($calum_list, "return_plan_qty");
              array_push($values_list, "'0'");
              // 送信区分(未送信)
              array_push($calum_list, "snd_kbn");
              array_push($values_list, "'0'");
              // 理由区分
              array_push($calum_list, "order_reason_kbn");
              array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
              // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
              $m_section_comb_hkey = md5(
                $auth['corporate_id']
                .$wearer_data_input['agreement_no']
                .$wearer_data_input['section']
              );
              array_push($calum_list, "m_section_comb_hkey");
              array_push($values_list, "'".$m_section_comb_hkey."'");
              // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
              $m_item_comb_hkey = md5(
                $auth['corporate_id']
                .$now_item_map['now_item_cd']
                .$now_item_map['now_color_cd']
                .$now_item_map['now_size_cd']
              );
              array_push($calum_list, "m_item_comb_hkey");
              array_push($values_list, "'".$m_item_comb_hkey."'");
              $calum_query = implode(',', $calum_list);
              $values_query = implode(',', $values_list);

              $arg_str = "";
              $arg_str = "INSERT INTO t_returned_plan_info";
              $arg_str .= "(".$calum_query.")";
              $arg_str .= " VALUES ";
              $arg_str .= "(".$values_query.")";
              //ChromePhp::LOG($arg_str);
              $t_returned_plan_info = new TReturnedPlanInfo();
              $results = new Resultset(NULL, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
              //ChromePhp::LOG($results);
            }
          }
        }
      }

      // トランザクションコミット
      $m_wearer_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
    } catch (Exception $e) {
      ChromePhp::LOG($e);

      // トランザクションロールバック
      $m_wearer_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));

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
$app->post('/wearer_change_send', function ()use($app){
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
  $now_item_input = $params["now_item"];
  $add_item_input = $params["add_item"];

  $json_list = array();
  // DB更新エラーコード 0:正常 その他:要因エラー
  $json_list["error_code"] = "0";
  $json_list["error_msg"] = array();

  if ($mode == "check") {
/*
    ChromePhp::LOG("着用者入力");
    ChromePhp::LOG($wearer_data_input);
    ChromePhp::LOG("現在貸与中アイテム");
    ChromePhp::LOG($now_item_input);
    ChromePhp::LOG("追加されるアイテム");
    ChromePhp::LOG($add_item_input);
*/
    //--入力内容確認--//
    // 変更なしエラーチェック
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
    $job_type_cd = explode(':', $wearer_data_input['job_type']);
    $job_type_cd = $job_type_cd[0];
    array_push($query_list, "job_type_cd = '".$job_type_cd."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "m_wearer_std";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    //ChromePhp::LOG($arg_str);
    $m_wearer_std = new MWearerStd();
    $results = new Resultset(NULL, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);
    if (!empty($results_cnt)) {
      $json_list["error_code"] = "1";
      $error_msg = "拠点、又は貸与パターンを変更してください。";
      array_push($json_list["error_msg"], $error_msg);
    }

    // 現在貸与中のアイテム
    foreach ($now_item_input as $now_item_input_map) {
      // 発注枚数フォーマットチェック
      if (empty($now_item_input_map["now_order_num_disable"])) {
        if (!ctype_digit(strval($now_item_input_map["now_order_num"]))) {
          if (empty($now_order_num_format_err)) {
            $now_order_num_format_err = "err";
            $json_list["error_code"] = "1";
            $error_msg = "現在貸与中のアイテムの発注枚数には半角数字を入力してください。";
            array_push($json_list["error_msg"], $error_msg);
          }
        }
      }
      // 返却枚数フォーマットチェック
      if (empty($now_item_input_map["now_return_num_disable"])) {
        if (!ctype_digit(strval($now_item_input_map["now_return_num"]))) {
          if (empty($now_return_num_format_err)) {
            $now_return_num_format_err = "err";
            $json_list["error_code"] = "1";
            $error_msg = "現在貸与中のアイテムの返却枚数には半角数字を入力してください。";
            array_push($json_list["error_msg"], $error_msg);
          }
        }
      }
      // 返却枚数チェック
      if (!empty($now_item_input_map["individual_data"])) {
        $target_cnt = 0;
        for ($i=0; $i<count($now_item_input_map["individual_data"]); $i++) {
          if ($now_item_input_map["individual_data"][$i]["now_target_flg"] == "1") {
            $target_cnt = $target_cnt + 1;
          }
        }
        if ($now_item_input_map["now_return_num"] !== $target_cnt) {
          if (empty($now_return_num_err)) {
            $now_return_num_err = "err";
            $json_list["error_code"] = "1";
            $error_msg = "現在貸与中のアイテムで、返却枚数が足りない商品があります。";
            array_push($json_list["error_msg"], $error_msg);
          }
        }
      }
    }

    // 新たに追加されるアイテム
    foreach ($add_item_input as $add_item_input_map) {
      // 発注枚数フォーマットチェック
      if (empty($add_item_input_map["add_order_num_disable"])) {
        if (!ctype_digit(strval($add_item_input_map["add_order_num"]))) {
          if (empty($add_order_num_format_err)) {
            $add_return_num_format_err = "err";
            $json_list["error_code"] = "1";
            $error_msg = "新たに追加されるアイテムで、発注枚数には半角数字を入力してください。";
            array_push($json_list["error_msg"], $error_msg);
          }
        }
      }
    }

    echo json_encode($json_list);
  } else if ($mode == "update") {
    //ChromePhp::LOG("着用者入力");
    //ChromePhp::LOG($wearer_data_input);
    //ChromePhp::LOG("現在貸与中アイテム");
    //ChromePhp::LOG($now_item_input);
    //ChromePhp::LOG("追加されるアイテム");
    //ChromePhp::LOG($add_item_input);

    //--発注NGパターンチェック--//
    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_edit_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
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
      }
      //※汎用コードマスタ参照
      $query_list = array();
      array_push($query_list, "cls_cd = '001'");
      array_push($query_list, "gen_cd = '".$order_sts_kbn."'");
      $query = implode(' AND ', $query_list);
      $gencode = MGencode::query()
          ->where($query)
          ->columns('*')
          ->execute();
      foreach ($gencode as $gencode_map) {
        $order_sts_kbn_name = $gencode_map->gen_name;
      }

      // 着用者編集の場合、何かしらの発注区分の情報がある際は発注NGとする
      $json_list["error_code"] = "1";
      $error_msg = $order_sts_kbn_name."発注が登録されていた為、操作を完了できませんでした。";
      $error_msg .= $order_sts_kbn_name."発注を削除してから再度登録して下さい。";
      array_push($json_list["error_msg"], $error_msg);

      echo json_encode($json_list);
      return;
    }

    //--着用者基本マスタトラン更新--//
    // 着用者基本マスタ参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "order_sts_kbn";
    $arg_str .= " FROM ";
    $arg_str .= "m_wearer_std_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    //ChromePhp::LOG($arg_str);
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);
    $order_sts_kbn = "";
    if (!empty($results_cnt)) {
      $paginator_model = new PaginatorModel(
          array(
              "data"  => $results,
              "limit" => $results_cnt,
              "page" => 1
          )
      );
      $paginator = $paginator_model->getPaginate();
      $results = $paginator->items;
      //ChromePhp::LOG($results);
      foreach ($results as $result) {
        $order_sts_kbn = $result->order_sts_kbn;
      }
    }

    // トランザクション開始
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
    try {
      // 着用者基本マスタトラン更新処理
      //※検索条件
      $src_query_list = array();
      array_push($src_query_list, "corporate_id = '".$auth['corporate_id']."'");
      array_push($src_query_list, "werer_cd = '".$wearer_edit_post['werer_cd']."'");
      array_push($src_query_list, "rntl_sect_cd = '".$wearer_edit_post['rntl_sect_cd']."'");
      array_push($src_query_list, "job_type_cd = '".$wearer_edit_post['job_type_cd']."'");
      $src_query = implode(' AND ', $src_query_list);

      //※更新内容
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
      // 発令日
      if (!empty($wearer_data_input['appointment_ymd'])) {
        $appointment_ymd = date('Ymd', strtotime($wearer_data_input['appointment_ymd']));
        array_push($up_query_list, "appointment_ymd = '".$appointment_ymd."'");
      } else {
        array_push($up_query_list, "appointment_ymd = NULL");
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
      if ($order_sts_kbn !== "6") {
        array_push($up_query_list, "order_sts_kbn = '5'");
      }
      // 更新区分
      array_push($up_query_list, "upd_kbn = '5'");
      // Web更新日時
      array_push($up_query_list, "web_upd_date = '".date("Y/m/d H:i:s", time())."'");
      // 送信区分(送信済み)
      array_push($up_query_list, "snd_kbn = '1'");
      // 削除区分
      array_push($up_query_list, "del_kbn = '0'");
      // 更新日時
      array_push($up_query_list, "upd_date = '".date("Y/m/d H:i:s", time())."'");
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

      //--発注情報トラン登録--//
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

      $cnt = 1;
      // 現在貸与中のアイテム内容登録
      if (!empty($now_item_input)) {
        foreach ($now_item_input as $now_item_map) {
          $calum_list = array();
          $values_list = array();

          // 発注依頼行No.生成
          $order_req_line_no = $cnt++;

          // 発注情報_統合ハッシュキー(企業ID、発注依頼No、発注依頼行No)
          $t_order_comb_hkey = md5(
            $auth['corporate_id']
            .$shin_order_req_no
            .$order_req_line_no
          );
          array_push($calum_list, "t_order_comb_hkey");
          array_push($values_list, "'".$t_order_comb_hkey."'");
          // 企業ID
          array_push($calum_list, "corporate_id");
          array_push($values_list, "'".$auth['corporate_id']."'");
          // 発注依頼No.
          array_push($calum_list, "order_req_no");
          array_push($values_list, "'".$shin_order_req_no."'");
          // 発注依頼行No.
          array_push($calum_list, "order_req_line_no");
          array_push($values_list, "'".$order_req_line_no."'");
          // 発注依頼日
          array_push($calum_list, "order_req_ymd");
          array_push($values_list, "'".date('Ymd', time())."'");
          // 発注状況区分(異動)
          array_push($calum_list, "order_sts_kbn");
          array_push($values_list, "'5'");
          // レンタル契約No
          array_push($calum_list, "rntl_cont_no");
          array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
          // レンタル部門コード
          array_push($calum_list, "rntl_sect_cd");
          array_push($values_list, "'".$wearer_data_input['section']."'");
          // 貸与パターン
          $job_type_cd = explode(':', $wearer_data_input['job_type']);
          $job_type_cd = $job_type_cd[0];
          array_push($calum_list, "job_type_cd");
          array_push($values_list, "'".$job_type_cd."'");
          // 職種アイテムコード
          array_push($calum_list, "job_type_item_cd");
          array_push($values_list, "'".$now_item_map['now_job_type_item_cd']."'");
          // 着用者コード
          array_push($calum_list, "werer_cd");
          array_push($values_list, "'".$wearer_edit_post['werer_cd']."'");
          // 商品コード
          array_push($calum_list, "item_cd");
          array_push($values_list, "'".$now_item_map['now_item_cd']."'");
          // 色コード
          array_push($calum_list, "color_cd");
          array_push($values_list, "'".$now_item_map['now_color_cd']."'");
          // サイズコード
          array_push($calum_list, "size_cd");
          array_push($values_list, "'".$now_item_map['now_size_cd']."'");
          // サイズコード2
          array_push($calum_list, "size_two_cd");
          array_push($values_list, "' '");
/*
          // 倉庫コード
          array_push($calum_list, "whse_cd");
          array_push($values_list, "NULL");
          // 在庫USRコード
          array_push($calum_list, "stk_usr_cd");
          array_push($values_list, "NULL");
          // 在庫USR支店コード
          array_push($calum_list, "stk_usr_brnch_cd");
          array_push($values_list, "NULL");
*/
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
          // 発注枚数
          array_push($calum_list, "order_qty");
          array_push($values_list, "'".$now_item_map['now_order_num']."'");
          // 備考欄
          if (!empty($wearer_data_input['comment'])) {
            array_push($calum_list, "memo");
            array_push($values_list, "'".$wearer_data_input['comment']."'");
          }
          // 着用者名
          if (!empty($wearer_data_input['member_name'])) {
            array_push($calum_list, "werer_name");
            array_push($values_list, "'".$wearer_data_input['member_name']."'");
          }
          // 客先社員コード
          if (!empty($wearer_data_input['member_no'])) {
            array_push($calum_list, "cster_emply_cd");
            array_push($values_list, "'".$wearer_data_input['member_no']."'");
          }
          // 着用者状況区分(稼働)
          array_push($calum_list, "werer_sts_kbn");
          array_push($values_list, "'1'");
          // 発令日
          if (!empty($wearer_data_input['appointment_ymd'])) {
            $appointment_ymd = date('Ymd', strtotime($wearer_data_input['appointment_ymd']));
            array_push($calum_list, "appointment_ymd");
            array_push($values_list, "'".$appointment_ymd."'");
          } else {
            array_push($calum_list, "appointment_ymd");
            array_push($values_list, "NULL");
          }
          // 異動日
          if (!empty($wearer_data_input['resfl_ymd'])) {
            $resfl_ymd = date('Ymd', strtotime($wearer_data_input['resfl_ymd']));
            array_push($calum_list, "resfl_ymd");
            array_push($values_list, "'".$resfl_ymd."'");
          } else {
            array_push($calum_list, "resfl_ymd");
            array_push($values_list, "NULL");
          }
          // 送信区分(送信済み)
          array_push($calum_list, "snd_kbn");
          array_push($values_list, "'1'");
          // 削除区分
          array_push($calum_list, "del_kbn");
          array_push($values_list, "'0'");
          // 登録日時
          array_push($calum_list, "rgst_date");
          array_push($values_list, "'".date("Y/m/d H:i:s", time())."'");
          // 登録ユーザーID
          array_push($calum_list, "rgst_user_id");
          array_push($values_list, "'".$auth['accnt_no']."'");
          // 更新日時
          array_push($calum_list, "upd_date");
          array_push($values_list, "'".date("Y/m/d H:i:s", time())."'");
          // 更新ユーザーID
          array_push($calum_list, "upd_user_id");
          array_push($values_list, "'".$auth['accnt_no']."'");
          // 更新PGID
          array_push($calum_list, "upd_pg_id");
          array_push($values_list, "'".$auth['accnt_no']."'");
          // 発注ステータス(未出荷)
          array_push($calum_list, "order_status");
          array_push($values_list, "'0'");
          // 理由区分
          array_push($calum_list, "order_reason_kbn");
          array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
          // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
          $m_item_comb_hkey = md5(
            $auth['corporate_id']
            .$now_item_map['now_item_cd']
            .$now_item_map['now_color_cd']
            .$now_item_map['now_size_cd']
          );
          array_push($calum_list, "m_item_comb_hkey");
          array_push($values_list, "'".$m_item_comb_hkey."'");
          // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
          $m_job_type_comb_hkey = md5(
            $auth['corporate_id']
            .$wearer_data_input['agreement_no']
            .$wearer_data_input['job_type']
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
          // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
          $m_wearer_std_comb_hkey = md5(
            $auth['corporate_id']
            .$wearer_edit_post["werer_cd"]
            .$wearer_data_input['agreement_no']
            .$wearer_data_input['section']
            .$wearer_data_input['job_type']
          );
          array_push($calum_list, "m_wearer_std_comb_hkey");
          array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
          // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
          $m_wearer_item_comb_hkey = md5(
            $auth['corporate_id']
            .$wearer_edit_post["werer_cd"]
            .$wearer_data_input['agreement_no']
            .$wearer_data_input['section']
            .$wearer_data_input['job_type']
            .$now_item_map['now_job_type_item_cd']
            .$now_item_map['now_item_cd']
            .$now_item_map['now_color_cd']
            .$now_item_map['now_size_cd']
          );
          array_push($calum_list, "m_wearer_item_comb_hkey");
          array_push($values_list, "'".$m_wearer_item_comb_hkey."'");
          $calum_query = implode(',', $calum_list);
          $values_query = implode(',', $values_list);

          $arg_str = "";
          $arg_str = "INSERT INTO t_order_tran";
          $arg_str .= "(".$calum_query.")";
          $arg_str .= " VALUES ";
          $arg_str .= "(".$values_query.")";
          //ChromePhp::LOG($arg_str);
          $t_order_tran = new TOrderTran();
          $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
          //ChromePhp::LOG($results);
        }
      }
      // 新たに追加されるアイテム内容登録
      if (!empty($add_item_input)) {
        foreach ($add_item_input as $add_item_map) {
          $calum_list = array();
          $values_list = array();

          // 発注依頼行No.生成
          $order_req_line_no = $cnt++;

          // 発注情報_統合ハッシュキー(企業ID、発注依頼No、発注依頼行No)
          $t_order_comb_hkey = md5(
            $auth['corporate_id']
            .$shin_order_req_no
            .$order_req_line_no
          );
          array_push($calum_list, "t_order_comb_hkey");
          array_push($values_list, "'".$t_order_comb_hkey."'");
          // 企業ID
          array_push($calum_list, "corporate_id");
          array_push($values_list, "'".$auth['corporate_id']."'");
          // 発注依頼No.
          array_push($calum_list, "order_req_no");
          array_push($values_list, "'".$shin_order_req_no."'");
          // 発注依頼行No.
          array_push($calum_list, "order_req_line_no");
          array_push($values_list, "'".$order_req_line_no."'");
          // 発注依頼日
          array_push($calum_list, "order_req_ymd");
          array_push($values_list, "'".date('Ymd', time())."'");
          // 発注状況区分(異動)
          array_push($calum_list, "order_sts_kbn");
          array_push($values_list, "'5'");
          // レンタル契約No
          array_push($calum_list, "rntl_cont_no");
          array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
          // レンタル部門コード
          array_push($calum_list, "rntl_sect_cd");
          array_push($values_list, "'".$wearer_data_input['section']."'");
          // 貸与パターン
          $job_type_cd = explode(':', $wearer_data_input['job_type']);
          $job_type_cd = $job_type_cd[0];
          array_push($calum_list, "job_type_cd");
          array_push($values_list, "'".$job_type_cd."'");
          // 職種アイテムコード
          array_push($calum_list, "job_type_item_cd");
          array_push($values_list, "'".$add_item_map['add_job_type_item_cd']."'");
          // 着用者コード
          array_push($calum_list, "werer_cd");
          array_push($values_list, "'".$wearer_edit_post['werer_cd']."'");
          // 商品コード
          array_push($calum_list, "item_cd");
          array_push($values_list, "'".$add_item_map['add_item_cd']."'");
          // 色コード
          array_push($calum_list, "color_cd");
          array_push($values_list, "'".$add_item_map['add_color_cd']."'");
          // サイズコード
          array_push($calum_list, "size_cd");
          array_push($values_list, "'".$add_item_map['add_size_cd']."'");
          // サイズコード2
          array_push($calum_list, "size_two_cd");
          array_push($values_list, "' '");
/*
          // 倉庫コード
          array_push($calum_list, "whse_cd");
          array_push($values_list, "NULL");
          // 在庫USRコード
          array_push($calum_list, "stk_usr_cd");
          array_push($values_list, "NULL");
          // 在庫USR支店コード
          array_push($calum_list, "stk_usr_brnch_cd");
          array_push($values_list, "NULL");
*/
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
          // 発注枚数
          array_push($calum_list, "order_qty");
          array_push($values_list, "'".$add_item_map['add_order_num']."'");
          // 備考欄
          if (!empty($wearer_data_input['comment'])) {
            array_push($calum_list, "memo");
            array_push($values_list, "'".$wearer_data_input['comment']."'");
          }
          // 着用者名
          if (!empty($wearer_data_input['member_name'])) {
            array_push($calum_list, "werer_name");
            array_push($values_list, "'".$wearer_data_input['member_name']."'");
          }
          // 客先社員コード
          if (!empty($wearer_data_input['member_no'])) {
            array_push($calum_list, "cster_emply_cd");
            array_push($values_list, "'".$wearer_data_input['member_no']."'");
          }
          // 着用者状況区分(稼働)
          array_push($calum_list, "werer_sts_kbn");
          array_push($values_list, "'1'");
          // 発令日
          if (!empty($wearer_data_input['appointment_ymd'])) {
            $appointment_ymd = date('Ymd', strtotime($wearer_data_input['appointment_ymd']));
            array_push($calum_list, "appointment_ymd");
            array_push($values_list, "'".$appointment_ymd."'");
          } else {
            array_push($calum_list, "appointment_ymd");
            array_push($values_list, "NULL");
          }
          // 異動日
          if (!empty($wearer_data_input['resfl_ymd'])) {
            $resfl_ymd = date('Ymd', strtotime($wearer_data_input['resfl_ymd']));
            array_push($calum_list, "resfl_ymd");
            array_push($values_list, "'".$resfl_ymd."'");
          } else {
            array_push($calum_list, "resfl_ymd");
            array_push($values_list, "NULL");
          }
          // 送信区分(送信済み)
          array_push($calum_list, "snd_kbn");
          array_push($values_list, "'1'");
          // 削除区分
          array_push($calum_list, "del_kbn");
          array_push($values_list, "'0'");
          // 登録日時
          array_push($calum_list, "rgst_date");
          array_push($values_list, "'".date("Y/m/d H:i:s", time())."'");
          // 登録ユーザーID
          array_push($calum_list, "rgst_user_id");
          array_push($values_list, "'".$auth['accnt_no']."'");
          // 更新日時
          array_push($calum_list, "upd_date");
          array_push($values_list, "'".date("Y/m/d H:i:s", time())."'");
          // 更新ユーザーID
          array_push($calum_list, "upd_user_id");
          array_push($values_list, "'".$auth['accnt_no']."'");
          // 更新PGID
          array_push($calum_list, "upd_pg_id");
          array_push($values_list, "'".$auth['accnt_no']."'");
          // 発注ステータス(未出荷)
          array_push($calum_list, "order_status");
          array_push($values_list, "'0'");
          // 理由区分
          array_push($calum_list, "order_reason_kbn");
          array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
          // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
          $m_item_comb_hkey = md5(
            $auth['corporate_id']
            .$add_item_map['add_item_cd']
            .$add_item_map['add_color_cd']
            .$add_item_map['add_size_cd']
          );
          array_push($calum_list, "m_item_comb_hkey");
          array_push($values_list, "'".$m_item_comb_hkey."'");
          // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
          $m_job_type_comb_hkey = md5(
            $auth['corporate_id']
            .$wearer_data_input['agreement_no']
            .$wearer_data_input['job_type']
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
          // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
          $m_wearer_std_comb_hkey = md5(
            $auth['corporate_id']
            .$wearer_edit_post["werer_cd"]
            .$wearer_data_input['agreement_no']
            .$wearer_data_input['section']
            .$wearer_data_input['job_type']
          );
          array_push($calum_list, "m_wearer_std_comb_hkey");
          array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
          // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
          $m_wearer_item_comb_hkey = md5(
            $auth['corporate_id']
            .$wearer_edit_post["werer_cd"]
            .$wearer_data_input['agreement_no']
            .$wearer_data_input['section']
            .$wearer_data_input['job_type']
            .$add_item_map['add_job_type_item_cd']
            .$add_item_map['add_item_cd']
            .$add_item_map['add_color_cd']
            .$add_item_map['add_size_cd']
          );
          array_push($calum_list, "m_wearer_item_comb_hkey");
          array_push($values_list, "'".$m_wearer_item_comb_hkey."'");
          $calum_query = implode(',', $calum_list);
          $values_query = implode(',', $values_list);

          $arg_str = "";
          $arg_str = "INSERT INTO t_order_tran";
          $arg_str .= "(".$calum_query.")";
          $arg_str .= " VALUES ";
          $arg_str .= "(".$values_query.")";
          //ChromePhp::LOG($arg_str);
          $t_order_tran = new TOrderTran();
          $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
          //ChromePhp::LOG($results);
       }
     }

     //--返却予定情報トラン登録--//
     // 発注依頼No.生成
     //※シーケンス取得
     $arg_str = "";
     $arg_str = "SELECT NEXTVAL('t_returned_plan_info_index_seq')";
     $t_returned_plan_info = new TReturnedPlanInfo();
     $results = new Resultset(NULL, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
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
       $arg_str = "SELECT SETVAL('t_returned_plan_info_index_seq',".$order_no_seq.")";
       $t_returned_plan_info = new TReturnedPlanInfo();
       $results = new Resultset(NULL, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
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

     $cnt = 1;
     // 現在貸与中のアイテム内容登録
     if (!empty($now_item_input)) {
       foreach ($now_item_input as $now_item_map) {
         // 個体管理番号が存在する(返却数するものがある)場合
         if ($now_item_map["individual_disp"] === true && !empty($now_item_map["now_return_num"])) {
           if (!empty($now_item_map["individual_data"])) {
             // 個体管理番号、対象単位での登録処理
             foreach ($now_item_map["individual_data"] as $individual_data_map) {
               // 個体管理番号単位で対象にチェックがONのデータのみ登録
               if ($individual_data_map["now_target_flg"] == "1") {
                 $calum_list = array();
                 $values_list = array();

                 // 発注依頼行No.生成
                 $order_req_line_no = $cnt++;

                 // 企業ID
                 array_push($calum_list, "corporate_id");
                 array_push($values_list, "'".$auth['corporate_id']."'");
                 // 発注依頼No.
                 array_push($calum_list, "order_req_no");
                 array_push($values_list, "'".$shin_order_req_no."'");
                 // 発注依頼行No.
                 array_push($calum_list, "order_req_line_no");
                 array_push($values_list, "'".$order_req_line_no."'");
                 // レンタル契約No
                 array_push($calum_list, "rntl_cont_no");
                 array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
                 // 商品コード
                 array_push($calum_list, "item_cd");
                 array_push($values_list, "'".$now_item_map['now_item_cd']."'");
                 // 色コード
                 array_push($calum_list, "color_cd");
                 array_push($values_list, "'".$now_item_map['now_color_cd']."'");
                 // サイズコード
                 array_push($calum_list, "size_cd");
                 array_push($values_list, "'".$now_item_map['now_size_cd']."'");

                 // 個体管理番号
                 array_push($calum_list, "individual_ctrl_no");
                 array_push($values_list, "'".$individual_data_map['individual_ctrl_no']."'");

                 // 着用者コード
                 array_push($calum_list, "werer_cd");
                 array_push($values_list, "'".$wearer_edit_post['werer_cd']."'");
                 // 客先社員コード
                 array_push($calum_list, "cster_emply_cd");
                 array_push($values_list, "'".$wearer_data_input['cster_emply_cd']."'");
                 // レンタル部門コード
                 array_push($calum_list, "rntl_sect_cd");
                 array_push($values_list, "'".$wearer_data_input['section']."'");
                 // 貸与パターン
                 $job_type_cd = explode(':', $wearer_data_input['job_type']);
                 $job_type_cd = $job_type_cd[0];
                 array_push($calum_list, "job_type_cd");
                 array_push($values_list, "'".$job_type_cd."'");
                 // 発注依頼日
                 array_push($calum_list, "order_date");
                 array_push($values_list, "'".date('Y/m/d H:i:s', time())."'");
                 // 返却日
                 array_push($calum_list, "return_date");
                 array_push($values_list, "'".date('Y/m/d H:i:s', time())."'");
                 // 返却ステータス(未返却)
                 array_push($calum_list, "return_status");
                 array_push($values_list, "'1'");
                 // 発注状況区分(異動)
                 array_push($calum_list, "order_sts_kbn");
                 array_push($values_list, "'5'");
                 // 返却予定数
                 array_push($calum_list, "return_plan_qty");
                 array_push($values_list, "'".$now_item_map['now_return_num']."'");
                 // 返却数
                 array_push($calum_list, "return_plan_qty");
                 array_push($values_list, "'0'");
                 // 送信区分(送信済み)
                 array_push($calum_list, "snd_kbn");
                 array_push($values_list, "'1'");
                 // 理由区分
                 array_push($calum_list, "order_reason_kbn");
                 array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
                 // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
                 $m_section_comb_hkey = md5(
                   $auth['corporate_id']
                   .$wearer_data_input['agreement_no']
                   .$wearer_data_input['section']
                 );
                 array_push($calum_list, "m_section_comb_hkey");
                 array_push($values_list, "'".$m_section_comb_hkey."'");
                 // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
                 $m_item_comb_hkey = md5(
                   $auth['corporate_id']
                   .$now_item_map['now_item_cd']
                   .$now_item_map['now_color_cd']
                   .$now_item_map['now_size_cd']
                 );
                 array_push($calum_list, "m_item_comb_hkey");
                 array_push($values_list, "'".$m_item_comb_hkey."'");
                 $calum_query = implode(',', $calum_list);
                 $values_query = implode(',', $values_list);

                 $arg_str = "";
                 $arg_str = "INSERT INTO t_returned_plan_info";
                 $arg_str .= "(".$calum_query.")";
                 $arg_str .= " VALUES ";
                 $arg_str .= "(".$values_query.")";
                 //ChromePhp::LOG($arg_str);
                 $t_returned_plan_info = new TReturnedPlanInfo();
                 $results = new Resultset(NULL, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
                 //ChromePhp::LOG($results);
               }
             }
           } else {
             // 個体管理番号非表示につき、商品単位での登録処理
             $calum_list = array();
             $values_list = array();

             // 発注依頼行No.生成
             $order_req_line_no = $cnt++;

             // 企業ID
             array_push($calum_list, "corporate_id");
             array_push($values_list, "'".$auth['corporate_id']."'");
             // 発注依頼No.
             array_push($calum_list, "order_req_no");
             array_push($values_list, "'".$shin_order_req_no."'");
             // 発注依頼行No.
             array_push($calum_list, "order_req_line_no");
             array_push($values_list, "'".$order_req_line_no."'");
             // レンタル契約No
             array_push($calum_list, "rntl_cont_no");
             array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
             // 商品コード
             array_push($calum_list, "item_cd");
             array_push($values_list, "'".$now_item_map['now_item_cd']."'");
             // 色コード
             array_push($calum_list, "color_cd");
             array_push($values_list, "'".$now_item_map['now_color_cd']."'");
             // サイズコード
             array_push($calum_list, "size_cd");
             array_push($values_list, "'".$now_item_map['now_size_cd']."'");
             // 個体管理番号
             array_push($calum_list, "individual_ctrl_no");
             array_push($values_list, "NULL");
             // 着用者コード
             array_push($calum_list, "werer_cd");
             array_push($values_list, "'".$wearer_edit_post['werer_cd']."'");
             // 客先社員コード
             array_push($calum_list, "cster_emply_cd");
             array_push($values_list, "'".$wearer_data_input['cster_emply_cd']."'");
             // レンタル部門コード
             array_push($calum_list, "rntl_sect_cd");
             array_push($values_list, "'".$wearer_data_input['section']."'");
             // 貸与パターン
             $job_type_cd = explode(':', $wearer_data_input['job_type']);
             $job_type_cd = $job_type_cd[0];
             array_push($calum_list, "job_type_cd");
             array_push($values_list, "'".$job_type_cd."'");
             // 発注依頼日
             array_push($calum_list, "order_date");
             array_push($values_list, "'".date('Y/m/d H:i:s', time())."'");
             // 返却日
             array_push($calum_list, "return_date");
             array_push($values_list, "'".date('Y/m/d H:i:s', time())."'");
             // 返却ステータス(未返却)
             array_push($calum_list, "return_status");
             array_push($values_list, "'1'");
             // 発注状況区分(異動)
             array_push($calum_list, "order_sts_kbn");
             array_push($values_list, "'5'");
             // 返却予定数
             array_push($calum_list, "return_plan_qty");
             array_push($values_list, "'".$now_item_map['now_return_num']."'");
             // 返却数
             array_push($calum_list, "return_plan_qty");
             array_push($values_list, "'0'");
             // 送信区分(送信済み)
             array_push($calum_list, "snd_kbn");
             array_push($values_list, "'1'");
             // 理由区分
             array_push($calum_list, "order_reason_kbn");
             array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
             // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
             $m_section_comb_hkey = md5(
               $auth['corporate_id']
               .$wearer_data_input['agreement_no']
               .$wearer_data_input['section']
             );
             array_push($calum_list, "m_section_comb_hkey");
             array_push($values_list, "'".$m_section_comb_hkey."'");
             // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
             $m_item_comb_hkey = md5(
               $auth['corporate_id']
               .$now_item_map['now_item_cd']
               .$now_item_map['now_color_cd']
               .$now_item_map['now_size_cd']
             );
             array_push($calum_list, "m_item_comb_hkey");
             array_push($values_list, "'".$m_item_comb_hkey."'");
             $calum_query = implode(',', $calum_list);
             $values_query = implode(',', $values_list);

             $arg_str = "";
             $arg_str = "INSERT INTO t_returned_plan_info";
             $arg_str .= "(".$calum_query.")";
             $arg_str .= " VALUES ";
             $arg_str .= "(".$values_query.")";
             //ChromePhp::LOG($arg_str);
             $t_returned_plan_info = new TReturnedPlanInfo();
             $results = new Resultset(NULL, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
             //ChromePhp::LOG($results);
           }
         }
       }
     }

     // トランザクションコミット
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
   } catch (Exception $e) {
     ChromePhp::LOG($e);

     // トランザクションロールバック
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));

     $json_list["error_code"] = "1";
     $error_msg = "発注登録処理において、データ更新エラーが発生しました。";
     array_push($json_list["error_msg"], $error_msg);

     echo json_encode($json_list);
     return;
   }

   echo json_encode($json_list);
 }
});