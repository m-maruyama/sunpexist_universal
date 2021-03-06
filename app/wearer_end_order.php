<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * 発注入力
 * 入力項目：理由区分
 */
$app->post('/wearer_end/reason_kbn', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_end_post = $app->session->get("wearer_end_post");
    //ChromePhp::LOG($wearer_end_post);

    //--発注管理単位取得--//
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "job_type_cd = '".$wearer_end_post['job_type_cd']."'");
    $query = implode(' AND ', $query_list);
    $arg_str = '';
    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_job_type';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $m_job_type = new MJobType();
    $results = new Resultset(null, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
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
        $order_control_unit = $result->order_control_unit;
    }

    //--理由区分リスト取得--//
    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();
    array_push($query_list, "cls_cd = '002'");
    array_push($query_list, "relation_cls_cd = '001'");
    array_push($query_list, "relation_gen_cd = '2'");
    array_push($query_list, "relation_cls_cd_2 = '003'");
    array_push($query_list, "relation_gen_cd_2 = '".$order_control_unit."'");
    $query = implode(' AND ', $query_list);
    $arg_str = '';
    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_gencode';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY dsp_order asc';
    $m_gencode = new MGencode();
    $results = new Resultset(null, $m_gencode, $m_gencode->getReadConnection()->query($arg_str));
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

      //理由区分未選択追加
      $all_list[] = array(
      'reason_kbn' => '',
      'reason_kbn_name' => '',
      'selected' => ''
      );
        foreach ($results as $result) {
            if($result->gen_cd!='07'){
                $list['reason_kbn'] = $result->gen_cd;
                $list['reason_kbn_name'] = $result->gen_name;
                $list['selected'] = '';
                // 発注情報トランフラグ有の場合は初期選択状態版を生成
                if ($list['reason_kbn'] == $wearer_end_post['order_reason_kbn']) {
                    $list['selected'] = 'selected';
                }
                array_push($all_list, $list);
            }
        }
    } else {
        $list['reason_kbn'] = null;
        $list['reason_kbn_name'] = '';
        $list['selected'] = '';
        array_push($all_list, $list);
    }
    $json_list['reason_kbn_list'] = $all_list;
    echo json_encode($json_list);
});

/**
 * 発注入力（貸与終了）
 * 入力項目：初期値情報、前画面セッション取得
 *
 */
$app->post('/wearer_end_order_info', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
  $auth = $app->session->get("auth");
  //ChromePhp::LOG($auth);

  // 前画面セッション取得
  $wearer_end_post = $app->session->get("wearer_end_post");
  //ChromePhp::LOG($wearer_end_post);

  $json_list = array();

  //--着用者入力項目情報--//
  $all_list = array();
  $list = array();
  $json_list['wearer_info'] = "";

  // 発注情報トラン参照
  $query_list = array();
  array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
  array_push($query_list, "t_order_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
  array_push($query_list, "t_order_tran.order_sts_kbn = '2'");
  array_push($query_list, "t_order_tran.order_reason_kbn <> '07'");
  $query = implode(' AND ', $query_list);
  $arg_str = "";
  $arg_str = "SELECT distinct on (order_req_no) ";
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
  // コメント欄、異動日
  $list["comment"] = "";
  $list["resfl_ymd"] = "";
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
      $list["comment"] = $result->memo;
      $ymd = $result->resfl_ymd;
      if (!empty($ymd)) {
        $list["resfl_ymd"] = date("Y/m/d", strtotime($ymd));
      }
    }
  }

  // 着用者基本マスタトラン参照
  $query_list = array();
  array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
  array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
  array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_end_post['rntl_sect_cd']."'");
  array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_end_post['job_type_cd']."'");
  array_push($query_list, "m_wearer_std_tran.order_sts_kbn = '2'");
  array_push($query_list, "t_order_tran.order_reason_kbn <> '07'");
  $query = implode(' AND ', $query_list);
  $arg_str = "";
  $arg_str = "SELECT ";
  $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
  $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
  $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana,";
  $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn";
  $arg_str .= " FROM ";
  $arg_str .= "m_wearer_std_tran";
  $arg_str .= " INNER JOIN t_order_tran";
  $arg_str .= " ON m_wearer_std_tran.order_req_no = t_order_tran.order_req_no";
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
    // 着用者基本マスタトラン有り
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
      // 社員番号
      $list['cster_emply_cd'] = $result->as_cster_emply_cd;
      // 着用者名
      $list['werer_name'] = $result->as_werer_name;
      // 着用者名（カナ）
      $list['werer_name_kana'] = $result->as_werer_name_kana;
      // 性別
      $list['sex_kbn'] = $result->as_sex_kbn;
    }

    array_push($all_list, $list);
  }

  // 上記参照のトラン情報がない場合、着用者基本マスタ情報を参照する
  if (empty($all_list)) {
    // 着用者基本マスタトラン無し
    $json_list['tran_flg'] = "0";

    $query_list = array();
    array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "m_wearer_std.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "m_wearer_std.werer_cd = '".$wearer_end_post['werer_cd']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std.werer_name_kana as as_werer_name_kana,";
    $arg_str .= "m_wearer_std.sex_kbn as as_sex_kbn";
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
        // 社員番号
        $list['cster_emply_cd'] = $result->as_cster_emply_cd;
        // 着用者名
        $list['werer_name'] = $result->as_werer_name;
        // 着用者名（カナ）
        $list['werer_name_kana'] = $result->as_werer_name_kana;
        // 性別
        $list['sex_kbn'] = $result->as_sex_kbn;
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
  array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
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
      $list['rntl_cont_name'] = $result->rntl_emply_cont_name;
      array_push($all_list, $list);
    }
  } else {
    $list['rntl_cont_no'] = null;
    $list['rntl_cont_name'] = '';
    array_push($all_list, $list);
  }
  $json_list['agreement_no_list'] = $all_list;

  //--拠点--//
  $all_list = array();
  $json_list['section_list'] = "";
  $query_list = array();
  $list = array();
  array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
  array_push($query_list, "rntl_sect_cd = '".$wearer_end_post['rntl_sect_cd']."'");
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
  array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
  array_push($query_list, "job_type_cd = '".$wearer_end_post['job_type_cd']."'");
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

  //--出荷先--//
  $all_list = array();
  $list = array();
  $list['ship_to_cd'] = $wearer_end_post['ship_to_cd'];
  $list['ship_to_brnch_cd'] = $wearer_end_post['ship_to_brnch_cd'];
  array_push($all_list, $list);
  $json_list['shipment_list'] = $all_list;
  //ChromePhp::LOG($json_list['shipment_list']);

  //--発注情報トラン・返却予定情報トラン内、「貸与終了」情報の有無確認--//
  //※発注情報トラン参照
  $query_list = array();
  array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
  array_push($query_list, "t_order_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
  array_push($query_list,"t_order_tran.order_sts_kbn = '2'");
  array_push($query_list,"t_order_tran.order_reason_kbn <> '07'");
  $query = implode(' AND ', $query_list);
  $arg_str = "";
  $arg_str = "SELECT ";
  $arg_str .= "order_req_no";
  $arg_str .= " FROM ";
  $arg_str .= "t_order_tran";
  $arg_str .= " WHERE ";
  $arg_str .= $query;
  $arg_str .= " ORDER BY t_order_tran.upd_date DESC";
  //ChromePhp::LOG($arg_str);
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
    foreach ($results as $result) {
      // 発注情報トラン.発注No
      $json_list['order_req_no'] = $result->order_req_no;
      // 発注情報トランフラグ
      $json_list['order_tran_flg'] = "1";
    }
  } else {
    // 発注情報トラン.発注No
    $json_list['order_req_no'] = "";
    // 発注情報トランフラグ
    $json_list['order_tran_flg'] = "0";
  }
  // ※返却予定情報トラン参照
  $query_list = array();
  array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
  array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
  array_push($query_list,"t_returned_plan_info_tran.order_sts_kbn = '2'");
  array_push($query_list,"t_returned_plan_info_tran.order_reason_kbn <> '07'");
  $query = implode(' AND ', $query_list);
  $arg_str = "";
  $arg_str = "SELECT ";
  $arg_str .= "order_req_no";
  $arg_str .= " FROM ";
  $arg_str .= "t_returned_plan_info_tran";
  $arg_str .= " WHERE ";
  $arg_str .= $query;
  $arg_str .= " ORDER BY order_req_no DESC";
  //ChromePhp::LOG($arg_str);
  $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
  $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
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
    foreach ($results as $result) {
      // 返却予定情報トラン.発注No
      $json_list['return_req_no'] = $result->order_req_no;
      // 返却予定情報トランフラグ
      $json_list['return_tran_flg'] = "1";
    }
  } else {
    // 返却予定情報トラン.発注No
    $json_list['return_req_no'] = "";
    // 返却予定情報トランフラグ
    $json_list['return_tran_flg'] = "0";
  }

  //--前画面セッション情報--//
  // レンタル契約No
  $json_list['rntl_cont_no'] = $wearer_end_post["rntl_cont_no"];
  // 部門コード
  $json_list['rntl_sect_cd'] = $wearer_end_post["rntl_sect_cd"];
  // 貸与パターン
  $json_list['job_type_cd'] = $wearer_end_post["job_type_cd"];
  // 着用者コード
  $json_list['werer_cd'] = $wearer_end_post["werer_cd"];
  // 着用者基本マスタトランフラグ
  $json_list['wearer_tran_flg'] = $wearer_end_post["wearer_tran_flg"];

  echo json_encode($json_list);
});

/**
 * 発注入力
 * 入力項目：返却商品一覧
 */
$app->post('/wearer_end_order_list', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_end_post = $app->session->get("wearer_end_post");

    // フロントパラメータ取得
    $cond = $params['data'];
    //個体管理番号表示フラグ
    $individual_flg = individual_flg($auth['corporate_id'], $wearer_end_post['rntl_cont_no']);

    //--一覧生成用の主要部門コード・職種コード取得--//
    // 着用者基本マスタ参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
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
      foreach ($results as $result) {
        // 着用者基本マスタ.レンタル部門コード
        $m_wearer_rntl_sect_cd = $result->rntl_sect_cd;
        // 着用者基本マスタ.職種コード
        $m_wearer_job_type_cd = $result->job_type_cd;
      }
    }

    // 発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
    array_push($query_list, "order_req_no = '".$wearer_end_post['order_req_no']."'");
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
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_end_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_end_post['job_type_cd']."'");
    array_push($query_list, "order_req_no = '".$wearer_end_post['return_req_no']."'");
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

    //--返却商品の取得--//
    $query_list = array();
    $list = array();
    $now_wearer_list = array();
    array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_end_post['werer_cd']."'");
    array_push($query_list, "t_delivery_goods_state_details.rtn_ok_flg = '1'");
    array_push($query_list, "t_delivery_goods_state_details.receipt_status = '2'");
    //自分の貸与パターンを絞り込み
    $query_list[] = "m_input_item.job_type_cd = '".$m_wearer_job_type_cd."'";
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str .= "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (m_input_item.item_cd, m_input_item.color_cd, m_input_item.job_type_item_cd) ";
    $arg_str .= "t_delivery_goods_state_details.quantity as as_quantity,";
    $arg_str .= "t_delivery_goods_state_details.return_plan__qty as as_return_plan_qty,";
    $arg_str .= "t_delivery_goods_state_details.returned_qty as as_returned_qty,";
    $arg_str .= "t_delivery_goods_state_details.werer_cd as as_werer_cd,";
    $arg_str .= "m_item.item_cd as as_item_cd,";
    $arg_str .= "m_item.color_cd as as_color_cd,";
    $arg_str .= "m_item.size_cd as as_size_cd,";
    $arg_str .= "m_item.item_name as as_item_name,";
    $arg_str .= "m_input_item.job_type_cd as as_job_type_cd,";
    $arg_str .= "m_input_item.job_type_item_cd as as_job_type_item_cd,";
    $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
    $arg_str .= "m_input_item.size_two_cd as as_size_two_cd,";
    $arg_str .= "m_input_item.std_input_qty as as_std_input_qty";
    $arg_str .= " FROM ";
    $arg_str .= "t_delivery_goods_state_details INNER JOIN m_item";
    $arg_str .= " ON (t_delivery_goods_state_details.corporate_id = m_item.corporate_id";
    $arg_str .= " AND t_delivery_goods_state_details.item_cd = m_item.item_cd";
    $arg_str .= " AND t_delivery_goods_state_details.color_cd = m_item.color_cd";
    $arg_str .= " AND t_delivery_goods_state_details.size_cd = m_item.size_cd)";
    $arg_str .= " INNER JOIN m_input_item";
    $arg_str .= " ON (m_item.corporate_id = m_input_item.corporate_id";
    $arg_str .= " AND m_item.item_cd = m_input_item.item_cd";
    $arg_str .= " AND m_item.color_cd = m_input_item.color_cd)";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_item_cd, as_job_type_item_cd, as_color_cd ASC";
    //ChromePhp::log($arg_str);
    $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
    $results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
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
        // レンタル契約No
        $list["rntl_cont_no"] = $wearer_end_post['rntl_cont_no'];
        // 商品コード
        $list["item_cd"] = $result->as_item_cd;
        // 色コード
        $list["color_cd"] = $result->as_color_cd;
        // サイズコード
        $list["size_cd"] = $result->as_size_cd;
        // 商品名
        $list["item_name"] = $result->as_item_name;
        // 職種コード
        $list["job_type_cd"] = $m_wearer_job_type_cd;
        // 部門コード
        $list["rntl_sect_cd"] = $m_wearer_rntl_sect_cd;
        // 職種アイテムコード
        $list["job_type_item_cd"] = $result->as_job_type_item_cd;
        // サイズコード2
        $list["size_two_cd"] = $result->as_size_two_cd;
        // 標準投入数
        $list["std_input_qty"] = $result->as_std_input_qty;
        // 投入商品名
        $list["input_item_name"] = $result->as_input_item_name;

          //返却予定数と数量の総数を計算する。
          $parameter = array(
              "corporate_id" => $auth['corporate_id'],
              "rntl_cont_no" => $wearer_end_post['rntl_cont_no'],
              "werer_cd" => $result->as_werer_cd,
              "item_cd" => $result->as_item_cd,
              "size_cd" => $result->as_size_cd);
          //返却予定数の総数
          $TDeliveryGoodsStateDetails = TDeliveryGoodsStateDetails::find(array(
              'conditions'  => "corporate_id = :corporate_id: AND rntl_cont_no = :rntl_cont_no:  AND werer_cd = :werer_cd: AND item_cd = :item_cd: AND size_cd = :size_cd:",
              "bind" => $parameter
          ));
          $each_item_count = $TDeliveryGoodsStateDetails->count();
          // foreachでまわす
          $each_item_return_plan_qty = 0;
          $each_item_quantity = 0;
          for($i = 0; $i < $each_item_count; $i++){
              $each_item_return_plan_qty = $each_item_return_plan_qty + $TDeliveryGoodsStateDetails[$i]->return_plan__qty;
              $each_item_quantity = $each_item_quantity + $TDeliveryGoodsStateDetails[$i]->quantity;
          }

        // 数量 納品状況明細情報の商品ごとの数量を合計した数
        $list["quantity"] = $each_item_quantity;
        // 返却予定数 納品状況明細情報の商品ごとの返却予定数を合計した数
        $list["return_plan_qty"] = $each_item_return_plan_qty;
        // 返却済数
        $list["returned_qty"] = $result->as_returned_qty;
        // 商品単位の返却可能枚数(所持枚数)
        //$list["possible_num"] = $list["quantity"] - $list["return_plan_qty"] - $list["returned_qty"];
        // 商品単位の返却可能枚数(所持枚数) 数量の総数 - 返却予定数の総数
        $list["possible_num"] = $list["quantity"] - $list["return_plan_qty"];
        if($list["possible_num"] > 0){
        array_push($now_wearer_list, $list);
        }
      }
    }

    $chk_list = array();
    $now_list = array();
    if (!empty($now_wearer_list)) {
        $arr_cnt = 0;
        $list_cnt = 1;
        $rowspan = '';
        foreach ($now_wearer_list as $now_wearer_map) {
            $list = array();
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
            array_push($query_list, "m_input_item.corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "m_input_item.rntl_cont_no = '".$now_wearer_map['rntl_cont_no']."'");
            array_push($query_list, "m_input_item.job_type_cd = '".$now_wearer_map['job_type_cd']."'");
            array_push($query_list, "m_input_item.item_cd = '".$now_wearer_map['item_cd']."'");
            array_push($query_list, "m_input_item.job_type_item_cd = '". $now_wearer_map["job_type_item_cd"]."'");
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
                if(!$rowspan){
                    $rowspan = 'rowspan='.$results_cnt;
                    $list["rowspan"] = $rowspan;
                }else{
                    $rowspan = 'style=display:none';
                    $list["rowspan"] = $rowspan;

                }
                $list["choice"] = "複数選択";
                $list["choice_type"] = "2";
            } else {
                $list["choice"] = "単一選択";
                $list["choice_type"] = "1";
                $list["rowspan"] = null;
                $rowspan = '';
            }
            // 標準枚数
            $list["std_input_qty"] = $now_wearer_map['std_input_qty'];
            // 商品-色
            $list["item_and_color"] = $now_wearer_map['item_cd']."-".$now_wearer_map['color_cd'];
            // 商品名
            $list["input_item_name"] = $now_wearer_map['input_item_name'];
            // サイズ
            $list["size_cd"] = $now_wearer_map['size_cd'];
            // 個体管理番号
            // ※個体管理番号リスト、対象チェックボックス値の生成
            if ($individual_flg == "1") {
              $list["individual_ctrl_no"] = "";
              $list["individual_chk"] = array();
              $individual_ctrl_no = array();
              $query_list = array();
              array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
              array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
              array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_end_post['werer_cd']."'");
              array_push($query_list, "t_delivery_goods_state_details.item_cd = '".$now_wearer_map['item_cd']."'");
              array_push($query_list, "t_delivery_goods_state_details.color_cd = '".$now_wearer_map['color_cd']."'");
              array_push($query_list, "t_delivery_goods_state_details.size_cd = '".$list["size_cd"]."'");
              $query = implode(' AND ', $query_list);
              $arg_str = "";
              $arg_str = "SELECT ";
              $arg_str .= "individual_ctrl_no,";
              $arg_str .= "quantity,";
              $arg_str .= "return_plan__qty,";
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
                  $last_val = count($results);
                  $cnt = 1;
                  //ChromePhp::LOG($results);
                  foreach ($results as $result) {
                      $cnt = $cnt++;
                      if($result->quantity - $result->return_plan__qty !== 0 ){
                      array_push($individual_ctrl_no, $result->individual_ctrl_no);

                      // 返却可能フラグによるdisable制御
                      $individual = array();
                      $individual["individual_ctrl_no"] = $result->individual_ctrl_no;
                      $individual["checked"] = "checked";
                      $individual["disabled"] = "disabled";

                      // 対象チェックname属性No
                      $individual["name_no"] = $list["arr_num"];
                      // 対象チェックボックス改行設定
                      if ($last_val == $cnt) {
                          $individual["br"] = "";
                      } else {
                          $individual["br"] = "<br/>";
                      }

                      // 対象チェックボックス値
                      array_push($list["individual_chk"], $individual);
                      }
                  }

                  // 表示個体管理番号数
                  $list["individual_cnt"] = count($individual_ctrl_no);
                  // 個体管理番号
                  $list["individual_ctrl_no"] = implode("<br>", $individual_ctrl_no);
              }
                $list["return_num"] = $list["individual_cnt"];
                $list["possible_num"] = $list["individual_cnt"];
            }
            // ※返却可能枚数
            $list["possible_num"] = 0;

            if ($individual_flg == "0") {
                /*
                for ($i = 0; $i < count($now_wearer_list); $i++) {
                    if (
                        $now_wearer_map['item_cd'] == $now_wearer_list[$i]['item_cd']
                        && $now_wearer_map['color_cd'] == $now_wearer_list[$i]['color_cd']
                    ) {
                        if ($now_wearer_list[$i]['std_input_qty'] < $now_wearer_map['std_input_qty']) {
                            $list["return_num"] = $now_wearer_map['possible_num'];
                        }
                    } else {
                        $list["return_num"] = $now_wearer_map['possible_num'];
                    }
                }
                */
                // 返却可能枚数（所持数）
                $list["return_num_disable"] = "disabled";
                $list["possible_num"] = $now_wearer_map['possible_num'];
                $list["return_num"] = $now_wearer_map['possible_num'];
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

            // 発注商品一覧の入力項目「サイズ」作成
            //サイズコードセレクトボックス作成
            $size_list = array();
            $size_list_to = array();
            $size_list['size_cd'] = $list["size_cd"];
            array_push($size_list_to , $size_list);
            // サイズコードセレクトボックス
            $list['size_cd_list'] = $size_list_to;
            array_push($now_list, $list);
        }
    }
    // 返却商品一覧内容の表示フラグ
    if (!empty($now_list)) {
        $json_list["list_disp_flg"] = true;
    } else {
        $json_list["list_disp_flg"] = false;
    }

    //「対象」、「個体管理番号」列の表示/非表示の制御フラグ
    $json_list["individual_flg"] = $individual_flg;
    $json_list["list_cnt"] = count($now_list);
    $json_list['list'] = $now_list;
    echo json_encode($json_list);
});

/*
 *  入力完了 or 発注送信
 */
$app->post('/wearer_end_order_insert', function () use ($app) {
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション
  $auth = $app->session->get('auth');
  //ChromePhp::LOG($auth);

  // 前画面セッション
  $wearer_end_post = $app->session->get("wearer_end_post");
  //ChromePhp::LOG($wearer_end_post);

  // フロント側パラメータ
  $mode = $params["mode"];
  $wearer_data_input = $params["wearer_data"];
  $item_list = $params["item"];
  //ChromePhp::LOG($mode);
  //ChromePhp::LOG($item_list);

  $query_list = array();
  $list = array();
  $json_list = array();
  $error_list = array();

  // DB更新エラーコード 0:正常 1:更新エラー
  $json_list["error_code"] = "0";
  $json_list["error_msg"] = array();

  if ($mode == "check") {
    // 共通
    if (empty($item_list)) {
      $json_list["error_code"] = "1";
      $error_msg = "対象商品がない為、貸与終了の発注を行うことができません。";
      array_push($json_list["error_msg"], $error_msg);
      echo json_encode($json_list);
      return;
    }
    if (!$wearer_data_input['resfl_ymd']) {
        $json_list["error_code"] = "1";
        $error_msg = "異動日を入力してください。";
        array_push($json_list["error_msg"], $error_msg);
    }
    //理由区分
    if (empty($wearer_data_input["reason_kbn"])) {
      $json_list["error_code"] = "1";
      $error_msg = "理由区分を選択してください。";
      array_push($json_list["error_msg"], $error_msg);
    }





/*
    // 社員番号
    if ($wearer_data_input['emply_cd_flg']) {
      if (mb_strlen($wearer_data_input['member_no']) == 0) {
        $json_list["error_code"] = "1";
        $error_msg = "社員番号ありにチェックしている場合、社員番号を入力してください。";
        array_push($json_list["error_msg"], $error_msg);
      }
    }
    if (!$wearer_data_input['emply_cd_flg']) {
      if (mb_strlen($wearer_data_input['member_no']) > 0) {
        $json_list["error_code"] = "1";
        $error_msg = "社員番号ありにチェックしていない場合、社員番号の入力は不要です。";
        array_push($json_list["error_msg"], $error_msg);
      }
    }
    // 着用者名
    if (empty($wearer_data_input["member_name"])) {
      $json_list["error_code"] = "1";
      $error_msg = "着用者名を入力してください。";
      array_push($json_list["error_msg"], $error_msg);
    }
    if (mb_strlen($wearer_data_input['member_name']) > 0) {
       if (strlen(mb_convert_encoding($wearer_data_input['member_name'], "SJIS")) > 22) {
         $json_list["error_code"] = "1";
         $error_msg = "着用者名が規定の文字数をオーバーしています。";
         array_push($json_list["error_msg"], $error_msg);
       }

       $get_strs = "";
       $get_strs = preg_split('//u', $wearer_data_input["comment"], -1, PREG_SPLIT_NO_EMPTY);
       if (!empty($get_strs)) {
         foreach ($get_strs as $get_str) {
           if (mb_convert_variables('SJIS', 'UTF-8', $get_str) == false) {
             $json_list["error_code"] = "1";
             $error_msg = "コメント欄にて使用できない文字が含まれています。";
             array_push($json_list["error_msg"], $error_msg);
             break;
           }
         }
       }
    }
    // 着用者名（カナ）
    if (empty($wearer_data_input["member_name_kana"])) {
      $json_list["error_code"] = "1";
      $error_msg = "着用者名(カナ)を入力してください。";
      array_push($json_list["error_msg"], $error_msg);
    }
    if (mb_strlen($wearer_data_input['member_name_kana']) > 0) {
       if (strlen(mb_convert_encoding($wearer_data_input['member_name_kana'], "SJIS")) > 25) {
         $json_list["error_code"] = "1";
         $error_msg = "着用者名(カナ)が規定の文字数をオーバーしています。";
         array_push($json_list["error_msg"], $error_msg);
       }

       $get_strs = "";
       $get_strs = preg_split('//u', $wearer_data_input["comment"], -1, PREG_SPLIT_NO_EMPTY);
       if (!empty($get_strs)) {
         foreach ($get_strs as $get_str) {
           if (mb_convert_variables('SJIS', 'UTF-8', $get_str) == false) {
             $json_list["error_code"] = "1";
             $error_msg = "コメント欄にて使用できない文字が含まれています。";
             array_push($json_list["error_msg"], $error_msg);
             break;
           }
         }
       }
    }
*/
    // コメント欄
    if (mb_strlen($wearer_data_input['comment']) > 0) {
      if (strlen(mb_convert_encoding($wearer_data_input['comment'], "SJIS")) > 100) {
        $json_list["error_code"] = "1";
        $error_msg = "コメント欄は50文字以内で入力してください。";
        array_push($json_list["error_msg"], $error_msg);
      }
      //コメント欄使用不可文字
      $str_utf8 = $wearer_data_input['comment'];
      if (convert_not_sjis($str_utf8) !== true) {
          $output_text = convert_not_sjis($str_utf8);
          $json_list["error_code"] = "1";
          $error_msg = 'コメント欄に使用できない文字が含まれています。' . "$output_text";
          array_push($json_list["error_msg"], $error_msg);
      };
    }
    // ※発注情報状況の商品レコード取得
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
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
        "rntl_cont_no" => $wearer_end_post['rntl_cont_no'],
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
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
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
        "rntl_cont_no" => $wearer_end_post['rntl_cont_no'],
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
      $json_list["error_code"] = "1";
      $error_msg = "対象の方は未出荷の商品がある為、貸与終了の発注はできません。";
      array_push($json_list["error_msg"], $error_msg);
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
        //ChromePhp::log($order_item_list[$i]['unshipped_qty']);
        //未出荷商品が0以上または、発注情報があるのに、出荷情報（発注状況）がない場合はエラー
        if($order_item_list[$i]['unshipped_qty'] > 0 || is_null($order_item_list[$i]['unshipped_qty'])){
          $json_list["error_code"] = "1";
          $error_msg = "対象の方は未出荷の商品がある為、貸与終了の発注はできません。";
          array_push($json_list["error_msg"], $error_msg);

          echo json_encode($json_list);
          return;
        }
      }
    }

      $query_list = array();
      array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
      array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
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
          $json_list["error_code"] = "1";
          $error_msg = "対象の方は未受領の商品がある為、貸与終了の発注を完了できません。";
          array_push($json_list["error_msg"], $error_msg);
          echo json_encode($json_list);
          return;
      }

    echo json_encode($json_list);
  } else if ($mode == "update") {
    //--発注NGパターンチェック-- ここから//
    //※着用者基本マスタトラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str .= "SELECT ";
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
              "limit" => $results_cnt,
              "page" => 1
          )
      );
      $paginator = $paginator_model->getPaginate();
      $results = $paginator->items;
      //ChromePhp::LOG($results);
      foreach ($results as $result) {
        // 着用者基本マスタトラン.発注状況区分 = 「着用者編集」の情報がある際は発注NG
        $order_sts_kbn = $result->order_sts_kbn;
        if ($order_sts_kbn == "6") {
          $json_list["error_code"] = "1";
          $error_msg = "着用者編集の発注が登録されていた為、操作を完了できませんでした。着用者編集の発注を削除してから再度登録して下さい。";
          array_push($json_list["error_msg"], $error_msg);
          echo json_encode($json_list);
          return;
        }
      }
    }
    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str .= "SELECT ";
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
              "limit" => $results_cnt,
              "page" => 1
          )
      );
      $paginator = $paginator_model->getPaginate();
      $results = $paginator->items;
      //ChromePhp::LOG($results);
      foreach ($results as $result) {
          $order_sts_kbn = $result->order_sts_kbn;
          $order_reason_kbn = $result->order_reason_kbn;

          // 発注情報トラン.発注状況区分 = 「終了(貸与終了)」以外の発注がある際は発注NG
          if ($order_sts_kbn == "1" && ($order_reason_kbn == "03" || $order_reason_kbn == "27")) {
              $json_list["error_code"] = "1";
              $error_msg = "追加貸与の発注が登録されていた為、操作を完了できませんでした。追加貸与の発注を削除してから再度登録して下さい。";
              array_push($json_list["error_msg"], $error_msg);
              echo json_encode($json_list);
              return;
          }
          if ($order_sts_kbn == "5") {
              $json_list["error_code"] = "1";
              $error_msg = "職種変更または異動の発注が登録されていた為、操作を完了できませんでした。職種変更または異動の発注を削除してから再度登録して下さい。";
              array_push($json_list["error_msg"], $error_msg);
              echo json_encode($json_list);
              return;
          }
          if ($order_sts_kbn == "2" && ($order_reason_kbn == "07" || $order_reason_kbn == "28")) {
              $json_list["error_code"] = "1";
              $error_msg = "不要品返却の発注が登録されていた為、操作を完了できませんでした。不要品返却の発注を削除してから再度登録して下さい。";
              array_push($json_list["error_msg"], $error_msg);
              echo json_encode($json_list);
              return;
          }
          if ($order_sts_kbn == "3" || $order_sts_kbn == "4") {
              $json_list["error_code"] = "1";
              $error_msg = "交換の発注が登録されていた為、操作を完了できませんでした。交換の発注を削除してから再度登録して下さい。";
              array_push($json_list["error_msg"], $error_msg);
              echo json_encode($json_list);
              return;
          }
      }
    }

    // ※発注情報状況の商品レコード取得
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
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
        "rntl_cont_no" => $wearer_end_post['rntl_cont_no'],
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
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
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
        "rntl_cont_no" => $wearer_end_post['rntl_cont_no'],
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
        }
        $list['order_qty'] = $each_item_order;
        $list['unshipped_qty'] = null;

        array_push($order_item_list, $list);
      }
    }
    //出荷情報が0な時点で未出荷があるとみなし、未出荷エラー
    if($results_cnt_ship_item == 0){
      $json_list["error_code"] = "1";
      $error_msg = "対象の方は未出荷の商品がある為、貸与終了の発注はできません。";
      array_push($json_list["error_msg"], $error_msg);
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
          $json_list["error_code"] = "1";
          $error_msg = "対象の方は未出荷の商品がある為、貸与終了の発注はできません。";
          array_push($json_list["error_msg"], $error_msg);
        }
      }
    }

    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
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
      $json_list["error_code"] = "1";
      $error_msg = "対象の方は未受領の商品がある為、貸与終了の発注を完了できません。";
      array_push($json_list["error_msg"], $error_msg);
      echo json_encode($json_list);
      return;
    }
    //--発注NGパターンチェック-- ここまで//

    //--DB登録・更新処理--//
    $t_order_tran = new TOrderTran();
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query('begin'));
    try {
      if (empty($wearer_data_input['tran_req_no'])) {
        // 発注情報トランのデータがない場合、新規入力として発注依頼No.生成
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
      } else {
        // 発注情報トランのデータがある場合、編集入力として既存の発注依頼No.をそのまま使用する
        $shin_order_req_no = $wearer_data_input['tran_req_no'];
      }
      //ChromePhp::LOG("発注依頼No採番");
      //ChromePhp::LOG($shin_order_req_no);

      if ($wearer_end_post['wearer_tran_flg'] == "1") {
        //--着用者基本マスタトランに情報がある場合、更新処理--//
        //ChromePhp::LOG("着用者基本マスタトラン更新");
        $src_query_list = array();
        array_push($src_query_list, "order_req_no = '".$shin_order_req_no."'");
        $src_query = implode(' AND ', $src_query_list);

        $up_query_list = array();
        // 貸与パターン
        $job_type_cd = explode(':', $wearer_data_input['job_type']);
        $job_type_cd = $job_type_cd[0];
        array_push($up_query_list, "job_type_cd = '".$job_type_cd."'");
        // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
        $m_wearer_std_comb_hkey = md5(
          $auth['corporate_id']."-".
          $wearer_end_post["werer_cd"]."-".
          $wearer_data_input['agreement_no']."-".
          $wearer_data_input['section']."-".
          $job_type_cd
        );
        array_push($up_query_list, "m_wearer_std_comb_hkey = '".$m_wearer_std_comb_hkey."'");
        // 発注No
        array_push($up_query_list, "order_req_no = '".$shin_order_req_no."'");
        // 企業ID
        array_push($up_query_list, "corporate_id = '".$auth['corporate_id']."'");
        // 着用者コード
        array_push($up_query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
        // 契約No
        array_push($up_query_list, "rntl_cont_no = '".$wearer_data_input['agreement_no']."'");
        // 部門コード
        array_push($up_query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
        // 客先社員番号
        if (isset($wearer_data_input['member_no'])) {
          array_push($up_query_list, "cster_emply_cd = '".$wearer_data_input['member_no']."'");
        } else {
          array_push($up_query_list, "cster_emply_cd = NULL");
        }
        // 着用者名
        array_push($up_query_list, "werer_name = '".$wearer_data_input['member_name']."'");
        // 着用者名かな
        if (isset($wearer_data_input['member_name_kana'])) {
          array_push($up_query_list, "werer_name_kana = '".$wearer_data_input['member_name_kana']."'");
        } else {
          array_push($up_query_list, "werer_name_kana = NULL");
        }
        // 性別区分
        array_push($up_query_list, "sex_kbn = '".$wearer_data_input['sex_kbn']."'");
        // 着用者状況区分(その他（着用終了）)
        array_push($up_query_list, "werer_sts_kbn = '3'");
        // 異動日
        if (!empty($wearer_data_input['resfl_ymd'])) {
          array_push($up_query_list, "resfl_ymd = '".date("Ymd", strtotime($wearer_data_input['resfl_ymd']))."'");
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
            array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
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
        } else {
          array_push($up_query_list, "ship_to_cd = ''");
          array_push($up_query_list, "ship_to_brnch_cd = ' '");
        }
        // 発注状況区分(終了)
        if ($order_sts_kbn !== "6") {
          array_push($up_query_list, "order_sts_kbn = '2'");
        }
        // 更新区分(WEB発注システム(終了）)
        array_push($up_query_list, "upd_kbn = '2'");
        // Web更新日時
        array_push($up_query_list, "web_upd_date = '".date("Y-m-d H:i:s", time())."'");
        // 送信区分
        array_push($up_query_list, "snd_kbn = '".$wearer_data_input['snd_kbn']."'");
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
          $auth['corporate_id']."-".
          $wearer_data_input['agreement_no']."-".
          $job_type_cd
        );
        array_push($up_query_list, "m_job_type_comb_hkey = '".$m_job_type_comb_hkey."'");
        // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
        $m_section_comb_hkey = md5(
          $auth['corporate_id']."-".
          $wearer_data_input['agreement_no']."-".
          $wearer_data_input['section']
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
        //--着用者基本マスタトラン更新処理 ここまで--//
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
          $auth['corporate_id']."-".
          $wearer_end_post["werer_cd"]."-".
          $wearer_data_input['agreement_no']."-".
          $wearer_data_input['section']."-".
          $job_type_cd
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
        array_push($values_list, "'".$wearer_end_post['werer_cd']."'");
        // レンタル契約No
        array_push($calum_list, "rntl_cont_no");
        array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
        // レンタル部門コード
        array_push($calum_list, "rntl_sect_cd");
        array_push($values_list, "'".$wearer_data_input['section']."'");
        // 客先社員番号
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
        // 着用者状況区分(その他（着用終了）)
        array_push($calum_list, "werer_sts_kbn");
        array_push($values_list, "'3'");
        // 異動日
        if (!empty($wearer_data_input['resfl_ymd'])) {
          array_push($calum_list, "resfl_ymd");
          array_push($values_list, "'".date("Ymd", strtotime($wearer_data_input['resfl_ymd']))."'");
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
            array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
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
        } else {
          array_push($calum_list, "ship_to_cd");
          array_push($values_list, "''");
          array_push($calum_list, "ship_to_brnch_cd");
          array_push($values_list, "' '");
        }
        // 発注状況区分(終了)
        array_push($calum_list, "order_sts_kbn");
        array_push($values_list, "'2'");
        // 更新区分(WEB発注システム(終了))
        array_push($calum_list, "upd_kbn");
        array_push($values_list, "'2'");
        // Web更新日時
        array_push($calum_list, "web_upd_date");
        array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
        // 送信区分
        array_push($calum_list, "snd_kbn");
        array_push($values_list, "'".$wearer_data_input['snd_kbn']."'");
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
          $auth['corporate_id']."-".
          $wearer_data_input['agreement_no']."-".
          $job_type_cd
        );
        array_push($calum_list, "m_job_type_comb_hkey");
        array_push($values_list, "'".$m_job_type_comb_hkey."'");
        // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
        $m_section_comb_hkey = md5(
          $auth['corporate_id']."-".
          $wearer_data_input['agreement_no']."-".
          $wearer_data_input['section']
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
        //--着用者基本マスタトラン登録処理 ここまで--//
      }

      //--発注情報トラン登録--//
      $cnt = 1;
      if (!empty($item_list)) {
        if ($wearer_end_post['order_tran_flg'] == '1') {
          //ChromePhp::LOG("発注情報トランクリーン");
          $query_list = array();
          array_push($query_list, "order_req_no = '".$shin_order_req_no."'");
          $query = implode(' AND ', $query_list);

          $arg_str = "";
          $arg_str = "DELETE FROM ";
          $arg_str .= "t_order_tran";
          $arg_str .= " WHERE ";
          $arg_str .= $query;
          //ChromePhp::LOG($arg_str);
          $t_order_tran = new TOrderTran();
          $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
          $results_cnt = $result_obj["\0*\0_count"];
          //ChromePhp::LOG($results_cnt);
        }
        //ChromePhp::LOG("発注情報トラン登録");

        $calum_list = array();
        $values_list = array();

        // 発注依頼行No.生成
        $order_req_line_no = $cnt++;
        // 発注情報_統合ハッシュキー(企業ID、発注依頼No、発注依頼行No)
        $t_order_comb_hkey = md5(
          $auth['corporate_id']."-".
          $shin_order_req_no."-".
          $order_req_line_no
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
        // 発注状況区分(終了)
        array_push($calum_list, "order_sts_kbn");
        array_push($values_list, "'2'");
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
        array_push($values_list, "''");
        // 着用者コード
        array_push($calum_list, "werer_cd");
        array_push($values_list, "'".$wearer_end_post['werer_cd']."'");
        // 商品コード
        array_push($calum_list, "item_cd");
        array_push($values_list, "''");
        // 色コード
        array_push($calum_list, "color_cd");
        array_push($values_list, "''");
        // サイズコード
        array_push($calum_list, "size_cd");
        array_push($values_list, "''");
        // サイズコード2
        array_push($calum_list, "size_two_cd");
        array_push($values_list, "' '");
        // 倉庫コード
        array_push($calum_list, "whse_cd");
        array_push($values_list, "''");
        // 在庫USRコード
        array_push($calum_list, "stk_usr_cd");
        array_push($values_list, "' '");
        // 在庫USR支店コード
        array_push($calum_list, "stk_usr_brnch_cd");
        array_push($values_list, "' '");
        // 出荷先、出荷先支店コード
        if (!empty($wearer_data_input['shipment'])) {
          $shipment = explode(':', $wearer_data_input['shipment']);
          $ship_to_cd = $shipment[0];
          $ship_to_brnch_cd = $shipment[1];

          // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
          if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
            array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
            $query = implode(' AND ', $query_list);
            $arg_str = '';
            $arg_str .= 'SELECT ';
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
        } else {
          array_push($calum_list, "ship_to_cd");
          array_push($values_list, "''");
          array_push($calum_list, "ship_to_brnch_cd");
          array_push($values_list, "' '");
        }
        // 発注枚数
        array_push($calum_list, "order_qty");
        array_push($values_list, 0);
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
        // 客先社員番号
        if (!empty($wearer_data_input['member_no'])) {
          array_push($calum_list, "cster_emply_cd");
          array_push($values_list, "'".$wearer_data_input['member_no']."'");
        }
        // 着用者状況区分(その他（着用終了）)
        array_push($calum_list, "werer_sts_kbn");
        array_push($values_list, "'3'");
        // 異動日
        if (!empty($wearer_data_input['resfl_ymd'])) {
          array_push($calum_list, "resfl_ymd");
          array_push($values_list, "'".date("Ymd", strtotime($wearer_data_input['resfl_ymd']))."'");
        }
        // 送信区分
        array_push($calum_list, "snd_kbn");
        array_push($values_list, "'".$wearer_data_input['snd_kbn']."'");
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
        // 発注ステータス(未出荷)
        array_push($calum_list, "order_status");
        array_push($values_list, "'1'");
        // 理由区分
        array_push($calum_list, "order_reason_kbn");
        array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
          // 発注時レンタル部門コード
          array_push($calum_list, "order_rntl_sect_cd");
          array_push($values_list, "'".$wearer_data_input['section']."'");
        // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
        $m_item_comb_hkey = "1";
        array_push($calum_list, "m_item_comb_hkey");
        array_push($values_list, "'".$m_item_comb_hkey."'");
        // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
        $m_job_type_comb_hkey = md5(
          $auth['corporate_id']."-".
          $wearer_data_input['agreement_no']."-".
          $job_type_cd
        );
        array_push($calum_list, "m_job_type_comb_hkey");
        array_push($values_list, "'".$m_job_type_comb_hkey."'");
        // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
        $m_section_comb_hkey = md5(
          $auth['corporate_id']."-".
          $wearer_data_input['agreement_no']."-".
          $wearer_data_input['section']
        );
        array_push($calum_list, "m_section_comb_hkey");
        array_push($values_list, "'".$m_section_comb_hkey."'");
        // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
        $m_wearer_std_comb_hkey = md5(
          $auth['corporate_id']."-".
          $wearer_end_post["werer_cd"]."-".
          $wearer_data_input['agreement_no']."-".
          $wearer_data_input['section']."-".
          $job_type_cd
        );
        array_push($calum_list, "m_wearer_std_comb_hkey");
        array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
        // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
        $m_wearer_item_comb_hkey = "1";
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
        $results_cnt = $result_obj["\0*\0_count"];
        //ChromePhp::LOG($results_cnt);
      }

      //--返却予定情報トラン登録--//
      $cnt = 1;
      // 発注商品一覧内容登録
      if (!empty($item_list)) {
        //ChromePhp::LOG("返却予定情報トランクリーン");
        $query_list = array();
        array_push($query_list, "order_req_no = '".$shin_order_req_no."'");
        $query = implode(' AND ', $query_list);
        $arg_str = "";
        $arg_str = "DELETE FROM ";
        $arg_str .= "t_returned_plan_info_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        //ChromePhp::LOG($arg_str);
        $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
        $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
        $results_cnt = $result_obj["\0*\0_count"];
        //ChromePhp::LOG($results_cnt);
        //ChromePhp::LOG("返却予定情報トラン登録");
        foreach ($item_list as $item_map) {
          if ($item_map["individual_flg"] == true && !empty($item_map["individual_data"])) {
            // ※個体管理番号単位での登録の場合
            foreach ($item_map["individual_data"] as $individual_data) {
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
              array_push($values_list, "'".$item_map['item_cd']."'");
              // 色コード
              array_push($calum_list, "color_cd");
              array_push($values_list, "'".$item_map['color_cd']."'");
              // サイズコード
              array_push($calum_list, "size_cd");
              array_push($values_list, "'".$item_map['size_cd']."'");
              // 個体管理番号
              array_push($calum_list, "individual_ctrl_no");
              array_push($values_list, "'".$individual_data['individual_ctrl_no']."'");
              // 着用者コード
              array_push($calum_list, "werer_cd");
              array_push($values_list, "'".$wearer_end_post['werer_cd']."'");
              // 客先社員番号
              if (isset($wearer_data_input['member_no'])) {
                array_push($calum_list, "cster_emply_cd");
                array_push($values_list, "'".$wearer_data_input['member_no']."'");
              }
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
              array_push($values_list, "'".date('Y-m-d H:i:s', time())."'");
              // 返却日
              array_push($calum_list, "return_date");
              array_push($values_list, "'".date('Y-m-d H:i:s', time())."'");
              // 返却ステータス(未返却)
              array_push($calum_list, "return_status");
              array_push($values_list, "'1'");
              // 発注状況区分(終了)
              array_push($calum_list, "order_sts_kbn");
              array_push($values_list, "'2'");
              // 返却予定数
              array_push($calum_list, "return_plan_qty");
              array_push($values_list, "'1'");
              // 返却数
              array_push($calum_list, "return_qty");
              array_push($values_list, "'0'");
              // 送信区分
              array_push($calum_list, "snd_kbn");
              array_push($values_list, "'".$wearer_data_input['snd_kbn']."'");
              // 理由区分
              array_push($calum_list, "order_reason_kbn");
              array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
              // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
              $m_section_comb_hkey = md5(
                $auth['corporate_id']."-".
                $wearer_data_input['agreement_no']."-".
                $wearer_data_input['section']
              );
              array_push($calum_list, "m_section_comb_hkey");
              array_push($values_list, "'".$m_section_comb_hkey."'");
              // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
              $m_item_comb_hkey = md5(
                $auth['corporate_id']."-".
                $item_map['item_cd']."-".
                $item_map['color_cd']."-".
                $item_map['size_cd']
              );
              array_push($calum_list, "m_item_comb_hkey");
              array_push($values_list, "'".$m_item_comb_hkey."'");
              $calum_query = implode(',', $calum_list);
              $values_query = implode(',', $values_list);

              $arg_str = "";
              $arg_str .= "INSERT INTO t_returned_plan_info_tran";
              $arg_str .= "(".$calum_query.")";
              $arg_str .= " VALUES ";
              $arg_str .= "(".$values_query.")";
              //ChromePhp::LOG($arg_str);
              $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
              $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
              $results_cnt = $result_obj["\0*\0_count"];
              //ChromePhp::LOG($results_cnt);
            }
          } else if ($item_map["individual_flg"] == false && !empty($item_map["return_num"])) {
            // ※商品単位での登録の場合
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
            array_push($values_list, "'".$item_map['item_cd']."'");
            // 色コード
            array_push($calum_list, "color_cd");
            array_push($values_list, "'".$item_map['color_cd']."'");
            // サイズコード
            array_push($calum_list, "size_cd");
            array_push($values_list, "'".$item_map['size_cd']."'");
            // 着用者コード
            array_push($calum_list, "werer_cd");
            array_push($values_list, "'".$wearer_end_post['werer_cd']."'");
            // 客先社員番号
            if (isset($wearer_data_input['member_no'])) {
              array_push($calum_list, "cster_emply_cd");
              array_push($values_list, "'".$wearer_data_input['member_no']."'");
            }
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
            array_push($values_list, "'".date('Y-m-d H:i:s', time())."'");
            // 返却日
            array_push($calum_list, "return_date");
            array_push($values_list, "'".date('Y-m-d H:i:s', time())."'");
            // 返却ステータス(未返却)
            array_push($calum_list, "return_status");
            array_push($values_list, "'1'");
            // 発注状況区分(終了)
            array_push($calum_list, "order_sts_kbn");
            array_push($values_list, "'2'");
            // 返却予定数
            array_push($calum_list, "return_plan_qty");
            array_push($values_list, "'".$item_map['return_num']."'");
            // 返却数
            array_push($calum_list, "return_qty");
            array_push($values_list, "'0'");
            // 送信区分
            array_push($calum_list, "snd_kbn");
            array_push($values_list, "'".$wearer_data_input['snd_kbn']."'");
            // 理由区分
            array_push($calum_list, "order_reason_kbn");
            array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");

            //個体管理番号
            $query_list = array();
            array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_data_input['agreement_no']."'");
            array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_end_post['werer_cd']."'");
            array_push($query_list, "t_delivery_goods_state_details.item_cd = '".$item_map['item_cd']."'");
            array_push($query_list, "t_delivery_goods_state_details.color_cd = '".$item_map['color_cd']."'");
            array_push($query_list, "t_delivery_goods_state_details.size_cd = '".$item_map['size_cd']."'");
            array_push($query_list, "t_delivery_goods_state_details.rtn_ok_flg = '1'");
            array_push($query_list, "t_delivery_goods_state_details.receipt_status = '2'");
            $query = implode(' AND ', $query_list);

            $arg_str = "";
            $arg_str = "SELECT ";
            $arg_str .= " * ";
            $arg_str .= " FROM ";
            $arg_str .= "t_delivery_goods_state_details";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $arg_str .= " ORDER BY individual_ctrl_no ASC";
            $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
            $t_delivery_goods_state_details_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
            $result_obj = (array)$t_delivery_goods_state_details_results;
            $results_cnt = $result_obj["\0*\0_count"];

            foreach ($t_delivery_goods_state_details_results as $t_delivery_goods_state_details_result) {
                $list["individual_ctrl_no"] = $t_delivery_goods_state_details_result->individual_ctrl_no;
            }

            array_push($calum_list, "individual_ctrl_no");
            array_push($values_list, "'".$list["individual_ctrl_no"]."'");

            // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
            $m_section_comb_hkey = md5(
              $auth['corporate_id']."-".
              $wearer_data_input['agreement_no']."-".
              $wearer_data_input['section']
            );
            array_push($calum_list, "m_section_comb_hkey");
            array_push($values_list, "'".$m_section_comb_hkey."'");
            // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
            $m_item_comb_hkey = md5(
              $auth['corporate_id']."-".
              $item_map['item_cd']."-".
              $item_map['color_cd']."-".
              $item_map['size_cd']
            );
            array_push($calum_list, "m_item_comb_hkey");
            array_push($values_list, "'".$m_item_comb_hkey."'");
            $calum_query = implode(',', $calum_list);
            $values_query = implode(',', $values_list);

            $arg_str = "";
            $arg_str = "INSERT INTO t_returned_plan_info_tran";
            $arg_str .= "(".$calum_query.")";
            $arg_str .= " VALUES ";
            $arg_str .= "(".$values_query.")";
            //ChromePhp::LOG($arg_str);
            $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
            $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
            $results_cnt = $result_obj["\0*\0_count"];
            //ChromePhp::LOG($results_cnt);
          }
        }
      }

      $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query('commit'));
    } catch (Exception $e) {
      $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query('rollback'));

      //ChromePhp::LOG($e);
      $json_list["error_code"] = "1";
      $error_msg = "入力登録処理において、データ更新エラーが発生しました。";
      array_push($json_list["error_msg"], $error_msg);
      echo json_encode($json_list);
      return;
    }

    // 返却伝票用パラメータ
    $json_list['param'] = '';
    $json_list['param'] .= $wearer_end_post['rntl_cont_no'].':';
    $json_list['param'] .= $shin_order_req_no;

    echo json_encode($json_list);
  }
});



/**
 * 発注入力
 * 発注取消処理
 */
$app->post('/wearer_end_order_delete', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション
    $auth = $app->session->get('auth');

    // 前画面セッション
    $wearer_end_post = $app->session->get("wearer_end_post");

    // フロントパラメータ
    if (!empty($params['data'])) {
        $cond = $params['data'];
        $wearer_end_post = array();
        $wearer_end_post['order_req_no'] = $cond['order_req_no'];
        $wearer_end_post['werer_cd'] = $cond['werer_cd'];
        $wearer_end_post['rntl_cont_no'] = $cond['rntl_cont_no'];
        $wearer_end_post['rntl_sect_cd'] = $cond['rntl_sect_cd'];
        $wearer_end_post['job_type_cd'] = $cond['job_type_cd'];
        $wearer_end_post['return_req_no'] = $cond['order_req_no'];
    }

    $query_list = array();
    $list = array();
    $json_list = array();
    $error_list = array();

    // DB更新エラーコード 0:正常 1:更新エラー
    $json_list["error_code"] = "0";
    $json_list['error_msg'] = array();

    // トランザクション開始
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
    try {
        //--着用者基本マスタトラン削除--//
        // 発注情報トランを参照
        $query_list = array();
        array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
        if (!empty($cond["order_req_no"])) {
          array_push($query_list, "t_order_tran.order_req_no <> '".$cond['order_req_no']."'");
        } else {
          array_push($query_list, "t_order_tran.order_req_no <> '".$wearer_end_post['order_req_no']."'");
        }
        if (!empty($cond["werer_cd"])) {
          array_push($query_list, "t_order_tran.werer_cd = '".$cond['werer_cd']."'");
        } else {
          array_push($query_list, "t_order_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
        }
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "*";
        $arg_str .= " FROM ";
        $arg_str .= "t_order_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $t_order_tran = new TOrderTran();
        $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];

        // 上記発注情報トラン件数が0の場合に着用者基本マスタトランのデータを削除する
        if (empty($results_cnt)) {
            $query_list = array();
            array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
            array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
            array_push($query_list, "m_wearer_std_tran.order_sts_kbn <> '6'");
            $query = implode(' AND ', $query_list);
            $arg_str = "";
            $arg_str = "DELETE FROM ";
            $arg_str .= "m_wearer_std_tran";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $m_wearer_std_tran = new MWearerStdTran();
            $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
            $result_obj = (array)$results;
            $results_cnt = $result_obj["\0*\0_count"];
        }

        //--発注情報トラン削除--//
        $query_list = array();
        array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
        array_push($query_list, "t_order_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
        array_push($query_list, "t_order_tran.order_sts_kbn = '2'");
        $reason_kbn = array();
        array_push($reason_kbn, '05');
        array_push($reason_kbn, '06');
        array_push($reason_kbn, '08');
        array_push($reason_kbn, '20');
        if(!empty($reason_kbn)) {
            $reason_kbn_str = implode("','",$reason_kbn);
            $reason_kbn_query = "t_order_tran.order_reason_kbn IN ('".$reason_kbn_str."')";
            array_push($query_list, $reason_kbn_query);
        }
        $query = implode(' AND ', $query_list);
        $arg_str = "";
        $arg_str .= "DELETE FROM ";
        $arg_str .= "t_order_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $t_order_tran = new TOrderTran();
        $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];

        //--返却予定情報トラン削除--//
        $query_list = array();
        array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
        array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
        array_push($query_list, "t_returned_plan_info_tran.order_sts_kbn = '2'");
        $reason_kbn = array();
        array_push($reason_kbn, '05');
        array_push($reason_kbn, '06');
        array_push($reason_kbn, '08');
        array_push($reason_kbn, '20');
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
        $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
        $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];

        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
    } catch (Exception $e) {
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));

        $json_list["error_code"] = "1";
        $error_msg = "発注取消において、データ更新エラーが発生しました。";
        array_push($json_list["error_msg"], $error_msg);
        echo json_encode($json_list);
        return;
    }

    echo json_encode($json_list);
});
