<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 受領確認照会検索
 */
$app->post('/receive/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();

    //---契約リソースマスター 0000000000フラグ確認処理---//
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
	array_push($query_list,"t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	if(!empty($cond['agreement_no'])){
		array_push($query_list,"t_delivery_goods_state_details.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//発注No
	if(!empty($cond['no'])){
		array_push($query_list,"t_order.order_req_no LIKE '".$cond['no']."%'");
	}
	//お客様発注No
	if(!empty($cond['emply_order_no'])){
		array_push($query_list,"t_order.emply_order_req_no LIKE '".$cond['emply_order_no']."%'");
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
		array_push($query_list,"t_order.rntl_sect_cd = '".$cond['section']."'");
	}
	//貸与パターン
	if(!empty($cond['job_type'])){
		array_push($query_list,"t_order.job_type_cd = '".$cond['job_type']."'");
	}
	//商品
	if(!empty($cond['input_item'])){
		array_push($query_list,"t_delivery_goods_state_details.item_cd = '".$cond['input_item']."'");
	}
	//色
	if(!empty($cond['item_color'])){
		array_push($query_list,"t_delivery_goods_state_details.color_cd = '".$cond['item_color']."'");
	}
	//サイズ
	if(!empty($cond['item_size'])){
		array_push($query_list,"t_delivery_goods_state_details.size_cd = '".$cond['item_size']."'");
	}
	//発注日from
	if(!empty($cond['order_day_from'])){
		array_push($query_list,"TO_DATE(t_order.order_req_ymd,'YYYY/MM/DD') >= TO_DATE('".$cond['order_day_from']."','YYYY/MM/DD')");
	}
	//発注日to
	if(!empty($cond['order_day_to'])){
		array_push($query_list,"TO_DATE(t_order.order_req_ymd,'YYYY/MM/DD') <= TO_DATE('".$cond['order_day_to']."','YYYY/MM/DD')");
	}
	//受領日from
	if(!empty($cond['receipt_day_from'])){
		$cond['receipt_day_from'] = date('Y-m-d 00:00:00', strtotime($cond['receipt_day_from']));
		array_push($query_list,"t_delivery_goods_state_details.receipt_date >= '".$cond['receipt_day_from']."'");
//		array_push($query_list,"TO_DATE(t_order_state.ship_ymd,'YYYY/MM/DD') >= TO_DATE('".$cond['send_day_from']."','YYYY/MM/DD')");
	}
	//受領日to
	if(!empty($cond['receipt_day_to'])){
		$cond['receipt_day_to'] = date('Y-m-d 23:59:59', strtotime($cond['receipt_day_to']));
		array_push($query_list,"t_delivery_goods_state_details.receipt_date <= '".$cond['receipt_day_to']."'");
//		array_push($query_list,"TO_DATE(t_order_state.ship_ymd,'YYYY/MM/DD') <= TO_DATE('".$cond['send_day_to']."','YYYY/MM/DD')");
	}
	//個体管理番号
	if(!empty($cond['individual_number'])){
		array_push($query_list,"t_delivery_goods_state_details.individual_ctrl_no LIKE '".$cond['individual_number']."%'");
	}
	// 着用者状況区分
	array_push($query_list,"m_wearer_std.werer_sts_kbn = '1'");
	//ゼロ埋めがない場合、ログインアカウントの条件追加
	if($rntl_sect_cd_zero_flg == 0) {
			array_push($query_list, "m_contract_resource.accnt_no = '$accnt_no'");
	}

	$status_kbn_list = array();

	//ステータス
	$status_list = array();
	if($cond['status0']){
		// 未受領
		array_push($status_list,"1");
	}
	if($cond['status1']){
		// 受領済み
		array_push($status_list,"2");
	}
	if(!empty($status_list)) {
		$status_str = implode("','",$status_list);
//		$status_query = "order_status IN ('".$status_str."')";
		array_push($query_list,"t_delivery_goods_state_details.receipt_status IN ('".$status_str."')");
//		array_push($status_kbn_list,$status_query);
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
	if(!empty($order_kbn)){
		$order_kbn_str = implode("','",$order_kbn);
		$order_kbn_query = "t_order.order_sts_kbn IN ('".$order_kbn_str."')";
//		array_push($query_list,"order_sts_kbn IN ('".$order_kbn_str."')");
		array_push($status_kbn_list,$order_kbn_query);
	}
	// 理由区分
	$reason_kbn = array();
	if($cond['reason_kbn0']){
		array_push($reason_kbn,'01');
	}
	if($cond['reason_kbn1']){
		array_push($reason_kbn,'02');
	}
	if($cond['reason_kbn2']){
		array_push($reason_kbn,'03');
	}
	if($cond['reason_kbn3']){
		array_push($reason_kbn,'04');
	}
	if($cond['reason_kbn4']){
		array_push($reason_kbn,'19');
	}
	if($cond['reason_kbn5']){
		array_push($reason_kbn,'14');
	}
	if($cond['reason_kbn6']){
		array_push($reason_kbn,'15');
	}
	if($cond['reason_kbn7']){
		array_push($reason_kbn,'16');
	}
	if($cond['reason_kbn8']){
		array_push($reason_kbn,'17');
	}
	if($cond['reason_kbn9']){
		array_push($reason_kbn,'21');
	}
	if($cond['reason_kbn10']){
		array_push($reason_kbn,'22');
	}
	if($cond['reason_kbn11']){
		array_push($reason_kbn,'23');
	}
	if($cond['reason_kbn12']){
		array_push($reason_kbn,'09');
	}
	if($cond['reason_kbn13']){
		array_push($reason_kbn,'10');
	}
	if($cond['reason_kbn14']){
		array_push($reason_kbn,'11');
	}
	if($cond['reason_kbn15']){
		array_push($reason_kbn,'05');
	}
	if($cond['reason_kbn16']){
		array_push($reason_kbn,'06');
	}
	if($cond['reason_kbn17']){
		array_push($reason_kbn,'07');
	}
	if($cond['reason_kbn18']){
		array_push($reason_kbn,'08');
	}
	if($cond['reason_kbn19']){
		array_push($reason_kbn,'24');
	}
	if(!empty($reason_kbn)){
		$reason_kbn_str = implode("','",$reason_kbn);
		$reason_kbn_query = "t_order.order_reason_kbn IN ('".$reason_kbn_str."')";
//		array_push($query_list,"order_reason_kbn IN ('".$reason_kbn_str."')");
		array_push($status_kbn_list,$reason_kbn_query);
	}

	//各区分を' OR 'で結合
	if (!empty($status_kbn_list)) {
		$status_kbn_map = implode(' OR ', $status_kbn_list);
		array_push($query_list,"(".$status_kbn_map.")");
	}

	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';

	//ソート設定
	if(!empty($page['sort_key'])){
		$sort_key = $page['sort_key'];
		$order = $page['order'];
		// 受領日
		if($sort_key == 'receipt_date'){
			$q_sort_key = 'as_receipt_date';
		}
		// メーカー伝票番号
		if($sort_key == 'maker_rec_no'){
			$q_sort_key = 'as_rec_order_no';
		}
		// 商品名
		if($sort_key == 'item_name'){
			$q_sort_key = 'as_input_item_name';
		}
		// 個体管理番号
		if($sort_key == 'individual_num'){
			$q_sort_key = 'as_individual_ctrl_no';
		}
		// 発注No
		if($sort_key == 'order_req_no'){
			$q_sort_key = 'as_order_req_no';
		}
		// 発注行No
		if($sort_key == 'order_line_no'){
			$q_sort_key = 'as_order_req_line_no';
		}
		// 社員番号
		if($sort_key == 'cster_emply_cd'){
			$q_sort_key = 'as_cster_emply_cd';
		}
		// 着用者名
		if($sort_key == 'werer_name'){
			$q_sort_key = 'as_werer_name';
		}
		// 拠点
		if($sort_key == 'rntl_sect_name'){
			$q_sort_key = 'as_rntl_sect_name';
		}
		// 貸与パターン
		if($sort_key == 'job_type_cd'){
			$q_sort_key = 'as_job_type_name';
		}
		// 受領ステータス
		if($sort_key == 'receipt_status'){
			$q_sort_key = 'as_receipt_status';
		}
		// 発注区分
		if($sort_key == 'order_sts_kbn'){
			$q_sort_key = 'as_order_sts_kbn';
		}
		// 発注日
		if($sort_key == 'order_req_ymd'){
			$q_sort_key = 'as_order_req_ymd';
		}
		// 出荷日
		if($sort_key == 'send_ymd'){
			$q_sort_key = 'as_ship_ymd';
		}
		// メーカー受注番号
		if($sort_key == 'maker_send_no'){
			$q_sort_key = 'as_ship_no';
		}
	} else {
		//指定がなければ発注No
		$q_sort_key = "as_order_req_no";
		$order = 'asc';
	}

	//---SQLクエリー実行---//
	$arg_str = "SELECT ";
	$arg_str .= " * ";
	$arg_str .= " FROM ";
	$arg_str .= "(SELECT distinct on ";
	$arg_str .= "(t_delivery_goods_state_details.ship_no,";
	$arg_str .= "t_delivery_goods_state_details.ship_line_no) ";
	$arg_str .= "t_delivery_goods_state_details.receipt_status as as_receipt_status,";
	$arg_str .= "t_delivery_goods_state_details.receipt_date as as_receipt_date,";
	$arg_str .= "t_delivery_goods_state_details.ship_no as as_ship_no,";
	$arg_str .= "t_delivery_goods_state_details.ship_line_no as as_ship_line_no,";
	$arg_str .= "t_order.item_cd as as_item_cd,";
	$arg_str .= "t_order.color_cd as as_color_cd,";
	$arg_str .= "t_order.size_cd as as_size_cd,";
	$arg_str .= "t_order.size_two_cd as as_size_two_cd,";
	$arg_str .= "m_input_item.input_item_name as as_input_item_name,";
	$arg_str .= "t_order_state.ship_qty as as_ship_qty,";
	$arg_str .= "t_delivery_goods_state_details.individual_ctrl_no as as_individual_ctrl_no,";
	$arg_str .= "t_order.order_req_no as as_order_req_no,";
	$arg_str .= "t_order.order_req_line_no as as_order_req_line_no,";
	$arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
	$arg_str .= "m_wearer_std.werer_name as as_werer_name,";
	$arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
	$arg_str .= "m_job_type.job_type_name as as_job_type_name,";
	$arg_str .= "t_order.order_sts_kbn as as_order_sts_kbn,";
	$arg_str .= "t_order.order_req_ymd as as_order_req_ymd,";
	$arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
	$arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no";
	$arg_str .= " FROM t_delivery_goods_state_details INNER JOIN";
	$arg_str .= " (t_delivery_goods_state INNER JOIN";
	$arg_str .= " (t_order_state INNER JOIN (t_order";
	if ($rntl_sect_cd_zero_flg == 1){
		$arg_str .= " INNER JOIN m_section";
		$arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
	} else if ($rntl_sect_cd_zero_flg == 0){
		$arg_str .= " INNER JOIN (m_section INNER JOIN m_contract_resource";
		$arg_str .= " ON m_section.corporate_id = m_contract_resource.corporate_id";
		$arg_str .= " AND m_section.rntl_cont_no = m_contract_resource.rntl_cont_no";
		$arg_str .= " AND m_section.rntl_sect_cd = m_contract_resource.rntl_sect_cd)";
		$arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
	}
	$arg_str .= " INNER JOIN m_wearer_std";
	$arg_str .= " ON t_order.werer_cd = m_wearer_std.werer_cd";
	$arg_str .= " INNER JOIN (m_job_type INNER JOIN m_input_item";
	$arg_str .= " ON m_job_type.corporate_id = m_input_item.corporate_id";
	$arg_str .= " AND m_job_type.rntl_cont_no = m_input_item.rntl_cont_no";
	$arg_str .= " AND m_job_type.job_type_cd = m_input_item.job_type_cd)";
	$arg_str .= " ON t_order.corporate_id = m_job_type.corporate_id";
	$arg_str .= " AND t_order.rntl_cont_no = m_job_type.rntl_cont_no";
	$arg_str .= " AND t_order.job_type_cd = m_job_type.job_type_cd";
	$arg_str .= " AND t_order.item_cd = m_input_item.item_cd";
	$arg_str .= " AND t_order.color_cd = m_input_item.color_cd)";
	$arg_str .= " ON t_order_state.corporate_id = t_order.corporate_id";
	$arg_str .= " AND t_order_state.order_req_no = t_order.order_req_no";
	$arg_str .= " AND t_order_state.order_req_line_no = t_order.order_req_line_no)";
	$arg_str .= " ON t_delivery_goods_state.corporate_id = t_order_state.corporate_id";
	$arg_str .= " AND t_delivery_goods_state.rec_order_no = t_order_state.rec_order_no";
	$arg_str .= " AND t_delivery_goods_state.rec_order_line_no = t_order_state.rec_order_line_no)";
	$arg_str .= " ON t_delivery_goods_state_details.corporate_id = t_delivery_goods_state.corporate_id";
	$arg_str .= " AND t_delivery_goods_state_details.ship_no = t_delivery_goods_state.ship_no";
	$arg_str .= " AND t_delivery_goods_state_details.ship_line_no = t_delivery_goods_state.ship_line_no";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= ") as distinct_table";
	if (!empty($q_sort_key)) {
		$arg_str .= " ORDER BY ";
		$arg_str .= $q_sort_key." ".$order;
	}
	//ChromePhp::LOG($arg_str);
	$t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
	$results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
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
			// 受領ステータス
			$list['receipt_status'] = $result->as_receipt_status;
			// 受領日
			$list['receipt_date'] = $result->as_receipt_date;
			// メーカー伝票番号
			if (!empty($result->as_ship_no)) {
				$list['ship_no'] = $result->as_ship_no;
			} else {
				$list['ship_no'] = "-";
			}
			// 商品コード
			$list['item_cd'] = $result->as_item_cd;
			// 色コード
			$list['color_cd'] = $result->as_color_cd;
			// サイズ
			$list['size_cd'] = $result->as_size_cd;
			// サイズ２
			$list['size_two_cd'] = $result->as_size_two_cd;
			// 商品名
			if (!empty($result->as_input_item_name)) {
				$list['input_item_name'] = $result->as_input_item_name;
			} else {
				$list['input_item_name'] = "-";
			}
			// 出荷数
			if (!empty($result->as_ship_qty)) {
				$list['ship_qty'] = $result->as_ship_qty;
			} else {
				$list['ship_qty'] = "-";
			}
			// 個体管理番号
			if (!empty($result->as_individual_ctrl_no)) {
				$list['individual_ctrl_no'] = $result->as_individual_ctrl_no;
			} else {
				$list['individual_ctrl_no'] = "-";
			}
			// 発注No
			if (!empty($result->as_order_req_no)) {
				$list['order_req_no'] = $result->as_order_req_no;
			} else {
				$list['order_req_no'] = "-";
			}
			// 発注行No
			if (!empty($result->as_order_req_line_no)) {
				$list['order_req_line_no'] = $result->as_order_req_line_no;
			} else {
				$list['order_req_line_no'] = "-";
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
			// 発注区分
			$list['order_sts_kbn'] = $result->as_order_sts_kbn;
			// 理由区分
//			$list['order_reason_kbn'] = $result->as_order_reason_kbn;
			// 発注日
			$list['order_req_ymd'] = $result->as_order_req_ymd;
			// 出荷日
			$list['ship_ymd'] = $result->as_ship_ymd;
			// メーカー受注番号
			if (!empty($result->as_rec_order_no)) {
				$list['rec_order_no'] = $result->as_rec_order_no;
			} else {
				$list['rec_order_no'] = "-";
			}
			// 出荷行No
			$list['ship_line_no'] = $result->as_ship_line_no;

			//---日付設定---//
			// 受領日
			if(!empty($list['receipt_date'])){
				$list['receipt_date'] = date('Y/m/d',strtotime($list['receipt_date']));
			}else{
				$list['receipt_date'] = '-';
			}
			// 発注日
			if(!empty($list['order_req_ymd'])){
				$list['order_req_ymd'] = date('Y/m/d',strtotime($list['order_req_ymd']));
			}else{
				$list['order_req_ymd'] = '-';
			}
			// 出荷日
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

			//---受領ステータス名称---//
			$query_list = array();
			// 汎用コードマスタ.分類コード
			array_push($query_list, "cls_cd = '007'");
			// 汎用コードマスタ. レンタル契約No
			array_push($query_list, "gen_cd = '".$list['receipt_status']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $query_list);
			$gencode = MGencode::query()
				->where($query)
				->columns('*')
				->execute();
			foreach ($gencode as $gencode_map) {
				$list['receipt_status_name'] = $gencode_map->gen_name;
			}

			//--受領チェック表示--//
			if ($list['receipt_date'] == "-") {
				$list['chk_disp'] = true;
			} else {
				if (strtotime($list['receipt_date']) >= strtotime(date("Y/m/d", time()))) {
					$list['chk_disp'] = true;
				} else {
					$list['chk_disp'] = false;
				}
			}

			array_push($all_list,$list);
		}
	}

	//ソート設定(配列ソート)
	// 商品-色(サイズ-サイズ2)
	if($sort_key == 'item_code'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'shin_item_code'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'shin_item_code'), SORT_ASC, $all_list);
		}
	}

	// 個体管理番号表示/非表示フラグ設定
	if ($auth["individual_flg"] == 1) {
		$individual_flg = true;
	} else {
		$individual_flg = false;
	}

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $results_cnt;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;
	$json_list['individual_flag'] = $individual_flg;
	echo json_encode($json_list);
});



/**
 * 受領ステータス更新
 */
$app->post('/receive/update', function ()use($app) {
	$data = json_decode(file_get_contents("php://input"), true);

	$cond = $data['cond'];
	//ChromePhp::LOG($cond);
	$page = $data['page'];
	//ChromePhp::LOG($page);

	$json_list = array();

	$json_list["error_code"] = "0";
	$json_list["error_msg"] = array();

	$on_list = array();
	$off_list = array();
	if (!empty($cond)) {
		foreach ($cond as $key) {
			// チェック/未チェック確認、受領ステータス条件リスト生成
			$chk_val = explode(',', $key);
			// チェックされていない場合(未受領)
			if ($chk_val[1] == "1") {
				array_push($off_list, $chk_val[0]);
			}
			// チェックされる場合(受領済み)
			if ($chk_val[1] == "2") {
				array_push($on_list, $chk_val[0]);
			}
		}
	}
	//ChromePhp::LOG($on_list);
	//ChromePhp::LOG($off_list);

	$t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
	$results = new Resultset(NULL, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query('begin'));
	try {
		// 受領ステータス「未受領」更新
		if(!empty($off_list)){
			foreach ($off_list as $off_map) {
				$src_query_list = array();
				$arr = "";
				$arr = explode(':', $off_map);
				$ship_no = $arr[0];
				$ship_no_qy = "ship_no = '".$ship_no."'";
				$src_query_list[] = $ship_no_qy;
				$ship_line_no = $arr[1];
				$ship_line_no_qy = "ship_line_no = ".$ship_line_no;
				$src_query_list[] = $ship_line_no_qy;
				$src_query = implode(' AND ', $src_query_list);

				$up_query_list = array();
				$up_query_list[] = "receipt_status = '1'";
				$up_query_list[] = "receipt_date = NULL";
				$up_query = implode(',', $up_query_list);

				$arg_str = "";
				$arg_str .= "UPDATE t_delivery_goods_state_details SET ";
				$arg_str .= $up_query;
				$arg_str .= " WHERE ";
				$arg_str .= $src_query;
				//ChromePhp::LOG($arg_str);
				$results = new Resultset(NULL, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
			}
		}
		// 受領ステータス「受領済み」更新
		if($on_list){
			foreach ($on_list as $on_map) {
				$src_query_list = array();
				$arr = "";
				$arr = explode(':', $on_map);
				$ship_no = $arr[0];
				$ship_no_qy = "ship_no = '".$ship_no."'";
				$src_query_list[] = $ship_no_qy;
				$ship_line_no = $arr[1];
				$ship_line_no_qy = "ship_line_no = ".$ship_line_no;
				$src_query_list[] = $ship_line_no_qy;
				$src_query = implode(' AND ', $src_query_list);

				$up_query_list = array();
				$up_query_list[] = "receipt_status = '2'";
				$up_query_list[] = "receipt_date = '".date('Y-m-d H:i:s.sss', time())."'";
				$up_query = implode(',', $up_query_list);

				$arg_str = "";
				$arg_str .= "UPDATE t_delivery_goods_state_details SET ";
				$arg_str .= $up_query;
				$arg_str .= " WHERE ";
				$arg_str .= $src_query;
				//ChromePhp::LOG($arg_str);
				$results = new Resultset(NULL, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
			}
		}

		$results = new Resultset(NULL, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query('commit'));
	} catch (Exception $e) {
		$results = new Resultset(NULL, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query('rollback'));

		//ChromePhp::LOG($e);
		$json_list["error_code"] = "1";
		$json_list["error_msg"] = '受領ステータスの更新に失敗しました。';
		echo json_encode($json_list);
		return;
	}

	$page_list['page_number'] = $page['page_number'];
	$json_list['page'] = $page_list;
	echo json_encode($json_list);
});
