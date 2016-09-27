<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 発注入力（職種変更または異動）
 * 入力項目：契約No
 */
$app->post('/agreement_no_change', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    //ChromePhp::LOG($auth);

    // 前画面セッション取得
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    //ChromePhp::LOG($wearer_chg_post);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // 契約マスタ. 企業ID
    array_push($query_list, "m_contract.corporate_id = '".$auth['corporate_id']."'");
    // 契約マスタ. レンタル契約フラグ
    array_push($query_list, "m_contract.rntl_cont_flg = '1'");
    // 契約リソースマスタ. 企業ID
    array_push($query_list, "m_contract_resource.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ.企業ID
    array_push($query_list, "m_account.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ. ユーザーID
    array_push($query_list, "m_account.user_id = '".$auth['user_id']."'");

    $query = implode(' AND ', $query_list);

    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= '(SELECT distinct on (m_contract.rntl_cont_no) ';
    $arg_str .= 'm_contract.rntl_cont_no as as_rntl_cont_no,';
    $arg_str .= 'm_contract.rntl_cont_name as as_rntl_cont_name';
    $arg_str .= ' FROM m_contract LEFT JOIN';
    $arg_str .= ' (m_contract_resource INNER JOIN m_account ON m_contract_resource.accnt_no = m_account.accnt_no)';
    $arg_str .= ' ON m_contract.corporate_id = m_contract_resource.corporate_id';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ') as distinct_table';
    $arg_str .= ' ORDER BY as_rntl_cont_no asc';

    $m_contract = new MContract();
    $results = new Resultset(null, $m_contract, $m_contract->getReadConnection()->query($arg_str));
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
        $list['rntl_cont_no'] = $result->as_rntl_cont_no;
        $list['rntl_cont_name'] = $result->as_rntl_cont_name;
        if ($list['rntl_cont_no'] == $wearer_chg_post['rntl_cont_no']) {
          $list['selected'] = 'selected';
        } else {
          $list['selected'] = '';
        }

        array_push($all_list, $list);
      }
    } else {
      $list['rntl_cont_no'] = null;
      $list['rntl_cont_name'] = '';
      $list['selected'] = '';
      array_push($all_list, $list);
    }

    $json_list['agreement_no_list'] = $all_list;
    echo json_encode($json_list);
});

/**
 * 発注入力（職種変更または異動）
 * 入力項目：理由区分
 */
$app->post('/reason_kbn_change', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    //ChromePhp::LOG($wearer_chg_post);

    //--発注管理単位取得--//
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    array_push($query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
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
    //ChromePhp::LOG($order_control_unit);

    //--理由区分リスト取得--//
    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    array_push($query_list, "cls_cd = '002'");
    array_push($query_list, "relation_cls_cd = '001'");
    array_push($query_list, "relation_gen_cd = '5'");
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

      foreach ($results as $result) {
        $list['reason_kbn'] = $result->gen_cd;
        $list['reason_kbn_name'] = $result->gen_name;

        // 発注情報トランフラグ有の場合は初期選択状態版を生成
        if ($wearer_chg_post['order_tran_flg'] == '1') {
          if ($list['reason_kbn'] == $wearer_chg_post['order_reason_kbn']) {
            $list['selected'] = 'selected';
          } else {
            $list['selected'] = '';
          }
        } else {
          $list['selected'] = '';
        }

        array_push($all_list, $list);
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
 * 発注入力（職種変更または異動）着用者情報
 * 入力項目：性別
 */
$app->post('/sex_kbn_change', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    //ChromePhp::LOG($auth);

    // 前画面セッション取得
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    //ChromePhp::LOG($wearer_chg_post);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

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

      foreach ($results as $result) {
        $list['sex_kbn'] = $result->gen_cd;
        $list['sex_kbn_name'] = $result->gen_name;

        // 発注情報トランフラグ有の場合は初期選択状態版を生成
        if ($wearer_chg_post['order_tran_flg'] == '1') {
          if ($list['sex_kbn'] == $wearer_chg_post['sex_kbn']) {
            $list['selected'] = 'selected';
          } else {
            $list['selected'] = '';
          }
        } else {
          $list['selected'] = '';
        }

        array_push($all_list, $list);
      }
    } else {
      $list['sex_kbn'] = null;
      $list['sex_kbn_name'] = '';
      $list['selected'] = '';
      array_push($all_list, $list);
    }

    $json_list['sex_kbn_list'] = $all_list;
    echo json_encode($json_list);
});

/**
 * 発注入力（職種変更または異動）着用者情報
 * 入力項目：拠点
 */
$app->post('/section_change', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    //ChromePhp::LOG($auth);

    // 前画面セッション取得
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    //ChromePhp::LOG($wearer_chg_post);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (rntl_sect_cd) *';
    $arg_str .= ' FROM m_section';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY rntl_sect_cd asc';

    $m_section = new MSection();
    $results = new Resultset(null, $m_section, $m_section->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

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
        $list['rntl_sect_cd'] = $result->rntl_sect_cd;
        $list['rntl_sect_name'] = $result->rntl_sect_name;

        // 発注情報トランフラグ有の場合は初期選択状態版を生成
        if ($wearer_chg_post['order_tran_flg'] == '1') {
          if ($list['rntl_sect_cd'] == $wearer_chg_post['rntl_sect_cd']) {
            $list['selected'] = 'selected';
          } else {
            $list['selected'] = '';
          }
        } else {
          $list['selected'] = '';
        }

        array_push($all_list, $list);
      }
    } else {
      $list['rntl_sect_cd'] = null;
      $list['rntl_sect_name'] = '';
      $list['selected'] = '';
      array_push($all_list, $list);
    }

    $json_list['section_list'] = $all_list;
    echo json_encode($json_list);
});

/**
 * 発注入力（職種変更または異動）着用者情報
 * 入力項目：貸与パターン
 */
$app->post('/job_type_change', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    //ChromePhp::LOG($auth);

    // 前画面セッション取得
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    //ChromePhp::LOG($wearer_chg_post);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (job_type_cd) *';
    $arg_str .= ' FROM m_job_type';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY job_type_cd asc';

    $m_job_type = new MJobType();
    $results = new Resultset(null, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

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
        $list['job_type_cd'] = $result->job_type_cd;
        $list['job_type_name'] = $result->job_type_name;
        $list['sp_job_type_flg'] = $result->sp_job_type_flg;

        // 発注情報トランフラグ有の場合は初期選択状態版を生成
        if ($wearer_chg_post['order_tran_flg'] == '1') {
          if ($list['job_type_cd'] == $wearer_chg_post['job_type_cd']) {
            $list['selected'] = 'selected';
          } else {
            $list['selected'] = '';
          }
        } else {
          $list['selected'] = '';
        }

        array_push($all_list, $list);
      }
    } else {
      $list['job_type_cd'] = null;
      $list['job_type_name'] = '';
      $list['sp_job_type_flg'] = '0';
      $list['selected'] = '';
      array_push($all_list, $list);
    }

    $json_list['job_type_list'] = $all_list;
    echo json_encode($json_list);
});

/**
 * 発注入力（職種変更または異動）着用者情報
 * 入力項目：出荷先(郵便番号、住所込み)
 */
$app->post('/shipment_change', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    //ChromePhp::LOG($auth);

    // 前画面セッション取得
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    //ChromePhp::LOG($wearer_chg_post);

    // フロント側からの取得パラメータ
    $cond = $params["data"];
    //ChromePhp::LOG($cond);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    //--出荷先選択ボックス生成--//
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (ship_to_cd,ship_to_brnch_cd) *';
    $arg_str .= ' FROM m_shipment_to';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY ship_to_cd asc,ship_to_brnch_cd asc';

    $m_shipment_to = new MShipmentTo();
    $results = new Resultset(null, $m_shipment_to, $m_shipment_to->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

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
        $list['ship_to_cd'] = $result->ship_to_cd;
        $list['ship_to_brnch_cd'] = $result->ship_to_brnch_cd;
        $list['cust_to_brnch_name1'] = $result->cust_to_brnch_name1;
        $list['cust_to_brnch_name2'] = $result->cust_to_brnch_name2;
        $list['zip_no'] = $result->zip_no;
        $list['address1'] = $result->address1;
        $list['address2'] = $result->address2;
        $list['address3'] = $result->address3;
        $list['address4'] = $result->address4;

        if (!empty($cond["chg_flg"])) {
          // 「出荷先コード」変更時の生成
          if ($list['ship_to_cd'] == $cond['ship_to_cd'] && $list['ship_to_brnch_cd'] == $cond['ship_to_brnch_cd']) {
            $list['selected'] = 'selected';
          } else {
            $list['selected'] = '';
          }
        } else {
          // 初期遷移時、発注情報トランフラグ有の場合は初期選択状態版を生成
          if ($wearer_chg_post['order_tran_flg'] == '1') {
            if ($list['ship_to_cd'] == $wearer_chg_post['ship_to_cd'] && $list['ship_to_brnch_cd'] == $wearer_chg_post['ship_to_brnch_cd']) {
              $list['selected'] = 'selected';
            } else {
              $list['selected'] = '';
            }
          } else {
            $list['selected'] = '';
          }
        }

        array_push($all_list, $list);
      }
    } else {
      $list['ship_to_cd'] = '';
      $list['ship_to_brnch_cd'] = '';
      $list['cust_to_brnch_name1'] = '';
      $list['cust_to_brnch_name2'] = '';
      $list['selected'] = '';
      array_push($all_list, $list);
    }
    $json_list['shipment_list'] = $all_list;
    //ChromePhp::LOG($json_list['shipment_list']);

    // 表示する対象支店の郵便番号、住所を設定
    $post_address = array();
    for ($i=0; count($all_list)>$i; $i++) {
      if (!empty($all_list[$i]['selected'])) {
        $post_address = array(
          'zip_no' => preg_replace('/^(\d{3})(\d{4})$/', '$1-$2', $all_list[$i]['zip_no']),
          'address1' => $all_list[$i]['address1'],
          'address2' => $all_list[$i]['address2'],
          'address3' => $all_list[$i]['address3'],
          'address4' => $all_list[$i]['address4']
        );
        $post_address_list = array();
        array_push($post_address_list, $post_address);
      }
    }
    if (empty($post_address)) {
      $post_address = array(
        'zip_no' => '',
        'address1' => '',
        'address2' => '',
        'address3' => '',
        'address4' => ''
      );
      $post_address_list = array();
      array_push($post_address_list, $post_address);
    }
    $json_list['post_address'] = $post_address_list;
    //ChromePhp::LOG($json_list['post_address']);

    echo json_encode($json_list);
});

/**
 * 発注入力（職種変更または異動）
 * 入力項目：社員コード、着用者名、着用者名（かな）
 */
$app->post('/wearer_info', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    //ChromePhp::LOG($auth);

    // 前画面セッション取得
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    //ChromePhp::LOG($wearer_chg_post);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    if ($wearer_chg_post['wearer_tran_flg'] == '1') {
      //--着用者基本マスタトラン有の場合--//
      array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
      array_push($query_list,"m_wearer_std_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
      array_push($query_list,"m_wearer_std_tran.cster_emply_cd = '".$wearer_chg_post['cster_emply_cd']."'");
      $query = implode(' AND ', $query_list);

      $arg_str = "";
      $arg_str = "SELECT ";
      $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
      $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
      $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana,";
      $arg_str .= "m_wearer_std_tran.appointment_ymd as as_appointment_ymd";
      $arg_str .= " FROM ";
      $arg_str .= "m_wearer_std_tran";
      $arg_str .= " WHERE ";
      $arg_str .= $query;
      $arg_str .= " ORDER BY m_wearer_std_tran.upd_date DESC";

      $m_weare_std_tran = new MWearerStdTran();
      $results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
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
          // 社員コード
          $list['cster_emply_cd'] = $result->as_cster_emply_cd;
          // 着用者名
          $list['werer_name'] = $result->as_werer_name;
          // 着用者名（読み仮名）
          $list['werer_name_kana'] = $result->as_werer_name_kana;
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

      $json_list['wearer_info'] = $all_list;
    } elseif ($wearer_chg_post['wearer_tran_flg'] == '0') {
      //--着用者基本マスタトラン無の場合--//
      array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "m_wearer_std.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
      array_push($query_list,"m_wearer_std.werer_cd = '".$wearer_chg_post['werer_cd']."'");
      array_push($query_list,"m_wearer_std.cster_emply_cd = '".$wearer_chg_post['cster_emply_cd']."'");
      $query = implode(' AND ', $query_list);

      $arg_str = "";
      $arg_str = "SELECT ";
      $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
      $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
      $arg_str .= "m_wearer_std.werer_name_kana as as_werer_name_kana,";
      $arg_str .= "m_wearer_std.appointment_ymd as as_appointment_ymd";
      $arg_str .= " FROM ";
      $arg_str .= "m_wearer_std";
      $arg_str .= " WHERE ";
      $arg_str .= $query;
      $arg_str .= " ORDER BY m_wearer_std.upd_date DESC";

      $m_weare_std = new MWearerStd();
      $results = new Resultset(null, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
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
          // 社員コード
          $list['cster_emply_cd'] = $result->as_cster_emply_cd;
          // 着用者名
          $list['werer_name'] = $result->as_werer_name;
          // 着用者名（読み仮名）
          $list['werer_name_kana'] = $result->as_werer_name_kana;
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

      $json_list['wearer_info'] = $all_list;
    }

    echo json_encode($json_list);
});

/**
 * 発注入力（職種変更または異動）
 * 入力項目：現在貸与中のアイテム
 */
 $app->post('/wearer_change_list', function ()use($app){
   $params = json_decode(file_get_contents("php://input"), true);

   // アカウントセッション取得
   $auth = $app->session->get("auth");
   //ChromePhp::LOG($auth);

   // 前画面セッション取得
   $wearer_chg_post = $app->session->get("wearer_chg_post");
   //ChromePhp::LOG($wearer_chg_post);

   $json_list = array();

   //--一覧生成用の主要職種コードの設定--//
   // 着用者基本マスタ参照
   $query_list = array();
   array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "m_wearer_std.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
   array_push($query_list, "m_wearer_std.werer_cd = '".$wearer_chg_post['werer_cd']."'");
   array_push($query_list, "m_wearer_std.werer_sts_kbn = '1'");
   $query = implode(' AND ', $query_list);

   $arg_str = "";
   $arg_str = "SELECT ";
   $arg_str .= "m_wearer_std.rntl_sect_cd as as_rntl_sect_cd,";
   $arg_str .= "m_wearer_std.job_type_cd as as_job_type_cd";
   $arg_str .= " FROM ";
   $arg_str .= "m_wearer_std";
   $arg_str .= " WHERE ";
   $arg_str .= $query;
   $arg_str .= " ORDER BY m_wearer_std.upd_date DESC";

   $m_weare_std = new MWearerStd();
   $results = new Resultset(null, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   $m_wearer_cnt = $results_cnt;
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
       // 着用者基本マスタ.レンタル部門コード
       $m_wearer_rntl_sect_cd = $result->as_rntl_sect_cd;
       // 着用者基本マスタ.職種コード
       $m_wearer_job_type_cd = $result->as_job_type_cd;
     }
   }

   // 発注情報トラン参照
   $query_list = array();
   array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
   array_push($query_list, "t_order_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
   array_push($query_list, "t_order_tran.rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
   array_push($query_list, "t_order_tran.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
   $query = implode(' AND ', $query_list);

   $arg_str = "";
   $arg_str = "SELECT ";
   $arg_str .= "t_order_tran.rntl_sect_cd as as_rntl_sect_cd,";
   $arg_str .= "t_order_tran.job_type_cd as as_job_type_cd";
   $arg_str .= " FROM ";
   $arg_str .= "t_order_tran";
   $arg_str .= " WHERE ";
   $arg_str .= $query;
   $arg_str .= " ORDER BY t_order_tran.upd_date DESC";

   $t_order_tran = new TOrderTran();
   $results = new Resultset(null, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   $t_order_tran_cnt = $results_cnt;
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
       $t_order_tran_rntl_sect_cd = $result->as_rntl_sect_cd;
       // 発注情報トラン.職種コード
       $t_order_tran_job_type_cd = $result->as_job_type_cd;
     }
   }

   // 返却予定情報トラン参照
   $query_list = array();
   array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
   array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
   array_push($query_list, "t_returned_plan_info_tran.rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
   array_push($query_list, "t_returned_plan_info_tran.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
   $query = implode(' AND ', $query_list);

   $arg_str = "";
   $arg_str = "SELECT ";
   $arg_str .= "t_returned_plan_info_tran.rntl_sect_cd as as_rntl_sect_cd,";
   $arg_str .= "t_returned_plan_info_tran.job_type_cd as as_job_type_cd";
   $arg_str .= " FROM ";
   $arg_str .= "t_returned_plan_info_tran";
   $arg_str .= " WHERE ";
   $arg_str .= $query;
   $arg_str .= " ORDER BY t_returned_plan_info_tran.order_req_no DESC";

   $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
   $results = new Resultset(null, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   $t_returned_plan_info_tran_cnt = $results_cnt;
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
       $t_returned_plan_info_tran_rntl_sect_cd = $result->as_rntl_sect_cd;
       // 発注情報トラン.職種コード
       $t_returned_plan_info_tran_job_type_cd = $result->as_job_type_cd;
     }
   }

   echo json_encode($json_list);
});
