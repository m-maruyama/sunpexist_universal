<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * 在庫照会検索
 */
$app->post('/stock/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['cond'];
	//ChromePhp::LOG($cond);
	$page = $params['page'];
	$query_list = array();

	//企業ID
	array_push($query_list,"t_sdmzk.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	if(!empty($cond['agreement_no'])){
		array_push($query_list,"t_sdmzk.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//貸与パターン
	if(!empty($cond['job_type_zaiko'])){
		array_push($query_list,"substring(t_sdmzk.rent_pattern_data, 3) = '".$cond['job_type_zaiko']."'");
	}
	//商品
	if(!empty($cond['item'])){
		array_push($query_list,"m_item.item_cd = '".$cond['item']."'");
	}
	//色
	if(!empty($cond['item_color'])){
		array_push($query_list,"m_item.color_cd = '".$cond['item_color']."'");
	}
	//サイズ
	if(!empty($cond['item_size'])){
		array_push($query_list,"m_item.size_cd = '".$cond['item_size']."'");
	}

	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';

	//第一ソート設定
	if(!empty($page['sort_key'])){
		$sort_key = $page['sort_key'];
		$order = $page['order'];
		// 商品名
		if($sort_key == 'item_name'){
			$q_sort_key = 'as_item_name,';
		}
		// 在庫状態
		if($sort_key == 'stock_status'){
			$q_sort_key = 'as_zk_status_cd,';
		}
		// 倉庫コード
		if($sort_key == 'zkwhcd'){
			$q_sort_key = 'as_zkwhcd,';
		}
		// ラベル
		if($sort_key == 'label'){
			$q_sort_key = 'as_label,';
		}
		// 返却処理中
		if($sort_key == 'rtn_proc_qty'){
			$q_sort_key = 'as_rtn_proc_qty,';
		}
		// 返却予定
		if($sort_key == 'rtn_plan_qty'){
			$q_sort_key = 'as_rtn_plan_qty,';
		}
		// 貸与中
		if($sort_key == 'in_use_qty'){
			$q_sort_key = 'as_in_use_qty,';
		}
		// その他出荷
		if($sort_key == 'other_ship_qty'){
			$q_sort_key = 'as_other_ship_qty,';
		}
	} else {
		//指定がなければデフォルトソート順
		$q_sort_key = "";
		$order = 'asc';
	}

	$arg_str = "SELECT ";
	$arg_str .= "t_sdmzk.zkwhcd as as_zkwhcd,";
	$arg_str .= "t_sdmzk.zkprcd as as_zkprcd,";
	$arg_str .= "t_sdmzk.zkclor as as_zkclor,";
	$arg_str .= "t_sdmzk.zksize as as_zksize,";
	$arg_str .= "t_sdmzk.label as as_label,";
	$arg_str .= "t_sdmzk.zksize_display_order as as_zksize_display_order,";
	$arg_str .= "t_sdmzk.zk_status_cd as as_zk_status_cd,";
	$arg_str .= "t_sdmzk.total_qty as as_total_qty,";
	$arg_str .= "t_sdmzk.new_qty as as_new_qty,";
	$arg_str .= "t_sdmzk.used_qty as as_used_qty,";
	$arg_str .= "t_sdmzk.rtn_proc_qty as as_rtn_proc_qty,";
	$arg_str .= "t_sdmzk.rtn_plan_qty as as_rtn_plan_qty,";
	$arg_str .= "t_sdmzk.in_use_qty as as_in_use_qty,";
	$arg_str .= "t_sdmzk.other_ship_qty as as_other_ship_qty,";
	$arg_str .= "t_sdmzk.discarded_qty as as_discarded_qty,";
	$arg_str .= "t_sdmzk.rent_pattern_data as as_rent_pattern_data,";
	$arg_str .= "m_item.item_name as as_item_name";
	$arg_str .= " FROM t_sdmzk";
	$arg_str .= " INNER JOIN m_item ON t_sdmzk.m_item_comb_hkey = m_item.m_item_comb_hkey";
	$arg_str .= " INNER JOIN m_rent_pattern_for_sdmzk ON substring(t_sdmzk.rent_pattern_data, 3) = m_rent_pattern_for_sdmzk.rent_pattern_data";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	if (!empty($q_sort_key)) {
		$arg_str .= " ORDER BY ";
		$arg_str .= $q_sort_key."as_rent_pattern_data,as_zkprcd,as_zkclor,as_zksize_display_order,as_zksize ".$order;
	}

	$t_sdmzk = new TSdmzk();
	$results = new Resultset(null, $t_sdmzk, $t_sdmzk->getReadConnection()->query($arg_str));
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

	if(!empty($results_cnt)){
		$paginator = $paginator_model->getPaginate();
		$results = $paginator->items;
		foreach($results as $result){
			// 倉庫コード
			if (!empty($result->as_zkwhcd)) {
				$list['zkwhcd'] = $result->as_zkwhcd;
			} else {
				$list['zkwhcd'] = "-";
			}
			// 商品コード
			$list['zkprcd'] = $result->as_zkprcd;
			// 商品色
			$list['zkclor'] = $result->as_zkclor;
			// サイズコード
			$list['zksize'] = $result->as_zksize;
			// ラベル
			if (!empty($result->as_label)) {
				$list['label'] = $result->as_label;
			} else {
				$list['label'] = "-";
			}
			// 在庫区分
			$list['zk_status_cd'] = $result->as_zk_status_cd;
			// 在庫数（総数）
			$list['total_qty'] = $result->as_total_qty;
			// 在庫数（新品）
			$list['new_qty'] = $result->as_new_qty;
			// 在庫数（中古）
			$list['used_qty'] = $result->as_used_qty;
			// 返却処理中
			$list['rtn_proc_qty'] = $result->as_rtn_proc_qty;
			// 返却予定
			$list['rtn_plan_qty'] = $result->as_rtn_plan_qty;
			// 貸与中
			$list['in_use_qty'] = $result->as_in_use_qty;
			// その他出荷
			$list['other_ship_qty'] = $result->as_other_ship_qty;
			// 廃棄済み
			$list['discarded_qty'] = $result->as_discarded_qty;
			// 商品名
			if (!empty($result->as_item_name)) {
				$list['item_name'] = $result->as_item_name;
			} else {
				$list['item_name'] = "-";
			}

			// 商品-色(サイズ-サイズ2)表示変換
			$list['shin_item_code'] = $list['zkprcd']."-".$list['zkclor']."(".$list['zksize'].")";
//			$list['shin_item_code'] = $list['zkprcd']."-".$list['zkclor']."(".$list['zksize']."-".$list['size_two_cd'].")";

			//---在庫区分名称---//
			$query_list = array();
			array_push($query_list, "cls_cd = '010'");
			array_push($query_list, "gen_cd = '".$list['zk_status_cd']."'");
			$query = implode(' AND ', $query_list);
			$gencode = MGencode::query()
				->where($query)
				->columns('*')
				->execute();
			foreach ($gencode as $gencode_map) {
				$list['zk_status_name'] = $gencode_map->gen_name;
			}

			array_push($all_list,$list);
		}
	}

	//---第二ソートキー(配列ソート)---//
	// 商品-色(サイズ-サイズ2)
	if($sort_key == 'item_code'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'shin_item_code'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'shin_item_code'), SORT_ASC, $all_list);
		}
	}

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $results_cnt;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;
	echo json_encode($json_list);
});
