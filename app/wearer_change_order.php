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

    array_push($query_list, "m_contract.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "m_contract.rntl_cont_flg = '1'");
    array_push($query_list, "m_contract_resource.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "m_account.corporate_id = '".$auth['corporate_id']."'");
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
    $results = new Resultset(NULL, $m_contract, $m_contract->getReadConnection()->query($arg_str));
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
      $list['rntl_cont_no'] = NULL;
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

    // フロント側からの取得パラメータ
    $cond = $params["data"];
    //ChromePhp::LOG($cond);

    //--発注管理単位取得--//
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    if (!empty($cond["job_type_cd"])) {
      array_push($query_list, "job_type_cd = '".$cond['job_type_cd']."'");
    } else {
      array_push($query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
    }

    $query = implode(' AND ', $query_list);

    $arg_str = '';
    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_job_type';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;

    $m_job_type = new MJobType();
    $results = new Resultset(NULL, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
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
    $arg_str .= ' ORDER BY dsp_order ASC';
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
      $list['reason_kbn'] = NULL;
      $list['reason_kbn_name'] = '';
      $list['selected'] = '';
      array_push($all_list, $list);
    }

    $json_list['reason_kbn_list'] = $all_list;
    ///ChromePhp::LOG($json_list['reason_kbn_list']);
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
        if ($list['sex_kbn'] == $wearer_chg_post['sex_kbn']) {
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
    $results = new Resultset(NULL, $m_section, $m_section->getReadConnection()->query($arg_str));
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
        // 初期選択状態版を生成
        if ($list['rntl_sect_cd'] == $wearer_chg_post['rntl_sect_cd']) {
          $list['selected'] = 'selected';
        } else {
          $list['selected'] = '';
        }

        array_push($all_list, $list);
      }
    } else {
      $list['rntl_sect_cd'] = NULL;
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
    $results = new Resultset(NULL, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
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

        // 初期選択状態版を生成
        if ($list['job_type_cd'] == $wearer_chg_post['job_type_cd']) {
          $list['selected'] = 'selected';
        } else {
          $list['selected'] = '';
        }
        array_push($all_list, $list);
      }
    } else {
      $list['job_type_cd'] = NULL;
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

    $list = array();
    $all_list = array();
    $json_list = array();

    //--出荷先選択ボックス生成--//
    $query_list = array();
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
    $results = new Resultset(NULL, $m_shipment_to, $m_shipment_to->getReadConnection()->query($arg_str));
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

      // 拠点にあわせる選択肢
      $list['ship_to_cd'] = "0";
      $list['ship_to_brnch_cd'] = "0";
      $list['cust_to_brnch_name1'] = "拠点と同じ";
      $list['cust_to_brnch_name2'] = "";
      $list['selected'] = "";
      array_push($all_list, $list);

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

        //「出荷先」変更時
        if (!empty($cond["chg_flg"])) {
          if ($list['ship_to_cd'] == $cond['ship_to_cd'] && $list['ship_to_brnch_cd'] == $cond['ship_to_brnch_cd']) {
            $list['selected'] = 'selected';
          } else {
            $list['selected'] = '';
          }
        } else {
          // 初期遷移時は初期選択状態版を生成
          if ($list['ship_to_cd'] == $wearer_chg_post['ship_to_cd'] && $list['ship_to_brnch_cd'] == $wearer_chg_post['ship_to_brnch_cd']) {
            $list['selected'] = 'selected';
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

    //「支店店舗とおなじ」が選択されている場合
    if ($cond['ship_to_cd'] == "0" && $cond['ship_to_brnch_cd'] == "0") {
      // 部門マスタから標準出荷先、支店コード取得
      $query_list = array();
      array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
      array_push($query_list, "rntl_sect_cd = '".$cond['section']."'");
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
        $std_ship_to_cd = $result->std_ship_to_cd;
        $std_ship_to_brnch_cd = $result->std_ship_to_brnch_cd;
      }
    }

    $json_list['shipment_list'] = $all_list;
    //ChromePhp::LOG($json_list['shipment_list']);

    // 表示する対象支店の郵便番号、住所を設定
    $post_address = array();
    for ($i=0; count($all_list)>$i; $i++) {
      if ($i !== 0) {
        //「支店店舗とおなじ」が選択されている場合
        if ($cond['ship_to_cd'] == "0" && $cond['ship_to_brnch_cd'] == "0") {
          if ($all_list[$i]['ship_to_cd'] == $std_ship_to_cd && $all_list[$i]['ship_to_brnch_cd'] == $std_ship_to_brnch_cd) {
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
        } else {
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
 * 入力項目：社員コード、着用者名、着用者名（かな）、コメント欄
 * ※前画面セッション情報
 */
$app->post('/wearer_change/info', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    //ChromePhp::LOG($auth);

    // 前画面セッション取得
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    //ChromePhp::LOG($wearer_chg_post);

    $list = array();
    $all_list = array();
    $json_list = array();

    // 発注情報トラン参照
    $query_list = array();
    array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    array_push($query_list, "t_order_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
    array_push($query_list, "t_order_tran.rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
    array_push($query_list, "t_order_tran.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
    array_push($query_list, "t_order_tran.order_sts_kbn = '5'");
    $reason_kbns = array();
    array_push($reason_kbns, "t_order_tran.order_reason_kbn = '09'");
    array_push($reason_kbns, "t_order_tran.order_reason_kbn = '10'");
    array_push($reason_kbns, "t_order_tran.order_reason_kbn = '11'");
    array_push($reason_kbns, "t_order_tran.order_reason_kbn = '24'");
    $reason_kbns = implode(' OR ', $reason_kbns);
    array_push($query_list, "(".$reason_kbns.")");
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

    if ($wearer_chg_post['wearer_tran_flg'] == '1') {
      //--着用者基本マスタトラン有の場合--//
      $query_list = array();
      array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
      array_push($query_list,"m_wearer_std_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
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

      $m_weare_std_tran = new MWearerStdTran();
      $results = new Resultset(NULL, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
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
          // 異動日
          $list['resfl_ymd'] = $result->as_resfl_ymd;
          if (!empty($list['resfl_ymd'])) {
            $list['resfl_ymd'] = date('Y/m/d', strtotime($list['resfl_ymd']));
          } else {
            $list['resfl_ymd'] = '';
          }
        }

        array_push($all_list, $list);
      }

      $json_list['wearer_info'] = $all_list;
    } else if ($wearer_chg_post['wearer_tran_flg'] == '0') {
      //--着用者基本マスタトラン無の場合--//
      $query_list = array();
      array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "m_wearer_std.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
      array_push($query_list, "m_wearer_std.werer_cd = '".$wearer_chg_post['werer_cd']."'");
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

      $m_weare_std = new MWearerStd();
      $results = new Resultset(NULL, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
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
          // 異動日
          $list['resfl_ymd'] = $result->as_resfl_ymd;
          if (!empty($list['resfl_ymd'])) {
            $list['resfl_ymd'] = date('Y/m/d', strtotime($list['resfl_ymd']));
          } else {
            $list['resfl_ymd'] = '';
          }
        }

        array_push($all_list, $list);
      }

      $json_list['wearer_info'] = $all_list;
    }

    //--発注情報トラン・返却予定情報トラン内、「職種変更または異動」情報の有無確認--//
    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    array_push($query_list, "t_order_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
    array_push($query_list, "t_order_tran.rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
    array_push($query_list, "t_order_tran.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
    array_push($query_list, "t_order_tran.order_sts_kbn = '5'");
    $reason_kbns = array();
    array_push($reason_kbns, "t_order_tran.order_reason_kbn = '09'");
    array_push($reason_kbns, "t_order_tran.order_reason_kbn = '10'");
    array_push($reason_kbns, "t_order_tran.order_reason_kbn = '11'");
    array_push($reason_kbns, "t_order_tran.order_reason_kbn = '24'");
    $reason_kbns = implode(' OR ', $reason_kbns);
    array_push($query_list, "(".$reason_kbns.")");
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
        $app->session->set("wearer_chg_post.order_req_no", $json_list['order_req_no']);
        // 発注情報トランフラグ
        $json_list['order_tran_flg'] = "1";
      }
    } else {
      // 発注情報トラン.発注No
      $json_list['order_req_no'] = "";
      $app->session->set("wearer_chg_post.order_req_no", $json_list['order_req_no']);
      // 発注情報トランフラグ
      $json_list['order_tran_flg'] = "0";
    }
    // ※返却予定情報トラン参照
    $query_list = array();
    array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
    array_push($query_list, "t_returned_plan_info_tran.rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
    array_push($query_list, "t_returned_plan_info_tran.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
    array_push($query_list, "t_returned_plan_info_tran.order_sts_kbn = '5'");
    $reason_kbns = array();
    array_push($reason_kbns, "t_returned_plan_info_tran.order_reason_kbn = '09'");
    array_push($reason_kbns, "t_returned_plan_info_tran.order_reason_kbn = '10'");
    array_push($reason_kbns, "t_returned_plan_info_tran.order_reason_kbn = '11'");
    array_push($reason_kbns, "t_returned_plan_info_tran.order_reason_kbn = '24'");
    $reason_kbns = implode(' OR ', $reason_kbns);
    array_push($query_list, "(".$reason_kbns.")");
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
        $app->session->set("wearer_chg_post.return_req_no", $json_list['return_req_no']);
        // 返却予定情報トランフラグ
        $json_list['return_tran_flg'] = "1";
      }
    } else {
      // 返却予定情報トラン.発注No
      $json_list['return_req_no'] = "";
      $app->session->set("wearer_chg_post.return_req_no", $json_list['return_req_no']);
      // 返却予定情報トランフラグ
      $json_list['return_tran_flg'] = "0";
    }

    //--前画面セッション情報--//
    // レンタル契約No
    $json_list['rntl_cont_no'] = $wearer_chg_post["rntl_cont_no"];
    // 部門コード
    $json_list['rntl_sect_cd'] = $wearer_chg_post["rntl_sect_cd"];
    // 貸与パターン
    $json_list['job_type_cd'] = $wearer_chg_post["job_type_cd"];
    // 着用者コード
    $json_list['werer_cd'] = $wearer_chg_post["werer_cd"];
    // 着用者基本情報トランフラグ
    $json_list['wearer_tran_flg'] = $wearer_chg_post["wearer_tran_flg"];

    echo json_encode($json_list);
});

/**
 * 発注入力（職種変更または異動）
 * 入力項目：現在貸与中のアイテム、新たに追加するアイテム
 */
 $app->post('/wearer_change/list', function ()use($app){
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
   array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
   array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
   array_push($query_list, "rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
   array_push($query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
   array_push($query_list, "order_req_no = '".$wearer_chg_post['return_req_no']."'");
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
   if (!empty($cond["section"])) {
     // 着用者情報項目「貸与パターン」が変更された場合
     $chg_wearer_rntl_sect_cd = $cond["section"];
   } else {
     // 発注情報トラン情報がある場合（編集）
     if (!empty($t_order_tran_rntl_sect_cd)) {
       $chg_wearer_rntl_sect_cd = $t_order_tran_rntl_sect_cd;
     } elseif (!empty($t_returned_plan_info_tran_rntl_sect_cd)) {
       $chg_wearer_rntl_sect_cd = $t_returned_plan_info_tran_rntl_sect_cd;
     } else {
       // 発注情報トランがないAND初期遷移時のケースフラグ
       $first_flg = "1";
     }
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
     } else {
       // 発注情報トランがないAND初期遷移時のケースフラグ
       $first_flg = "1";
     }
   }
   //ChromePhp::LOG('【発注後】部門コード、職種コード');
   //ChromePhp::LOG($chg_wearer_rntl_sect_cd);
   //ChromePhp::LOG($chg_wearer_job_type_cd);

   //--【変更前】商品の取得--//
   $query_list = array();
   $list = array();
   $now_wearer_list = array();
   array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
   array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
   array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_chg_post['werer_cd']."'");
   array_push($query_list, "t_delivery_goods_state_details.rtn_ok_flg = '1'");
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
   $arg_str .= "m_input_item.size_two_cd as as_size_two_cd,";
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
   //ChromePhp::LOG($arg_str);
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
       $list["rntl_cont_no"] = $wearer_chg_post['rntl_cont_no'];
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
       // 数量
       $list["quantity"] = $result->as_quantity;
       // 返却予定数
       $list["return_plan_qty"] = $result->as_return_plan_qty;
       // 返却済数
       $list["returned_qty"] = $result->as_returned_qty;
       // 商品単位の返却可能枚数(所持枚数)
       $list["possible_num"] = $list["quantity"] - $list["return_plan_qty"] - $list["returned_qty"];

       array_push($now_wearer_list, $list);
     }
   }
/*
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
   $arg_str .= "(SELECT distinct on (mi.item_cd, mi.color_cd, mii.job_type_item_cd) ";
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
       $list["rntl_cont_no"] = $wearer_chg_post['rntl_cont_no'];
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

       array_push($now_wearer_list, $list);
     }
   }
*/
   //ChromePhp::LOG('【変更前】商品リスト');
   //ChromePhp::LOG(count($now_wearer_list));
   //ChromePhp::LOG($now_wearer_list);

   //--【変更後】商品の取得--//
   if ($wearer_chg_post['wearer_tran_flg'] == "1") {
     // 着用者基本マスタトランの情報がある場合
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
     $arg_str .= "(m_wearer_std_tran as mw INNER JOIN m_job_type as mj ON (mw.corporate_id=mj.corporate_id AND mw.rntl_cont_no=mj.rntl_cont_no AND mw.job_type_cd=mj.job_type_cd))";
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
         //$list["rntl_cont_no"] = $result->as_rntl_cont_no;
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
     } else {
       // 着用者基本マスタトランの情報がない場合
       $query_list = array();
       $list = array();
       $chg_wearer_list = array();

       array_push($query_list, "mi.corporate_id = '".$auth['corporate_id']."'");
       array_push($query_list, "mii.corporate_id = '".$auth['corporate_id']."'");
       array_push($query_list, "mii.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
       array_push($query_list, "mii.job_type_cd = '".$chg_wearer_job_type_cd."'");
       $query = implode(' AND ', $query_list);

       $arg_str = "";
       $arg_str = "SELECT ";
       $arg_str .= "*";
       $arg_str .= " FROM ";
       $arg_str .= "(SELECT distinct on (mi.item_cd,mi.color_cd) ";
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
       $arg_str .= "m_input_item as mii";
       $arg_str .= " INNER JOIN ";
       $arg_str .= "m_job_type as mj";
       $arg_str .= " ON (mii.corporate_id=mj.corporate_id AND mii.rntl_cont_no=mj.rntl_cont_no AND mii.job_type_cd=mj.job_type_cd)";
       $arg_str .= " INNER JOIN ";
       $arg_str .= "m_item as mi";
       $arg_str .= " ON (mii.item_cd=mi.item_cd AND mii.color_cd=mi.color_cd)";
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
           //$list["rntl_cont_no"] = $result->as_rntl_cont_no;
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
           $list["rntl_sect_cd"] = $chg_wearer_rntl_sect_cd;
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
     }
     //ChromePhp::LOG('【変更後】商品リスト');
     //ChromePhp::LOG(count($chg_wearer_list));
     //ChromePhp::LOG($chg_wearer_list);
   } else {
     // 着用者基本マスタトランの情報がない場合
     $query_list = array();
     $list = array();
     $chg_wearer_list = array();

     array_push($query_list, "mi.corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "mii.corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "mii.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
     array_push($query_list, "mii.job_type_cd = '".$chg_wearer_job_type_cd."'");
     $query = implode(' AND ', $query_list);

     $arg_str = "";
     $arg_str = "SELECT ";
     $arg_str .= "*";
     $arg_str .= " FROM ";
     $arg_str .= "(SELECT distinct on (mi.item_cd,mi.color_cd) ";
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
     $arg_str .= "m_input_item as mii";
     $arg_str .= " INNER JOIN ";
     $arg_str .= "m_job_type as mj";
     $arg_str .= " ON (mii.corporate_id=mj.corporate_id AND mii.rntl_cont_no=mj.rntl_cont_no AND mii.job_type_cd=mj.job_type_cd)";
     $arg_str .= " INNER JOIN ";
     $arg_str .= "m_item as mi";
     $arg_str .= " ON (mii.item_cd=mi.item_cd AND mii.color_cd=mi.color_cd)";
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
         //$list["rntl_cont_no"] = $result->as_rntl_cont_no;
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
         $list["rntl_sect_cd"] = $chg_wearer_rntl_sect_cd;
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
   }

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
        && ($chg_wearer_list[$i]["std_input_qty"] == $now_wearer_list[$j]["std_input_qty"]
        || $chg_wearer_list[$i]["std_input_qty"] < $now_wearer_list[$j]["std_input_qty"])
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
       array_push($query_list, "m_job_type.job_type_cd = '".$chk_map['job_type_cd']."'");
       array_push($query_list, "m_input_item.job_type_cd = '".$chk_map['job_type_cd']."'");
       array_push($query_list, "m_input_item.item_cd = '".$chk_map['item_cd']."'");
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
       $element = array();
       $query_list = array();
       $query_list[] = "m_item.item_cd = '".$chk_map['item_cd']."'";
       $query_list[] = "m_item.color_cd = '".$chk_map['color_cd']."'";
       $query = implode(' AND ', $query_list);
       $arg_str = "";
       $arg_str = "SELECT ";
       $arg_str .= "size_cd";
       $arg_str .= " FROM ";
       $arg_str .= "m_item";
       $arg_str .= " WHERE ";
       $arg_str .= $query;
       $arg_str .= " ORDER BY size_cd ASC";
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
           $element["size"] = $result->size_cd;

           $query_list = array();
           $query_list[] = "corporate_id = '".$auth['corporate_id']."'";
           $query_list[] = "job_type_cd = '".$chk_map['job_type_cd']."'";
           $query_list[] = "order_req_no = '".$wearer_chg_post['order_req_no']."'";
           $query_list[] = "item_cd = '".$chk_map['item_cd']."'";
           $query_list[] = "color_cd = '".$chk_map['color_cd']."'";
           $query_list[] = "job_type_item_cd = '".$chk_map['job_type_item_cd']."'";
           $query_list[] = "size_cd = '".$result->size_cd."'";
           $query = implode(' AND ', $query_list);
           $arg_str = "";
           $arg_str .= "SELECT ";
           $arg_str .= "*";
           $arg_str .= " FROM ";
           $arg_str .= "t_order_tran";
           $arg_str .= " WHERE ";
           $arg_str .= $query;
           $t_order_tran = new TOrderTran();
           $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
           $result_obj = (array)$results;
           $results_cnt = $result_obj["\0*\0_count"];
           if ($results_cnt > 0) {
             $element["selected"] = "selected";
           } else {
             $element["selected"] = "";
           }
           $list["size_cd"][] = $element;
         }
       }
       // 発注数(単一選択=入力不可、複数選択=入力可)
       if ($list["choice_type"] == "1") {
         $list["order_num_disable"] = "disabled";
         $list["order_num"] = $chk_map['std_input_qty'];
       } else {
         // 発注情報トラン参照
         $query_list = array();
         array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
         array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
         array_push($query_list, "t_order_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
         array_push($query_list, "t_order_tran.rntl_sect_cd = '".$chk_map["rntl_sect_cd"]."'");
         array_push($query_list, "t_order_tran.job_type_cd = '".$chk_map["job_type_cd"]."'");
         array_push($query_list, "t_order_tran.item_cd = '".$chk_map["item_cd"]."'");
         array_push($query_list, "t_order_tran.color_cd = '".$chk_map["color_cd"]."'");
         array_push($query_list,"t_order_tran.order_sts_kbn = '5'");
         $reason_kbns = array();
         array_push($reason_kbns, "t_order_tran.order_reason_kbn = '09'");
         array_push($reason_kbns, "t_order_tran.order_reason_kbn = '10'");
         array_push($reason_kbns, "t_order_tran.order_reason_kbn = '11'");
         array_push($reason_kbns, "t_order_tran.order_reason_kbn = '24'");
         $reason_kbns = implode(' OR ', $reason_kbns);
         array_push($query_list, "(".$reason_kbns.")");
         $query = implode(' AND ', $query_list);

         $arg_str = "";
         $arg_str = "SELECT ";
         $arg_str .= "order_qty";
         $arg_str .= " FROM ";
         $arg_str .= "t_order_tran";
         $arg_str .= " WHERE ";
         $arg_str .= $query;
         $t_order_tran = new TOrderTran();
         $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
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
             $list["order_num_disable"] = "";
             $list["order_num"] = $result->order_qty;
           }
         } else {
           $list["order_num_disable"] = "";
           $list["order_num"] = "";
         }
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
   if (!empty($now_wearer_list) && empty($first_flg)) {
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
          && ($now_wearer_map['std_input_qty'] == $chg_wearer_list[$i]['std_input_qty']
          || $now_wearer_map['std_input_qty'] < $chg_wearer_list[$i]['std_input_qty'])
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
       $list["size_cd"] = $now_wearer_map['size_cd'];
       // 個体管理番号
       // ※個体管理番号リスト、対象チェックボックス値の生成
       if ($auth["individual_flg"] == "1") {
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
             array_push($individual_ctrl_no, $result->individual_ctrl_no);

             // 返却可能フラグによるdisable制御
             $individual = array();
             $individual["individual_ctrl_no"] = $result->individual_ctrl_no;
             if ($result->rtn_ok_flg == '0') {
               $individual["disabled"] = "disabled";
             } else {
               $individual["disabled"] = "";
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
             $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
             $result_obj = (array)$results;
             $results_cnt = $result_obj["\0*\0_count"];
             if ($results_cnt > 0) {
               $individual["checked"] = "checked";
             } else {
               $individual["checked"] = "";
             }
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
           // 表示個体管理番号数
           $list["individual_cnt"] = count($individual_ctrl_no);
           // 個体管理番号
           $list["individual_ctrl_no"] = implode("<br>", $individual_ctrl_no);
         }
       }
       // 返却枚数
       $list["return_num"] = "";
       for ($i=0; $i<count($chg_wearer_list); $i++) {
         if (
          $now_wearer_map['item_cd'] == $chg_wearer_list[$i]['item_cd']
          && $now_wearer_map['color_cd'] == $chg_wearer_list[$i]['color_cd']
         )
         {
           if ($chg_wearer_list[$i]['std_input_qty'] < $now_wearer_map['std_input_qty']) {
             $list["return_num"] =$now_wearer_map['possible_num'];
           }
         } else {
           $list["return_num"] = $now_wearer_map['possible_num'];
         }
       }
       $list["return_num_disable"] = "disabled";
       // 返却可能枚数（所持数）
       $list["possible_num"] = $now_wearer_map['possible_num'];

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
   $list["sum_order_num"] = 0;
   $cnt = 0;
   if (!empty($add_list)) {
     $multiples = array();
     foreach ($add_list as $add_map) {
       if ($add_map["choice_type"] == "2") {
         if (in_array($add_map["item_cd"], $multiples)) {
           continue;
         } else {
           $list["sum_order_num"] += $add_map["std_input_qty"];
           array_push($multiples, $add_map["item_cd"]);
         }
       } else {
         $list["sum_order_num"] += $add_map["std_input_qty"];
       }
     }
   }

   // 返却総枚数
   $list["sum_return_num"] = 0;
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
 * 発注入力（職種変更または異動）
 * 発注取消処理
 */
$app->post('/wearer_change/delete', function ()use($app){
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
  // DB更新エラーコード 0:正常 1:更新エラー
  $json_list["error_code"] = "0";

  // トランザクション開始
  $m_wearer_std_tran = new MWearerStdTran();
  $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
  try {
    //--着用者商品マスタトラン削除--//
    //ChromePhp::LOG("着用者商品マスタトラン削除");
    $query_list = array();
    array_push($query_list, "m_wearer_item_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_order_tran.order_req_no = '".$cond['order_req_no']."'");
    // 発注区分「異動」
    array_push($query_list, "t_order_tran.order_sts_kbn = '5'");
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
    // 発注区分「異動」
    array_push($query_list, "t_order_tran.order_sts_kbn = '5'");
    // 理由区分「職種変更または異動」系ステータス
    $reason_kbn = array();
    array_push($reason_kbn, '09');
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
    array_push($query_list, "t_returned_plan_info_tran.order_req_no = '".$cond['return_req_no']."'");
    // 発注区分「異動」
    array_push($query_list, "t_returned_plan_info_tran.order_sts_kbn = '5'");
    // 理由区分「職種変更または異動」系ステータス
    $reason_kbn = array();
    array_push($reason_kbn, '09');
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
 * 発注入力（職種変更または異動）
 * 入力完了処理
 */
$app->post('/wearer_change/complete', function ()use($app){
   $params = json_decode(file_get_contents("php://input"), true);

   // アカウントセッション取得
   $auth = $app->session->get("auth");
   //ChromePhp::LOG($auth);

   // 前画面セッション取得
   $wearer_chg_post = $app->session->get("wearer_chg_post");
   //ChromePhp::LOG($wearer_chg_post);

   // フロントパラメータ取得
   $mode = $params["mode"];
   $wearer_data_input = $params["wearer_data"];
   $now_item_input = $params["now_item"];
   $add_item_input = $params["add_item"];
   //ChromePhp::LOG($wearer_data_input);
   //ChromePhp::LOG($now_item_input);
   //ChromePhp::LOG($add_item_input);

   $json_list = array();
   // DB更新エラーコード 0:正常 その他:要因エラー
   $json_list["error_code"] = "0";
   $json_list["error_msg"] = array();

   if ($mode == "check") {
     //--入力内容確認--//
     // 拠点・貸与パターン変更なしチェック
     $query_list = array();
     array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
     array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
     $job_type_cd = explode(':', $wearer_data_input['job_type']);
     $job_type_cd = $job_type_cd[0];
     array_push($query_list, "job_type_cd = '".$job_type_cd."'");
     // 着用者状況区分(稼働)
     array_push($query_list, "werer_sts_kbn = '1'");
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
     // 着用者名（読み仮名）
     if (empty($wearer_data_input["member_name_kana"])) {
       $json_list["error_code"] = "1";
       $error_msg = "着用者名(読み仮名)を入力してください。";
       array_push($json_list["error_msg"], $error_msg);
     }
     if (mb_strlen($wearer_data_input['member_name_kana']) > 0) {
        if (strlen(mb_convert_encoding($wearer_data_input['member_name_kana'], "SJIS")) > 25) {
          $json_list["error_code"] = "1";
          $error_msg = "着用者名(読み仮名)が規定の文字数をオーバーしています。";
          array_push($json_list["error_msg"], $error_msg);
        }
     }
     // 着用開始日
     if (empty($wearer_data_input["resfl_ymd"])) {
       $json_list["error_code"] = "1";
       $error_msg = "着用開始日を入力してください。";
       array_push($json_list["error_msg"], $error_msg);
     }
     // コメント欄
     if (mb_strlen($wearer_data_input['comment']) > 0) {
       if (strlen(mb_convert_encoding($wearer_data_input['comment'], "SJIS")) > 100) {
         $json_list["error_code"] = "1";
         $error_msg = "コメント欄の規定文字数がオーバーしています。";
         array_push($json_list["error_msg"], $error_msg);
       }
     }
     // 現在貸与中のアイテム
     if (!empty($now_item_input)) {
       foreach ($now_item_input as $now_item_input_map) {
         // 返却枚数フォーマットチェック
         if (!ctype_digit(strval($now_item_input_map["now_return_num"]))) {
           if (empty($now_return_num_format_err)) {
             $now_return_num_format_err = "err";
             $json_list["error_code"] = "1";
             $error_msg = "現在貸与中のアイテム：返却枚数には半角数字を入力してください。";
             array_push($json_list["error_msg"], $error_msg);
           }
         }
         // 返却枚数チェック
         if ($now_item_input_map["individual_flg"] == "1") {
           //※個体管理番号有りの場合
           $target_cnt = 0;
           for ($i=0; $i<count($now_item_input_map["individual_data"]); $i++) {
             if ($now_item_input_map["individual_data"][$i]["now_target_flg"] == "1") {
               $target_cnt = $target_cnt + 1;
             }
           }
           if ($now_item_input_map["possible_num"] != $target_cnt) {
             if (empty($now_return_num_err1)) {
               $now_return_num_err1 = "err";
               $json_list["error_code"] = "1";
               $error_msg = "現在貸与中のアイテム：返却枚数が足りない商品があります。";
               array_push($json_list["error_msg"], $error_msg);
             }
           }
         } else {
           //※個体管理番号なしの場合
           if ($now_item_input_map["possible_num"] < $now_item_input_map["now_return_num"]) {
             if (empty($now_return_num_err2)) {
               $now_return_num_err2 = "err";
               $json_list["error_code"] = "1";
               $error_msg = "現在貸与中のアイテム：返却枚数が超過している商品があります。";
               array_push($json_list["error_msg"], $error_msg);
             }
           }
           if ($now_item_input_map["possible_num"] > $now_item_input_map["now_return_num"]) {
             if (empty($now_return_num_err2)) {
               $now_return_num_err2 = "err";
               $json_list["error_code"] = "1";
               $error_msg = "現在貸与中のアイテム：返却枚数が足りない商品があります。";
               array_push($json_list["error_msg"], $error_msg);
             }
           }
         }
       }
     }
     if (!empty($add_item_input)) {
       // 新たに追加されるアイテム
       foreach ($add_item_input as $add_item_input_map) {
         // 発注枚数フォーマットチェック
         if (!empty($add_item_input_map["add_order_num"])) {
           if (!ctype_digit(strval($add_item_input_map["add_order_num"]))) {
             if (empty($add_order_num_format_err)) {
               $add_return_num_format_err = "err";
               $json_list["error_code"] = "1";
               $error_msg = "新たに追加されるアイテム：発注枚数には半角数字を入力してください。";
               array_push($json_list["error_msg"], $error_msg);
             }
           }
         }
         // 発注枚数チェック
         //※単一選択の場合
         if ($add_item_input_map["add_choice_type"] == "1") {
           if ($add_item_input_map["add_std_input_qty"] < $add_item_input_map["add_order_num"]) {
             if (empty($add_order_num_err1)) {
               $add_order_num_err1 = "err";
               $json_list["error_code"] = "1";
               $error_msg = "新たに追加されるアイテム：単一選択で発注枚数が超過している商品があります。";
               array_push($json_list["error_msg"], $error_msg);
             }
           }
           if ($add_item_input_map["add_std_input_qty"] > $add_item_input_map["add_order_num"]) {
             if (empty($add_order_num_err2)) {
               $add_order_num_err2 = "err";
               $json_list["error_code"] = "1";
               $error_msg = "新たに追加されるアイテム：単一選択で発注枚数が足りない商品があります。";
               array_push($json_list["error_msg"], $error_msg);
             }
           }
         }
         // 複数選択の場合
         if ($add_item_input_map["add_choice_type"] == "2") {
           $item_sum_num = 0;
           foreach ($add_item_input as $add_item_input_map_2) {
             $item_num = 0;
             if (
              $add_item_input_map_2["add_choice_type"] == "2" &&
              $add_item_input_map["add_item_cd"] == $add_item_input_map_2["add_item_cd"]
             )
             {
               if (!empty($add_item_input_map_2["add_order_num"])) {
                 $item_num = $add_item_input_map_2["add_order_num"];
               }
               $item_sum_num = $item_sum_num + $item_num;
             }
           }
           if ($add_item_input_map["add_std_input_qty"] < $item_sum_num) {
             if (empty($add_order_num_err3)) {
               $add_order_num_err3 = "err";
               $json_list["error_code"] = "1";
               $error_msg = "新たに追加されるアイテム：複数選択で発注枚数が超過している商品があります。";
               array_push($json_list["error_msg"], $error_msg);
             }
           }
         }
       }
     }

     echo json_encode($json_list);
   } else if ($mode == "update") {
     //--発注NGパターンチェック--//
     //※着用者基本マスタトラン参照
     $query_list = array();
     array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
     array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
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
         $json_list["error_code"] = "1";
         $error_msg = "着用者編集の発注が登録されていた為、操作を完了できませんでした。着用者編集の発注を削除してから再度登録して下さい。";
         $json_list["error_msg"] = $error_msg;

         //ChromePhp::LOG($json_list);
         echo json_encode($json_list);
         return;
       }
     }

     //※発注情報トラン参照
     $query_list = array();
     array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
     array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
     array_push($query_list, "rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
     array_push($query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
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

         echo json_encode($json_list);
         return;
       }
     }

     // 着用者基本マスタ参照
     $query_list = array();
     array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
     array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
     array_push($query_list, "rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
     array_push($query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
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

       if ($wearer_chg_post['wearer_tran_flg'] == "1") {
         //--着用者基本マスタトランに情報がある場合、更新処理--//
         //ChromePhp::LOG("着用者基本マスタトラン更新");
         $src_query_list = array();
         array_push($src_query_list, "corporate_id = '".$auth['corporate_id']."'");
         array_push($src_query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
         array_push($src_query_list, "rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
         array_push($src_query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
         $src_query = implode(' AND ', $src_query_list);

         $up_query_list = array();
         // 貸与パターン
         $job_type_cd = explode(':', $wearer_data_input['job_type']);
         $job_type_cd = $job_type_cd[0];
         array_push($up_query_list, "job_type_cd = '".$job_type_cd."'");
         // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
         $m_wearer_std_comb_hkey = md5(
           $auth['corporate_id']."-".
           $wearer_chg_post["werer_cd"]."-".
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
         array_push($up_query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
         // 契約No
         array_push($up_query_list, "rntl_cont_no = '".$wearer_data_input['agreement_no']."'");
         // 部門コード
         array_push($up_query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
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
         // 着用者状況区分(稼働)
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
             array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
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
         // 更新区分(WEB発注システム(異動）)
         array_push($up_query_list, "upd_kbn = '5'");
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
           $wearer_chg_post["werer_cd"]."-".
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
         array_push($values_list, "'".$wearer_chg_post['werer_cd']."'");
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
         // 発令日
         if (!empty($wearer_data_input['appointment_ymd'])) {
           $appointment_ymd = date('Ymd', strtotime($wearer_data_input['appointment_ymd']));
           array_push($calum_list, "appointment_ymd");
           array_push($values_list, "'".$appointment_ymd."'");
         } else {
           array_push($calum_list, "appointment_ymd");
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
             array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
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
         // 発注状況区分(異動)
         array_push($calum_list, "order_sts_kbn");
         array_push($values_list, "'5'");
         // 更新区分(WEB発注システム(異動))
         array_push($calum_list, "upd_kbn");
         array_push($values_list, "'5'");
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
       // 新たに追加されるアイテム内容登録
       //ChromePhp::LOG("発注情報トランクリーン");
       $query_list = array();
       array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
       array_push($query_list, "order_req_no = '".$wearer_chg_post['order_req_no']."'");
       // 発注区分「異動」
       //array_push($query_list, "order_sts_kbn = '5'");
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
       if (!empty($add_item_input)) {
         // 現発注Noの発注情報トランをクリーン
         //ChromePhp::LOG("発注情報トラン登録");
         foreach ($add_item_input as $add_item_map) {
           $calum_list = array();
           $values_list = array();
           // 発注枚数が設定されている商品のみ登録、ない場合は以降処理しない
           if (empty($add_item_map["add_order_num"])) {
             continue;
           }

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
           array_push($values_list, "'".$wearer_chg_post['werer_cd']."'");
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
               array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
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
             $add_item_map['add_item_cd']."-".
             $add_item_map['add_color_cd']."-".
             $add_item_map['add_size_cd']
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
             $wearer_chg_post["werer_cd"]."-".
             $wearer_data_input['agreement_no']."-".
             $wearer_data_input['section']."-".
             $job_type_cd
           );
           array_push($calum_list, "m_wearer_std_comb_hkey");
           array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
           // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
           $m_wearer_item_comb_hkey = md5(
             $auth['corporate_id']."-".
             $wearer_chg_post["werer_cd"]."-".
             $wearer_data_input['agreement_no']."-".
             $wearer_data_input['section']."-".
             $job_type_cd."-".
             $add_item_map['add_job_type_item_cd']."-".
             $add_item_map['add_item_cd']."-".
             $add_item_map['add_color_cd']."-".
             $add_item_map['add_size_cd']
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
      } else {
        // 商品情報がない場合（拠点のみの変更）、必要情報だけのレコードを生成
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
        // 着用者コード
        array_push($calum_list, "werer_cd");
        array_push($values_list, "'".$wearer_chg_post['werer_cd']."'");
        // 倉庫コード
        //array_push($calum_list, "whse_cd");
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
            array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
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
          $wearer_chg_post["werer_cd"]."-".
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
      // 現在貸与中のアイテム内容登録
      if (!empty($now_item_input)) {
        // 現発注Noの返却予定情報トランをクリーン
        if (!empty($wearer_chg_post['return_req_no'])) {
          //ChromePhp::LOG("返却予定情報トランクリーン");
          $query_list = array();
          array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
          array_push($query_list, "order_req_no = '".$wearer_chg_post['return_req_no']."'");
          // 発注区分「異動」
          //array_push($query_list, "order_sts_kbn = '5'");
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
        }
        //ChromePhp::LOG("返却予定情報トラン登録");
        foreach ($now_item_input as $now_item_input_map) {
          if ($now_item_input_map["individual_flg"] == true && !empty($now_item_input_map["individual_data"])) {
            // ※個体管理番号単位での登録の場合
            foreach ($now_item_input_map["individual_data"] as $individual_data) {
              // 対象にチェックされている商品のみが登録対象、それ以外は以降処理しない
              if ($individual_data["now_target_flg"] == "0") {
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
              array_push($values_list, "'".$now_item_input_map['now_item_cd']."'");
              // 色コード
              array_push($calum_list, "color_cd");
              array_push($values_list, "'".$now_item_input_map['now_color_cd']."'");
              // サイズコード
              array_push($calum_list, "size_cd");
              array_push($values_list, "'".$now_item_input_map['now_size_cd']."'");
              // 個体管理番号
              array_push($calum_list, "individual_ctrl_no");
              array_push($values_list, "'".$individual_data['individual_ctrl_no']."'");
              // 着用者コード
              array_push($calum_list, "werer_cd");
              array_push($values_list, "'".$wearer_chg_post['werer_cd']."'");
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
              // 発注状況区分(異動)
              array_push($calum_list, "order_sts_kbn");
              array_push($values_list, "'5'");
              // 返却予定数
              array_push($calum_list, "return_plan_qty");
              array_push($values_list, "'".$individual_data['return_num']."'");
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
                $now_item_input_map['now_item_cd']."-".
                $now_item_input_map['now_color_cd']."-".
                $now_item_input_map['now_size_cd']
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
          } else if ($now_item_input_map["individual_flg"] == false && !empty($now_item_input_map["return_num"])) {
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
            array_push($values_list, "'".$now_item_input_map['now_item_cd']."'");
            // 色コード
            array_push($calum_list, "color_cd");
            array_push($values_list, "'".$now_item_input_map['now_color_cd']."'");
            // サイズコード
            array_push($calum_list, "size_cd");
            array_push($values_list, "'".$now_item_input_map['now_size_cd']."'");
            // 着用者コード
            array_push($calum_list, "werer_cd");
            array_push($values_list, "'".$wearer_chg_post['werer_cd']."'");
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
            // 発注状況区分(異動)
            array_push($calum_list, "order_sts_kbn");
            array_push($values_list, "'5'");
            // 返却予定数
            array_push($calum_list, "return_plan_qty");
            array_push($values_list, "'".$now_item_input_map['return_num']."'");
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
              $now_item_input_map['now_item_cd']."-".
              $now_item_input_map['now_color_cd']."-".
              $now_item_input_map['now_size_cd']
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
      ChromePhp::LOG($e);

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
 * 発注入力（職種変更または異動）
 * 発注送信処理
 */
$app->post('/wearer_change/send', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション取得
  $auth = $app->session->get("auth");
  //ChromePhp::LOG($auth);

  // 前画面セッション取得
  $wearer_chg_post = $app->session->get("wearer_chg_post");
  //ChromePhp::LOG($wearer_chg_post);

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
    //--入力内容確認--//
    // 拠点・貸与パターン変更なしチェック
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
    $job_type_cd = explode(':', $wearer_data_input['job_type']);
    $job_type_cd = $job_type_cd[0];
    array_push($query_list, "job_type_cd = '".$job_type_cd."'");
    // 着用者状況区分(稼働)
    array_push($query_list, "werer_sts_kbn = '1'");
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
    // 着用者名（読み仮名）
    if (empty($wearer_data_input["member_name_kana"])) {
      $json_list["error_code"] = "1";
      $error_msg = "着用者名(読み仮名)を入力してください。";
      array_push($json_list["error_msg"], $error_msg);
    }
    if (mb_strlen($wearer_data_input['member_name_kana']) > 0) {
       if (strlen(mb_convert_encoding($wearer_data_input['member_name_kana'], "SJIS")) > 25) {
         $json_list["error_code"] = "1";
         $error_msg = "着用者名(読み仮名)が規定の文字数をオーバーしています。";
         array_push($json_list["error_msg"], $error_msg);
       }
    }
    // 着用開始日
    if (empty($wearer_data_input["resfl_ymd"])) {
      $json_list["error_code"] = "1";
      $error_msg = "着用開始日を入力してください。";
      array_push($json_list["error_msg"], $error_msg);
    }
    // コメント欄
    if (mb_strlen($wearer_data_input['comment']) > 0) {
      if (strlen(mb_convert_encoding($wearer_data_input['comment'], "SJIS")) > 100) {
        $json_list["error_code"] = "1";
        $error_msg = "コメント欄の規定文字数がオーバーしています。";
        array_push($json_list["error_msg"], $error_msg);
      }
    }
    // 現在貸与中のアイテム
    if (!empty($now_item_input)) {
      foreach ($now_item_input as $now_item_input_map) {
        // 返却枚数フォーマットチェック
        if (!ctype_digit(strval($now_item_input_map["now_return_num"]))) {
          if (empty($now_return_num_format_err)) {
            $now_return_num_format_err = "err";
            $json_list["error_code"] = "1";
            $error_msg = "現在貸与中のアイテム：返却枚数には半角数字を入力してください。";
            array_push($json_list["error_msg"], $error_msg);
          }
        }
        // 返却枚数チェック
        if ($now_item_input_map["individual_flg"] == "1") {
          //※個体管理番号有りの場合
          $target_cnt = 0;
          for ($i=0; $i<count($now_item_input_map["individual_data"]); $i++) {
            if ($now_item_input_map["individual_data"][$i]["now_target_flg"] == "1") {
              $target_cnt = $target_cnt + 1;
            }
          }
          if ($now_item_input_map["possible_num"] != $target_cnt) {
            if (empty($now_return_num_err1)) {
              $now_return_num_err1 = "err";
              $json_list["error_code"] = "1";
              $error_msg = "現在貸与中のアイテム：返却枚数が足りない商品があります。";
              array_push($json_list["error_msg"], $error_msg);
            }
          }
        } else {
          //※個体管理番号なしの場合
          if ($now_item_input_map["possible_num"] < $now_item_input_map["now_return_num"]) {
            if (empty($now_return_num_err2)) {
              $now_return_num_err2 = "err";
              $json_list["error_code"] = "1";
              $error_msg = "現在貸与中のアイテム：返却枚数が超過している商品があります。";
              array_push($json_list["error_msg"], $error_msg);
            }
          }
          if ($now_item_input_map["possible_num"] > $now_item_input_map["now_return_num"]) {
            if (empty($now_return_num_err2)) {
              $now_return_num_err2 = "err";
              $json_list["error_code"] = "1";
              $error_msg = "現在貸与中のアイテム：返却枚数が足りない商品があります。";
              array_push($json_list["error_msg"], $error_msg);
            }
          }
        }
      }
    }
    if (!empty($add_item_input)) {
      // 新たに追加されるアイテム
      foreach ($add_item_input as $add_item_input_map) {
        // 発注枚数フォーマットチェック
        if (!empty($add_item_input_map["add_order_num"])) {
          if (!ctype_digit(strval($add_item_input_map["add_order_num"]))) {
            if (empty($add_order_num_format_err)) {
              $add_return_num_format_err = "err";
              $json_list["error_code"] = "1";
              $error_msg = "新たに追加されるアイテム：発注枚数には半角数字を入力してください。";
              array_push($json_list["error_msg"], $error_msg);
            }
          }
        }
        // 発注枚数チェック
        //※単一選択の場合
        if ($add_item_input_map["add_choice_type"] == "1") {
          if ($add_item_input_map["add_std_input_qty"] < $add_item_input_map["add_order_num"]) {
            if (empty($add_order_num_err1)) {
              $add_order_num_err1 = "err";
              $json_list["error_code"] = "1";
              $error_msg = "新たに追加されるアイテム：単一選択で発注枚数が超過している商品があります。";
              array_push($json_list["error_msg"], $error_msg);
            }
          }
          if ($add_item_input_map["add_std_input_qty"] > $add_item_input_map["add_order_num"]) {
            if (empty($add_order_num_err2)) {
              $add_order_num_err2 = "err";
              $json_list["error_code"] = "1";
              $error_msg = "新たに追加されるアイテム：単一選択で発注枚数が足りない商品があります。";
              array_push($json_list["error_msg"], $error_msg);
            }
          }
        }
        // 複数選択の場合
        if ($add_item_input_map["add_choice_type"] == "2") {
          $item_sum_num = 0;
          foreach ($add_item_input as $add_item_input_map_2) {
            $item_num = 0;
            if (
             $add_item_input_map_2["add_choice_type"] == "2" &&
             $add_item_input_map["add_item_cd"] == $add_item_input_map_2["add_item_cd"]
            )
            {
              if (!empty($add_item_input_map_2["add_order_num"])) {
                $item_num = $add_item_input_map_2["add_order_num"];
              }
              $item_sum_num = $item_sum_num + $item_num;
            }
          }
          if ($add_item_input_map["add_std_input_qty"] < $item_sum_num) {
            if (empty($add_order_num_err3)) {
              $add_order_num_err3 = "err";
              $json_list["error_code"] = "1";
              $error_msg = "新たに追加されるアイテム：複数選択で発注枚数が超過している商品があります。";
              array_push($json_list["error_msg"], $error_msg);
            }
          }
        }
      }
    }

    echo json_encode($json_list);
  } else if ($mode == "update") {
    //--発注NGパターンチェック--//
    //※着用者基本マスタトラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
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
        $json_list["error_code"] = "1";
        $error_msg = "着用者編集の発注が登録されていた為、操作を完了できませんでした。着用者編集の発注を削除してから再度登録して下さい。";
        $json_list["error_msg"] = $error_msg;

        //ChromePhp::LOG($json_list);
        echo json_encode($json_list);
        return;
      }
    }

    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
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

        echo json_encode($json_list);
        return;
      }
    }

    // 着用者基本マスタ参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
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

      if ($wearer_chg_post['wearer_tran_flg'] == "1") {
        //--着用者基本マスタトランに情報がある場合、更新処理--//
        $src_query_list = array();
        array_push($src_query_list, "corporate_id = '".$auth['corporate_id']."'");
        array_push($src_query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
        array_push($src_query_list, "rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
        array_push($src_query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
        $src_query = implode(' AND ', $src_query_list);

        $up_query_list = array();
        // 貸与パターン
        $job_type_cd = explode(':', $wearer_data_input['job_type']);
        $job_type_cd = $job_type_cd[0];
        array_push($up_query_list, "job_type_cd = '".$job_type_cd."'");
        // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
        $m_wearer_std_comb_hkey = md5(
          $auth['corporate_id']."-".
          $wearer_chg_post["werer_cd"]."-".
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
        array_push($up_query_list, "werer_cd = '".$wearer_chg_post['werer_cd']."'");
        // 契約No
        array_push($up_query_list, "rntl_cont_no = '".$wearer_data_input['agreement_no']."'");
        // 部門コード
        array_push($up_query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
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
        // 着用者状況区分(稼働)
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
            array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
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
        // 更新区分(WEB発注システム(異動))
        array_push($up_query_list, "upd_kbn = '5'");
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
        //--着用者基本マスタトランに情報がある場合、更新処理 ここまで--//
      } else {
        //--着用者基本マスタトランに情報がない場合、登録処理--//
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
          $wearer_chg_post["werer_cd"]."-".
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
        array_push($values_list, "'".$wearer_chg_post['werer_cd']."'");
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
        // 発令日
        if (!empty($wearer_data_input['appointment_ymd'])) {
          $appointment_ymd = date('Ymd', strtotime($wearer_data_input['appointment_ymd']));
          array_push($calum_list, "appointment_ymd");
          array_push($values_list, "'".$appointment_ymd."'");
        } else {
          array_push($calum_list, "appointment_ymd");
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
            array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
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
        // 発注状況区分(異動)
        array_push($calum_list, "order_sts_kbn");
        array_push($values_list, "'5'");
        // 更新区分(WEB発注システム(異動))
        array_push($calum_list, "upd_kbn");
        array_push($values_list, "'5'");
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
        //ChromePhp::LOG($results);
        //--着用者基本マスタトラン登録処理 ここまで--//
      }

      //--発注情報トラン登録--//
      $cnt = 1;
      //ChromePhp::LOG("発注情報トランクリーン");
      $query_list = array();
      array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "order_req_no = '".$wearer_chg_post['order_req_no']."'");
      // 発注区分「異動」
      //array_push($query_list, "order_sts_kbn = '5'");
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
      // 新たに追加されるアイテム内容登録
      if (!empty($add_item_input)) {
        //ChromePhp::LOG("発注情報トラン登録");
        foreach ($add_item_input as $add_item_map) {
          $calum_list = array();
          $values_list = array();
          // 発注枚数が設定されている商品のみ登録、ない場合は以降処理しない
          if (empty($add_item_map["add_order_num"])) {
            continue;
          }

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
          array_push($values_list, "'".$wearer_chg_post['werer_cd']."'");
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
              array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
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
            $add_item_map['add_item_cd']."-".
            $add_item_map['add_color_cd']."-".
            $add_item_map['add_size_cd']
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
            $wearer_chg_post["werer_cd"]."-".
            $wearer_data_input['agreement_no']."-".
            $wearer_data_input['section']."-".
            $job_type_cd
          );
          array_push($calum_list, "m_wearer_std_comb_hkey");
          array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
          // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
          $m_wearer_item_comb_hkey = md5(
            $auth['corporate_id']."-".
            $wearer_chg_post["werer_cd"]."-".
            $wearer_data_input['agreement_no']."-".
            $wearer_data_input['section']."-".
            $job_type_cd."-".
            $add_item_map['add_job_type_item_cd']."-".
            $add_item_map['add_item_cd']."-".
            $add_item_map['add_color_cd']."-".
            $add_item_map['add_size_cd']
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
     } else {
       // 商品情報がない場合（拠点のみの変更）、必要情報だけのレコードを生成
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
       // 着用者コード
       array_push($calum_list, "werer_cd");
       array_push($values_list, "'".$wearer_chg_post['werer_cd']."'");
       // 倉庫コード
       //array_push($calum_list, "whse_cd");
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
           array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
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
         $wearer_chg_post["werer_cd"]."-".
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
     // 現在貸与中のアイテム内容登録
     if (!empty($now_item_input)) {
       // 現発注Noの返却予定情報トランをクリーン
       if (!empty($wearer_chg_post['return_req_no'])) {
         //ChromePhp::LOG("返却予定情報トランクリーン");
         $query_list = array();
         array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
         array_push($query_list, "order_req_no = '".$wearer_chg_post['return_req_no']."'");
         // 発注区分「異動」
         //array_push($query_list, "order_sts_kbn = '5'");
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
       }

       //ChromePhp::LOG("返却予定情報トラン登録");
       foreach ($now_item_input as $now_item_input_map) {
         if ($now_item_input_map["individual_flg"] == true && !empty($now_item_input_map["individual_data"])) {
           // ※個体管理番号単位での登録の場合
           foreach ($now_item_input_map["individual_data"] as $individual_data) {
             // 対象にチェックされている商品のみが登録対象、それ以外は以降処理しない
             if ($individual_data["now_target_flg"] == "0") {
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
             array_push($values_list, "'".$now_item_input_map['now_item_cd']."'");
             // 色コード
             array_push($calum_list, "color_cd");
             array_push($values_list, "'".$now_item_input_map['now_color_cd']."'");
             // サイズコード
             array_push($calum_list, "size_cd");
             array_push($values_list, "'".$now_item_input_map['now_size_cd']."'");
             // 個体管理番号
             array_push($calum_list, "individual_ctrl_no");
             array_push($values_list, "'".$individual_data['individual_ctrl_no']."'");
             // 着用者コード
             array_push($calum_list, "werer_cd");
             array_push($values_list, "'".$wearer_chg_post['werer_cd']."'");
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
             // 発注状況区分(異動)
             array_push($calum_list, "order_sts_kbn");
             array_push($values_list, "'5'");
             // 返却予定数
             array_push($calum_list, "return_plan_qty");
             array_push($values_list, "'".$individual_data['return_num']."'");
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
               $now_item_input_map['now_item_cd']."-".
               $now_item_input_map['now_color_cd']."-".
               $now_item_input_map['now_size_cd']
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
         } else if ($now_item_input_map["individual_flg"] == false && !empty($now_item_input_map["return_num"])) {
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
           array_push($values_list, "'".$now_item_input_map['now_item_cd']."'");
           // 色コード
           array_push($calum_list, "color_cd");
           array_push($values_list, "'".$now_item_input_map['now_color_cd']."'");
           // サイズコード
           array_push($calum_list, "size_cd");
           array_push($values_list, "'".$now_item_input_map['now_size_cd']."'");
           // 着用者コード
           array_push($calum_list, "werer_cd");
           array_push($values_list, "'".$wearer_chg_post['werer_cd']."'");
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
           // 発注状況区分(異動)
           array_push($calum_list, "order_sts_kbn");
           array_push($values_list, "'5'");
           // 返却予定数
           array_push($calum_list, "return_plan_qty");
           array_push($values_list, "'".$now_item_input_map['return_num']."'");
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
             $now_item_input_map['now_item_cd']."-".
             $now_item_input_map['now_color_cd']."-".
             $now_item_input_map['now_size_cd']
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
