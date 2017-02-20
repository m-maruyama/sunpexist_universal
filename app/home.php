<?php
//use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * ホーム
 */
$app->post('/home', function ()use($app){

    $list = array();
    $all_list = array();
    $json_list = array();
    $auth = $app->session->get("auth");
    $corporate_id = $app->session->get("auth")['corporate_id'];

    //お知らせ
    $now = date( "Y/m/d H:i:s", time() );
    $results = TInfo::find(array(
        "conditions" => "open_date < ?1 AND close_date > ?1",
        "bind"	=> array(1 => $now),
        'order'	  => "display_order asc"
    ));
    // $results = TInfo::find();
    foreach ($results as $result) {
        // $list['open_date'] = date('Y/m/d H:i',strtotime($result->open_date));
        // $list['message'] = '・'.nl2br(htmlspecialchars( $result->message, ENT_QUOTES, 'UTF-8'));
        $list['message'] = '・'.$result->message;
        array_push($all_list,$list);
    }
    $json_list['info_list'] = $all_list;

    //ボタン表示非表示制御
    if($app->session->get("auth")['button1_use_flg']==1){$json_list['button1_use_flg']=1;};
    if($app->session->get("auth")['button2_use_flg']==1){$json_list['button2_use_flg']=1;};
    if($app->session->get("auth")['button3_use_flg']==1){$json_list['button3_use_flg']=1;};
    if($app->session->get("auth")['button4_use_flg']==1){$json_list['button4_use_flg']=1;};
    if($app->session->get("auth")['button5_use_flg']==1){$json_list['button5_use_flg']=1;};
    if($app->session->get("auth")['button6_use_flg']==1){$json_list['button6_use_flg']=1;};
    if($app->session->get("auth")['button7_use_flg']==1){$json_list['button7_use_flg']=1;};
    if($app->session->get("auth")['button8_use_flg']==1){$json_list['button8_use_flg']=1;};
    if($app->session->get("auth")['button9_use_flg']==1){$json_list['button9_use_flg']=1;};
    if($app->session->get("auth")['button10_use_flg']==1){$json_list['button10_use_flg']=1;};
    if($app->session->get("auth")['button11_use_flg']==1){$json_list['button11_use_flg']=1;};
    if($app->session->get("auth")['button12_use_flg']==1){$json_list['button12_use_flg']=1;};
    if($app->session->get("auth")['button13_use_flg']==1){$json_list['button13_use_flg']=1;};
    if($app->session->get("auth")['button14_use_flg']==1){$json_list['button14_use_flg']=1;};
    if($app->session->get("auth")['button15_use_flg']==1){$json_list['button15_use_flg']=1;};
    if($app->session->get("auth")['button16_use_flg']==1){$json_list['button16_use_flg']=1;};
    if($app->session->get("auth")['button17_use_flg']==1){$json_list['button17_use_flg']=1;};
    if($app->session->get("auth")['button18_use_flg']==1){$json_list['button18_use_flg']=1;};
    if($app->session->get("auth")['button19_use_flg']==1){$json_list['button19_use_flg']=1;};
    if($app->session->get("auth")['button20_use_flg']==1){$json_list['button20_use_flg']=1;};
    if($app->session->get("auth")['button21_use_flg']==1){$json_list['button21_use_flg']=1;};
    if($app->session->get("auth")['button22_use_flg']==1){$json_list['button22_use_flg']=1;};
    if($app->session->get("auth")['button23_use_flg']==1){$json_list['button23_use_flg']=1;};
    if($app->session->get("auth")['button24_use_flg']==1){$json_list['button24_use_flg']=1;};
    if($app->session->get("auth")['button25_use_flg']==1){$json_list['button25_use_flg']=1;};
    if($app->session->get("auth")['button26_use_flg']==1){$json_list['button26_use_flg']=1;};
    if($app->session->get("auth")['button27_use_flg']==1){$json_list['button27_use_flg']=1;};
    if($app->session->get("auth")['button28_use_flg']==1){$json_list['button28_use_flg']=1;};
    if($app->session->get("auth")['button29_use_flg']==1){$json_list['button29_use_flg']=1;};
    if($app->session->get("auth")['button30_use_flg']==1){$json_list['button30_use_flg']=1;};

    //document処理
    //ChromePhp::log(DOCUMENT_UPLOAD.$corporate_id.'/meta.txt');
    //ChromePhp::log(file_exists(DOCUMENT_UPLOAD.$corporate_id.'/meta.txt'));
    if(file_exists(DOCUMENT_UPLOAD.$corporate_id.'/meta.txt')){

        //企業idディレクトリ内のメタ.txtを取得
        $fileName = DOCUMENT_UPLOAD.$corporate_id.'/meta.txt';

        $file = file($fileName);
        mb_convert_variables("UTF-8", "SJIS-win", $file);

        //$chk_file = $file;
        //unset($chk_file[0]); //チェック時はヘッダーを無視する
        $tmp_manual_list = array();
        $manual_list = array();

        if(count($file) > 0){
        foreach($file as $item){
            $tmp_manual_list[] = explode(',',$item);
        }
        foreach($tmp_manual_list as $list){
            $manual_list[] = array(
                'name' => $list[0],
                'file' => preg_replace('/\r\n/', '', $list[1]),
                'corporate' => $corporate_id
            );
        }
        //ChromePhp::log($manual_list);
        $json_list['manual_list'] = $manual_list;
        }
    }
    json_encode($json_list);
    echo json_encode($json_list);
});

$app->post('/home_count', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);

    $all_list = array();
    $json_list = array();
    $auth = $app->session->get("auth");
    $corporate_id = $app->session->get("auth")['corporate_id'];
    $accnt_no = $auth['accnt_no'];
    $emply_cd_no_regist_cnt = 0;
    $results_cnt_list = 0;
    $no_return_cnt = 0;

    $rntl_cont_no_array = $params['rntl_cont_no'];

    //アカウントが所持する契約no単位で処理を行う。
    foreach ($rntl_cont_no_array as $rntl_cont_no) {//---契約リソースマスター 0000000000フラグ確認処理---//
        //前処理 契約リソースマスタ参照 拠点ゼロ埋め確認
        $arg_str = "";
        $arg_str .= "SELECT ";
        $arg_str .= " * ";
        $arg_str .= " FROM ";
        $arg_str .= "m_contract_resource";
        $arg_str .= " WHERE ";
        $arg_str .= "corporate_id = '$corporate_id'";
        $arg_str .= " AND rntl_cont_no = '$rntl_cont_no'";
        $arg_str .= " AND accnt_no = '$accnt_no'";

        $m_contract_resource = new MContractResource();
        $results = new Resultset(null, $m_contract_resource, $m_contract_resource->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
        if ($results_cnt > 0) {
            $paginator_model = new PaginatorModel(
            array(
            "data" => $results,
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
        } else {
            $rntl_sect_cd_zero_flg = 0;
        }


        //発注未送信
        $query_list = array();
        $query_list[] = "m_wearer_std_tran.corporate_id = '" . $auth['corporate_id'] . "'";
        $query_list[] = "m_wearer_std_tran.rntl_cont_no = '" . $rntl_cont_no . "'";

        if (!$rntl_sect_cd_zero_flg) {
            //ゼロ埋めがない場合、ログインアカウントの条件追加
                if ($all_list > 0) {
                    $order_section = array();
                    $all_list_count = count($all_list);
                    for ($i = 0; $i < $all_list_count; $i++) {
                        //着用者区分
                        array_push($order_section, $all_list[$i]);
                    }
                    if (!empty($order_section)) {
                        $order_section_str = implode("','", $order_section);
                        $order_section_query = "t_order_tran.order_rntl_sect_cd IN ('" . $order_section_str . "')";
                    }
                    $rntl_accnt_no = "(m_contract_resource.accnt_no = '" . $auth['accnt_no'] . "'";
                    $accnt_no_and_order_section = $rntl_accnt_no . " OR " . $order_section_query . ")";
                }
                array_push($query_list, "$accnt_no_and_order_section");
        }
        $query_list[] = "m_wearer_std_tran.snd_kbn = '0'";

        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str .= "SELECT ";
        $arg_str .= " * ";
        $arg_str .= " FROM ";
        $arg_str .= "(SELECT distinct on (m_wearer_std_tran.order_req_no) ";
        $arg_str .= "m_wearer_std_tran.order_req_no as as_wst_order_req_no,";
        $arg_str .= "m_wearer_std_tran.werer_cd as as_werer_cd,";
        $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
        $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
        $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";
        $arg_str .= "m_wearer_std_tran.order_sts_kbn as as_wst_order_sts_kbn,";
        $arg_str .= "m_wearer_std_tran.snd_kbn as as_wst_snd_kbn,";
        $arg_str .= "m_wearer_std_tran.corporate_id as as_corporate_id,";
        $arg_str .= "m_wearer_std_tran.rntl_cont_no as as_rntl_cont_no,";
        $arg_str .= "m_wearer_std_tran.rntl_sect_cd as as_rntl_sect_cd,";
        $arg_str .= "m_wearer_std_tran.job_type_cd as as_job_type_cd,";
        $arg_str .= "m_wearer_std_tran.rgst_date as as_rgst_date,";
        $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
        $arg_str .= "m_job_type.job_type_name as as_job_type_name,";
        $arg_str .= "t_order_tran.order_req_no as as_order_req_no,";
        $arg_str .= "t_order_tran.order_req_ymd as as_order_req_ymd,";
        $arg_str .= "t_order_tran.order_sts_kbn as as_order_sts_kbn,";
        $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";
        $arg_str .= "t_order_tran.snd_kbn as as_snd_kbn,";
        $arg_str .= "t_returned_plan_info_tran.order_req_no as as_rtn_order_req_no";
        $arg_str .= " FROM ";
        $arg_str .= "(m_wearer_std_tran";
        if ($rntl_sect_cd_zero_flg) {
            $arg_str .= " LEFT JOIN ";
            $arg_str .= "m_section";
            $arg_str .= " ON (m_wearer_std_tran.corporate_id = m_section.corporate_id";
            $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = m_section.rntl_cont_no";
            $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = m_section.rntl_sect_cd)";
        } else {
            $arg_str .= " LEFT JOIN ";
            $arg_str .= "(m_section";
            $arg_str .= " LEFT JOIN m_contract_resource";
            $arg_str .= " ON m_section.corporate_id = m_contract_resource.corporate_id";
            $arg_str .= " AND m_section.rntl_cont_no = m_contract_resource.rntl_cont_no";
            $arg_str .= " AND m_section.rntl_sect_cd = m_contract_resource.rntl_sect_cd)";
            $arg_str .= " ON m_wearer_std_tran.corporate_id = m_section.corporate_id";
            $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = m_section.rntl_cont_no";
            $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = m_section.rntl_sect_cd";
        }
        $arg_str .= " INNER JOIN m_job_type";
        $arg_str .= " ON (m_wearer_std_tran.corporate_id = m_job_type.corporate_id";
        $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = m_job_type.rntl_cont_no";
        $arg_str .= " AND m_wearer_std_tran.job_type_cd = m_job_type.job_type_cd))";
        $arg_str .= " LEFT JOIN ";
        $arg_str .= "t_order_tran";
        $arg_str .= " ON m_wearer_std_tran.order_req_no = t_order_tran.order_req_no";
        $arg_str .= " LEFT JOIN ";
        $arg_str .= "t_returned_plan_info_tran";
        $arg_str .= " ON m_wearer_std_tran.order_req_no = t_returned_plan_info_tran.order_req_no";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= ") as distinct_table";
        $arg_str .= " ORDER BY as_wst_order_req_no ASC";
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];


        if (!empty($results_cnt)) {
            $emply_cd_no_regist_list = array();

            foreach ($results as $result) {
                // 発注区分=貸与で発注情報トランのデータが存在しない場合は対象外とする
                if ($result->as_wst_order_sts_kbn == "1" && empty($result->as_order_req_no)) {
                    continue;
                }
                $list['order_req_no'] = $result->as_order_req_no;
                // 着用者コード
                $list['werer_cd'] = $result->as_werer_cd;
                // 発注状況区分
                $list['order_sts_kbn'] = $result->as_wst_order_sts_kbn;
                $emply_cd_no_regist_list[] = $list;
                //発注未送信件数
            }
        }
        if (count($emply_cd_no_regist_list) > 0) {
            $emply_cd_no_regist_cnt += count($emply_cd_no_regist_list);
        }
        //未受領
        $query_list = array();

        //企業ID
        array_push($query_list, "t_delivery_goods_state_details.corporate_id = '" . $auth['corporate_id'] . "'");
        //契約No
        array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '" . $rntl_cont_no . "'");
        array_push($query_list, "t_delivery_goods_state_details.receipt_status = '1'");

        //ゼロ埋めがない場合、ログインアカウントの条件追加
        if ($rntl_sect_cd_zero_flg == 0) {
            array_push($query_list, "m_contract_resource.accnt_no = '$accnt_no'");
        }
        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $query_list);

        // 発注区分=貸与で発注情報トランのデータが存在しない場合は対象外とする
        // パターン１ 発注区分 = 貸与 発注トランに発注番号を省いた件数 未送信
        //---SQLクエリー実行---//
        $arg_str = "SELECT ";
        $arg_str .= " * ";
        $arg_str .= " FROM ";
        $arg_str .= "(SELECT distinct on ";
        $arg_str .= "(t_delivery_goods_state_details.ship_no,";
        $arg_str .= "t_delivery_goods_state_details.ship_line_no) ";
        $arg_str .= "*";
        $arg_str .= " FROM t_delivery_goods_state_details INNER JOIN";
        $arg_str .= " (t_delivery_goods_state INNER JOIN";
        $arg_str .= " (t_order_state INNER JOIN (t_order";
        if ($rntl_sect_cd_zero_flg == 1) {
            $arg_str .= " INNER JOIN m_section";
            $arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";
        } else if ($rntl_sect_cd_zero_flg == 0) {
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

        //ChromePhp::log($arg_str);
        $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
        $results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt_list += $result_obj["\0*\0_count"];

        $query_list = array();


        //未返却
        //ゼロ埋めがない場合、ログインアカウントの条件追加
        if ($rntl_sect_cd_zero_flg == 0) {
            $section_list = array();
            $section_query = "(";
            foreach ($all_list as $rntl_sect_cd) {
                array_push($section_list,"t_returned_plan_info.rntl_sect_cd = '".$rntl_sect_cd."'");
                array_push($section_list,"m_wearer_std.rntl_sect_cd = '".$rntl_sect_cd."'");

            }
            $section_query .= implode(' OR ' , $section_list);
            $section_query .= ")";
            array_push($query_list, $section_query);

//            array_push($query_list, "m_contract_resource.accnt_no = '$accnt_no'");
        }

        array_push($query_list, "t_returned_plan_info.corporate_id = '" . $auth['corporate_id'] . "'");
        array_push($query_list, "t_returned_plan_info.rntl_cont_no = '" . $rntl_cont_no . "'");
        array_push($query_list, "t_returned_plan_info.return_status = '1'");
        //array_push($query_list, "(t_order.order_sts_kbn = '3' OR t_order.order_sts_kbn = '4')");

        $query = implode(' AND ', $query_list);
        if (individual_flg($auth['corporate_id'], $rntl_cont_no) == 1) {
            //---SQLクエリー実行---//
            $arg_str = "SELECT ";
            $arg_str .= " * ";
            $arg_str .= " FROM ";
            $arg_str .= "(SELECT distinct on (t_returned_plan_info.order_req_no, t_returned_plan_info.item_cd, t_returned_plan_info.color_cd, t_returned_plan_info.size_cd) ";
            $arg_str .= "*";
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
            $arg_str .= " INNER JOIN m_wearer_std";
            $arg_str .= " ON t_returned_plan_info.werer_cd = m_wearer_std.werer_cd";
            $arg_str .= " AND t_returned_plan_info.corporate_id = m_wearer_std.corporate_id";
            $arg_str .= " AND t_returned_plan_info.rntl_cont_no = m_wearer_std.rntl_cont_no";
            $arg_str .= " INNER JOIN m_contract";
            $arg_str .= " ON t_returned_plan_info.rntl_cont_no = m_contract.rntl_cont_no";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $arg_str .= ") as distinct_table";
        } else {
            //---SQLクエリー実行---//
            $arg_str = "SELECT ";
            $arg_str .= "*";
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
            $arg_str .= " INNER JOIN m_wearer_std";
            $arg_str .= " ON t_returned_plan_info.werer_cd = m_wearer_std.werer_cd";
            $arg_str .= " AND t_returned_plan_info.corporate_id = m_wearer_std.corporate_id";
            $arg_str .= " AND t_returned_plan_info.rntl_cont_no = m_wearer_std.rntl_cont_no";
            $arg_str .= " INNER JOIN m_contract";
            $arg_str .= " ON t_returned_plan_info.rntl_cont_no = m_contract.rntl_cont_no";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
        }
        $t_order = new TOrder();
        $results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
        $no_return_cnt += $results_cnt;
    }

    $json_list['emply_cd_no_regist_cnt'] = $emply_cd_no_regist_cnt;
    $json_list['no_recieve_cnt'] = $results_cnt_list;
    $json_list['no_return_cnt'] = $no_return_cnt;

    json_encode($json_list);
    echo json_encode($json_list);

});


/*
 * 契約noの個数をチェックするAPI
 *
 *
 *
 *
 *
 */
$app->post('/check/agreement_no', function ()use($app) {
    // アカウントセッション取得
    $auth = $app->session->get("auth");

    $json_list = array();
    $all_list = array();
    $corporate_id = $auth['corporate_id'];
    $accnt_no = $auth['accnt_no'];

    //前処理 契約リソースマスタ参照 拠点ゼロ埋め確認
    $arg_str = "";
    $arg_str .= "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (rntl_cont_no)";
    $arg_str .= " * ";
    $arg_str .= "FROM m_contract_resource";
    $arg_str .= " WHERE ";
    $arg_str .= "corporate_id = '$corporate_id'";
    $arg_str .= " AND accnt_no = '$accnt_no'";
    $arg_str .= ") as distinct_table";
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
            $all_list[] = $result->rntl_cont_no;
        }
    }

    $json_list['rntl_cont_no'] = $all_list;


    json_encode($json_list);
    echo json_encode($json_list);
});


$app->post('/home_manual', function ()use($app){

    $params = json_decode($_POST['data'], true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    $cond = $params['cond'];

    $filename = attachmentFileName($cond['name']);

    //ファイルの存在を確認し処理を実行
    header("Content-Type: application/octet-stream");
    if(file_exists(DOCUMENT_UPLOAD.$cond['corporate']."/".$cond['file'])){
        //拡張子取り出し
        $ext = substr(strrchr($cond['file'], '.'), 0);
        //実体ファイルセット
        $fileName = DOCUMENT_UPLOAD.$cond['corporate']."/".$cond['file'];
        //ファイル名セット
        header("Content-Disposition: attachment; filename=".$filename.$ext);
    }else{
        //実体ファイルがない場合はこちらの処理
        $fileName = DOCUMENT_UPLOAD."/file_no.pdf";
        header("Content-Disposition: attachment; filename=nofile.pdf");
    }
    //ファイルのダウンロード
    readfile($fileName);
});


function attachmentFileName($fileName)
{
    $outputFilename = $fileName;
    $outputFilename = str_replace([' ', '\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $outputFilename);
    if(mb_convert_encoding($outputFilename, "US-ASCII", "UTF-8") == $outputFilename) {
        $outputFilename = rawurlencode($outputFilename);
    }else{
        $ua = $_SERVER['HTTP_USER_AGENT'];

        if (strpos($ua, 'MSIE') !== false && strpos($ua, 'Opera') === false) {
            $outputFilename = mb_convert_encoding($outputFilename, "SJIS-win", "UTF-8");
        } elseif (strpos($ua, 'Firefox') !== false ||
            strpos($ua, "Chrome") !== false ||
            strpos($ua, 'Opera') !== false
        ) {
            //$outputFilename = '=?UTF-8?B?' . base64_encode($outputFilename) . '?=';
        } elseif (strpos($ua, "Safari") !== false ) {
        } else {
            $outputFilename = mb_convert_encoding($outputFilename, "SJIS-win", "UTF-8");
        }
    }
    return $outputFilename;
}
