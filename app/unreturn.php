<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 返却状況照会検索
 */
$app->post('/unreturn/search', function ()use($app){

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
        array_push($query_list,"CAST(CASE 
            WHEN t_order.order_req_ymd = '00000000' THEN NULL 
            ELSE t_order.order_req_ymd 
            END 
            AS DATE) >= CAST('".$cond['order_day_from']."' AS DATE)");
	}
	//発注日to
	if(!empty($cond['order_day_to'])){
        array_push($query_list,"CAST(CASE 
            WHEN t_order.order_req_ymd = '00000000' THEN NULL 
            ELSE t_order.order_req_ymd 
            END 
            AS DATE) <= CAST('".$cond['order_day_to']."' AS DATE)");
	}
	//返却日from
	if(!empty($cond['return_day_from'])){
        array_push($query_list,"CAST(CASE 
            WHEN t_returned_plan_info.return_date = '00000000' THEN NULL 
            ELSE t_returned_plan_info.return_date 
            END 
            AS DATE) >= CAST('".$cond['return_day_from']."' AS DATE)");
	}
	//返却日to
	if(!empty($cond['return_day_to'])){
        array_push($query_list,"CAST(CASE 
            WHEN t_returned_plan_info.return_date = '00000000' THEN NULL 
            ELSE t_returned_plan_info.return_date 
            END 
            AS DATE) <= CAST('".$cond['return_day_to']."' AS DATE)");
	}
	//個体管理番号
	if(!empty($cond['individual_number'])){
		array_push($query_list,"t_returned_plan_info.individual_ctrl_no LIKE '".$cond['individual_number']."%'");
	}
	// 着用者状況区分
	//array_push($query_list,"m_wearer_std.werer_sts_kbn = '1'");

    //ゼロ埋めがない場合、ログインアカウントの条件追加
    if($rntl_sect_cd_zero_flg == 0){
        array_push($query_list,"m_contract_resource.accnt_no = '$accnt_no'");
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
    $kbn_list = array();

    //交換
    $reason_kbn_2 = array();
    if($cond['order_kbn0']) {
        //交換にチェックがついてたら
        $order_kbn = "(t_order.order_sts_kbn = '3' OR t_order.order_sts_kbn = '4') AND m_wearer_std.werer_sts_kbn = '1'";
        if($cond['reason_kbn0']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '14'");
        }
        if($cond['reason_kbn1']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '15'");
        }
        if($cond['reason_kbn2']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '16'");
        }
        if($cond['reason_kbn3']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '17'");
        }
        if($cond['reason_kbn4']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '12'");
        }
        if($cond['reason_kbn5']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '13'");
        }
        if($cond['reason_kbn6']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '23'");
        }
        if ($reason_kbn_2) {
            //理由区分と発注区分
            $reason_kbn_2_str = implode(' OR ', $reason_kbn_2);
            array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_2_str . "))");
        } else {
            //発注区分のみ
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '14'");
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '15'");
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '16'");
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '17'");
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '12'");
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '13'");
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '23'");
            $reason_kbn_2_str = implode(' OR ', $reason_kbn_2);
            array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_2_str . "))");
        }
    }else{
        //交換にチェックがついてない
        if($cond['reason_kbn0']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '14'");
        }
        if($cond['reason_kbn1']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '15'");
        }
        if($cond['reason_kbn2']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '16'");
        }
        if($cond['reason_kbn3']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '17'");
        }
        if($cond['reason_kbn4']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '12'");
        }
        if($cond['reason_kbn5']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '13'");
        }
        if($cond['reason_kbn6']){
            array_push($reason_kbn_2, "t_order.order_reason_kbn = '23'");
        }
        if ($reason_kbn_2) {
            //理由区分のみ
            $reason_kbn_2_str = implode(' OR ', $reason_kbn_2);
            array_push($kbn_list, "(".$reason_kbn_2_str .")");
        }else{
            $order_kbn = "(t_order.order_sts_kbn != '3' AND t_order.order_sts_kbn != '4')";
            //何もチェックなければ交換を除く
            array_push($query_list, $order_kbn);
        }
    }

    //職種変更または異動
    $reason_kbn_3 = array();
    if($cond['order_kbn1']) {
        //異動の場合、着用者基本マスタ.着用者状況区分＝8：異動の着用者を検索する。
        //職種変更または異動にチェックがついてたら
        $order_kbn = "(t_order.order_sts_kbn = '5' AND m_wearer_std.werer_sts_kbn = '8')";
        if($cond['reason_kbn7']){
            array_push($reason_kbn_3, "t_order.order_reason_kbn = '09'");
        }
        if($cond['reason_kbn8']){
            array_push($reason_kbn_3, "t_order.order_reason_kbn = '10'");
        }
        if($cond['reason_kbn9']){
            array_push($reason_kbn_3, "t_order.order_reason_kbn = '11'");
        }
        if ($reason_kbn_3) {
            //理由区分と発注区分
            $reason_kbn_3_str = implode(' OR ', $reason_kbn_3);
            array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_3_str . "))");
        } else {
            //発注区分のみ
            array_push($reason_kbn_3, "t_order.order_reason_kbn = '09'");
            array_push($reason_kbn_3, "t_order.order_reason_kbn = '10'");
            array_push($reason_kbn_3, "t_order.order_reason_kbn = '11'");
            $reason_kbn_3_str = implode(' OR ', $reason_kbn_3);
            array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_3_str . "))");
        }
    }else{
        //職種変更または異動にチェックがついてない
        if($cond['reason_kbn7']){
            array_push($reason_kbn_3, "t_order.order_reason_kbn = '09'");
        }
        if($cond['reason_kbn8']){
            array_push($reason_kbn_3, "t_order.order_reason_kbn = '10'");
        }
        if($cond['reason_kbn9']){
            array_push($reason_kbn_3, "t_order.order_reason_kbn = '11'");
        }
        if ($reason_kbn_3) {
            //理由区分のみ
            //異動の場合、着用者基本マスタ.着用者状況区分＝8：異動の着用者を検索する。
            $reason_kbn_3_str = implode(' OR ', $reason_kbn_3);
            array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_3_str . "))");
        }else{
            $order_kbn = "t_order.order_sts_kbn != '5'";
            //何もチェックなければ交換を除く
            array_push($query_list, $order_kbn);
        }
    }
    //貸与終了
    $reason_kbn_4 = array();
    if($cond['order_kbn2']) {
        //貸与終了にチェックがついてたら
        $order_kbn = "t_order.order_sts_kbn = '2'";
        if($cond['reason_kbn10']){
            //貸与終了、かつ、理由区分＝05：退職の場合、着用者基本マスタ.着用者状況区分＝4：退社の着用者を検索する。
            array_push($reason_kbn_4, "(t_order.order_reason_kbn = '05' AND m_wearer_std.werer_sts_kbn = '4')");
        }
        if($cond['reason_kbn11']){
            //貸与終了、かつ、理由区分＝06：休職の場合、着用者基本マスタ.着用者状況区分＝2:休職の着用者を検索する。
            array_push($reason_kbn_4, "(t_order.order_reason_kbn = '06' AND m_wearer_std.werer_sts_kbn = '2')");
        }
        if($cond['reason_kbn12']){
            array_push($reason_kbn_4, "t_order.order_reason_kbn = '07' AND m_wearer_std.werer_sts_kbn = '1'");
        }
        if($cond['reason_kbn13']){
            array_push($reason_kbn_4, "t_order.order_reason_kbn = '08' AND m_wearer_std.werer_sts_kbn = '1'");
        }
        if($cond['reason_kbn14']){
            array_push($reason_kbn_4, "t_order.order_reason_kbn = '24' AND m_wearer_std.werer_sts_kbn = '1'");
        }
        if ($reason_kbn_4) {
            //理由区分と発注区分
            $reason_kbn_4_str = implode(' OR ', $reason_kbn_4);
            array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_4_str . "))");
        } else {
            //発注区分のみ
            array_push($reason_kbn_4, "(t_order.order_reason_kbn = '05' AND m_wearer_std.werer_sts_kbn = '4')");
            array_push($reason_kbn_4, "(t_order.order_reason_kbn = '06' AND m_wearer_std.werer_sts_kbn = '2')");
            array_push($reason_kbn_4, "t_order.order_reason_kbn = '07' AND m_wearer_std.werer_sts_kbn = '1'");
            array_push($reason_kbn_4, "t_order.order_reason_kbn = '08' AND m_wearer_std.werer_sts_kbn = '1'");
            array_push($reason_kbn_4, "t_order.order_reason_kbn = '24' AND m_wearer_std.werer_sts_kbn = '1'");
            $reason_kbn_4_str = implode(' OR ', $reason_kbn_4);
            array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_4_str . "))");
        }
    }else{
        //貸与終了にチェックがついてない
        if($cond['reason_kbn10']){
            array_push($reason_kbn_4, "(t_order.order_reason_kbn = '05' AND m_wearer_std.werer_sts_kbn = '4')");
        }
        if($cond['reason_kbn11']){
            array_push($reason_kbn_4, "(t_order.order_reason_kbn = '06' AND m_wearer_std.werer_sts_kbn = '2')");
        }
        if($cond['reason_kbn12']){
            array_push($reason_kbn_4, "t_order.order_reason_kbn = '07' AND m_wearer_std.werer_sts_kbn = '1'");
        }
        if($cond['reason_kbn13']){
            array_push($reason_kbn_4, "t_order.order_reason_kbn = '08' AND m_wearer_std.werer_sts_kbn = '1'");
        }
        if($cond['reason_kbn14']){
            array_push($reason_kbn_4, "t_order.order_reason_kbn = '24' AND m_wearer_std.werer_sts_kbn = '1'");
        }
        if ($reason_kbn_4) {
            //理由区分のみ
            $reason_kbn_4_str = implode(' OR ', $reason_kbn_4);
            array_push($kbn_list, "(".$reason_kbn_4_str .")");
        }else{
            $order_kbn = "t_order.order_sts_kbn != '2'";
            //何もチェックなければ交換を除く
            array_push($query_list, $order_kbn);
        }
    }

    //その他
    if($cond['order_kbn3']){
        array_push($kbn_list,"t_order.order_sts_kbn = '9' AND m_wearer_std.werer_sts_kbn = '1'");
    }

    //区分を検索条件に追加
    if($kbn_list){
        array_push($query_list,'('.implode(' OR ', $kbn_list).')');
    }

	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';

	//ソート設定
	if(isset($page['sort_key'])){
		$sort_key = $page['sort_key'];
		$order = $page['order'];
		if($sort_key == 'order_req_no' || $sort_key == 'order_req_ymd' || $sort_key == 'return_status' || $sort_key == 'order_sts_kbn'){
			$q_sort_key = 'as_'.$sort_key;
		}
		if($sort_key == 'job_type_cd'){
			$q_sort_key = 'as_job_type_name';
		}
		if($sort_key == 'cster_emply_cd'){
			$q_sort_key = 'as_cster_emply_cd';
		}
		if($sort_key == 'rntl_sect_name'){
			$q_sort_key = 'as_rntl_sect_name';
		}
		if($sort_key == 'werer_name'){
			$q_sort_key = 'as_werer_name';
		}
        if($sort_key == 'item_code'){
            $q_sort_key = 'as_item_cd,as_size_cd';
        }
		if($sort_key == 'item_name'){
			$q_sort_key = 'as_input_item_name';
		}
		if($sort_key == 'maker_rec_no'){
			$q_sort_key = 'as_rec_order_no';
		}
		if($sort_key == 'return_shd_ymd'){
			$q_sort_key = 'as_re_order_date';
		}
		if($sort_key == 'maker_send_no'){
			$q_sort_key = 'as_ship_no';
		}
		if($sort_key == 'ship_ymd'){
			$q_sort_key = 'as_ship_ymd';
		}
		if($sort_key == 'send_ymd'){
			$q_sort_key = 'as_ship_ymd';
		}
		if($sort_key == 'individual_num'){
			$q_sort_key = 'as_individual_ctrl_no';
		}
		if($sort_key == 'order_res_ymd'){
			$q_sort_key = 'as_receipt_date';
		}
		if($sort_key == 'rental_no'){
			$q_sort_key = 'as_rntl_cont_no';
		}
		if($sort_key == 'rental_name'){
			$q_sort_key = 'as_rntl_cont_name';
		}
	} else {
		//指定がなければ発注No
		$q_sort_key = "as_order_req_no";
		$order = 'asc';
	}
    //ChromePhp::log($sort_key);
    //ChromePhp::log($q_sort_key);

	$arg_str = "SELECT ";
	$arg_str .= " * ";
	$arg_str .= " FROM ";
//	$arg_str .= "(SELECT ";
	$arg_str .= "(SELECT distinct on (t_returned_plan_info.order_req_no, t_returned_plan_info.order_req_line_no) ";
	$arg_str .= "t_returned_plan_info.order_req_no as as_order_req_no,";
	$arg_str .= "t_order.order_req_ymd as as_order_req_ymd,";
	$arg_str .= "t_returned_plan_info.order_sts_kbn as as_order_sts_kbn,";
	$arg_str .= "t_order.order_reason_kbn as as_order_reason_kbn,";
	$arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
	$arg_str .= "m_job_type.job_type_name as as_job_type_name,";
	$arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
	$arg_str .= "m_wearer_std.werer_name as as_werer_name,";
	$arg_str .= "t_returned_plan_info.item_cd as as_item_cd,";
	$arg_str .= "t_returned_plan_info.color_cd as as_color_cd,";
	$arg_str .= "t_returned_plan_info.size_cd as as_size_cd,";
	$arg_str .= "t_returned_plan_info.job_type_cd as as_job_type_cd,";
	$arg_str .= "t_order.size_two_cd as as_size_two_cd,";
	$arg_str .= "t_order.order_qty as as_order_qty,";
    $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
    $arg_str .= "t_returned_plan_info.order_date as as_re_order_date,";
	$arg_str .= "t_returned_plan_info.return_status as as_return_status,";
	$arg_str .= "t_returned_plan_info.return_date as as_return_date,";
	$arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no,";
	$arg_str .= "t_delivery_goods_state.ship_no as as_ship_no,";
	$arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
	$arg_str .= "t_delivery_goods_state.ship_qty as as_ship_qty,";
	$arg_str .= "t_delivery_goods_state.return_qty as as_return_qty,";
	$arg_str .= "t_returned_plan_info.individual_ctrl_no as as_individual_ctrl_no,";
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
    $arg_str .= " LEFT JOIN (m_job_type INNER JOIN m_input_item";
    $arg_str .= " ON m_job_type.corporate_id = m_input_item.corporate_id";
    $arg_str .= " AND m_job_type.rntl_cont_no = m_input_item.rntl_cont_no";
    $arg_str .= " AND m_job_type.job_type_cd = m_input_item.job_type_cd)";
    $arg_str .= " ON t_returned_plan_info.corporate_id = m_job_type.corporate_id";
    $arg_str .= " AND t_returned_plan_info.rntl_cont_no = m_job_type.rntl_cont_no";
    $arg_str .= " AND t_returned_plan_info.job_type_cd = m_job_type.job_type_cd";
    $arg_str .= " AND t_returned_plan_info.corporate_id = m_input_item.corporate_id";
    $arg_str .= " AND t_returned_plan_info.item_cd = m_input_item.item_cd";
    $arg_str .= " AND t_returned_plan_info.color_cd = m_input_item.color_cd";

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
	//$arg_str .= " INNER JOIN m_job_type";
	//$arg_str .= " ON t_order.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
    $arg_str .= " INNER JOIN m_wearer_std";
    $arg_str .= " ON t_order.werer_cd = m_wearer_std.werer_cd";
    $arg_str .= " AND t_order.corporate_id = m_wearer_std.corporate_id";
    $arg_str .= " AND t_order.rntl_cont_no = m_wearer_std.rntl_cont_no";
	$arg_str .= " INNER JOIN m_contract";
	$arg_str .= " ON t_order.rntl_cont_no = m_contract.rntl_cont_no";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= ") as distinct_table";
	if (!empty($q_sort_key)) {
		$arg_str .= " ORDER BY ";
		$arg_str .= $q_sort_key." ".$order;
	}
	$t_order = new TOrder();
	$results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
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

    //色づけ処理用変数
    $order_req_no_check = "";
    $list['color'] = "blue";

	if(!empty($results_cnt)) {
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

		foreach($results as $result){

            //色づけ処理分岐
            if($list['color'] == 'blue'){
                if ($order_req_no_check == $result->as_order_req_no){
                    $list['diff'] = 'same';
                    $list['color'] = 'blue';
                }elseif($order_req_no_check == ""){
                    $list['diff'] = 'same';
                    $list['color'] = 'blue';
                }elseif($order_req_no_check !== $result->as_order_req_no){
                    $list['diff'] = 'differ';
                    $list['color'] = 'white';
                }
            }elseif($list['color'] == 'white'){
                if ($order_req_no_check == $result->as_order_req_no){
                    $list['diff'] = 'same';
                    $list['color'] = 'white';
                }elseif($order_req_no_check == ""){
                    $list['diff'] = 'same';
                    $list['color'] = 'white';
                }elseif($order_req_no_check !== $result->as_order_req_no){
                    $list['diff'] = 'differ';
                    $list['color'] = 'blue';
                }
            }

            // 発注依頼No.
            if (!empty($result->as_order_req_no)) {
                $list['order_req_no'] = $result->as_order_req_no;
                $order_req_no_check = $result->as_order_req_no;
            } else {
                $list['order_req_no'] = "-";
            }
			// 発注依頼日
			$list['order_req_ymd'] = $result->as_order_req_ymd;
			// 発注区分
			$list['order_sts_kbn'] = $result->as_order_sts_kbn;
			// 理由区分
			$list['order_reason_kbn'] = $result->as_order_reason_kbn;
			// 契約No
			if (!empty($result->as_rntl_cont_no)) {
				$list['rntl_cont_no'] = $result->as_rntl_cont_no;
			} else {
				$list['rntl_cont_no'] = "-";
			}
			// 拠点
			if (!empty($result->as_rntl_sect_name)) {
				$list['rntl_sect_name'] = $result->as_rntl_sect_name;
			} else {
				$list['rntl_sect_name'] = "-";
			}
			// 貸与パターン
			$list['job_type_cd'] = $result->as_job_type_cd;
			if (!empty($result->as_job_type_name)) {
				$list['job_type_name'] = $result->as_job_type_name;
			} else {
				$list['job_type_name'] = "-";
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
			// 商品コード
			$list['item_cd'] = $result->as_item_cd;
			// 色コード
			$list['color_cd'] = $result->as_color_cd;
			// サイズコード
			$list['size_cd'] = $result->as_size_cd;
			// サイズ2コード
			$list['size_two_cd'] = $result->as_size_two_cd;
			// 投入商品名
			$list['input_item_name'] = "-";
			$query_list = array();
		  $query_list[] = "corporate_id = '".$auth['corporate_id']."'";
		  $query_list[] = "rntl_cont_no = '".$list['rntl_cont_no']."'";
		  $query_list[] = "job_type_cd = '".$list['job_type_cd']."'";
		  $query_list[] = "item_cd = '".$list['item_cd']."'";
		  $query_list[] = "color_cd = '".$list['color_cd']."'";
		  $query = implode(' AND ', $query_list);
			$arg_str = "";
		  $arg_str = "SELECT ";
		  $arg_str .= "input_item_name";
		  $arg_str .= " FROM ";
		  $arg_str .= "m_input_item";
		  $arg_str .= " WHERE ";
		  $arg_str .= $query;
		  //ChromePhp::LOG($arg_str);
		  $m_input_item = new MInputItem();
		  $m_input_item_results = new Resultset(NULL, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
		  $result_obj = (array)$m_input_item_results;
		  $m_input_item_results_cnt = $result_obj["\0*\0_count"];
			if ($m_input_item_results_cnt > 0) {
				$paginator_model = new PaginatorModel(
		        array(
		            "data"  => $m_input_item_results,
		            "limit" => 1,
		            "page" => 1
		        )
		    );
		    $paginator = $paginator_model->getPaginate();
		    $m_input_item_results = $paginator->items;
				foreach ($m_input_item_results as $m_input_item_result) {
					$list['input_item_name'] = $m_input_item_result->input_item_name;
				}
			}
			// 商品-色(サイズ-サイズ2)表示変換
			if (!empty($list['item_cd']) && !empty($list['color_cd'])) {
				$list['shin_item_code'] = $list['item_cd']."-".$list['color_cd']."(".$list['size_cd']."-".$list['size_two_cd'].")";
			} else {
				$list['shin_item_code'] = "-";
			}
            // 発注数
            $list['order_qty'] = '0';
            if($result->as_order_qty){
                $list['order_qty'] = $result->as_order_qty;
            }
			// メーカー受注番号
			if (!empty($result->as_rec_order_no)) {
				$list['rec_order_no'] = $result->as_rec_order_no;
			} else {
				$list['rec_order_no'] = "-";
			}
			// 返却日
			$list['re_order_date'] = $result->as_re_order_date;
			// 返却ステータス
			$list['return_status'] = $result->as_return_status;
			// 返却数
			$list['return_qty'] = $result->as_return_qty;
			// メーカー伝票番号
			if (!empty($result->as_ship_no)) {
				$list['ship_no'] = $result->as_ship_no;
			} else {
				$list['ship_no'] = "-";
			}
			// 出荷日
			$list['ship_ymd'] = $result->as_ship_ymd;
            // 出荷数
            $list['ship_qty'] = '0';
            if($result->as_ship_qty){
                $list['ship_qty'] = $result->as_ship_qty;
            }
			// 契約No
			if (!empty($result->as_rntl_cont_name)) {
				$list['rntl_cont_name'] = $result->as_rntl_cont_name;
			} else {
				$list['rntl_cont_name'] = "-";
			}

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

            //---受領日時の取得---//
            $list['individual_num'] = "-";
            $list['order_res_ymd'] = "-";
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "ship_no = '".$list['ship_no']."'");
            array_push($query_list, "item_cd = '".$list['item_cd']."'");
            array_push($query_list, "color_cd = '".$list['color_cd']."'");
            //rray_push($query_list, "size_cd = '".$list['size_cd']."'");
            $query = implode(' AND ', $query_list);
            $arg_str = "";
            $arg_str .= "SELECT ";
            $arg_str .= "receipt_date";
            $arg_str .= " FROM ";
            $arg_str .= "t_delivery_goods_state_details";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
            $del_gd_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
            $result_obj = (array)$del_gd_results;
            $results_cnt2 = $result_obj["\0*\0_count"];
            if ($results_cnt2 > 0) {
                $paginator_model = new PaginatorModel(
                    array(
                        "data"  => $del_gd_results,
                        "limit" => $results_cnt2,
                        "page" => 1
                    )
                );
                $paginator = $paginator_model->getPaginate();
                $del_gd_results = $paginator->items;

                $num_list = array();
                $day_list = array();
                foreach ($del_gd_results as $del_gd_result) {
                    if ($del_gd_result->receipt_date !== null) {
                        array_push($day_list,  date('Y/m/d',strtotime($del_gd_result->receipt_date)));
                    } else {
                        array_push($day_list, "-");
                    }
                }
                // 受領日
                //ChromePhp::log($day_list);
                $receipt_date = implode("<br>", $day_list);
                //ChromePhp::log($receipt_date);
                $list['order_res_ymd'] = $receipt_date;
            }


            //---個体管理番号---//
            $list['individual_num'] = "-";
            $list['order_res_ymd'] = "-";
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "order_req_no = '".$list['order_req_no']."'");
            array_push($query_list, "item_cd = '".$list['item_cd']."'");
            array_push($query_list, "color_cd = '".$list['color_cd']."'");
            //rray_push($query_list, "size_cd = '".$list['size_cd']."'");
            $query = implode(' AND ', $query_list);
            $arg_str = "";
            $arg_str .= "SELECT ";
            $arg_str .= "individual_ctrl_no";
            $arg_str .= " FROM ";
            $arg_str .= "t_returned_plan_info";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            ChromePhp::log($arg_str);
            $t_returned_plan_info = new TReturnedPlanInfo();
            $t_returned_results = new Resultset(null, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
            $result_obj = (array)$t_returned_results;
            $results_cnt3 = $result_obj["\0*\0_count"];
            if ($results_cnt3 > 0) {
                $paginator_model = new PaginatorModel(
                    array(
                        "data"  => $t_returned_results,
                        "limit" => $results_cnt3,
                        "page" => 1
                    )
                );
                $paginator = $paginator_model->getPaginate();
                $t_returned_results = $paginator->items;

                $num_list = array();
                foreach ($t_returned_results as $t_returned_result) {
                    array_push($num_list, $t_returned_result->individual_ctrl_no);
                }
                // 個体管理番号
                $individual_ctrl_no = implode("<br>", $num_list);
                $list['individual_num'] = $individual_ctrl_no;
            }
            array_push($all_list,$list);
        }
    }

	//ソート設定(配列ソート)
	// 商品-色(サイズ-サイズ2)
    /*
	if($sort_key == 'item_code'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'shin_item_code'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'shin_item_code'), SORT_ASC, $all_list);
		}
	}
    */

    // 個体管理番号表示/非表示フラグ設定
    if (individual_flg($auth['corporate_id'], $cond['agreement_no']) == 1) {
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

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $results_cnt;

    ChromePhp::log($page_list);


	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;
	$json_list['individual_flag'] = $individual_flg;
	echo json_encode($json_list);
});
