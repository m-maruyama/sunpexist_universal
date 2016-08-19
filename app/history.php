<?php
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 発注状況照会検索
 */
$app->post('/history/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();

	//よろず発注No
	if(isset($cond['no'])){
		array_push($query_list,"TOrder.order_req_no LIKE '%".$cond['no']."%'");
	}
	//社員番号
	if(isset($cond['member_no'])){
		array_push($query_list,"TOrder.cster_emply_cd LIKE '%".$cond['member_no']."%'");
	}
	//拠点
	if(isset($cond['office'])){
		array_push($query_list,"MSection.rntl_sect_name LIKE '%".$cond['office']."%'");
	}

	//貸与パターン
	if(isset($cond['job_type'])){
		array_push($query_list,"MJobType.job_type_cd = '".$cond['job_type']."'");
	}
	//発注日from
	if(isset($cond['order_day_from'])){
		array_push($query_list,"TO_DATE(TOrder.order_req_ymd,'YYYYMMDD') >= TO_DATE('".$cond['order_day_from']."','YYYY/MM/DD')");
	}
	//発注日to
	if(isset($cond['order_day_to'])){
		array_push($query_list,"TO_DATE(TOrder.order_req_ymd,'YYYYMMDD') <= TO_DATE('".$cond['order_day_to']."','YYYY/MM/DD')");
	}
	//ステータス
	//havingを使ってgroup化後のデータで検索
	$status = array();
	$status_list = array();
	if($cond['status0']){
		array_push($status_list,"sum(CASE TOrder.order_status WHEN '1' THEN 1 ELSE 0 END ) > 0");
	}
	if($cond['status1']){
		array_push($status_list,"sum(CASE TOrder.order_status WHEN '2' THEN 1 ELSE 0 END ) = count(TOrder.order_req_no)");
	}
	if($cond['status4']){
		array_push($status_list,"sum(CASE TOrder.order_status WHEN '9' THEN 1 ELSE 0 END ) > 0");
	}
	//受領ステータス
	$r_status = '';
	if($cond['status2']){
		//未受領のみ
		array_push($status_list,"sum(CASE TDeliveryGoodsState.receipt_status WHEN '1' THEN 1 WHEN null THEN 1 ELSE 0 END ) > 0");
	}
	if($cond['status3']){
		array_push($status_list,"sum(CASE TDeliveryGoodsState.receipt_status WHEN '2' THEN TDeliveryGoodsState.ship_qty ELSE 0 END ) = sum(TDeliveryGoodsState.ship_qty)");
	}
	$status_query ='';
	if($status_list){
		$status_query = implode(' OR ', $status_list);
	}

	//よろず発注区分
	$order_kbn = array();
	if($cond['order_kbn0']){
		array_push($order_kbn,'1');
	}
	if($cond['order_kbn1']){
		array_push($order_kbn,'3');
	}
	if($cond['order_kbn2']){
		array_push($order_kbn,'4');
	}
	if($cond['order_kbn3']){
		array_push($order_kbn,'5');
	}
	if($order_kbn){
		$order_kbn_str = implode("','",$order_kbn);
		array_push($query_list,"order_sts_kbn IN ('".$order_kbn_str."')");
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
		//なければよろず発注No
		$sort_key = "TOrder.order_req_no";
		$order = 'asc';
	}
	$builder = $app->modelsManager->createBuilder()
		->where($query)
		->from('TOrder')
		->columns(array('TOrder.order_req_no as as_order_req_no',
		'min(TOrder.cster_emply_cd) as as_cster_emply_cd',
		'min(MSection.rntl_sect_name) as as_rntl_sect_name',
		'min(MJobType.job_type_name) as as_job_type_name',
		'min(TOrder.order_req_ymd) as as_order_req_ymd',
		'min(TOrder.order_status) as as_order_status',
		'min(TOrder.order_sts_kbn) as as_order_sts_kbn',
		'min(TDeliveryGoodsState.receipt_status) as as_receipt_status',
		'max(TDeliveryGoodsState.ship_ymd) as as_ship_ymd',
		'sum(TOrder.order_qty) as as_order_qty',
		"sum(CASE TOrder.order_status WHEN '1' THEN 1 ELSE 0 END )  as as_misyukka",
		"sum(CASE TOrder.order_status WHEN '9' THEN 1 ELSE 0 END )  as as_cancel",
		"sum(CASE TDeliveryGoodsState.ship_qty > 0 WHEN true THEN TDeliveryGoodsState.ship_qty ELSE 0 END) as as_ship_qty",
		"sum(CASE TDeliveryGoodsState.receipt_status WHEN '2' THEN TDeliveryGoodsState.ship_qty ELSE 0 END ) as receipt_num",
		"sum( CASE TDeliveryGoodsState.receipt_status WHEN '1' THEN 1 END) as as_rec_num"))
		->having($status_query)
		->leftJoin('TOrderState','TOrderState.t_order_comb_hkey = TOrder.t_order_comb_hkey')
		->leftJoin('TDeliveryGoodsState','TDeliveryGoodsState.t_order_state_comb_hkey = TOrderState.t_order_state_comb_hkey')
		->join('MSection','MSection.m_section_comb_hkey = TOrder.m_section_comb_hkey')
		->join('MJobType','MJobType.m_job_type_comb_hkey = TOrder.m_job_type_comb_hkey')
		->groupBy('TOrder.order_req_no');

	//総件数取得用(phalconのバグでgroup by時の検索総件数が取れないため)
	$sql = $builder->getQuery()->getSql();
	$cnt = $app->db->fetchColumn('select count(*) from ('.$sql['sql'].') as cnt');

	$builder->orderBy($sort_key.' '.$order);
	$paginator_model = new PaginatorQueryBuilder(
		array(
			"builder"  => $builder,
			"limit" => $page['records_per_page'],
			"page" => $page['page_number']
		)
	);
	$list = array();
	$all_list = array();
	$json_list = array();
	if($cnt){
		$paginator = $paginator_model->getPaginate();
		$results = $paginator->items;
		foreach($results as $result){
			if(!isset($result)){
				break;
			}
			$list['order_req_no'] = $result->as_order_req_no;
			$list['cster_emply_cd'] = $result->as_cster_emply_cd;
			$list['rntl_sect_name'] = $result->as_rntl_sect_name;
			$list['job_type_name'] = $result->as_job_type_name;

			if($result->as_order_req_ymd){
				$list['order_req_ymd'] =  date('Y/m/d',strtotime($result->as_order_req_ymd));
			}else{
				$list['order_req_ymd'] = '-';
			}
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
			array_push($all_list,$list);
		}

	}

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $cnt;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;
	echo json_encode($json_list);

});
