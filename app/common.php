<?php
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
//前処理
$app->before(function()use($app){
	$params = json_decode(file_get_contents("php://input"), true);
	if(!$params&&isset($_FILES['file'])){
		$params['scr'] = 'upfile:'.$_FILES['file']['name'];
	}
	//操作ログ
	$log	= new TLog();
	if(isset($params['scr'])){
		if($params['scr'] != 'ログイン'&&$params['scr'] != 'パスワード変更'){
			if (!$app->session->has("auth")) {
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
	$auth = $app->session->get("auth");
	if(isset($auth['user_id'])){
		$log->user_id = $auth['user_id']; //操作ユーザーID
	} else {
		$log->user_id = '{}';
	}
	$now = date('Y/m/d H:i:s.sss');
	$log->ctrl_date = $now; //操作日時
	$log->access_url = $_SERVER["HTTP_REFERER"]; //アクセスURL
	if(file_get_contents("php://input")){
		$log->post_param = file_get_contents("php://input"); //POSTパラメーター
	}else if($_FILES){
		$log->post_param = $_FILES;
	}else{
		$log->post_param = '{}';
	}
	$log->ip_address = $_SERVER["REMOTE_ADDR"]; //端末識別情報
	$log->user_agent = $_SERVER["HTTP_USER_AGENT"]; //USER_AGENT
	$log->memo = "メモ"; //   メモ
	if ($log->save() == false) {
		// $error_list['update'] = '操作ログの登録に失敗しました。';
		// $json_list['errors'] = $error_list;
		// echo json_encode($json_list);
		return true;
	}
});
/**
 * グローバルメニュー
 */
$app->post('/global_menu', function ()use($app){
	$auth = $app->session->get("auth");
	$user_name = array();
	$json_list['login_disp_name'] = $auth['login_disp_name'];
	if($auth['user_type'] != '1'){
		$json_list['admin'] = $auth['user_type'];
	}
	echo json_encode($json_list);
});

/**
 * 契約No取得
 */
$app->post('/agreement_no', function () {
	$params = json_decode(file_get_contents("php://input"), true);

	$query_list = array();
	$list = array();
	$all_list = array();
	$json_list = array();

	// アカウントマスタ.企業ID
	array_push($query_list,"MAccount.m_account = ".$app->session->get("auth")['corporate_id']);
	// アカウントマスタ. ユーザーID
	array_push($query_list,"MAccount.user_id = ".$app->session->get("auth")['corporate_id']);

/*
	$results = MContract::find(array(
		'order'	  => "cast(rntl_cont_no as integer) asc"
	));
*/
	//初っ端は空データ
//	$list['rntl_cont_no'] = null;
//	$list['rntl_cont_name'] = null;
//	array_push($all_list,$list);
	foreach ($results as $result) {
		$list['rntl_cont_no'] = $result->rntl_cont_no;
		$list['rntl_cont_name'] = $result->rntl_cont_name;
		array_push($all_list,$list);
	}
	$json_list['agreement_no_list'] = $all_list;
	echo json_encode($json_list);
});

/**
 * 貸与パターン取得
 */
$app->post('/job_type', function () {
	$params = json_decode(file_get_contents("php://input"), true);
	$results = MJobType::find(array(
		'order'	  => "cast(job_type_cd as integer) asc"
	));
	$list = array();
	$all_list = array();
	$json_list = array();
	//初っ端は空データ
	$list['job_type_cd'] = null;
	$list['job_type_name'] = '全て';
	array_push($all_list,$list);
	foreach ($results as $result) {
		$list['job_type_cd'] = $result->job_type_cd;
		$list['job_type_name'] = $result->job_type_name;
		array_push($all_list,$list);
	}
	$json_list['job_type_list'] = $all_list;
	echo json_encode($json_list);
});
/**
 * 在庫専用貸与パターン取得
 */
$app->post('/job_type_zaiko', function () {
	$params = json_decode(file_get_contents("php://input"), true);
	$results = MRentPatternForSdmzk::find(array(
		'order'	  => "rent_pattern_data asc"
	));
	$list = array();
	$all_list = array();
	$json_list = array();
	//初っ端は空データ
	$list['job_type_cd'] = null;
	$list['job_type_name'] = null;
	array_push($all_list,$list);
	foreach ($results as $result) {
		$list['job_type_cd'] = $result->rent_pattern_data;
		$list['job_type_name'] = $result->rent_pattern_name;
		// $list['sort'] = $result->sort;
		array_push($all_list,$list);
	}
	$json_list['job_type_list'] = $all_list;
	echo json_encode($json_list);
});
/**
 * 拠点候補取得
 */
$app->post('/suggest', function () {
	$params = json_decode(file_get_contents("php://input"), true);

	//拠点
	if(isset($params['text'])){
		$query = "rntl_sect_name LIKE '%".$params['text']."%'";
		$results = MSection::find(array(
		'conditions' => $query
		));
		$json_list = array();
		$i = 0;
		foreach ($results as $result) {
		$json_list[$i]['office_cd'] = $result->rntl_sect_cd;
		$json_list[$i]['office_name'] = $result->rntl_sect_name;
		$i++;
		}
		echo json_encode($json_list);
	} else {
		return true;
	}
});
?>
