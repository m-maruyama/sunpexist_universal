<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * 発注入力（不要品返却）
 * 入力項目：初期値情報、前画面セッション取得
 *
 */
$app->post('/wearer_return/info', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
  $auth = $app->session->get("auth");
  //ChromePhp::LOG($auth);

  // 前画面セッション取得
  $wearer_other_post = $app->session->get("wearer_other_post");
  //ChromePhp::LOG($wearer_other_post);

  $json_list = array();

  //--着用者入力項目情報--//
  $all_list = array();
  $list = array();
  $json_list['wearer_info'] = "";

  // 発注情報トラン参照
  $query_list = array();
  array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
  array_push($query_list, "t_order_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
  // 発注状況区分(終了)
  array_push($query_list,"t_order_tran.order_sts_kbn = '2'");
  // 理由区分(不要品返却)
  array_push($query_list,"(t_order_tran.order_reason_kbn = '28' OR t_order_tran.order_reason_kbn = '07')");
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
  // コメント欄
  $list["comment"] = "";
  if (!empty($results_cnt)) {
    foreach ($results as $result) {
      $list["comment"] = $result->memo;
    }
  }

  // 着用者基本マスタトラン参照
  $query_list = array();
  array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
  array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
  array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
  array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
  array_push($query_list, "m_wearer_std_tran.order_sts_kbn = '2'");
  array_push($query_list,"(t_order_tran.order_reason_kbn = '28' OR t_order_tran.order_reason_kbn = '07')");
  $query = implode(' AND ', $query_list);

  $arg_str = "";
  $arg_str = "SELECT ";
  $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
  $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
  $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana";
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
    // 着用者基本マスタトラン（不要品返却）有り
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
      // 着用者名（カナ）
      $list['werer_name_kana'] = $result->as_werer_name_kana;
    }

    array_push($all_list, $list);
  }

  // 上記参照のトラン情報がない場合、着用者基本マスタ情報を参照する
  if (empty($all_list)) {
    // 発注区分=着用者編集　参照
    $query_list = array();
    array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
    array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
    array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
    array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
    //発注状況区分　着用者編集
    array_push($query_list, "m_wearer_std_tran.order_sts_kbn = '6'");
    $query = implode(' AND ', $query_list);
    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana,";
    $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn";
    $arg_str .= " FROM ";
    $arg_str .= "m_wearer_std_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY m_wearer_std_tran.upd_date DESC";
    $m_weare_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if ($results_cnt > 0) {
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
      foreach ($results as $result) {
        // 社員コード
        $list['cster_emply_cd'] = $result->as_cster_emply_cd;
        // 着用者名
        $list['werer_name'] = $result->as_werer_name;
        // 着用者名（カナ）
        $list['werer_name_kana'] = $result->as_werer_name_kana;
        // 性別
        $wearer_other_post['sex_kbn'] = $result->as_sex_kbn;
      }

      array_push($all_list, $list);
    } else {
      // 着用者基本マスタトラン（着用者編集）無し
      $json_list['tran_flg'] = "0";

      $query_list = array();
      array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "m_wearer_std.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
      array_push($query_list, "m_wearer_std.werer_cd = '".$wearer_other_post['werer_cd']."'");
      $query = implode(' AND ', $query_list);

      $arg_str = "";
      $arg_str = "SELECT ";
      $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
      $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
      $arg_str .= "m_wearer_std.werer_name_kana as as_werer_name_kana";
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
          // 着用者名（カナ）
          $list['werer_name_kana'] = $result->as_werer_name_kana;
        }

        array_push($all_list, $list);
      }
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
  array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
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
      if ($list['sex_kbn'] == $wearer_other_post['sex_kbn']) {
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
  array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
  array_push($query_list, "rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
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
  array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
  array_push($query_list, "job_type_cd = '".$wearer_other_post['job_type_cd']."'");
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
      //発注管理単位
      $list['order_control_unit'] = $result->order_control_unit;
      //order_control_unit = $result->order_control_unit;
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
  $list['ship_to_cd'] = $wearer_other_post['ship_to_cd'];
  $list['ship_to_brnch_cd'] = $wearer_other_post['ship_to_brnch_cd'];
  array_push($all_list, $list);
  $json_list['shipment_list'] = $all_list;
  //ChromePhp::LOG($json_list['shipment_list']);

  //--発注情報トラン・返却予定情報トラン内、「不要品返却」情報の有無確認--//
  //※発注情報トラン参照
  $query_list = array();
  array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
  array_push($query_list, "t_order_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
  array_push($query_list, "t_order_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
  array_push($query_list, "t_order_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
  // 発注状況区分(終了)
  array_push($query_list,"t_order_tran.order_sts_kbn = '2'");
  // 理由区分(不要品返却)
  array_push($query_list,"(t_order_tran.order_reason_kbn = '28' OR t_order_tran.order_reason_kbn = '07')");
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
  array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
  array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
  array_push($query_list, "t_returned_plan_info_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
  array_push($query_list, "t_returned_plan_info_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
  // 発注状況区分(終了)
  array_push($query_list,"t_returned_plan_info_tran.order_sts_kbn = '2'");
  // 理由区分(不要品返却)
  array_push($query_list,"(t_returned_plan_info_tran.order_reason_kbn = '28' OR t_returned_plan_info_tran.order_reason_kbn = '07')");
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
  $json_list['rntl_cont_no'] = $wearer_other_post["rntl_cont_no"];
  // 部門コード
  $json_list['rntl_sect_cd'] = $wearer_other_post["rntl_sect_cd"];
  // 貸与パターン
  $json_list['job_type_cd'] = $wearer_other_post["job_type_cd"];
  // 着用者コード
  $json_list['werer_cd'] = $wearer_other_post["werer_cd"];
  // 着用者基本マスタトランフラグ
  $json_list['wearer_tran_flg'] = $wearer_other_post["wearer_tran_flg"];

  echo json_encode($json_list);
});

/**
 * 発注入力（不要品返却）
 * 入力項目：発注商品一覧
 */
 $app->post('/wearer_return/list', function ()use($app){
   $params = json_decode(file_get_contents("php://input"), true);

   // アカウントセッション取得
   $auth = $app->session->get("auth");
   //ChromePhp::LOG($auth);

   // 前画面セッション取得
   $wearer_other_post = $app->session->get("wearer_other_post");

   // フロントパラメータ取得
   $cond = $params['data'];
   //ChromePhp::LOG("フロント側パラメータ");
   //ChromePhp::LOG($cond);

   $json_list = array();
   $all_list = array();

   // 返却予定情報トラン(不要品返却発注履歴)確認
   $tran_list = array();
   $list = array();
   $json_list["sum_return_num"] = '0';
   $query_list = array();
   array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
   array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
   array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
   array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
   // 発注状況区分
   array_push($query_list, "t_returned_plan_info_tran.order_sts_kbn = '2'");
   // 理由区分
   array_push($query_list,"(t_returned_plan_info_tran.order_reason_kbn = '28' OR t_returned_plan_info_tran.order_reason_kbn = '07')");
   $query = implode(' AND ', $query_list);

   $arg_str = "";
   $arg_str = "SELECT ";
   $arg_str .= " * ";
   $arg_str .= " FROM ";
   $arg_str .= "(SELECT distinct on (t_returned_plan_info_tran.item_cd, t_returned_plan_info_tran.color_cd, t_returned_plan_info_tran.size_cd) ";
   $arg_str .= "t_returned_plan_info_tran.item_cd as as_order_item_cd,";
   $arg_str .= "t_returned_plan_info_tran.color_cd as as_order_color_cd,";
   $arg_str .= "t_returned_plan_info_tran.size_cd as as_order_size_cd,";
   $arg_str .= "t_returned_plan_info_tran.return_plan_qty as as_return_plan_qty";
   $arg_str .= " FROM ";
   $arg_str .= "m_wearer_std_tran INNER JOIN t_returned_plan_info_tran";
   $arg_str .= " ON (m_wearer_std_tran.corporate_id = t_returned_plan_info_tran.corporate_id";
   $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = t_returned_plan_info_tran.rntl_cont_no";
   $arg_str .= " AND m_wearer_std_tran.werer_cd = t_returned_plan_info_tran.werer_cd";
   $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = t_returned_plan_info_tran.rntl_sect_cd";
   $arg_str .= " AND m_wearer_std_tran.job_type_cd = t_returned_plan_info_tran.job_type_cd)";
   $arg_str .= " WHERE ";
   $arg_str .= $query;
   $arg_str .= ") as distinct_table";
   $arg_str .= " ORDER BY as_order_item_cd ASC, as_order_color_cd ASC";
   $m_wearer_std_tran = new MWearerStdTran();
   $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG("発注情報トラン商品一覧件数");
   if (!empty($results_cnt)) {
     // 発注情報トランに情報が存在する場合、こちらで商品一覧生成
     $paginator_model = new PaginatorModel(
         array(
             "data"  => $results,
             "limit" => $results_cnt,
             "page" => 1
         )
     );
     $paginator = $paginator_model->getPaginate();
     $results = $paginator->items;
     //ChromePhp::LOG("発注情報トラン商品一覧仮リスト");
     //ChromePhp::LOG($results);
     $arr_num = 0;
     $list_cnt = 1;
     foreach ($results as $result) {
       // 商品コード
       $list["item_cd"] = $result->as_order_item_cd;
       // 色コード
       $list["color_cd"] = $result->as_order_color_cd;
       // サイズコード
       $list["size_cd"] = $result->as_order_size_cd;
       // 返却予定数
       $list["return_plan_qty"] = $result->as_return_plan_qty;

       array_push($tran_list, $list);
     }

     // 商品情報取得
     $list = array();
     $query_list = array();
     array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
     array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_other_post['werer_cd']."'");
     array_push($query_list, "t_delivery_goods_state_details.rtn_ok_flg = '1'");
     array_push($query_list, "t_delivery_goods_state_details.receipt_status = '2'");
       //自分の貸与パターンを絞り込み
       $query_list[] = "m_input_item.job_type_cd = '".$wearer_other_post['job_type_cd']."'";

       $query = implode(' AND ', $query_list);

     $arg_str = "";
     $arg_str = "SELECT ";
     $arg_str .= " * ";
     $arg_str .= " FROM ";
     $arg_str .= "(SELECT distinct on (m_item.item_cd, m_item.color_cd, m_item.size_cd) ";
     $arg_str .= "t_delivery_goods_state_details.quantity as as_quantity,";
     $arg_str .= "t_delivery_goods_state_details.return_plan__qty as as_return_plan_qty,";
     $arg_str .= "t_delivery_goods_state_details.returned_qty as as_returned_qty,";
     $arg_str .= "m_item.item_cd as as_item_cd,";
     $arg_str .= "m_item.color_cd as as_color_cd,";
     $arg_str .= "m_item.size_cd as as_size_cd,";
     $arg_str .= "m_item.item_name as as_item_name,";
     $arg_str .= "m_input_item.job_type_item_cd as as_job_type_item_cd,";
     $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
     $arg_str .= "m_input_item.std_input_qty as as_std_input_qty";
     $arg_str .= " FROM ";
     $arg_str .= "t_delivery_goods_state_details INNER JOIN m_item";
     $arg_str .= " ON (t_delivery_goods_state_details.corporate_id = m_item.corporate_id";
     $arg_str .= " AND t_delivery_goods_state_details.item_cd = m_item.item_cd";
     $arg_str .= " AND t_delivery_goods_state_details.color_cd = m_item.color_cd";
     $arg_str .= " AND t_delivery_goods_state_details.size_cd = m_item.size_cd)";
     $arg_str .= " INNER JOIN m_input_item";
     $arg_str .= " ON (m_item.corporate_id = m_item.corporate_id";
     $arg_str .= " AND m_item.item_cd = m_input_item.item_cd";
     $arg_str .= " AND m_item.color_cd = m_input_item.color_cd)";
     $arg_str .= " WHERE ";
     $arg_str .= $query;
     $arg_str .= ") as distinct_table";
     $arg_str .= " ORDER BY as_item_cd ASC, as_color_cd ASC";
     $m_input_item = new MInputItem();
     $item_results = new Resultset(null, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
     $result_obj = (array)$item_results;
     $results_cnt = $result_obj["\0*\0_count"];
     if (!empty($results_cnt)) {
       $paginator_model = new PaginatorModel(
           array(
               "data"  => $item_results,
               "limit" => $results_cnt,
               "page" => 1
           )
       );
       $paginator = $paginator_model->getPaginate();
       $item_results = $paginator->items;
       //ChromePhp::LOG("発注情報トラン商品一覧仮リスト");
       //ChromePhp::LOG($results);
       $sum_return_cnt = 0;
       foreach ($item_results as $item_result) {
         // アイテム
         $list["item_name"] = $item_result->as_item_name;
         // 数量
         $list["quantity"] = $item_result->as_quantity;
         // 返却予定数
         $list["return_plan_qty"] = $item_result->as_return_plan_qty;
         // 返却済数
         $list["returned_qty"] = $item_result->as_returned_qty;
         // 商品単位の返却可能枚数(所持枚数)
         $list["possible_num"] = $list["quantity"] - $list["return_plan_qty"];

         // 商品コード
         $list["item_cd"] = $item_result->as_item_cd;
         // 色コード
         $list["color_cd"] = $item_result->as_color_cd;
         // 商品-色
         $list["item_and_color"] = $list["item_cd"]."-".$list["color_cd"];
         // 商品名
         $list["input_item_name"] = $item_result->as_input_item_name;
         // サイズ
         $list["size_cd"] = $item_result->as_size_cd;
         //返却枚数 初期化
         $list["return_num"] = 0;

         // 対象、個体管理番号
         if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "1" || individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "0") {
           // 返却予定情報トラン参照（個体管理番号取得）
           $individual_list = array();
           $query_list = array();
           array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
           array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
           array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
           array_push($query_list, "t_returned_plan_info_tran.item_cd = '".$list["item_cd"]."'");
           array_push($query_list, "t_returned_plan_info_tran.color_cd = '".$list["color_cd"]."'");
           array_push($query_list, "t_returned_plan_info_tran.size_cd = '".$list["size_cd"]."'");
           // 発注状況区分
           array_push($query_list, "t_returned_plan_info_tran.order_sts_kbn = '2'");
           // 理由区分
           array_push($query_list,"(t_returned_plan_info_tran.order_reason_kbn = '28' OR t_returned_plan_info_tran.order_reason_kbn = '07')");
           $query = implode(' AND ', $query_list);

           $arg_str = "";
           $arg_str = "SELECT ";
           $arg_str .= "individual_ctrl_no,";
           $arg_str .= "return_plan_qty,";
           $arg_str .= "item_cd";
           $arg_str .= " FROM ";
           $arg_str .= "t_returned_plan_info_tran";
           $arg_str .= " WHERE ";
           $arg_str .= $query;
           $arg_str .= " ORDER BY individual_ctrl_no ASC";
           $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
           $t_returned_plan_info_tran_results = new Resultset(null, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
           $result_obj = (array)$t_returned_plan_info_tran_results;
           $results_cnt = $result_obj["\0*\0_count"];
           if (!empty($results_cnt)) {
             $paginator_model = new PaginatorModel(
                 array(
                     "data"  => $t_returned_plan_info_tran_results,
                     "limit" => $results_cnt,
                     "page" => 1
                 )
             );
             $paginator = $paginator_model->getPaginate();
             $t_returned_plan_info_tran_results = $paginator->items;
             foreach ($t_returned_plan_info_tran_results as $t_returned_plan_info_tran_result) {
               array_push($individual_list, $t_returned_plan_info_tran_result->individual_ctrl_no);
               if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "0") {
                 $list["return_num"] = $t_returned_plan_info_tran_result->return_plan_qty;
               }
             }
           }
           //個あり、トランあり、返却数、総返却数設定
           if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "1") {
             //各商品の返却数
             $list["return_num"] = $results_cnt;
             //トータルの総返却数
             $sum_return_cnt += intval($list["return_num"]);
           }
           //個なし、トランあり、返却数、総返却数設定
           if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "0") {
             //トータルの総返却数
             $sum_return_cnt += intval($list["return_num"]);
           }

           // 納品状況明細情報参照
           $list["individual_chk"] = array();
           $element = array();
           $list["individual_ctrl_no"] = array();
           $query_list = array();
           array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
           array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
           array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_other_post['werer_cd']."'");
           array_push($query_list, "t_delivery_goods_state_details.item_cd = '".$list["item_cd"]."'");
           array_push($query_list, "t_delivery_goods_state_details.color_cd = '".$list["color_cd"]."'");
           array_push($query_list, "t_delivery_goods_state_details.size_cd = '".$list["size_cd"]."'");
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
           //ChromePhp::LOG($arg_str);
           $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
           $t_delivery_goods_state_details_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
           $result_obj = (array)$t_delivery_goods_state_details_results;
           $results_cnt = $result_obj["\0*\0_count"];
           //ChromePhp::LOG($results_cnt);
           $paginator_model = new PaginatorModel(
               array(
                   "data"  => $t_delivery_goods_state_details_results,
                   "limit" => $results_cnt,
                   "page" => 1
               )
           );
           $paginator = $paginator_model->getPaginate();
           $t_delivery_goods_state_details_results = $paginator->items;
           $i = 0;
           $item_add_flg = true;
           $t_rtn_tran_count = 0;
           $quantity = 0;
           $return_plan__qty = 0;

           foreach ($t_delivery_goods_state_details_results as $items) {
             //個なしの数量
             if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "0") {
               $quantity += $items->quantity;
               $return_plan__qty += $items->return_plan__qty;
             }
             //個体管理番号他のその他交換、サイズ交換、発注チェック
             if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "1") {
               $query_list = array();
               array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
               array_push($query_list, "rntl_cont_no = '" . $wearer_other_post['rntl_cont_no'] . "'");
               array_push($query_list, "werer_cd = '" . $wearer_other_post['werer_cd'] . "'");
               //商品情報
               array_push($query_list, "item_cd = '" . $list['item_cd'] . "'");
               array_push($query_list, "color_cd = '" . $list['color_cd'] . "'");
               array_push($query_list, "individual_ctrl_no = '" . $items->individual_ctrl_no . "'");
               //発注状況区分
               array_push($query_list, "(order_sts_kbn = '3' OR order_sts_kbn = '4')");//サイズ交換のトラン
               //sql文字列を' AND 'で結合
               $query = implode(' AND ', $query_list);
               //--- クエリー実行・取得 ---//
               $t_rtn_tran_count = TReturnedPlanInfoTran::find(array(
               'conditions' => $query
               ))->count();
             }
             //個体管理番号ごとの数量-返却予定数計算
             $individual_possible_qty = $items->quantity - $items->return_plan__qty;
             if($individual_possible_qty > 0){
             if($t_rtn_tran_count == 0){
               // name属性用カウント値
              // $list["arr_num"] = $arr_num++;
             // 返却予定情報トランの個体管理番号があるか確認
             $element["checked"] = "";
             if (!empty($individual_list)) {
               foreach ($individual_list as $tran_individual_ctrl_no) {
                 if ($items->individual_ctrl_no == $tran_individual_ctrl_no) {
                   $element["checked"] = "checked";
                 }
               }
             }
             array_push($list["individual_ctrl_no"], $items->individual_ctrl_no);
             $element["name_no"] = $arr_num;
             $element["individual_ctrl_no"] = $items->individual_ctrl_no;
             if ($results_cnt - 1 !== $i) {
               $element["br"] = "<br/>";
             } else {
               $element["br"] = "";
             }
             array_push($list["individual_chk"], $element);
             }else{
               $item_add_flg = false;
             }
             }
           }
           // 個体管理番号数
           $list["individual_cnt"] = count($list["individual_ctrl_no"]);
           // 個体管理番号(表示用)
           $list["individual_ctrl_no"] = implode("<br/>", $list["individual_ctrl_no"]);
         }
         // 返却枚数
         // ※返却予定情報トランに存在する場合はこちらを設定
         /*
         $list["return_num"] = "0";
         foreach ($tran_list as $tran_map) {
           if (
             $list["item_cd"] == $tran_map["item_cd"] &&
             $list["color_cd"] == $tran_map["color_cd"] &&
             $list["size_cd"] == $tran_map["size_cd"]
           )
           {
             $list["return_num"] = $tran_map["return_plan_qty"];
               $sum_return_cnt += intval($tran_map["return_plan_qty"]);
           }
         }
         */
         $json_list["sum_return_num"] = $sum_return_cnt;
         // 個体管理番号表示フラグ
         if ($auth["individual_flg"] == "1") {
           $list["individual_flg"] = true;
           //その他交換、サイズ交換があった時用の個数０フラグ
           if($list["individual_cnt"] == 0){
             $list['item_add_flg'] = false;
           }else{
             $list['item_add_flg'] = true;
             $list["arr_num"] = $arr_num++;
           }
           // 所持枚数
           $list["possible_num"] = $list["individual_cnt"];

         } else {
           $list["arr_num"] = $arr_num++;
           $list["individual_flg"] = false;
           // 所持枚数 数量 - 返却予定数のサマリ
           $list["possible_num"] = $quantity - $return_plan__qty;

         }
         //--その他の必要hiddenパラメータ--//
         // 部門コード
         $list["rntl_sect_cd"] = $wearer_other_post["rntl_sect_cd"];
         // 職種コード
         $list["job_type_cd"] = $wearer_other_post['job_type_cd'];
         // 職種アイテムコード
         $list["job_type_item_cd"] = $item_result->as_job_type_item_cd;

         //その他交換、サイズ交換、発注のチェック 個なし
         if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "0") {
           $query_list = array();
           array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
           array_push($query_list, "rntl_cont_no = '" . $wearer_other_post['rntl_cont_no'] . "'");
           array_push($query_list, "werer_cd = '" . $wearer_other_post['werer_cd'] . "'");
           //商品情報
           array_push($query_list, "item_cd = '" . $list['item_cd'] . "'");
           array_push($query_list, "color_cd = '" . $list['color_cd'] . "'");
           //発注状況区分
           array_push($query_list, "(order_sts_kbn = '3' OR order_sts_kbn = '4')");//サイズ交換のトラン

           //sql文字列を' AND 'で結合
           $query = implode(' AND ', $query_list);
           //--- クエリー実行・取得 ---//
           $t_order_tran_items = TOrderTran::find(array(
           'conditions' => $query
           ));
           $t_order_tran_count = $t_order_tran_items->count();

           //個なしのサイズ交換、その他交換の発注があった場合の計算
           if($t_order_tran_count > 0) {
             foreach ($t_order_tran_items as $t_order_tran_item) {
               // 所持枚数
               $list["possible_num"] = $list["possible_num"] - $t_order_tran_item->order_qty;
             }
             //ChromePhp::log($list["possible_num"]);
             if ($list["possible_num"] < 1) {
               $list['item_add_flg'] = false;
             }
           }else{
             //個なしのサイズ交換、その他交換の発注がない場合の計算 所持枚数が０以上だったら表示
             if($list["possible_num"] > 0){
               $list['item_add_flg'] = true;
             }else{
               $list['item_add_flg'] = false;
             }
           }
         }
          //その他交換、サイズ交換の発注が入ってないこと。
         if($list['item_add_flg']) {
           // No
           $list["list_no"] = $list_cnt++;
           array_push($all_list, $list);
         }
       }
     }
   } else {
     $json_list["sum_return_num"] = '0';
     // 発注情報トランに情報が存在しない場合、こちらで商品一覧生成
     $all_list = array();
     $list = array();
     $query_list = array();
     array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
     array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_other_post['werer_cd']."'");
     array_push($query_list, "t_delivery_goods_state_details.rtn_ok_flg = '1'");
     array_push($query_list, "t_delivery_goods_state_details.receipt_status = '2'");
     //自分の貸与パターンを絞り込み
     $query_list[] = "m_input_item.job_type_cd = '".$wearer_other_post['job_type_cd']."'";
     $query = implode(' AND ', $query_list);

     $arg_str = "";
     $arg_str = "SELECT ";
     $arg_str .= " * ";
     $arg_str .= " FROM ";
     $arg_str .= "(SELECT distinct on (m_item.item_cd, m_item.color_cd, m_item.size_cd) ";
     $arg_str .= "t_delivery_goods_state_details.quantity as as_quantity,";
     $arg_str .= "t_delivery_goods_state_details.return_plan__qty as as_return_plan_qty,";
     $arg_str .= "t_delivery_goods_state_details.returned_qty as as_returned_qty,";
     $arg_str .= "m_item.item_cd as as_item_cd,";
     $arg_str .= "m_item.color_cd as as_color_cd,";
     $arg_str .= "m_item.size_cd as as_size_cd,";
     $arg_str .= "m_item.item_name as as_item_name,";
     $arg_str .= "m_input_item.job_type_cd as as_job_type_cd,";
     $arg_str .= "m_input_item.job_type_item_cd as as_job_type_item_cd,";
     $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
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
     $arg_str .= " ORDER BY as_item_cd ASC, as_color_cd ASC";
     $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
     $results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
     $result_obj = (array)$results;
     $results_cnt = $result_obj["\0*\0_count"];
     //ChromePhp::LOG("通常商品一覧件数");
     //ChromePhp::LOG($results_cnt);
     if (!empty($results_cnt)) {
       // 発注情報トランに情報が存在する場合、こちらで商品一覧生成
       $paginator_model = new PaginatorModel(
           array(
               "data"  => $results,
               "limit" => $results_cnt,
               "page" => 1
           )
       );
       $paginator = $paginator_model->getPaginate();
       $results = $paginator->items;
       //ChromePhp::LOG("発注情報トラン商品一覧仮リスト");
       //ChromePhp::LOG($results);
       $arr_num = 0;
       $list_cnt = 1;
       foreach ($results as $result) {


         // アイテム
         $list["item_name"] = $result->as_item_name;
         // 所持枚数
         $list["possible_num"] = $result->as_quantity - $result->as_return_plan_qty;
         // 数量
         $list["quantity"] = $result->as_quantity;
         // 返却予定数
         $list["return_plan_qty"] = $result->as_return_plan_qty;
         // 返却済数
         $list["returned_qty"] = $result->as_returned_qty;
         // 商品コード
         $list["item_cd"] = $result->as_item_cd;
         // 色コード
         $list["color_cd"] = $result->as_color_cd;
         // 商品-色
         $list["item_and_color"] = $list["item_cd"]."-".$list["color_cd"];
         // 商品名
         $list["input_item_name"] = $result->as_input_item_name;
         // サイズ
         $list["size_cd"] = $result->as_size_cd;
         // 対象、個体管理番号
         if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "1" || individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "0") {
           // 納品状況明細情報参照
           $list["individual_chk"] = array();
           $element = array();
           $list["individual_ctrl_no"] = array();
           $query_list = array();
           array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
           array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
           array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_other_post['werer_cd']."'");
           array_push($query_list, "t_delivery_goods_state_details.item_cd = '".$list["item_cd"]."'");
           array_push($query_list, "t_delivery_goods_state_details.color_cd = '".$list["color_cd"]."'");
           array_push($query_list, "t_delivery_goods_state_details.size_cd = '".$list["size_cd"]."'");
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
           //ChromePhp::LOG($results_cnt);
           $paginator_model = new PaginatorModel(
               array(
                   "data"  => $t_delivery_goods_state_details_results,
                   "limit" => $results_cnt,
                   "page" => 1
               )
           );
           $paginator = $paginator_model->getPaginate();
           $t_delivery_goods_state_details_results = $paginator->items;
           $i = 0;
           $t_rtn_tran_count = 0;
           $list['item_add_flg'] = true;
           $quantity = 0;
           $return_plan__qty = 0;

           foreach ($t_delivery_goods_state_details_results as $items) {
             //個なしの数量
             if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "0") {
                  $quantity += $items->quantity;
                  $return_plan__qty += $items->return_plan__qty;
             }
               //個体管理番号他のその他交換、サイズ交換、発注チェック
             if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "1") {
               $query_list = array();
               array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
               array_push($query_list, "rntl_cont_no = '" . $wearer_other_post['rntl_cont_no'] . "'");
               array_push($query_list, "werer_cd = '" . $wearer_other_post['werer_cd'] . "'");
               //商品情報
               array_push($query_list, "item_cd = '" . $list['item_cd'] . "'");
               array_push($query_list, "color_cd = '" . $list['color_cd'] . "'");
               array_push($query_list, "individual_ctrl_no = '" . $items->individual_ctrl_no . "'");
               //発注状況区分
               array_push($query_list, "(order_sts_kbn = '3' OR order_sts_kbn = '4')");//サイズ交換のトラン
               //sql文字列を' AND 'で結合
               $query = implode(' AND ', $query_list);
               //--- クエリー実行・取得 ---//
               $t_rtn_tran_count = TReturnedPlanInfoTran::find(array(
               'conditions' => $query
               ))->count();
             }
             //個体管理番号ごとの数量-返却予定数計算
             $individual_possible_qty = $items->quantity - $items->return_plan__qty;
             if($individual_possible_qty > 0) {
               //各個体管理番号
               if ($t_rtn_tran_count == 0) {
                 // name属性用カウント値
                 $element["name_no"] = $arr_num;
                 array_push($list["individual_ctrl_no"], $items->individual_ctrl_no);
                 $element["individual_ctrl_no"] = $items->individual_ctrl_no;
                 if ($results_cnt - 1 !== $i) {
                   $element["br"] = "<br/>";
                 } else {
                   $element["br"] = "";
                 }
                 array_push($list["individual_chk"], $element);
               }
             }
           }
           // 個体管理番号数
           $list["individual_cnt"] = count($list["individual_ctrl_no"]);
           // 個体管理番号(表示用)
           $list["individual_ctrl_no"] = implode("<br/>", $list["individual_ctrl_no"]);
         }
         // 返却枚数
         $list["return_num"] = "0";
         // 個体管理番号表示フラグ
         if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "1") {
           //個体管理番号ありフラグ
           $list["individual_flg"] = true;
           //その他交換、サイズ交換があった時用の個数０フラグ
           if($list["individual_cnt"] == 0){
             $list['item_add_flg'] = false;
           }else{
             $list['item_add_flg'] = true;
             //$list["arr_num"] = $arr_num++;
           }
           // 所持枚数
           $list["possible_num"] = $list["individual_cnt"];
         } else {
           $list["individual_flg"] = false;
           // 所持枚数 数量 - 返却予定数のサマリ
           $list["possible_num"] = $quantity - $return_plan__qty;

         }
         //--その他の必要hiddenパラメータ--//
         // 部門コード
         $list["rntl_sect_cd"] = $wearer_other_post["rntl_sect_cd"];
         // 職種コード
         $list["job_type_cd"] = $wearer_other_post['job_type_cd'];
         // 職種アイテムコード
         $list["job_type_item_cd"] = $result->as_job_type_item_cd;

         //その他交換、サイズ交換、発注のチェック 個なし
         if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "0") {
           $query_list = array();
           array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
           array_push($query_list, "rntl_cont_no = '" . $wearer_other_post['rntl_cont_no'] . "'");
           array_push($query_list, "werer_cd = '" . $wearer_other_post['werer_cd'] . "'");
           //商品情報
           array_push($query_list, "item_cd = '" . $list['item_cd'] . "'");
           array_push($query_list, "color_cd = '" . $list['color_cd'] . "'");
           //発注状況区分
           array_push($query_list, "(order_sts_kbn = '3' OR order_sts_kbn = '4')");//サイズ交換のトラン

           //sql文字列を' AND 'で結合
           $query = implode(' AND ', $query_list);
           //--- クエリー実行・取得 ---//
           $t_order_tran_items = TOrderTran::find(array(
           'conditions' => $query
           ));
           $t_order_tran_count = $t_order_tran_items->count();
           if($t_order_tran_count > 0){
             foreach($t_order_tran_items as $t_order_tran_item){
               // 所持枚数
               $list["possible_num"] =  $list["quantity"] - $t_order_tran_item->order_qty;
             }
           }

           if ($list["possible_num"] == 0) {
             $list['item_add_flg'] = false;
           }

         }
         //その他交換、サイズ交換の発注が入ってないこと。
         if($list['item_add_flg']){
           $list["arr_num"] = $arr_num++;
           // No
             $list["list_no"] = $list_cnt++;
             array_push($all_list, $list);
         }
       }
     }
   }
  // 返却総枚数(返却可能枚数)
   $json_list["sum_num"] = array();

   array_push($json_list["sum_num"], $list);

   // 商品リスト件数による一覧表示制御
   $json_list["list_disp_flg"] = true;
   if (count($all_list) == 0) {
     $json_list["list_disp_flg"] = false;
   }

   // 商品リスト件数、リスト内容
   $json_list["list_cnt"] = count($all_list);
   $json_list["item_list"] = $all_list;

   // 個体管理番号表示フラグ
   if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "1") {
     $json_list["individual_flg"] = true;
   } else {
     $json_list["individual_flg"] = false;
   }

   echo json_encode($json_list);
   //ChromePhp::LOG('JSON_LIST');
   //ChromePhp::LOG($json_list);
});

/**
 * 発注入力（不要品返却）
 * 発注取消処理
 */
$app->post('/wearer_return/delete', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
  $auth = $app->session->get("auth");
  //ChromePhp::LOG($auth);
  // 前画面セッション取得
  $wearer_other_post = $app->session->get("wearer_other_post");
  //ChromePhp::LOG($wearer_other_post);
  // フロントパラメータ取得
  $cond = $params['data'];
  //ChromePhp::LOG("フロント側パラメータ");
  //ChromePhp::LOG($cond);

  $json_list = array();
  // DB更新エラーコード 0:正常 1:更新エラー
  $json_list["error_code"] = "0";

  // トランザクション開始
  $m_wearer_std_tran = new MWearerStdTran();
  $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
  try {
    //--着用者基本マスタトラン削除--//
      //ChromePhp::LOG("着用者基本マスタトラン削除");
      $query_list = array();
      array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "m_wearer_std_tran.werer_cd = '".$cond['werer_cd']."'");
      array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$cond['rntl_cont_no']."'");
      array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$cond['rntl_sect_cd']."'");
      array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$cond['job_type_cd']."'");
      // 発注状況区分(終了)
      array_push($query_list,"m_wearer_std_tran.order_sts_kbn = '2'");
      // 着用者状況区分(その他（着用終了）)
      array_push($query_list,"m_wearer_std_tran.werer_sts_kbn = '3'");
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


    //--発注情報トラン削除--//
    //ChromePhp::LOG("発注情報トラン削除");
    $query_list = array();
    array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_order_tran.order_req_no = '".$cond['order_req_no']."'");
    // 発注区分「終了」
    array_push($query_list, "t_order_tran.order_sts_kbn = '2'");
    // 理由区分「不要品返却」系ステータス
    array_push($query_list,"(t_order_tran.order_reason_kbn = '28' OR t_order_tran.order_reason_kbn = '07')");
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
    array_push($query_list, "t_returned_plan_info_tran.order_req_no = '".$cond['return_req_no']."'");
    // 発注区分「終了」
    array_push($query_list, "t_returned_plan_info_tran.order_sts_kbn = '2'");
    // 理由区分「不要品返却」系ステータス
    array_push($query_list,"(t_returned_plan_info_tran.order_reason_kbn = '28' OR t_returned_plan_info_tran.order_reason_kbn = '07')");
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

    // トランザクションコミット
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
  } catch (Exception $e) {
    // トランザクションロールバック
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));

    $json_list["error_code"] = "1";

    echo json_encode($json_list);
    return;
  }

  //ChromePhp::LOG("発注取消処理コード");
  //ChromePhp::LOG($json_list["error_code"]);
  echo json_encode($json_list);
});

/**
 * 発注入力（不要品返却）
 * 入力完了処理
 */
$app->post('/wearer_return/complete', function ()use($app){
   $params = json_decode(file_get_contents("php://input"), true);
   // アカウントセッション取得
   $auth = $app->session->get("auth");
   //ChromePhp::LOG($auth);

   // 前画面セッション取得
   $wearer_other_post = $app->session->get("wearer_other_post");
   //ChromePhp::LOG($wearer_other_post);

    //--貸与パターン--//
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
    array_push($query_list, "job_type_cd = '".$wearer_other_post['job_type_cd']."'");
    $query = implode(' AND ', $query_list);
    //ChromePhp::log($query);
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
                "data" => $results,
                "limit" => 1,
                "page" => 1
            )
        );
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach ($results as $result) {
            $order_control_unit = $result->order_control_unit;
            //1:人員管理、2:貸与枚数管理
        }
    }

   // フロントパラメータ取得
   $mode = $params["mode"];
   $wearer_data_input = $params["wearer_data"];
   $item_list = $params["item"];
   //ChromePhp::LOG($wearer_data_input);
   //ChromePhp::LOG($item_list);

   $json_list = array();
   // DB更新エラーコード 0:正常 その他:要因エラー
   $json_list["error_code"] = "0";
   $json_list["error_msg"] = array();

   if ($mode == "check") {
     //--入力内容確認--//
     // 共通
     if (empty($item_list)) {
       $json_list["error_code"] = "1";
       $error_msg = "対象商品がない為、不良品返却登録を行うことができません。";
       array_push($json_list["error_msg"], $error_msg);
       echo json_encode($json_list);
       return;
     }
/*
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
*/
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
     }

     if (mb_strlen($wearer_data_input['member_name_kana']) > 0) {
        if (strlen(mb_convert_encoding($wearer_data_input['member_name_kana'], "SJIS")) > 25) {
          $json_list["error_code"] = "1";
          $error_msg = "着用者名(カナ)が規定の文字数をオーバーしています。";
          array_push($json_list["error_msg"], $error_msg);
        }
     }
     // コメント欄
     if (mb_strlen($wearer_data_input['comment']) > 0) {
       if (strlen(mb_convert_encoding($wearer_data_input['comment'], "SJIS")) > 100) {
         $json_list["error_code"] = "1";
         $error_msg = "コメント欄は100文字以内で入力してください。";
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
     // 発注商品一覧
     foreach ($item_list as $item_map) {
       // 返却枚数フォーマットチェック
       if (!empty($item_map["return_num"])) {
         if (!ctype_digit(strval($item_map["return_num"]))) {
           if (empty($return_num_format_err)) {
             $return_num_format_err = "err";
             $json_list["error_code"] = "1";
             $error_msg = "発注商品一覧の返却枚数には半角数字を入力してください。";
             array_push($json_list["error_msg"], $error_msg);
           }
         }
       }
       if (!$item_map["individual_flg"]) {
         // ※個体管理番号表示フラグがOFFの場合
         // 商品毎返却可能チェック
         if (!empty($item_map["return_num"])) {
           if ($item_map["return_num"] > $item_map["possible_num"]) {
             if (empty($return_num_possible_err)) {
               $return_num_possible_err = "err";
               $json_list["error_code"] = "1";
               $error_msg = "発注商品一覧にて返却枚数が現在の所持枚数を超過している商品があります。";
               array_push($json_list["error_msg"], $error_msg);
             }
           }
         }
       } else {
         // ※個体管理番号表示フラグがONの場合
         // 商品毎返却可能チェック
         if (!empty($item_map["individual_data"])) {
           $target_cnt = 0;
           foreach ($item_map["individual_data"] as $individual_data) {
             if ($individual_data["target_flg"] == "1") {
               $target_cnt++;
             }
           }
           if ($item_map["possible_num"] < $target_cnt) {
             if (empty($return_num_possible_err)) {
               $return_num_possible_err = "err";
               $json_list["error_code"] = "1";
               $error_msg = "発注商品一覧にて返却枚数が現在の所持枚数を超過している商品があります。";
               array_push($json_list["error_msg"], $error_msg);
             }
           }
         }
       }
     }

     if (empty($json_list["error_code"]) && empty($json_list["error_msg"])) {
       $sum_possible_num = 0;
       $sum_return_num = 0;
       foreach ($item_list as $item_map) {
         $sum_possible_num += $item_map["possible_num"];
         if ($item_map["individual_flg"] == false) {
           // ※個体管理番号表示フラグがOFFの場合
           // 返却可能総枚数チェック
           if (!empty($item_map["return_num"])) {
             $sum_return_num += $item_map["return_num"];
           }
         } else {
           // ※個体管理番号表示フラグがONの場合
           // 返却可能総枚数チェック
           $target_cnt = 0;
           if (!empty($item_map["individual_data"])) {
             foreach ($item_map["individual_data"] as $individual_data) {
               if ($individual_data["target_flg"] == "1") {
                 $target_cnt += 1;
               }
             }
             $sum_return_num += $target_cnt;
           }
         }
       }
       if ($sum_return_num == 0) {
         $json_list["error_code"] = "1";
         $error_msg = "１つ以上の商品の返却枚数を指定してから登録を行ってください。";
         array_push($json_list["error_msg"], $error_msg);
       }
     }
     //サイズ交換 その他交換
    if (empty($json_list["error_code"]) && empty($json_list["error_msg"])) {
        if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "1") {
            foreach ($item_list as $item_map) {
                foreach ($item_map["individual_data"] as $individual_data) {
                    if($individual_data['target_flg'] == 1){
                        $query_list = array();
                        array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
                        array_push($query_list, "rntl_cont_no = '" . $wearer_other_post['rntl_cont_no'] . "'");
                        array_push($query_list, "werer_cd = '" . $wearer_other_post['werer_cd'] . "'");
                        //商品情報
                        array_push($query_list, "item_cd = '" . $item_map['item_cd'] . "'");
                        array_push($query_list, "color_cd = '" . $item_map['color_cd'] . "'");
                        array_push($query_list, "individual_ctrl_no = '" . $individual_data['individual_ctrl_no'] . "'");
                        //発注状況区分
                        array_push($query_list, "(order_sts_kbn = '3' OR order_sts_kbn = '4')");//サイズ交換のトラン
                        //sql文字列を' AND 'で結合
                        $query = implode(' AND ', $query_list);
                        //--- クエリー実行・取得 ---//
                        $t_rtn_tran_count = TReturnedPlanInfoTran::find(array(
                        'conditions' => $query
                        ))->count();

                        if ($t_rtn_tran_count > 0) {
                            $json_list["error_code"] = "1";
                            $error_msg = $item_map['item_cd'] . "-" . $item_map['color_cd'] . "の個体管理番号:" . $individual_data['individual_ctrl_no'] . "は既にサイズ交換またはその他交換の発注がされています。";
                            array_push($json_list["error_msg"], $error_msg);
                        }
                    }
                }
            }
        }


        //その他交換、サイズ交換、発注のチェック 個なし
        if (individual_flg($auth['corporate_id'], $wearer_other_post['rntl_cont_no']) == "0") {
            foreach ($item_list as $item_map) {
                $query_list = array();
                array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
                array_push($query_list, "rntl_cont_no = '" . $wearer_other_post['rntl_cont_no'] . "'");
                array_push($query_list, "werer_cd = '" . $wearer_other_post['werer_cd'] . "'");
                //商品情報
                array_push($query_list, "item_cd = '" . $item_map['item_cd'] . "'");
                array_push($query_list, "color_cd = '" . $item_map['color_cd'] . "'");
                //発注状況区分
                array_push($query_list, "(order_sts_kbn = '3' OR order_sts_kbn = '4')");//サイズ交換のトラン

                //sql文字列を' AND 'で結合
                $query = implode(' AND ', $query_list);
                //--- クエリー実行・取得 ---//
                $t_order_tran_items = TOrderTran::find(array(
                'conditions' => $query
                ));
                $t_order_tran_count = $t_order_tran_items->count();
                if ($t_order_tran_count > 0) {
                    foreach ($t_order_tran_items as $t_order_tran_item) {
                        // 所持枚数
                        $item_map["possible_num"] = $item_map["quantity"] - $t_order_tran_item->order_qty;
                    }
                }

                if ($item_map["possible_num"] < 0) {
                    $json_list["error_code"] = "1";
                    $error_msg = $item_map['item_cd']."-".$item_map['color_cd']."は既に交換可能枚数を超える、サイズ交換またはその他交換の発注がされています。";
                    array_push($json_list["error_msg"], $error_msg);
                }
            }
        }
    }

     echo json_encode($json_list);
   } else if ($mode == "update") {
     //--発注NGパターンチェック-- ここから//
     //※着用者基本マスタトラン参照
     $query_list = array();
     array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
     array_push($query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
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
/*
       // 着用者基本マスタトラン.発注状況区分 = 「着用者編集」の情報がある際は発注NG
       if ($order_sts_kbn == "6") {
         $json_list["error_code"] = "1";
         $error_msg = "着用者編集の発注が登録されていた為、操作を完了できませんでした。着用者編集の発注を削除してから再度登録して下さい。";
         $json_list["error_msg"] = $error_msg;

         //ChromePhp::LOG($json_list);
         echo json_encode($json_list);
         return;
       }
*/
     }
     //※発注情報トラン参照
     $query_list = array();
     array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
     array_push($query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
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

         // 発注情報トラン.発注状況区分 = 「終了」または「異動」情報がある際は発注NG
         if ($order_sts_kbn == "2" && ($order_reason_kbn == "05" || $order_reason_kbn == "06" || $order_reason_kbn == "08" || $order_reason_kbn == "20")) {
           $json_list["error_code"] = "1";
           $error_msg = "貸与終了の発注が登録されていた為、操作を完了できませんでした。貸与終了の発注を削除してから再度登録して下さい。";
           $json_list["error_msg"] = $error_msg;
           echo json_encode($json_list);
           return;
         }
         if ($order_sts_kbn == "5" && ($order_reason_kbn == "09" || $order_reason_kbn == "10" || $order_reason_kbn == "11" || $order_reason_kbn == "24")) {
           $json_list["error_code"] = "1";
           $error_msg = "職種変更または異動の発注が登録されていた為、操作を完了できませんでした。職種変更または異動の発注を削除してから再度登録して下さい。";
           $json_list["error_msg"] = $error_msg;
           echo json_encode($json_list);
           return;
         }
       }
     }
     //--発注NGパターンチェック-- ここまで//

     // 着用者基本マスタ参照
     $query_list = array();
     array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
     array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
     array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
     // 発注状況区分(終了)
     array_push($query_list,"m_wearer_std_tran.order_sts_kbn = '2'");
     // 着用者状況区分(その他（着用終了）)
     array_push($query_list,"m_wearer_std_tran.werer_sts_kbn = '3'");
     // 理由区分
     array_push($query_list,"(t_order_tran.order_reason_kbn = '28' OR t_order_tran.order_reason_kbn = '07')");
     $query = implode(' AND ', $query_list);

     $arg_str = "";
     $arg_str = "SELECT ";
       $arg_str .= "m_wearer_std_tran.order_sts_kbn,";
       $arg_str .= "m_wearer_std_tran.order_req_no";
     $arg_str .= " FROM ";
     $arg_str .= "m_wearer_std_tran INNER JOIN t_order_tran";
     $arg_str .= " ON (m_wearer_std_tran.corporate_id = t_order_tran.corporate_id";
     $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = t_order_tran.rntl_cont_no";
     $arg_str .= " AND m_wearer_std_tran.werer_cd = t_order_tran.werer_cd";
     $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = t_order_tran.rntl_sect_cd";
     $arg_str .= " AND m_wearer_std_tran.job_type_cd = t_order_tran.job_type_cd)";
     $arg_str .= " WHERE ";
     $arg_str .= $query;
     //ChromePhp::LOG($arg_str);
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
     $result_obj = (array)$results;
     $tran_results_cnt = $result_obj["\0*\0_count"];
     //ChromePhp::LOG($results_cnt);
     $order_sts_kbn = "";
     if (!empty($tran_results_cnt)) {
         $paginator_model = new PaginatorModel(
             array(
                 "data"  => $results,
                 "limit" => $tran_results_cnt,
                 "page" => 1
             )
         );
         $paginator = $paginator_model->getPaginate();
         $results = $paginator->items;
         //ChromePhp::LOG($results);
         foreach ($results as $result) {
             $order_sts_kbn = $result->order_sts_kbn;
             $order_req_no = $result->order_req_no;
         }
     }

     // トランザクション開始
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
     try {
       if ($tran_results_cnt <= 0) {
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
         $shin_order_req_no = $order_req_no;
       }
       //ChromePhp::LOG("発注依頼No採番");
       //ChromePhp::LOG($shin_order_req_no);

       if ($tran_results_cnt > 0) {
         //--着用者基本マスタトランに情報がある場合、更新処理--//
         //ChromePhp::LOG("着用者基本マスタトラン更新");
         $src_query_list = array();
         array_push($src_query_list, "corporate_id = '".$auth['corporate_id']."'");
         array_push($src_query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
         array_push($src_query_list, "rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
         array_push($src_query_list, "job_type_cd = '".$wearer_other_post['job_type_cd']."'");
         array_push($src_query_list, "order_req_no = '".$shin_order_req_no."'");
         // 発注状況区分(終了)
         array_push($src_query_list,"order_sts_kbn = '2'");
         // 着用者状況区分(その他（着用終了）)
         array_push($src_query_list,"werer_sts_kbn = '3'");
         $src_query = implode(' AND ', $src_query_list);

         $up_query_list = array();
         // 貸与パターン
         $job_type_cd = explode(':', $wearer_data_input['job_type']);
         $job_type_cd = $job_type_cd[0];
//         array_push($up_query_list, "job_type_cd = '".$job_type_cd."'");
         // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
//         $m_wearer_std_comb_hkey = md5(
//           $auth['corporate_id']."-".
//           $wearer_other_post["werer_cd"]."-".
//           $wearer_data_input['agreement_no']."-".
//           $wearer_data_input['section']."-".
//           $job_type_cd
//         );
//         array_push($up_query_list, "m_wearer_std_comb_hkey = '".$m_wearer_std_comb_hkey."'");
//         // 発注No
//         array_push($up_query_list, "order_req_no = '".$shin_order_req_no."'");
//         // 企業ID
//         array_push($up_query_list, "corporate_id = '".$auth['corporate_id']."'");
//         // 着用者コード
//         array_push($up_query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
//         // 契約No
//         array_push($up_query_list, "rntl_cont_no = '".$wearer_data_input['agreement_no']."'");
//         // 部門コード
//         array_push($up_query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
         // 客先社員コード
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
         // 出荷先、出荷先支店コード
         if (!empty($wearer_data_input['shipment'])) {
           $shipment = explode(':', $wearer_data_input['shipment']);
           $ship_to_cd = $shipment[0];
           $ship_to_brnch_cd = $shipment[1];

           // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
           if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
             $query_list = array();
             array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
             array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
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
         // 発注状況区分(終了)
         if ($order_sts_kbn !== "6") {
           array_push($up_query_list, "order_sts_kbn = '2'");
         }
         // 更新区分(WEB発注システム(終了）)
         array_push($up_query_list, "upd_kbn = '2'");
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
           $wearer_other_post["werer_cd"]."-".
           $wearer_data_input['agreement_no']."-".
           $wearer_data_input['section']."-".
           $job_type_cd."-".
           $shin_order_req_no
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
         array_push($values_list, "'".$wearer_other_post['werer_cd']."'");
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
         // 着用者状況区分(その他（着用終了）)
         array_push($calum_list, "werer_sts_kbn");
         array_push($values_list, "'3'");
         // 出荷先、出荷先支店コード
         if (!empty($wearer_data_input['shipment'])) {
           $shipment = explode(':', $wearer_data_input['shipment']);
           $ship_to_cd = $shipment[0];
           $ship_to_brnch_cd = $shipment[1];

           // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
           if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
             $query_list = array();
             array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
             array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
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
         // 発注状況区分(終了)
         array_push($calum_list, "order_sts_kbn");
         array_push($values_list, "'2'");
         // 更新区分(WEB発注システム(終了))
         array_push($calum_list, "upd_kbn");
         array_push($values_list, "'2'");
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
       // 発注商品一覧内容登録
       if (!empty($item_list)) {
         // 現在の不要品返却発注の情報をクリーン
         //ChromePhp::LOG("発注情報トランクリーン");
         $query_list = array();
         array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
         array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
         array_push($query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
         // 発注状況区分「終了」
         array_push($query_list, "order_sts_kbn = '2'");
         // 理由区分「不要品返却」
         array_push($query_list,"(order_reason_kbn = '28' OR order_reason_kbn = '07')");
         // 着用者状況区分「その他（着用終了）」
         array_push($query_list, "werer_sts_kbn = '3'");
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

         //ChromePhp::LOG("発注情報トラン登録");
         foreach ($item_list as $item_map) {
           if (
            ($item_map["individual_flg"] == true && !empty($item_map["individual_data"])) ||
            ($item_map["individual_flg"] == false && !empty($item_map["return_num"]))
           )
           {
             // 個体管理番号単位の場合、商品毎の対象チェックが１つ以上あるもののみ登録する。それ以外は登録対象外
             if (!empty($item_map["individual_data"])) {
               $target_cnt = 0;
               foreach ($item_map["individual_data"] as $individual_data) {
                 if ($individual_data["target_flg"] == "1") {
                   $target_cnt = $target_cnt + 1;
                 }
               }
               if ($target_cnt == 0) {
                 continue;
               }
             }

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
             array_push($values_list, "'".$item_map['job_type_item_cd']."'");
             // 着用者コード
             array_push($calum_list, "werer_cd");
             array_push($values_list, "'".$wearer_other_post['werer_cd']."'");
             // 商品コード
             array_push($calum_list, "item_cd");
             array_push($values_list, "'".$item_map['item_cd']."'");
             // 色コード
             array_push($calum_list, "color_cd");
             array_push($values_list, "'".$item_map['color_cd']."'");
             // サイズコード
             array_push($calum_list, "size_cd");
             array_push($values_list, "'".$item_map['size_cd']."'");
             // サイズコード2
             array_push($calum_list, "size_two_cd");
             array_push($values_list, "' '");
             // 倉庫コード
             //rray_push($calum_list, "whse_cd");
             //array_push($values_list, "NULL");
             // 在庫USRコード
             //array_push($calum_list, "stk_usr_cd");
             //array_push($values_list, "NULL");
             // 在庫USR支店コード
             //array_push($calum_list, "stk_usr_brnch_cd");
             //array_push($values_list, "NULL");
             // 出荷先、出荷先支店コード
             if (!empty($wearer_data_input['shipment'])) {
               $shipment = explode(':', $wearer_data_input['shipment']);
               $ship_to_cd = $shipment[0];
               $ship_to_brnch_cd = $shipment[1];

               // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
               if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
                 $query_list = array();
                 array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
                 array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
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
             array_push($values_list, "'0'");
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
             // 着用者状況区分(その他（着用終了）)
             array_push($calum_list, "werer_sts_kbn");
             array_push($values_list, "'3'");
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
             $m_item_comb_hkey = md5(
               $auth['corporate_id']."-".
               $item_map['item_cd']."-".
               $item_map['color_cd']."-".
               $item_map['size_cd']
             );
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
               $wearer_other_post["werer_cd"]."-".
               $wearer_data_input['agreement_no']."-".
               $wearer_data_input['section']."-".
               $job_type_cd
             );
             array_push($calum_list, "m_wearer_std_comb_hkey");
             array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
             // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
             $m_wearer_item_comb_hkey = md5(
               $auth['corporate_id']."-".
               $wearer_other_post["werer_cd"]."-".
               $wearer_data_input['agreement_no']."-".
               $wearer_data_input['section']."-".
               $job_type_cd."-".
               $item_map['job_type_item_cd']."-".
               $item_map['item_cd']."-".
               $item_map['color_cd']."-".
               $item_map['size_cd']
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
             $results_cnt = $result_obj["\0*\0_count"];
             //ChromePhp::LOG($results_cnt);
          }
        }
      }
      //--返却予定情報トラン登録--//
      $cnt = 1;
      // 発注商品一覧内容登録
      if (!empty($item_list)) {
        // 現在の不要品返却発注の情報をクリーン
        //ChromePhp::LOG("発注情報トランクリーン");
        $query_list = array();
        array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
        array_push($query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
        // 発注状況区分「終了」
        array_push($query_list, "order_sts_kbn = '2'");
        // 理由区分「不要品返却」
        array_push($query_list,"(order_reason_kbn = '28' OR order_reason_kbn = '07')");
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
        //ChromePhp::log($item_map);
        foreach ($item_list as $item_map) {
          if ($item_map["individual_flg"] == true && !empty($item_map["individual_data"])) {
            // ※個体管理番号単位での登録の場合
            foreach ($item_map["individual_data"] as $individual_data) {
              // 対象にチェックされている商品のみが登録対象、それ以外は以降処理しない
              if ($individual_data["target_flg"] == "0") {
                continue;
              }
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
              array_push($values_list, "'".$wearer_other_post['werer_cd']."'");
              // 客先社員コード
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
              // 送信区分(未送信)
              array_push($calum_list, "snd_kbn");
              array_push($values_list, "'0'");
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
            // 個体管理番号（個なしの場合でも納品状況明細情報の個体管理番号を登録)
            array_push($calum_list, "individual_ctrl_no");
            array_push($values_list, "'".$item_map['individual_no']."'");
            // 着用者コード
            array_push($calum_list, "werer_cd");
            array_push($values_list, "'".$wearer_other_post['werer_cd']."'");
            // 客先社員コード
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
            // 送信区分(未送信)
            array_push($calum_list, "snd_kbn");
            array_push($values_list, "'0'");
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

      // トランザクションコミット
      $m_wearer_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
    } catch (Exception $e) {
      // トランザクションロールバック
      $m_wearer_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));
      //ChromePhp::LOG($e);

      $json_list["error_code"] = "1";
      $error_msg = "入力登録処理において、データ更新エラーが発生しました。";
      array_push($json_list["error_msg"], $error_msg);

      echo json_encode($json_list);
      return;
    }

    // 返却伝票用パラメータ
    $json_list['param'] = '';
    $json_list['param'] .= $wearer_data_input['agreement_no'].':';
    $json_list['param'] .= $shin_order_req_no;

    echo json_encode($json_list);
  }
});

/**
 * 発注入力（不要品返却）
 * 発注送信処理
 */
$app->post('/wearer_return/send', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
  $auth = $app->session->get("auth");
  //ChromePhp::LOG($auth);

  // 前画面セッション取得
  $wearer_other_post = $app->session->get("wearer_other_post");
  //ChromePhp::LOG($wearer_other_post);

  // フロントパラメータ取得
  $mode = $params["mode"];
  $wearer_data_input = $params["wearer_data"];
  $item_list = $params["item"];
  //ChromePhp::LOG($wearer_data_input);
  //ChromePhp::LOG($item_list);

  $json_list = array();
  // DB更新エラーコード 0:正常 その他:要因エラー
  $json_list["error_code"] = "0";
  $json_list["error_msg"] = array();

  if ($mode == "check") {
    //--入力内容確認--//
    // 共通
    if (empty($item_list)) {
      $json_list["error_code"] = "1";
      $error_msg = "対象商品がない為、不良品返却登録を行うことができません。";
      array_push($json_list["error_msg"], $error_msg);
      echo json_encode($json_list);
      return;
    }
/*
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
*/
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
    }

    if (mb_strlen($wearer_data_input['member_name_kana']) > 0) {
       if (strlen(mb_convert_encoding($wearer_data_input['member_name_kana'], "SJIS")) > 25) {
         $json_list["error_code"] = "1";
         $error_msg = "着用者名(カナ)が規定の文字数をオーバーしています。";
         array_push($json_list["error_msg"], $error_msg);
       }
    }
    // コメント欄
    if (mb_strlen($wearer_data_input['comment']) > 0) {
      if (strlen(mb_convert_encoding($wearer_data_input['comment'], "SJIS")) > 100) {
        $json_list["error_code"] = "1";
        $error_msg = "コメント欄は100文字以内で入力してください。";
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
    // 発注商品一覧
    foreach ($item_list as $item_map) {
      // 返却枚数フォーマットチェック
      if (!empty($item_map["return_num"])) {
        if (!ctype_digit(strval($item_map["return_num"]))) {
          if (empty($return_num_format_err)) {
            $return_num_format_err = "err";
            $json_list["error_code"] = "1";
            $error_msg = "発注商品一覧の返却枚数には半角数字を入力してください。";
            array_push($json_list["error_msg"], $error_msg);
          }
        }
      }
      if (!$item_map["individual_flg"]) {
        // ※個体管理番号表示フラグがOFFの場合
        // 商品毎返却可能チェック
        if (!empty($item_map["return_num"])) {
          if ($item_map["return_num"] > $item_map["possible_num"]) {
            if (empty($return_num_possible_err)) {
              $return_num_possible_err = "err";
              $json_list["error_code"] = "1";
              $error_msg = "発注商品一覧にて返却枚数が現在の所持枚数を超過している商品があります。";
              array_push($json_list["error_msg"], $error_msg);
            }
          }
        }
      } else {
        // ※個体管理番号表示フラグがONの場合
        // 商品毎返却可能チェック
        if (!empty($item_map["individual_data"])) {
          $target_cnt = 0;
          foreach ($item_map["individual_data"] as $individual_data) {
            if ($individual_data["target_flg"] == "1") {
              $target_cnt++;
            }
          }
          if ($item_map["possible_num"] < $target_cnt) {
            if (empty($return_num_possible_err)) {
              $return_num_possible_err = "err";
              $json_list["error_code"] = "1";
              $error_msg = "発注商品一覧にて返却枚数が現在の所持枚数を超過している商品があります。";
              array_push($json_list["error_msg"], $error_msg);
            }
          }
        }
      }
    }
    if (empty($json_list["error_code"]) && empty($json_list["error_msg"])) {
      $sum_possible_num = 0;
      $sum_return_num = 0;
      foreach ($item_list as $item_map) {
        $sum_possible_num += $item_map["possible_num"];
        if ($item_map["individual_flg"] == false) {
          // ※個体管理番号表示フラグがOFFの場合
          // 返却可能総枚数チェック
          if (!empty($item_map["return_num"])) {
            $sum_return_num += $item_map["return_num"];
          }
        } else {
          // ※個体管理番号表示フラグがONの場合
          // 返却可能総枚数チェック
          $target_cnt = 0;
          if (!empty($item_map["individual_data"])) {
            foreach ($item_map["individual_data"] as $individual_data) {
              if ($individual_data["target_flg"] == "1") {
                $target_cnt += 1;
              }
            }
            $sum_return_num += $target_cnt;
          }
        }
      }
      if ($sum_return_num == 0) {
        $json_list["error_code"] = "1";
        $error_msg = "１つ以上の商品の返却枚数を指定してから登録を行ってください。";
        array_push($json_list["error_msg"], $error_msg);
      }
      /*
      if ($sum_possible_num <= $sum_return_num) {
        $json_list["error_code"] = "1";
        $error_msg = "現在所持している商品を全て返却することはできません。";
        array_push($json_list["error_msg"], $error_msg);
      }
      */
    }

    echo json_encode($json_list);
  } else if ($mode == "update") {
    //--発注NGパターンチェック-- ここから//
    //※着用者基本マスタトラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
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
/*
      // 着用者基本マスタトラン.発注状況区分 = 「着用者編集」の情報がある際は発注NG
      if ($order_sts_kbn == "6") {
        $json_list["error_code"] = "1";
        $error_msg = "着用者編集の発注が登録されていた為、操作を完了できませんでした。着用者編集の発注を削除してから再度登録して下さい。";
        $json_list["error_msg"] = $error_msg;

        //ChromePhp::LOG($json_list);
        echo json_encode($json_list);
        return;
      }
*/
    }
    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
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

        // 発注情報トラン.発注状況区分 = 「終了」または「異動」情報がある際は発注NG
        if ($order_sts_kbn == "2" && ($order_reason_kbn == "05" || $order_reason_kbn == "06" || $order_reason_kbn == "08" || $order_reason_kbn == "20")) {
          $json_list["error_code"] = "1";
          $error_msg = "貸与終了の発注が登録されていた為、操作を完了できませんでした。貸与終了の発注を削除してから再度登録して下さい。";
          $json_list["error_msg"] = $error_msg;
          echo json_encode($json_list);
          return;
        }
        if ($order_sts_kbn == "5" && ($order_reason_kbn == "09" || $order_reason_kbn == "10" || $order_reason_kbn == "11" || $order_reason_kbn == "24")) {
          $json_list["error_code"] = "1";
          $error_msg = "職種変更または異動の発注が登録されていた為、操作を完了できませんでした。職種変更または異動の発注を削除してから再度登録して下さい。";
          $json_list["error_msg"] = $error_msg;
          echo json_encode($json_list);
          return;
        }
      }
    }
    //--発注NGパターンチェック-- ここまで//

    // 着用者基本マスタ参照
    $query_list = array();
    array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
    array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
    array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
    // 発注状況区分(終了)
    array_push($query_list,"m_wearer_std_tran.order_sts_kbn = '2'");
    // 着用者状況区分(その他（着用終了）)
    array_push($query_list,"m_wearer_std_tran.werer_sts_kbn = '3'");
    // 理由区分
    array_push($query_list,"(t_order_tran.order_reason_kbn = '28' OR t_order_tran.order_reason_kbn = '07')");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "m_wearer_std_tran.order_sts_kbn,";
    $arg_str .= "m_wearer_std_tran.order_req_no";
    $arg_str .= " FROM ";
    $arg_str .= "m_wearer_std_tran INNER JOIN t_order_tran";
    $arg_str .= " ON (m_wearer_std_tran.corporate_id = t_order_tran.corporate_id";
    $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = t_order_tran.rntl_cont_no";
    $arg_str .= " AND m_wearer_std_tran.werer_cd = t_order_tran.werer_cd";
    $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = t_order_tran.rntl_sect_cd";
    $arg_str .= " AND m_wearer_std_tran.job_type_cd = t_order_tran.job_type_cd)";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    //ChromePhp::LOG($arg_str);
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $tran_results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);
    $order_sts_kbn = "";
    if (!empty($tran_results_cnt)) {
        $paginator_model = new PaginatorModel(
            array(
                "data"  => $results,
                "limit" => $tran_results_cnt,
                "page" => 1
            )
        );
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;
        //ChromePhp::LOG($results);
        foreach ($results as $result) {
            $order_sts_kbn = $result->order_sts_kbn;
            $order_req_no = $result->order_req_no;
        }
    }

    // トランザクション開始
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
    try {
      if ($tran_results_cnt <= 0) {
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
        $shin_order_req_no = $order_req_no;
      }
      //ChromePhp::LOG("発注依頼No採番");
      //ChromePhp::LOG($shin_order_req_no);

      if ($tran_results_cnt > 0) {
        //--着用者基本マスタトランに情報がある場合、更新処理--//
        //ChromePhp::LOG("着用者基本マスタトラン更新");
        $src_query_list = array();
        array_push($src_query_list, "corporate_id = '".$auth['corporate_id']."'");
        array_push($src_query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
        array_push($src_query_list, "rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
        array_push($src_query_list, "job_type_cd = '".$wearer_other_post['job_type_cd']."'");
        array_push($src_query_list, "order_req_no = '".$shin_order_req_no."'");
        // 発注状況区分(終了)
        array_push($src_query_list,"order_sts_kbn = '2'");
        // 着用者状況区分(その他（着用終了）)
        array_push($src_query_list,"werer_sts_kbn = '3'");
        $src_query = implode(' AND ', $src_query_list);

        $up_query_list = array();
        // 貸与パターン
        $job_type_cd = explode(':', $wearer_data_input['job_type']);
        $job_type_cd = $job_type_cd[0];
//        array_push($up_query_list, "job_type_cd = '".$job_type_cd."'");
//        // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
//        $m_wearer_std_comb_hkey = md5(
//          $auth['corporate_id']."-".
//          $wearer_other_post["werer_cd"]."-".
//          $wearer_data_input['agreement_no']."-".
//          $wearer_data_input['section']."-".
//          $job_type_cd
//        );
//        array_push($up_query_list, "m_wearer_std_comb_hkey = '".$m_wearer_std_comb_hkey."'");
//        // 発注No
//        array_push($up_query_list, "order_req_no = '".$shin_order_req_no."'");
//        // 企業ID
//        array_push($up_query_list, "corporate_id = '".$auth['corporate_id']."'");
//        // 着用者コード
//        array_push($up_query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
//        // 契約No
//        array_push($up_query_list, "rntl_cont_no = '".$wearer_data_input['agreement_no']."'");
//        // 部門コード
//        array_push($up_query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
        // 客先社員コード
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
        // 出荷先、出荷先支店コード
        if (!empty($wearer_data_input['shipment'])) {
          $shipment = explode(':', $wearer_data_input['shipment']);
          $ship_to_cd = $shipment[0];
          $ship_to_brnch_cd = $shipment[1];

          // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
          if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
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
        // 発注状況区分(終了)
        if ($order_sts_kbn !== "6") {
          array_push($up_query_list, "order_sts_kbn = '2'");
        }
        // 更新区分(WEB発注システム(終了）)
        array_push($up_query_list, "upd_kbn = '2'");
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
            $wearer_other_post["werer_cd"]."-".
            $wearer_data_input['agreement_no']."-".
            $wearer_data_input['section']."-".
            $job_type_cd."-".
            $shin_order_req_no
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
        array_push($values_list, "'".$wearer_other_post['werer_cd']."'");
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
        // 着用者状況区分(その他（着用終了）)
        array_push($calum_list, "werer_sts_kbn");
        array_push($values_list, "'3'");
        // 出荷先、出荷先支店コード
        if (!empty($wearer_data_input['shipment'])) {
          $shipment = explode(':', $wearer_data_input['shipment']);
          $ship_to_cd = $shipment[0];
          $ship_to_brnch_cd = $shipment[1];

          // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
          if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
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
        // 発注状況区分(終了)
        array_push($calum_list, "order_sts_kbn");
        array_push($values_list, "'2'");
        // 更新区分(WEB発注システム(新規登録))
        array_push($calum_list, "upd_kbn");
        array_push($values_list, "'1'");
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
      // 発注商品一覧内容登録
      if (!empty($item_list)) {
        // 現在の不要品返却発注の情報をクリーン
        //ChromePhp::LOG("発注情報トランクリーン");
        $query_list = array();
        array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
        array_push($query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
        // 発注状況区分「終了」
        array_push($query_list, "order_sts_kbn = '2'");
        // 理由区分「不要品返却」
        array_push($query_list,"(order_reason_kbn = '28' OR order_reason_kbn = '07')");
        // 着用者状況区分「その他（着用終了）」
        array_push($query_list, "werer_sts_kbn = '3'");
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

        //ChromePhp::LOG("発注情報トラン登録");
        foreach ($item_list as $item_map) {
          if (
           ($item_map["individual_flg"] == true && !empty($item_map["individual_data"])) ||
           ($item_map["individual_flg"] == false && !empty($item_map["return_num"]))
          )
          {
            // 個体管理番号単位の場合、商品毎の対象チェックが１つ以上あるもののみ登録する。それ以外は登録対象外
            if (!empty($item_map["individual_data"])) {
              $target_cnt = 0;
              foreach ($item_map["individual_data"] as $individual_data) {
                if ($individual_data["target_flg"] == "1") {
                  $target_cnt = $target_cnt + 1;
                }
              }
              if ($target_cnt == 0) {
                continue;
              }
            }

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
            array_push($values_list, "'".$item_map['job_type_item_cd']."'");
            // 着用者コード
            array_push($calum_list, "werer_cd");
            array_push($values_list, "'".$wearer_other_post['werer_cd']."'");
            // 商品コード
            array_push($calum_list, "item_cd");
            array_push($values_list, "'".$item_map['item_cd']."'");
            // 色コード
            array_push($calum_list, "color_cd");
            array_push($values_list, "'".$item_map['color_cd']."'");
            // サイズコード
            array_push($calum_list, "size_cd");
            array_push($values_list, "'".$item_map['size_cd']."'");
            // サイズコード2
            array_push($calum_list, "size_two_cd");
            array_push($values_list, "' '");
            // 倉庫コード
            //array_push($calum_list, "whse_cd");
            //array_push($values_list, " ");
            // 在庫USRコード
            //array_push($calum_list, "stk_usr_cd");
            //array_push($values_list, " ");
            // 在庫USR支店コード
            //array_push($calum_list, "stk_usr_brnch_cd");
            //array_push($values_list, " ");
            // 出荷先、出荷先支店コード
            if (!empty($wearer_data_input['shipment'])) {
              $shipment = explode(':', $wearer_data_input['shipment']);
              $ship_to_cd = $shipment[0];
              $ship_to_brnch_cd = $shipment[1];

              // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
              if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
                $query_list = array();
                array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
                array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
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
            array_push($values_list, "'0'");
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
            // 着用者状況区分(その他（着用終了）)
            array_push($calum_list, "werer_sts_kbn");
            array_push($values_list, "'3'");
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
            $m_item_comb_hkey = md5(
              $auth['corporate_id']."-".
              $item_map['item_cd']."-".
              $item_map['color_cd']."-".
              $item_map['size_cd']
            );
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
              $wearer_other_post["werer_cd"]."-".
              $wearer_data_input['agreement_no']."-".
              $wearer_data_input['section']."-".
              $job_type_cd
            );
            array_push($calum_list, "m_wearer_std_comb_hkey");
            array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
            // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
            $m_wearer_item_comb_hkey = md5(
              $auth['corporate_id']."-".
              $wearer_other_post["werer_cd"]."-".
              $wearer_data_input['agreement_no']."-".
              $wearer_data_input['section']."-".
              $job_type_cd."-".
              $item_map['job_type_item_cd']."-".
              $item_map['item_cd']."-".
              $item_map['color_cd']."-".
              $item_map['size_cd']
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
            $results_cnt = $result_obj["\0*\0_count"];
            //ChromePhp::LOG($results_cnt);
         }
       }
     }
     //--返却予定情報トラン登録--//
     $cnt = 1;
     // 発注商品一覧内容登録
     if (!empty($item_list)) {
       // 現在の不要品返却発注の情報をクリーン
       //ChromePhp::LOG("発注情報トランクリーン");
       $query_list = array();
       array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
       array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
       array_push($query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
       // 発注状況区分「終了」
       array_push($query_list, "order_sts_kbn = '2'");
       // 理由区分「不要品返却」
       array_push($query_list,"(order_reason_kbn = '28' OR order_reason_kbn = '07')");
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
             // 対象にチェックされている商品のみが登録対象、それ以外は以降処理しない
             if ($individual_data["target_flg"] == "0") {
               continue;
             }
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
             array_push($values_list, "'".$wearer_other_post['werer_cd']."'");
             // 客先社員コード
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
             // 送信区分(送信済み)
             array_push($calum_list, "snd_kbn");
             array_push($values_list, "'1'");
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
           // 個体管理番号（個なしの場合でも納品状況明細情報の個体管理番号を登録)
           array_push($calum_list, "individual_ctrl_no");
           array_push($values_list, "'".$item_map['individual_no']."'");
           // 着用者コード
           array_push($calum_list, "werer_cd");
           array_push($values_list, "'".$wearer_other_post['werer_cd']."'");
           // 客先社員コード
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
           // 送信区分(送信済み)
           array_push($calum_list, "snd_kbn");
           array_push($values_list, "'1'");
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

     // トランザクションコミット
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
   } catch (Exception $e) {
     // トランザクションロールバック
     $m_wearer_std_tran = new MWearerStdTran();
     $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));
     //ChromePhp::LOG($e);

     $json_list["error_code"] = "1";
     $error_msg = "発注送信処理において、データ更新エラーが発生しました。";
     array_push($json_list["error_msg"], $error_msg);

     echo json_encode($json_list);
     return;
   }

   // 返却伝票用パラメータ
   $json_list['param'] = '';
   $json_list['param'] .= $wearer_data_input['agreement_no'].':';
   $json_list['param'] .= $shin_order_req_no;

   echo json_encode($json_list);
 }
});
