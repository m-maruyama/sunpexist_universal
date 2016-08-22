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
 * アカウントセッション取得
 */
$app->post('/account_session', function ()use($app) {
	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$json_list = $auth;
	echo json_encode($json_list);
});


/**
 * 検索項目：契約No
 */
$app->post('/agreement_no', function ()use($app) {
	$params = json_decode(file_get_contents("php://input"), true);

	$query_list = array();
	$list = array();
	$all_list = array();
	$json_list = array();

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	//--- 検索条件 ---//
	// 契約マスタ. 企業ID
	array_push($query_list,"MContract.corporate_id = '".$auth['corporate_id']."'");
	// 契約マスタ. レンタル契約フラグ
	array_push($query_list,"MContract.rntl_cont_flg = '1'");
	// 契約リソースマスタ. 企業ID
	array_push($query_list,"MContractResource.corporate_id = '".$auth['corporate_id']."'");
	// アカウントマスタ.企業ID
	array_push($query_list,"MAccount.corporate_id = '".$auth['corporate_id']."'");
	// アカウントマスタ. ユーザーID
	array_push($query_list,"MAccount.user_id = '".$auth['user_id']."'");

	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);

	//--- クエリー実行・取得 ---//
	$results = MContract::query()
		->where($query)
		->columns(array('MContract.*','MContractResource.*','MAccount.*'))
		->leftJoin('MContractResource','MContract.corporate_id = MContractResource.corporate_id')
		->join('MAccount','MAccount.accnt_no = MContractResource.accnt_no')
//		->orderBy('cast(MContract.rntl_cont_no asc as integer)')
		->execute();

	foreach ($results as $result) {
		$list['rntl_cont_no'] = $result->mContract->rntl_cont_no;
		$list['rntl_cont_name'] = $result->mContract->rntl_cont_name;
		array_push($all_list,$list);
	}

	$json_list['agreement_no_list'] = $all_list;
	echo json_encode($json_list);
});


/**
 * 検索項目：拠点
 */
$app->post('/section', function ()use($app) {
	$params = json_decode(file_get_contents("php://input"), true);

	$query_list = array();
	$list = array();
	$all_list = array();
	$json_list = array();

	// アカウントセッション取得
	$auth = $app->session->get("auth");
/*
	// 契約上の部門コードを参照
	// 契約マスタ. 企業ID
	array_push($query_list,"MContract.corporate_id = '".$auth['corporate_id']."'");
	// 契約マスタ. レンタル契約フラグ
	array_push($query_list,"MContract.rntl_cont_flg = '1'");
	// 契約リソースマスタ. 企業ID
	array_push($query_list,"MContractResource.corporate_id = '".$auth['corporate_id']."'");
	// アカウントマスタ.企業ID
	array_push($query_list,"MAccount.corporate_id = '".$auth['corporate_id']."'");
	// アカウントマスタ. ユーザーID
	array_push($query_list,"MAccount.user_id = '".$auth['user_id']."'");

	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);

	$results = MContract::query()
		->where($query)
		->columns(array('MContract.*','MContractResource.*','MAccount.*'))
		->leftJoin('MContractResource','MContract.corporate_id = MContractResource.corporate_id')
		->join('MAccount','MAccount.accnt_no = MContractResource.accnt_no')
//		->orderBy('cast(MContract.rntl_cont_no asc as integer)')
		->execute();

	// レンタル部門コードのオール「0」チェック
	$all_zero_flag = 0;
	foreach ($results as $result) {
		if (preg_match("/^[0]+$/", $result->mContractResource->rntl_sect_cd)) {
			$all_zero_flag = 1;
		}
	}
	if ($all_zero_flag == 0) {
		// レンタル部門コードにオール「0」のコードが含まれていない場合

	}
*/

	//--- 検索条件 ---//
	// 部門マスタ. 企業ID
	array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
	// 部門マスタ. レンタル契約No
	array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");

	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);

	//--- クエリー実行・取得 ---//
	$results = MSection::query()
		->where($query)
		->columns('*')
		->execute();

	// デフォルト「全て」を設定
	$list['rntl_sect_cd'] = null;
	$list['rntl_sect_name'] = '全て';
	array_push($all_list,$list);

	foreach ($results as $result) {
		$list['rntl_sect_cd'] = $result->rntl_sect_cd;
		$list['rntl_sect_name'] = $result->rntl_sect_name;
		array_push($all_list,$list);
	}

	$json_list['section_list'] = $all_list;
	echo json_encode($json_list);

/* 前項目：拠点-テキスト形式
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
*/
});


/**
　* 検索項目：貸与パターン
　*/
$app->post('/job_type', function ()use($app) {
	$params = json_decode(file_get_contents("php://input"), true);

	$query_list = array();
	$list = array();
	$all_list = array();
	$json_list = array();

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	//--- 検索条件 ---//
	// 職種マスタ. 企業ID
	array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
	// 職種マスタ. レンタル契約No
	array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");

	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);

	//--- クエリー実行・取得 ---//
	$results = MJobType::query()
		->where($query)
		->columns('*')
		->execute();

	// デフォルト「全て」を設定
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
　* 検索項目：商品
　*/
$app->post('/input_item', function ()use($app) {
	$params = json_decode(file_get_contents("php://input"), true);

	$query_list = array();
	$list = array();
	$all_list = array();
	$json_list = array();

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	//--- 検索条件 ---//
	// 投入商品マスタ. 企業ID
	array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
	// 投入商品マスタ. レンタル契約No
	array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");

	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);

	//--- クエリー実行・取得 ---//
	$results = MInputItem::query()
		->where($query)
		->columns('*')
		->execute();

	// デフォルト「全て」を設定
	$list['item_cd'] = null;
	$list['input_item_name'] = '全て';
	array_push($all_list,$list);

	foreach ($results as $result) {
		$list['item_cd'] = $result->item_cd;
		$list['input_item_name'] = $result->input_item_name;
		array_push($all_list,$list);
	}

	$json_list['input_item_list'] = $all_list;
	echo json_encode($json_list);
});


/**
　* 検索項目：色
　*/
$app->post('/item_color', function ()use($app) {
	$params = json_decode(file_get_contents("php://input"), true);

	$query_list = array();
	$list = array();
	$all_list = array();
	$json_list = array();

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	//--- 検索条件 ---//
	// 投入商品マスタ. 企業ID
	array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
	// 投入商品マスタ. レンタル契約No
	array_push($query_list, "rntl_cont_no = '".$auth['rntl_cont_no']."'");

	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);

	//--- クエリー実行・取得 ---//
	$results = MInputItem::query()
		->where($query)
		->columns('*')
//		->group('color_cd')
		->execute();

	// デフォルト「全て」を設定
	$list['color_cd_id'] = null;
	$list['color_cd_name'] = '全て';
	array_push($all_list,$list);

	foreach ($results as $result) {
		$list['color_cd_id'] = $result->color_cd;
		$list['color_cd_name'] = $result->color_cd;
		array_push($all_list,$list);
	}

	$json_list['item_color_list'] = $all_list;
	echo json_encode($json_list);
});


/**
 * 検索項目：在庫専用貸与パターン
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

?>
