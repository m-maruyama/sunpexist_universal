<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 画面区別コード一覧
 *
 * @param csv_code
 *
 * 0001:発注状況照会
 * 0002:返却状況照会
 * 0003:受領確認照会
 * 0004:貸与リスト
 *
 */

/**
  * エラーコード一覧
  *
  * @param err_code
  *
  * 0000:正常
	* 9001:検索結果０件
	* 9002:CSVダウンロード処理エラー
	*
  */

/**
 * CSVダウンロード機能
 */
$app->post('/csv_download', function ()use($app){

//	$params = json_decode(file_get_contents("php://input"), true);
	$params = json_decode($_POST['data'], true);

	//---アカウントセッション取得---//
	$auth = $app->session->get("auth");

	//--フロント側パラメータ取得--//
	$cond = $params['cond'];

	//--レスポンス配列生成--//
	$result = array();
	// 処理結果コード　0:成功　1:失敗
	$result["result"] = "0";
	// 画面コード　上記画面区別コード参照
	$result["csv_code"] = "0000";
	// エラーコード　上記エラーコード参照
	$result["err_code"] = "0000";
	//--検索条件配列生成--//
	$query_list = array();



	//--発注状況照会CSVダウンロード--//
	if ($cond["ui_type"] === "history") {
		$result["csv_code"] = "0001";

		//---発注状況検索処理---//
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
			$cond['send_day_from'] = date('Y/m/d 00:00:00', strtotime($cond['send_day_from']));
			array_push($query_list,"t_order_state.ship_ymd >= '".$cond['send_day_from']."'");
//		array_push($query_list,"TO_DATE(t_order_state.ship_ymd,'YYYY/MM/DD') >= TO_DATE('".$cond['send_day_from']."','YYYY/MM/DD')");
		}
		//出荷日to
		if(!empty($cond['send_day_to'])){
			$cond['send_day_to'] = date('Y/m/d 23:59:59', strtotime($cond['send_day_to']));
			array_push($query_list,"t_order_state.ship_ymd <= '".$cond['send_day_to']."'");
//		array_push($query_list,"TO_DATE(t_order_state.ship_ymd,'YYYY/MM/DD') <= TO_DATE('".$cond['send_day_to']."','YYYY/MM/DD')");
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

		if(!empty($page['sort_key'])){
			$sort_key = $page['sort_key'];
			$order = $page['order'];
			if($sort_key == 'order_req_no' || $sort_key == 'order_req_ymd' || $sort_key == 'order_status' || $sort_key == 'order_sts_kbn'){
				$sort_key = 'as_'.$sort_key;
			}
			if($sort_key == 'job_type_cd'){
				$sort_key = 'as_job_type_name';
			}
			if($sort_key == 'cster_emply_cd'){
				$sort_key = 'as_cster_emply_cd';
			}
			if($sort_key == 'rntl_sect_name'){
				$sort_key = 'as_rntl_sect_name';
			}
			if($sort_key == 'werer_name'){
				$sort_key = 'as_werer_name';
			}
			if($sort_key == 'item_code'){
				$sort_key = 'as_item_cd';
			}
			if($sort_key == 'item_code'){
				$sort_key = 'as_item_cd';
			}
			if($sort_key == 'item_name'){
				$sort_key = 'as_input_item_name';
			}
			if($sort_key == 'maker_rec_no'){
				$sort_key = 'as_rec_order_no';
			}
			if($sort_key == 'send_shd_ymd'){
				$sort_key = 'as_order_req_ymd';
			}
			if($sort_key == 'order_status'){
				$sort_key = 'as_order_status';
			}
			if($sort_key == 'maker_send_no'){
				$sort_key = 'as_ship_no';
			}
			if($sort_key == 'ship_ymd'){
				$sort_key = 'as_ship_ymd';
			}
			if($sort_key == 'send_ymd'){
				$sort_key = 'as_ship_ymd';
			}
			if($sort_key == 'individual_num'){
				$sort_key = 'as_individual_ctrl_no';
			}
			if($sort_key == 'order_res_ymd'){
				$sort_key = 'as_receipt_date';
			}
			if($sort_key == 'rental_no'){
				$sort_key = 'as_rntl_cont_no';
			}
			if($sort_key == 'rental_name'){
				$sort_key = 'as_rntl_cont_name';
			}
		} else {
			//指定がなければ発注No
			$sort_key = "as_order_req_no";
			$order = 'asc';
		}

		// SQLクエリー実行
		$arg_str = "SELECT ";
		$arg_str .= " * ";
		$arg_str .= " FROM ";
		$arg_str .= "(SELECT distinct on (t_order.order_req_no, t_order.order_req_line_no) ";
		$arg_str .= "t_order.order_req_no as as_order_req_no,";
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
		$arg_str .= "t_delivery_goods_state_details.individual_ctrl_no as as_individual_ctrl_no,";
		$arg_str .= "t_delivery_goods_state_details.receipt_date as as_receipt_date,";
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
		$arg_str .= ") as distinct_table";
		$arg_str .= " ORDER BY ";
		$arg_str .= $sort_key." ".$order;

		$t_order = new TOrder();
		$t_order_list = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
		// 取得オブジェクトを配列化→クラス内propety：protected値を取得する
		$t_order_list_array = (array)$t_order_list;
		$t_order_list = $t_order_list_array["\0*\0_rows"];
		$t_order_list_cnt = $t_order_list_array["\0*\0_count"];

		$list = array();
		$all_list = array();
		$json_list = array();

		if(!empty($t_order_list)){
			foreach($t_order_list as $t_order_map){
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
					$individual_ctrl_no = implode(PHP_EOL, $num_list);
					$list['individual_num'] = $individual_ctrl_no;
					// 受領日
					$receipt_date = implode(PHP_EOL, $day_list);
					$list['order_res_ymd'] = $receipt_date;
				} else {
					$list['individual_num'] = "-";
					$list['order_res_ymd'] = "-";
				}

				array_push($all_list,$list);
			}
		}

		// 個体管理番号表示/非表示フラグ設定
		if ($auth["individual_flg"] == 1) {
			$individual_flg = true;
		} else {
			$individual_flg = false;
		}
/*
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

		//---CSV出力---//
		$csv_datas = array();

		// ヘッダー作成
		$header_1 = array(
			'抽出件数：'.$t_order_list_cnt.'件'
		);
		array_push($csv_datas, $header_1);

		$header_2 = array();
		array_push($header_2, "発注No");
		array_push($header_2, "発注日");
		array_push($header_2, "発注区分");
		array_push($header_2, "拠点");
		array_push($header_2, "貸与パターン");
		array_push($header_2, "社員番号");
		array_push($header_2, "着用者名");
		array_push($header_2, "商品-色(サイズ-サイズ2)");
		array_push($header_2, "商品名");
		array_push($header_2, "発注数");
		array_push($header_2, "メーカー受注番号");
		array_push($header_2, "出荷予定日");
		array_push($header_2, "受注数");
		array_push($header_2, "ステータス");
		array_push($header_2, "メーカー伝票番号");
		array_push($header_2, "出荷日");
		array_push($header_2, "出荷数");
		if ($individual_flg) {
			array_push($header_2, "個体管理番号");
		}
		array_push($header_2, "受領日");
		array_push($header_2, "契約No");
		array_push($header_2, "契約名");
		array_push($csv_datas, $header_2);

		// ボディ作成
		if (!empty($all_list)) {
			foreach ($all_list as $all_map) {
				$csv_body_list = array();
				// 発注No
				array_push($csv_body_list, $all_map["order_req_no"]);
				// 発注日
				array_push($csv_body_list, $all_map["order_req_ymd"]);
				// 発注区分
				$str = $all_map["order_sts_name"]."(".$all_map["order_reason_name"].")";
				array_push($csv_body_list, $str);
				// 拠点
				array_push($csv_body_list, $all_map["rntl_sect_name"]);
				// 貸与パターン
				array_push($csv_body_list, $all_map["job_type_name"]);
				// 社員番号
				array_push($csv_body_list, $all_map["cster_emply_cd"]);
				// 着用者名
				array_push($csv_body_list, $all_map["werer_name"]);
				// 商品-色(サイズ-サイズ2)
				array_push($csv_body_list, $all_map["shin_item_code"]);
				// 商品-色(サイズ-サイズ2)・商品名
				array_push($csv_body_list, $all_map["input_item_name"]);
				// 発注数
				array_push($csv_body_list, $all_map["order_qty"]);
				// メーカー受注番号
				array_push($csv_body_list, $all_map["rec_order_no"]);
				// 出荷予定日
				array_push($csv_body_list, $all_map["send_shd_ymd"]);
				// 受注数
				array_push($csv_body_list, $all_map["order_qty"]);
				// ステータス
				array_push($csv_body_list, $all_map["order_status_name"]);
				// メーカー伝票番号
				array_push($csv_body_list, $all_map["ship_no"]);
				// 出荷日
				array_push($csv_body_list, $all_map["ship_ymd"]);
				// 出荷数
				array_push($csv_body_list, $all_map["ship_qty"]);
				// 個体管理番号
				if ($individual_flg) {
					array_push($csv_body_list, $all_map["individual_num"]);
				}
				// 受領日
				array_push($csv_body_list, $all_map["order_res_ymd"]);
				// 契約No
				array_push($csv_body_list, $all_map["rntl_cont_no"]);
				// 契約No・契約名
				array_push($csv_body_list, $all_map["rntl_cont_name"]);

				// CSVレコード配列にマージ
				array_push($csv_datas, $csv_body_list);
			}
		}

		// CSVデータ書き込み
		$file_name = "history_".date("YmdHis", time()).".csv";
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".$file_name);

		$fp = fopen('php://output','w');
		foreach ($csv_datas as $csv_data) {
			mb_convert_variables("SJIS-win", "UTF-8", $csv_data);
			fputcsv($fp, $csv_data);
		}

		fclose($fp);
	}
	//--発注状況照会CSVダウンロード ここまで--//



	//--返却状況照会CSVダウンロード--//
	if ($cond["ui_type"] === "unreturn") {
		$result["csv_code"] = "0002";

		//---返却状況検索処理---//
		//企業ID
		array_push($query_list,"t_returned_plan_info.corporate_id = '".$auth['corporate_id']."'");
		//契約No
		if(!empty($cond['agreement_no'])){
			array_push($query_list,"t_returned_plan_info.rntl_cont_no = '".$cond['agreement_no']."'");
		}
		//発注No
		if(!empty($cond['no'])){
			array_push($query_list,"t_returned_plan_info.order_req_no LIKE '".$cond['no']."%'");
		}
		//お客様発注No
		if(!empty($cond['emply_order'])){
			array_push($query_list,"t_returned_plan_info.emply_req_no LIKE '".$cond['emply_order_no']."%'");
		}
		//社員番号
		if(!empty($cond['member_no'])){
			array_push($query_list,"t_returned_plan_info.cster_emply_cd LIKE '".$cond['member_no']."%'");
		}
		//着用者名
		if(!empty($cond['member_name'])){
			array_push($query_list,"t_order.werer_name LIKE '%".$cond['member_name']."%'");
		}
		//拠点
		if(!empty($cond['section'])){
			array_push($query_list,"t_returned_plan_info.rntl_sect_cd = '".$cond['section']."'");
		}
		//貸与パターン
		if(!empty($cond['job_type'])){
			array_push($query_list,"t_returned_plan_info.rent_pattern_code = '".$cond['job_type']."'");
		}
		//商品
		if(!empty($cond['input_item'])){
			array_push($query_list,"t_returned_plan_info.item_cd = '".$cond['input_item']."'");
		}
		//色
		if(!empty($cond['item_color'])){
			array_push($query_list,"t_returned_plan_info.color_cd = '".$cond['item_color']."'");
		}
		//サイズ
		if(!empty($cond['item_size'])){
			array_push($query_list,"t_returned_plan_info.size_cd = '".$cond['item_size']."'");
		}
		//発注日from
		if(!empty($cond['order_day_from'])){
			array_push($query_list,"TO_DATE(t_order.order_req_ymd,'YYYY/MM/DD') >= TO_DATE('".$cond['order_day_from']."','YYYY/MM/DD')");
		}
		//発注日to
		if(!empty($cond['order_day_to'])){
			array_push($query_list,"TO_DATE(t_order.order_req_ymd,'YYYY/MM/DD') <= TO_DATE('".$cond['order_day_to']."','YYYY/MM/DD')");
		}
		//返却日from
		if(!empty($cond['return_day_from'])){
			$cond['return_day_from'] = date('Y/m/d 00:00:00', strtotime($cond['return_day_from']));
			array_push($query_list,"t_returned_plan_info.return_date >= '".$cond['return_day_from']."'");
	//		array_push($query_list,"TO_DATE(t_returned_results.return_date,'YYYY/MM/DD') >= TO_DATE('".$cond['return_day_from']."','YYYY/MM/DD')");
		}
		//返却日to
		if(!empty($cond['return_day_to'])){
			$cond['return_day_to'] = date('Y/m/d 23:59:59', strtotime($cond['return_day_to']));
			array_push($query_list,"t_returned_plan_info.return_date <= '".$cond['return_day_to']."'");
	//		array_push($query_list,"TO_DATE(t_returned_results.return_date,'YYYY/MM/DD') <= TO_DATE('".$cond['return_day_to']."','YYYY/MM/DD')");
		}
		//個体管理番号
		if(!empty($cond['individual_number'])){
			array_push($query_list,"t_returned_plan_info.individual_ctrl_no LIKE '".$cond['individual_number']."%'");
		}

		$status_kbn_list = array();

		//ステータス
		$status_list = array();
		if($cond['status0']){
			// 未返却
			array_push($status_list,"1");
		}
		if($cond['status1']){
			// 返却済み
			array_push($status_list,"2");
		}
		if(!empty($status_list)) {
			$status_str = implode("','",$status_list);
	//		$status_query = "order_status IN ('".$status_str."')";
			array_push($query_list,"t_returned_plan_info.return_status IN ('".$status_str."')");
	//		array_push($status_kbn_list,$status_query);
		}
		//発注区分
		$order_kbn = array();
		if($cond['order_kbn0']){
			array_push($order_kbn,'3');
		}
		if($cond['order_kbn1']){
			array_push($order_kbn,'5');
		}
		if($cond['order_kbn2']){
			array_push($order_kbn,'2');
		}
		if($cond['order_kbn3']){
			array_push($order_kbn,'9');
		}
		if(!empty($order_kbn)){
			$order_kbn_str = implode("','",$order_kbn);
			$order_kbn_query = "t_returned_plan_info.order_sts_kbn IN ('".$order_kbn_str."')";
	//		array_push($query_list,"order_sts_kbn IN ('".$order_kbn_str."')");
			array_push($status_kbn_list,$order_kbn_query);
		}
		// 理由区分
		$reason_kbn = array();
		if($cond['reason_kbn0']){
			array_push($reason_kbn,'14');
		}
		if($cond['reason_kbn1']){
			array_push($reason_kbn,'15');
		}
		if($cond['reason_kbn2']){
			array_push($reason_kbn,'16');
		}
		if($cond['reason_kbn3']){
			array_push($reason_kbn,'17');
		}
		if($cond['reason_kbn4']){
			array_push($reason_kbn,'21');
		}
		if($cond['reason_kbn5']){
			array_push($reason_kbn,'22');
		}
		if($cond['reason_kbn6']){
			array_push($reason_kbn,'23');
		}
		if($cond['reason_kbn7']){
			array_push($reason_kbn,'9');
		}
		if($cond['reason_kbn8']){
			array_push($reason_kbn,'10');
		}
		if($cond['reason_kbn9']){
			array_push($reason_kbn,'11');
		}
		if($cond['reason_kbn10']){
			array_push($reason_kbn,'5');
		}
		if($cond['reason_kbn11']){
			array_push($reason_kbn,'6');
		}
		if($cond['reason_kbn12']){
			array_push($reason_kbn,'7');
		}
		if($cond['reason_kbn13']){
			array_push($reason_kbn,'8');
		}
		if($cond['reason_kbn14']){
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
			if($sort_key == 'order_req_no' || $sort_key == 'order_req_ymd' || $sort_key == 'return_status' || $sort_key == 'order_sts_kbn'){
				$sort_key = 'as_'.$sort_key;
			}
			if($sort_key == 'job_type_cd'){
				$sort_key = 'as_job_type_name';
			}
			if($sort_key == 'cster_emply_cd'){
				$sort_key = 'as_cster_emply_cd';
			}
			if($sort_key == 'rntl_sect_name'){
				$sort_key = 'as_rntl_sect_name';
			}
			if($sort_key == 'werer_name'){
				$sort_key = 'as_werer_name';
			}
			if($sort_key == 'item_code'){
				$sort_key = 'as_item_cd';
			}
			if($sort_key == 'item_name'){
				$sort_key = 'as_input_item_name';
			}
			if($sort_key == 'maker_rec_no'){
				$sort_key = 'as_rec_order_no';
			}
			if($sort_key == 'return_shd_ymd'){
				$sort_key = 'as_re_order_date';
			}
			if($sort_key == 'maker_send_no'){
				$sort_key = 'as_ship_no';
			}
			if($sort_key == 'ship_ymd'){
				$sort_key = 'as_ship_ymd';
			}
			if($sort_key == 'send_ymd'){
				$sort_key = 'as_ship_ymd';
			}
			if($sort_key == 'individual_num'){
				$sort_key = 'as_individual_ctrl_no';
			}
			if($sort_key == 'order_res_ymd'){
				$sort_key = 'as_receipt_date';
			}
			if($sort_key == 'rental_no'){
				$sort_key = 'as_rntl_cont_no';
			}
			if($sort_key == 'rental_name'){
				$sort_key = 'as_rntl_cont_name';
			}
		} else {
			//指定がなければ発注No
			$sort_key = "as_order_req_no";
			$order = 'asc';
		}

		// SQLクエリー実行
		$arg_str = "SELECT ";
		$arg_str .= " * ";
		$arg_str .= " FROM ";
		$arg_str .= "(SELECT distinct on (t_returned_plan_info.order_req_no, t_returned_plan_info.order_req_line_no) ";
		$arg_str .= "t_returned_plan_info.order_req_no as as_order_req_no,";
		$arg_str .= "t_order.order_req_ymd as as_order_req_ymd,";
		$arg_str .= "t_returned_plan_info.order_sts_kbn as as_order_sts_kbn,";
		$arg_str .= "t_order.order_reason_kbn as as_order_reason_kbn,";
		$arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
		$arg_str .= "m_job_type.job_type_name as as_job_type_name,";
		$arg_str .= "t_returned_plan_info.cster_emply_cd as as_cster_emply_cd,";
		$arg_str .= "t_order.werer_name as as_werer_name,";
		$arg_str .= "t_returned_plan_info.item_cd as as_item_cd,";
		$arg_str .= "t_returned_plan_info.color_cd as as_color_cd,";
		$arg_str .= "t_returned_plan_info.size_cd as as_size_cd,";
		$arg_str .= "t_order.size_two_cd as as_size_two_cd,";
		$arg_str .= "m_input_item.input_item_name as as_input_item_name,";
		$arg_str .= "t_order.order_qty as as_order_qty,";
		$arg_str .= "t_returned_plan_info.order_date as as_re_order_date,";
		$arg_str .= "t_returned_plan_info.return_status as as_return_status,";
		$arg_str .= "t_returned_plan_info.return_date as as_return_date,";
		$arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no,";
		$arg_str .= "t_delivery_goods_state.ship_no as as_ship_no,";
		$arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
		$arg_str .= "t_delivery_goods_state.ship_qty as as_ship_qty,";
		$arg_str .= "t_delivery_goods_state.return_qty as as_return_qty,";
		$arg_str .= "t_delivery_goods_state_details.individual_ctrl_no as as_individual_ctrl_no,";
		$arg_str .= "t_delivery_goods_state_details.receipt_date as as_receipt_date,";
		$arg_str .= "t_returned_plan_info.rntl_cont_no as as_rntl_cont_no,";
		$arg_str .= "m_contract.rntl_cont_name as as_rntl_cont_name";
		$arg_str .= " FROM t_order LEFT JOIN";
		$arg_str .= " (t_returned_plan_info LEFT JOIN";
		$arg_str .= " (t_order_state LEFT JOIN ";
		$arg_str .= " (t_delivery_goods_state LEFT JOIN t_delivery_goods_state_details ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no)";
		$arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";
		$arg_str .= " ON t_returned_plan_info.order_req_no = t_order_state.order_req_no)";
		$arg_str .= " ON t_order.order_req_no = t_returned_plan_info.order_req_no";
		$arg_str .= " INNER JOIN m_section";
		$arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
		$arg_str .= " INNER JOIN (m_job_type INNER JOIN m_input_item ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey)";
		$arg_str .= " ON t_order.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
		$arg_str .= " INNER JOIN m_contract";
		$arg_str .= " ON t_order.rntl_cont_no = m_contract.rntl_cont_no";
		$arg_str .= " WHERE ";
		$arg_str .= $query;
		$arg_str .= ") as distinct_table";
		$arg_str .= " ORDER BY ";
		$arg_str .= $sort_key." ".$order;

		$t_order = new TOrder();
		$results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
		// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
		$results_array = (array)$results;
		$results = $results_array["\0*\0_rows"];
		$results_cnt = $results_array["\0*\0_count"];

		$list = array();
		$all_list = array();
		$json_list = array();

		if(!empty($results)){
			foreach($results as $result){
				$list['order_req_no'] = $result["as_order_req_no"];
				$list['order_req_ymd'] = $result["as_order_req_ymd"];
				$list['order_sts_kbn'] = $result["as_order_sts_kbn"];
				$list['order_reason_kbn'] = $result["as_order_reason_kbn"];
				$list['rntl_sect_name'] = $result["as_rntl_sect_name"];
				$list['job_type_name'] = $result["as_job_type_name"];
				$list['cster_emply_cd'] = $result["as_cster_emply_cd"];
				$list['werer_name'] = $result["as_werer_name"];
				$list['item_cd'] = $result["as_item_cd"];
				$list['color_cd'] = $result["as_color_cd"];
				$list['size_cd'] = $result["as_size_cd"];
				$list['size_two_cd'] = $result["as_size_two_cd"];
				$list['input_item_name'] = $result["as_input_item_name"];
				$list['order_qty'] = $result["as_order_qty"];
				$list['rec_order_no'] = $result["as_rec_order_no"];
				$list['re_order_date'] = $result["as_re_order_date"];
				$list['return_status'] = $result["as_return_status"];
				$list['ship_no'] = $result["as_ship_no"];
				$list['ship_ymd'] = $result["as_ship_ymd"];
				$list['return_qty'] = $result["as_return_qty"];
				$list['ship_qty'] = $result["as_ship_qty"];
				$list['rntl_cont_no'] = $result["as_rntl_cont_no"];
				$list['rntl_cont_name'] = $result["as_rntl_cont_name"];

				//---日付設定---//
				// 発注依頼日
				if(!empty($list['order_req_ymd'])){
					$list['order_req_ymd'] = date('Y/m/d',strtotime($list['order_req_ymd']));
				}else{
					$list['order_req_ymd'] = '-';
				}
				// 依頼日（返却予定日）
				if(!empty($list['re_order_date'])){
					$list['re_order_date'] =  date('Y/m/d',strtotime($list['re_order_date']));
				}else{
					$list['re_order_date'] = '-';
				}
				// 出荷日
				if(!empty($list['ship_ymd'])){
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

				//---返却ステータス名称---//
				$query_list = array();
				// 汎用コードマスタ.分類コード
				array_push($query_list, "cls_cd = '008'");
				// 汎用コードマスタ. レンタル契約No
				array_push($query_list, "gen_cd = '".$list['return_status']."'");
				//sql文字列を' AND 'で結合
				$query = implode(' AND ', $query_list);
				$gencode = MGencode::query()
					->where($query)
					->columns('*')
					->execute();
				foreach ($gencode as $gencode_map) {
					$list['return_status_name'] = $gencode_map->gen_name;
				}

				//---返却ステータス名称---//
				$query_list = array();
				// 汎用コードマスタ.分類コード
				array_push($query_list, "cls_cd = '008'");
				// 汎用コードマスタ. レンタル契約No
				array_push($query_list, "gen_cd = '".$list['return_status']."'");
				//sql文字列を' AND 'で結合
				$query = implode(' AND ', $query_list);
				$gencode = MGencode::query()
					->where($query)
					->columns('*')
					->execute();
				foreach ($gencode as $gencode_map) {
					$list['return_status_name'] = $gencode_map->gen_name;
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
					$individual_ctrl_no = implode(PHP_EOL, $num_list);
					$list['individual_num'] = $individual_ctrl_no;
					// 受領日
					$receipt_date = implode(PHP_EOL, $day_list);
					$list['order_res_ymd'] = $receipt_date;
				} else {
					$list['individual_num'] = "-";
					$list['order_res_ymd'] = "-";
				}

				array_push($all_list,$list);
			}
		}

		// 個体管理番号表示/非表示フラグ設定
		if ($auth["individual_flg"] == 1) {
			$individual_flg = true;
		} else {
			$individual_flg = false;
		}
/*
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

		//---CSV出力---//
		$csv_datas = array();

		// ヘッダー作成
		$header_1 = array(
			'抽出件数：'.$results_cnt.'件'
		);
		array_push($csv_datas, $header_1);

		$header_2 = array();
		array_push($header_2, "発注No");
		array_push($header_2, "発注日");
		array_push($header_2, "発注区分");
		array_push($header_2, "拠点");
		array_push($header_2, "貸与パターン");
		array_push($header_2, "社員番号");
		array_push($header_2, "着用者名");
		array_push($header_2, "商品-色(サイズ-サイズ2)");
		array_push($header_2, "商品名");
		array_push($header_2, "発注数");
		array_push($header_2, "メーカー受注番号");
		array_push($header_2, "返却予定日");
		array_push($header_2, "返却数");
		array_push($header_2, "ステータス");
		array_push($header_2, "メーカー伝票番号");
		array_push($header_2, "出荷日");
		array_push($header_2, "出荷数");
		if ($individual_flg) {
			array_push($header_2, "個体管理番号");
		}
		array_push($header_2, "受領日");
		array_push($header_2, "契約No");
		array_push($header_2, "契約名");
		array_push($csv_datas, $header_2);

		// ボディ作成
		if (!empty($all_list)) {
			foreach ($all_list as $all_map) {
				$csv_body_list = array();
				// 発注No
				array_push($csv_body_list, $all_map["order_req_no"]);
				// 発注日
				array_push($csv_body_list, $all_map["order_req_ymd"]);
				// 発注区分
				$str = $all_map["order_sts_name"].PHP_EOL."(".$all_map["order_reason_name"].")";
				array_push($csv_body_list, $str);
				// 拠点
				array_push($csv_body_list, $all_map["rntl_sect_name"]);
				// 貸与パターン
				array_push($csv_body_list, $all_map["job_type_name"]);
				// 社員番号
				array_push($csv_body_list, $all_map["cster_emply_cd"]);
				// 着用者名
				array_push($csv_body_list, $all_map["werer_name"]);
				// 商品-色(サイズ-サイズ2)
				array_push($csv_body_list, $all_map["shin_item_code"]);
				// 商品名
				array_push($csv_body_list, $all_map["input_item_name"]);
				// 発注数
				array_push($csv_body_list, $all_map["order_qty"]);
				// メーカー受注番号
				array_push($csv_body_list, $all_map["rec_order_no"]);
				// 返却予定日
				array_push($csv_body_list, $all_map["re_order_date"]);
				// 返却数
				array_push($csv_body_list, $all_map["return_qty"]);
				// ステータス
				array_push($csv_body_list, $all_map["return_status_name"]);
				// メーカー伝票番号
				array_push($csv_body_list, $all_map["ship_no"]);
				// 出荷日
				array_push($csv_body_list, $all_map["ship_ymd"]);
				// 出荷数
				array_push($csv_body_list, $all_map["ship_qty"]);
				// 個体管理番号
				if ($individual_flg) {
					array_push($csv_body_list, $all_map["individual_num"]);
				}
				// 受領日
				array_push($csv_body_list, $all_map["order_res_ymd"]);
				// 契約No
				array_push($csv_body_list, $all_map["rntl_cont_no"]);
				// 契約名
				array_push($csv_body_list, $all_map["rntl_cont_name"]);

				// CSVレコード配列にマージ
				array_push($csv_datas, $csv_body_list);
			}
		}

		// CSVデータ書き込み
		$file_name = "unreturn_".date("YmdHis", time()).".csv";
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".$file_name);

		$fp = fopen('php://output','w');
		foreach ($csv_datas as $csv_data) {
			mb_convert_variables("SJIS-win", "UTF-8", $csv_data);
			fputcsv($fp, $csv_data);
		}

		fclose($fp);
	}
	//--返却状況照会CSVダウンロード ここまで--//



	//--受領確認照会CSVダウンロード--//
	if ($cond["ui_type"] === "receive") {
		$result["csv_code"] = "0003";

		//---受領確認検索処理---//
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
		$arg_str .= "t_order.cster_emply_cd as as_cster_emply_cd,";
		$arg_str .= "t_order.werer_name as as_werer_name,";
	//	$arg_str .= "m_wearer_std.werer_name as as_werer_name,";
		$arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
		$arg_str .= "m_job_type.job_type_name as as_job_type_name,";
		$arg_str .= "t_order.order_sts_kbn as as_order_sts_kbn,";
	//	$arg_str .= "t_order.order_reason_kbn as as_order_reason_kbn,";
		$arg_str .= "t_order.order_req_ymd as as_order_req_ymd,";
		$arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
		$arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no";
		$arg_str .= " FROM t_delivery_goods_state_details LEFT JOIN";
		$arg_str .= " (t_delivery_goods_state LEFT JOIN";
		$arg_str .= " (t_order_state LEFT JOIN";
		$arg_str .= " (t_order INNER JOIN m_section ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
		$arg_str .= " INNER JOIN m_wearer_std ON t_order.m_wearer_std_comb_hkey = m_wearer_std.m_wearer_std_comb_hkey";
		$arg_str .= " INNER JOIN (m_job_type INNER JOIN m_input_item ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey)";
		$arg_str .= " ON t_order.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey)";
		$arg_str .= " ON t_order_state.t_order_comb_hkey = t_order.t_order_comb_hkey)";
		$arg_str .= " ON t_delivery_goods_state.t_order_state_comb_hkey = t_order_state.t_order_state_comb_hkey)";
		$arg_str .= " ON t_delivery_goods_state_details.ship_no = t_delivery_goods_state.ship_no";
		$arg_str .= " WHERE ";
		$arg_str .= $query;
		$arg_str .= ") as distinct_table";
		if (!empty($q_sort_key)) {
			$arg_str .= " ORDER BY ";
			$arg_str .= $q_sort_key." ".$order;
		}

		$t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
		$results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
		$result_obj = (array)$results;
		$results_cnt = $result_obj["\0*\0_count"];
		$results = $result_obj["\0*\0_rows"];

		$list = array();
		$all_list = array();
		$json_list = array();

		if(!empty($results)){
			foreach($results as $result){
				// 受領ステータス
				$list['receipt_status'] = $result["as_receipt_status"];
				// 受領日
				$list['receipt_date'] = $result["as_receipt_date"];
				// メーカー伝票番号
				if (!empty($result["as_ship_no"])) {
					$list['ship_no'] = $result["as_ship_no"];
				} else {
					$list['ship_no'] = "-";
				}
				// 商品コード
				$list['item_cd'] = $result["as_item_cd"];
				// 色コード
				$list['color_cd'] = $result["as_color_cd"];
				// サイズ
				$list['size_cd'] = $result["as_size_cd"];
				// サイズ２
				$list['size_two_cd'] = $result["as_size_two_cd"];
				// 商品名
				if (!empty($result["as_input_item_name"])) {
					$list['input_item_name'] = $result["as_input_item_name"];
				} else {
					$list['input_item_name'] = "-";
				}
				// 出荷数
				if (!empty($result["as_ship_qty"])) {
					$list['ship_qty'] = $result["as_ship_qty"];
				} else {
					$list['ship_qty'] = "-";
				}
				// 個体管理番号
				if (!empty($result["as_individual_ctrl_no"])) {
					$list['individual_ctrl_no'] = $result["as_individual_ctrl_no"];
				} else {
					$list['individual_ctrl_no'] = "-";
				}
				// 発注No
				if (!empty($result["as_order_req_no"])) {
					$list['order_req_no'] = $result["as_order_req_no"];
				} else {
					$list['order_req_no'] = "-";
				}
				// 発注行No
				if (!empty($result["as_order_req_line_no"])) {
					$list['order_req_line_no'] = $result["as_order_req_line_no"];
				} else {
					$list['order_req_line_no'] = "-";
				}
				// 社員番号
				if (!empty($result["as_cster_emply_cd"])) {
					$list['cster_emply_cd'] = $result["as_cster_emply_cd"];
				} else {
					$list['cster_emply_cd'] = "-";
				}
				// 着用者名
				if (!empty($result["as_werer_name"])) {
					$list['werer_name'] = $result["as_werer_name"];
				} else {
					$list['werer_name'] = "-";
				}
				// 拠点
				if (!empty($result["as_rntl_sect_name"])) {
					$list['rntl_sect_name'] = $result["as_rntl_sect_name"];
				} else {
					$list['rntl_sect_name'] = "-";
				}
				// 貸与パターン
				if (!empty($result["as_job_type_name"])) {
					$list['job_type_name'] = $result["as_job_type_name"];
				} else {
					$list['job_type_name'] = "-";
				}
				// 発注区分
				$list['order_sts_kbn'] = $result["as_order_sts_kbn"];
				// 理由区分
	//			$list['order_reason_kbn'] = $result["as_order_reason_kbn"];
				// 発注日
				$list['order_req_ymd'] = $result["as_order_req_ymd"];
				// 出荷日
				$list['ship_ymd'] = $result["as_ship_ymd"];
				// メーカー受注番号
				if (!empty($result["as_rec_order_no"])) {
					$list['rec_order_no'] = $result["as_rec_order_no"];
				} else {
					$list['rec_order_no'] = "-";
				}

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
	/*
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
	*/
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
/*
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

		//---CSV出力---//
		$csv_datas = array();

		// ヘッダー作成
		$header_1 = array(
			'抽出件数：'.$results_cnt.'件'
		);
		array_push($csv_datas, $header_1);

		$header_2 = array();
		array_push($header_2, "受領日");
		array_push($header_2, "メーカー伝票番号");
		array_push($header_2, "商品-色(サイズ-サイズ2)");
		array_push($header_2, "商品名");
		array_push($header_2, "出荷数");
		if ($individual_flg) {
			array_push($header_2, "個体管理番号");
		}
		array_push($header_2, "発注No");
		array_push($header_2, "発注行No");
		array_push($header_2, "社員番号");
		array_push($header_2, "着用者名");
		array_push($header_2, "拠点");
		array_push($header_2, "貸与パターン");
		array_push($header_2, "ステータス");
		array_push($header_2, "発注区分");
		array_push($header_2, "発注日");
		array_push($header_2, "出荷日");
		array_push($header_2, "メーカー受注番号");
		array_push($csv_datas, $header_2);

		// ボディ作成
		if (!empty($all_list)) {
			foreach ($all_list as $all_map) {
				$csv_body_list = array();
				// 受領日
				array_push($csv_body_list, $all_map["receipt_date"]);
				// メーカー伝票番号
				array_push($csv_body_list, $all_map["ship_no"]);
				// 商品-色(サイズ-サイズ2)
				array_push($csv_body_list, $all_map["shin_item_code"]);
				// 商品名
				array_push($csv_body_list, $all_map["input_item_name"]);
				// 出荷数
				array_push($csv_body_list, $all_map["ship_qty"]);
				// 個体管理番号
				if ($individual_flg) {
					array_push($csv_body_list, $all_map["individual_ctrl_no"]);
				}
				// 発注No
				array_push($csv_body_list, $all_map["order_req_no"]);
				// 発注行No
				array_push($csv_body_list, $all_map["order_req_line_no"]);
				// 社員番号
				array_push($csv_body_list, $all_map["cster_emply_cd"]);
				// 着用者名
				array_push($csv_body_list, $all_map["werer_name"]);
				// 拠点
				array_push($csv_body_list, $all_map["rntl_sect_name"]);
				// 貸与パターン
				array_push($csv_body_list, $all_map["job_type_name"]);
				// ステータス
				array_push($csv_body_list, $all_map["receipt_status_name"]);
				// 発注区分
				array_push($csv_body_list, $all_map["order_sts_name"]);
				// 発注日
				array_push($csv_body_list, $all_map["order_req_ymd"]);
				// 出荷日
				array_push($csv_body_list, $all_map["ship_ymd"]);
				// メーカー受注番号
				array_push($csv_body_list, $all_map["rec_order_no"]);

				// CSVレコード配列にマージ
				array_push($csv_datas, $csv_body_list);
			}
		}

		// CSVデータ書き込み
		$file_name = "receive_".date("YmdHis", time()).".csv";
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".$file_name);

		$fp = fopen('php://output','w');
		foreach ($csv_datas as $csv_data) {
			mb_convert_variables("SJIS-win", "UTF-8", $csv_data);
			fputcsv($fp, $csv_data);
		}

		fclose($fp);
	}
	//--受領確認照会CSVダウンロード ここまで--//



	//--貸与リストCSVダウンロード--//
	if ($cond["ui_type"] === "lend") {
		$result["csv_code"] = "0004";

		//---貸与リスト検索処理---//
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
		$results = $result_obj["\0*\0_rows"];
		$results_cnt = $result_obj["\0*\0_count"];

		$list = array();
		$all_list = array();
		$json_list = array();

		if(!empty($results_cnt)){
			foreach($results as $result){
				// 社員番号
				if (!empty($result["as_cster_emply_cd"])) {
					$list['cster_emply_cd'] = $result["as_cster_emply_cd"];
				} else {
					$list['cster_emply_cd'] = "-";
				}
				// 着用者名
				if (!empty($result["as_werer_name"])) {
					$list['werer_name'] = $result["as_werer_name"];
				} else {
					$list['werer_name'] = "-";
				}
				// 現在の拠点コード
				$list['now_rntl_sect_cd'] = $result["as_now_rntl_sect_cd"];
				// 現在の貸与パターン
				$list['now_job_type_cd'] = $result["as_now_job_type_cd"];
				// 納品時の拠点コード
				$list['old_rntl_sect_cd'] = $result["as_old_rntl_sect_cd"];
				// 納品時の貸与パターン
				$list['old_job_type_cd'] = $result["as_old_job_type_cd"];
				// 商品コード
				$list['item_cd'] = $result["as_item_cd"];
				// 色コード
				$list['color_cd'] = $result["as_color_cd"];
				// サイズコード
				$list['size_cd'] = $result["as_size_cd"];
				// サイズコード２
				$list['size_two_cd'] = $result["as_size_two_cd"];
				// 職種アイテムコード
				$list['job_type_item_cd'] = $result["as_job_type_item_cd"];
				// 個体管理番号
				if (!empty($result["as_individual_ctrl_no"])) {
					$list['individual_ctrl_no'] = $result["as_individual_ctrl_no"];
				} else {
					$list['individual_ctrl_no'] = "-";
				}
				// 出荷数
				if (!empty($result["as_ship_qty"])) {
					$list['ship_qty'] = $result["as_ship_qty"];
				} else {
					$list['ship_qty'] = "-";
				}
				// 出荷日
				$list['ship_ymd'] = $result["as_ship_ymd"];
				// 返却予定日
				$list['re_order_date'] = $result["as_re_order_date"];
				// 発注No
				if (!empty($result["as_order_req_no"])) {
					$list['order_req_no'] = $result["as_order_req_no"];
				} else {
					$list['order_req_no'] = "-";
				}
				// メーカー受注番号
				if (!empty($result["as_rec_order_no"])) {
					$list['rec_order_no'] = $result["as_rec_order_no"];
				} else {
					$list['rec_order_no'] = "-";
				}
				// メーカー伝票番号
				if (!empty($result["as_ship_no"])) {
					$list['ship_no'] = $result["as_ship_no"];
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

		// 個体管理番号表示/非表示フラグ設定
		if ($auth["individual_flg"] == 1) {
			$individual_flg = true;
		} else {
			$individual_flg = false;
		}
/*
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

		//---CSV出力---//
		$csv_datas = array();

		// ヘッダー作成
		$header_1 = array(
			'抽出件数：'.$results_cnt.'件'
		);
		array_push($csv_datas, $header_1);

		$header_2 = array();
		array_push($header_2, "社員番号");
		array_push($header_2, "着用者名");
		array_push($header_2, "現在の拠点");
		array_push($header_2, "納品時の貸与パターン");
		array_push($header_2, "納品時の拠点");
		array_push($header_2, "現在の貸与パターン");
		array_push($header_2, "商品-色(サイズ-サイズ2)");
		array_push($header_2, "商品名");
		if ($individual_flg) {
			array_push($header_2, "個体管理番号");
		}
		array_push($header_2, "出荷数");
		array_push($header_2, "出荷日");
		array_push($header_2, "返却予定日");
		array_push($header_2, "発注No");
		array_push($header_2, "メーカー受注番号");
		array_push($header_2, "メーカー伝票番号");
		array_push($csv_datas, $header_2);

		// ボディ作成
		if (!empty($all_list)) {
			foreach ($all_list as $all_map) {
				$csv_body_list = array();
				// 社員番号
				array_push($csv_body_list, $all_map["cster_emply_cd"]);
				// 着用者名
				array_push($csv_body_list, $all_map["werer_name"]);
				// 現在の拠点
				array_push($csv_body_list, $all_map["now_rntl_sect_name"]);
				// 現在の貸与パターン
				array_push($csv_body_list, $all_map["now_job_type_name"]);
				// 納品時の拠点
				array_push($csv_body_list, $all_map["old_rntl_sect_name"]);
				// 納品時の貸与パターン
				array_push($csv_body_list, $all_map["old_job_type_name"]);
				// 商品-色(サイズ-サイズ2)
				array_push($csv_body_list, $all_map["shin_item_code"]);
				// 商品名
				array_push($csv_body_list, $all_map["input_item_name"]);
				// 個体管理番号
				if ($individual_flg) {
					array_push($csv_body_list, $all_map["individual_ctrl_no"]);
				}
				// 出荷数
				array_push($csv_body_list, $all_map["ship_qty"]);
				// 出荷日
				array_push($csv_body_list, $all_map["ship_ymd"]);
				// 返却予定日
				array_push($csv_body_list, $all_map["re_order_date"]);
				// 発注No
				array_push($csv_body_list, $all_map["order_req_no"]);
				// メーカー受注番号
				array_push($csv_body_list, $all_map["rec_order_no"]);
				// メーカー伝票番号
				array_push($csv_body_list, $all_map["ship_no"]);

				// CSVレコード配列にマージ
				array_push($csv_datas, $csv_body_list);
			}
		}

		// CSVデータ書き込み
		$file_name = "lend_".date("YmdHis", time()).".csv";
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".$file_name);

		$fp = fopen('php://output','w');
		foreach ($csv_datas as $csv_data) {
			mb_convert_variables("SJIS-win", "UTF-8", $csv_data);
			fputcsv($fp, $csv_data);
		}

		fclose($fp);
	}
	//--貸与リストCSVダウンロード ここまで--//

});
