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
 * 0005:在庫照会
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
    $page = $params['page'];

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
			foreach ($results as $rentl_sect_cd_result) {
					$all_list[] = $rentl_sect_cd_result->rntl_sect_cd;
			}
	}

	if (in_array("0000000000", $all_list)) {
			$rntl_sect_cd_zero_flg = 1;

	}else{
			$rntl_sect_cd_zero_flg = 0;
	}

	//--発注状況照会CSVダウンロード--//
	if ($cond["ui_type"] === "history") {
        $result["csv_code"] = "0001";

		//---発注状況検索処理---//	//企業ID
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
            array_push($query_list,"(t_order.rntl_sect_cd = '".$cond['section']."' OR t_order.order_rntl_sect_cd = '".$cond['section']."')");
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

        //出荷日from
        if(!empty($cond['send_day_from'])){
            array_push($query_list,"CAST(CASE 
            WHEN t_order_state.ship_ymd = '00000000' THEN NULL 
            ELSE t_order_state.ship_ymd 
            END 
            AS DATE) >= CAST('".$cond['send_day_from']."' AS DATE)");
        }
        //出荷日to
        if(!empty($cond['send_day_to'])){
            array_push($query_list,"CAST(CASE 
            WHEN t_order_state.ship_ymd = '00000000' THEN NULL 
            ELSE t_order_state.ship_ymd 
            END 
            AS DATE) <= CAST('".$cond['send_day_to']."' AS DATE)");
        }
        //個体管理番号
        if(!empty($cond['individual_number'])){
            array_push($query_list,"t_delivery_goods_state_details.individual_ctrl_no LIKE '".$cond['individual_number']."%'");
        }

          //ゼロ埋めがない場合、ログインアカウントの条件追加
        if($rntl_sect_cd_zero_flg == 0){
              if(empty($cond['section'])) {
                  if ($all_list > 0) {
                      $order_section = array();
                      $all_list_count = count($all_list);
                      for ($i = 0; $i < $all_list_count; $i++) {
                          //着用者区分
                          array_push($order_section, $all_list[$i]);
                      }
                      if (!empty($order_section)) {
                          $order_section_str = implode("','", $order_section);
                          $order_section_query = "t_order.order_rntl_sect_cd IN ('" . $order_section_str . "')";
                      }
                      $rntl_accnt_no = "m_contract_resource.accnt_no = '$accnt_no'";
                      $accnt_no_and_order_section = $rntl_accnt_no . " OR " . $order_section_query;
                  }
                  array_push($query_list, "$accnt_no_and_order_section");
              }else{
                  //array_push($query_list,"m_contract_resource.accnt_no = '$accnt_no'");
              }
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
            array_push($query_list,"t_order.order_status IN ('".$status_str."')");
        }
        //発注区分
        $reason_kbn_1 = array();
        $kbn_list = array();
        if($cond['order_kbn0']) {
            $chk_flg = '1';
            //貸与開始にチェックがついてたら
            $order_kbn = "t_order.order_sts_kbn = '1' AND m_wearer_std.werer_sts_kbn = '1'";
            if ($cond['reason_kbn0']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '01'");
            }
            if ($cond['reason_kbn1']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '02'");
            }
            if ($cond['reason_kbn2']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '03'");
            }
            if ($cond['reason_kbn3']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '04'");
            }
            if ($cond['reason_kbn4']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '19'");
            }
            if ($reason_kbn_1) {
                //理由区分と発注区分
                $reason_kbn_1_str = implode(' OR ', $reason_kbn_1);
                array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_1_str . "))");
            } else {
                //発注区分のみ
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '01'");
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '02'");
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '03'");
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '03'");
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '04'");
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '19'");
                $reason_kbn_1_str = implode(' OR ', $reason_kbn_1);
                array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_1_str . "))");
            }
        }else{
            //貸与開始にチェックが付いてない
            if ($cond['reason_kbn0']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '01'");
            }
            if ($cond['reason_kbn1']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '02'");
            }
            if ($cond['reason_kbn2']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '03'");
            }
            if ($cond['reason_kbn3']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '04'");
            }
            if ($cond['reason_kbn4']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '19'");
            }
            if ($reason_kbn_1) {
                //理由区分のみ
                $reason_kbn_1_str = implode(' OR ', $reason_kbn_1);
                array_push($kbn_list, "(".$reason_kbn_1_str .")");
            }else{
                //何もチェックなければ貸与開始を除く
                $order_kbn = "t_order.order_sts_kbn != '1'";
                array_push($query_list, $order_kbn);
            }
        }

        //交換
        $reason_kbn_2 = array();
        if($cond['order_kbn1']) {
            //交換にチェックがついてたら
            $order_kbn = "(t_order.order_sts_kbn = '3' OR t_order.order_sts_kbn = '4') AND m_wearer_std.werer_sts_kbn = '1'";
            if($cond['reason_kbn5']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '14'");
            }
            if($cond['reason_kbn6']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '15'");
            }
            if($cond['reason_kbn7']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '16'");
            }
            if($cond['reason_kbn8']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '17'");
            }
            if($cond['reason_kbn9']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '12'");
            }
            if($cond['reason_kbn10']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '13'");
            }
            if($cond['reason_kbn11']){
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
            if($cond['reason_kbn5']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '14'");
            }
            if($cond['reason_kbn6']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '15'");
            }
            if($cond['reason_kbn7']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '16'");
            }
            if($cond['reason_kbn8']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '17'");
            }
            if($cond['reason_kbn9']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '12'");
            }
            if($cond['reason_kbn10']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '13'");
            }
            if($cond['reason_kbn11']){
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
        if($cond['order_kbn2']) {
            //異動の場合、着用者基本マスタ.着用者状況区分＝8：異動の着用者を検索する。
            //職種変更または異動にチェックがついてたら
            $order_kbn = "(t_order.order_sts_kbn = '5' AND m_wearer_std.werer_sts_kbn = '8')";
            if($cond['reason_kbn12']){
                array_push($reason_kbn_3, "t_order.order_reason_kbn = '09'");
            }
            if($cond['reason_kbn13']){
                array_push($reason_kbn_3, "t_order.order_reason_kbn = '10'");
            }
            if($cond['reason_kbn14']){
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
            if($cond['reason_kbn12']){
                array_push($reason_kbn_3, "t_order.order_reason_kbn = '09'");
            }
            if($cond['reason_kbn13']){
                array_push($reason_kbn_3, "t_order.order_reason_kbn = '10'");
            }
            if($cond['reason_kbn14']){
                array_push($reason_kbn_3, "t_order.order_reason_kbn = '11'");
            }
            if ($reason_kbn_3) {
                $order_kbn = "(t_order.order_sts_kbn = '5' AND m_wearer_std.werer_sts_kbn = '8')";
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
        if($cond['order_kbn3']) {
            //貸与終了にチェックがついてたら
            $order_kbn = "t_order.order_sts_kbn = '2'";
            if($cond['reason_kbn15']){
                //貸与終了、かつ、理由区分＝05：退職の場合、着用者基本マスタ.着用者状況区分＝4：退社の着用者を検索する。
                array_push($reason_kbn_4, "(t_order.order_reason_kbn = '05' AND m_wearer_std.werer_sts_kbn = '4')");
            }
            if($cond['reason_kbn16']){
                //貸与終了、かつ、理由区分＝06：休職の場合、着用者基本マスタ.着用者状況区分＝2:休職の着用者を検索する。
                array_push($reason_kbn_4, "(t_order.order_reason_kbn = '06' AND m_wearer_std.werer_sts_kbn = '2')");
            }
            if($cond['reason_kbn17']){
                array_push($reason_kbn_4, "t_order.order_reason_kbn = '07' AND m_wearer_std.werer_sts_kbn = '1'");
            }
            if($cond['reason_kbn18']){
                array_push($reason_kbn_4, "t_order.order_reason_kbn = '08' AND m_wearer_std.werer_sts_kbn = '1'");
            }
            if($cond['reason_kbn19']){
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
            if($cond['reason_kbn15']){
                array_push($reason_kbn_4, "(t_order.order_reason_kbn = '05' AND m_wearer_std.werer_sts_kbn = '4')");
            }
            if($cond['reason_kbn16']){
                array_push($reason_kbn_4, "(t_order.order_reason_kbn = '06' AND m_wearer_std.werer_sts_kbn = '2')");
            }
            if($cond['reason_kbn17']){
                array_push($reason_kbn_4, "t_order.order_reason_kbn = '07' AND m_wearer_std.werer_sts_kbn = '1'");
            }
            if($cond['reason_kbn18']){
                array_push($reason_kbn_4, "t_order.order_reason_kbn = '08' AND m_wearer_std.werer_sts_kbn = '1'");
            }
            if($cond['reason_kbn19']){
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
        if($cond['order_kbn4']){
            array_push($kbn_list,"t_order.order_sts_kbn = '9' AND m_wearer_std.werer_sts_kbn = '1'");
        }

        //区分を検索条件に追加
        if($kbn_list){
            array_push($query_list,'('.implode(' OR ', $kbn_list).')');
        }
        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $query_list);
        $sort_key ='';
        $order ='';

        //ソート設定
        if(!empty($page['sort_key'])){
            $sort_key = $page['sort_key'];
            $order = $page['order'];
            if($sort_key == 'order_req_no' || $sort_key == 'order_req_ymd' || $sort_key == 'order_status' || $sort_key == 'order_sts_kbn'){
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
            if($sort_key == 'send_shd_ymd'){
                $q_sort_key = 'as_order_req_ymd';
            }
            if($sort_key == 'order_status'){
                $q_sort_key = 'as_order_status';
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
        //---SQLクエリー実行---//
        $arg_str = "SELECT ";
        $arg_str .= " * ";
        $arg_str .= " FROM ";
        $arg_str .= "(SELECT distinct on (t_order.order_req_no, t_order.order_req_line_no) ";
        $arg_str .= "t_order.order_req_no as as_order_req_no,";
        $arg_str .= "t_order.order_req_ymd as as_order_req_ymd,";
        $arg_str .= "t_order.order_sts_kbn as as_order_sts_kbn,";
        $arg_str .= "t_order.werer_cd as as_werer_cd,";
        $arg_str .= "t_order.order_reason_kbn as as_order_reason_kbn,";
        $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
        $arg_str .= "m_job_type.job_type_name as as_job_type_name,";
        $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
        $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
        $arg_str .= "t_order.job_type_cd as as_job_type_cd,";
        $arg_str .= "t_order.item_cd as as_item_cd,";
        $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
        $arg_str .= "t_order.color_cd as as_color_cd,";
        $arg_str .= "t_order.size_cd as as_size_cd,";
        $arg_str .= "t_order.size_two_cd as as_size_two_cd,";
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
        $arg_str .= " LEFT JOIN (m_job_type INNER JOIN m_input_item";
        $arg_str .= " ON m_job_type.corporate_id = m_input_item.corporate_id";
        $arg_str .= " AND m_job_type.rntl_cont_no = m_input_item.rntl_cont_no";
        $arg_str .= " AND m_job_type.job_type_cd = m_input_item.job_type_cd)";
        $arg_str .= " ON t_order.corporate_id = m_job_type.corporate_id";
        $arg_str .= " AND t_order.rntl_cont_no = m_job_type.rntl_cont_no";
        $arg_str .= " AND t_order.job_type_cd = m_job_type.job_type_cd";
        $arg_str .= " AND t_order.corporate_id = m_input_item.corporate_id";
        $arg_str .= " AND t_order.item_cd = m_input_item.item_cd";
        $arg_str .= " AND t_order.color_cd = m_input_item.color_cd";
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

		$list = array();
		$all_list = array();
		$json_list = array();

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

			foreach($results as $result){
				// 発注依頼No.
				if (!empty($result->as_order_req_no)) {
					$list['order_req_no'] = $result->as_order_req_no;
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
				$list['order_qty'] = $result->as_order_qty;
				// 受注番号
				if (!empty($result->as_rec_order_no)) {
					$list['rec_order_no'] = $result->as_rec_order_no;
				} else {
					$list['rec_order_no'] = "-";
				}
				// 発注ステータス
				$list['order_status'] = $result->as_order_status;
				// 伝票番号
				if (!empty($result->as_ship_no)) {
					$list['ship_no'] = $result->as_ship_no;
				} else {
					$list['ship_no'] = "-";
				}
				// 出荷日
				$list['ship_ymd'] = $result->as_ship_ymd;
				// 出荷数
				$list['ship_qty'] = $result->as_ship_qty;
				// 契約No
				if (!empty($result->as_rntl_cont_name)) {
					$list['rntl_cont_name'] = $result->as_rntl_cont_name;
				} else {
					$list['rntl_cont_name'] = "-";
				}
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
					if ($list['ship_ymd'] !== "00000000") {
						$list['ship_ymd'] = date('Y/m/d',strtotime($list['ship_ymd']));
					} else {
						$list['ship_ymd'] = "-";
					}
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
				$list['individual_num'] = "-";
				$list['order_res_ymd'] = "-";
				$query_list = array();
				array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
				array_push($query_list, "ship_no = '".$list['ship_no']."'");
				array_push($query_list, "item_cd = '".$list['item_cd']."'");
				array_push($query_list, "color_cd = '".$list['color_cd']."'");
				array_push($query_list, "size_cd = '".$list['size_cd']."'");
				$query = implode(' AND ', $query_list);
				$arg_str = "";
				$arg_str .= "SELECT ";
				$arg_str .= "individual_ctrl_no,";
				$arg_str .= "receipt_date";
				$arg_str .= " FROM ";
				$arg_str .= "t_delivery_goods_state_details";
				$arg_str .= " WHERE ";
				$arg_str .= $query;
				$t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
				$del_gd_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
				$result_obj = (array)$del_gd_results;
				$del_gd_results_cnt = $result_obj["\0*\0_count"];
				if ($del_gd_results_cnt > 0) {
					$paginator_model = new PaginatorModel(
							array(
									"data"  => $del_gd_results,
									"limit" => $del_gd_results_cnt,
									"page" => 1
							)
					);
					$paginator = $paginator_model->getPaginate();
					$del_gd_results = $paginator->items;

					$num_list = array();
					$day_list = array();
					foreach ($del_gd_results as $del_gd_result) {
						array_push($num_list, $del_gd_result->individual_ctrl_no);
						if ($del_gd_result->receipt_date !== null) {
							array_push($day_list, date('Y/m/d',strtotime($del_gd_result->receipt_date)));
						} else {
							array_push($day_list, "-");
						}
					}
					// 個体管理番号
					$individual_ctrl_no = implode(PHP_EOL, $num_list);
					$list['individual_num'] = $individual_ctrl_no;
					// 受領日
					$receipt_date = implode(PHP_EOL, $day_list);
					$list['order_res_ymd'] = $receipt_date;
				}

				array_push($all_list,$list);
			}
		}

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
		array_push($header_2, "受注番号");
		array_push($header_2, "出荷予定日");
		array_push($header_2, "受注数");
		array_push($header_2, "ステータス");
		array_push($header_2, "伝票番号");
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
				// 受注番号
				array_push($csv_body_list, $all_map["rec_order_no"]);
				// 出荷予定日
				array_push($csv_body_list, $all_map["send_shd_ymd"]);
				// 受注数
				array_push($csv_body_list, $all_map["order_qty"]);
				// ステータス
				array_push($csv_body_list, $all_map["order_status_name"]);
				// 伝票番号
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
            array_push($query_list,"t_returned_plan_info.job_type_cd = '".$cond['job_type']."'");
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
                $order_kbn = "(t_order.order_sts_kbn = '5' AND m_wearer_std.werer_sts_kbn = '8')";
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


        if (individual_flg($auth['corporate_id'], $cond['agreement_no']) == 1) {

            //---SQLクエリー実行---//
            $arg_str = "SELECT ";
            $arg_str .= " * ";
            $arg_str .= " FROM ";
//	$arg_str .= "(SELECT ";
            $arg_str .= "(SELECT distinct on (t_returned_plan_info.order_req_no, t_returned_plan_info.item_cd, t_returned_plan_info.color_cd, t_returned_plan_info.size_cd) ";
            $arg_str .= "t_returned_plan_info.order_req_no as as_order_req_no,";
            $arg_str .= "t_returned_plan_info.order_date as as_order_req_ymd,";
            $arg_str .= "t_returned_plan_info.order_sts_kbn as as_order_sts_kbn,";
            $arg_str .= "t_order.order_reason_kbn as as_order_reason_kbn,";
            $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
            $arg_str .= "m_job_type.job_type_name as as_job_type_name,";
            $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
            $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
            $arg_str .= "t_returned_plan_info.item_cd as as_item_cd,";
            $arg_str .= "t_returned_plan_info.color_cd as as_color_cd,";
            $arg_str .= "t_returned_plan_info.size_cd as as_size_cd,";
            $arg_str .= "t_order.job_type_cd as as_job_type_cd,";
            $arg_str .= "t_order.size_two_cd as as_size_two_cd,";
            $arg_str .= "t_order.order_qty as as_order_qty,";
            $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
            $arg_str .= "t_returned_plan_info.order_date as as_re_order_date,";
            $arg_str .= "t_returned_plan_info.return_status as as_return_status,";
            $arg_str .= "t_returned_plan_info.return_date as as_return_date,";
            $arg_str .= "t_returned_plan_info.job_type_cd as as_return_job_type_cd,";
            $arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no,";
            $arg_str .= "t_delivery_goods_state.ship_no as as_ship_no,";
            $arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
            $arg_str .= "t_order_state.ship_qty as as_ship_qty,";
            $arg_str .= "t_returned_plan_info.return_qty as as_return_qty,";
            $arg_str .= "t_returned_plan_info.individual_ctrl_no as as_individual_ctrl_no,";
            $arg_str .= "t_delivery_goods_state_details.receipt_date as as_receipt_date,";
            $arg_str .= "t_returned_plan_info.return_plan_qty as as_return_plan__qty,";
            $arg_str .= "t_returned_plan_info.rntl_cont_no as as_rntl_cont_no,";
            $arg_str .= "m_contract.rntl_cont_name as as_rntl_cont_name";
            $arg_str .= " FROM t_returned_plan_info LEFT JOIN";
            $arg_str .= " (t_order LEFT JOIN";
            $arg_str .= " (t_order_state LEFT JOIN ";
            $arg_str .= " (t_delivery_goods_state LEFT JOIN t_delivery_goods_state_details ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no AND t_delivery_goods_state.ship_line_no = t_delivery_goods_state_details.ship_line_no)";
            $arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";
            $arg_str .= " ON t_order.t_order_comb_hkey = t_order_state.t_order_comb_hkey)";
            $arg_str .= " ON t_order.order_req_no = t_returned_plan_info.order_req_no";

            if ($rntl_sect_cd_zero_flg == 1) {
                $arg_str .= " INNER JOIN m_section";
                $arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
            } elseif ($rntl_sect_cd_zero_flg == 0) {
                $arg_str .= " INNER JOIN (m_section INNER JOIN m_contract_resource";
                $arg_str .= " ON m_section.corporate_id = m_contract_resource.corporate_id";
                $arg_str .= " AND m_section.rntl_cont_no = m_contract_resource.rntl_cont_no";
                $arg_str .= " AND m_section.rntl_sect_cd = m_contract_resource.rntl_sect_cd";
                $arg_str .= " ) ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
            }
            $arg_str .= " LEFT JOIN (m_job_type INNER JOIN m_input_item";
            $arg_str .= " ON m_job_type.corporate_id = m_input_item.corporate_id";
            $arg_str .= " AND m_job_type.rntl_cont_no = m_input_item.rntl_cont_no";
            $arg_str .= " AND m_job_type.job_type_cd = m_input_item.job_type_cd)";
            $arg_str .= " ON t_order.corporate_id = m_job_type.corporate_id";
            $arg_str .= " AND t_order.rntl_cont_no = m_job_type.rntl_cont_no";
            $arg_str .= " AND t_order.job_type_cd = m_job_type.job_type_cd";
            $arg_str .= " AND t_order.corporate_id = m_input_item.corporate_id";
            $arg_str .= " AND t_order.item_cd = m_input_item.item_cd";
            $arg_str .= " AND t_order.color_cd = m_input_item.color_cd";
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
                $arg_str .= $q_sort_key . " " . $order;
            }

        }else {

            //---SQLクエリー実行---//
            $arg_str = "SELECT ";
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
            $arg_str .= "t_order.job_type_cd as as_job_type_cd,";
            $arg_str .= "t_order.size_two_cd as as_size_two_cd,";
            $arg_str .= "t_order.order_qty as as_order_qty,";
            $arg_str .= "t_returned_plan_info.order_date as as_re_order_date,";
            $arg_str .= "t_returned_plan_info.return_status as as_return_status,";
            $arg_str .= "t_returned_plan_info.return_date as as_return_date,";
            $arg_str .= "t_returned_plan_info.job_type_cd as as_return_job_type_cd,";
            $arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no,";
            $arg_str .= "t_delivery_goods_state.ship_no as as_ship_no,";
            $arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
            $arg_str .= "t_order_state.ship_qty as as_ship_qty,";
            $arg_str .= "t_returned_plan_info.return_qty as as_return_qty,";
            $arg_str .= "t_returned_plan_info.individual_ctrl_no as as_individual_ctrl_no,";
            $arg_str .= "t_delivery_goods_state_details.receipt_date as as_receipt_date,";
            $arg_str .= "t_returned_plan_info.return_plan_qty as as_return_plan__qty,";
            $arg_str .= "t_returned_plan_info.rntl_cont_no as as_rntl_cont_no,";
            $arg_str .= "m_contract.rntl_cont_name as as_rntl_cont_name";
            $arg_str .= " FROM t_returned_plan_info LEFT JOIN";
            $arg_str .= " (t_order LEFT JOIN";
            $arg_str .= " (t_order_state LEFT JOIN ";
            $arg_str .= " (t_delivery_goods_state LEFT JOIN t_delivery_goods_state_details ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no AND t_delivery_goods_state.ship_line_no = t_delivery_goods_state_details.ship_line_no)";
            $arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";
            $arg_str .= " ON t_order.t_order_comb_hkey = t_order_state.t_order_comb_hkey)";
            $arg_str .= " ON t_order.order_req_no = t_returned_plan_info.order_req_no";
            $arg_str .= " AND t_order.order_req_line_no = t_returned_plan_info.order_req_line_no";
            if ($rntl_sect_cd_zero_flg == 1) {
                $arg_str .= " INNER JOIN m_section";
                $arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
            } elseif ($rntl_sect_cd_zero_flg == 0) {
                $arg_str .= " INNER JOIN (m_section INNER JOIN m_contract_resource";
                $arg_str .= " ON m_section.corporate_id = m_contract_resource.corporate_id";
                $arg_str .= " AND m_section.rntl_cont_no = m_contract_resource.rntl_cont_no";
                $arg_str .= " AND m_section.rntl_sect_cd = m_contract_resource.rntl_sect_cd";
                $arg_str .= " ) ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
            }
            $arg_str .= " LEFT JOIN (m_job_type INNER JOIN m_input_item";
            $arg_str .= " ON m_job_type.corporate_id = m_input_item.corporate_id";
            $arg_str .= " AND m_job_type.rntl_cont_no = m_input_item.rntl_cont_no";
            $arg_str .= " AND m_job_type.job_type_cd = m_input_item.job_type_cd)";
            $arg_str .= " ON t_order.corporate_id = m_job_type.corporate_id";
            $arg_str .= " AND t_order.rntl_cont_no = m_job_type.rntl_cont_no";
            $arg_str .= " AND t_order.job_type_cd = m_job_type.job_type_cd";
            $arg_str .= " AND t_order.corporate_id = m_input_item.corporate_id";
            $arg_str .= " AND t_order.item_cd = m_input_item.item_cd";
            $arg_str .= " AND t_order.color_cd = m_input_item.color_cd";
            $arg_str .= " INNER JOIN m_wearer_std";
            $arg_str .= " ON t_order.werer_cd = m_wearer_std.werer_cd";
            $arg_str .= " AND t_order.corporate_id = m_wearer_std.corporate_id";
            $arg_str .= " AND t_order.rntl_cont_no = m_wearer_std.rntl_cont_no";
            $arg_str .= " INNER JOIN m_contract";
            $arg_str .= " ON t_order.rntl_cont_no = m_contract.rntl_cont_no";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            //$arg_str .= ") as distinct_table";
            if (!empty($q_sort_key)) {
                $arg_str .= " ORDER BY ";
                $arg_str .= $q_sort_key . " " . $order;
            }
        }
		$t_order = new TOrder();
		$results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
		$results_array = (array)$results;
		$results_cnt = $results_array["\0*\0_count"];

		$list = array();
		$all_list = array();
		$json_list = array();

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
				// 発注依頼No.
				if (!empty($result->as_order_req_no)) {
					$list['order_req_no'] = $result->as_order_req_no;
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
			  $query_list[] = "job_type_cd = '".$result->as_return_job_type_cd."'";
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
                // 返却予定数
                $list['order_qty'] = '0';
                if($result->as_order_qty){
                    $list['order_qty'] = $result->as_return_plan__qty;
                }
				// 受注番号
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
                $list['return_qty'] ='0';
                if($result->as_return_qty){
                    $list['return_qty'] = $result->as_return_qty;
                }
				// 伝票番号
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
                    $receipt_date = implode(PHP_EOL, $day_list);
                    //ChromePhp::log($receipt_date);
                    $list['order_res_ymd'] = $receipt_date;
                }

                //---個体管理番号---//
                $list['individual_num'] = "-";
                $query_list = array();
                array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
                array_push($query_list, "order_req_no = '".$list['order_req_no']."'");
                array_push($query_list, "item_cd = '".$list['item_cd']."'");
                array_push($query_list, "color_cd = '".$list['color_cd']."'");
                array_push($query_list, "size_cd = '".$list['size_cd']."'");
                $query = implode(' AND ', $query_list);
                $arg_str = "";
                $arg_str .= "SELECT ";
                $arg_str .= "individual_ctrl_no";
                $arg_str .= " FROM ";
                $arg_str .= "t_returned_plan_info";
                $arg_str .= " WHERE ";
                $arg_str .= $query;
                $t_returned_plan_info = new TReturnedPlanInfo();
                $t_returned_results = new Resultset(null, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
                $result_obj = (array)$t_returned_results;
                $results_cnt3 = $result_obj["\0*\0_count"];
                if (individual_flg($auth['corporate_id'], $cond['agreement_no']) == 1) {
                    //出荷数
                    $list['ship_qty'] = $results_cnt3;
                }
                if ($results_cnt3 > 0) {
                    $paginator_model = new PaginatorModel(
                        array(
                            "data" => $t_returned_results,
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
                    $individual_ctrl_no = implode(PHP_EOL, $num_list);
                    $list['individual_num'] = $individual_ctrl_no;
                }

				array_push($all_list,$list);
			}
		}

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
		array_push($header_2, "返却予定数");
		array_push($header_2, "受注番号");
		array_push($header_2, "返却予定日");
		array_push($header_2, "返却数");
		array_push($header_2, "ステータス");
		array_push($header_2, "伝票番号");
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
				//受注番号
				array_push($csv_body_list, $all_map["rec_order_no"]);
				// 返却予定日
				array_push($csv_body_list, $all_map["re_order_date"]);
				// 返却数
				array_push($csv_body_list, $all_map["return_qty"]);
				// ステータス
				array_push($csv_body_list, $all_map["return_status_name"]);
				// 伝票番号
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
        //受領日from
        if(!empty($cond['receipt_day_from'])){
            array_push($query_list,"CAST(t_delivery_goods_state_details.receipt_date AS DATE) >= CAST('".$cond['receipt_day_from']."' AS DATE)");
        }
        //受領日to
        if(!empty($cond['receipt_day_to'])){
            array_push($query_list,"CAST(t_delivery_goods_state_details.receipt_date AS DATE) <= CAST('".$cond['receipt_day_to']."' AS DATE)");

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
        $reason_kbn_1 = array();
        $kbn_list = array();
        if($cond['order_kbn0']) {
            $chk_flg = '1';
            //貸与開始にチェックがついてたら
            $order_kbn = "t_order.order_sts_kbn = '1' AND m_wearer_std.werer_sts_kbn = '1'";
            if ($cond['reason_kbn0']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '01'");
            }
            if ($cond['reason_kbn1']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '02'");
            }
            if ($cond['reason_kbn2']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '03'");
            }
            if ($cond['reason_kbn3']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '04'");
            }
            if ($cond['reason_kbn4']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '19'");
            }
            if ($reason_kbn_1) {
                //理由区分と発注区分
                $reason_kbn_1_str = implode(' OR ', $reason_kbn_1);
                array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_1_str . "))");
            } else {
                //発注区分のみ
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '01'");
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '02'");
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '03'");
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '03'");
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '04'");
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '19'");
                $reason_kbn_1_str = implode(' OR ', $reason_kbn_1);
                array_push($kbn_list, "(" . $order_kbn . " AND (" . $reason_kbn_1_str . "))");
            }
        }else{
            //貸与開始にチェックが付いてない
            if ($cond['reason_kbn0']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '01'");
            }
            if ($cond['reason_kbn1']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '02'");
            }
            if ($cond['reason_kbn2']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '03'");
            }
            if ($cond['reason_kbn3']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '04'");
            }
            if ($cond['reason_kbn4']) {
                array_push($reason_kbn_1, "t_order.order_reason_kbn = '19'");
            }
            if ($reason_kbn_1) {
                //理由区分のみ
                $reason_kbn_1_str = implode(' OR ', $reason_kbn_1);
                array_push($kbn_list, "(".$reason_kbn_1_str .")");
            }else{
                //何もチェックなければ貸与開始を除く
                $order_kbn = "t_order.order_sts_kbn != '1'";
                array_push($query_list, $order_kbn);
            }
        }

        //交換
        $reason_kbn_2 = array();
        if($cond['order_kbn1']) {
            //交換にチェックがついてたら
            $order_kbn = "(t_order.order_sts_kbn = '3' OR t_order.order_sts_kbn = '4') AND m_wearer_std.werer_sts_kbn = '1'";
            if($cond['reason_kbn5']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '14'");
            }
            if($cond['reason_kbn6']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '15'");
            }
            if($cond['reason_kbn7']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '16'");
            }
            if($cond['reason_kbn8']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '17'");
            }
            if($cond['reason_kbn9']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '12'");
            }
            if($cond['reason_kbn10']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '13'");
            }
            if($cond['reason_kbn11']){
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
            if($cond['reason_kbn5']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '14'");
            }
            if($cond['reason_kbn6']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '15'");
            }
            if($cond['reason_kbn7']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '16'");
            }
            if($cond['reason_kbn8']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '17'");
            }
            if($cond['reason_kbn9']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '12'");
            }
            if($cond['reason_kbn10']){
                array_push($reason_kbn_2, "t_order.order_reason_kbn = '13'");
            }
            if($cond['reason_kbn11']){
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
        if($cond['order_kbn2']) {
            //異動の場合、着用者基本マスタ.着用者状況区分＝8：異動の着用者を検索する。
            //職種変更または異動にチェックがついてたら
            $order_kbn = "(t_order.order_sts_kbn = '5' AND m_wearer_std.werer_sts_kbn = '8')";
            if($cond['reason_kbn12']){
                array_push($reason_kbn_3, "t_order.order_reason_kbn = '09'");
            }
            if($cond['reason_kbn13']){
                array_push($reason_kbn_3, "t_order.order_reason_kbn = '10'");
            }
            if($cond['reason_kbn14']){
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
            if($cond['reason_kbn12']){
                array_push($reason_kbn_3, "t_order.order_reason_kbn = '09'");
            }
            if($cond['reason_kbn13']){
                array_push($reason_kbn_3, "t_order.order_reason_kbn = '10'");
            }
            if($cond['reason_kbn14']){
                array_push($reason_kbn_3, "t_order.order_reason_kbn = '11'");
            }
            if ($reason_kbn_3) {
                $order_kbn = "(t_order.order_sts_kbn = '5' AND m_wearer_std.werer_sts_kbn = '8')";
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
        if($cond['order_kbn3']) {
            //貸与終了にチェックがついてたら
            $order_kbn = "t_order.order_sts_kbn = '2'";
            if($cond['reason_kbn15']){
                //貸与終了、かつ、理由区分＝05：退職の場合、着用者基本マスタ.着用者状況区分＝4：退社の着用者を検索する。
                array_push($reason_kbn_4, "(t_order.order_reason_kbn = '05' AND m_wearer_std.werer_sts_kbn = '4')");
            }
            if($cond['reason_kbn16']){
                //貸与終了、かつ、理由区分＝06：休職の場合、着用者基本マスタ.着用者状況区分＝2:休職の着用者を検索する。
                array_push($reason_kbn_4, "(t_order.order_reason_kbn = '06' AND m_wearer_std.werer_sts_kbn = '2')");
            }
            if($cond['reason_kbn17']){
                array_push($reason_kbn_4, "t_order.order_reason_kbn = '07' AND m_wearer_std.werer_sts_kbn = '1'");
            }
            if($cond['reason_kbn18']){
                array_push($reason_kbn_4, "t_order.order_reason_kbn = '08' AND m_wearer_std.werer_sts_kbn = '1'");
            }
            if($cond['reason_kbn19']){
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
            if($cond['reason_kbn15']){
                array_push($reason_kbn_4, "(t_order.order_reason_kbn = '05' AND m_wearer_std.werer_sts_kbn = '4')");
            }
            if($cond['reason_kbn16']){
                array_push($reason_kbn_4, "(t_order.order_reason_kbn = '06' AND m_wearer_std.werer_sts_kbn = '2')");
            }
            if($cond['reason_kbn17']){
                array_push($reason_kbn_4, "t_order.order_reason_kbn = '07' AND m_wearer_std.werer_sts_kbn = '1'");
            }
            if($cond['reason_kbn18']){
                array_push($reason_kbn_4, "t_order.order_reason_kbn = '08' AND m_wearer_std.werer_sts_kbn = '1'");
            }
            if($cond['reason_kbn19']){
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
        if($cond['order_kbn4']){
            array_push($kbn_list,"t_order.order_sts_kbn = '9' AND m_wearer_std.werer_sts_kbn = '1'");
        }

        //区分を検索条件に追加
        if($kbn_list){
            array_push($query_list,'('.implode(' OR ', $kbn_list).')');
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
			// 伝票番号
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
			// 受注番号
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
        $arg_str .= " AND t_order.corporate_id = m_wearer_std.corporate_id";
        $arg_str .= " AND t_order.rntl_cont_no = m_wearer_std.rntl_cont_no";
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

		$t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
		$results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
		$result_obj = (array)$results;
		$results_cnt = $result_obj["\0*\0_count"];

		$list = array();
		$all_list = array();
		$json_list = array();

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

			foreach($results as $result){
				// 受領ステータス
				$list['receipt_status'] = $result->as_receipt_status;
				// 受領日
				$list['receipt_date'] = $result->as_receipt_date;
				// 伝票番号
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

                //---個体管理番号・受領日時の取得---//
                $list['individual_num'] = "-";
                $query_list = array();
                array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
                array_push($query_list, "ship_no = '".$list['ship_no']."'");
                array_push($query_list, "item_cd = '".$list['item_cd']."'");
                array_push($query_list, "color_cd = '".$list['color_cd']."'");
                //rray_push($query_list, "size_cd = '".$list['size_cd']."'");
                $query = implode(' AND ', $query_list);
                $arg_str = "";
                $arg_str .= "SELECT ";
                $arg_str .= "individual_ctrl_no";
                $arg_str .= " FROM ";
                $arg_str .= "t_delivery_goods_state_details";
                $arg_str .= " WHERE ";
                $arg_str .= $query;
                $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
                $del_gd_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
                $result_obj = (array)$del_gd_results;
                $del_gd_results_cnt = $result_obj["\0*\0_count"];
                if ($del_gd_results_cnt > 0) {
                    $paginator_model = new PaginatorModel(
                        array(
                            "data"  => $del_gd_results,
                            "limit" => $del_gd_results_cnt,
                            "page" => 1
                        )
                    );
                    $paginator = $paginator_model->getPaginate();
                    $del_gd_results = $paginator->items;

                    $num_list = array();
                    $day_list = array();
                    foreach ($del_gd_results as $del_gd_result) {
                        array_push($num_list, $del_gd_result->individual_ctrl_no);
                    }
                    // 個体管理番号
                    $individual_ctrl_no = implode(PHP_EOL, $num_list);
                    $list['individual_num'] = $individual_ctrl_no;
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
				// 受注番号
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

		//---CSV出力---//
		$csv_datas = array();

		// ヘッダー作成
		$header_1 = array(
			'抽出件数：'.$results_cnt.'件'
		);
		array_push($csv_datas, $header_1);

		$header_2 = array();
		array_push($header_2, "受領日");
		array_push($header_2, "伝票番号");
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
		array_push($header_2, "受注番号");
		array_push($csv_datas, $header_2);

		// ボディ作成
		if (!empty($all_list)) {
			foreach ($all_list as $all_map) {
				$csv_body_list = array();
				// 受領日
				array_push($csv_body_list, $all_map["receipt_date"]);
				// 伝票番号
				array_push($csv_body_list, $all_map["ship_no"]);
				// 商品-色(サイズ-サイズ2)
				array_push($csv_body_list, $all_map["shin_item_code"]);
				// 商品名
				array_push($csv_body_list, $all_map["input_item_name"]);
				// 出荷数
				array_push($csv_body_list, $all_map["ship_qty"]);
				// 個体管理番号
                /*
				if ($individual_flg) {
					array_push($csv_body_list, $all_map["individual_ctrl_no"]);
				}*/
                // 個体管理番号
                if ($individual_flg) {
                    array_push($csv_body_list, $all_map["individual_num"]);
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
				// 受注番号
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
		// 着用者状況区分(稼働)
		array_push($query_list,"m_wearer_std.werer_sts_kbn = '1'");

        //納品状況明細情報 数量　<> 納品状況明細情報 返却済数
        array_push($query_list,"NOT EXISTS (SELECT * FROM t_delivery_goods_state_details as TS WHERE t_delivery_goods_state_details.quantity = t_delivery_goods_state_details.returned_qty)");

        //ゼロ埋めがない場合、ログインアカウントの条件追加
        if($rntl_sect_cd_zero_flg == 0){
            array_push($query_list,"m_contract_resource.accnt_no = '$accnt_no'");
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
			// 受注番号
			if($sort_key == 'maker_rec_no'){
				$q_sort_key = 'as_rec_order_no';
			}
			// 伝票番号
			if($sort_key == 'maker_send_no'){
				$q_sort_key = 'as_ship_no';
			}
		} else {
			//指定がなければ社員番号
			$q_sort_key = "as_cster_emply_cd";
			$order = 'asc';
		}

        //商品cd、色cd単位でdistinct
        //---SQLクエリー実行---//
        $arg_str = "SELECT ";
        $arg_str .= " * ";
        $arg_str .= " FROM ";
        $arg_str .= "(SELECT distinct on (m_wearer_item.item_cd,m_wearer_item.color_cd,m_wearer_item.size_cd,t_delivery_goods_state.ship_no) ";
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
        $arg_str .= "t_delivery_goods_state_details.quantity as as_quantity,";
        $arg_str .= "t_delivery_goods_state_details.returned_qty as as_returned_qty,";

        $arg_str .= "t_delivery_goods_state.ship_qty as as_ship_qty,";
        $arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
        $arg_str .= "t_returned_plan_info.order_date as as_re_order_date,";
        $arg_str .= "t_order.order_req_no as as_order_req_no,";
        $arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no,";
        $arg_str .= "t_delivery_goods_state.ship_no as as_ship_no";
        $arg_str .= " FROM t_order LEFT JOIN";
        $arg_str .= " (t_order_state LEFT JOIN";
        $arg_str .= " (t_delivery_goods_state LEFT JOIN";
        $arg_str .= " t_delivery_goods_state_details";
        $arg_str .= " ON t_delivery_goods_state.corporate_id = t_delivery_goods_state_details.corporate_id";
        $arg_str .= " AND t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no";
        $arg_str .= " AND t_delivery_goods_state.ship_line_no = t_delivery_goods_state_details.ship_line_no)";
        $arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";
        $arg_str .= " ON t_order.t_order_comb_hkey = t_order_state.t_order_comb_hkey";
        $arg_str .= " LEFT JOIN t_returned_plan_info";
        $arg_str .= " ON t_order.corporate_id = t_returned_plan_info.corporate_id";
        $arg_str .= " AND t_order.order_req_no = t_returned_plan_info.order_req_no";
        $arg_str .= " AND t_order.order_req_line_no = t_returned_plan_info.order_req_line_no";
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
        $arg_str .= " INNER JOIN m_wearer_std";
        $arg_str .= " ON t_order.corporate_id = m_wearer_std.corporate_id";
        $arg_str .= " AND t_order.rntl_cont_no = m_wearer_std.rntl_cont_no";
        $arg_str .= " AND t_order.werer_cd = m_wearer_std.werer_cd";
        $arg_str .= " INNER JOIN m_wearer_item";
        $arg_str .= " ON t_order.m_wearer_item_comb_hkey = m_wearer_item.m_wearer_item_comb_hkey";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= ") as distinct_table";
        if (!empty($q_sort_key)) {
            $arg_str .= " ORDER BY ";
            $arg_str .= $q_sort_key." ".$order;
        }        //ChromePhp::log($arg_str);
		$t_order = new TOrder();
		$results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
		// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
		$result_obj = (array)$results;
		$results_cnt = $result_obj["\0*\0_count"];

		$list = array();
		$all_list = array();
		$json_list = array();

		if(!empty($results_cnt)) {
			$paginator_model = new PaginatorModel(
				array(
					"data"  => $results,
					"limit" => $results_cnt,
					"page" => 1
				)
			);
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
				// 受注番号
				if (!empty($result->as_rec_order_no)) {
					$list['rec_order_no'] = $result->as_rec_order_no;
				} else {
					$list['rec_order_no'] = "-";
				}
				// 伝票番号
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
                array_push($search_q, "job_type_cd = '".$list['old_job_type_cd']."'");
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
                //---個体管理番号・受領日時の取得---//
                $list['individual_num'] = "-";
                $list['order_res_ymd'] = "-";
                $query_list = array();
                array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
                array_push($query_list, "ship_no = '".$list['ship_no']."'");
                array_push($query_list, "item_cd = '".$list['item_cd']."'");
                array_push($query_list, "color_cd = '".$list['color_cd']."'");
                array_push($query_list, "size_cd = '".$list['size_cd']."'");
                $query = implode(' AND ', $query_list);
                $arg_str = "";
                $arg_str .= "SELECT ";
                $arg_str .= "individual_ctrl_no,";
                $arg_str .= "receipt_date";
                $arg_str .= " FROM ";
                $arg_str .= "t_delivery_goods_state_details";
                $arg_str .= " WHERE ";
                $arg_str .= $query;
                $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
                $del_gd_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
                $result_obj = (array)$del_gd_results;
                $del_gd_results_cnt = $result_obj["\0*\0_count"];
                if ($del_gd_results_cnt > 0) {
                    $paginator_model = new PaginatorModel(
                        array(
                            "data"  => $del_gd_results,
                            "limit" => $del_gd_results_cnt,
                            "page" => 1
                        )
                    );
                    $paginator = $paginator_model->getPaginate();
                    $del_gd_results = $paginator->items;

                    $num_list = array();
                    $day_list = array();
                    foreach ($del_gd_results as $del_gd_result) {
                        array_push($num_list, $del_gd_result->individual_ctrl_no);
                        if ($del_gd_result->receipt_date !== null) {
                            array_push($day_list, date('Y/m/d',strtotime($del_gd_result->receipt_date)));
                        } else {
                            array_push($day_list, "-");
                        }
                    }
                    // 個体管理番号
                    $individual_ctrl_no = implode('"'.PHP_EOL.'"', $num_list);
                    $list['individual_num'] = $individual_ctrl_no;
                    //ChromePhp::log($list['individual_num']);
                    // 受領日,
                    $receipt_date = implode(PHP_EOL, $day_list);
                    $list['order_res_ymd'] = $receipt_date;
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
		array_push($header_2, "受注番号");
		array_push($header_2, "伝票番号");
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
                /*
				if ($individual_flg) {
					array_push($csv_body_list, '="'.$all_map["individual_ctrl_no"].'"');
				}*/
                // 個体管理番号
                if ($individual_flg) {
                    array_push($csv_body_list, $all_map["individual_num"]);
                }
				// 出荷数
				array_push($csv_body_list, $all_map["ship_qty"]);
				// 出荷日
				array_push($csv_body_list, $all_map["ship_ymd"]);
				// 返却予定日
				array_push($csv_body_list, $all_map["re_order_date"]);
				// 発注No
				array_push($csv_body_list, $all_map["order_req_no"]);
				// 受注番号
				array_push($csv_body_list, $all_map["rec_order_no"]);
				// 伝票番号
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


	//--在庫照会CSVダウンロード--//
	if ($cond["ui_type"] === "stock") {
		$result["csv_code"] = "0005";

		//---在庫照会検索処理---//
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

		//---SQLクエリー実行---//
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
    //ChromePhp::log($arg_str);
		$t_sdmzk = new TSdmzk();
		$results = new Resultset(null, $t_sdmzk, $t_sdmzk->getReadConnection()->query($arg_str));
		$result_obj = (array)$results;
		$results_cnt = $result_obj["\0*\0_count"];
		$paginator_model = new PaginatorModel(
			array(
				"data"  => $results,
				"limit" => $results_cnt,
				"page" => 1
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

		//---CSV出力---//
		$csv_datas = array();

		// ヘッダー作成
		$header_1 = array(
			'抽出件数：'.$results_cnt.'件'
		);
		array_push($csv_datas, $header_1);

		$header_2 = array();
		array_push($header_2, "商品-色(サイズ-サイズ2)");
		array_push($header_2, "商品名");
		array_push($header_2, "在庫状態");
		array_push($header_2, "倉庫コード");
		array_push($header_2, "ラベル");
		array_push($header_2, "在庫数(総数)");
		array_push($header_2, "在庫数(新品)");
		array_push($header_2, "在庫数(中古)");
		array_push($header_2, "返却処理中");
		array_push($header_2, "返却予定");
		array_push($header_2, "貸与中");
		array_push($header_2, "その他出荷");
		array_push($header_2, "廃棄済み");
		array_push($csv_datas, $header_2);

		// ボディ作成
		if (!empty($all_list)) {
			foreach ($all_list as $all_map) {
				$csv_body_list = array();
				// 商品-色(サイズ-サイズ2)
				array_push($csv_body_list, $all_map["shin_item_code"]);
				// 商品名
				array_push($csv_body_list, $all_map["item_name"]);
				// 在庫状態
				array_push($csv_body_list, $all_map["zk_status_name"]);
				// 倉庫コード
				array_push($csv_body_list, $all_map["zkwhcd"]);
				// ラベル
				array_push($csv_body_list, $all_map["label"]);
				// 在庫数(総数)
				array_push($csv_body_list, $all_map["total_qty"]);
				// 在庫数(新品)
				array_push($csv_body_list, $all_map["new_qty"]);
				// 在庫数(中古)
				array_push($csv_body_list, $all_map["used_qty"]);
				// 返却処理中
				array_push($csv_body_list, $all_map["rtn_proc_qty"]);
				// 返却予定
				array_push($csv_body_list, $all_map["rtn_plan_qty"]);
				// 貸与中
				array_push($csv_body_list, $all_map["in_use_qty"]);
				// その他出荷
				array_push($csv_body_list, $all_map["other_ship_qty"]);
				// 廃棄済み
				array_push($csv_body_list, $all_map["discarded_qty"]);

				// CSVレコード配列にマージ
				array_push($csv_datas, $csv_body_list);
			}
		}

		// CSVデータ書き込み
		$file_name = "stock_".date("YmdHis", time()).".csv";
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".$file_name);

		$fp = fopen('php://output','w');
		foreach ($csv_datas as $csv_data) {
			mb_convert_variables("SJIS-win", "UTF-8", $csv_data);
			fputcsv($fp, $csv_data);
		}

		fclose($fp);
	}
	//--在庫照会CSVダウンロード ここまで--//

});
