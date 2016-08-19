<?php
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

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
