<?php
//use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

//前処理
$app->before(function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);
    if (!$params && isset($_FILES['file'])) {
        $params['scr'] = 'upfile:'.$_FILES['file']['name'];
    }
    //操作ログ
    $log = new TLog();
    if (isset($params['scr'])) {
        if ($params['scr'] != 'ログイン' && $params['scr'] != 'パスワード変更') {
            if (!$app->session->has('auth')) {
                http_response_code(403);
                exit();
            }
        }
        $log->scr_name = $params['scr']; //画面名
    } else {
        $log->scr_name = '{}';
    }
    $log->log_type = 1; //ログ種別 1:操作ログ
    $log->log_level = 1; //ログレベル 1:INFO
    $auth = $app->session->get('auth');
    if (isset($auth['user_id'])) {
        $log->user_id = $auth['user_id']; //操作ユーザーID
    } else {
        $log->user_id = '{}';
    }
    $now = date('Y/m/d H:i:s.sss');
    $log->ctrl_date = $now; //操作日時
    $log->access_url = $_SERVER['HTTP_REFERER']; //アクセスURL
    if (file_get_contents('php://input')) {
        $log->post_param = file_get_contents('php://input'); //POSTパラメーター
    } elseif ($_FILES) {
        $log->post_param = $_FILES;
    } else {
        $log->post_param = '{}';
    }
    $log->ip_address = $_SERVER['REMOTE_ADDR']; //端末識別情報
    $log->user_agent = $_SERVER['HTTP_USER_AGENT']; //USER_AGENT
    $log->memo = 'メモ'; //   メモ
    if ($log->save() == false) {
        // $error_list['update'] = '操作ログの登録に失敗しました。';
        // $json_list['errors'] = $error_list;
        // echo json_encode($json_list);
        return true;
    }
});

/*
 * グローバルメニュー
 */
$app->post('/global_menu', function () use ($app) {
    $auth = $app->session->get('auth');
    $user_name = array();
    $json_list['login_disp_name'] = $auth['login_disp_name'];
    if ($auth['user_type'] != '1') {
        $json_list['admin'] = $auth['user_type'];
    }
    echo json_encode($json_list);
});

/*
 * アカウントセッション取得
 */
$app->post('/account_session', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    $json_list = $auth;
    echo json_encode($json_list);
});

/*
 * 検索項目：契約No
 */
$app->post('/agreement_no', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
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

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
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

/*
    // デフォルトは空を設定
    $list['rntl_cont_no'] = null;
    $list['rntl_cont_name'] = null;
    array_push($all_list,$list);
*/

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
          array_push($all_list, $list);
      }
    } else {
        $list['rntl_cont_no'] = null;
        $list['rntl_cont_name'] = '';
        array_push($all_list, $list);
    }

    $json_list['agreement_no_list'] = $all_list;
    echo json_encode($json_list);
});

/*
 * 検索項目：企業ID 全てあり
 */
$app->post('/corporate_id_all', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    //コーポレートテーブルを全て取得//
    $results = MCorporate::find(array(
        'order' => 'corporate_id ASC',
    ));

// デフォルト「全て」を設定
$list['corporate_id'] = null;
$list['corporate_name'] = '全て';
ChromePhp::log($list['corporate_name']);

array_push($all_list, $list);
    foreach ($results as $result) {
        $list['corporate_id'] = $result->corporate_id;
        $list['corporate_name'] = $result->corporate_name;
        array_push($all_list, $list);
    }

    $json_list['corporate_id_list'] = $all_list;
    echo json_encode($json_list);
});

/*
 * 検索項目：企業ID 全てなし
 */
$app->post('/corporate_id', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    //コーポレートテーブルを全て取得//
    $results = MCorporate::find(array(
        'order' => 'corporate_id ASC',
    ));
    //ChromePhp::log($results);

    foreach ($results as $result) {
        $list['corporate_id'] = $result->corporate_id;
        $list['corporate_name'] = $result->corporate_name;
        array_push($all_list, $list);
    }

    $json_list['corporate_id_list'] = $all_list;
    echo json_encode($json_list);
});

/*
 * 検索項目：拠点
 */
$app->post('/section', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);
    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "rntl_cont_no = '".$params['agreement_no']."'");
    } else {
        array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");
    }
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
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
/*
    // デフォルト「全て」を設定
    if ($results_cnt > 1) {
        $list['rntl_sect_cd'] = null;
        $list['rntl_sect_name'] = '全て';
        array_push($all_list, $list);
    }
*/
    if ($results_cnt > 0) {
      $list['rntl_sect_cd'] = null;
      $list['rntl_sect_name'] = '全て';
      array_push($all_list, $list);

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
          array_push($all_list, $list);
      }
    } else {
        $list['rntl_sect_cd'] = null;
        $list['rntl_sect_name'] = '';
        array_push($all_list, $list);
    }

    $json_list['section_list'] = $all_list;
    echo json_encode($json_list);
});

/*
　* 検索項目：貸与パターン
　*/
$app->post('/job_type', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "rntl_cont_no = '".$params['agreement_no']."'");
    } else {
        array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");
    }
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
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
/*
    // デフォルト「全て」を設定
    if ($results_cnt > 1) {
        $list['job_type_cd'] = null;
        $list['job_type_name'] = '全て';
        array_push($all_list, $list);
    }
*/
    if ($results_cnt > 0) {
      $list['job_type_cd'] = null;
      $list['job_type_name'] = '全て';
      array_push($all_list, $list);

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
          array_push($all_list, $list);
      }
    } else {
        $list['job_type_cd'] = null;
        $list['job_type_name'] = '';
        array_push($all_list, $list);
    }

    $json_list['job_type_list'] = $all_list;
    echo json_encode($json_list);
});

/*
　* 検索項目：商品
　*/
$app->post('/input_item', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "rntl_cont_no = '".$params['agreement_no']."'");
    } else {
        array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");
    }
    if (!empty($params['job_type'])) {
        array_push($query_list, "job_type_cd = '".$params['job_type']."'");
    }
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (item_cd, job_type_item_cd) *';
    $arg_str .= ' FROM m_input_item';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY item_cd,job_type_item_cd asc';

    $m_input_item = new MInputItem();
    $results = new Resultset(null, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
/*
    // デフォルト「全て」を設定
    if ($results_cnt > 1) {
        $list['item_cd'] = null;
        $list['input_item_name'] = '全て';
        array_push($all_list, $list);
    }
*/
    if ($results_cnt > 0) {
      if ($results_cnt > 1) {
          $list['item_cd'] = null;
          $list['input_item_name'] = '全て';
          array_push($all_list, $list);
      }

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
          $list['item_cd'] = $result->item_cd;
          $list['input_item_name'] = $result->input_item_name;
          array_push($all_list, $list);
      }
    } else {
        $list['item_cd'] = null;
        $list['input_item_name'] = '';
        array_push($all_list, $list);
    }

    $json_list['input_item_list'] = $all_list;
    echo json_encode($json_list);
});

/*
　* 検索項目：色
　*/
$app->post('/item_color', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "rntl_cont_no = '".$params['agreement_no']."'");
    } else {
        array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");
    }
    if (!empty($params['job_type'])) {
        array_push($query_list, "job_type_cd = '".$params['job_type']."'");
    }
    if (!empty($params['input_item'])) {
        array_push($query_list, "item_cd = '".$params['input_item']."'");
    }
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (color_cd) *';
    $arg_str .= ' FROM m_input_item';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY color_cd asc';

    $m_input_item = new MInputItem();
    $results = new Resultset(null, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
/*
    // デフォルト「全て」を設定
    if ($results_cnt > 1) {
        $list['color_cd_id'] = null;
        $list['color_cd_name'] = '全て';
        array_push($all_list, $list);
    }
*/
    if ($results_cnt > 0) {
      $list['color_cd_id'] = null;
      $list['color_cd_name'] = '全て';
      array_push($all_list, $list);

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
          $list['color_cd_id'] = $result->color_cd;
          $list['color_cd_name'] = $result->color_cd;
          array_push($all_list, $list);
      }
    } else {
        $list['color_cd_id'] = null;
        $list['color_cd_name'] = '';
        array_push($all_list, $list);
    }

    $json_list['item_color_list'] = $all_list;
    echo json_encode($json_list);
});

/*
　* 検索項目：個体管理番号(表示有無フラグ)
　*/
$app->post('/individual_num', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    $json_list = $auth;
    echo json_encode($json_list);
/*
    //--- 検索条件 ---//
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $results = MInputItem::query()
        ->where($query)
        ->columns('*')
        ->execute();

    foreach ($results as $result) {
        $list['color_cd_id'] = $result->color_cd;
        array_push($all_list,$list);
    }

    $json_list['item_color_list'] = $all_list;
    echo json_encode($json_list);
*/
});

/*
 * 検索項目：在庫照会専用-貸与パターン
 */
$app->post('/zaiko_job_type', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "rntl_cont_no = '".$params['agreement_no']."'");
    } else {
        array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");
    }
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (rent_pattern_data) *';
    $arg_str .= ' FROM m_rent_pattern_for_sdmzk';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY rent_pattern_data asc';

    $m_rent_pattern_for_sdmzk = new MRentPatternForSdmzk();
    $results = new Resultset(null, $m_rent_pattern_for_sdmzk, $m_rent_pattern_for_sdmzk->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
/*
    // デフォルト「全て」を設定
    if ($results_cnt > 1) {
        $list['rent_pattern_data'] = null;
        $list['rent_pattern_name'] = '全て';
        array_push($all_list, $list);
    }
*/
    if ($results_cnt > 0) {
      $list['rent_pattern_data'] = null;
      $list['rent_pattern_name'] = '全て';
      array_push($all_list, $list);

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
          $list['rent_pattern_data'] = $result->rent_pattern_data;
          $list['rent_pattern_name'] = $result->rent_pattern_name;
          array_push($all_list, $list);
      }
    } else {
        $list['rent_pattern_data'] = null;
        $list['rent_pattern_name'] = '';
        array_push($all_list, $list);
    }

    $json_list['rent_pattern_list'] = $all_list;
    echo json_encode($json_list);
});

/*
　* 検索項目：在庫照会専用-商品
　*/
$app->post('/zaiko_item', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    array_push($query_list, "m_item.corporate_id = '".$auth['corporate_id']."'");
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "t_sdmzk.rntl_cont_no = '".$params['agreement_no']."'");
    } else {
        array_push($query_list, "t_sdmzk.rntl_cont_no = '".$auth['rntl_cont_no']."'");
    }
    if (!empty($params['job_type_zaiko'])) {
        array_push($query_list, "t_sdmzk.rent_pattern_data = '".$params['job_type_zaiko']."'");
    }
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (m_item.item_cd)';
    $arg_str .= ' m_item.item_cd as as_item_cd,';
    $arg_str .= ' m_item.item_name as as_item_name';
    $arg_str .= ' FROM m_item';
    $arg_str .= ' INNER JOIN t_sdmzk ON m_item.m_item_comb_hkey = t_sdmzk.m_item_comb_hkey';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY as_item_cd asc';

    $m_item = new MItem();
    $results = new Resultset(null, $m_item, $m_item->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
/*
    // デフォルト「全て」を設定
    if ($results_cnt > 1) {
        $list['item_cd'] = null;
        $list['item_name'] = '全て';
        array_push($all_list, $list);
    }
*/
    if ($results_cnt > 0) {
      $list['item_cd'] = null;
      $list['item_name'] = '全て';
      array_push($all_list, $list);

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
          $list['item_cd'] = $result->as_item_cd;
          $list['item_name'] = $result->as_item_name;
          array_push($all_list, $list);
      }
    } else {
        $list['item_cd'] = null;
        $list['item_name'] = '';
        array_push($all_list, $list);
    }

    $json_list['item_list'] = $all_list;
    echo json_encode($json_list);
});

/*
　* 検索項目：在庫照会専用-色
　*/
$app->post('/zaiko_item_color', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    array_push($query_list, "m_item.corporate_id = '".$auth['corporate_id']."'");
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "t_sdmzk.rntl_cont_no = '".$params['agreement_no']."'");
    } else {
        array_push($query_list, "t_sdmzk.rntl_cont_no = '".$auth['rntl_cont_no']."'");
    }
    if (!empty($params['job_type_zaiko'])) {
        array_push($query_list, "t_sdmzk.rent_pattern_data = '".$params['job_type_zaiko']."'");
    }
    if (!empty($params['item'])) {
        array_push($query_list, "m_item.item_cd = '".$params['item']."'");
    }
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (m_item.color_cd)';
    $arg_str .= ' m_item.color_cd as as_color_cd';
    $arg_str .= ' FROM m_item';
    $arg_str .= ' INNER JOIN t_sdmzk ON m_item.m_item_comb_hkey = t_sdmzk.m_item_comb_hkey';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY as_color_cd asc';

    $m_item = new MItem();
    $results = new Resultset(null, $m_item, $m_item->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
/*
    // デフォルト「全て」を設定
    if ($results_cnt > 1) {
        $list['color_cd_id'] = null;
        $list['color_cd_name'] = '全て';
        array_push($all_list, $list);
    }
*/
    if ($results_cnt > 0) {
      $list['color_cd_id'] = null;
      $list['color_cd_name'] = '全て';
      array_push($all_list, $list);

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
          $list['color_cd_id'] = $result->as_color_cd;
          $list['color_cd_name'] = $result->as_color_cd;
          array_push($all_list, $list);
      }
    } else {
        $list['color_cd_id'] = null;
        $list['color_cd_name'] = '';
        array_push($all_list, $list);
    }

    $json_list['item_color_list'] = $all_list;
    echo json_encode($json_list);
});

/*
 * 拠点絞り込み検索
 */
$app->post('/section_modal', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);
    $query_list = array();
    $cond = $params['cond'];
    $page = $params['page'];

    // アカウントセッション取得
    $auth = $app->session->get('auth');
    //拠点
    //--- 検索条件 ---//
    // 部門マスタ. 企業ID
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    // 部門マスタ. レンタル契約No
    array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");
    if (isset($cond['rntl_sect_cd'])) {
        array_push($query_list, "rntl_sect_cd LIKE '%".$cond['rntl_sect_cd']."'");
    }
    if (isset($cond['rntl_sect_name'])) {
        array_push($query_list, "rntl_sect_name LIKE '%".$cond['rntl_sect_name']."%'");
    }
    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    $builder = $app->modelsManager->createBuilder()
        ->where($query)
        ->from('MSection');
    $paginator_model = new PaginatorQueryBuilder(
        array(
            'builder' => $builder,
            'limit' => $page['records_per_page'],
            'page' => $page['page_number'],
        )
    );
    $results = array();
    if ($paginator_model) {
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;
    }
    $all_list = array();
    $json_list = array();
    $i = 0;
    foreach ($results as $result) {
        $all_list[$i]['rntl_sect_cd'] = $result->rntl_sect_cd;
        $all_list[$i]['rntl_sect_name'] = $result->rntl_sect_name;
        ++$i;
    }
    $json_list['list'] = $all_list;
    $page_list['records_per_page'] = $page['records_per_page'];
    $page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = $paginator->total_items;
    $json_list['page'] = $page_list;
    echo json_encode($json_list);
});

/*
 * 検索項目：拠点
 */
$app->post('/section_purchase', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);
    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();
    ChromePhp::log($params);
    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "rntl_cont_no = '".$params['agreement_no']."'");
    } else {
        array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");
    }
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
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
    /*
        // デフォルト「全て」を設定
        if ($results_cnt > 1) {
            $list['rntl_sect_cd'] = null;
            $list['rntl_sect_name'] = '全て';
            array_push($all_list, $list);
        }
    */
    if ($results_cnt > 0) {
        //$list['rntl_sect_cd'] = null;
        //$list['rntl_sect_name'] = '全て';
        //array_push($all_list, $list);

        $paginator_model = new PaginatorModel(
            array(
                'data' => $results,
                'limit' => $results_cnt,
                'page' => 1,
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

    echo json_encode($json_list);
});

/*
* 性別
*/
$app->post('/sex_kbn', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');

    $query_list = array();
    $list = array();
    $json_list = array();

    //--性別ここから
    $sex_kbn_list = array();
    //--- 検索条件 ---//
    // 汎用コードマスタ. 分類コード
    array_push($query_list, "cls_cd = '004'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $m_gencode_results = MGencode::query()
        ->where($query)
        ->columns('*')
        ->execute();
    foreach ($m_gencode_results as $m_gencode_result) {
        $list['cls_cd'] = $m_gencode_result->cls_cd;
        $list['gen_name'] = $m_gencode_result->gen_name;
        array_push($sex_kbn_list, $list);
    }
    //--性別ここまで
    $json_list['sex_kbn_list'] = $sex_kbn_list;
    echo json_encode($json_list);
});
