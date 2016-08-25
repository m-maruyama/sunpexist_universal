<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 画面区別コード一覧
 * @param csv_code
 *
 * 0001:発注状況照会
 *
 */

/**
 * CSVダウンロード機能
 */
$app->post('/csv_download', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	//---アカウントセッション取得---//
	$auth = $app->session->get("auth");

	//--フロント側パラメータ取得--//
	$cond = $params['cond'];

	//--レスポンス配列生成--//
	$result = array();
	// 処理結果コード　0:成功　1:失敗
	$result["result"] = "0";
	// 画面コード　0000:デフォルト　9999:エラーコード　その他は各照会系画面区別コード参照
	$result["csv_code"] = "0000";

	//--検索条件配列生成--//
	$query_list = array();



	//--発注状況照会CSVダウンロード--//
	if ($cond["ui_type"] === "history") {
		$result["csv_code"] = "0001";

		//---検索条件---//
		//企業ID
		array_push($query_list,"t_order.corporate_id = '".$auth['corporate_id']."'");

		//契約No
		if(!empty($cond['agreement_no'])){
			array_push($query_list,"t_order.rntl_cont_no = '".$cond['agreement_no']."'");
		}
		//発注No
		if(!empty($cond['no'])){
			array_push($query_list,"t_order.order_req_no LIKE '".$cond['no']."%'");
		}
		//お客様発注No
		if(!empty($cond['emply_order'])){
			array_push($query_list,"t_order.emply_req_no LIKE '".$cond['emply_order_no']."%'");
		}
		//社員番号
		if(!empty($cond['member_no'])){
			array_push($query_list,"t_order.cster_emply_cd LIKE '".$cond['member_no']."%'");
		}
		//着用者名
		if(!empty($cond['member_name'])){
			array_push($query_list,"t_order.werer_name LIKE '%".$cond['member_name']."%'");
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
		//発注日from
		if(!empty($cond['order_day_from'])){
			array_push($query_list,"TO_DATE(t_order.order_req_ymd,'YYYY/MM/DD') >= TO_DATE('".$cond['order_day_from']."','YYYY/MM/DD')");
		}
		//発注日to
		if(!empty($cond['order_day_to'])){
			array_push($query_list,"TO_DATE(t_order.order_req_ymd,'YYYY/MM/DD') <= TO_DATE('".$cond['order_day_to']."','YYYY/MM/DD')");
		}
		//出荷日from
		if(!empty($cond['send_day_from'])){
			array_push($query_list,"TO_DATE(t_order_state.ship_ymd,'YYYY/MM/DD') >= TO_DATE('".$cond['send_day_from']."','YYYY/MM/DD')");
		}
		//出荷日to
		if(!empty($cond['send_day_to'])){
			array_push($query_list,"TO_DATE(t_order_state.ship_ymd,'YYYY/MM/DD') <= TO_DATE('".$cond['send_day_to']."','YYYY/MM/DD')");
		}
		//個体管理番号
		if(!empty($cond['individual_number'])){
			array_push($query_list,"t_delivery_goods_state_details.individual_ctrl_no LIKE '".$cond['individual_number']."%'");
		}

		$status_kbn_list = array();

		//ステータス
		$status_list = array();
		if($cond['status0']){
			// 未出荷
			array_push($status_list,"1");
		}
		if($cond['status1']){
			// 出荷済み
			array_push($status_list,"2");
		}
		if(!empty($status_list)) {
			$status_str = implode("','",$status_list);
	//		$status_query = "order_status IN ('".$status_str."')";
			array_push($query_list,"order_status IN ('".$status_str."')");
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
			$order_kbn_query = "order_sts_kbn IN ('".$order_kbn_str."')";
	//		array_push($query_list,"order_sts_kbn IN ('".$order_kbn_str."')");
			array_push($status_kbn_list,$order_kbn_query);
		}
		// 理由区分
		$reason_kbn = array();
		if($cond['reason_kbn0']){
			array_push($reason_kbn,'1');
		}
		if($cond['reason_kbn1']){
			array_push($reason_kbn,'2');
		}
		if($cond['reason_kbn2']){
			array_push($reason_kbn,'3');
		}
		if($cond['reason_kbn3']){
			array_push($reason_kbn,'4');
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
			array_push($reason_kbn,'9');
		}
		if($cond['reason_kbn13']){
			array_push($reason_kbn,'10');
		}
		if($cond['reason_kbn14']){
			array_push($reason_kbn,'11');
		}
		if($cond['reason_kbn15']){
			array_push($reason_kbn,'5');
		}
		if($cond['reason_kbn16']){
			array_push($reason_kbn,'6');
		}
		if($cond['reason_kbn17']){
			array_push($reason_kbn,'7');
		}
		if($cond['reason_kbn18']){
			array_push($reason_kbn,'8');
		}
		if($cond['reason_kbn19']){
			array_push($reason_kbn,'24');
		}
		if(!empty($reason_kbn)){
			$reason_kbn_str = implode("','",$reason_kbn);
			$reason_kbn_query = "order_sts_kbn IN ('".$reason_kbn_str."')";
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

/*
		$sort_key ='';
		$order ='';
		//ソートキー
		if(isset($page['sort_key'])){
			$sort_key = $page['sort_key'];
			if($sort_key == 'job_type_cd'){
				$sort_key = 't_order.'.$sort_key;
			}else{
				$sort_key = 't_order.'.$sort_key;
			}
			if($sort_key == 'cster_emply_cd'){
				$sort_key = 'cster_emply_cd';
			}
			if($sort_key == 'order_req_no' || $sort_key == 'order_req_ymd' || $sort_key == 'order_status' || $sort_key == 'order_sts_kbn'){
				$sort_key = 't_order.'.$sort_key;
			}
			if($sort_key == 'ship_ymd'){
				$sort_key = 't_order_state'.$sort_key;
			}
			if($sort_key == 'rntl_sect_name'){
				$sort_key = 't_order.'.$sort_key;
			}
			$order = $page['order'];
		} else {
			//指定がなければ発注No
			$sort_key = "t_order.order_req_no";
			$order = 'asc';
		}
*/

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
		$arg_str .= " (t_order_state LEFT JOIN (t_delivery_goods_state LEFT JOIN t_delivery_goods_state_details ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no)";
		$arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";
		$arg_str .= " ON t_order.t_order_comb_hkey = t_order_state.t_order_comb_hkey";
		$arg_str .= " INNER JOIN m_section";
		$arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
		$arg_str .= " INNER JOIN (m_job_type INNER JOIN m_input_item ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey)";
		$arg_str .= " ON t_order.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
		$arg_str .= " INNER JOIN m_contract";
		$arg_str .= " ON t_order.rntl_cont_no = m_contract.rntl_cont_no";

		$arg_str .= " WHERE ";
		$arg_str .= $query;

		$arg_str .= " ORDER BY ";
		$arg_str .= "t_order.order_req_no ASC";

		$t_order = new TOrder();
		$t_order_list = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
		// 取得オブジェクトを配列化→クラス内propety：protected値を取得する
		$t_order_list_array = (array)$t_order_list;
		$t_order_list = $t_order_list_array["\0*\0_rows"];

		$list = array();
		$all_list = array();
		$json_list = array();

		if(!empty($t_order_list)){
			foreach($t_order_list as $t_order_map){
				if(!isset($t_order_map)){
					break;
				}
				$list['order_req_no'] = $t_order_map["as_order_req_no"];
				$list['order_req_ymd'] = $t_order_map["as_order_req_ymd"];
				$list['order_sts_kbn'] = $t_order_map["as_order_sts_kbn"];
				$list['order_reason_kbn'] = $t_order_map["as_order_reason_kbn"];
				$list['rntl_sect_name'] = $t_order_map["as_rntl_sect_name"];
				$list['job_type_name'] = $t_order_map["as_job_type_name"];
				$list['cster_emply_cd'] = $t_order_map["as_cster_emply_cd"];
				$list['werer_name'] = $t_order_map["as_werer_name"];
				$list['item_cd'] = $t_order_map["as_item_cd"];
				$list['color_cd'] = $t_order_map["as_color_cd"];
				$list['size_cd'] = $t_order_map["as_size_cd"];
				$list['size_two_cd'] = $t_order_map["as_size_two_cd"];
				$list['input_item_name'] = $t_order_map["as_input_item_name"];
				$list['order_qty'] = $t_order_map["as_order_qty"];
				$list['rec_order_no'] = $t_order_map["as_rec_order_no"];
				$list['order_status'] = $t_order_map["as_order_status"];
				$list['ship_no'] = $t_order_map["as_ship_no"];
				$list['ship_ymd'] = $t_order_map["as_ship_ymd"];
				$list['ship_qty'] = $t_order_map["as_ship_qty"];
				$list['rntl_cont_no'] = $t_order_map["as_rntl_cont_no"];
				$list['rntl_cont_name'] = $t_order_map["as_rntl_cont_name"];

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

				array_push($all_list,$list);
			}
		} else {
			$result["result"] = "1";
			$result["csv_code"] = "9999";
		}
	}

/*
	// CSV出力
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=delivery_".$now = date('YmdHis').".csv");
	$fp = fopen('php://output','w');
	$header_1 = array(
		'発注No',
		'発注区分',
		'拠点',
		'社員番号',
		'商品-色(サイズ-サイズ2)',
		'発注数',
		'メーカー受注番号',
		'受注数',
		'ステータス',
		'メーカー伝票番号',
		'出荷数',
		'個体管理番号',
		'受領日',
		'契約No',
	);
	$header_2 = array(
		'発注日',
		'',
		'貸与パターン',
		'',
		'着用者名',
		'商品名',
		'',
		'出荷予定日',
		'',
		'',
		'出荷日',
		'',
		'',
		'',
		'契約名',
	);
	_fputcsv($fp,$header);
	foreach ($results as $result) {
		$list = array();
		if(!isset($result)){
			break;
		}
		$list['order_req_no'] = $result->tOrder->order_req_no;
		$list['order_req_line_no'] = $result->tOrder->order_req_line_no;
		$list['cster_emply_cd'] = $result->tOrder->cster_emply_cd;
		$list['rntl_sect_name'] = $result->mSection->rntl_sect_name;
		$list['job_type_name'] = $result->mJobType->job_type_name;
		// $list['order_req_ymd'] = $result->TOrderState->TOrder->order_req_ymd;

		if($result->tOrder->order_req_ymd){
			$list['order_req_ymd'] =  date('Y/m/d',strtotime($result->tOrder->order_req_ymd));
		}else{
			$list['order_req_ymd'] = '-';
		}

		$list['statusText'] = statusText($result->tOrder->order_status,$result->tDeliveryGoodsState->receipt_status);
		$list['kubunText'] = kubunText($result->tOrder->order_sts_kbn);
		//受領ステータス
		// if(!in_array($result->receipt_status, $receipt_status)){
			// array_push($receipt_status,$result->receipt_status);
		// }
		// $list['receipt_status'] = $result->receipt_status;
		// $list['order_sts_kbn'] = $result->TOrderState->TOrder->order_sts_kbn;
		//納品状況情報．出荷日
		if($result->tDeliveryGoodsState->ship_ymd){
			$list['ship_ymd'] =  date('Y/m/d',strtotime($result->tDeliveryGoodsState->ship_ymd));
		}else{
			$list['ship_ymd'] = '-';
		}

		$list['rec_order_no'] = $result->tDeliveryGoodsState->rec_order_no;//納品状況情報．受注No.
		$list['ship_no'] = $result->tDeliveryGoodsState->ship_no;//納品状況情報．配送伝票No.
		$list['item_name'] = $result->mItem->item_name;//商品マスタ．商品名（漢字）
		$list['item_cd'] = $result->tOrderState->item_cd;//発注状況情報．商品コード
		$list['color_cd'] = $result->tOrderState->color_cd;//発注状況情報．色コード
		$list['size_cd'] = $result->tOrderState->size_cd;//発注状況情報．サイズコード
		$list['ship_qty'] = $result->tDeliveryGoodsState->ship_qty;//出荷数
		//受領数
		if($result->tDeliveryGoodsState->receipt_status == 2){
			$list['receipt_num'] = $result->tDeliveryGoodsState->ship_qty;
		} else {
			$list['receipt_num'] = '0';
		}
		_fputcsv($fp,$list);
	}
	fclose($fp);
*/
	echo json_encode($result);
	return;
});
