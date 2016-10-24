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
    array_push($query_list,"t_inquiry.interrogator_date >= '".$cond['contact_day_to']."'");
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
	$arg_str .= " ON t_inquiry.rntl_cont_no = m_contract.rntl_cont_no";
  $arg_str .= " INNER JOIN m_section";
  $arg_str .= " ON t_inquiry.rntl_sect_cd = m_section.rntl_sect_cd";
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
    $err_msg = "登録処理において、データ更新エラーが発生しました。";
    array_push($json_list["err_msg"], $err_msg);

    echo json_encode($json_list);
    return;
  }

  echo json_encode($json_list);
});

/**
 * 請求書データ照会詳細モーダル
 */
$app->post('/manpower_info/detail', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	// フロントパラメータ取得
	$cond = $params;
	//ChromePhp::log($cond);

	// json返却値
	$json_list = array();

	//---小見出し項目---//
	$heading = array();
	$heading_list = array();
	// 対象年月
	if (!empty($cond['yyyymm'])) {
		$heading['yyyymm'] = $cond['yyyymm'];
		$heading['yyyymm'] = date('Y年m月', strtotime($heading['yyyymm']));
	} else {
		$heading['yyyymm'] = '';
	}
	// 拠点コード
	$heading['rntl_sect_cd'] = $cond['rntl_sect_cd'];
	// 拠点名
	$heading['rntl_sect_name'] = $cond['rntl_sect_name'];
	// 人数
	$heading['staff_total'] = $cond['staff_total'];
	array_push($heading_list, $heading);
	$json_list['heading'] = $heading_list;

	//---詳細検索条件---//
	$query_list = array();
	//企業ID
	array_push($query_list,"t_staff_detail_body.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	if(!empty($cond['agreement_no'])){
		array_push($query_list,"t_staff_detail_body.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//拠点
	if(!empty($cond['rntl_sect_cd'])){
		array_push($query_list,"t_staff_detail_body.rntl_sect_cd = '".$cond['rntl_sect_cd']."'");
	}
	//対象年月
	if(!empty($cond['yyyymm'])){
		array_push($query_list,"TO_DATE(t_staff_detail_body.yyyymm,'YYYY/MM') = TO_DATE('".$cond['yyyymm']."','YYYY/MM')");
	}

	$query = implode(' AND ', $query_list);

	$arg_str = "SELECT ";
	$arg_str .= "t_staff_detail_body.rntl_sect_cd as as_rntl_sect_cd,";
	$arg_str .= "t_staff_detail_body.yyyymm as as_yyyymm,";
	$arg_str .= "t_staff_detail_body.line_no as as_line_no,";
	$arg_str .= "t_staff_detail_body.staff_detail_data as as_staff_detail_data";
	$arg_str .= " FROM t_staff_detail_body";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= " ORDER BY t_staff_detail_body.line_no ASC";

	$t_staff_detail_body = new TStaffDetailBody();
	$results = new Resultset(null, $t_staff_detail_body, $t_staff_detail_body->getReadConnection()->query($arg_str));
	$result_obj = (array)$results;
	$results_cnt = $result_obj["\0*\0_count"];
	//ChromePhp::log($m_wearer_std->getReadConnection()->query($arg_str));

	$label = array();
	$label_list = array();
	$detail_list = array();

	if(!empty($results_cnt)){
		$paginator_model = new PaginatorModel(
			array(
				"data"  => $results,
				"limit" => $results_cnt,
				"page" => 1
			)
		);
		$paginator = $paginator_model->getPaginate();
		$results = $paginator->items;

		foreach($results as $result) {
			// 詳細項目ラベル設定
			if ($result->as_line_no === 0) {
				if (!empty($result->as_staff_detail_data)) {
					$headers = explode(',', $result->as_staff_detail_data);
					foreach($headers as $header) {
						$label["data"] = $header;
						array_push($label_list, $label);
					}
				}

				continue;
			}

			// 詳細データリスト設定
			if (!empty($result->as_staff_detail_data)) {
				$detail = array();
				$manpower_details = explode(',', $result->as_staff_detail_data);
				foreach($manpower_details as $manpower_detail) {
					$list["data"] = $manpower_detail;
					array_push($detail, $list);
				}

				array_push($detail_list, $detail);
			}
		}

		$json_list['label_list'] = $label_list;
		$json_list['detail_list'] = $detail_list;
		//ChromePhp::log($json_list['heading']);
	} else {
		$json_list['label_list'] = null;
		$json_list['detail_list'] = null;
		//ChromePhp::log($json_list['heading']);
	}

	echo json_encode($json_list);
});
