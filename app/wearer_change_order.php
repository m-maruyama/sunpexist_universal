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
 * 入力項目：現在貸与中のアイテム、新たに追加するアイテム
 */
 $app->post('/wearer_change_list', function ()use($app){
   $params = json_decode(file_get_contents("php://input"), true);

   // アカウントセッション取得
   $auth = $app->session->get("auth");
   //ChromePhp::LOG($auth);

   // 前画面セッション取得
   $wearer_chg_post = $app->session->get("wearer_chg_post");
   //ChromePhp::LOG($wearer_chg_post);

   // フロントパラメータ取得
   $cond = $params['data'];
   //ChromePhp::LOG("フロント側パラメータ");
   //ChromePhp::LOG($cond);

   $json_list = array();

   //--一覧生成用の主要部門コード・職種コード取得--//
   // 着用者基本マスタ参照
   $query_list = array();
   array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
   array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
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
   $results = new Resultset(null, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   $m_wearer_cnt = $results_cnt;
   $m_wearer_rntl_sect_cd = null;
   $m_wearer_job_type_cd = null;
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
   array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
   array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
   array_push($query_list, "rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
   array_push($query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
   array_push($query_list, "order_req_no = '".$wearer_chg_post['order_req_no']."'");
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
   $results = new Resultset(null, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   $t_order_tran_cnt = $results_cnt;
   $t_order_tran_rntl_sect_cd = null;
   $t_order_tran_job_type_cd = null;
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
   array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
   array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
   array_push($query_list, "rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
   array_push($query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
   array_push($query_list, "order_req_no = '".$wearer_chg_post['order_req_no']."'");
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
   $results = new Resultset(null, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
   $result_obj = (array)$results;
   $results_cnt = $result_obj["\0*\0_count"];
   //ChromePhp::LOG($results_cnt);

   $t_returned_plan_info_tran_cnt = $results_cnt;
   $t_returned_plan_info_tran_rntl_sect_cd = null;
   $t_returned_plan_info_tran_job_type_cd = null;
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
   $chg_wearer_rntl_sect_cd = null;
   if (!empty($t_order_tran_rntl_sect_cd)) {
     $chg_wearer_rntl_sect_cd = $t_order_tran_rntl_sect_cd;
   } elseif (!empty($t_returned_plan_info_tran_rntl_sect_cd)) {
     $chg_wearer_rntl_sect_cd = $t_returned_plan_info_tran_rntl_sect_cd;
   }
   // 職種コード
   $chg_wearer_job_type_cd = null;
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
   array_push($query_list, "mw.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
   array_push($query_list, "mw.werer_cd = '".$wearer_chg_post['werer_cd']."'");
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
   $results = new Resultset(null, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
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
   array_push($query_list, "mw.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
   array_push($query_list, "mw.werer_cd = '".$wearer_chg_post['werer_cd']."'");
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
   $results = new Resultset(null, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
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
       $results = new Resultset(null, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
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
       $results = new Resultset(null, $m_item, $m_item->getReadConnection()->query($arg_str));
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
       $results = new Resultset(null, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
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
       array_push($query_list, "m_wearer_item.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
       array_push($query_list, "m_wearer_item.werer_cd = '".$wearer_chg_post['werer_cd']."'");
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
       $results = new Resultset(null, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
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
/*
       if (!$orverlap) {
         $list["individual_disp"] = true;
       }
*/
       // ※個体管理番号リスト、対象チェックボックス値の生成
       $list["individual_ctrl_no"] = "";
       $list["individual_chk"] = array();
       $individual_ctrl_no = array();
       $query_list = array();
       array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
       array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
       array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_chg_post['werer_cd']."'");
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
       $results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
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
           array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
           array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
           array_push($query_list, "t_returned_plan_info_tran.order_req_no = '".$wearer_chg_post["order_req_no"]."'");
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
           $results = new Resultset(null, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
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
       $list["return_num"] = $list["std_input_qty"];
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
   ChromePhp::LOG('JSON_LIST');
   ChromePhp::LOG($json_list);
});
