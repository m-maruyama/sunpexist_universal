<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * お問い合わせ管理
 * 一覧検索
 */
$app->post('/info/search', function ()use($app){
	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();
	//ChromePhp::log($cond);

  $json_list = array();

	$cond = $params['cond'];
	$page = $params['page'];

  $list = array();
  $all_list = array();

  $arg_str = 'SELECT ';
  $arg_str .= ' t_info.index as as_index,';
  $arg_str .= ' t_info.corporate_id as as_corporate_id,';
  $arg_str .= ' t_info.message as as_message,';
  $arg_str .= ' t_info.display_order as as_display_order,';
  $arg_str .= ' t_info.open_date as as_open_date,';
  $arg_str .= ' t_info.close_date as as_close_date,';
  $arg_str .= ' m_corporate.corporate_name as as_corporate_name';
  $arg_str .= ' FROM ';
  $arg_str .= 't_info';
  $arg_str .= ' INNER JOIN m_corporate';
  $arg_str .= ' ON t_info.corporate_id = m_corporate.corporate_id';
  $arg_str .= ' ORDER BY t_info.index ASC';
  $t_info = new TInfo();
  $results = new Resultset(null, $t_info, $t_info->getReadConnection()->query($arg_str));
  $results_array = (array) $results;
  $results_cnt = $results_array["\0*\0_count"];
  if (!empty($results_cnt)) {
    $paginator_model = new PaginatorModel(
      array(
        "data"  => $results,
        "limit" => $page['records_per_page'],
        "page" => $page['page_number']
      )
    );
    $paginator = $paginator_model->getPaginate();
    $results = $paginator->items;
    foreach ($results as $result) {
  		$list['index'] = $result->as_index;
      $list['corporate_id'] = $result->as_corporate_id;
      $list['corporate_name'] = $result->as_corporate_name;
  		$list['message'] = mb_strimwidth(htmlspecialchars($result->as_message), 0, 100, "・・・");
  		$list['display_order'] = $result->as_display_order;
  		$list['open_date'] = date('Y/m/d H:i', strtotime($result->as_open_date));
  		$list['close_date'] = date('Y/m/d H:i', strtotime($result->as_close_date));

  		$all_list[] = $list;
  	}
  }
	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = count($results);
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;
	// ChromePhp::log($json_list);

	echo json_encode($json_list);
});

/*
 * お知らせ管理
 * お知らせ追加
 */
$app->post('/info/add', function () use ($app) {
  $params = json_decode(file_get_contents('php://input'), true);

  // アカウントセッション
  $auth = $app->session->get('auth');

  // フロントパラメータ
	$cond = $params['data'];
  //ChromePhp::LOG($cond);

  $json_list = array();

	// エラーコード 0:正常 1:異常
	$json_list["error_code"] = "0";
	$error_msg = array();

	if ($cond["mode"] == "input") {
		//--企業名--//
	  $query_list = array();
		$all_list = array();
	  $list = array();
		$arg_str = '';
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
	    foreach ($results as $result) {
	      $list['corporate_id'] = $result->corporate_id;
	      $list['corporate_name'] = $result->corporate_name;
	      $all_list[] = $list;
	    }
	  } else {
	    $list['corporate_id'] = "";
	    $list['corporate_name'] = "";
	    $all_list[] = $list;
	  }
	  $json_list['corporate_list'] = $all_list;

		echo json_encode($json_list);
	}
	if ($cond["mode"] == "update") {
		//--入力値チェック--//
		_input_check($cond, $error_msg);
		if (!empty($error_msg)) {
			// エラーがあった場合、以降処理をしない
			$json_list["error_code"] = "1";
			$json_list["error_msg"] = $error_msg;
			echo json_encode($json_list);
			return;
		}

		//--お知らせ登録処理--//
		// CALUM値の設定
    $calum_list = array(
      "corporate_id",
      "message",
      "display_order",
      "open_date",
      "close_date",
      "rgst_date",
      "rgst_user_id",
      "upd_date",
      "upd_user_id"
    );
    $calum_query = implode(",", $calum_list);
		// VALUES値の設定
    $values_list = array();
		$values_list[] = "'".$cond["corporate"]."'";
		$values_list[] = "'".$cond["message"]."'";
		$values_list[] = "'".$cond["display_order"]."'";
		$values_list[] = "'".$cond["open_date"]."'";
		$values_list[] = "'".$cond["close_date"]."'";
		$values_list[] = "'".date("Y-m-d H:i:s", time())."'";
		$values_list[] = "'".$auth["accnt_no"]."'";
		$values_list[] = "'".date("Y-m-d H:i:s", time())."'";
		$values_list[] = "'".$auth["accnt_no"]."'";
		$values_query = implode(",", $values_list);

	  $t_info = new TInfo();
	  $results = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query('begin'));
		try {
			$arg_str = "";
	    $arg_str = "INSERT INTO t_info";
	    $arg_str .= "(".$calum_query.")";
	    $arg_str .= " VALUES ";
	    $arg_str .= "(".$values_query.")";
	    //ChromePhp::LOG($arg_str);
	    $results = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query($arg_str));

	    $transaction = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query("commit"));
		} catch (Exception $e) {
	    $transaction = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query("rollback"));

	    //ChromePhp::log($e);
			$json_list["error_code"] = "1";
	    $error_msg[] = 'E001 登録処理中に予期せぬエラーが発生しました。';
	    $json_list['error_msg'] = $error_msg;
	    echo json_encode($json_list);
	    return;
	  }

		echo json_encode($json_list);
	}
});

/*
 * お知らせ管理
 * お知らせ編集
 */
$app->post('/info/edit', function () use ($app) {
  $params = json_decode(file_get_contents('php://input'), true);

  // アカウントセッション
  $auth = $app->session->get('auth');

  // フロントパラメータ
	$cond = $params['data'];
  //ChromePhp::LOG($cond);

  $json_list = array();

	// エラーコード 0:正常 1:異常
	$json_list["error_code"] = "0";
	$error_msg = array();

	if ($cond["mode"] == "input") {
		if (empty($cond["id"])) {
			$json_list["error_code"] = "1";
			echo json_encode($json_list);
			return;
		}
		//--お知らせ参照--//
		$query_list = array();
		$query_list[] = "index = ".$cond["id"];
		$query = implode(' AND ', $query_list);
		$arg_str = '';
	  $arg_str = 'SELECT ';
	  $arg_str .= ' * ';
	  $arg_str .= ' FROM ';
	  $arg_str .= 't_info';
		$arg_str .= ' WHERE ';
	  $arg_str .= $query;
		//ChromePhp::log($arg_str);
	  $t_info = new TInfo();
	  $results = new Resultset(null, $t_info, $t_info->getReadConnection()->query($arg_str));
	  $results_array = (array) $results;
	  $results_cnt = $results_array["\0*\0_count"];
		if (!empty($results_cnt)) {
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
				// ID
				$json_list['info_id'] = $result->index;
				// 企業名
			  $query_list = array();
				$query_list[] = "corporate_id = '".$result->corporate_id."'";
				$query = implode(' AND ', $query_list);
				$arg_str = '';
			  $arg_str = 'SELECT ';
			  $arg_str .= ' * ';
			  $arg_str .= ' FROM ';
			  $arg_str .= 'm_corporate';
				$arg_str .= ' WHERE ';
			  $arg_str .= $query;
			  $m_corporate = new MCorporate();
			  $m_corporate_results = new Resultset(null, $m_corporate, $m_corporate->getReadConnection()->query($arg_str));
			  $results_array = (array) $m_corporate_results;
			  $results_cnt = $results_array["\0*\0_count"];
			  if ($results_cnt > 0) {
			    $paginator_model = new PaginatorModel(
			      array(
			        "data"  => $m_corporate_results,
			        "limit" => 1,
			        "page" => 1
			      )
			    );
			    $paginator = $paginator_model->getPaginate();
			    $m_corporate_results = $paginator->items;
			    foreach ($m_corporate_results as $m_corporate_result) {
						$json_list['corporate'] = $m_corporate_result->corporate_name;
			    }
			  } else {
			    $json_list['corporate'] = "";
			  }
				// 表示順
				$json_list['display_order'] = $result->display_order;
				// 公開開始日
				$json_list['open_date'] = date("Y/m/d H:i", strtotime($result->open_date));
				// 公開終了日
				$json_list['close_date'] = date("Y/m/d H:i", strtotime($result->close_date));
				// 表示メッセージ
				$json_list['message'] = $result->message;
			}
		} else {
			// 対象のおしらせ情報が存在しない場合
			$json_list["error_code"] = "1";
	    $error_msg[] = '対象のデータは既に存在していません。';
	    $json_list['error_msg'] = $error_msg;
		}

		echo json_encode($json_list);
	}
	if ($cond["mode"] == "update") {
		//--入力値チェック--//
		_input_check($cond, $error_msg);
		// ※別でIDチェック
		if (empty($cond["info_id"])) {
			$error_msg[] = "ID：値が不正です。";
		}
		if (!empty($error_msg)) {
			// エラーがあった場合、以降処理をしない
			$json_list["error_code"] = "1";
			$json_list["error_msg"] = $error_msg;
			echo json_encode($json_list);
			return;
		}

		//--お知らせ更新処理--//
		$src_query_list = array();
		$src_query_list[] = "index = ".$cond["info_id"];
		$src_query = implode(' AND ', $src_query_list);

		$up_query_list = array();
		$up_query_list[] = "message = '".$cond["message"]."'";
		$up_query_list[] = "display_order = '".$cond["display_order"]."'";
		$up_query_list[] = "open_date = '".$cond["open_date"]."'";
		$up_query_list[] = "close_date = '".$cond["close_date"]."'";
		$up_query_list[] = "upd_date = '".date("Y-m-d H:i:s", time())."'";
		$up_query_list[] = "upd_user_id = '".$auth["accnt_no"]."'";
		$up_query = implode(',', $up_query_list);

	  $t_info = new TInfo();
	  $results = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query('begin'));
		try {
			$arg_str = "";
			$arg_str = "UPDATE t_info SET ";
			$arg_str .= $up_query;
			$arg_str .= " WHERE ";
			$arg_str .= $src_query;
			//ChromePhp::LOG($arg_str);
			$t_info = new TInfo();
			$results = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query($arg_str));
			$result_obj = (array)$results;
			$results_cnt = $result_obj["\0*\0_count"];

	    $transaction = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query("commit"));
		} catch (Exception $e) {
	    $transaction = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query("rollback"));

	    //ChromePhp::log($e);
			$json_list["error_code"] = "1";
	    $error_msg[] = 'E001 更新処理中に予期せぬエラーが発生しました。';
	    $json_list['error_msg'] = $error_msg;
	    echo json_encode($json_list);
	    return;
	  }

		echo json_encode($json_list);
	}
});

/*
 * お知らせ管理
 * お知らせ削除
 */
$app->post('/info/delete', function () use ($app) {
  $params = json_decode(file_get_contents('php://input'), true);

  // アカウントセッション
  $auth = $app->session->get('auth');

  // フロントパラメータ
	$cond = $params['data'];
  //ChromePhp::LOG($cond);

  $json_list = array();

	// エラーコード 0:正常 1:異常
	$json_list["error_code"] = "0";
	$error_msg = array();

	if (empty($cond["info_id"])) {
		$json_list["error_code"] = "1";
		$error_msg[] = "削除するIDが不正です。";
		$json_list['error_msg'] = $error_msg;
		echo json_encode($json_list);
		return;
	}

	$t_info = new TInfo();
	$results = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query('begin'));
	try {
		$query_list = array();
		$query_list[] = "index = ".$cond["info_id"];
		$query = implode(' AND ', $query_list);

		$arg_str = "";
    $arg_str = "DELETE FROM ";
    $arg_str .= "t_info";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    //ChromePhp::LOG($arg_str);
    $t_info = new TInfo();
    $results = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];

		$transaction = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query("commit"));
	} catch (Exception $e) {
		$transaction = new Resultset(NULL, $t_info, $t_info->getReadConnection()->query("rollback"));

		//ChromePhp::log($e);
		$json_list["error_code"] = "1";
		$error_msg[] = 'E001 削除処理中に予期せぬエラーが発生しました。';
		$json_list['error_msg'] = $error_msg;
		echo json_encode($json_list);
		return;
	}

	echo json_encode($json_list);
});

/**
 * 入力値バリデーション
 *
 * 入力値のチェックを行う
 *
 * @param string $cond チェックする値
 * @return error_msg エラーメッセージ
 */
function _input_check($cond, &$error_msg) {
	// 企業名
	if (empty($cond["corporate"])) {
		$error_msg[] = "企業名：未選択です。";
	}
	// 表示順
	if (mb_strlen($cond["display_order"]) == 0) {
		$display_order_err = "err";
		$error_msg[] = "表示順：未入力です。";
	}
	if (empty($display_order_err)) {
		if (!ctype_digit(strval($cond["display_order"]))) {
			$error_msg[] = "表示順：整数で入力してください。";
		}
		if ($cond["display_order"] == 0) {
			$error_msg[] = "表示順：0以上の整数で入力してください。";
		}
	}
	// 公開開始日、公開終了日
	if (empty($cond["open_date"])) {
		$date_err = "err";
		$error_msg[] = "公開開始日：未入力です。";
	}
	if (!empty($cond["open_date"])) {
		if (!strptime($cond["open_date"], '%Y/%m/%d %H:%M')) {
			$date_err = "err";
			$error_msg[] = "公開開始日：日付形式が不正です。";
		}
	}
	if (empty($cond["close_date"])) {
		$date_err = "err";
		$error_msg[] = "公開終了日：未入力です。";
	}
	if (!empty($cond["close_date"])) {
		if (!strptime($cond["close_date"], '%Y/%m/%d %H:%M')) {
			$date_err = "err";
			$error_msg[] = "公開終了日：日付形式が不正です。";
		}
	}
	if (empty($date_err)) {
		if (strtotime($cond["open_date"]) < time()) {
			$error_msg[] = "公開開始日：現日時以降で設定してください。";
		}
		if (strtotime($cond["close_date"]) < time()) {
			$error_msg[] = "公開終了日：現日時以降で設定してください。";
		}
		if (strtotime($cond["open_date"]) > strtotime($cond["close_date"])) {
			$error_msg[] = "公開開始日、公開終了日：正しく設定してください。";
		}
	}
	// 表示メッセージ
	if (empty($cond["message"])) {
		$error_msg[] = "表示メッセージが未入力です。";
	}

	return;
}
