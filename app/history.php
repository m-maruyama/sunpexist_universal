<?php
//use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 発注状況照会検索
 */
$app->post('/history/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();

	//---検索条件---//
	//企業ID
	array_push($query_list,"TOrder.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	if(isset($cond['agreement_no'])){
		array_push($query_list,"TOrder.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//発注No
	if(isset($cond['no'])){
		array_push($query_list,"TOrder.order_req_no LIKE '".$cond['no']."%'");
	}
	//お客様発注No
	if(isset($cond['emply_order'])){
		array_push($query_list,"TOrder.emply_req_no LIKE '".$cond['emply_order_no']."%'");
	}
	//社員番号
	if(isset($cond['member_no'])){
		array_push($query_list,"TOrder.cster_emply_cd LIKE '".$cond['member_no']."%'");
	}
	//着用者名
	if(isset($cond['member_name'])){
		array_push($query_list,"TOrder.werer_name LIKE '%".$cond['member_name']."%'");
	}
	//拠点
	if(isset($cond['section'])){
		array_push($query_list,"TOrder.rntl_sect_cd = '".$cond['section']."'");
	}
//	if(isset($cond['office'])){
//		array_push($query_list,"MSection.rntl_sect_name LIKE '%".$cond['office']."%'");
//	}
	//貸与パターン
	if(isset($cond['job_type'])){
		array_push($query_list,"TOrder.job_type_cd = '".$cond['job_type']."'");
	}
	//商品
	if(isset($cond['input_item'])){
		array_push($query_list,"TOrder.item_cd = '".$cond['input_item']."'");
	}
	//色
	if(isset($cond['item_color'])){
		array_push($query_list,"TOrder.color_cd = '".$cond['item_color']."'");
	}
	//サイズ
	if(isset($cond['item_size'])){
		array_push($query_list,"TOrder.size_cd = '".$cond['item_size']."'");
	}
	//発注日from
	if(isset($cond['order_day_from'])){
		array_push($query_list,"TO_DATE(TOrder.order_req_ymd,'YYYYMMDD') >= TO_DATE('".$cond['order_day_from']."','YYYY/MM/DD')");
	}
	//発注日to
	if(isset($cond['order_day_to'])){
		array_push($query_list,"TO_DATE(TOrder.order_req_ymd,'YYYYMMDD') <= TO_DATE('".$cond['order_day_to']."','YYYY/MM/DD')");
	}
	//出荷日from
	if(isset($cond['send_day_from'])){
		array_push($query_list,"TO_DATE(TOrderState.ship_ymd,'YYYYMMDD') >= TO_DATE('".$cond['send_day_from']."','YYYY/MM/DD')");
	}
	//出荷日to
	if(isset($cond['send_day_to'])){
		array_push($query_list,"TO_DATE(TOrderState.ship_ymd,'YYYYMMDD') <= TO_DATE('".$cond['send_day_to']."','YYYY/MM/DD')");
	}
	//個体管理番号
	if(isset($cond['individual_number'])){
		array_push($query_list,"TDeliveryGoodsStateDetails.individual_ctrl_no LIKE '".$cond['individual_number']."%'");
	}
	//ステータス
	//havingを使ってgroup化後のデータで検索
	$status = array();
	$status_list = array();
	if($cond['status0']){
		// 未出荷
		array_push($status_list,"sum(CASE TOrder.order_status WHEN '1' THEN 1 ELSE 0 END ) > 0");
	}
	if($cond['status1']){
		// 出荷済み
		array_push($status_list,"sum(CASE TOrder.order_status WHEN '2' THEN 1 ELSE 0 END ) = count(TOrder.order_req_no)");
	}
/*
	if($cond['status4']){
		array_push($status_list,"sum(CASE TOrder.order_status WHEN '9' THEN 1 ELSE 0 END ) > 0");
	}
*/
/*
	//受領ステータス
	$r_status = '';
	if($cond['status2']){
		//未受領のみ
		array_push($status_list,"sum(CASE TDeliveryGoodsState.receipt_status WHEN '1' THEN 1 WHEN null THEN 1 ELSE 0 END ) > 0");
	}
	if($cond['status3']){
		array_push($status_list,"sum(CASE TDeliveryGoodsState.receipt_status WHEN '2' THEN TDeliveryGoodsState.ship_qty ELSE 0 END ) = sum(TDeliveryGoodsState.ship_qty)");
	}
*/

	$status_query ='';
	if($status_list){
		$status_query = implode(' OR ', $status_list);
	}

	//発注区分
	$order_kbn = array();
	if($cond['order_kbn0']){
		array_push($order_kbn,'1');
	}
	if($cond['order_kbn1']){
		array_push($order_kbn,'3');
	}
	if($cond['order_kbn2']){
		array_push($order_kbn,'5');
	}
	if($cond['order_kbn3']){
		array_push($order_kbn,'2');
	}
	if($cond['order_kbn4']){
		array_push($order_kbn,'9');
	}
	if($order_kbn){
		$order_kbn_str = implode("','",$order_kbn);
		array_push($query_list,"order_sts_kbn IN ('".$order_kbn_str."')");
	}

	// 理由区分
	$reason_kbn = array();
	if($cond['reason_kbn0']){
		array_push($reason_kbn,'1');
	}
	$reason_kbn = array();
	if($cond['reason_kbn1']){
		array_push($reason_kbn,'1');
	}
	$reason_kbn = array();
	if($cond['reason_kbn2']){
		array_push($reason_kbn,'2');
	}
	$reason_kbn = array();
	if($cond['reason_kbn3']){
		array_push($reason_kbn,'3');
	}
	$reason_kbn = array();
	if($cond['reason_kbn4']){
		array_push($reason_kbn,'19');
	}
	$reason_kbn = array();
	if($cond['reason_kbn5']){
		array_push($reason_kbn,'14');
	}
	$reason_kbn = array();
	if($cond['reason_kbn6']){
		array_push($reason_kbn,'15');
	}
	$reason_kbn = array();
	if($cond['reason_kbn7']){
		array_push($reason_kbn,'16');
	}
	$reason_kbn = array();
	if($cond['reason_kbn8']){
		array_push($reason_kbn,'17');
	}
	$reason_kbn = array();
	if($cond['reason_kbn9']){
		array_push($reason_kbn,'21');
	}
	$reason_kbn = array();
	if($cond['reason_kbn10']){
		array_push($reason_kbn,'22');
	}
	$reason_kbn = array();
	if($cond['reason_kbn11']){
		array_push($reason_kbn,'23');
	}
	$reason_kbn = array();
	if($cond['reason_kbn12']){
		array_push($reason_kbn,'9');
	}
	$reason_kbn = array();
	if($cond['reason_kbn13']){
		array_push($reason_kbn,'10');
	}
	$reason_kbn = array();
	if($cond['reason_kbn14']){
		array_push($reason_kbn,'11');
	}
	$reason_kbn = array();
	if($cond['reason_kbn15']){
		array_push($reason_kbn,'5');
	}
	$reason_kbn = array();
	if($cond['reason_kbn16']){
		array_push($reason_kbn,'6');
	}
	$reason_kbn = array();
	if($cond['reason_kbn17']){
		array_push($reason_kbn,'7');
	}
	$reason_kbn = array();
	if($cond['reason_kbn18']){
		array_push($reason_kbn,'8');
	}
	$reason_kbn = array();
	if($cond['reason_kbn19']){
		array_push($reason_kbn,'24');
	}
	if($reason_kbn){
		$reason_kbn_str = implode("','",$reason_kbn);
		array_push($query_list,"order_reason_kbn IN ('".$reason_kbn_str."')");
	}

	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';
	//ソートキー
	if(isset($page['sort_key'])){
		$sort_key = $page['sort_key'];

		if($sort_key=='job_type_cd'){
			$sort_key = 'as_job_type_name';
		}else{
			$sort_key = 'as_'.$sort_key;
		}
		// if($sort_key=='cster_emply_cd'){
			// $sort_key = 'as_cster_emply_cd';
		// }
		// if($sort_key=='order_req_no'||$sort_key=='order_req_ymd'||$sort_key=='order_status'||$sort_key=='order_sts_kbn'){
			// $sort_key = 'TOrder.'.$sort_key;
		// }
		// if($sort_key=='ship_ymd'){
			// $sort_key = 'TDeliveryGoodsState.'.$sort_key;
		// }
		// if($sort_key=='rntl_sect_name'){
			// $sort_key = 'MSection.'.$sort_key;
		// }
		$order = $page['order'];
	} else {
		//なければ発注No
		$sort_key = "TOrder.order_req_no";
		$order = 'asc';
	}

	//---SQLクエリー実行---//
		$arg_str = "SELECT distinct on (t_order.order_req_no, t_order.order_req_line_no) ";
		$arg_str .= "t_order.order_req_no AS as_order_req_no,";
		$arg_str .= "t_order.order_req_ymd as as_order_req_ymd,";
		$arg_str .= "t_order.order_sts_kbn as as_order_sts_kbn,";
		$arg_str .= "t_order.order_reason_kbn as as_order_reason_kbn,";
		$arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
		$arg_str .= "m_job_type.job_type_name as as_job_type_name,";
		$arg_str .= "t_order.cster_emply_cd as as_cster_emply_cd,";
		$arg_str .= "t_order.werer_name as as_werer_name,";
		$arg_str .= "t_order.item_cd as as_item_cd,";
		$arg_str .= "t_order.color_cd as as_color_cd,";
		$arg_str .= "t_order.size_cd as as_size_cd,";
		$arg_str .= "t_order.size_two_cd as as_size_two_cd,";
		$arg_str .= "m_input_item.input_item_name as as_input_item_name,";
		$arg_str .= "t_order.order_qty as as_order_qty,";
		$arg_str .= "t_order_state.rec_order_no as as_rec_order_no,";
		$arg_str .= "t_order.order_status as as_order_status,";
		$arg_str .= "t_delivery_goods_state.ship_no as as_ship_no,";
		$arg_str .= "t_order_state.ship_ymd as as_ship_ymd,";
		$arg_str .= "t_order_state.ship_qty as as_ship_qty,";
		$arg_str .= "t_order.rntl_cont_no as as_rntl_cont_no,";
		$arg_str .= "m_contract.rntl_cont_name as as_rntl_cont_name";
		$arg_str .= " FROM t_order LEFT JOIN";
		$arg_str .= " (t_order_state LEFT JOIN t_delivery_goods_state ";
		$arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";
		$arg_str .= " ON t_order.t_order_comb_hkey = t_order_state.t_order_comb_hkey";
		$arg_str .= " INNER JOIN m_section";
		$arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
		$arg_str .= " INNER JOIN (m_job_type INNER JOIN m_input_item ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey)";
		$arg_str .= " ON t_order.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
		$arg_str .= " INNER JOIN m_contract";
		$arg_str .= " ON t_order.rntl_cont_no = m_contract.rntl_cont_no";

		$t_order = new TOrder();
		$results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));

/*
	$builder = $app->modelsManager->createBuilder()
//		->where($query)
		->from('TOrder')
		->columns(
		array('TOrder.order_req_no as as_order_req_no',
		'TOrder.order_req_ymd as as_order_req_ymd',
		'TOrder.order_sts_kbn as as_order_sts_kbn',
		'TOrder.order_reason_kbn as as_order_reason_kbn',
		'MSection.rntl_sect_name as as_rntl_sect_name',
		'MJobType.job_type_name as as_job_type_name',
		'TOrder.cster_emply_cd as as_cster_emply_cd',
		'TOrder.werer_name as as_werer_name',
		'TOrder.item_cd as as_item_cd',
		'TOrder.color_cd as as_color_cd',
		'TOrder.size_cd as as_size_cd',
		'TOrder.size_two_cd as as_size_two_cd',
		'MInputItem.input_item_name as as_input_item_name',
		'TOrder.order_qty as as_order_qty',
		'TOrderState.rec_order_no as as_rec_order_no',
		'TOrder.order_status as as_order_status',
		'TDeliveryGoodsState.ship_no as as_ship_no',
		'TOrderState.ship_ymd as as_ship_ymd',
		'TOrderState.ship_qty as as_ship_qty',
		'TOrder.rntl_cont_no as as_rntl_cont_no',
		'MContract.rntl_cont_name as as_rntl_cont_name')
		)
//		->having($status_query)
		->leftJoin('TOrderState','TOrderState.t_order_comb_hkey = TOrder.t_order_comb_hkey')
		->leftJoin('TDeliveryGoodsState','TDeliveryGoodsState.t_order_state_comb_hkey = TOrderState.t_order_state_comb_hkey')
		->leftJoin('TDeliveryGoodsStateDetails','TDeliveryGoodsStateDetails.ship_no = TDeliveryGoodsState.ship_no')
		->join('MSection','MSection.m_section_comb_hkey = TOrder.m_section_comb_hkey')
		->join('MJobType','MJobType.m_job_type_comb_hkey = TOrder.m_job_type_comb_hkey')
		->join('MInputItem','MInputItem.m_job_type_comb_hkey = MJobType.m_job_type_comb_hkey')
		->join('MContract','MContract.rntl_cont_no = TOrder.rntl_cont_no')
		->distinct('TOrder.order_req_no');

	//総件数取得用(phalconのバグでgroup by時の検索総件数が取れないため)
	$sql = $builder->getQuery()->getSql();
	$cnt = $app->db->fetchColumn('select count(*) from ('.$sql['sql'].') as cnt');
//	$builder->orderBy($sort_key.' '.$order);
*/

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

	if($results){
		$paginator = $paginator_model->getPaginate();
		$results = $paginator->items;
		foreach($results as $result){
			if(!isset($result)){
				break;
			}
			$list['order_req_no'] = $result->as_order_req_no;
			$list['order_req_ymd'] = $result->as_order_req_ymd;
			$list['order_sts_kbn'] = $result->as_order_sts_kbn;
			$list['order_reason_kbn'] = $result->as_order_reason_kbn;
			$list['rntl_sect_name'] = $result->as_rntl_sect_name;
			$list['job_type_name'] = $result->as_job_type_name;
			$list['cster_emply_cd'] = $result->as_cster_emply_cd;
			$list['werer_name'] = $result->as_werer_name;
			$list['item_cd'] = $result->as_item_cd;
			$list['color_cd'] = $result->as_color_cd;
			$list['size_cd'] = $result->as_size_cd;
			$list['size_two_cd'] = $result->as_size_two_cd;
			$list['input_item_name'] = $result->as_input_item_name;
			$list['order_qty'] = $result->as_order_qty;
			$list['rec_order_no'] = $result->as_rec_order_no;
			$list['order_status'] = $result->as_order_status;
			$list['ship_no'] = $result->as_ship_no;
			$list['ship_ymd'] = $result->as_ship_ymd;
			$list['ship_qty'] = $result->as_ship_qty;
			$list['rntl_cont_no'] = $result->as_rntl_cont_no;
			$list['rntl_cont_name'] = $result->as_rntl_cont_name;

			// 日付設定
			if($list['order_req_ymd']){
				$list['order_req_ymd'] = date('Y/m/d',strtotime($list['order_req_ymd']));
				// 出荷予定日
				$list['send_shd_ymd'] = date('Y/m/d',strtotime($list['order_req_ymd'].' +7 day'));
			}else{
				$list['order_req_ymd'] = '-';
				$list['send_shd_ymd'] = '-';
			}
			if($list['ship_ymd']){
				$list['ship_ymd'] =  date('Y/m/d',strtotime($list['ship_ymd']));
			}else{
				$list['ship_ymd'] = '-';
			}

			// 商品-色(サイズ-サイズ2)表示変換
			$list['shin_item_code'] = $list['item_cd']."-".$list['color_cd']."(".$list['size_cd']."-".$list['size_two_cd'].")";

			//---発注区分名称---//
			$query_list = array();
			// 汎用コードマスタ.分類コード
			array_push($query_list, "cls_cd = '001'");
			// 汎用コードマスタ. レンタル契約No
			array_push($query_list, "gen_cd = '".$list['order_sts_kbn']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $query_list);
			$gencode = MGencode::query()
				->where($query)
				->columns('*')
				->execute();
			foreach ($gencode as $gencode_map) {
				$list['order_sts_name'] = $gencode_map->gen_name;
			}

			//---理由区分名称---//
			$query_list = array();
			// 汎用コードマスタ.分類コード
			array_push($query_list, "cls_cd = '002'");
			// 汎用コードマスタ. レンタル契約No
			array_push($query_list, "gen_cd = '".$list['order_reason_kbn']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $query_list);
			$gencode = MGencode::query()
				->where($query)
				->columns('*')
				->execute();
			foreach ($gencode as $gencode_map) {
				$list['order_reason_name'] = $gencode_map->gen_name;
			}

			//---発注ステータス名称---//
			$query_list = array();
			// 汎用コードマスタ.分類コード
			array_push($query_list, "cls_cd = '006'");
			// 汎用コードマスタ. レンタル契約No
			array_push($query_list, "gen_cd = '".$list['order_status']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $query_list);
			$gencode = MGencode::query()
				->where($query)
				->columns('*')
				->execute();
			foreach ($gencode as $gencode_map) {
				$list['order_status_name'] = $gencode_map->gen_name;
			}

			//---個体管理番号・受領日時の取得---//
			$query_list = array();
			// 納品状況明細情報. 企業ID
			array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
			// 納品状況明細情報. 出荷No
			array_push($query_list, "ship_no = '".$list['ship_no']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $query_list);
			$del_gd_std = TDeliveryGoodsStateDetails::query()
				->where($query)
				->columns('*')
				->execute();
			if ($del_gd_std) {
				$num_list = array();
				$day_list = array();
				foreach ($del_gd_std as $del_gd_std_map) {
					array_push($num_list, $del_gd_std_map->individual_ctrl_no);
					array_push($day_list, date('Y/m/d',strtotime($del_gd_std_map->receipt_date)));
				}
				// 個体管理番号
				$individual_ctrl_no = implode("<br>", $num_list);
				$list['individual_num'] = $individual_ctrl_no;
				// 受領日
				$receipt_date = implode("<br>", $day_list);
				$list['order_res_ymd'] = $receipt_date;
			} else {
				$list['individual_num'] = "-";
				$list['order_res_ymd'] = "-";
			}

/*
			// 未出荷＝発注情報テーブル．発注数のサマリ != 納品状況情報テーブル．出荷数のサマリ の場合
			// キャンセル＝発注情報テーブル．発注ステータス = 9のデータが存在する場合
			// 出荷済＝発注情報テーブル．発注数のサマリ == 納品状況情報テーブル．出荷数のサマリ の場合
			$list['order_status'] = null;
			if($result->as_misyukka > 0){
				$list['order_status'] = '1';
			} elseif($result->as_cancel > 0) {
				$list['order_status'] = '9';
			}else{
				$list['order_status'] = '2';
			}
			$list['order_sts_kbn'] = $result->as_order_sts_kbn;
			//出荷数
			$list['ship_qty'] = 0;
			$list['ship_qty'] = $result->as_ship_qty;//納品状況情報．出荷数
			//受領数
			$list['receipt_num'] = $result->receipt_num;
			//受領ステータス
			if($result->as_receipt_status){
				$list['receipt_status'] = $result->as_receipt_status;
			} else {
				$list['receipt_status'] = 1;
			}
			//最新出荷日
			if($result->as_ship_ymd){
				$list['ship_ymd'] =  date('Y/m/d',strtotime($result->as_ship_ymd));//納品状況情報．出荷日
			}else{
				$list['ship_ymd'] = '-';
			}
*/
			array_push($all_list,$list);
		}
	}

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
//	$page_list['total_records'] = $cnt;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;
	echo json_encode($json_list);
});