<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 着用者照会検索
 */
$app->post('/wearer/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();

	//---契約リソースマスター 0000000000フラグ確認処理---//
	$all_list = array();
	//ログインid
	$login_id_session = $auth['corporate_id'];
	//アカウントno
	$accnt_no = $auth['accnt_no'];
	//画面で選択された契約no
	$agreement_no = $cond['agreement_no'];
	//前処理 契約リソースマスタ参照 拠点ゼロ埋め確認
	$arg_str = "";
	$arg_str .= "SELECT ";
	$arg_str .= " * ";
	$arg_str .= " FROM ";
	$arg_str .= "m_contract_resource";
	$arg_str .= " WHERE ";
	$arg_str .= "corporate_id = '$login_id_session'";
	$arg_str .= " AND rntl_cont_no = '$agreement_no'";
	$arg_str .= " AND accnt_no = '$accnt_no'";
	$m_contract_resource = new MContractResource();
	$results = new Resultset(null, $m_contract_resource, $m_contract_resource->getReadConnection()->query($arg_str));
	$result_obj = (array)$results;
	$results_cnt = $result_obj["\0*\0_count"];
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
					$all_list[] = $result->rntl_sect_cd;
			}
	}
	if (in_array("0000000000", $all_list)) {
			$rntl_sect_cd_zero_flg = 1;
	}else{
			$rntl_sect_cd_zero_flg = 0;
	}

	//---検索条件---//
	//企業ID
	array_push($query_list,"m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	if(!empty($cond['agreement_no'])){
		array_push($query_list,"m_wearer_std.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//社員番号
	if(!empty($cond['member_no'])){
		array_push($query_list,"m_wearer_std.cster_emply_cd LIKE '".$cond['member_no']."%'");
	}
	//着用者名
	// ※前方一致/部分一致
	if ($cond['wearer_name_src1']) {
		if(!empty($cond['member_name'])){
			array_push($query_list,"m_wearer_std.werer_name LIKE '".$cond['member_name']."%'");
		}
	} else {
		if(!empty($cond['member_name'])){
			array_push($query_list,"m_wearer_std.werer_name LIKE '%".$cond['member_name']."%'");
		}
	}
	//拠点
	if(!empty($cond['section'])){
		array_push($query_list,"m_wearer_std.rntl_sect_cd = '".$cond['section']."'");
	}
	//貸与パターン
	if(!empty($cond['job_type'])){
		array_push($query_list,"m_wearer_std.job_type_cd = '".$cond['job_type']."'");
	}
	//商品
	if(!empty($cond['input_item'])){
		array_push($query_list,"t_order.item_cd = '".$cond['input_item']."'");
	}
	//色
	if(!empty($cond['item_color'])){
		array_push($query_list,"t_order.color_cd = '".$cond['item_color']."'");
	}
	//サイズ
	if(!empty($cond['item_size'])){
		array_push($query_list,"t_order.size_cd = '".$cond['item_size']."'");
	}
	//個体管理番号
	if(!empty($cond['individual_number'])){
		array_push($query_list,"t_delivery_goods_state_details.individual_ctrl_no LIKE '".$cond['individual_number']."%'");
	}

    //ゼロ埋めがない場合、ログインアカウントの条件追加
    if($rntl_sect_cd_zero_flg == 0){
        array_push($query_list,"m_contract_resource.accnt_no = '$accnt_no'");
    }

	$status_kbn_list = array();

	//着用者区分
	$wearer_kbn = array();
	if($cond['wearer_kbn0']){
		array_push($wearer_kbn,'1');
	}
	if($cond['wearer_kbn1']){
		array_push($wearer_kbn,'2');
	}
	if($cond['wearer_kbn2']){
		array_push($wearer_kbn,'4');
	}
	if($cond['wearer_kbn3']){
		array_push($wearer_kbn,'8');
	}
	if($cond['wearer_kbn4']){
		array_push($wearer_kbn,'3');
	}
	if(!empty($wearer_kbn)){
		$wearer_kbn_str = implode("','",$wearer_kbn);
		$wearer_kbn_query = "m_wearer_std.werer_sts_kbn IN ('".$wearer_kbn_str."')";
		array_push($status_kbn_list,$wearer_kbn_query);
	}

	if (!empty($status_kbn_list)) {
		$status_kbn_map = implode(' OR ', $status_kbn_list);
		array_push($query_list,"(".$status_kbn_map.")");
	}

	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';

	//ソート設定
	if(!empty($page['sort_key'])){
		$sort_key = $page['sort_key'];
		$order = $page['order'];

		if($sort_key == 'cster_emply_cd'){
			$q_sort_key = 'as_cster_emply_cd';
		}
		if($sort_key == 'werer_name'){
			$q_sort_key = 'as_werer_name';
		}
		if($sort_key == 'job_type_cd'){
			$q_sort_key = 'as_job_type_name';
		}
		if($sort_key == 'rntl_sect_name'){
			$q_sort_key = 'as_rntl_sect_name';
		}
	} else {
		// デフォルトソート
		$q_sort_key = "as_cster_emply_cd";
		$order = 'asc';
	}

	//---SQLクエリー実行---//
	$arg_str = "SELECT ";
	$arg_str .= " * ";
	$arg_str .= " FROM ";
	$arg_str .= "(SELECT distinct on (m_wearer_std.werer_cd) ";
	$arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
	$arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
	$arg_str .= "m_wearer_std.werer_name as as_werer_name,";
	$arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
	$arg_str .= "m_job_type.job_type_name as as_job_type_name";
	$arg_str .= " FROM m_wearer_std LEFT JOIN";
	$arg_str .= " ((t_order";
    if($rntl_sect_cd_zero_flg == 1){
        $arg_str .= " INNER JOIN m_section";
        $arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
    }elseif($rntl_sect_cd_zero_flg == 0){
        $arg_str .= " INNER JOIN (m_section INNER JOIN m_contract_resource";
        $arg_str .= " ON m_section.corporate_id = m_contract_resource.corporate_id";
        $arg_str .= " AND m_section.rntl_cont_no = m_contract_resource.rntl_cont_no";
        $arg_str .= " AND m_section.rntl_sect_cd = m_contract_resource.rntl_sect_cd";
        $arg_str .= " ) ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
    }

	$arg_str .= " INNER JOIN m_job_type ON t_order.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey)";
	$arg_str .= " LEFT JOIN (t_order_state LEFT JOIN (t_delivery_goods_state LEFT JOIN t_delivery_goods_state_details ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no)";
	$arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";
	$arg_str .= " ON t_order.t_order_comb_hkey = t_order_state.t_order_comb_hkey)";
	$arg_str .= " ON m_wearer_std.werer_cd = t_order.werer_cd";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= ") as distinct_table";
	if (!empty($q_sort_key)) {
		$arg_str .= " ORDER BY ";
		$arg_str .= $q_sort_key." ".$order;
	}

	$m_wearer_std = new MWearerStd();
	$results = new Resultset(null, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
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
			// 選択契約No
			$list['agreement_no'] = $cond['agreement_no'];

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
				$list['cster_emply_cd'] = "-";
			}
			// 着用者名
			if (!empty($result->as_werer_name)) {
				$list['werer_name'] = $result->as_werer_name;
			} else {
				$list['werer_name'] = "-";
			}
			// 拠点
			if (!empty($result->as_rntl_sect_name)) {
				$list['rntl_sect_name'] = $result->as_rntl_sect_name;
			} else {
				$list['rntl_sect_name'] = "-";
			}
			// 貸与パターン
			if (!empty($result->as_job_type_name)) {
				$list['job_type_name'] = $result->as_job_type_name;
			} else {
				$list['job_type_name'] = "-";
			}

			array_push($all_list,$list);
		}
	}

/*
	// 個体管理番号表示/非表示フラグ設定
	if ($auth["individual_flg"] == 1) {
		$individual_flg = true;
	} else {
		$individual_flg = false;
	}
	$query_list = array();
	array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
	array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");
	$query = implode(' AND ', $query_list);
	$m_contract = MContract::query()
		->where($query)
		->columns('*')
		->execute();
	$m_contract_obj = (array)$m_contract;
	$cnt = $m_contract_obj["\0*\0_count"];
	$individual_flg = "";
	if (!empty($cnt)) {
		foreach ($m_contract as $m_contract_map) {
			$individual_flg = $m_contract_map->individual_flg;
		}
		if ($individual_flg == 1) {
			$individual_flg = true;
		} else {
			$individual_flg = false;
		}
	}
*/

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $results_cnt;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;
//	$json_list['individual_flag'] = $individual_flg;
	echo json_encode($json_list);
});



/**
 * 着用者詳細検索
 */
$app->post('/wearer/detail', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");
	//ChromePhp::log($auth);

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
	$arg_str .= "m_wearer_std.werer_sts_kbn as as_werer_sts_kbn,";
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
			// 着用者状況区分
			$list['werer_sts_kbn'] = $result->as_werer_sts_kbn;
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

			//---着用者状況区分名称---//
			$query_list = array();
			array_push($query_list, "cls_cd = '009'");
			array_push($query_list, "gen_cd = '".$list['werer_sts_kbn']."'");
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
	$json_list['taiyo_list'] = null;
	// 現在着用中情報抽出
	$query_list = array();
	array_push($query_list,"m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
	array_push($query_list,"m_wearer_std.rntl_cont_no = '".$cond['agreement_no']."'");
	array_push($query_list,"m_wearer_std.werer_cd = '".$cond['wearer_cd']."'");
	$query = implode(' AND ', $query_list);
	$arg_str = "";
	$arg_str .= "SELECT ";
	$arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
	$arg_str .= "m_wearer_std.rntl_cont_no as as_rntl_cont_no,";
	$arg_str .= "m_wearer_item.item_cd as as_item_cd,";
	$arg_str .= "m_wearer_item.color_cd as as_color_cd,";
	$arg_str .= "m_wearer_item.size_cd as as_size_cd,";
	$arg_str .= "m_wearer_item.size_two_cd as as_size_two_cd,";
	$arg_str .= "m_input_item.input_item_name as as_input_item_name";
	$arg_str .= " FROM ";
	$arg_str .= "m_wearer_std INNER JOIN";
	$arg_str .= " (m_wearer_item INNER JOIN m_input_item";
	$arg_str .= " ON m_wearer_item.corporate_id = m_input_item.corporate_id";
	$arg_str .= " AND m_wearer_item.rntl_cont_no = m_input_item.rntl_cont_no";
	$arg_str .= " AND m_wearer_item.job_type_cd = m_input_item.job_type_cd";
	$arg_str .= " AND m_wearer_item.job_type_item_cd = m_input_item.job_type_item_cd";
	$arg_str .= " AND m_wearer_item.item_cd = m_input_item.item_cd";
	$arg_str .= " AND m_wearer_item.color_cd = m_input_item.color_cd)";
	$arg_str .= " ON m_wearer_std.corporate_id = m_wearer_item.corporate_id";
	$arg_str .= " AND m_wearer_std.rntl_cont_no = m_wearer_item.rntl_cont_no";
	$arg_str .= " AND m_wearer_std.werer_cd = m_wearer_item.werer_cd";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= " ORDER BY as_item_cd, as_color_cd, as_size_cd ASC";
	$m_wearer_std = new MWearerStd();
	$results = new Resultset(null, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
	$result_obj = (array)$results;
	$results_cnt = $result_obj["\0*\0_count"];

	$wearer_item_list = array();
	$list = array();

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
			if (!empty($result->as_size_cd)) {
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
			// 投入商品名(商品名)
			if (!empty($result->as_input_item_name)) {
				$list['item_name'] = $result->as_input_item_name;
			} else {
				$list['item_name'] = "-";
			}
			// 商品-色(サイズ-サイズ2)変換
			$list['shin_item_code'] = $list['item_cd']."-".$list['color_cd']."(".$list['size_cd']."-".$list['size_two_cd'].")";
			// 個体管理番号(バーコード)
			$list['individual_ctrl_no'] = "-";
			$query_list = array();
			array_push($query_list,"t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
			array_push($query_list,"t_delivery_goods_state_details.rntl_cont_no = '".$result->as_rntl_cont_no."'");
			array_push($query_list,"t_delivery_goods_state_details.werer_cd = '".$result->as_werer_cd."'");
			array_push($query_list,"t_delivery_goods_state_details.item_cd = '".$result->as_item_cd."'");
			array_push($query_list,"t_delivery_goods_state_details.color_cd = '".$result->as_color_cd."'");
			array_push($query_list,"t_delivery_goods_state_details.size_cd = '".$result->as_size_cd."'");
			$query = implode(' AND ', $query_list);
			$arg_str = "";
			$arg_str .= "SELECT ";
			$arg_str .= "t_delivery_goods_state_details.individual_ctrl_no as as_individual_ctrl_no";
			$arg_str .= " FROM ";
			$arg_str .= "t_delivery_goods_state_details";
			$arg_str .= " WHERE ";
			$arg_str .= $query;
			$t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
			$delivery_goods_state_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
			$result_obj = (array)$delivery_goods_state_results;
			$results_cnt = $result_obj["\0*\0_count"];
			if(!empty($results_cnt)){
				$paginator_model = new PaginatorModel(
					array(
						"data"  => $delivery_goods_state_results,
						"limit" => 1,
						"page" => 1
					)
				);
				$paginator = $paginator_model->getPaginate();
				$results = $paginator->items;
				foreach($delivery_goods_state_results as $delivery_goods_state_result) {
					$list['individual_ctrl_no'] = $delivery_goods_state_result->as_individual_ctrl_no;
				}
			}

			array_push($wearer_item_list, $list);
		}
	}

	// 未返却情報抽出
	$query_list = array();
	array_push($query_list,"t_returned_plan_info.corporate_id = '".$auth['corporate_id']."'");
	array_push($query_list,"t_returned_plan_info.rntl_cont_no = '".$cond['agreement_no']."'");
	array_push($query_list,"t_returned_plan_info.werer_cd = '".$cond['wearer_cd']."'");
	array_push($query_list,"t_returned_plan_info.return_status = '1'");
	$query = implode(' AND ', $query_list);
	$arg_str = "";
	$arg_str .= "SELECT ";
	$arg_str .= " * ";
	$arg_str .= " FROM ";
	$arg_str .= "(SELECT distinct on (t_returned_plan_info.order_req_no,t_returned_plan_info.order_req_line_no) ";
	$arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
	$arg_str .= "m_wearer_std.rntl_cont_no as as_rntl_cont_no,";
	$arg_str .= "t_returned_plan_info.order_req_no as as_order_req_no,";
	$arg_str .= "t_returned_plan_info.order_req_line_no as as_order_req_line_no,";
	$arg_str .= "t_returned_plan_info.individual_ctrl_no as as_individual_ctrl_no,";
	$arg_str .= "t_returned_plan_info.item_cd as as_item_cd,";
	$arg_str .= "t_returned_plan_info.color_cd as as_color_cd,";
	$arg_str .= "t_returned_plan_info.size_cd as as_size_cd,";
	$arg_str .= "m_input_item.size_two_cd as as_size_two_cd,";
	$arg_str .= "m_input_item.input_item_name as as_input_item_name";
	$arg_str .= " FROM ";
	$arg_str .= "m_wearer_std INNER JOIN";
	$arg_str .= " (t_returned_plan_info INNER JOIN m_input_item";
	$arg_str .= " ON t_returned_plan_info.corporate_id = m_input_item.corporate_id";
	$arg_str .= " AND t_returned_plan_info.rntl_cont_no = m_input_item.rntl_cont_no";
	$arg_str .= " AND t_returned_plan_info.job_type_cd = m_input_item.job_type_cd";
	$arg_str .= " AND t_returned_plan_info.item_cd = m_input_item.item_cd";
	$arg_str .= " AND t_returned_plan_info.color_cd = m_input_item.color_cd)";
	$arg_str .= " ON m_wearer_std.corporate_id = t_returned_plan_info.corporate_id";
	$arg_str .= " AND m_wearer_std.rntl_cont_no = t_returned_plan_info.rntl_cont_no";
	$arg_str .= " AND m_wearer_std.werer_cd = t_returned_plan_info.werer_cd";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= ") as distinct_table";
	$arg_str .= " ORDER BY as_order_req_no, as_order_req_line_no ASC";
	$t_returned_plan_info = new TReturnedPlanInfo();
	$results = new Resultset(null, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
	$result_obj = (array)$results;
	$results_cnt = $result_obj["\0*\0_count"];

	$list = array();
	$return_list = array();

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
			if (!empty($result->as_size_cd)) {
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
			// 投入商品名(商品名)
			if (!empty($result->as_input_item_name)) {
				$list['item_name'] = $result->as_input_item_name;
			} else {
				$list['item_name'] = "-";
			}
			// 商品-色(サイズ-サイズ2)変換
			$list['shin_item_code'] = $list['item_cd']."-".$list['color_cd']."(".$list['size_cd']."-".$list['size_two_cd'].")";
			// 個体管理番号(バーコード)
			if (!empty($result->as_individual_ctrl_no)) {
				$list['individual_ctrl_no'] = $result->as_individual_ctrl_no;
			} else {
				$list['individual_ctrl_no'] = "";
			}

			array_push($return_list, $list);
		}
	}

	// 未返却リストがある場合、既存の貸与リストにマージ
	if (!empty($return_list)) {
		$wearer_item_list = array_merge($wearer_item_list, $return_list);
	}

	// 個体管理番号表示制御フラグ
	if ($auth["individual_flg"] == "1") {
		$json_list["individual_flg"] = true;
	} else {
		$json_list["individual_flg"] = false;
	}

	$json_list['taiyo_list'] = $wearer_item_list;
	echo json_encode($json_list);
});
