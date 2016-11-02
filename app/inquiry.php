<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/*
 * お問い合わせ一覧
 * 検索項目：企業名
 */
$app->post('/inquiry/corporate', function () use ($app) {
  $params = json_decode(file_get_contents('php://input'), true);

  // アカウントセッション取得
  $auth = $app->session->get('auth');

  // フロントパラメータ取得
  if (!empty($params['data'])) {
    $cond = $params['data'];
  }
  //ChromePhp::LOG($cond);

  $query_list = array();
  $list = array();
  $all_list = array();
  $json_list = array();

	//--ログインしているアカウントのユーザー権限により表示制御--//
	if ($auth["user_type"] !== "1") {
		// 一般ユーザー以上の権限者の場合
		$json_list["user_type"] = "1";

		$arg_str = 'SELECT ';
	  $arg_str .= ' * ';
	  $arg_str .= ' FROM ';
	  $arg_str .= 'm_corporate';
	  $arg_str .= ' ORDER BY corporate_id ASC';
	  $m_corporate = new MCorporate();
	  $results = new Resultset(null, $m_corporate, $m_corporate->getReadConnection()->query($arg_str));
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

			if ($results_cnt > 1) {
				//　初期選択は「全て」
				$list['corporate_id'] = "";
				$list['corporate_name'] = "全て";
				array_push($all_list, $list);
			}

	    foreach ($results as $result) {
				$list['corporate_id'] = $result->corporate_id;
				$list['corporate_name'] = $result->corporate_name;
        if (!empty($cond["corporate"])) {
          if ($list['corporate_id'] == $cond["corporate"]) {
            $list['selected'] = "selected";
          } else {
            $list['selected'] = "";
          }
        } else {
          $list['selected'] = "";
        }
	      array_push($all_list, $list);
	    }
	  } else {
			$list['corporate_id'] = "";
			$list['corporate_name'] = "";
      $list['selected'] = "";
			array_push($all_list, $list);
	  }
	} else {
		// 一般ユーザーの権限者の場合
		$json_list["user_type"] = "0";
	}
  $json_list['corporate_list'] = $all_list;

  echo json_encode($json_list);
});

/*
 * お問い合わせ一覧
 * 検索項目：契約No
 */
$app->post('/inquiry/agreement_no', function () use ($app) {
  $params = json_decode(file_get_contents('php://input'), true);

  // アカウントセッション取得
  $auth = $app->session->get('auth');

  // フロントパラメータ取得
  if (!empty($params['data'])) {
    $cond = $params['data'];
  }
  //ChromePhp::LOG($cond);

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
  $arg_str .= ' FROM ';
  $arg_str .= 'm_contract';
  $arg_str .= ' INNER JOIN m_contract_resource';
  $arg_str .= ' ON m_contract.corporate_id = m_contract_resource.corporate_id';
  $arg_str .= ' AND m_contract.rntl_cont_no = m_contract_resource.rntl_cont_no';
  $arg_str .= ' INNER JOIN m_account';
  $arg_str .= ' ON m_contract_resource.accnt_no = m_account.accnt_no';
  $arg_str .= ' AND m_contract_resource.corporate_id = m_account.corporate_id';
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

		// アカウントのユーザー区分、取得件数により「全て」の選択肢表示制御
		if ($auth["user_type"] !== "1") {
			$list['rntl_cont_no'] = "";
			$list['rntl_cont_name'] = "全て";
			array_push($all_list, $list);
		}

    foreach ($results as $result) {
      $list['rntl_cont_no'] = $result->as_rntl_cont_no;
      $list['rntl_cont_name'] = $result->as_rntl_cont_name;
      if (!empty($cond["agreement_no"])) {
        if ($list['rntl_cont_no'] == $cond["agreement_no"]) {
          $list['selected'] = "selected";
        } else {
          $list['selected'] = '';
        }
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

/*
 * お問い合わせ一覧
 * 検索項目：ジャンル
 */
$app->post('/inquiry/genre', function () use ($app) {
  $params = json_decode(file_get_contents('php://input'), true);

  // アカウントセッション取得
  $auth = $app->session->get('auth');

  // フロントパラメータ取得
  if (!empty($params['data'])) {
    $cond = $params['data'];
  }
  //ChromePhp::LOG($cond);

  $query_list = array();
  $list = array();
  $all_list = array();
  $json_list = array();

  array_push($query_list, "cls_cd = '024'");
  $query = implode(' AND ', $query_list);

  $m_gencode_results = MGencode::query()
      ->where($query)
      ->columns('*')
      ->execute();

	$list['gen_cd'] = "";
	$list['gen_name'] = "全て";
	array_push($all_list, $list);

  foreach ($m_gencode_results as $m_gencode_result) {
    $list['gen_cd'] = $m_gencode_result->gen_cd;
    $list['gen_name'] = $m_gencode_result->gen_name;
    if (!empty($cond["genre"])) {
      if ($list['gen_cd'] == $cond["genre"]) {
        $list['selected'] = "selected";
      } else {
        $list['selected'] = '';
      }
    } else {
      $list['selected'] = '';
    }
    array_push($all_list, $list);
  }

  $json_list['genre_list'] = $all_list;
  echo json_encode($json_list);
});

/**
 * お問い合わせ一覧
 * 検索
 */
$app->post('/inquiry/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();
	//ChromePhp::log($cond);

	//---検索条件---//
	//企業ID
	if(!empty($cond['corporate'])){
		array_push($query_list,"t_inquiry.corporate_id = '".$cond['corporate']."'");
	}
	//契約No
	if(!empty($cond['agreement_no'])){
		array_push($query_list,"t_inquiry.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//回答ステータス
	$status_kbn_list = array();
	$status_list = array();
	if($cond['answer_kbn0']){
		// 未回答
		array_push($status_list,"1");
	}
	if($cond['answer_kbn1']){
		// 回答済み
		array_push($status_list,"2");
	}
	if(!empty($status_list)) {
		$status_str = implode("','",$status_list);
		array_push($query_list,"t_inquiry.interrogator_status IN ('".$status_str."')");
	}
	//お問い合わせ日付from
	if(!empty($cond['contact_day_from'])){
    $cond['contact_day_from'] = date('Y-m-d 00:00:00', strtotime($cond['contact_day_from']));
    array_push($query_list,"t_inquiry.interrogator_date >= '".$cond['contact_day_from']."'");
	}
	//お問い合わせ日付to
	if(!empty($cond['contact_day_to'])){
    $cond['contact_day_to'] = date('Y-m-d 00:00:00', strtotime($cond['contact_day_to']));
    array_push($query_list,"t_inquiry.interrogator_date <= '".$cond['contact_day_to']."'");
	}
	//回答日付from
	if(!empty($cond['answer_day_from'])){
		$cond['answer_day_from'] = date('Y-m-d 00:00:00', strtotime($cond['answer_day_from']));
		array_push($query_list,"t_inquiry.interrogator_answer_date >= '".$cond['answer_day_from']."'");
	}
	//回答日付to
	if(!empty($cond['answer_day_to'])){
		$cond['answer_day_to'] = date('Y-m-d 23:59:59', strtotime($cond['answer_day_to']));
		array_push($query_list,"t_inquiry.interrogator_answer_date <= '".$cond['answer_day_to']."'");
	}
	//拠点
	if(!empty($cond['section'])){
		array_push($query_list,"t_inquiry.rntl_sect_cd = '".$cond['section']."'");
	}
	//お名前
	if(!empty($cond['interrogator_name'])){
		array_push($query_list,"t_inquiry.interrogator_name LIKE '%".$cond['interrogator_name']."%'");
	}
	//ジャンル
	if(!empty($cond['genre'])){
		array_push($query_list,"t_inquiry.category_name = '".$cond['genre']."'");
	}
	//お名前
	if(!empty($cond['interrogator_info'])){
		array_push($query_list,"t_inquiry.interrogator_info LIKE '%".$cond['interrogator_info']."%'");
	}
	$query = implode(' AND ', $query_list);

	$arg_str = "SELECT ";
  $arg_str .= "t_inquiry.index as as_index,";
	$arg_str .= "t_inquiry.category_name as as_category_name,";
  $arg_str .= "t_inquiry.interrogator_name as as_interrogator_name,";
	$arg_str .= "t_inquiry.interrogator_date as as_interrogator_date,";
	$arg_str .= "t_inquiry.interrogator_info as as_interrogator_info,";
	$arg_str .= "t_inquiry.interrogator_answer_date as as_interrogator_answer_date,";
	$arg_str .= "t_inquiry.interrogator_answer as as_interrogator_answer,";
	$arg_str .= "t_inquiry.interrogator_status as as_interrogator_status,";
	$arg_str .= "m_corporate.corporate_id as as_corporate_id,";
	$arg_str .= "m_corporate.corporate_name as as_corporate_name,";
	$arg_str .= "m_contract.rntl_cont_no as as_rntl_cont_no,";
	$arg_str .= "m_contract.rntl_cont_name as as_rntl_cont_name,";
  $arg_str .= "m_section.rntl_sect_cd as as_rntl_sect_cd,";
  $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name";
	$arg_str .= " FROM t_inquiry";
	$arg_str .= " INNER JOIN m_corporate";
	$arg_str .= " ON t_inquiry.corporate_id = m_corporate.corporate_id";
	$arg_str .= " INNER JOIN m_contract";
  $arg_str .= " ON (t_inquiry.corporate_id = m_contract.corporate_id";
	$arg_str .= " AND t_inquiry.rntl_cont_no = m_contract.rntl_cont_no)";
  $arg_str .= " INNER JOIN m_section";
  $arg_str .= " ON (t_inquiry.corporate_id = m_section.corporate_id";
  $arg_str .= " AND t_inquiry.rntl_cont_no = m_section.rntl_cont_no";
  $arg_str .= " AND t_inquiry.rntl_sect_cd = m_section.rntl_sect_cd)";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= " ORDER BY as_interrogator_date ASC";
	$t_inquiry = new TInquiry();
	$results = new Resultset(null, $t_inquiry, $t_inquiry->getReadConnection()->query($arg_str));
	$result_obj = (array)$results;
	$results_cnt = $result_obj["\0*\0_count"];
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

	if(!empty($results_cnt)) {
		$paginator = $paginator_model->getPaginate();
		$results = $paginator->items;

		foreach($results as $result) {
      // お問い合わせID（シーケンスID）
      $list['index'] = $result->as_index;
			// お問い合わせ日時
			$list['interrogator_date'] = date("Y/m/d H:i:s", strtotime($result->as_interrogator_date));
			// 回答日時
      if (!empty($result->as_interrogator_answer_date)) {
        $list['interrogator_answer_date'] = date("Y/m/d H:i:s", strtotime($result->as_interrogator_answer_date));
      } else {
        $list['interrogator_answer_date'] = "";
      }
      // 企業名,ID
      $list['corporate_id'] = $result->as_corporate_id;
      $list['corporate_name'] = $result->as_corporate_name;
			// 拠点,コード
			$list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
			$list['rntl_sect_name'] = $result->as_rntl_sect_name;
			// お名前
			if (!empty($result->as_interrogator_name)) {
				$list['interrogator_name'] = $result->as_interrogator_name;
			} else {
				$list['interrogator_name'] = "-";
			}
			// ジャンル
			if (isset($result->as_category_name)) {
        $query_list = array();
        array_push($query_list, "cls_cd = '024'");
        array_push($query_list, "gen_cd = '".$result->as_category_name."'");
        $query = implode(' AND ', $query_list);

        $m_gencode_results = MGencode::query()
            ->where($query)
            ->columns('*')
            ->execute();
        foreach ($m_gencode_results as $m_gencode_result) {
          $list['genre_cd'] = $m_gencode_result->gen_cd;
          $list['genre_name'] = $m_gencode_result->gen_name;
        }
			} else {
        $list['genre_cd'] = "";
        $list['genre_name'] = "";
			}
			// 内容
			if (!empty($result->as_interrogator_info)) {
				$list['interrogator_info'] = mb_strimwidth($result->as_interrogator_info, 0, 50, "・・・");
			} else {
        $list['interrogator_info'] = "";
			}
      // 契約No,契約名
      $list['rntl_cont_no'] = $result->as_rntl_cont_no;
			$list['rntl_cont_name'] = $result->as_rntl_cont_name;

			array_push($all_list,$list);
		}
	}

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $results_cnt;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;

	echo json_encode($json_list);
});

/*
 * お問い合わせ入力
 * 入力項目
 */
$app->post('/inquiry/input', function () use ($app) {
  $params = json_decode(file_get_contents('php://input'), true);

  // アカウントセッション取得
  $auth = $app->session->get('auth');

  // フロントパラメータ取得
  if (!empty($params['data'])) {
    $cond = $params['data'];
  }
  //ChromePhp::LOG($cond);

  $json_list = array();
  //--アカウント権限（ユーザー区分）--//
  $json_list['user_type'] = $auth["user_type"];

  //--企業名--//
  $query_list = array();
  $list = array();
  $all_list = array();
  array_push($query_list, "corporate_id = '".$auth["corporate_id"]."'");
  $query = implode(' AND ', $query_list);

  $arg_str = 'SELECT ';
  $arg_str .= ' * ';
  $arg_str .= ' FROM ';
  $arg_str .= 'm_corporate';
  $arg_str .= ' WHERE ';
  $arg_str .= $query;
  $arg_str .= ' ORDER BY corporate_id ASC';
  $m_corporate = new MCorporate();
  $results = new Resultset(null, $m_corporate, $m_corporate->getReadConnection()->query($arg_str));
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
      $list['corporate_id'] = $result->corporate_id;
      $list['corporate_name'] = $result->corporate_name;
      array_push($all_list, $list);
    }
  } else {
    $list['corporate_id'] = "";
    $list['corporate_name'] = "";
    array_push($all_list, $list);
  }
  $json_list['corporate_list'] = $all_list;

  //--契約No--/
  $query_list = array();
  $list = array();
  $all_list = array();
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
  $arg_str .= ' FROM m_contract INNER JOIN';
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
      if (!empty($cond["agreement_no"])) {
        if ($list['rntl_cont_no'] == $cond["agreement_no"]) {
          $list['selected'] = "selected";
        } else {
          $list['selected'] = '';
        }
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

  //--ジャンル--//
  $query_list = array();
  $list = array();
  $all_list = array();
  array_push($query_list, "cls_cd = '024'");
  $query = implode(' AND ', $query_list);

  $m_gencode_results = MGencode::query()
      ->where($query)
      ->columns('*')
      ->execute();

  foreach ($m_gencode_results as $m_gencode_result) {
    $list['gen_cd'] = $m_gencode_result->gen_cd;
    $list['gen_name'] = $m_gencode_result->gen_name;
    if (!empty($cond["genre"])) {
      if ($list['gen_cd'] == $cond["genre"]) {
        $list['selected'] = "selected";
      } else {
        $list['selected'] = '';
      }
    } else {
      $list['selected'] = '';
    }
    array_push($all_list, $list);
  }
  $json_list['genre_list'] = $all_list;

  echo json_encode($json_list);
});

/*
 * お問い合わせ入力
 * 入力値チェック、登録処理
 */
$app->post('/inquiry/complete', function () use ($app) {
  $params = json_decode(file_get_contents('php://input'), true);

  // アカウントセッション取得
  $auth = $app->session->get('auth');

  // フロントパラメータ取得
  $cond = $params['data'];
  //ChromePhp::LOG($cond);

  $json_list = array();

  $json_list["err_code"] = "0";
  $json_list["err_msg"] = array();

  //--入力値チェック--//
  // 企業名
  $query_list = array();
  array_push($query_list, "corporate_id = '".$cond["corporate"]."'");
  $query = implode(' AND ', $query_list);

  $arg_str = 'SELECT ';
  $arg_str .= ' * ';
  $arg_str .= ' FROM ';
  $arg_str .= 'm_corporate';
  $arg_str .= ' WHERE ';
  $arg_str .= $query;
  $m_corporate = new MCorporate();
  $results = new Resultset(null, $m_corporate, $m_corporate->getReadConnection()->query($arg_str));
  $results_array = (array) $results;
  $results_cnt = $results_array["\0*\0_count"];
  if ($results_cnt == 0) {
    $json_list["err_code"] = "1";
    $err_msg = "企業名の値が不正です。";
    array_push($json_list["err_msg"], $err_msg);
  }
  // 契約No
  if (empty($cond["agreement_no"])) {
    $json_list["err_code"] = "1";
    $err_msg = "契約Noが未選択です。";
    array_push($json_list["err_msg"], $err_msg);
  }
  if (!empty($cond["agreement_no"])) {
    $query_list = array();
    array_push($query_list, "rntl_cont_no = '".$cond["agreement_no"]."'");
    $query = implode(' AND ', $query_list);

    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_contract';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $m_contract = new MContract();
    $results = new Resultset(null, $m_contract, $m_contract->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    if ($results_cnt == 0) {
      $json_list["err_code"] = "1";
      $err_msg = "契約Noの値が不正です。";
      array_push($json_list["err_msg"], $err_msg);
    }
  }
  // 拠点
  if (empty($cond["section"])) {
    $json_list["err_code"] = "1";
    $err_msg = "拠点が未選択です。";
    array_push($json_list["err_msg"], $err_msg);
  }
  if (!empty($cond["section"])) {
    $query_list = array();
    array_push($query_list, "rntl_sect_cd = '".$cond["section"]."'");
    $query = implode(' AND ', $query_list);

    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_section';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $m_section = new MSection();
    $results = new Resultset(null, $m_section, $m_section->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    if ($results_cnt == 0) {
      $json_list["err_code"] = "1";
      $err_msg = "拠点の値が不正です。";
      array_push($json_list["err_msg"], $err_msg);
    }
  }
  // お名前
  if (mb_strlen($cond["interrogator_name"]) == 0) {
    $json_list["err_code"] = "1";
    $err_msg = "お名前が未入力です。";
    array_push($json_list["err_msg"], $err_msg);
  }
  // ジャンル
  if (empty($cond["genre"])) {
    $json_list["err_code"] = "1";
    $err_msg = "ジャンルが未選択です。";
    array_push($json_list["err_msg"], $err_msg);
  }
  if (!empty($cond["genre"])) {
    $query_list = array();
    array_push($query_list, "cls_cd = '024'");
    array_push($query_list, "gen_cd = '".$cond["genre"]."'");
    $query = implode(' AND ', $query_list);

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
    if ($results_cnt == 0) {
      $json_list["err_code"] = "1";
      $err_msg = "ジャンルの値が不正です。";
      array_push($json_list["err_msg"], $err_msg);
    }
  }
  // お問い合わせ内容
  if (mb_strlen($cond["interrogator_info"]) == 0) {
    $json_list["err_code"] = "1";
    $err_msg = "お問い合わせ内容が未入力です。";
    array_push($json_list["err_msg"], $err_msg);
  }
  // 入力値に不正があった場合は、以降処理せずエラーレスポンス
  if ($json_list["err_code"] !== "0") {
    echo json_encode($json_list);
    return;
  }

  //--お問い合わせ内容登録--//
  // トランザクション開始
  $t_inquiry = new TInquiry();
  $results = new Resultset(NULL, $t_inquiry, $t_inquiry->getReadConnection()->query('begin'));
  try {
    $calum_list = array();
    $values_list = array();

    // 企業ID
    array_push($calum_list, "corporate_id");
    array_push($values_list, "'".$cond["corporate"]."'");
    // 契約No
    array_push($calum_list, "rntl_cont_no");
    array_push($values_list, "'".$cond['agreement_no']."'");
    // レンタル部門コード
    array_push($calum_list, "rntl_sect_cd");
    array_push($values_list, "'".$cond['section']."'");
    // お名前
    array_push($calum_list, "interrogator_name");
    array_push($values_list, "'".$cond['interrogator_name']."'");
    // ジャンル
    array_push($calum_list, "category_name");
    array_push($values_list, "'".$cond['genre']."'");
    // お問い合わせ日時
    array_push($calum_list, "interrogator_date");
    array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
    // お問い合わせ内容
    array_push($calum_list, "interrogator_info");
    array_push($values_list, "'".$cond['interrogator_info']."'");
    // 回答ステータス
    array_push($calum_list, "interrogator_status");
    array_push($values_list, "'1'");
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
    $calum_query = implode(',', $calum_list);
    $values_query = implode(',', $values_list);

    $arg_str = "";
    $arg_str = "INSERT INTO t_inquiry";
    $arg_str .= "(".$calum_query.")";
    $arg_str .= " VALUES ";
    $arg_str .= "(".$values_query.")";
    //ChromePhp::LOG($arg_str);
    $t_inquiry = new TInquiry();
    $results = new Resultset(NULL, $t_inquiry, $t_inquiry->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];

    // トランザクションコミット
    $t_inquiry = new TInquiry();
    $results = new Resultset(NULL, $t_inquiry, $t_inquiry->getReadConnection()->query('commit'));
  }  catch (Exception $e) {
    // トランザクションロールバック
    $t_inquiry = new TInquiry();
    $results = new Resultset(NULL, $t_inquiry, $t_inquiry->getReadConnection()->query('rollback'));
    ChromePhp::LOG($e);

    $json_list["err_code"] = "1";
    $err_msg = "登録処理において、予期せぬエラーが発生しました。";
    array_push($json_list["err_msg"], $err_msg);

    echo json_encode($json_list);
    return;
  }

  echo json_encode($json_list);
});

/*
 * お問い合わせ詳細
 * 詳細項目
 */
$app->post('/inquiry/detail', function () use ($app) {
  $params = json_decode(file_get_contents('php://input'), true);

  // アカウントセッション取得
  $auth = $app->session->get('auth');

  // フロントパラメータ取得
  $cond = $params['data'];
  //ChromePhp::LOG($cond);

  $json_list = array();

  //--アカウントタイプ（ユーザー区分）--//
  $json_list["user_type"] = $auth["user_type"];

  //--お問い合わせ詳細内容--//
  $query_list = array();
  $list = array();
  $all_list = array();
  array_push($query_list, "index = '".$cond["index"]."'");
  $query = implode(' AND ', $query_list);

  $arg_str = 'SELECT ';
  $arg_str .= 't_inquiry.index as as_index,';
  $arg_str .= 't_inquiry.corporate_id as as_corporate_id,';
  $arg_str .= 't_inquiry.rntl_sect_cd as as_rntl_sect_cd,';
  $arg_str .= 't_inquiry.rntl_cont_no as as_rntl_cont_no,';
  $arg_str .= 't_inquiry.interrogator_name as as_interrogator_name,';
  $arg_str .= 't_inquiry.category_name as as_category_name,';
  $arg_str .= 't_inquiry.interrogator_info as as_interrogator_info,';
  $arg_str .= 't_inquiry.interrogator_answer as as_interrogator_answer,';
  $arg_str .= 'm_corporate.corporate_name as as_corporate_name,';
  $arg_str .= 'm_contract.rntl_cont_name as as_rntl_cont_name,';
  $arg_str .= 'm_section.rntl_sect_name as as_rntl_sect_name';
  $arg_str .= ' FROM ';
  $arg_str .= 't_inquiry';
  $arg_str .= " INNER JOIN m_corporate";
	$arg_str .= " ON t_inquiry.corporate_id = m_corporate.corporate_id";
	$arg_str .= " INNER JOIN m_contract";
  $arg_str .= " ON (t_inquiry.corporate_id = m_contract.corporate_id";
	$arg_str .= " AND t_inquiry.rntl_cont_no = m_contract.rntl_cont_no)";
  $arg_str .= " INNER JOIN m_section";
  $arg_str .= " ON (t_inquiry.corporate_id = m_section.corporate_id";
  $arg_str .= " AND t_inquiry.rntl_cont_no = m_section.rntl_cont_no";
  $arg_str .= " AND t_inquiry.rntl_sect_cd = m_section.rntl_sect_cd)";
  $arg_str .= ' WHERE ';
  $arg_str .= $query;
  $t_inquiry = new TInquiry();
  $results = new Resultset(null, $t_inquiry, $t_inquiry->getReadConnection()->query($arg_str));
  $results_array = (array) $results;
  $results_cnt = $results_array["\0*\0_count"];
  $json_list["detail_cnt"] = $results_cnt;
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
      // お問い合わせID（シーケンスID）
      $list['index'] = $result->as_index;
      // 企業名
      $list['corporate'] = $result->as_corporate_id." ".$result->as_corporate_name;
      // 契約No
      $list['agreement'] = $result->as_rntl_cont_no." ".$result->as_rntl_cont_name;
      // 拠点
      $list['section'] = $result->as_rntl_sect_name;
      // お名前
      $list['interrogator_name'] = $result->as_interrogator_name;
      // ジャンル
      $query_list = array();
      array_push($query_list, "cls_cd = '024'");
      array_push($query_list, "gen_cd = '".$result->as_category_name."'");
      $query = implode(' AND ', $query_list);
      $m_gencode_results = MGencode::query()
          ->where($query)
          ->columns('*')
          ->execute();
      foreach ($m_gencode_results as $m_gencode_result) {
        $list['category_cd'] = $m_gencode_result->gen_cd;
        $list['category_name'] = $m_gencode_result->gen_name;
      }
      // お問い合わせ内容
      $list['interrogator_info'] = $result->as_interrogator_info;
      // お問い合わせ回答
      $list['interrogator_answer'] = $result->as_interrogator_answer;
    }
    array_push($all_list, $list);
  }
  $json_list["detail_list"] = $all_list;

  echo json_encode($json_list);
});

/*
 * お問い合わせ詳細
 * 回答内容更新
 */
$app->post('/inquiry/update', function () use ($app) {
  $params = json_decode(file_get_contents('php://input'), true);

  // アカウントセッション取得
  $auth = $app->session->get('auth');

  // フロントパラメータ取得
  $cond = $params['data'];
  //ChromePhp::LOG($cond);

  $json_list = array();

  $json_list["err_code"] = "0";
  $json_list["err_msg"] = array();

  //--お問い合わせ内容登録--//
  // トランザクション開始
  $t_inquiry = new TInquiry();
  $results = new Resultset(NULL, $t_inquiry, $t_inquiry->getReadConnection()->query('begin'));
  try {
    $src_query_list = array();
    array_push($src_query_list, "index = '".$cond['index']."'");
    $src_query = implode(' AND ', $src_query_list);

    $up_query_list = array();
    // お問い合わせ回答日時
    array_push($up_query_list, "interrogator_answer_date = '".date("Y-m-d H:i:s", time())."'");
    // お問い合わせ回答
    array_push($up_query_list, "interrogator_answer = '".$cond['interrogator_answer']."'");
    // 回答ステータス
    array_push($up_query_list, "interrogator_status = '2'");
    // 更新日時
    array_push($up_query_list, "upd_date = '".date("Y-m-d H:i:s", time())."'");
    // 更新ユーザーID
    array_push($up_query_list, "upd_user_id = '".$auth['accnt_no']."'");
    $up_query = implode(',', $up_query_list);

    $arg_str = "";
    $arg_str = "UPDATE t_inquiry SET ";
    $arg_str .= $up_query;
    $arg_str .= " WHERE ";
    $arg_str .= $src_query;
    //ChromePhp::LOG($arg_str);
    $t_inquiry = new TInquiry();
    $results = new Resultset(NULL, $t_inquiry, $t_inquiry->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    // トランザクションコミット
    $t_inquiry = new TInquiry();
    $results = new Resultset(NULL, $t_inquiry, $t_inquiry->getReadConnection()->query('commit'));
  }  catch (Exception $e) {
    // トランザクションロールバック
    $t_inquiry = new TInquiry();
    $results = new Resultset(NULL, $t_inquiry, $t_inquiry->getReadConnection()->query('rollback'));
    ChromePhp::LOG($e);

    $json_list["err_code"] = "1";
    $err_msg = "更新処理において、予期せぬエラーが発生しました。";
    array_push($json_list["err_msg"], $err_msg);

    echo json_encode($json_list);
    return;
  }

  echo json_encode($json_list);
});
