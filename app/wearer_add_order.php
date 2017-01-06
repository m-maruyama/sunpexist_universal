<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * 発注入力（追加貸与）
 * 入力項目：初期値情報、前画面セッション取得
 *
 */
$app->post('/wearer_add/info', function ()use($app){
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
  // 発注状況区分(貸与)
  array_push($query_list,"t_order_tran.order_sts_kbn = '1'");
  // 理由区分(追加貸与)
  array_push($query_list,"t_order_tran.order_reason_kbn = '03'");
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
  array_push($query_list, "m_wearer_std_tran.order_sts_kbn = '1'");
  array_push($query_list, "t_order_tran.order_reason_kbn = '03'");
  $query = implode(' AND ', $query_list);

  $arg_str = "";
  $arg_str = "SELECT ";
  $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
  $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
  $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana,";
  $arg_str .= "m_wearer_std_tran.appointment_ymd as as_appointment_ymd,";
  $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";
  $arg_str .= "m_wearer_std_tran.resfl_ymd as as_resfl_ymd";
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
    // 着用者基本マスタトラン（追加貸与）有り
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
      // 性別
      $wearer_other_post['sex_kbn'] = $result->as_sex_kbn;
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

  //--発注情報トラン内、「追加貸与」情報の有無確認--//
  //※発注情報トラン参照
  $query_list = array();
  array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
  array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
  array_push($query_list, "t_order_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
  array_push($query_list, "t_order_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
  array_push($query_list, "t_order_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
  // 発注状況区分(貸与)
  array_push($query_list,"t_order_tran.order_sts_kbn = '1'");
  // 理由区分(不要品返却)
  array_push($query_list,"t_order_tran.order_reason_kbn = '03'");
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
 * 発注入力（追加貸与）
 * 入力項目：発注商品一覧
 */
 $app->post('/wearer_add/list', function ()use($app){
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

   // 発注情報トラン(追加貸与発注履歴)確認
   $all_list = array();
   $list = array();
   $query_list = array();
   array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
   array_push($query_list, "t_order_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
   array_push($query_list, "t_order_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
   array_push($query_list, "t_order_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
   // 発注状況区分
   array_push($query_list, "t_order_tran.order_sts_kbn = '1'");
   // 理由区分
   array_push($query_list, "t_order_tran.order_reason_kbn = '03'");
   $query = implode(' AND ', $query_list);

   $arg_str = "";
   $arg_str = "SELECT ";
   $arg_str .= "t_order_tran.item_cd as as_order_item_cd,";
   $arg_str .= "t_order_tran.color_cd as as_order_color_cd,";
   $arg_str .= "t_order_tran.size_cd as as_order_size_cd,";
   $arg_str .= "t_order_tran.order_qty as as_order_qty";
   $arg_str .= " FROM ";
   $arg_str .= "t_order_tran";
   $arg_str .= " WHERE ";
   $arg_str .= $query;
   $arg_str .= " ORDER BY as_order_item_cd ASC, as_order_color_cd ASC";
   $m_wearer_std_tran = new MWearerStdTran();
   $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG("発注情報トラン商品一覧件数");
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
       // 商品情報取得
       $query_list = array();
       array_push($query_list, "m_job_type.corporate_id = '".$auth['corporate_id']."'");
       array_push($query_list, "m_job_type.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
       array_push($query_list, "m_job_type.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
       array_push($query_list, "m_item.item_cd = '".$result->as_order_item_cd."'");
       array_push($query_list, "m_item.color_cd= '".$result->as_order_color_cd."'");
       array_push($query_list, "m_item.size_cd= '".$result->as_order_size_cd."'");
       $query = implode(' AND ', $query_list);

       $arg_str = "";
       $arg_str = "SELECT ";
       $arg_str .= " * ";
       $arg_str .= " FROM ";
       $arg_str .= "(SELECT distinct on (m_item.item_cd, m_item.color_cd, m_item.size_cd,m_input_item.job_type_item_cd) ";
       $arg_str .= "m_item.item_cd as as_item_cd,";
       $arg_str .= "m_item.color_cd as as_color_cd,";
       $arg_str .= "m_item.size_cd as as_size_cd,";
       $arg_str .= "m_item.item_name as as_item_name,";
       $arg_str .= "m_input_item.job_type_item_cd as as_job_type_item_cd,";
       $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
       $arg_str .= "m_input_item.std_input_qty as as_std_input_qty";
       $arg_str .= " FROM ";
       $arg_str .= "m_job_type INNER JOIN m_input_item";
       $arg_str .= " ON (m_job_type.corporate_id = m_input_item.corporate_id";
       $arg_str .= " AND m_job_type.rntl_cont_no = m_input_item.rntl_cont_no";
       $arg_str .= " AND m_job_type.job_type_cd = m_input_item.job_type_cd)";
       $arg_str .= " INNER JOIN m_item";
       $arg_str .= " ON (m_input_item.corporate_id = m_item.corporate_id";
       $arg_str .= " AND m_input_item.item_cd = m_item.item_cd";
       $arg_str .= " AND m_input_item.color_cd = m_item.color_cd)";
       $arg_str .= " WHERE ";
       $arg_str .= $query;
       $arg_str .= ") as distinct_table";
       $arg_str .= " ORDER BY as_item_cd ASC, as_color_cd ASC";
       //ChromePhp::LOG($arg_str);
       $m_job_type = new MJobType();
       $item_results = new Resultset(null, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
       $result_obj = (array)$item_results;
       $results_cnt = $result_obj["\0*\0_count"];
       if (!empty($results_cnt)) {
         $paginator_model = new PaginatorModel(
             array(
                 "data"  => $item_results,
                 "limit" => 1,
                 "page" => 1
             )
         );
         $paginator = $paginator_model->getPaginate();
         $item_results = $paginator->items;
         //ChromePhp::LOG("発注情報トラン商品一覧仮リスト");
         //ChromePhp::LOG($results);
         foreach ($item_results as $item_result) {
           // name属性用カウント値
           $list["arr_num"] = $arr_num++;
           // No
           $list["list_no"] = $list_cnt++;
           // アイテム
           $list["item_name"] = $item_result->as_item_name;
           // 商品コード
           $list["item_cd"] = $item_result->as_item_cd;
           // 色コード
           $list["color_cd"] = $item_result->as_color_cd;
           // 選択方法
           //※着用者の職種マスタ.職種コードに紐づく投入商品マスタの職種アイテムコード単位で単一or複数判断
           $query_list = array();
           array_push($query_list, "m_input_item.corporate_id = '".$auth['corporate_id']."'");
           array_push($query_list, "m_input_item.job_type_cd = '".$wearer_other_post["job_type_cd"]."'");
           array_push($query_list, "m_input_item.item_cd = '".$list["item_cd"]."'");
           $query = implode(' AND ', $query_list);

           $arg_str = "";
           $arg_str = "SELECT ";
           $arg_str .= "m_input_item.job_type_item_cd";
           $arg_str .= " FROM ";
           $arg_str .= "m_input_item";
           $arg_str .= " INNER JOIN ";
           $arg_str .= "m_job_type";
           $arg_str .= " ON (m_input_item.corporate_id = m_job_type.corporate_id";
           $arg_str .= " AND m_input_item.rntl_cont_no = m_job_type.rntl_cont_no";
           $arg_str .= " AND m_input_item.job_type_cd = m_job_type.job_type_cd)";
           $arg_str .= " WHERE ";
           $arg_str .= $query;
           $arg_str .= " ORDER BY m_input_item.item_cd ASC, m_input_item.color_cd ASC";
           $m_input_item = new MInputItem();
           $m_input_item_results = new Resultset(NULL, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
           $result_obj = (array)$m_input_item_results;
           $results_cnt = $result_obj["\0*\0_count"];
           if ($results_cnt > 1) {
             $list["choice"] = "複数選択";
             $list["choice_type"] = "2";
           } else {
             $list["choice"] = "単一選択";
             $list["choice_type"] = "1";
           }
           // 標準枚数
           $list["std_input_qty"] = $item_result->as_std_input_qty;
           // 商品-色
           $list["item_and_color"] = $list["item_cd"]."-".$list["color_cd"];
           // 商品名
           $list["input_item_name"] = $item_result->as_input_item_name;
           // サイズ
           $list["size_cd"] = array();
           $element = array();
           $query_list = array();
           array_push($query_list, "m_item.item_cd = '".$list["item_cd"]."'");
           array_push($query_list, "m_item.color_cd = '".$list["color_cd"]."'");
           $query = implode(' AND ', $query_list);

           $arg_str = "";
           $arg_str = "SELECT ";
           $arg_str .= "size_cd";
           $arg_str .= " FROM ";
           $arg_str .= "m_item";
           $arg_str .= " WHERE ";
           $arg_str .= $query;
           $arg_str .= " ORDER BY item_cd ASC, color_cd ASC";
           $m_item = new MItem();
           $m_item_results = new Resultset(NULL, $m_item, $m_item->getReadConnection()->query($arg_str));
           $result_obj = (array)$m_item_results;
           $results_cnt = $result_obj["\0*\0_count"];
           $results_rows = $result_obj["\0*\0_rows"];
           //ChromePhp::LOG($results_rows);
           //ChromePhp::LOG($list["order_size_cd"]);
           if (!empty($results_cnt)) {
             $paginator_model = new PaginatorModel(
                 array(
                     "data"  => $m_item_results,
                     "limit" => $results_cnt,
                     "page" => 1
                 )
             );
             $paginator = $paginator_model->getPaginate();
             $m_item_results = $paginator->items;
             foreach ($m_item_results as $m_item_result) {
               $element["size"] = $m_item_result->size_cd;
               // 初期選択設定
               if ($element["size"] === $result->as_order_size_cd) {
                 $element["selected"] = "selected";
               } else {
                 $element["selected"] = "";
               }

               array_push($list["size_cd"], $element);
             }
           }
           // 発注枚数
           if (!empty($result->as_order_qty)) {
             $list["order_num"] = $result->as_order_qty;
           } else {
             $list["order_num"] = "";
           }
           // 発注枚数（単一選択=入力不可、複数選択=入力可）
           if ($list["choice_type"] == "1") {
             $list["order_num_disable"] = "disabled";
           } else {
             $list["order_num_disable"] = "";
           }
           // 返却枚数
           $list["return_num"] = "-";
           $list["return_num_disable"] = "disabled";

           //--その他の必要hiddenパラメータ--//
           // 部門コード
           $list["rntl_sect_cd"] = $wearer_other_post["rntl_sect_cd"];
           // 職種コード
           $list["job_type_cd"] = $wearer_other_post['job_type_cd'];
           // 職種アイテムコード
           $list["job_type_item_cd"] = $item_result->as_job_type_item_cd;

           array_push($all_list, $list);
         }
       }
     }
   } else {
     // 発注情報トランに情報が存在しない場合、こちらで商品一覧生成
     $all_list = array();
     $list = array();
     $query_list = array();
     array_push($query_list, "m_job_type.corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "m_job_type.rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
     array_push($query_list, "m_job_type.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
     $query = implode(' AND ', $query_list);

     $arg_str = "";
     $arg_str = "SELECT ";
     $arg_str .= " * ";
     $arg_str .= " FROM ";
     $arg_str .= "(SELECT distinct on (m_item.item_cd, m_item.color_cd, m_input_item.job_type_item_cd) ";
     $arg_str .= "m_item.item_cd as as_item_cd,";
     $arg_str .= "m_item.color_cd as as_color_cd,";
     $arg_str .= "m_item.size_cd as as_size_cd,";
     $arg_str .= "m_item.item_name as as_item_name,";
     $arg_str .= "m_input_item.job_type_item_cd as as_job_type_item_cd,";
     $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
     $arg_str .= "m_input_item.std_input_qty as as_std_input_qty";
     $arg_str .= " FROM ";
     $arg_str .= "m_job_type INNER JOIN m_input_item";
     $arg_str .= " ON (m_job_type.corporate_id = m_input_item.corporate_id";
     $arg_str .= " AND m_job_type.rntl_cont_no = m_input_item.rntl_cont_no";
     $arg_str .= " AND m_job_type.job_type_cd = m_input_item.job_type_cd)";
     $arg_str .= " INNER JOIN m_item";
     $arg_str .= " ON (m_input_item.corporate_id = m_item.corporate_id";
     $arg_str .= " AND m_input_item.item_cd = m_item.item_cd";
     $arg_str .= " AND m_input_item.color_cd = m_item.color_cd)";
     $arg_str .= " WHERE ";
     $arg_str .= $query;
     $arg_str .= ") as distinct_table";
     $arg_str .= " ORDER BY as_item_cd ASC, as_color_cd ASC";
     //ChromePhp::LOG($arg_str);
     $m_job_type = new MJobType();
     $results = new Resultset(null, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
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
         // name属性用カウント値
         $list["arr_num"] = $arr_num++;
         // No
         $list["list_no"] = $list_cnt++;
         // アイテム
         $list["item_name"] = $result->as_item_name;
         // 商品コード
         $list["item_cd"] = $result->as_item_cd;
         // 色コード
         $list["color_cd"] = $result->as_color_cd;
         // 選択方法
         //※着用者の職種マスタ.職種コードに紐づく投入商品マスタの職種アイテムコード単位で単一or複数判断
         $query_list = array();
         array_push($query_list, "m_input_item.corporate_id = '".$auth['corporate_id']."'");
         array_push($query_list, "m_input_item.job_type_cd = '".$wearer_other_post["job_type_cd"]."'");
         array_push($query_list, "m_input_item.item_cd = '".$list["item_cd"]."'");
         $query = implode(' AND ', $query_list);

         $arg_str = "";
         $arg_str = "SELECT ";
         $arg_str .= "m_input_item.job_type_item_cd";
         $arg_str .= " FROM ";
         $arg_str .= "m_input_item";
         $arg_str .= " INNER JOIN ";
         $arg_str .= "m_job_type";
         $arg_str .= " ON (m_input_item.corporate_id = m_job_type.corporate_id";
         $arg_str .= " AND m_input_item.rntl_cont_no = m_job_type.rntl_cont_no";
         $arg_str .= " AND m_input_item.job_type_cd = m_job_type.job_type_cd)";
         $arg_str .= " WHERE ";
         $arg_str .= $query;

         $m_input_item = new MInputItem();
         $m_input_item_results = new Resultset(NULL, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
         $result_obj = (array)$m_input_item_results;
         $results_cnt = $result_obj["\0*\0_count"];
         if ($results_cnt > 1) {
           $list["choice"] = "複数選択";
           $list["choice_type"] = "2";
         } else {
           $list["choice"] = "単一選択";
           $list["choice_type"] = "1";
         }
         // 標準枚数
         $list["std_input_qty"] = $result->as_std_input_qty;
         // 商品-色
         $list["item_and_color"] = $list["item_cd"]."-".$list["color_cd"];
         // 商品名
         $list["input_item_name"] = $result->as_input_item_name;
         // サイズ
         $list["size_cd"] = array();
         $element = array();
         $query_list = array();
         array_push($query_list, "m_item.item_cd = '".$list["item_cd"]."'");
         array_push($query_list, "m_item.color_cd = '".$list["color_cd"]."'");
         $query = implode(' AND ', $query_list);
         $arg_str = "";
         $arg_str = "SELECT ";
         $arg_str .= "size_cd";
         $arg_str .= " FROM ";
         $arg_str .= "m_item";
         $arg_str .= " WHERE ";
         $arg_str .= $query;
         $m_item = new MItem();
         $m_item_results = new Resultset(NULL, $m_item, $m_item->getReadConnection()->query($arg_str));
         $result_obj = (array)$m_item_results;
         $results_cnt = $result_obj["\0*\0_count"];
         if (!empty($results_cnt)) {
           $paginator_model = new PaginatorModel(
               array(
                   "data"  => $m_item_results,
                   "limit" => $results_cnt,
                   "page" => 1
               )
           );
           $paginator = $paginator_model->getPaginate();
           $m_item_results = $paginator->items;
           //ChromePhp::LOG($results);
           foreach ($m_item_results as $m_item_result) {
             $element["size"] = $m_item_result->size_cd;
             $element["selected"] = "";
             array_push($list["size_cd"], $element);
           }
         }
         // 発注枚数
         $list["order_num"] = $result->as_std_input_qty;
         // 発注枚数（単一選択=入力不可、複数選択=入力可）
         if ($list["choice_type"] == "1") {
           $list["order_num_disable"] = "disabled";
         } else {
           $list["order_num_disable"] = "";
         }
         // 返却枚数
         $list["return_num"] = "-";
         $list["return_num_disable"] = "disabled";

         //--その他の必要hiddenパラメータ--//
         // 部門コード
         $list["rntl_sect_cd"] = $wearer_other_post["rntl_sect_cd"];
         // 職種コード
         $list["job_type_cd"] = $wearer_other_post['job_type_cd'];
         // 職種アイテムコード
         $list["job_type_item_cd"] = $result->as_job_type_item_cd;

         array_push($all_list, $list);
       }
     }
   }

   // 発注総枚数
   $json_list["sum_num"] = array();
   $list["sum_order_num"] = '';
   if (!empty($all_list)) {
     $list["sum_order_num"] = 0;
     foreach ($all_list as $all_map) {
       $list["sum_order_num"] += $all_map["std_input_qty"];
     }
   }
   array_push($json_list["sum_num"], $list);

   // 商品リスト件数による一覧表示制御
   $json_list["list_disp_flg"] = true;
   if (count($all_list) == 0) {
     $json_list["list_disp_flg"] = false;
   }

   // 商品リスト件数、リスト内容
   $json_list["list_cnt"] = count($all_list);
   $json_list["item_list"] = $all_list;

   echo json_encode($json_list);
   //ChromePhp::LOG('JSON_LIST');
   //ChromePhp::LOG($json_list);
});

/**
 * 発注入力（追加貸与）
 * 発注取消処理
 */
$app->post('/wearer_add/delete', function ()use($app){
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
      // 発注状況区分(貸与)
      array_push($query_list,"m_wearer_std_tran.order_sts_kbn = '1'");
      // 着用者状況区分(その他（着用開始）)
      array_push($query_list,"m_wearer_std_tran.werer_sts_kbn = '7'");
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

    //--発注情報トラン削除--//
    //ChromePhp::LOG("発注情報トラン削除");
    $query_list = array();
    array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_order_tran.order_req_no = '".$cond['order_req_no']."'");
    // 発注区分「貸与」
    array_push($query_list, "t_order_tran.order_sts_kbn = '1'");
    // 理由区分「追加貸与」系ステータス
    array_push($query_list, "t_order_tran.order_reason_kbn = '03'");
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

    // トランザクションコミット
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
  } catch (Exception $e) {
    // トランザクションロールバック
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));

    $json_list["error_code"] = "1";
    //ChromePhp::LOG("発注取消処理エラー");
    //ChromePhp::LOG($e);

    echo json_encode($json_list);
    return;
  }

  //ChromePhp::LOG("発注取消処理コード");
  //ChromePhp::LOG($json_list["error_code"]);
  echo json_encode($json_list);
});

/**
 * 発注入力（追加貸与）
 * 入力完了処理
 */
$app->post('/wearer_add/complete', function ()use($app){
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
     }
     // コメント欄
     if (mb_strlen($wearer_data_input['comment']) > 0) {
       if (strlen(mb_convert_encoding($wearer_data_input['comment'], "SJIS")) > 100) {
         $json_list["error_code"] = "1";
         $error_msg = "コメント欄の規定文字数がオーバーしています。";
         array_push($json_list["error_msg"], $error_msg);
       }
     }
     // 発注商品一覧
     foreach ($item_list as $item_map) {
       // 発注枚数フォーマットチェック(複数選択商品)
       if (empty($item_map["order_num_disable"])) {
         if (!empty($item_map["order_num"])) {
           if (!ctype_digit(strval($item_map["order_num"]))) {
             if (empty($order_num_format_err)) {
               $order_num_format_err = "err";
               $json_list["error_code"] = "1";
               $error_msg = "発注商品一覧の発注枚数には半角数字を入力してください。";
               array_push($json_list["error_msg"], $error_msg);
             }
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
     array_push($query_list, "rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
     array_push($query_list, "job_type_cd = '".$wearer_other_post['job_type_cd']."'");
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
     //--発注NGパターンチェック-- ここまで//

     // 着用者基本マスタ参照
     $query_list = array();
     array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
     array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
     array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
     // 発注状況区分(貸与)
     array_push($query_list,"m_wearer_std_tran.order_sts_kbn = '1'");
     // 着用者状況区分(その他（着用開始）)
     array_push($query_list,"m_wearer_std_tran.werer_sts_kbn = '7'");
     // 理由区分
     array_push($query_list,"t_order_tran.order_reason_kbn = '03'");
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
         // 発注情報トランのデータがある場合
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
         // 発注状況区分(貸与)
         array_push($src_query_list,"order_sts_kbn = '1'");
         // 着用者状況区分(その他（着用開始）)
         array_push($src_query_list,"werer_sts_kbn = '7'");
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
         // 着用者状況区分(その他（着用開始）)
         array_push($up_query_list, "werer_sts_kbn = '7'");
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
         // 発注状況区分(貸与)
         if ($order_sts_kbn !== "6") {
           array_push($up_query_list, "order_sts_kbn = '1'");
         }
         // 更新区分(WEB発注システム(新規登録）)
         array_push($up_query_list, "upd_kbn = '1'");
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
         // 着用者状況区分(その他（着用開始）)
         array_push($calum_list, "werer_sts_kbn");
         array_push($values_list, "'7'");
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
         // 発注状況区分(貸与)
         array_push($calum_list, "order_sts_kbn");
         array_push($values_list, "'1'");
         // 更新区分(WEB発注システム(新規登録))
         array_push($calum_list, "upd_kbn");
         array_push($values_list, "'1'");
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
       if (!empty($item_list)) {
         $cnt = 1;
         // 現在の追加貸与発注の情報をクリーン
         //ChromePhp::LOG("発注情報トランクリーン");
         $query_list = array();
         array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
         array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
         array_push($query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
         // 発注状況区分「貸与」
         array_push($query_list, "order_sts_kbn = '1'");
         // 理由区分「追加貸与」
         array_push($query_list, "order_reason_kbn = '03'");
         // 着用者状況区分「その他（着用開始）」
         array_push($query_list, "werer_sts_kbn = '7'");
         $query = implode(' AND ', $query_list);
         $arg_str = "";
         $arg_str = "DELETE FROM ";
         $arg_str .= "t_order_tran";
         $arg_str .= " WHERE ";
         $arg_str .= $query;
         $t_order_tran = new TOrderTran();
         $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
         $results_cnt = $result_obj["\0*\0_count"];

         // 発注商品一覧内容登録
         foreach ($item_list as $item_map) {
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
           // 発注状況区分(貸与)
           array_push($calum_list, "order_sts_kbn");
           array_push($values_list, "'1'");
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
           array_push($values_list, "'".$item_map['order_num']."'");
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
           // 着用者状況区分(その他（着用開始）)
           array_push($calum_list, "werer_sts_kbn");
           array_push($values_list, "'7'");
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

      // トランザクションコミット
      $m_wearer_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
    } catch (Exception $e) {
      // トランザクションロールバック
      $m_wearer_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));
      ChromePhp::LOG($e);

      $json_list["error_code"] = "1";
      $error_msg = "入力登録処理において、データ更新エラーが発生しました。";
      array_push($json_list["error_msg"], $error_msg);

      echo json_encode($json_list);
      return;
    }

    echo json_encode($json_list);
  }
});

/**
 * 発注入力（追加貸与）
 * 発注送信処理
 */
$app->post('/wearer_add/send', function ()use($app){
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
    }
    // コメント欄
    if (mb_strlen($wearer_data_input['comment']) > 0) {
      if (strlen(mb_convert_encoding($wearer_data_input['comment'], "SJIS")) > 100) {
        $json_list["error_code"] = "1";
        $error_msg = "コメント欄の規定文字数がオーバーしています。";
        array_push($json_list["error_msg"], $error_msg);
      }
    }
    // 発注商品一覧
    foreach ($item_list as $item_map) {
      // 発注枚数フォーマットチェック
      if (empty($item_map["order_num_disable"])) {
        if (!ctype_digit(strval($item_map["order_num"]))) {
          if (empty($order_num_format_err)) {
            $order_num_format_err = "err";
            $json_list["error_code"] = "1";
            $error_msg = "発注商品一覧の発注枚数には半角数字を入力してください。";
            array_push($json_list["error_msg"], $error_msg);
          }
        }
      }
    }

    echo json_encode($json_list);
  } else if ($mode == "update") {
/*
ChromePhp::LOG("着用者入力");
ChromePhp::LOG($wearer_data_input);
ChromePhp::LOG("発注商品一覧");
ChromePhp::LOG($item_input);
*/
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
    array_push($query_list, "rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_other_post['job_type_cd']."'");
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
    //--発注NGパターンチェック-- ここまで//

    // 着用者基本マスタ参照
    $query_list = array();
    array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_other_post['werer_cd']."'");
    array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_other_post['rntl_sect_cd']."'");
    array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_other_post['job_type_cd']."'");
    // 発注状況区分(貸与)
    array_push($query_list,"m_wearer_std_tran.order_sts_kbn = '1'");
    // 着用者状況区分(その他（着用開始）)
    array_push($query_list,"m_wearer_std_tran.werer_sts_kbn = '7'");
    // 理由区分
    array_push($query_list,"t_order_tran.order_reason_kbn = '03'");
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
        // 発注状況区分(貸与)
        array_push($src_query_list,"order_sts_kbn = '1'");
        // 着用者状況区分(その他（着用開始）)
        array_push($src_query_list,"werer_sts_kbn = '7'");
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
        // 着用者状況区分(その他（着用開始）)
        array_push($up_query_list, "werer_sts_kbn = '7'");
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
        // 発注状況区分(貸与)
        if ($order_sts_kbn !== "6") {
          array_push($up_query_list, "order_sts_kbn = '1'");
        }
        // 更新区分(WEB発注システム(新規登録）)
        array_push($up_query_list, "upd_kbn = '1'");
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
        // 着用者状況区分(その他（着用開始）)
        array_push($calum_list, "werer_sts_kbn");
        array_push($values_list, "'7'");
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
        // 発注状況区分(貸与)
        array_push($calum_list, "order_sts_kbn");
        array_push($values_list, "'1'");
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
        // 現在の追加貸与発注の情報をクリーン
        //ChromePhp::LOG("発注情報トランクリーン");
        $query_list = array();
        array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "rntl_cont_no = '".$wearer_other_post['rntl_cont_no']."'");
        array_push($query_list, "werer_cd = '".$wearer_other_post['werer_cd']."'");
        // 発注状況区分「貸与」
        array_push($query_list, "order_sts_kbn = '1'");
        // 理由区分「追加貸与」
        array_push($query_list, "order_reason_kbn = '03'");
        // 着用者状況区分「その他（着用開始）」
        array_push($query_list, "werer_sts_kbn = '7'");
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
          // 発注状況区分(貸与)
          array_push($calum_list, "order_sts_kbn");
          array_push($values_list, "'1'");
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
          array_push($values_list, "'".$item_map['order_num']."'");
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
          // 着用者状況区分(その他（着用開始）)
          array_push($calum_list, "werer_sts_kbn");
          array_push($values_list, "'7'");
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
