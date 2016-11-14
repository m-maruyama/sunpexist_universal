<?php
//use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Mvc\Model\Query;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/*
 * 検索項目：契約No(入力系)
 */
$app->post('/agreement_no_input', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();
    $referrer = $params['referrer'];
    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    // 契約マスタ. 企業ID
    array_push($query_list, "m_contract.corporate_id = '".$auth['corporate_id']."'");
    // 契約マスタ. レンタル契約フラグ
    array_push($query_list, "m_contract.rntl_cont_flg = '1'");
    // 契約リソースマスタ. 企業ID
    array_push($query_list, "m_contract_resource.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ.企業ID
    array_push($query_list, "m_account.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ. ユーザーID
    array_push($query_list, "m_account.user_id = '".$auth['user_id']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= '(SELECT distinct on (m_contract.rntl_cont_no) ';
    $arg_str .= 'm_contract.rntl_cont_no as as_rntl_cont_no,';
    $arg_str .= 'm_contract.rntl_cont_name as as_rntl_cont_name';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_contract';
    $arg_str .= ' INNER JOIN m_contract_resource';
    $arg_str .= ' ON m_contract.corporate_id = m_contract_resource.corporate_id';
    $arg_str .= ' AND m_contract.rntl_cont_no = m_contract_resource.rntl_cont_no';
    $arg_str .= ' INNER JOIN m_account';
    $arg_str .= ' ON m_contract_resource.accnt_no = m_account.accnt_no';
    $arg_str .= ' AND m_contract_resource.corporate_id = m_account.corporate_id';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ') as distinct_table';
    $arg_str .= ' ORDER BY as_rntl_cont_no asc';
    $m_contract = new MContract();
    $results = new Resultset(null, $m_contract, $m_contract->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];

    if ($results_cnt > 0) {
        $list['rntl_cont_no'] = null;
        $list['rntl_cont_name'] = null;
        array_push($all_list, $list);
        // 前画面が着用者検索画面でない場合、セッションを削除
        if($referrer>-1){
            // 前画面セッション取得
            $wearer_odr_post = $app->session->get("wearer_odr_post");
            foreach ($results as $result) {
                $list['rntl_cont_no'] = $result->as_rntl_cont_no;
                $list['rntl_cont_name'] = $result->as_rntl_cont_name;
                if (($list['rntl_cont_no'] == $wearer_odr_post['rntl_cont_no'])&&($referrer>-1)) {
                    $list['selected'] = 'selected';
                } else {
                    $list['selected'] = '';
                }

                array_push($all_list, $list);
            }
            if(isset($wearer_odr_post)){
                $json_list['rntl_cont_no'] = $wearer_odr_post['rntl_cont_no'];
                $json_list['werer_cd'] = $wearer_odr_post['werer_cd'];
                $json_list['cster_emply_cd'] = $wearer_odr_post['cster_emply_cd'];
                $json_list['sex_kbn'] = $wearer_odr_post['sex_kbn'];
                $json_list['rntl_sect_cd'] = $wearer_odr_post['rntl_sect_cd'];
                $json_list['job_type_cd'] = $wearer_odr_post['job_type_cd'];
                $json_list['ship_to_cd'] = $wearer_odr_post['ship_to_cd'];
                $json_list['ship_to_brnch_cd'] = $wearer_odr_post['ship_to_brnch_cd'];
                $json_list['appointment_ymd'] = date('Y/m/d', strtotime($wearer_odr_post['appointment_ymd']));
                $json_list['resfl_ymd'] = date('Y/m/d', strtotime($wearer_odr_post['resfl_ymd']));
            }
        }else{
            foreach ($results as $result) {
                $list['rntl_cont_no'] = $result->as_rntl_cont_no;
                $list['rntl_cont_name'] = $result->as_rntl_cont_name;
                array_push($all_list, $list);
            }
            $app->session->remove("wearer_odr_post");
        }
    } else {
        $list['rntl_cont_no'] = null;
        $list['rntl_cont_name'] = '';
        $list['selected'] = '';
        array_push($all_list, $list);
    }
    $json_list['agreement_no_list'] = $all_list;
    echo json_encode($json_list);
});

/*
 * 着用者入力各フォーム
 */
$app->post('/wearer_input', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');
    $cond = $params['cond'];

    //前画面からデータを引き継いでいる場合
    $wearer_odr_post = $app->session->get("wearer_odr_post");
    $referrer = $cond['referrer'];
    $query_list = array();
    $list = array();
    $json_list = array();

    //--性別ここから
    $sex_kbn_list = array();
    //--- 検索条件 ---//
    // 汎用コードマスタ. 分類コード
    array_push($query_list, "cls_cd = '004'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $m_gencode_results = MGencode::query()
        ->where($query)
        ->columns('*')
        ->execute();
    foreach ($m_gencode_results as $m_gencode_result) {
        $list['gen_cd'] = $m_gencode_result->gen_cd;
        $list['gen_name'] = $m_gencode_result->gen_name;
        if (($list['gen_cd'] == $wearer_odr_post['sex_kbn'])&&($referrer>-1)) {
            $list['sex_kbn_selected'] = 'selected';
        } else {
            $list['sex_kbn_selected'] = '';
        }
        array_push($sex_kbn_list, $list);
    }
    //--性別ここまで

    //拠点--ここから
    $all_list = array();
    $query_list = array();
    if (!empty($params["corporate_flg"])) {
        if (!empty($params["corporate"])) {
            array_push($query_list, "corporate_id = '".$params["corporate"]."'");
        }
    } else {
        array_push($query_list, "corporate_id = '".$auth["corporate_id"]."'");
    }
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "rntl_cont_no = '".$params['agreement_no']."'");
    } else {
        if (empty($params["corporate_flg"])) {
            array_push($query_list, "rntl_cont_no = '".$app->session->get('first_rntl_cont_no')."'");
        }
    }
    $query = implode(' AND ', $query_list);

    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (rntl_sect_cd) *';
    $arg_str .= ' FROM m_section';
    if (!empty($query)) {
        $arg_str .= ' WHERE ';
        $arg_str .= $query;
    }
    $arg_str .= ' ORDER BY rntl_sect_cd asc';

    $m_section = new MSection();
    $results = new Resultset(null, $m_section, $m_section->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];


    // 前画面が着用者検索画面でない場合、セッションを削除
    if($referrer>-1){
        // 前画面セッション取得
        $wearer_odr_post = $app->session->get("wearer_odr_post");
        foreach ($results as $result) {
            $list['rntl_sect_cd'] = $result->rntl_sect_cd;
            $list['rntl_cont_no'] = $result->rntl_cont_no;
            $list['rntl_sect_name'] = $result->rntl_sect_name;
            if (($list['rntl_sect_cd'] == $wearer_odr_post['rntl_sect_cd'])&&($referrer>-1)) {
                $list['rntl_sect_cd_selected'] = 'selected';
            } else {
                $list['rntl_sect_cd_selected'] = '';
            }

            array_push($all_list, $list);
        }
        if(isset($wearer_odr_post)){
            $json_list['rntl_cont_no'] = $wearer_odr_post['rntl_cont_no'];
            $json_list['werer_cd'] = $wearer_odr_post['werer_cd'];
            $json_list['cster_emply_cd'] = $wearer_odr_post['cster_emply_cd'];
            $json_list['sex_kbn'] = $wearer_odr_post['sex_kbn'];
            $json_list['rntl_sect_cd'] = $wearer_odr_post['rntl_sect_cd'];
            $json_list['job_type_cd'] = $wearer_odr_post['job_type_cd'];
            $json_list['ship_to_cd'] = $wearer_odr_post['ship_to_cd'];
            $json_list['ship_to_brnch_cd'] = $wearer_odr_post['ship_to_brnch_cd'];
            $json_list['appointment_ymd'] = date('Y/m/d', strtotime($wearer_odr_post['appointment_ymd']));
            $json_list['resfl_ymd'] = date('Y/m/d', strtotime($wearer_odr_post['resfl_ymd']));
        }
    }else{
        foreach ($results as $result) {
            $list['rntl_sect_cd'] = $result->rntl_sect_cd;
            $list['rntl_sect_name'] = $result->rntl_sect_name;
            array_push($all_list, $list);
        }
        $app->session->remove("wearer_odr_post");
    }

    $m_section_list = $all_list;
    //--拠点ここまで

    //貸与パターン--ここから
    $query_list = array();
    $list = array();
    $all_list = array();
    $job_type_list = array();
    //--- 検索条件 ---//
    // 職種マスタ. 企業ID
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    // 職種マスタ. レンタル契約No
    array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $m_job_type_results = MJobType::query()
        ->where($query)
        ->columns('*')
        ->execute();

    foreach ($m_job_type_results as $m_job_type_result) {
        $list['job_type_cd'] = $m_job_type_result->job_type_cd;
        $list['job_type_name'] = $m_job_type_result->job_type_name;
        $list['sp_job_type_flg'] = $m_job_type_result->sp_job_type_flg;
        if (($list['job_type_cd'] == $wearer_odr_post['job_type_cd'])&&($referrer>-1)) {
            $list['job_type_cd_selected'] = 'selected';
        } else {
            $list['job_type_cd_selected'] = '';
        }
        array_push($job_type_list, $list);
    }
    //貸与パターン--ここまで

    //出荷先--ここから
    $query_list = array();
    $list = array();
    $m_shipment_to_list = array();
    //--- 検索条件 ---//
    // 出荷先マスタ. 企業ID
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    // 出荷先マスタ. レンタル契約No
    array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $m_shipment_to_results = MShipmentTo::query()
        ->where($query)
        ->columns('*')
        ->execute();
    //一件目に「支店店舗と同じ:部門マスタ.標準出荷先コード:部門マスタ.標準出荷先支店コード」という選択肢とセレクトボックスを表示。
    foreach ($m_shipment_to_results as $m_shipment_to_result) {
        $list['ship_to_cd'] = $m_shipment_to_result->ship_to_cd;
        $list['ship_to_brnch_cd'] = $m_shipment_to_result->ship_to_brnch_cd;
        $list['cust_to_brnch_name1'] = $m_shipment_to_result->cust_to_brnch_name1;
        $list['cust_to_brnch_name2'] = $m_shipment_to_result->cust_to_brnch_name2;
        $list['zip_no'] = $m_shipment_to_result->zip_no;
        $list['address1'] = $m_shipment_to_result->address1;
        $list['address2'] = $m_shipment_to_result->address2;
        $list['address3'] = $m_shipment_to_result->address3;
        $list['address4'] = $m_shipment_to_result->address4;
        if (($list['ship_to_cd'] == $wearer_odr_post['ship_to_cd'])&&($list['ship_to_brnch_cd'] == $wearer_odr_post['ship_to_brnch_cd'])&&($referrer>-1)) {
            $list['ship_to_cd_selected'] = 'selected';
        } else {
            $list['ship_to_cd_selected'] = '';
        }
        array_push($m_shipment_to_list, $list);
    }
    //出荷先--ここまで
    $json_list['m_shipment_to_list'] = $m_shipment_to_list;
    $json_list['job_type_list'] = $job_type_list;
    $json_list['sex_kbn_list'] = $sex_kbn_list;
    $json_list['m_section_list'] = $m_section_list;


    if(isset($wearer_odr_post['rntl_cont_no'])){
        $json_list['rntl_cont_no'] = $wearer_odr_post['rntl_cont_no'];
        $json_list['werer_cd'] = $wearer_odr_post['werer_cd'];
        $json_list['cster_emply_cd'] = $wearer_odr_post['cster_emply_cd'];
        $json_list['sex_kbn'] = $wearer_odr_post['sex_kbn'];
        $json_list['rntl_sect_cd'] = $wearer_odr_post['rntl_sect_cd'];
        $json_list['job_type_cd'] = $wearer_odr_post['job_type_cd'];
        $json_list['appointment_ymd'] = date('Y/m/d', strtotime($wearer_odr_post['appointment_ymd']));
        $json_list['resfl_ymd'] = date('Y/m/d', strtotime($wearer_odr_post['resfl_ymd']));

        $query_list = array();
        array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
        array_push($query_list, "ship_to_cd = '".$wearer_odr_post['ship_to_cd']."'");
        array_push($query_list, "ship_to_brnch_cd = '".$wearer_odr_post['ship_to_brnch_cd']."'");
        $query = implode(' AND ', $query_list);

        //--- クエリー実行・取得 ---//
        $m_shipment_to_results = MShipmentTo::query()
            ->where($query)
            ->columns('*')
            ->execute();
        foreach ($m_shipment_to_results as $m_shipment_to_result) {
            $json_list['ship_to_cd'] = $m_shipment_to_result->ship_to_cd.','.$m_shipment_to_result->ship_to_brnch_cd;
            $json_list['cust_to_brnch_name'] = $m_shipment_to_result->cust_to_brnch_name1.$m_shipment_to_result->cust_to_brnch_name2;
            $json_list['zip_no'] = $m_shipment_to_result->zip_no;
            if($m_shipment_to_result->address1){
                $json_list['address1'] = $m_shipment_to_result->address1;
            }else{
                $json_list['address1'] = '';
            };
            if($m_shipment_to_result->address2){
                $json_list['address2'] = $m_shipment_to_result->address2;
            }else{
                $json_list['address2'] = '';
            };
            if($m_shipment_to_result->address3){
                $json_list['address3'] = $m_shipment_to_result->address3;
            }else{
                $json_list['address3'] = '';
            };
            if($m_shipment_to_result->address4){
                $json_list['address4'] = $m_shipment_to_result->address4;
            }else{
                $json_list['address4'] = '';
            };
        }
    }

    $query_list = array();
    $list = array();
    $all_list = array();
    $param_list = '';

    if ($wearer_odr_post['wearer_tran_flg'] == '1') {
        //--着用者基本マスタトラン有の場合--//
        array_push($query_list,"m_wearer_std_tran.m_wearer_std_comb_hkey = '".$wearer_odr_post['m_wearer_std_comb_hkey']."'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
        $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
        $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana,";
        $arg_str .= "m_wearer_std_tran.appointment_ymd as as_appointment_ymd,";
        $arg_str .= "m_wearer_std_tran.resfl_ymd as as_resfl_ymd";
        $arg_str .= " FROM ";
        $arg_str .= "m_wearer_std_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= " ORDER BY m_wearer_std_tran.upd_date DESC";

        $m_weare_std_tran = new MWearerStdTran();
        $results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];

        if (!empty($results_cnt)) {
            $paginator_model = new PaginatorModel(
                array(
                    "data"  => $results,
                    "limit" => 1,
                    "page" => 1
                )
            );
            $paginator = $paginator_model->getPaginate();
            $results = $paginator->items;
            foreach ($results as $result) {
                // 社員コード
                $list['cster_emply_cd'] = $result->as_cster_emply_cd;
                // 着用者名
                $list['werer_name'] = $result->as_werer_name;
                // 着用者名（読み仮名）
                $list['werer_name_kana'] = $result->as_werer_name_kana;
                // 発令日
                $list['appointment_ymd'] = $result->as_appointment_ymd;
                if (!empty($list['appointment_ymd'])) {
                    $list['appointment_ymd'] = date('Y/m/d', strtotime($list['appointment_ymd']));
                } else {
                    $list['appointment_ymd'] = '';
                }
                // 着用開始日
                $list['resfl_ymd'] = $result->as_resfl_ymd;
                if (!empty($list['resfl_ymd'])) {
                    $list['resfl_ymd'] = date('Y/m/d', strtotime($list['resfl_ymd']));
                } else {
                    $list['resfl_ymd'] = '';
                }
            }
            array_push($all_list, $list);
        }

        $json_list['wearer_info'] = $all_list;
    } elseif ($wearer_odr_post['wearer_tran_flg'] == '0') {
        //--着用者基本マスタトラン無の場合--//
        array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "m_wearer_std.rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
        array_push($query_list,"m_wearer_std.werer_cd = '".$wearer_odr_post['werer_cd']."'");
        array_push($query_list,"m_wearer_std.cster_emply_cd = '".$wearer_odr_post['cster_emply_cd']."'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
        $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
        $arg_str .= "m_wearer_std.werer_name_kana as as_werer_name_kana,";
        $arg_str .= "m_wearer_std.appointment_ymd as as_appointment_ymd";
        $arg_str .= " FROM ";
        $arg_str .= "m_wearer_std";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= " ORDER BY m_wearer_std.upd_date DESC";

        $m_weare_std = new MWearerStd();
        $results = new Resultset(null, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];

        if (!empty($results_cnt)) {
            $paginator_model = new PaginatorModel(
                array(
                    "data"  => $results,
                    "limit" => 1,
                    "page" => 1
                )
            );
            $paginator = $paginator_model->getPaginate();
            $results = $paginator->items;
            foreach ($results as $result) {
                // 社員コード
                $list['cster_emply_cd'] = $result->as_cster_emply_cd;
                // 着用者名
                $list['werer_name'] = $result->as_werer_name;
                // 着用者名（読み仮名）
                $list['werer_name_kana'] = $result->as_werer_name_kana;
                // 発令日
                $list['appointment_ymd'] = $result->as_appointment_ymd;
                if (!empty($list['appointment_ymd'])) {
                    $list['appointment_ymd'] = date('Y/m/d', strtotime($list['appointment_ymd']));
                } else {
                    $list['appointment_ymd'] = '';
                }
                // 着用開始日
                $list['resfl_ymd'] = $result->as_resfl_ymd;
                if (!empty($list['resfl_ymd'])) {
                    $list['resfl_ymd'] = date('Y/m/d', strtotime($list['resfl_ymd']));
                } else {
                    $list['resfl_ymd'] = '';
                }
            }

            array_push($all_list, $list);
        }

        $json_list['wearer_info'] = $all_list;
    }
    if(empty($json_list['wearer_info'])){
        if(!$wearer_odr_post['cster_emply_cd']){
            $cster_emply_cd = '';
        }else{
            $cster_emply_cd = $wearer_odr_post['cster_emply_cd'];
        }
        // 社員コード
        $list['cster_emply_cd'] = $cster_emply_cd;
        // 着用者名
        $list['werer_name'] = $wearer_odr_post['werer_name'];
        // 着用者名（読み仮名）
        $list['werer_name_kana'] = $wearer_odr_post['werer_name_kana'];
        // 発令日
        $list['appointment_ymd'] = $wearer_odr_post['appointment_ymd'];
        if (!empty($list['appointment_ymd'])) {
            $list['appointment_ymd'] = date('Y/m/d', strtotime($list['appointment_ymd']));
        } else {
            $list['appointment_ymd'] = '';
        }
        array_push($all_list, $list);
        $json_list['wearer_info'] = $all_list;
    }
    $param_list .= $wearer_odr_post['rntl_cont_no'].':';
    $param_list .= $wearer_odr_post['werer_cd'].':';
    $param_list .= $wearer_odr_post['cster_emply_cd'].':';
    $param_list .= $wearer_odr_post['sex_kbn'].':';
    $param_list .= $wearer_odr_post['rntl_sect_cd'].':';
    $param_list .= $wearer_odr_post['job_type_cd'].':';
    $param_list .= $wearer_odr_post['ship_to_cd'].':';
    $param_list .= $wearer_odr_post['ship_to_brnch_cd'].':';
    $param_list .= $wearer_odr_post['order_reason_kbn'].':';
    $param_list .= $wearer_odr_post['order_tran_flg'].':';
    $param_list .= $wearer_odr_post['wearer_tran_flg'].':';
    $param_list .= $wearer_odr_post['appointment_ymd'].':';
    $param_list .= $wearer_odr_post['resfl_ymd'];
    $json_list['param'] = $param_list;
    echo json_encode($json_list);
});

/*
 * 「拠点」のセレクトボックス変更時
 */
$app->post('/change_section', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');
    $cond = $params['cond'];

    $query_list = array();
    $list = array();
    $json_list = array();
    $m_shipment_to_list = array();
    //画面の「郵便番号」欄、「住所」欄の内容を動的に書き換える
    //--- 検索条件 ---//
    // 出荷先マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MShipmentTo.corporate_id = '".$auth['corporate_id']."'");
    // 出荷先マスタ．レンタル契約No.　＝　画面で選択されている契約No.
    array_push($query_list, "MShipmentTo.rntl_cont_no = '".$cond['agreement_no']."'");

    //出荷先」のセレクトボックスが「支店店舗と同じ」以外が選択状態の場合
    if ($cond['m_shipment_to_name'] != '支店店舗と同じ') {
        $m_shipment_to = explode(',', $cond['m_shipment_to']);
        //出荷先マスタ．出荷先コード　＝　画面で選択されている出荷先の出荷先コード　AND
        array_push($query_list, "MShipmentTo.ship_to_cd = '".$m_shipment_to[0]."'");
        //出荷先マスタ．出荷先支店コード　＝　画面で選択されている出荷先の出荷先支店コード
        array_push($query_list, "MShipmentTo.ship_to_brnch_cd = '".$m_shipment_to[1]."'");
    }
    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //--- クエリー実行・取得 ---//
    $q_str = MShipmentTo::query()
        ->where($query)
        ->columns(array('MShipmentTo.*'));
    // 「出荷先」のセレクトボックスが「支店店舗と同じ」が選択状態の場合
    if ($cond['m_shipment_to_name'] == '支店店舗と同じ') {
        $q_str->join('MSection', 'MShipmentTo.ship_to_cd = MSection.std_ship_to_cd AND MShipmentTo.ship_to_brnch_cd = MSection.std_ship_to_brnch_cd');
    }
    // 出荷先マスタ．出荷先コード　＝　部門マスタ．標準出荷先コード AND 出荷先マスタ．出荷先支店コード　＝　部門マスタ．標準出荷先支店コード
    $results = $q_str->execute();

    foreach ($results as $result) {
        $list['ship_to_cd'] = $result->ship_to_cd;
        $list['ship_to_brnch_cd'] = $result->ship_to_brnch_cd;
        $list['cust_to_brnch_name1'] = $result->cust_to_brnch_name1;
        $list['cust_to_brnch_name2'] = $result->cust_to_brnch_name2;
        $list['zip_no'] = $result->zip_no;
        $list['address'] = $result->address1.$result->address2.$result->address3.$result->address4;
        array_push($m_shipment_to_list, $list);
    }
    $json_list['change_m_shipment_to_list'] = $m_shipment_to_list;
    echo json_encode($json_list);
});

/*
 *  着用者のみ登録して終了
 */
$app->post('/input_insert', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);
//    $params = json_decode($_POST['data'], true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');
    $cond = $params['cond'];
    $query_list = array();
    $list = array();
    $json_list = array();
    $error_list = array();

    //更新可否チェック（更新可否チェック仕様書）

    //  入力された内容を元に、着用者基本マスタトラン、着用者商品マスタトラン、発注情報トランに登録を行う。
    //--- 検索条件 ---//
    //  アカウントマスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MAccount.corporate_id = '".$auth['corporate_id']."'");
    //  アカウントマスタ．ユーザーID　＝　ログインしているアカウントのユーザーID　AND
    array_push($query_list, "MAccount.user_id = '".$auth['user_id']."'");
    //　契約マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MContract.corporate_id = '".$auth['corporate_id']."'");
    //　契約マスタ．レンタル契約フラグ　＝　契約対象 AND
    array_push($query_list, "MContract.rntl_cont_flg = '1'");
    //  契約リソースマスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MContractResource.corporate_id = '".$auth['corporate_id']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $results = MContract::query()
        ->where($query)
        ->columns(array('MContractResource.*'))
        ->innerJoin('MContractResource','MContract.corporate_id = MContractResource.corporate_id')
        ->join('MAccount','MAccount.accnt_no = MContractResource.accnt_no')
        ->execute();
    if($results[0]->update_ok_flg == '0'){
      array_push($error_list,'こちらの契約リソースは更新出来ません。');
        $json_list['errors'] = $error_list;
        return;
    }
    //汎用コードマスタから更新不可時間を取得
    // 汎用コードマスタ．分類コード　＝　更新不可時間

    //--- クエリー実行・取得 ---//
    $m_gencode_results = MGencode::query()
        ->where("cls_cd = '015'")
        ->columns('*')
        ->execute();
    foreach ($m_gencode_results as $m_gencode_result) {
        if($m_gencode_result->gen_cd =='1'){
            //更新不可開始時間
            $start = $m_gencode_result->gen_name;
        }elseif($m_gencode_result->gen_cd =='2'){
            //経過時間
            $hour = $m_gencode_result->gen_name;

        }
    }
    $now_datetime = date("YmdHis");
    $now_date = date("Ymd");
    $start_datetime = $now_date.$start;
    $end_datetime = date("YmdHis", strtotime($start_datetime." + ".$hour." hour"));
    if(strtotime($start_datetime) <= strtotime($now_datetime)||strtotime($now_datetime) >= strtotime($end_datetime)){
        array_push($error_list,'現在の時間は更新出来ません。');
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);
        return;
    }
    //契約Noのマスタチェック
    $query_list = array();
    // 契約マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
    // 契約マスタ．レンタル契約No.　＝　画面で選択されている契約No.
    array_push($query_list,"rntl_cont_no = '".$cond['agreement_no']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //--- クエリー実行・取得 ---//
    $mc_count = MContract::find(array(
        'conditions' => $query
	))->count();
    //存在しない場合NG
    if($mc_count == 0){
        array_push($error_list,'契約Noの値が不正です。');
    }
    // 社員コード
    if ($cond['cster_emply_cd_chk']) {
        if (mb_strlen($cond['cster_emply_cd']) == 0) {
            $json_list["error_code"] = "1";
            $error_msg = "社員コードありにチェックしている場合、社員コードを入力してください。";
            array_push($error_list, $error_msg);
        }
    }
    if (!$cond['cster_emply_cd_chk']) {
        if (mb_strlen($cond['cster_emply_cd']) > 0) {
            $json_list["error_code"] = "1";
            $error_msg = "社員コードありにチェックしていない場合、社員コードの入力は不要です。";
            array_push($error_list, $error_msg);
        }
    }
    $query_list = array();
    //社員コードのマスタチェック(社員コードありの場合のみ)
    if($cond['cster_emply_cd']){
        // 着用者基本マスタ．客先社員コード ＝ 画面で入力された社員コード AND
        array_push($query_list,"cster_emply_cd = '".$cond['cster_emply_cd']."'");
        // 着用者基本マスタ．着用者状況区分 ＝ 稼働
        array_push($query_list,"werer_sts_kbn = '1'");

        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $query_list);
        //--- クエリー実行・取得 ---//
        $m_wearer_std_count = MWearerStd::find(array(
            'conditions' => $query
        ))->count();
        //存在する場合NG
        if($m_wearer_std_count > 0){
          array_push($error_list,'社員コードの値が不正です。');
        }
    }
    //拠点のマスタチェック
    $query_list = array();
    // 部門マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
    // 部門マスタ．レンタル契約No.　＝　画面で選択されている契約No.
    array_push($query_list,"rntl_cont_no = '".$cond['agreement_no']."'");
    // 部門マスタ．レンタル部門コード　＝　画面で選択されている拠点
    array_push($query_list,"rntl_sect_cd = '".$cond['rntl_sect_cd']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //--- クエリー実行・取得 ---//
    $m_section = MSection::find(array(
        'conditions' => $query
    ));
    $m_section_count = $m_section->count();
    //存在しない場合NG
    if($m_section_count == 0){
          array_push($error_list,'拠点の値が不正です。');
    }
    //貸与パターンのマスタチェック
    $query_list = array();
    // 職種マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
    // 職種マスタ．レンタル契約No.　＝　画面で選択されている契約No.
    array_push($query_list,"rntl_cont_no = '".$cond['agreement_no']."'");
    $deli_job = explode(',',$cond['job_type']);
    // 職種マスタ．レンタル部門コード　＝　画面で選択されている貸与パターン
    array_push($query_list,"job_type_cd = '".$deli_job[0]."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //--- クエリー実行・取得 ---//
    $m_job_type = MJobType::find(array(
        'conditions' => $query
    ));
    $m_job_type_cnt = $m_job_type->count();
    //存在しない場合NG
    if($m_job_type_cnt == 0){
          array_push($error_list,'貸与パターンの値が不正です。');
    }
    //出荷先のマスタチェック
    $query_list = array();
    // 出荷先マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");

    if($cond['ship_to_cd']){
        // 出荷先マスタ．出荷先コード　＝　画面で選択されている出荷先コード
        array_push($query_list,"ship_to_cd = '".$cond['ship_to_cd']."'");
        // 出荷先マスタ．出荷先支店コード　＝　画面で選択されている出荷先支店コード
        array_push($query_list,"ship_to_brnch_cd = '".$cond['ship_to_brnch_cd']."'");
    }else{
        // 部門マスタ．標準出荷先コード
        array_push($query_list,"ship_to_cd = '".$m_section[0]->std_ship_to_cd."'");
        // 部門マスタ．標準出荷先支店コード
        array_push($query_list,"ship_to_brnch_cd = '".$m_section[0]->std_ship_to_brnch_cd."'");
        $cond['ship_to_cd'] = $m_section[0]->std_ship_to_cd;
        $cond['std_ship_to_brnch_cd'] = $m_section[0]->std_ship_to_brnch_cd;
    }
    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //--- クエリー実行・取得 ---//
    $m_shipment_to_cnt = MShipmentTo::find(array(
        'conditions' => $query
    ))->count();

    //存在しない場合NG
    if($m_shipment_to_cnt == 0){
          array_push($error_list,'出荷先の値が不正です。');
    }

    if (byte_cnt($cond['cster_emply_cd']) > 10) {
        array_push($error_list, '社員コードの文字数が多すぎます。');
    }

    if (byte_cnt($cond['werer_name']) > 22) {
        array_push($error_list, '着用者名の文字数が多すぎます。');
    }

    if (byte_cnt($cond['werer_name_kana']) > 22) {
        array_push($error_list, '着用者名(カナ)の文字数が多すぎます。');
    }

//    DB登録
    if($error_list){
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);
        return true;
    }
    if($params['mode']=='check'){
        $json_list['ok'] = 'ok';
        echo json_encode($json_list);
        return;
    }
    $transaction = $app->transactionManager->get();

    //着用者基本マスタトラン
    $m_wearer_std_tran = new MWearerStdTran();
    $m_wearer_std_tran->setTransaction($transaction);
    $now = date('Y/m/d H:i:s.sss');

    //着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
    $wearer_odr_post = $app->session->get("wearer_odr_post");
    if(isset($wearer_odr_post['m_wearer_std_comb_hkey'])&&$wearer_odr_post['m_wearer_std_comb_hkey']){
        //前画面からデータを引き継いでいる場合
        $m_wearer_std_tran->werer_cd = $wearer_odr_post['werer_cd'];
        $m_wearer_std_tran->m_wearer_std_comb_hkey = $wearer_odr_post['m_wearer_std_comb_hkey'];
        $m_wearer_std_tran->corporate_id = $auth['corporate_id']; //企業ID
        $m_wearer_std_tran->rntl_cont_no = $cond['agreement_no']; //レンタル契約No.
        $m_wearer_std_tran->rntl_sect_cd = $cond['rntl_sect_cd']; //レンタル部門コード
        $m_wearer_std_tran->job_type_cd = $deli_job[0];//職種コード
        $m_wearer_std_tran->order_req_no = $wearer_odr_post['order_req_no']; //発注No
        $create_flg = false;
    }else{
        $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("select nextval('werer_cd_seq')"));
        $m_wearer_std_tran->werer_cd = str_pad($results[0]->nextval, 10, '0', STR_PAD_LEFT); //着用者コード
        $m_wearer_std_tran->corporate_id = $auth['corporate_id']; //企業ID
        $m_wearer_std_tran->m_wearer_std_comb_hkey = md5($auth['corporate_id'].str_pad($results[0]->nextval, 10, '0', STR_PAD_LEFT).$cond['agreement_no'].$cond['rntl_sect_cd'].$deli_job[0]);
        $m_wearer_std_tran->rntl_cont_no = $cond['agreement_no']; //レンタル契約No.
        $m_wearer_std_tran->rntl_sect_cd = $cond['rntl_sect_cd']; //レンタル部門コード
        $m_wearer_std_tran->job_type_cd = $deli_job[0];//職種コード
        $m_wearer_std_tran->order_req_no = '1'; //発注No
        $create_flg = true;
    }
    $m_wearer_std_tran->cster_emply_cd = $cond['cster_emply_cd'];//客先社員コード
    $m_wearer_std_tran->werer_name = $cond['werer_name'];//着用者名（漢字）
    $m_wearer_std_tran->werer_name_kana = $cond['werer_name_kana']; //着用者名（カナ）
    $m_wearer_std_tran->sex_kbn = $cond['sex_kbn'];//性別区分
    $m_wearer_std_tran->werer_sts_kbn  = '7';//着用者状況区分
    $m_wearer_std_tran->appointment_ymd = date("Ymd", strtotime($cond['appointment_ymd']));//発令日
    $m_wearer_std_tran->resfl_ymd = date("Ymd", strtotime($cond['resfl_ymd']));//着用開始日
    $m_wearer_std_tran->ship_to_cd = $cond['ship_to_cd']; //出荷先コード
    $m_wearer_std_tran->ship_to_brnch_cd = $cond['ship_to_brnch_cd']; //出荷先支店コード
    $m_wearer_std_tran->rntl_cont_no_bef = ''; //レンタル契約No.（前）
    $m_wearer_std_tran->rntl_sect_cd_bef = '';//レンタル部門コード（前）
    $m_wearer_std_tran->job_type_cd_bef = ''; //職種コード（前）
    $m_wearer_std_tran->werer_sts_kbn_bef = ''; //着用者状況区分（前）
    $m_wearer_std_tran->resfl_ymd_bef = ''; //異動日（前）
    $m_wearer_std_tran->order_sts_kbn = '1'; //発注状況区分 汎用コード：貸与
    $m_wearer_std_tran->upd_kbn = '1';//更新区分　汎用コード：web発注システム（新規登録）
    $m_wearer_std_tran->web_upd_date = $now;//WEB更新日付
    $m_wearer_std_tran->snd_kbn = '0';//送信区分
    $m_wearer_std_tran->snd_date  = $now;//送信日時
    $m_wearer_std_tran->del_kbn ='0';//削除区分
    $m_wearer_std_tran->rgst_date  = $now;//登録日時
    $m_wearer_std_tran->rgst_user_id = $auth['accnt_no'];//登録ユーザーID
    $m_wearer_std_tran->upd_date  = $now;//更新日時
    $m_wearer_std_tran->upd_user_id = $auth['accnt_no'];//更新ユーザーID
    $m_wearer_std_tran->upd_pg_id = $auth['accnt_no'];//更新プログラムID
    $m_wearer_std_tran->m_job_type_comb_hkey = $m_job_type[0]->m_job_type_comb_hkey;//職種マスタ_統合ハッシュキー
    $m_wearer_std_tran->m_section_comb_hkey = $m_section[0]->m_section_comb_hkey;//部門マスタ_統合ハッシュキー
    if($create_flg){
        $m_wearer_std_tran->m_section_comb_hkey = $m_section[0]->m_section_comb_hkey;//部門マスタ_統合ハッシュキー
        //新規作成
        if ($m_wearer_std_tran->create() == false) {
            array_push($error_list, '着用者の登録に失敗しました。');
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);

            return true;
        } else {
            $transaction->commit();
        }

    }else{
        //更新
        if ($m_wearer_std_tran->update() == false) {
            array_push($error_list, '着用者の更新に失敗しました。');
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);

            return true;
        } else {
            $transaction->commit();
        }

    }
    $app->session->remove("wearer_odr_post");
    echo json_encode($json_list);
    return;
});

/*
 *  着用者取消チェック
 */
$app->post('/input_delete_check', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);
    $wearer_odr_post = $app->session->get("wearer_odr_post");
    $json_list = array();
    $error_list = array();
    //--着用者基本マスタトラン削除--//
    // 発注情報トランを参照
    $query_list = array();
    array_push($query_list, "t_order_tran.m_wearer_std_comb_hkey = '".$wearer_odr_post['m_wearer_std_comb_hkey']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;

    $t_order_tran = new TOrderTran();
    $results = new Resultset(null, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if ($results_cnt == 0) {
        $json_list['ok'] = 'ok';
        echo json_encode($json_list);

        return true;
    } else {
        array_push($error_list, '先に商品明細を削除してください。');
        $json_list['error_msg'] = $error_list;
        echo json_encode($json_list);

        return true;
    }

});

/*
 *  着用者取消
 */
$app->post('/input_delete', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);
    try{
        $query_list = array();
        $wearer_odr_post = $app->session->get("wearer_odr_post");
        array_push($query_list, "m_wearer_std_tran.m_wearer_std_comb_hkey = '".$wearer_odr_post['m_wearer_std_comb_hkey']."'");
        // 発注区分「着用者編集」ではない
        array_push($query_list, "m_wearer_std_tran.order_sts_kbn <> '6'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "DELETE FROM ";
        $arg_str .= "m_wearer_std_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;

        $transaction = $app->transactionManager->get();
        //着用者基本マスタトラン
        $m_wearer_std_tran = new MWearerStdTran();
        $m_wearer_std_tran->setTransaction($transaction);
        $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
        if ($results_cnt == 0) {
            array_push($error_list, '着用者の取消に失敗しました。');
            $json_list['error_msg'] = $error_list;
            echo json_encode($json_list);
            $transaction->rollback();
            return;
        }else{
            $json_list['ok'] = 'ok';
            echo json_encode($json_list);
            $app->session->get("wearer_odr_post");
            $transaction->commit();
            return;
        }
    } catch (Exception $e) {
        array_push($error_list, '着用者の取消に失敗しました。');
        $json_list['error_msg'] = $error_list;
        echo json_encode($json_list);
        $transaction->rollback();
    }
    return;
});
function byte_cnt($data)
{
    //変換前文字コード
    $bf = 'UTF-8';
    //変換後文字コード
    $af = 'Shift-JIS';

    return strlen(bin2hex(mb_convert_encoding($data, $af, $bf))) / 2;
}
