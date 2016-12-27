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
    $rntl_cont_no = $app->session->get("auth")['rntl_cont_no'];



    //---契約リソースマスター 0000000000フラグ確認処理---//
    //ログインid
    $login_id_session = $corporate_id;
    //アカウントno
    $accnt_no = $auth['accnt_no'];
    //画面で選択された契約no
    $agreement_no = $rntl_cont_no;

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


    //発注未送信件数

    // 発注区分=貸与で発注情報トランのデータが存在しない場合は対象外とする
    // パターン１ 発注区分 = 貸与 発注トランに発注番号を省いた件数 未送信
    $arg_str = "";
    $arg_str .= "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (T2.order_req_no) ";
    $arg_str .= "T2.order_req_no as as_wst_order_req_no,";
    $arg_str .= "T2.order_sts_kbn as as_wst_order_sts_kbn,";
    $arg_str .= "T2.snd_kbn as as_snd_kbn,";
    $arg_str .= "T1.corporate_id as as_corporate_id,";
    $arg_str .= "T1.rntl_cont_no as as_rntl_cont_no";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT * FROM m_wearer_std_tran WHERE order_sts_kbn = '1') as T2";
    $arg_str .= " INNER JOIN (SELECT * FROM t_order_tran) as T1";
    $arg_str .= " ON T2.order_req_no = T1.order_req_no";
    if ($rntl_sect_cd_zero_flg == 1){
        $arg_str .= " INNER JOIN m_section";
        $arg_str .= " ON T1.m_section_comb_hkey = m_section.m_section_comb_hkey";
    } else if ($rntl_sect_cd_zero_flg == 0){
        $arg_str .= " INNER JOIN m_contract_resource";
        $arg_str .= " ON T2.corporate_id = m_contract_resource.corporate_id";
        $arg_str .= " AND T2.rntl_cont_no = m_contract_resource.rntl_cont_no";
        $arg_str .= " AND T2.rntl_sect_cd = m_contract_resource.rntl_sect_cd";
    }

    $arg_str .= " WHERE ";
    $arg_str .= "T2.snd_kbn = '0'";
    $arg_str .= " AND T1.corporate_id = '".$corporate_id."'";
    $arg_str .= " AND T1.rntl_cont_no = '".$rntl_cont_no."'";
    if($rntl_sect_cd_zero_flg == 0){
        $arg_str .= "AND m_contract_resource.accnt_no = '$accnt_no'";
    }
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_wst_order_req_no ASC";
    ChromePhp::log($arg_str);
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];

    // パターン２　発注区分 = 貸与以外 未送信
    $arg_str = "";
    $arg_str .= "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (T2.order_req_no) ";
    $arg_str .= "T2.order_req_no as as_wst_order_req_no,";
    $arg_str .= "T2.order_sts_kbn as as_wst_order_sts_kbn,";
    $arg_str .= "T2.snd_kbn as as_snd_kbn,";
    $arg_str .= "T1.order_req_no as as_order_req_no,";
    $arg_str .= "T1.corporate_id as as_corporate_id,";
    $arg_str .= "T1.rntl_cont_no as as_rntl_cont_no";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT * FROM m_wearer_std_tran WHERE NOT order_sts_kbn = '1') as T2";
    $arg_str .= " LEFT JOIN (SELECT * FROM t_order_tran) as T1";
    $arg_str .= " ON T2.order_req_no = T1.order_req_no";
    if ($rntl_sect_cd_zero_flg == 1){
        $arg_str .= " INNER JOIN m_section";
        $arg_str .= " ON T1.m_section_comb_hkey = m_section.m_section_comb_hkey";
    } else if ($rntl_sect_cd_zero_flg == 0){
        $arg_str .= " INNER JOIN m_contract_resource";
        $arg_str .= " ON T2.corporate_id = m_contract_resource.corporate_id";
        $arg_str .= " AND T2.rntl_cont_no = m_contract_resource.rntl_cont_no";
        $arg_str .= " AND T2.rntl_sect_cd = m_contract_resource.rntl_sect_cd";
    }
    $arg_str .= " WHERE ";
    $arg_str .= "T2.snd_kbn = '0'";
    $arg_str .= " AND T1.corporate_id = '".$corporate_id."'";
    $arg_str .= " AND T1.rntl_cont_no = '".$rntl_cont_no."'";
    if($rntl_sect_cd_zero_flg == 0){
        $arg_str .= "AND m_contract_resource.accnt_no = '$accnt_no'";
    }

    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_wst_order_req_no ASC";
    ChromePhp::log($arg_str);
    $m_wearer_std_tran2 = new MWearerStdTran();
    $results2 = new Resultset(null, $m_wearer_std_tran2, $m_wearer_std_tran2->getReadConnection()->query($arg_str));
    $result_obj2 = (array)$results2;
    $results_cnt2 = $result_obj2["\0*\0_count"];

    //発注未送信件数
    //パターン１とパターン２を足した件数
    $emply_cd_no_regist_cnt = $results_cnt + $results_cnt2;


    $query = "";

    $query .= "
            t_delivery_goods_state_details.corporate_id = '"."$corporate_id"."'
            AND t_delivery_goods_state_details.rntl_cont_no = '"."$rntl_cont_no"."'
            AND t_delivery_goods_state_details.receipt_status IN('1')
    AND (
        (
            t_order.order_sts_kbn = '1'
            AND m_wearer_std.werer_sts_kbn = '1'
            AND (
                t_order.order_reason_kbn = '01'
                OR  t_order.order_reason_kbn = '02'
                OR  t_order.order_reason_kbn = '03'
                OR  t_order.order_reason_kbn = '04'
                OR  t_order.order_reason_kbn = '19'
            )
        )
        OR  (
            (
                t_order.order_sts_kbn = '3'
                OR  t_order.order_sts_kbn = '4'
            )
            AND m_wearer_std.werer_sts_kbn = '1'
            AND (
                t_order.order_reason_kbn = '14'
                OR  t_order.order_reason_kbn = '15'
                OR  t_order.order_reason_kbn = '16'
                OR  t_order.order_reason_kbn = '17'
                OR  t_order.order_reason_kbn = '12'
                OR  t_order.order_reason_kbn = '13'
                OR  t_order.order_reason_kbn = '23'
            )
        )
        OR  (
            (
                t_order.order_sts_kbn = '5'
                AND m_wearer_std.werer_sts_kbn = '8'
            )
            AND (
                t_order.order_reason_kbn = '09'
                OR  t_order.order_reason_kbn = '10'
                OR  t_order.order_reason_kbn = '11'
            )
        )
        OR  (
            t_order.order_sts_kbn = '2'
            AND (
                (
                    t_order.order_reason_kbn = '05'
                    AND m_wearer_std.werer_sts_kbn = '4'
                )
                OR  (
                    t_order.order_reason_kbn = '06'
                    AND m_wearer_std.werer_sts_kbn = '2'
                )
                OR  t_order.order_reason_kbn = '07'
                AND m_wearer_std.werer_sts_kbn = '1'
                OR  t_order.order_reason_kbn = '08'
                AND m_wearer_std.werer_sts_kbn = '1'
                OR  t_order.order_reason_kbn = '24'
                AND m_wearer_std.werer_sts_kbn = '1'
            )
        )
        OR  t_order.order_sts_kbn = '9'
        AND m_wearer_std.werer_sts_kbn = '1'
    )";
    $query_list = array();
    //ゼロ埋めがない場合、ログインアカウントの条件追加
    if($rntl_sect_cd_zero_flg == 0){
        array_push($query_list,"m_contract_resource.accnt_no = '$accnt_no'");
        //未受領件数
        $query = implode(' AND ', $query_list);
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
    $arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
    $arg_str .= "m_wearer_std.rntl_cont_no as as_rntl_cont_no,";
    $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
    $arg_str .= "m_section.rntl_sect_cd as as_rntl_sect_cd,";
    $arg_str .= "m_job_type.job_type_name as as_job_type_name,";
    $arg_str .= "t_order.order_sts_kbn as as_order_sts_kbn,";
    $arg_str .= "t_order.order_req_ymd as as_order_req_ymd,";
    if ($rntl_sect_cd_zero_flg == 0) {
        $arg_str .= "m_contract_resource.update_ok_flg as as_update_ok_flg,";
    }
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

    $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
    $results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt_list = $result_obj["\0*\0_count"];


    //未返却件数
    $query = "";
    $query .= "
            t_returned_plan_info.corporate_id = '"."$corporate_id"."'
            AND t_returned_plan_info.rntl_cont_no = '"."$rntl_cont_no"."'
            AND t_returned_plan_info.return_status IN('1')
    AND (
        (
            (
                t_order.order_sts_kbn = '3'
                OR  t_order.order_sts_kbn = '4'
            )
            AND m_wearer_std.werer_sts_kbn = '1'
            AND (
                t_order.order_reason_kbn = '14'
                OR  t_order.order_reason_kbn = '15'
                OR  t_order.order_reason_kbn = '16'
                OR  t_order.order_reason_kbn = '17'
                OR  t_order.order_reason_kbn = '12'
                OR  t_order.order_reason_kbn = '13'
                OR  t_order.order_reason_kbn = '23'
            )
        )
        OR  (
            (
                t_order.order_sts_kbn = '5'
                AND m_wearer_std.werer_sts_kbn = '8'
            )
            AND (
                t_order.order_reason_kbn = '09'
                OR  t_order.order_reason_kbn = '10'
                OR  t_order.order_reason_kbn = '11'
            )
        )
        OR  (
            t_order.order_sts_kbn = '2'
            AND (
                (
                    t_order.order_reason_kbn = '05'
                    AND m_wearer_std.werer_sts_kbn = '4'
                )
                OR  (
                    t_order.order_reason_kbn = '06'
                    AND m_wearer_std.werer_sts_kbn = '2'
                )
                OR  t_order.order_reason_kbn = '07'
                AND m_wearer_std.werer_sts_kbn = '1'
                OR  t_order.order_reason_kbn = '08'
                AND m_wearer_std.werer_sts_kbn = '1'
                OR  t_order.order_reason_kbn = '24'
                AND m_wearer_std.werer_sts_kbn = '1'
            )
        )
        OR  t_order.order_sts_kbn = '9'
        AND m_wearer_std.werer_sts_kbn = '1'
    )";
    $query_list = array();
    //ゼロ埋めがない場合、ログインアカウントの条件追加
    if($rntl_sect_cd_zero_flg == 0){
        array_push($query_list,"m_contract_resource.accnt_no = '$accnt_no'");
        //未受領件数
        $query = implode(' AND ', $query_list);
    }

    if (individual_flg($auth['corporate_id'], $rntl_cont_no) == 1) {

        //---SQLクエリー実行---//
        $arg_str = "SELECT ";
        $arg_str .= " * ";
        $arg_str .= " FROM ";
        $arg_str .= "(SELECT distinct on (t_returned_plan_info.item_cd, t_returned_plan_info.color_cd, t_returned_plan_info.size_cd) ";
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
        $arg_str .= "t_returned_plan_info.order_date as as_re_order_date,";
        $arg_str .= "t_returned_plan_info.return_status as as_return_status,";
        $arg_str .= "t_returned_plan_info.return_date as as_return_date,";
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
        $arg_str .= " INNER JOIN m_job_type";
        $arg_str .= " ON t_order.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
        $arg_str .= " INNER JOIN m_wearer_std";
        $arg_str .= " ON t_order.werer_cd = m_wearer_std.werer_cd";
        $arg_str .= " AND t_order.corporate_id = m_wearer_std.corporate_id";
        $arg_str .= " AND t_order.rntl_cont_no = m_wearer_std.rntl_cont_no";
        $arg_str .= " INNER JOIN m_contract";
        $arg_str .= " ON t_order.rntl_cont_no = m_contract.rntl_cont_no";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= ") as distinct_table";

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
        $arg_str .= " INNER JOIN m_job_type";
        $arg_str .= " ON t_order.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
        $arg_str .= " INNER JOIN m_wearer_std";
        $arg_str .= " ON t_order.werer_cd = m_wearer_std.werer_cd";
        $arg_str .= " AND t_order.corporate_id = m_wearer_std.corporate_id";
        $arg_str .= " AND t_order.rntl_cont_no = m_wearer_std.rntl_cont_no";
        $arg_str .= " INNER JOIN m_contract";
        $arg_str .= " ON t_order.rntl_cont_no = m_contract.rntl_cont_no";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        ChromePhp::log($arg_str);

    }
    $t_order = new TOrder();
    $results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
    $results_array = (array)$results;
    $results_cnt = $results_array["\0*\0_count"];

    $no_return_cnt = $results_cnt;


    /*
    $no_return_cnt = TReturnedPlanInfo::find(array(
        "conditions" => "corporate_id = ?1 AND return_status = '1'",
        "bind"	=> array(1 => $corporate_id)
    ))->count();
    */

    $json_list['emply_cd_no_regist_cnt'] = $emply_cd_no_regist_cnt;
    $json_list['no_recieve_cnt'] = $results_cnt_list;
    $json_list['no_return_cnt'] = $no_return_cnt;
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
