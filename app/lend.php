<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 貸与リスト検索
 */
$app->post('/lend/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();

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
	if(!empty($cond['member_name'])){
		array_push($query_list,"m_wearer_std.werer_name LIKE '%".$cond['member_name']."%'");
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
		array_push($query_list,"m_wearer_item.item_cd = '".$cond['input_item']."'");
	}
	//色
	if(!empty($cond['item_color'])){
		array_push($query_list,"m_wearer_item.color_cd = '".$cond['item_color']."'");
	}
	//サイズ
	if(!empty($cond['item_size'])){
		array_push($query_list,"m_wearer_item.size_cd = '".$cond['item_size']."'");
	}
	//個体管理番号
	if(!empty($cond['individual_number'])){
		array_push($query_list,"t_delivery_goods_state_details.individual_ctrl_no LIKE '".$cond['individual_number']."%'");
	}

	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';

	//第一ソート設定
	if(!empty($page['sort_key'])){
		$sort_key = $page['sort_key'];
		$order = $page['order'];
		// 社員番号
		if($sort_key == 'cster_emply_cd'){
			$q_sort_key = 'as_cster_emply_cd';
		}
		// 着用者名
		if($sort_key == 'werer_name'){
			$q_sort_key = 'as_werer_name';
		}
		// 商品コード
		if($sort_key == 'item_code'){
			$q_sort_key = 'as_item_cd';
		}
		// 個体管理番号
		if($sort_key == 'individual_num'){
			$q_sort_key = 'as_individual_ctrl_no';
		}
		// 出荷日
		if($sort_key == 'send_ymd'){
			$q_sort_key = 'as_ship_ymd';
		}
		// 返却予定日
		if($sort_key == 'return_shd_ymd'){
			$q_sort_key = 'as_re_order_date';
		}
		// 発注No
		if($sort_key == 'order_req_no'){
			$q_sort_key = 'as_order_req_no';
		}
		// メーカー受注番号
		if($sort_key == 'maker_rec_no'){
			$q_sort_key = 'as_rec_order_no';
		}
		// メーカー伝票番号
		if($sort_key == 'maker_send_no'){
			$q_sort_key = 'as_ship_no';
		}
	} else {
		//指定がなければ社員番号
		$q_sort_key = "as_cster_emply_cd";
		$order = 'asc';
	}

	//---SQLクエリー実行---//
	$arg_str = "SELECT ";
	$arg_str .= " * ";
	$arg_str .= " FROM ";
//	$arg_str .= "(SELECT ";
	$arg_str .= "(SELECT distinct on (t_delivery_goods_state_details.individual_ctrl_no) ";
	$arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
	$arg_str .= "m_wearer_std.werer_name as as_werer_name,";
	$arg_str .= "m_wearer_std.rntl_sect_cd as as_now_rntl_sect_cd,";
	$arg_str .= "m_wearer_std.job_type_cd as as_now_job_type_cd,";
	$arg_str .= "t_order.rntl_sect_cd as as_old_rntl_sect_cd,";
	$arg_str .= "t_order.job_type_cd as as_old_job_type_cd,";
	$arg_str .= "m_wearer_item.item_cd as as_item_cd,";
	$arg_str .= "m_wearer_item.color_cd as as_color_cd,";
	$arg_str .= "m_wearer_item.size_cd as as_size_cd,";
	$arg_str .= "m_wearer_item.size_two_cd as as_size_two_cd,";
	$arg_str .= "m_wearer_item.job_type_item_cd as as_job_type_item_cd,";
	$arg_str .= "t_delivery_goods_state_details.individual_ctrl_no as as_individual_ctrl_no,";
	$arg_str .= "t_delivery_goods_state.ship_qty as as_ship_qty,";
	$arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
	$arg_str .= "t_returned_plan_info.order_date as as_re_order_date,";
	$arg_str .= "t_returned_plan_info.order_req_no as as_order_req_no,";
	$arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no,";
	$arg_str .= "t_delivery_goods_state.ship_no as as_ship_no";
	$arg_str .= " FROM t_order LEFT JOIN";
	$arg_str .= " (t_returned_plan_info LEFT JOIN";
	$arg_str .= " (t_order_state LEFT JOIN";
	$arg_str .= " (t_delivery_goods_state LEFT JOIN t_delivery_goods_state_details ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no)";
	$arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";
	$arg_str .= " ON t_returned_plan_info.order_req_no = t_order_state.order_req_no)";
	$arg_str .= " ON t_order.order_req_no = t_returned_plan_info.order_req_no";
	$arg_str .= " INNER JOIN m_wearer_std";
	$arg_str .= " ON t_order.m_wearer_std_comb_hkey = m_wearer_std.m_wearer_std_comb_hkey";
	$arg_str .= " INNER JOIN m_wearer_item";
	$arg_str .= " ON t_order.m_wearer_item_comb_hkey = m_wearer_item.m_wearer_item_comb_hkey";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= ") as distinct_table";
	if (!empty($q_sort_key)) {
		$arg_str .= " ORDER BY ";
		$arg_str .= $q_sort_key." ".$order;
	}

	$t_order = new TOrder();
	$results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
	// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
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
			// 現在の拠点コード
			$list['now_rntl_sect_cd'] = $result->as_now_rntl_sect_cd;
			// 現在の貸与パターン
			$list['now_job_type_cd'] = $result->as_now_job_type_cd;
			// 納品時の拠点コード
			$list['old_rntl_sect_cd'] = $result->as_old_rntl_sect_cd;
			// 納品時の貸与パターン
			$list['old_job_type_cd'] = $result->as_old_job_type_cd;
			// 商品コード
			$list['item_cd'] = $result->as_item_cd;
			// 色コード
			$list['color_cd'] = $result->as_color_cd;
			// サイズコード
			$list['size_cd'] = $result->as_size_cd;
			// サイズコード２
			$list['size_two_cd'] = $result->as_size_two_cd;
			// 職種アイテムコード
			$list['job_type_item_cd'] = $result->as_job_type_item_cd;
			// 個体管理番号
			if (!empty($result->as_individual_ctrl_no)) {
				$list['individual_ctrl_no'] = $result->as_individual_ctrl_no;
			} else {
				$list['individual_ctrl_no'] = "-";
			}
			// 出荷数
			if (!empty($result->as_ship_qty)) {
				$list['ship_qty'] = $result->as_ship_qty;
			} else {
				$list['ship_qty'] = "-";
			}
			// 出荷日
			$list['ship_ymd'] = $result->as_ship_ymd;
			// 返却予定日
			$list['re_order_date'] = $result->as_re_order_date;
			// 発注No
			if (!empty($result->as_order_req_no)) {
				$list['order_req_no'] = $result->as_order_req_no;
			} else {
				$list['order_req_no'] = "-";
			}
			// メーカー受注番号
			if (!empty($result->as_rec_order_no)) {
				$list['rec_order_no'] = $result->as_rec_order_no;
			} else {
				$list['rec_order_no'] = "-";
			}
			// メーカー伝票番号
			if (!empty($result->as_ship_no)) {
				$list['ship_no'] = $result->as_ship_no;
			} else {
				$list['ship_no'] = "-";
			}

			//---日付設定---//
			// 出荷日
			if(!empty($list['ship_ymd'])){
				$list['ship_ymd'] = date('Y/m/d',strtotime($list['ship_ymd']));
			}else{
				$list['ship_ymd'] = '-';
			}
			// 返却予定日
			if(!empty($list['re_order_date'])){
				$list['re_order_date'] =  date('Y/m/d',strtotime($list['re_order_date']));
			}else{
				$list['re_order_date'] = '-';
			}

			// 商品-色(サイズ-サイズ2)表示変換
			$list['shin_item_code'] = $list['item_cd']."-".$list['color_cd']."(".$list['size_cd']."-".$list['size_two_cd'].")";

			// 現在の拠点
			$search_q = array();
			array_push($search_q, "corporate_id = '".$auth['corporate_id']."'");
			array_push($search_q, "rntl_cont_no = '".$cond['agreement_no']."'");
			array_push($search_q, "rntl_sect_cd = '".$list['now_rntl_sect_cd']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $search_q);
			$section = MSection::query()
				->where($query)
				->columns('*')
				->execute();
			// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
			$section_obj = (array)$section;
			$cnt = $section_obj["\0*\0_count"];
			if (!empty($cnt)) {
				foreach ($section as $section_map) {
					$list['now_rntl_sect_name'] = $section_map->rntl_sect_name;
				}
			} else {
				$list['now_rntl_sect_name'] = "-";
			}
			// 納品時の拠点
			$search_q = array();
			array_push($search_q, "corporate_id = '".$auth['corporate_id']."'");
			array_push($search_q, "rntl_cont_no = '".$cond['agreement_no']."'");
			array_push($search_q, "rntl_sect_cd = '".$list['old_rntl_sect_cd']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $search_q);
			$section = MSection::query()
				->where($query)
				->columns('*')
				->execute();
			// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
			$section_obj = (array)$section;
			$cnt = $section_obj["\0*\0_count"];
			if (!empty($cnt)) {
				foreach ($section as $section_map) {
					$list['old_rntl_sect_name'] = $section_map->rntl_sect_name;
				}
			} else {
				$list['old_rntl_sect_name'] = "-";
			}

			// 現在の貸与パターン
			$search_q = array();
			array_push($search_q, "corporate_id = '".$auth['corporate_id']."'");
			array_push($search_q, "rntl_cont_no = '".$cond['agreement_no']."'");
			array_push($search_q, "job_type_cd = '".$list['now_job_type_cd']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $search_q);
			$job_type = MJobType::query()
				->where($query)
				->columns('*')
				->execute();
			// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
			$job_type_obj = (array)$job_type;
			$cnt = $job_type_obj["\0*\0_count"];
			if (!empty($cnt)) {
				foreach ($job_type as $job_type_map) {
					$list['now_job_type_name'] = $job_type_map->job_type_name;
				}
			} else {
				$list['now_job_type_name'] = "-";
			}
			// 納品時の貸与パターン
			$search_q = array();
			array_push($search_q, "corporate_id = '".$auth['corporate_id']."'");
			array_push($search_q, "rntl_cont_no = '".$cond['agreement_no']."'");
			array_push($search_q, "job_type_cd = '".$list['old_job_type_cd']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $search_q);
			$job_type = MJobType::query()
				->where($query)
				->columns('*')
				->execute();
			// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
			$job_type_obj = (array)$job_type;
			$cnt = $job_type_obj["\0*\0_count"];
			if (!empty($cnt)) {
				foreach ($job_type as $job_type_map) {
					$list['old_job_type_name'] = $job_type_map->job_type_name;
				}
			} else {
				$list['old_job_type_name'] = "-";
			}

			// 投入商品名
			$search_q = array();
			array_push($search_q, "corporate_id = '".$auth['corporate_id']."'");
			array_push($search_q, "rntl_cont_no = '".$cond['agreement_no']."'");
			array_push($search_q, "job_type_cd = '".$cond['job_type']."'");
			array_push($search_q, "job_type_item_cd = '".$list['job_type_item_cd']."'");
			array_push($search_q, "item_cd = '".$list['item_cd']."'");
			array_push($search_q, "color_cd = '".$list['color_cd']."'");
			array_push($search_q, "size_two_cd = '".$list['size_two_cd']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $search_q);
			$input_item = MInputItem::query()
				->where($query)
				->columns('*')
				->execute();
			// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
			$input_item_obj = (array)$input_item;
			$cnt = $input_item_obj["\0*\0_count"];
			if (!empty($cnt)) {
				foreach ($input_item as $input_item_map) {
					$list['input_item_name'] = $input_item_map->input_item_name;
				}
			} else {
				$list['input_item_name'] = "-";
			}

			array_push($all_list,$list);
		}
	}

	// 第二ソートキー(配列ソート)
	// 現在の拠点
	if($sort_key == 'now_rntl_sect_name'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'now_rntl_sect_name'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'now_rntl_sect_name'), SORT_ASC, $all_list);
		}
	}
	// 現在の貸与パターン
	if($sort_key == 'now_job_type_cd'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'now_job_type_name'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'now_job_type_name'), SORT_ASC, $all_list);
		}
	}
	// 納品時の拠点
	if($sort_key == 'old_rntl_sect_name'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'old_rntl_sect_name'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'old_rntl_sect_name'), SORT_ASC, $all_list);
		}
	}
	// 納品時の貸与パターン
	if($sort_key == 'old_job_type_cd'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'old_job_type_name'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'old_job_type_name'), SORT_ASC, $all_list);
		}
	}
	// 商品名（投入商品）
	if($sort_key == 'item_name'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'input_item_name'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'input_item_name'), SORT_ASC, $all_list);
		}
	}

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $results_cnt;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;
	echo json_encode($json_list);
});