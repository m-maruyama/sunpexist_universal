<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 着用者検索
 */
$app->post('/wearer_search/search', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);
    $app->session->remove("wearer_odr_post");

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    $cond = $params['cond'];
    $page = $params['page'];

    //（前処理）契約リソースマスタ参照、拠点コード「0」埋めデータ確認
    $query_list = array();
    $list = array();
    $all_list = array();
    $query_list[] = "corporate_id = '".$auth["corporate_id"]."'";
    $query_list[] = "rntl_cont_no = '".$cond['agreement_no']."'";
    $query_list[] = "accnt_no = '".$auth["accnt_no"]."'";
    $query = implode(' AND ', $query_list);
    $arg_str = '';
    $arg_str .= 'SELECT ';
    $arg_str .= ' distinct on (rntl_sect_cd) *';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_contract_resource';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $m_contract_resource = new MContractResource();
    $results = new Resultset(null, $m_contract_resource, $m_contract_resource->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
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
        $section_all_zero_flg = true;
    } else {
        $section_all_zero_flg = false;
    }

    //---既存着用者基本マスタ情報リスト取得---//
    $query_list = array();
    $query_list[] = "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'";
    if(!empty($cond['agreement_no'])){
        $query_list[] = "m_wearer_std_tran.rntl_cont_no = '".$cond['agreement_no']."'";
    }
    if(!empty($cond['cster_emply_cd'])){
        $query_list[] = "m_wearer_std_tran.cster_emply_cd LIKE '".$cond['cster_emply_cd']."%'";
    }
    if(!empty($cond['werer_name'])){
        $query_list[] = "m_wearer_std_tran.werer_name LIKE '%".$cond['werer_name']."%'";
    }
    if(!empty($cond['sex_kbn'])){
        $query_list[] = "m_wearer_std_tran.sex_kbn = '".$cond['sex_kbn']."'";
    }
    if(!empty($cond['section'])){
        $query_list[] = "m_wearer_std_tran.rntl_sect_cd = '".$cond['section']."'";
    }
    if(!empty($cond['job_type'])){
        $query_list[] = "m_wearer_std_tran.job_type_cd = '".$cond['job_type']."'";
    }
    $query_list[] = "(t_order_tran.order_sts_kbn = '1' OR m_wearer_std_tran.order_sts_kbn = '1')";
    $query_list[] = "((t_order_tran.order_reason_kbn <> '03' and t_order_tran.order_reason_kbn <> '27') or t_order_tran.order_reason_kbn IS NULL)";
    if (!$section_all_zero_flg) {
        $query_list[] = "wcr.corporate_id = '".$auth['corporate_id']."'";
        $query_list[] = "wcr.rntl_cont_no = '".$cond['agreement_no']."'";
        $query_list[] = "wcr.accnt_no = '".$auth['accnt_no']."'";
    }
    $query = implode(' AND ', $query_list);

    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (m_wearer_std_tran.m_wearer_std_comb_hkey) ";
    $arg_str .= "m_wearer_std_tran.m_wearer_std_comb_hkey as as_m_wearer_std_comb_hkey,";
    $arg_str .= "m_wearer_std_tran.order_req_no as as_wst_order_req_no,";
    $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std_tran.order_sts_kbn as as_order_sts_kbn,";
    $arg_str .= "m_wearer_std_tran.werer_cd as as_werer_cd,";
    $arg_str .= "m_wearer_std_tran.corporate_id as as_corporate_id,";
    $arg_str .= "m_wearer_std_tran.rntl_cont_no as as_rntl_cont_no,";
    $arg_str .= "m_wearer_std_tran.rntl_sect_cd as as_rntl_sect_cd,";
    $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";
    $arg_str .= "m_wearer_std_tran.order_sts_kbn as as_wearer_order_sts_kbn,";
    $arg_str .= "m_wearer_std_tran.ship_to_cd as as_ship_to_cd,";
    $arg_str .= "m_wearer_std_tran.ship_to_brnch_cd as as_ship_to_brnch_cd,";
    $arg_str .= "m_wearer_std_tran.snd_kbn as as_snd_kbn,";
    $arg_str .= "m_wearer_std_tran.appointment_ymd as as_appointment_ymd,";
    $arg_str .= "m_wearer_std_tran.resfl_ymd as as_resfl_ymd,";
    $arg_str .= "m_wearer_std_tran.job_type_cd as as_job_type_cd,";
    $arg_str .= "m_wearer_std_tran.order_req_no as as_wearer_order_req_no,";
    $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";
    $arg_str .= "t_order_tran.order_req_no as as_order_req_no,";
    $arg_str .= "t_order_tran.memo as as_memo,";
    $arg_str .= "wst.rntl_sect_name as as_rntl_sect_name,";
    $arg_str .= "wjt.job_type_name as as_job_type_name";
    $arg_str .= " FROM ";
    if ($section_all_zero_flg) {
        $arg_str .= "(m_wearer_std_tran INNER JOIN m_section as wst";
        $arg_str .= " ON m_wearer_std_tran.corporate_id = wst.corporate_id";
        $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wst.rntl_cont_no";
        $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = wst.rntl_sect_cd";
        $arg_str .= " INNER JOIN m_job_type as wjt";
        $arg_str .= " ON m_wearer_std_tran.corporate_id = wjt.corporate_id";
        $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wjt.rntl_cont_no";
        $arg_str .= " AND m_wearer_std_tran.job_type_cd = wjt.job_type_cd)";
    } else {
        $arg_str .= "(m_wearer_std_tran INNER JOIN (m_section as wst";
        $arg_str .= " INNER JOIN m_contract_resource as wcr";
        $arg_str .= " ON wst.corporate_id = wcr.corporate_id";
        $arg_str .= " AND wst.rntl_cont_no = wcr.rntl_cont_no";
        $arg_str .= " AND wst.rntl_sect_cd = wcr.rntl_sect_cd)";
        $arg_str .= " ON m_wearer_std_tran.corporate_id = wst.corporate_id";
        $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wst.rntl_cont_no";
        $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = wst.rntl_sect_cd";
        $arg_str .= " INNER JOIN m_job_type as wjt";
        $arg_str .= " ON m_wearer_std_tran.corporate_id = wjt.corporate_id";
        $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wjt.rntl_cont_no";
        $arg_str .= " AND m_wearer_std_tran.job_type_cd = wjt.job_type_cd)";
    }
    $arg_str .= " LEFT JOIN t_order_tran";
    $arg_str .= " ON m_wearer_std_tran.corporate_id = t_order_tran.corporate_id";
    $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = t_order_tran.rntl_cont_no";
    $arg_str .= " AND m_wearer_std_tran.werer_cd = t_order_tran.werer_cd";
    $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = t_order_tran.rntl_sect_cd";
    $arg_str .= " AND m_wearer_std_tran.job_type_cd = t_order_tran.job_type_cd";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";

    $m_weare_std_tran= new MWearerStdTran();
    $results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
//    $result_obj = (array)$results;
//    $results_cnt = $result_obj["\0*\0_count"];
    $results_cnt = count($results);
    $paginator_model = new PaginatorModel(
        array(
            "data"  => $results,
            "limit" => $page['records_per_page'],
            "page" => $page['page_number']
        )
    );

    $all_list = array();
    $json_list = array();

    if(!empty($results_cnt)){
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;
        foreach($results as $result) {
            $list = array();
            $list['werer_cd'] = $result->as_werer_cd;
            $list['corporate_id'] = $result->as_corporate_id;
            // レンタル契約No
            $list['rntl_cont_no'] = $result->as_rntl_cont_no;
            // レンタル部門コード
            $list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
            // 職種コード
            $list['job_type_cd'] = $result->as_job_type_cd;
            // 発令日
            $list['appointment_ymd'] = $result->as_appointment_ymd;
            // 着用開始日
            $list['resfl_ymd'] = $result->as_resfl_ymd;
            // 理由区分
            if ($result->as_order_reason_kbn) {
                $list['order_reason_kbn'] = $result->as_order_reason_kbn;
            } else {
                $list['order_reason_kbn'] = '7';
            }
            // 着用者コード
            $list['werer_cd'] = $result->as_werer_cd;
            // 社員番号
            if ($result->as_cster_emply_cd) {
                $list['cster_emply_cd'] = $result->as_cster_emply_cd;
            } else {
                $list['cster_emply_cd'] = "-";
            }
            // 性別区分
            $list['sex_kbn'] = $result->as_sex_kbn;
            // 着用者名
            if (!empty($result->as_werer_name)) {
                $list['werer_name'] = $result->as_werer_name;
            } else {
                $list['werer_name'] = "-";
            }
            //備考
            $list['comment'] = $result->as_memo;
            //---性別名称---//
            $query_list = array();
            $query_list[] = "cls_cd = '004'";
            $query_list[] = "gen_cd = '".$result->as_sex_kbn."'";
            $query = implode(' AND ', $query_list);
            $gencode = MGencode::query()
                ->where($query)
                ->columns('*')
                ->execute();
            foreach ($gencode as $gencode_map) {
                $list['sex_kbn_name'] = $gencode_map->gen_name;
            }
            // 発注、発注情報トラン有無フラグ
            if (isset($result->as_order_req_no)) {
                $list['order_req_no'] = $result->as_order_req_no;
                $list['order_kbn'] = "<font color='red'>済</font>";
                // 発注情報トラン有
                $list['order_tran_flg'] = '1';
            }else{
                $list['order_req_no'] = $result->as_wearer_order_req_no;
                $list['order_kbn'] = "未";
                // 発注情報トラン無
                $list['order_tran_flg'] = '0';
            }
            // 状態、着用者マスタトラン有無フラグ
            $list['snd_kbn'] = "-";
            if (isset($result->as_snd_kbn)) {
                // 状態
                if($result->as_snd_kbn == '0'){
                    $list['snd_kbn'] = "未送信";
                }elseif($result->as_snd_kbn == '1'){
                    $list['snd_kbn'] = "送信済";
                }elseif($result->as_snd_kbn == '9'){
                    $list['snd_kbn'] = "処理中";
                }
                // 着用者マスタトラン有
                $list['wearer_tran_flg'] = '1';
            } else {
                $result->as_snd_kbn = '';
                // 着用者マスタトラン無
                $list['wearer_tran_flg'] = '0';
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
            //--「貸与開始」ボタン生成--//
            // 発注情報トラン参照
            $query_list = array();
            $query_list[] = "corporate_id = '".$auth['corporate_id']."'";
            $query_list[] = "rntl_cont_no = '".$list['rntl_cont_no']."'";
            $query_list[] = "werer_cd = '".$list['werer_cd']."'";
            $query_list[] = "rntl_sect_cd = '".$list['rntl_sect_cd']."'";
            $query_list[] = "job_type_cd = '".$list['job_type_cd']."'";
            $query = implode(' AND ', $query_list);
            $arg_str = "";
            $arg_str .= "SELECT distinct on (order_req_no) ";
            $arg_str .= "*";
            $arg_str .= " FROM ";
            $arg_str .= "t_order_tran";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $t_order_tran = new TOrderTran();
            $t_order_tran_results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
            $result_obj = (array)$t_order_tran_results;
            $t_order_tran_cnt = $result_obj["\0*\0_count"];

            // 「貸与開始」パターンチェックスタート
            $list['btnPattern'] = "";
            $patarn_flg = true;
            $order_sts_kbn =$result-> as_wearer_order_sts_kbn;
            $snd_kbn = $result->as_snd_kbn;
            if (!empty($t_order_tran_cnt)) {

                if ($list['btnPattern'] == "") {
                    //パターンB： 発注情報トラン．発注状況区分 = 貸与 かつ、発注情報トラン．理由区分 = 追加貸与以外のデータがある場合、かつ、着用者基本マスタトラン．送信区分 = 未送信の場合、ボタンの文言は「貸与開始[済]」で表示する。
                    $patarn_flg = true;
                        if ($order_sts_kbn == '1' && $snd_kbn == '0') {
                            $patarn_flg = false;
                        }
                    if (!$patarn_flg) {
                        $list['wearer_input_button'] = "貸与開始";
                        $list['wearer_input_red'] = "[済]";
                        $list['disabled'] = "";
                        $list['btnPattern'] = "B";
                    }
                }
                if ($list['btnPattern'] == "") {
                    //パターンC： 発注情報トラン．発注状況区分 = 貸与 かつ、発注情報トラン．理由区分 = 追加貸与以外のデータがある場合、かつ、着用者基本マスタトラン．送信区分 = 送信済の場合、ボタンの文言は「貸与開始[済]」で非活性表示する。
                    $patarn_flg = true;
                        if ($order_sts_kbn == '1' && $snd_kbn == '1') {
                            $patarn_flg = false;
                        }
                    if (!$patarn_flg) {
                        $list['wearer_input_button'] = "貸与開始";
                        $list['wearer_input_red'] = "[済]";
                        $list['disabled'] = "disabled";
                        $list['btnPattern'] = "C";
                    }
                }
                if ($list['btnPattern'] == "") {
                    //パターンD： 発注情報トラン．発注状況区分 = 貸与 かつ、発注情報トラン．理由区分 = 追加貸与以外のデータがある場合、かつ、着用者基本マスタトラン．送信区分 = 送信中の場合、ボタンの文言は「貸与開始[済]」で非活性表示する。
                    $patarn_flg = true;
                        if ($order_sts_kbn == '1' && $snd_kbn == '9') {
                            $patarn_flg = false;
                    }
                    if (!$patarn_flg) {
                        $list['wearer_input_button'] = "貸与開始";
                        $list['wearer_input_red'] = "";
                        $list['disabled'] = "disabled";
                        $list['btnPattern'] = "D";
                    }
                }
            }elseif($order_sts_kbn == '1' && $snd_kbn == '9'){

                    $list['wearer_input_button'] = "貸与開始";
                    $list['wearer_input_red'] = "";
                    $list['disabled'] = "disabled";
                    $list['btnPattern'] = "D";
            }else{
                    $list['wearer_input_button'] = "貸与開始";
                    $list['wearer_input_red'] = "";
                    $list['disabled'] = "";
                    $list['btnPattern'] = "A";
                    $list['return_reciept_button'] = false;
            }

            // 発注入力へのパラメータ設定
            $list['param'] = '';
            if(!$result->as_ship_to_cd){
                $list['ship_to_cd'] = '';
            }else{
                $list['ship_to_cd'] = $result->as_ship_to_cd;
            }
            if(!$result->as_ship_to_brnch_cd){
                $list['ship_to_brnch_cd'] = '';
            }else{
                $list['ship_to_brnch_cd'] = $result->as_ship_to_brnch_cd;
            }
            $list['param'] .= $list['rntl_cont_no'].':';
            $list['param'] .= $list['werer_cd'].':';
            $list['param'] .= $result->as_cster_emply_cd.':';
            $list['param'] .= $list['sex_kbn'].':';
            $list['param'] .= $result->as_rntl_sect_cd.':';
            $list['param'] .= $list['job_type_cd'].':';
            $list['param'] .= $list['ship_to_cd'].':';
            $list['param'] .= $list['ship_to_brnch_cd'].':';
            $list['param'] .= $list['order_reason_kbn'].':';
            $list['param'] .= $list['order_tran_flg'].':';
            $list['param'] .= $result->as_wst_order_req_no.':';
            $list['param'] .= $list['order_req_no'].':';
            $list['param'] .= $result->as_m_wearer_std_comb_hkey.':';
            $list['param'] .= $list['comment'];

            $all_list[] = $list;
        }
    }

    $page_list['records_per_page'] = $page['records_per_page'];
    $page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = $results_cnt;
    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;

    echo json_encode($json_list);
});



/**
 * 「貸与開始」ボタンの押下時のパラメータのセッション保持
 * →発注入力（貸与開始）にてパラメータ利用
 */
$app->post('/wearer_search/req_param', function ()use($app) {
    $params = json_decode(file_get_contents("php://input"), true);

    // パラメータ取得
    if (!empty($params['data'])) {
      $cond = $params['data'];
    } else {
      $cond = "";
    }
    $wearer_odr_post = $app->session->get("wearer_odr_post");
    //ChromePhp::LOG($cond);
    //ChromePhp::LOG($wearer_odr_post);

    // 検索画面側パラメータ、着用者情報入力画面、商品詳細情報画面
    $add_item = array();
    if (isset($params['add_item'])){
        $add_item = $params['add_item'];
    }elseif($wearer_odr_post['add_item']) {
        $add_item = $wearer_odr_post['add_item'];
    }
    if(isset($cond["job_type"]) && isset($wearer_odr_post['job_type_cd'])){
        if($cond["job_type"] != $wearer_odr_post['job_type_cd']){
            $add_item = array();
        }
    }
    if(isset($cond["order_tran_flg"])){
      $order_tran_flg = $cond["order_tran_flg"];
    }elseif(isset($wearer_odr_post['order_tran_flg'])){
      $order_tran_flg = $wearer_odr_post["order_tran_flg"];
    }else{
      $order_tran_flg = '0';
    }
    if(isset($wearer_odr_post['wearer_tran_flg'])){
      $wearer_tran_flg = $wearer_odr_post["wearer_tran_flg"];
    }else{
      $wearer_tran_flg = '0';
    }
    if(isset($cond['order_reason_kbn'])) {
      $reason_kbn = $cond["order_reason_kbn"];
    }elseif(isset($wearer_odr_post['order_reason_kbn'])){
      $reason_kbn = $wearer_odr_post["order_reason_kbn"];
    }else{
      $reason_kbn = null;
    }
    if(isset($cond["m_wearer_std_comb_hkey"])) {
      $m_wearer_std_comb_hkey = $cond["m_wearer_std_comb_hkey"];
    } else if (isset($wearer_odr_post["m_wearer_std_comb_hkey"])) {
      $m_wearer_std_comb_hkey = $wearer_odr_post["m_wearer_std_comb_hkey"];
    } else {
      $m_wearer_std_comb_hkey = "";
    }
    if(isset($cond["agreement_no"])) {
      $agreement_no = $cond["agreement_no"];
    } else if (isset($wearer_odr_post["rntl_cont_no"])) {
      $agreement_no = $wearer_odr_post["rntl_cont_no"];
    } else {
      $agreement_no = "";
    }
    if(isset($cond["werer_cd"])) {
      $werer_cd = $cond["werer_cd"];
    } else if (isset($wearer_odr_post["werer_cd"])) {
      $werer_cd = $wearer_odr_post["werer_cd"];
    } else {
      $werer_cd = "";
    }
    if(isset($cond["werer_name"])) {
      $werer_name = $cond["werer_name"];
    } else if (isset($wearer_odr_post["werer_name"])) {
      $werer_name = $wearer_odr_post["werer_name"];
    } else {
      $werer_name = "";
    }
    if(isset($cond["werer_name_kana"])){
      $werer_name_kana = $cond["werer_name_kana"];
    } else if (isset($wearer_odr_post["werer_name_kana"])) {
      $werer_name_kana = $wearer_odr_post["werer_name_kana"];
    } else {
      $werer_name_kana = "";
    }
    if(isset($cond["cster_emply_cd"])){
      $cster_emply_cd = $cond["cster_emply_cd"];
    } else if (isset($wearer_odr_post["cster_emply_cd"])) {
      $cster_emply_cd = $wearer_odr_post["cster_emply_cd"];
    } else {
      $cster_emply_cd = "";
    }
    if(isset($cond["cster_emply_flg"])){
      $cster_emply_flg = $cond["cster_emply_flg"];
    } else if (isset($wearer_odr_post["cster_emply_flg"])) {
      $cster_emply_flg = $wearer_odr_post["cster_emply_flg"];
    } else {
      $cster_emply_flg = false;
    }
    if(isset($cond["sex_kbn"])){
      $sex_kbn = $cond["sex_kbn"];
    } else if (isset($wearer_odr_post["sex_kbn"])) {
      $sex_kbn = $wearer_odr_post["sex_kbn"];
    } else {
      $sex_kbn = "";
    }
    if(isset($cond["rntl_sect_cd"])){
      $rntl_sect_cd = $cond["rntl_sect_cd"];
    } else if (isset($wearer_odr_post["rntl_sect_cd"])) {
      $rntl_sect_cd = $wearer_odr_post["rntl_sect_cd"];
    } else {
      $rntl_sect_cd = "";
    }
    if(isset($cond["job_type"])){
      $job_type_cd = $cond["job_type"];
    } else if (isset($wearer_odr_post["job_type_cd"])) {
      $job_type_cd = $wearer_odr_post["job_type_cd"];
    } else {
      $job_type_cd = "";
    }
    if(isset($cond["appointment_ymd"])){
      $appointment_ymd = $cond["appointment_ymd"];
    } else if (isset($wearer_odr_post["appointment_ymd"])) {
      $appointment_ymd = $wearer_odr_post["appointment_ymd"];
    } else {
      $appointment_ymd = "";
    }
    if(isset($cond["resfl_ymd"])){
      $resfl_ymd = $cond["resfl_ymd"];
    } else if (isset($wearer_odr_post["resfl_ymd"])) {
      $resfl_ymd = $wearer_odr_post["resfl_ymd"];
    } else {
      $resfl_ymd = "";
    }
    if(isset($cond["wst_order_req_no"])){
      $wst_order_req_no = $cond["wst_order_req_no"];
    } else if (isset($wearer_odr_post["wst_order_req_no"])) {
      $wst_order_req_no = $wearer_odr_post["wst_order_req_no"];
    } else {
      $wst_order_req_no = "";
    }
    if(isset($cond["order_req_no"])){
      $order_req_no = $cond["order_req_no"];
    } else if (isset($wearer_odr_post["order_req_no"])) {
      $order_req_no = $wearer_odr_post["order_req_no"];
    } else {
      $order_req_no = "";
    }
    if(isset($cond["comment"])) {
      $comment = $cond["comment"];
    } else if (isset($wearer_odr_post["comment"])) {
      $comment = $wearer_odr_post["comment"];
    } else {
      $comment = "";
    }
    if (isset($cond['ship_to_cd'])) {
      $ship_to_cd = $cond['ship_to_cd'];
      $ship_to_brnch_cd = $cond['ship_to_brnch_cd'];
    } else if ($wearer_odr_post['ship_to_cd']) {
      $ship_to_cd = $wearer_odr_post['ship_to_cd'];
      $ship_to_brnch_cd = $wearer_odr_post['ship_to_brnch_cd'];
    } else {
      // アカウントセッション取得
      $auth = $app->session->get("auth");
      //拠点のマスタチェック
      $query_list = array();
      // 部門マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
      array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
      // 部門マスタ．レンタル契約No.　＝　画面で選択されている契約No.
      array_push($query_list,"rntl_cont_no = '".$agreement_no."'");
      // 部門マスタ．レンタル部門コード　＝　画面で選択されている拠点
      array_push($query_list,"rntl_sect_cd = '".$rntl_sect_cd."'");

      //sql文字列を' AND 'で結合
      $query = implode(' AND ', $query_list);
      //--- クエリー実行・取得 ---//
      $m_section = MSection::find(array(
          'conditions' => $query
      ));
      $ship_to_cd = $m_section[0]->std_ship_to_cd;
      $ship_to_brnch_cd = $m_section[0]->std_ship_to_brnch_cd;
    }

    // POSTパラメータのセッション格納
    $app->session->set("wearer_odr_post", array(
      'm_wearer_std_comb_hkey' => $m_wearer_std_comb_hkey,
      'rntl_cont_no' => $agreement_no,
      'werer_cd' => $werer_cd,
      'werer_name' => $werer_name,
      'werer_name_kana' => $werer_name_kana,
      'cster_emply_cd' => $cster_emply_cd,
      'cster_emply_flg' => $cster_emply_flg,
      'sex_kbn' => $sex_kbn,
      'rntl_sect_cd' => $rntl_sect_cd,
      'job_type_cd' => $job_type_cd,
      'ship_to_cd' => $ship_to_cd,
      'ship_to_brnch_cd' => $ship_to_brnch_cd,
      'order_reason_kbn' => $reason_kbn,
      'appointment_ymd' => $appointment_ymd,
      'resfl_ymd' => $resfl_ymd,
      'order_tran_flg' => $order_tran_flg,
      'wearer_tran_flg' => $wearer_tran_flg,
      'wst_order_req_no' => $wst_order_req_no,
      'order_req_no' => $order_req_no,
      'comment' => $comment,
      'add_item' => $add_item,
    ));
    //ChromePhp::LOG($app->session->get("wearer_odr_post"));

    $json_list = array();
    $json_list = $cond;
    echo json_encode($json_list);
});
