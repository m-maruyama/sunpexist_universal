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
          if ($list['corporate_id'] == $auth["corporate_id"]) {
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
