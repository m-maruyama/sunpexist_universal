<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;




/**
 * Q&A
 * 画面コンディション
 */
$app->post('/qa/condition', function ()use($app){
	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['data'];
	//ChromePhp::log($cond);

  $json_list = array();
  $list = array();
  $all_list = array();

	//--ログインしているアカウントのユーザー権限により表示制御--//
	$json_list["user_type"] = $auth["user_type"];
	if ($auth["user_type"] !== "1") {
		$arg_str = '';
		$arg_str .= 'SELECT ';
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
					if ($list['corporate_id'] == $auth["corporate_id"]) {
            $list['selected'] = "selected";
          } else {
            $list['selected'] = "";
          }
        }

	      $all_list[] = $list;
	    }
	  } else {
			$list['corporate_id'] = "";
			$list['corporate_name'] = "";
      $list['selected'] = "";
			$all_list[] = $list;
	  }
	}
  $json_list['corporate_list'] = $all_list;

  echo json_encode($json_list);
});

/**
 * Q&A
 * 表示
 */
$app->post('/qa/search', function ()use($app){
	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['data'];
	//ChromePhp::log($cond);

  $json_list = array();

	$json_list["case_info"] = "";

	// Q&A参照
	$query_list = array();
	if (!empty($cond["corporate"])) {
		$query_list[] = "corporate_id = '".$cond["corporate"]."'";
	} else {
		$query_list[] = "corporate_id = '".$auth['corporate_id']."'";
	}
	$query = implode(' AND ', $query_list);
	$arg_str = '';
  $arg_str .= 'SELECT ';
  $arg_str .= 'case_info';
  $arg_str .= ' FROM ';
  $arg_str .= 'q_and_a';
	$arg_str .= " WHERE ";
	$arg_str .= $query;
  $q_and_a = new QAndA();
  $results = new Resultset(null, $q_and_a, $q_and_a->getReadConnection()->query($arg_str));
  $results_array = (array) $results;
  $results_cnt = $results_array["\0*\0_count"];
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
			$json_list["case_info"] = $result->case_info;
  	}
  }

	echo json_encode($json_list);
});

/**
 * Q&A
 * 編集
 */
$app->post('/qa/input', function ()use($app){
	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['data'];
	//ChromePhp::LOG($cond);

  $json_list = array();

	$json_list["error_code"] = "0";
	$error_msg = array();

	//--入力項目表示--//
	if ($cond["mode"] == "input") {
		if (empty($cond["corporate"])) {
			$json_list["error_code"] = "1";
			echo json_encode($json_list);
			return;
		}

		// 企業
		$query_list = array();
		$query_list[] = "corporate_id = '".$cond["corporate"]."'";
		$query = implode(' AND ', $query_list);
		$arg_str = '';
		$arg_str .= 'SELECT ';
		$arg_str .= ' * ';
		$arg_str .= ' FROM ';
		$arg_str .= 'm_corporate';
		$arg_str .= " WHERE ";
		$arg_str .= $query;
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
				$json_list["corporate_id"] = $result->corporate_id;
				$json_list["corporate_name"] = $result->corporate_id." ".$result->corporate_name;
			}
		} else {
			// 企業ID不在のためエラー
			$json_list["error_code"] = "1";
			echo json_encode($json_list);
			return;
		}

		// 内容
		$query_list = array();
		$query_list[] = "corporate_id = '".$cond["corporate"]."'";
		$query = implode(' AND ', $query_list);
		$arg_str = '';
		$arg_str .= 'SELECT ';
		$arg_str .= ' * ';
		$arg_str .= ' FROM ';
		$arg_str .= 'q_and_a';
		$arg_str .= " WHERE ";
		$arg_str .= $query;
		$q_and_a = new QAndA();
		$results = new Resultset(null, $q_and_a, $q_and_a->getReadConnection()->query($arg_str));
		$results_array = (array) $results;
		$results_cnt = $results_array["\0*\0_count"];
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
				$json_list["case_info"] = $result->case_info;
			}
		} else {
			$json_list["case_info"] = "";
		}

	  echo json_encode($json_list);
	}
	//--Q&A更新--//
	if ($cond["mode"] == "update") {
		// 企業IDチェック
		if (empty($cond["corporate"])) {
			$error_msg[] = "企業：値が不正です。";
		}
		if (!empty($error_msg)) {
			// エラーがあった場合、以降処理をしない
			$json_list["error_code"] = "1";
			$json_list["error_msg"] = $error_msg;
			echo json_encode($json_list);
			return;
		}

		// Q&A参照
		$query_list = array();
		$query_list[] = "corporate_id = '".$cond["corporate"]."'";
		$query = implode(' AND ', $query_list);
		$arg_str = '';
	  $arg_str .= 'SELECT ';
	  $arg_str .= '*';
	  $arg_str .= ' FROM ';
	  $arg_str .= 'q_and_a';
		$arg_str .= " WHERE ";
		$arg_str .= $query;
	  $q_and_a = new QAndA();
	  $results = new Resultset(null, $q_and_a, $q_and_a->getReadConnection()->query($arg_str));
	  $results_array = (array) $results;
	  $results_cnt = $results_array["\0*\0_count"];

		$q_and_a = new QAndA();
		$results = new Resultset(NULL, $q_and_a, $q_and_a->getReadConnection()->query('begin'));
		try {
			if (!empty($results_cnt)) {
				// 既にQ&A情報が存在する場合は更新処理
				$src_query_list = array();
				$src_query_list[] = "corporate_id = '".$cond["corporate"]."'";
				$src_query = implode(' AND ', $src_query_list);

                //入力ソースのエスケープ処理
                $case_info_sorce = $cond["case_info"];
                $case_info_sorce = str_replace("'","''",$case_info_sorce);

				$up_query_list = array();
				$up_query_list[] = "case_info = '".$case_info_sorce."'";
				$up_query_list[] = "upd_date = '".date("Y-m-d H:i:s", time())."'";
				$up_query_list[] = "upd_user_id = '".$auth["accnt_no"]."'";
				$up_query = implode(',', $up_query_list);

				$arg_str = "";
				$arg_str = "UPDATE q_and_a SET ";
				$arg_str .= $up_query;
				$arg_str .= " WHERE ";
				$arg_str .= $src_query;
				ChromePhp::LOG($arg_str);
				$results = new Resultset(NULL, $q_and_a, $q_and_a->getReadConnection()->query($arg_str));
				$result_obj = (array)$results;
				$results_cnt = $result_obj["\0*\0_count"];
			} else {
				// Q&A情報が存在しない場合は新規登録
				// CALUM値の設定
				$calum_list = array(
					"corporate_id",
					"case_info",
					"rgst_date",
					"rgst_user_id",
					"upd_date",
					"upd_user_id"
				);
				$calum_query = implode(",", $calum_list);
				// VALUES値の設定
				$values_list = array();
				$values_list[] = "'".$cond["corporate"]."'";
				$values_list[] = "'".$cond["case_info"]."'";
				$values_list[] = "'".date("Y-m-d H:i:s", time())."'";
				$values_list[] = "'".$auth["accnt_no"]."'";
				$values_list[] = "'".date("Y-m-d H:i:s", time())."'";
				$values_list[] = "'".$auth["accnt_no"]."'";
				$values_query = implode(",", $values_list);

				$arg_str = "";
				$arg_str = "INSERT INTO q_and_a";
				$arg_str .= "(".$calum_query.")";
				$arg_str .= " VALUES ";
				$arg_str .= "(".$values_query.")";
				//ChromePhp::LOG($arg_str);
				$results = new Resultset(NULL, $q_and_a, $q_and_a->getReadConnection()->query($arg_str));
			}

			$transaction = new Resultset(NULL, $q_and_a, $q_and_a->getReadConnection()->query("commit"));
		} catch (Exception $e) {
			$transaction = new Resultset(NULL, $q_and_a, $q_and_a->getReadConnection()->query("rollback"));

			ChromePhp::log($e);
			$json_list["error_code"] = "1";
			$error_msg[] = 'E001 更新処理中に予期せぬエラーが発生しました。';
			$json_list['error_msg'] = $error_msg;
			echo json_encode($json_list);
			return;
		}

		echo json_encode($json_list);
	}
});
