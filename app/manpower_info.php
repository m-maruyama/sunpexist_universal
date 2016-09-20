<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 人員明細照会検索
 */
$app->post('/manpower_info/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();

	//---検索条件---//
	//企業ID
	array_push($query_list,"t_staff_detail_head.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	if(!empty($cond['agreement_no'])){
		array_push($query_list,"t_staff_detail_head.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//対象年月
	if(!empty($cond['target_ym'])){
		array_push($query_list,"TO_DATE(t_staff_detail_head.yyyymm,'YYYY/MM') = TO_DATE('".$cond['target_ym']."','YYYY/MM')");
	}
	//拠点
	if(!empty($cond['section'])){
		array_push($query_list,"t_staff_detail_head.rntl_sect_cd = '".$cond['section']."'");
	}

	$query = implode(' AND ', $query_list);

	$arg_str = "SELECT ";
	$arg_str .= "t_staff_detail_head.rntl_sect_cd as as_rntl_sect_cd,";
	$arg_str .= "t_staff_detail_head.yyyymm as as_yyyymm,";
	$arg_str .= "t_staff_detail_head.staff_total as as_staff_total,";
	$arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name";
	$arg_str .= " FROM t_staff_detail_head";
	$arg_str .= " INNER JOIN m_section ON t_staff_detail_head.rntl_sect_cd = m_section.rntl_sect_cd";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= " ORDER BY as_yyyymm DESC";

	$t_staff_detail_head = new TStaffDetailHead();
	$results = new Resultset(null, $t_staff_detail_head, $t_staff_detail_head->getReadConnection()->query($arg_str));
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

		// 表No設定
		$list_no = 1;
		foreach($results as $result) {
			// 表No
			$list['list_no'] = $list_no++;
			// 選択契約No
			$list['agreement_no'] = $cond['agreement_no'];

			// 拠点コード
			if (!empty($result->as_rntl_sect_cd)) {
				$list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
			} else {
				$list['rntl_sect_cd'] = "-";
			}
			// 年月
			if (!empty($result->as_yyyymm)) {
				$list['yyyymm'] = $result->as_yyyymm;
			} else {
				$list['yyyymm'] = "";
			}
			// 拠点名
			if (!empty($result->as_rntl_sect_name)) {
				$list['rntl_sect_name'] = $result->as_rntl_sect_name;
			} else {
				$list['rntl_sect_name'] = "-";
			}
			// 人員数
			if (isset($result->as_staff_total)) {
				$list['staff_total'] = $result->as_staff_total;
				if (!empty($list['staff_total'])) {
					$list['detail_flg'] = true;
				} else {
					$list['detail_flg'] = false;
				}
			} else {
				$list['staff_total'] = "-";
				$list['detail_flg'] = false;
			}

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



/**
 * 人員明細照会詳細モーダル
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



/**
 * 人員明細照会詳細ダウンロード
 */
$app->post('/manpower_info/download', function ()use($app){

//	$params = json_decode(file_get_contents("php://input"), true);
	$params = json_decode($_POST['data'], true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	//--フロント側パラメータ取得--//
	$cond = $params['cond'];
//	ChromePhp::log($cond);

	// json返却値
	$json_list = array();

	//---人員明細詳細検索処理---//
	//--小見出し項目--//
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

	//--詳細検索条件--//
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
	}


	//---CSV出力---//
	$csv_datas = array();

	// ヘッダー作成
	$header_1 = array(
		'人員明細詳細 ('.$heading['yyyymm'].' '.$heading['rntl_sect_cd'].' '.$heading['rntl_sect_name'].' '.$heading['staff_total'].'名)'
	);
	array_push($csv_datas, $header_1);

	$header_2 = array();
	if (!empty($label_list)) {
		foreach ($label_list as $label_map) {
			array_push($header_2, $label_map["data"]);
		}
	}
	array_push($csv_datas, $header_2);

	// ボディ作成
	if (!empty($detail_list)) {
		foreach ($detail_list as $detail_map) {
			$csv_body_list = array();
			foreach ($detail_map as $details) {
				array_push($csv_body_list, '="'.$details["data"].'"');
			}
			// CSVレコード配列にマージ
			array_push($csv_datas, $csv_body_list);
		}
	}

	// CSVデータ書き込み
	$file_name = "manpower_detail_".date("YmdHis", time()).".csv";
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=".$file_name);

	$fp = fopen('php://output','w');
	foreach ($csv_datas as $csv_data) {
		mb_convert_variables("SJIS-win", "UTF-8", $csv_data);
		fputcsv($fp, $csv_data);
	}

	fclose($fp);
});
