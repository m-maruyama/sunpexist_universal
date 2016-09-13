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
	array_push($query_list,"t_staff_detail_body.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	if(!empty($cond['agreement_no'])){
		array_push($query_list,"t_staff_detail_body.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//対象年月
	if(!empty($cond['target_ym'])){
		array_push($query_list,"TO_DATE(t_staff_detail_body.yyyymm,'YYYY/MM') = TO_DATE('".$cond['target_ym']."','YYYY/MM')");
	}
	//拠点
	if(!empty($cond['section'])){
		array_push($query_list,"t_staff_detail_body.rntl_sect_cd = '".$cond['section']."'");
	}

	$query = implode(' AND ', $query_list);

	//---SQLクエリー実行---//
	$arg_str = "SELECT ";
	$arg_str .= " * ";
	$arg_str .= " FROM ";
	$arg_str .= "(SELECT distinct on (t_staff_detail_body.rntl_sect_cd) ";
	$arg_str .= "t_staff_detail_body.rntl_sect_cd as as_rntl_sect_cd,";
	$arg_str .= "t_staff_detail_body.yyyymm as as_yyyymm,";
	$arg_str .= "t_staff_detail_head.staff_total as as_staff_total,";
	$arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name";
	$arg_str .= " FROM t_staff_detail_body";
	$arg_str .= "  INNER JOIN t_staff_detail_head ON t_staff_detail_body.yyyymm = t_staff_detail_head.yyyymm";
	$arg_str .= " ON t_staff_detail_body.yyyymm = t_staff_detail_head.yyyymm";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= ") as distinct_table";

	$t_staff_detail_body = new TStaffDetailBody();
	$results = new Resultset(null, $t_staff_detail_body, $t_staff_detail_body->getReadConnection()->query($arg_str));
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
			} else {
				$list['staff_total'] = "-";
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
 * 着用者詳細検索
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

	//---着用者個人情報---//
	$query_list = array();
	//企業ID
	array_push($query_list,"m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	if(!empty($cond['agreement_no'])){
		array_push($query_list,"m_wearer_std.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//着用者コード
	if(!empty($cond['wearer_cd'])){
		array_push($query_list,"m_wearer_std.werer_cd = '".$cond['wearer_cd']."'");
	}
	//社員番号
	if(!empty($cond['cster_emply_cd']) && $cond['cster_emply_cd'] !== "-"){
		array_push($query_list,"m_wearer_std.cster_emply_cd = '".$cond['cster_emply_cd']."'");
	}

	$query = implode(' AND ', $query_list);

	$arg_str = "";
	$arg_str = "SELECT ";
	$arg_str .= " * ";
	$arg_str .= " FROM ";
	$arg_str .= "(SELECT distinct on (m_wearer_std.werer_cd) ";
	$arg_str .= "m_wearer_std.rntl_cont_no as as_rntl_cont_no,";
	$arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
	$arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
	$arg_str .= "m_wearer_std.werer_name as as_werer_name,";
	$arg_str .= "m_wearer_std.werer_name_kana as as_werer_name_kana,";
	$arg_str .= "m_wearer_std.sex_kbn as as_sex_kbn,";
	$arg_str .= "m_wearer_std.resfl_ymd as as_resfl_ymd,";
	$arg_str .= "m_wearer_std.order_sts_kbn as as_order_sts_kbn,";
	$arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
	$arg_str .= "m_job_type.job_type_name as as_job_type_name,";
	$arg_str .= "m_shipment_to.ship_to_cd as as_ship_to_cd,";
	$arg_str .= "m_shipment_to.ship_to_brnch_cd as as_ship_to_brnch_cd,";
	$arg_str .= "m_shipment_to.cust_to_brnch_name1 as as_cust_to_brnch_name1,";
	$arg_str .= "m_shipment_to.cust_to_brnch_name2 as as_cust_to_brnch_name2,";
	$arg_str .= "m_shipment_to.zip_no as as_zip_no,";
	$arg_str .= "m_shipment_to.address1 as as_address1,";
	$arg_str .= "m_shipment_to.address2 as as_address2,";
	$arg_str .= "m_shipment_to.address3 as as_address3,";
	$arg_str .= "m_shipment_to.address4 as as_address4";
	$arg_str .= " FROM m_wearer_std";
	$arg_str .= " INNER JOIN m_section ON m_wearer_std.m_section_comb_hkey = m_section.m_section_comb_hkey";
	$arg_str .= " INNER JOIN m_job_type ON m_wearer_std.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
	$arg_str .= " INNER JOIN m_shipment_to ON m_wearer_std.ship_to_cd = m_shipment_to.ship_to_cd";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= ") as distinct_table";

	$m_wearer_std = new MWearerStd();
	$results = new Resultset(null, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
	$result_obj = (array)$results;
	$results_cnt = $result_obj["\0*\0_count"];
	//ChromePhp::log($m_wearer_std->getReadConnection()->query($arg_str));

	$list = array();
	$all_list = array();

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
			// レンタル契約No
			if (!empty($result->as_rntl_cont_no)) {
				$list['rntl_cont_no'] = $result->as_rntl_cont_no;
			} else {
				$list['rntl_cont_no'] = "";
			}
			// 着用者コード
			if (!empty($result->as_werer_cd)) {
				$list['werer_cd'] = $result->as_werer_cd;
			} else {
				$list['werer_cd'] = "";
			}
			// 社員番号
			if (!empty($result->as_cster_emply_cd)) {
				$list['cster_emply_cd'] = $result->as_cster_emply_cd;
			} else {
				$list['cster_emply_cd'] = "";
			}
			// 着用者名
			if (!empty($result->as_werer_name)) {
				$list['werer_name'] = $result->as_werer_name;
			} else {
				$list['werer_name'] = "";
			}
			// 着用者名かな
			if (!empty($result->as_werer_name_kana)) {
				$list['werer_name_kana'] = $result->as_werer_name_kana;
			} else {
				$list['werer_name_kana'] = "";
			}
			// 性別区分
			$list['sex_kbn'] = $result->as_sex_kbn;
			// 異動日
			$list['resfl_ymd'] = $result->as_resfl_ymd;
			// 発注状況区分
			$list['order_sts_kbn'] = $result->as_order_sts_kbn;
			// 拠点
			if (!empty($result->as_rntl_sect_name)) {
				$list['rntl_sect_name'] = $result->as_rntl_sect_name;
			} else {
				$list['rntl_sect_name'] = "";
			}
			// 貸与パターン
			if (!empty($result->as_job_type_name)) {
				$list['job_type_name'] = $result->as_job_type_name;
			} else {
				$list['job_type_name'] = "";
			}
			// 出荷先コード
			if (!empty($result->as_ship_to_cd)) {
				$list['ship_to_cd'] = $result->as_ship_to_cd;
			} else {
				$list['ship_to_cd'] = "";
			}
			// 出荷先支店コード
			if (!empty($result->as_ship_to_brnch_cd)) {
				$list['ship_to_brnch_cd'] = $result->as_ship_to_brnch_cd;
			} else {
				$list['ship_to_brnch_cd'] = "";
			}
			// 取引先支店名1
			if (!empty($result->as_cust_to_brnch_name1)) {
				$list['cust_to_brnch_name1'] = $result->as_cust_to_brnch_name1;
			} else {
				$list['cust_to_brnch_name1'] = "";
			}
			// 取引先支店名2
			if (!empty($result->as_cust_to_brnch_name2)) {
				$list['cust_to_brnch_name2'] = $result->as_cust_to_brnch_name2;
			} else {
				$list['cust_to_brnch_name2'] = "";
			}
			// 郵便番号
			if (!empty($result->as_zip_no)) {
				$list['zip_no'] = $result->as_zip_no;
			} else {
				$list['zip_no'] = "";
			}
			// 住所1
			$list['address1'] = $result->as_address1;
			// 住所2
			$list['address2'] = $result->as_address2;
			// 住所3
			$list['address3'] = $result->as_address3;
			// 住所4
			$list['address4'] = $result->as_address4;

			//---日付設定---//
			// 異動日
			if(!empty($list['resfl_ymd'])){
				$list['resfl_ymd'] = date('Y/m/d',strtotime($list['resfl_ymd']));
			}else{
				$list['resfl_ymd'] = '';
			}

			//---性別区分名称---//
			$query_list = array();
			array_push($query_list, "cls_cd = '004'");
			array_push($query_list, "gen_cd = '".$list['sex_kbn']."'");
			$query = implode(' AND ', $query_list);

			$arg_str = "";
			$arg_str = 'SELECT ';
			$arg_str .= ' * ';
			$arg_str .= ' FROM ';
			$arg_str .= 'm_gencode ';
			$arg_str .= ' WHERE ';
			$arg_str .= $query;

			$m_gencode = new MGencode();
			$results = new Resultset(null, $m_gencode, $m_gencode->getReadConnection()->query($arg_str));
			$results_array = (array) $results;
			$results_cnt = $results_array["\0*\0_count"];
			if (!empty($results_cnt)) {
				foreach ($results as $result) {
					$list['sex_kbn_name'] = $result->gen_name;
				}
			} else {
				$list['sex_kbn_name'] = "";
			}

			//---発注区分名称---//
			$query_list = array();
			array_push($query_list, "cls_cd = '001'");
			array_push($query_list, "gen_cd = '".$list['order_sts_kbn']."'");
			$query = implode(' AND ', $query_list);

			$arg_str = "";
	    $arg_str = 'SELECT ';
	    $arg_str .= ' * ';
	    $arg_str .= ' FROM ';
	    $arg_str .= 'm_gencode ';
	    $arg_str .= ' WHERE ';
	    $arg_str .= $query;

	    $m_gencode = new MGencode();
	    $results = new Resultset(null, $m_gencode, $m_gencode->getReadConnection()->query($arg_str));
	    $results_array = (array) $results;
	    $results_cnt = $results_array["\0*\0_count"];
			if (!empty($results_cnt)) {
				foreach ($results as $result) {
					$list['order_sts_name'] = $result->gen_name;
				}
			} else {
				$list['order_sts_name'] = "";
			}

			// 住所
			$list['wearer_address'] = $list['address1'].$list['address2'].$list['address3'].$list['address4'];

			array_push($all_list,$list);
		}

		$json_list['kozin_list'] = $all_list;
		//ChromePhp::log($json_list['kozin_list']);
	} else {
		$json_list['kozin_list'] = null;
		//ChromePhp::log($json_list['kozin_list']);
	}



	//---着用者貸与情報---//
	$query_list = array();
	//企業ID
	array_push($query_list,"t_returned_plan_info.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	array_push($query_list,"t_returned_plan_info.rntl_cont_no = '".$cond['agreement_no']."'");
	//着用者コード
	array_push($query_list,"t_returned_plan_info.werer_cd = '".$cond['wearer_cd']."'");
/*
	//社員番号
	if(!empty($cond['cster_emply_cd']) && $cond['cster_emply_cd'] !== "-"){
		array_push($query_list,"t_returned_plan_info.cster_emply_cd = '".$cond['cster_emply_cd']."'");
	}
*/
	// 返却ステータス(未返却)
	array_push($query_list,"t_returned_plan_info.return_status = '1'");

	$query = implode(' AND ', $query_list);

	$arg_str = "";
	$arg_str = "SELECT ";
	$arg_str .= " * ";
	$arg_str .= " FROM ";
	$arg_str .= "(SELECT distinct on (t_returned_plan_info.order_req_no,t_returned_plan_info.order_req_line_no) ";
	$arg_str .= "t_returned_plan_info.order_req_no as as_order_req_no,";
	$arg_str .= "t_returned_plan_info.order_req_line_no as as_order_req_line_no,";
	$arg_str .= "t_returned_plan_info.individual_ctrl_no as as_individual_ctrl_no,";
	$arg_str .= "m_item.item_cd as as_item_cd,";
	$arg_str .= "m_item.color_cd as as_color_cd,";
	$arg_str .= "m_item.size_cd as as_size_cd,";
	$arg_str .= "m_item.item_name as as_item_name,";
	$arg_str .= "m_input_item.size_two_cd as as_size_two_cd,";
	$arg_str .= "m_input_item.input_item_name as as_input_item_name";
	$arg_str .= " FROM t_returned_plan_info INNER JOIN";
	$arg_str .= " (m_wearer_std INNER JOIN";
	$arg_str .= " (m_job_type INNER JOIN m_input_item ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey)";
	$arg_str .= " ON m_wearer_std.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey)";
	$arg_str .= " ON t_returned_plan_info.werer_cd = m_wearer_std.werer_cd";
	$arg_str .= " INNER JOIN m_item ON t_returned_plan_info.m_item_comb_hkey = m_item.m_item_comb_hkey";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= ") as distinct_table";
	$arg_str .= " ORDER BY as_order_req_no, as_order_req_line_no ASC";

	$t_returned_plan_info = new TReturnedPlanInfo();
	$results = new Resultset(null, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
	$result_obj = (array)$results;
	$results_cnt = $result_obj["\0*\0_count"];
	//ChromePhp::log($t_returned_plan_info->getReadConnection()->query($arg_str));

	$list = array();
	$all_list = array();

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

		//表No用
		$no_num = 1;
		foreach($results as $result) {
			// 表No
			$list['list_no'] = $no_num++;
			// 商品コード
			if (!empty($result->as_item_cd)) {
				$list['item_cd'] = $result->as_item_cd;
			} else {
				$list['item_cd'] = "";
			}
			// 色コード
			if (!empty($result->as_color_cd)) {
				$list['color_cd'] = $result->as_color_cd;
			} else {
				$list['color_cd'] = "";
			}
			// サイズコード
			if (!empty($result->as_werer_name)) {
				$list['size_cd'] = $result->as_size_cd;
			} else {
				$list['size_cd'] = "";
			}
			// サイズコード2
			if (!empty($result->as_size_two_cd)) {
				$list['size_two_cd'] = $result->as_size_two_cd;
			} else {
				$list['size_two_cd'] = "";
			}
			// 商品名
			if (!empty($result->as_item_name)) {
				$list['item_name'] = $result->as_item_name;
			} else {
				$list['item_name'] = "-";
			}
			// 投入商品名
			if (!empty($result->as_input_item_name)) {
				$list['input_item_name'] = $result->as_input_item_name;
			} else {
				$list['input_item_name'] = "-";
			}
			// 個体管理番号(バーコード)
			if (!empty($result->as_individual_ctrl_no)) {
				$list['individual_ctrl_no'] = $result->as_individual_ctrl_no;
			} else {
				$list['individual_ctrl_no'] = "-";
			}

			// 商品-色(サイズ-サイズ2)変換
			$list['shin_item_code'] = $list['item_cd']."-".$list['color_cd']."(".$list['size_cd']."-".$list['size_two_cd'].")";

			array_push($all_list,$list);
		}
		$json_list['taiyo_list'] = $all_list;
		//ChromePhp::log($json_list['taiyo_list']);

	} else {
		$json_list['taiyo_list'] = null;
//		ChromePhp::log($json_list['taiyo_list']);
	}

	echo json_encode($json_list);
});
