<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * 発注入力（その他交換）
 * 入力項目：初期値情報、前画面セッション取得
 *
 */
$app->post('/wearer_other_change_info', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    $json_list = array();
    // 前画面セッション取得
    $wearer_size_change_post = $app->session->get("wearer_size_change_post");
    if(empty($wearer_size_change_post)){
        $json_list['no_session_flg'] = '1';
        echo json_encode($json_list);
        return;
    }

    //--着用者入力項目情報--//
    $all_list = array();
    $list = array();
    $json_list['wearer_info'] = "";

    //理由区分
    //--発注管理単位取得--//
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
    array_push($query_list, "job_type_cd = '".$wearer_size_change_post['job_type_cd']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = '';
    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_job_type';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;

    $m_job_type = new MJobType();
    $results = new Resultset(null, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];

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
        $order_control_unit = $result->order_control_unit;
    }

    //--理由区分リスト取得--//
    $query_list = array();
    $all_list = array();
    array_push($query_list, "cls_cd = '002'");
    array_push($query_list, "relation_cls_cd = '001'");
    array_push($query_list, "relation_gen_cd = '4'");
    array_push($query_list, "relation_cls_cd_2 = '003'");
    array_push($query_list, "relation_gen_cd_2 = '".$order_control_unit."'");
    $query = implode(' AND ', $query_list);

    $arg_str = '';
    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_gencode';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY dsp_order asc';

    $m_gencode = new MGencode();
    $results = new Resultset(null, $m_gencode, $m_gencode->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];

    if ($results_cnt > 0) {

        foreach ($results as $result) {
            $list['reason_kbn'] = $result->gen_cd;
            $list['reason_kbn_name'] = $result->gen_name;

            // 発注情報トランフラグ有の場合は初期選択状態版を生成
            if ($wearer_size_change_post['order_req_no']) {
                if ($list['reason_kbn'] == $wearer_size_change_post['order_reason_kbn']) {
                    $list['selected'] = 'selected';
                    $json_list['disabled'] = 'disabled';
                } else {
                    $list['selected'] = '';
                }
            } else {
                $list['selected'] = '';
            }

            array_push($all_list, $list);
        }
    } else {
        $list['reason_kbn'] = null;
        $list['reason_kbn_name'] = '';
        $list['selected'] = '';
        array_push($all_list, $list);
    }
    $json_list['reason_kbn_list'] = $all_list;

    // 発注情報トラン参照
    $all_list = array();
    $query_list = array();
    array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
    array_push($query_list, "t_order_tran.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
    array_push($query_list, "t_order_tran.rntl_sect_cd = '".$wearer_size_change_post['rntl_sect_cd']."'");
    array_push($query_list, "t_order_tran.job_type_cd = '".$wearer_size_change_post['job_type_cd']."'");
    // 発注状況区分(その他交換)
    array_push($query_list,"t_order_tran.order_sts_kbn = '4'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT distinct on (order_req_no) ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $t_order_tran = new TOrderTran();
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    // コメント欄
    $comment = "";
    //返却日
    $return_date = null;
    $list['return_date'] = null;
    if (!empty($results_cnt)) {
        foreach ($results as $result) {
            $comment = $result->memo;
            if($result->order_req_no){
                //返却日が返却予定情報トランにあれば取得する
                $t_returned_plan_info_trans = TReturnedPlanInfoTran::find(array('conditions' => 'order_req_no = '."'".$result->order_req_no."'"));

                foreach ($t_returned_plan_info_trans as $t_returned_plan_info_tran) {
                    $return_date = $t_returned_plan_info_tran->return_date;
                }
            }
        }
    }

    $sex_kbn='';
    // 着用者基本マスタトラン参照
    $query_list = array();
    array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
    array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
    array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_size_change_post['rntl_sect_cd']."'");
    array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_size_change_post['job_type_cd']."'");
    //発注状況区分 その他交換
    array_push($query_list, "m_wearer_std_tran.order_sts_kbn = '4'");

    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana,";
    $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn";
    $arg_str .= " FROM ";
    $arg_str .= "m_wearer_std_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY m_wearer_std_tran.upd_date DESC";
    $m_weare_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if (!empty($results_cnt)) {
        // 着用者基本マスタトラン（その他交換）有り
        $json_list['tran_flg'] = "1";

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
            // 性別
            $sex_kbn = $result->as_sex_kbn;
            $list['comment'] = $comment;
            if($return_date){
                $list['return_date'] = date("Y/m/d",strtotime($return_date));
            }
        }
        array_push($all_list, $list);
    }
    // 上記参照のトラン情報がない場合、着用者基本マスタ情報（着用者編集）を参照する
    if (empty($all_list)) {

        $query_list = array();
        array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
        array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
        array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_size_change_post['rntl_sect_cd']."'");
        array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_size_change_post['job_type_cd']."'");
        //発注状況区分　着用者編集
        array_push($query_list, "m_wearer_std_tran.order_sts_kbn = '6'");


        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
        $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
        $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana,";
        $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn";
        $arg_str .= " FROM ";
        $arg_str .= "m_wearer_std_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= " ORDER BY m_wearer_std_tran.upd_date DESC";
        $m_weare_std_tran = new MWearerStdTran();
        $results = new Resultset(NULL, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
        if (!empty($results_cnt)) {
            // 着用者基本マスタトラン（着用者編集）有り
            $json_list['tran_flg'] = "1";

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
                // 性別
                $sex_kbn = $result->as_sex_kbn;
                $list['comment'] = $comment;
                if($return_date){
                    $list['return_date'] = date("Y/m/d",strtotime($return_date));
                }
            }
            array_push($all_list, $list);

        }else{
            // 着用者基本マスタトラン（着用者編集）無し
            $json_list['tran_flg'] = "0";

            $query_list = array();
            array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "m_wearer_std.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
            array_push($query_list, "m_wearer_std.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
            $query = implode(' AND ', $query_list);

            $arg_str = "";
            $arg_str = "SELECT ";
            $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
            $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
            $arg_str .= "m_wearer_std.werer_name_kana as as_werer_name_kana,";
            $arg_str .= "m_wearer_std.sex_kbn as as_sex_kbn";
            $arg_str .= " FROM ";
            $arg_str .= "m_wearer_std";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $arg_str .= " ORDER BY m_wearer_std.upd_date DESC";
            //ChromePhp::LOG($arg_str);
            $m_weare_std = new MWearerStd();
            $results = new Resultset(NULL, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
            $result_obj = (array)$results;
            $results_cnt = $result_obj["\0*\0_count"];
            if (!empty($results_cnt)) {
                $list = array();
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
                    // 性別
                    $sex_kbn = $result->as_sex_kbn;
                    $list['comment'] = $comment;
                    $list['return_date'] = null;
                }

                array_push($all_list, $list);
            }
        }
    }
    $json_list['wearer_info'] = $all_list;

    //--契約No--//
    $all_list = array();
    $json_list['agreement_no_list'] = "";
    $query_list = array();
    $list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = '';
    $arg_str = 'SELECT ';
    $arg_str .= '*';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_contract';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY rntl_cont_no ASC';
    $m_contract = new MContract();
    $results = new Resultset(NULL, $m_contract, $m_contract->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    if ($results_cnt > 0) {
        $list = array();
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
            $list['rntl_cont_no'] = $result->rntl_cont_no;
            $list['rntl_cont_name'] = $result->rntl_cont_name;
            array_push($all_list, $list);
        }
    } else {
        $list['rntl_cont_no'] = null;
        $list['rntl_cont_name'] = '';
        array_push($all_list, $list);
    }
    $json_list['agreement_no_list'] = $all_list;

    //--性別区分--//
    $query_list = array();
    $list = array();
    $all_list = array();
    array_push($query_list, "cls_cd = '004'");
    $query = implode(' AND ', $query_list);

    $arg_str = '';
    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_gencode';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;

    $m_gencode = new MGencode();
    $results = new Resultset(NULL, $m_gencode, $m_gencode->getReadConnection()->query($arg_str));
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
            $list['sex_kbn'] = $result->gen_cd;
            $list['sex_kbn_name'] = $result->gen_name;
            // 初期選択状態版を生成
            if ($list['sex_kbn'] == $sex_kbn) {
                $list['selected'] = 'selected';
            } else {
                $list['selected'] = '';
            }

            array_push($all_list, $list);
        }
    } else {
        $list['sex_kbn'] = NULL;
        $list['sex_kbn_name'] = '';
        $list['selected'] = '';
        array_push($all_list, $list);
    }
    $json_list['sex_kbn_list'] = $all_list;

    //--拠点--//
    $all_list = array();
    $json_list['section_list'] = "";
    $query_list = array();
    $list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_size_change_post['rntl_sect_cd']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = '';
    $arg_str = 'SELECT ';
    $arg_str .= '*';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_section';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY rntl_sect_cd asc';
    $m_section = new MSection();
    $results = new Resultset(NULL, $m_section, $m_section->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    if ($results_cnt > 0) {
        $list = array();
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
            $list['rntl_sect_cd'] = $result->rntl_sect_cd;
            $list['rntl_sect_name'] = $result->rntl_sect_name;
            array_push($all_list, $list);
        }
    } else {
        $list['rntl_sect_cd'] = null;
        $list['rntl_sect_name'] = '';
        array_push($all_list, $list);
    }
    $json_list['section_list'] = $all_list;

    //--貸与パターン--//
    $all_list = array();
    $json_list['job_type_list'] = "";
    $query_list = array();
    $list = array();

    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
    array_push($query_list, "job_type_cd = '".$wearer_size_change_post['job_type_cd']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = '';
    $arg_str = 'SELECT ';
    $arg_str .= '*';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_job_type';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY job_type_cd asc';
    $m_job_type = new MJobType();
    $results = new Resultset(NULL, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);
    if ($results_cnt > 0) {
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
            $list['job_type_cd'] = $result->job_type_cd;
            $list['job_type_name'] = $result->job_type_name;
            $list['sp_job_type_flg'] = $result->sp_job_type_flg;
            array_push($all_list, $list);
        }
    } else {
        $list['job_type_cd'] = null;
        $list['job_type_name'] = '';
        $list['sp_job_type_flg'] = '0';
        array_push($all_list, $list);
    }
    $json_list['job_type_list'] = $all_list;
    //--出荷先--//
    $all_list = array();
    $list = array();
    $list['ship_to_cd'] = $wearer_size_change_post['ship_to_cd'];
    $list['ship_to_brnch_cd'] = $wearer_size_change_post['ship_to_brnch_cd'];
    array_push($all_list, $list);
    $json_list['shipment_list'] = $all_list;
    //--発注情報トラン・返却予定情報トラン内、「その他交換」情報の有無確認--//
    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_order_tran.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
    array_push($query_list, "t_order_tran.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
    array_push($query_list, "t_order_tran.rntl_sect_cd = '".$wearer_size_change_post['rntl_sect_cd']."'");
    array_push($query_list, "t_order_tran.job_type_cd = '".$wearer_size_change_post['job_type_cd']."'");
    // 発注状況区分(その他交換)
    array_push($query_list,"t_order_tran.order_sts_kbn = '4'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "order_req_no";
    $arg_str .= " FROM ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY t_order_tran.upd_date DESC";
    //ChromePhp::LOG($arg_str);
    $t_order_tran = new TOrderTran();
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
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
            // 発注情報トラン.発注No
            $json_list['order_req_no'] = $result->order_req_no;
            // 発注情報トランフラグ
            $json_list['order_tran_flg'] = "1";
        }
    } else {
        // 発注情報トラン.発注No
        $json_list['order_req_no'] = "";
        // 発注情報トランフラグ
        $json_list['order_tran_flg'] = "0";
    }
    // ※返却予定情報トラン参照
    $query_list = array();
    array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
    array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
    array_push($query_list, "t_returned_plan_info_tran.rntl_sect_cd = '".$wearer_size_change_post['rntl_sect_cd']."'");
    array_push($query_list, "t_returned_plan_info_tran.job_type_cd = '".$wearer_size_change_post['job_type_cd']."'");
    // 発注状況区分(その他交換)
    array_push($query_list,"t_returned_plan_info_tran.order_sts_kbn = '4'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "order_req_no";
    $arg_str .= " FROM ";
    $arg_str .= "t_returned_plan_info_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY order_req_no DESC";
    //ChromePhp::LOG($arg_str);
    $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
    $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
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
            // 返却予定情報トラン.発注No
            $json_list['return_req_no'] = $result->order_req_no;
            // 返却予定情報トランフラグ
            $json_list['return_tran_flg'] = "1";
        }
    } else {
        // 返却予定情報トラン.発注No
        $json_list['return_req_no'] = "";
        // 返却予定情報トランフラグ
        $json_list['return_tran_flg'] = "0";
    }

    //--前画面セッション情報--//
    // レンタル契約No
    $json_list['rntl_cont_no'] = $wearer_size_change_post["rntl_cont_no"];
    // 部門コード
    $json_list['rntl_sect_cd'] = $wearer_size_change_post["rntl_sect_cd"];
    // 貸与パターン
    $json_list['job_type_cd'] = $wearer_size_change_post["job_type_cd"];
    // 着用者コード
    $json_list['werer_cd'] = $wearer_size_change_post["werer_cd"];
    // 着用者基本マスタトランフラグ
    $json_list['wearer_tran_flg'] = $wearer_size_change_post["wearer_tran_flg"];

    //ダイナム様専用仕様
    if($auth['corporate_id'] == CORPORATE_ID_DYNAM){
        $json_list['dynam_msg'] = '※紛失・破損は個人請求となります。';
    }

    echo json_encode($json_list);
});

/**
 * 発注入力（その他交換）
 * 入力項目：発注商品一覧
 */
$app->post('/wearer_other_change_list', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_size_change_post = $app->session->get("wearer_size_change_post");
    //個体管理番号表示フラグ
    $individual_flg = individual_flg($auth['corporate_id'], $wearer_size_change_post['rntl_cont_no']);

    // フロントパラメータ取得
    $cond = $params['data'];

    $json_list = array();
    $all_list = array();
    // 返却予定情報トラン(その他交換発注履歴)確認
    $tran_list = array();
    $list = array();
    $query_list = array();
    array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
    array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
    array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_size_change_post['rntl_sect_cd']."'");
    array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_size_change_post['job_type_cd']."'");
    // 発注状況区分(その他交換)
    array_push($query_list,"t_returned_plan_info_tran.order_sts_kbn = '4'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (t_returned_plan_info_tran.individual_ctrl_no,t_returned_plan_info_tran.item_cd, t_returned_plan_info_tran.color_cd, t_returned_plan_info_tran.size_cd) ";
    $arg_str .= "t_returned_plan_info_tran.item_cd as as_order_item_cd,";
    $arg_str .= "t_returned_plan_info_tran.color_cd as as_order_color_cd,";
    $arg_str .= "t_returned_plan_info_tran.size_cd as as_order_size_cd,";
    $arg_str .= "t_returned_plan_info_tran.return_plan_qty as as_return_plan_qty";
    $arg_str .= " FROM ";
    $arg_str .= "m_wearer_std_tran INNER JOIN t_returned_plan_info_tran";
    $arg_str .= " ON (m_wearer_std_tran.corporate_id = t_returned_plan_info_tran.corporate_id";
    $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = t_returned_plan_info_tran.rntl_cont_no";
    $arg_str .= " AND m_wearer_std_tran.werer_cd = t_returned_plan_info_tran.werer_cd";
    $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = t_returned_plan_info_tran.rntl_sect_cd";
    $arg_str .= " AND m_wearer_std_tran.job_type_cd = t_returned_plan_info_tran.job_type_cd)";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_order_item_cd ASC, as_order_color_cd ASC";
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if (!empty($results_cnt)) {
        // 発注情報トランに情報が存在する場合、こちらで商品一覧生成
        $arr_num = 0;
        $list_cnt = 1;
        foreach ($results as $result) {
            // 商品コード
            $list["item_cd"] = $result->as_order_item_cd;
            // 色コード
            $list["color_cd"] = $result->as_order_color_cd;
            // サイズコード
            $list["size_cd"] = $result->as_order_size_cd;
            // 返却予定数
            $list["return_plan_qty"] = $result->as_return_plan_qty;

            array_push($tran_list, $list);
        }

        // 商品情報取得
        $list = array();
        $query_list = array();
        array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
        array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
        array_push($query_list, "t_delivery_goods_state_details.rtn_ok_flg = '1'");
        array_push($query_list, "t_delivery_goods_state_details.receipt_status = '2'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= " *, ";
        $arg_str .= " count(*) as_rtn_ok_cnt";
        $arg_str .= " FROM ";
        $arg_str .= "(SELECT distinct on (t_delivery_goods_state_details.individual_ctrl_no, m_item.item_cd, m_item.color_cd, m_item.size_cd) ";
        $arg_str .= "t_delivery_goods_state_details.quantity as as_quantity,";
        $arg_str .= "t_delivery_goods_state_details.return_plan__qty as as_return_plan_qty,";
        $arg_str .= "t_delivery_goods_state_details.returned_qty as as_returned_qty,";
        $arg_str .= "m_item.item_cd as as_item_cd,";
        $arg_str .= "m_item.color_cd as as_color_cd,";
        $arg_str .= "m_item.size_cd as as_size_cd,";
        $arg_str .= "m_item.item_name as as_item_name,";
        $arg_str .= "m_input_item.job_type_item_cd as as_job_type_item_cd,";
        $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
        $arg_str .= "m_input_item.std_input_qty as as_std_input_qty";
        $arg_str .= " FROM ";
        $arg_str .= "t_delivery_goods_state_details INNER JOIN m_item";
        $arg_str .= " ON (t_delivery_goods_state_details.corporate_id = m_item.corporate_id";
        $arg_str .= " AND t_delivery_goods_state_details.item_cd = m_item.item_cd";
        $arg_str .= " AND t_delivery_goods_state_details.color_cd = m_item.color_cd";
        $arg_str .= " AND t_delivery_goods_state_details.size_cd = m_item.size_cd)";
        $arg_str .= " INNER JOIN m_input_item";
        $arg_str .= " ON (m_item.corporate_id = m_item.corporate_id";
        $arg_str .= " AND m_item.item_cd = m_input_item.item_cd";
        $arg_str .= " AND m_item.color_cd = m_input_item.color_cd)";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= ") as distinct_table";
        $arg_str .= " GROUP BY as_quantity,
         as_return_plan_qty,
         as_returned_qty,
         as_item_cd,
         as_color_cd,
         as_size_cd,
         as_job_type_item_cd,
         as_std_input_qty,
         as_item_name,
         as_input_item_name,
         as_std_input_qty";
        $arg_str .= " ORDER BY as_item_cd ASC, as_color_cd ASC";
        $m_input_item = new MInputItem();
        $item_results = new Resultset(null, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
        $result_obj = (array)$item_results;
        $results_cnt = $result_obj["\0*\0_count"];
        if (!empty($results_cnt)) {
            foreach ($item_results as $item_result) {
                // name属性用カウント値
                $list["arr_num"] = $arr_num++;
                // No
                $list["list_no"] = $list_cnt++;
                // アイテム
                $list["item_name"] = $item_result->as_item_name;
                // 数量
                if(isset($item_result->as_quantity)){
                    $list["quantity"] = $item_result->as_quantity;
                }else{
                    $list["quantity"] = '0';
                }
                // 返却予定数
                $list["return_plan_qty"] = $item_result->as_return_plan_qty;
                // 返却済数
                $list["returned_qty"] = $item_result->as_returned_qty;
                // 標準枚数
                $list["possible_num"] = $item_result->as_std_input_qty;
//                $list["possible_num"] = $list["quantity"] - $list["return_plan_qty"] - $list["returned_qty"];
                // 商品コード
                $list["item_cd"] = $item_result->as_item_cd;
                // 色コード
                $list["color_cd"] = $item_result->as_color_cd;
                // 商品-色
                $list["item_and_color"] = $list["item_cd"]."-".$list["color_cd"];
                // 商品名
                $list["input_item_name"] = $item_result->as_input_item_name;
                // サイズ
                $list["size_cd"] = $item_result->as_size_cd;
                //返却数、発注数初期化
                $list["return_num"] = 0;
                $list["order_num"] = 0;
                // 対象、個体管理番号
                if ($individual_flg == "1") {
                    $list["order_num_disable"] = "disabled";
                    $list["return_num_disable"] = "disabled";
                    // 返却予定情報トラン参照（個体管理番号取得）
                    $individual_list = array();
                    $query_list = array();
                    array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
                    array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
                    array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
                    array_push($query_list, "t_returned_plan_info_tran.item_cd = '".$list["item_cd"]."'");
                    array_push($query_list, "t_returned_plan_info_tran.color_cd = '".$list["color_cd"]."'");
                    array_push($query_list, "t_returned_plan_info_tran.size_cd = '".$list["size_cd"]."'");
                    // 発注状況区分(その他交換)
                    array_push($query_list,"t_returned_plan_info_tran.order_sts_kbn = '4'");
                    $query = implode(' AND ', $query_list);

                    $arg_str = "";
                    $arg_str = "SELECT ";
                    $arg_str .= "individual_ctrl_no";
                    $arg_str .= " FROM ";
                    $arg_str .= "t_returned_plan_info_tran";
                    $arg_str .= " WHERE ";
                    $arg_str .= $query;
                    $arg_str .= " ORDER BY individual_ctrl_no ASC";
                    $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
                    $t_returned_plan_info_tran_results = new Resultset(null, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
                    $result_obj = (array)$t_returned_plan_info_tran_results;
                    $results_cnt = $result_obj["\0*\0_count"];
                    if (!empty($results_cnt)) {
                        foreach ($t_returned_plan_info_tran_results as $t_returned_plan_info_tran_result) {
                            array_push($individual_list, $t_returned_plan_info_tran_result->individual_ctrl_no);
                        }
                    }

                    // 納品状況明細情報参照
                    $list["individual_chk"] = array();
                    $element = array();
                    $list["individual_ctrl_no"] = array();
                    $query_list = array();
                    array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
                    array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
                    array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
                    array_push($query_list, "t_delivery_goods_state_details.item_cd = '".$list["item_cd"]."'");
                    array_push($query_list, "t_delivery_goods_state_details.color_cd = '".$list["color_cd"]."'");
                    array_push($query_list, "t_delivery_goods_state_details.size_cd = '".$list["size_cd"]."'");
                    array_push($query_list, "t_delivery_goods_state_details.rtn_ok_flg = '1'");
                    $query = implode(' AND ', $query_list);

                    $arg_str = "";
                    $arg_str = "SELECT ";
                    $arg_str .= " * ";
                    $arg_str .= " FROM ";
                    $arg_str .= "t_delivery_goods_state_details";
                    $arg_str .= " WHERE ";
                    $arg_str .= $query;
                    $arg_str .= " ORDER BY individual_ctrl_no ASC";
                    $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
                    $t_delivery_goods_state_details_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
                    $result_obj = (array)$t_delivery_goods_state_details_results;
                    $results_cnt = $result_obj["\0*\0_count"];
                    $paginator_model = new PaginatorModel(
                        array(
                            "data"  => $t_delivery_goods_state_details_results,
                            "limit" => $results_cnt,
                            "page" => 1
                        )
                    );
                    $paginator = $paginator_model->getPaginate();
                    $t_delivery_goods_state_details_results = $paginator->items;
                    $i = 0;
                    $return_num = 0;
                    foreach ($t_delivery_goods_state_details_results as $t_delivery_goods_state_details_result) {
                        // 返却予定情報トランの個体管理番号があるか確認
                        $element["checked"] = "";
                        if (!empty($individual_list)) {
                            foreach ($individual_list as $tran_individual_ctrl_no) {
                                if ($t_delivery_goods_state_details_result->individual_ctrl_no == $tran_individual_ctrl_no) {
                                    $element["checked"] = "checked";
                                    // 返却枚数
                                    // ※返却予定情報トランに存在する場合はこちらを設定
                                    $return_num += 1;
                                }
                            }
                        }
                        $list["return_num"] = $return_num;
                        $list["order_num"] = $return_num;
                        array_push($list["individual_ctrl_no"], $t_delivery_goods_state_details_result->individual_ctrl_no);
                        $element["name_no"] = $list["arr_num"];
                        $element["individual_ctrl_no"] = $t_delivery_goods_state_details_result->individual_ctrl_no;
                        if ($results_cnt - 1 !== $i) {
                            $element["br"] = "<br/>";
                        } else {
                            $element["br"] = "";
                        }
                        array_push($list["individual_chk"], $element);
                    }
                    // 個体管理番号数
                    $list["individual_cnt"] = count($list["individual_ctrl_no"]);
                    // 個体管理番号(val)
                    $list["individual_ctrl_no_val"] =  $list["individual_ctrl_no"];
                    // 個体管理番号(表示用)
                    $list["individual_ctrl_no"] = implode("<br/>", $list["individual_ctrl_no"]);
                }else{
                    // 交換可能枚数
                    $list["rtn_ok_cnt"] = $list["quantity"];

                    foreach ($tran_list as $tran_map) {
                        if (
                            $list["item_cd"] == $tran_map["item_cd"] &&
                            $list["color_cd"] == $tran_map["color_cd"] &&
                            $list["size_cd"] == $tran_map["size_cd"]
                        )
                        {
                            $list["return_num"] += $tran_map["return_plan_qty"];
                            $list["order_num"] += $tran_map["return_plan_qty"];
                        }
                    }
                }
                if(!$list["return_num"]){
                    $list["return_num"] = '0';
                }
                if(!$list["order_num"]){
                    $list["order_num"] = '0';
                }
                // 個体管理番号表示フラグ
                if ($individual_flg == "1") {
                    $list["individual_flg"] = true;
                } else {
                    $list["individual_flg"] = false;
                }

                //--その他の必要hiddenパラメータ--//
                // 部門コード
                $list["rntl_sect_cd"] = $wearer_size_change_post["rntl_sect_cd"];
                // 職種コード
                $list["job_type_cd"] = $wearer_size_change_post['job_type_cd'];
                // 職種アイテムコード
                $list["job_type_item_cd"] = $item_result->as_job_type_item_cd;

                array_push($all_list, $list);
            }
        }
        // 返却総枚数(返却可能枚数)
        $json_list["sum_num"] = array();
        $sum_list = array();
        $sum_list["sum_return_num"] = '0';
        $sum_list["sum_order_num"] = '0';
        if (!empty($all_list)) {
            $sum_return_num = 0;
            $sum_order_num = 0;
            foreach ($all_list as $all_map) {
                $sum_return_num += $all_map["return_num"];
            }
            $sum_list["sum_return_num"] = $sum_return_num;
            $sum_list["sum_order_num"] = $sum_return_num;
        }
        array_push($json_list["sum_num"], $sum_list);
    } else {
        // 発注情報トランに情報が存在しない場合、こちらで商品一覧生成
        $all_list = array();
        $list = array();
        $query_list = array();
        array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
        array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
        array_push($query_list, "t_delivery_goods_state_details.rtn_ok_flg = '1'");
        array_push($query_list, "t_delivery_goods_state_details.receipt_status = '2'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= " *, ";
        $arg_str .= " count(*) as_rtn_ok_cnt";
        $arg_str .= " FROM ";
        $arg_str .= "(SELECT distinct on (t_delivery_goods_state_details.individual_ctrl_no, m_item.item_cd, m_item.color_cd, m_item.size_cd)";
        $arg_str .= "t_delivery_goods_state_details.quantity as as_quantity,";
        $arg_str .= "t_delivery_goods_state_details.return_plan__qty as as_return_plan_qty,";
        $arg_str .= "t_delivery_goods_state_details.returned_qty as as_returned_qty,";
        $arg_str .= "m_item.item_cd as as_item_cd,";
        $arg_str .= "m_item.color_cd as as_color_cd,";
        $arg_str .= "m_item.size_cd as as_size_cd,";
        $arg_str .= "m_item.item_name as as_item_name,";
        $arg_str .= "m_input_item.job_type_item_cd as as_job_type_item_cd,";
        $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
        $arg_str .= "m_input_item.std_input_qty as as_std_input_qty";
        $arg_str .= " FROM ";
        $arg_str .= "t_delivery_goods_state_details INNER JOIN m_item";
        $arg_str .= " ON (t_delivery_goods_state_details.corporate_id = m_item.corporate_id";
        $arg_str .= " AND t_delivery_goods_state_details.item_cd = m_item.item_cd";
        $arg_str .= " AND t_delivery_goods_state_details.color_cd = m_item.color_cd";
        $arg_str .= " AND t_delivery_goods_state_details.size_cd = m_item.size_cd)";
        $arg_str .= " INNER JOIN m_input_item";
        $arg_str .= " ON (m_item.corporate_id = m_item.corporate_id";
        $arg_str .= " AND m_item.item_cd = m_input_item.item_cd";
        $arg_str .= " AND m_item.color_cd = m_input_item.color_cd)";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= ") as distinct_table";
        $arg_str .= " GROUP BY as_quantity,
         as_return_plan_qty,
         as_returned_qty,
         as_item_cd,
         as_color_cd,
         as_size_cd,
         as_job_type_item_cd,
         as_std_input_qty,
         as_item_name,
         as_input_item_name,
         as_std_input_qty";
        $arg_str .= " ORDER BY as_item_cd ASC, as_color_cd ASC";
        $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
        $results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
        if (!empty($results_cnt)) {
            // 発注情報トランに情報が存在する場合、こちらで商品一覧生成
            $arr_num = 0;
            $list_cnt = 1;
            foreach ($results as $result) {
                // name属性用カウント値
                $list["arr_num"] = $arr_num++;
                // No
                $list["list_no"] = $list_cnt++;
                // アイテム
                $list["item_name"] = $result->as_item_name;
                // 標準枚数
                $list["possible_num"] = $result->as_std_input_qty;
//                $list["possible_num"] = $result->as_quantity - $result->as_return_plan_qty - $result->as_returned_qty;
                // 数量
                if(isset($result->as_quantity)){
                    $list["quantity"] = $result->as_quantity;
                }else{
                    $list["quantity"] = '0';
                }
                // 返却予定数
                $list["return_plan_qty"] = $result->as_return_plan_qty;
                // 返却済数
                $list["returned_qty"] = $result->as_return_plan_qty;
                // 商品コード
                $list["item_cd"] = $result->as_item_cd;
                // 色コード
                $list["color_cd"] = $result->as_color_cd;
                // 商品-色
                $list["item_and_color"] = $list["item_cd"]."-".$list["color_cd"];
                // 商品名
                $list["input_item_name"] = $result->as_input_item_name;
                // サイズ
                $list["size_cd"] = $result->as_size_cd;
                // 対象、個体管理番号
                if ($individual_flg == "1") {
                    $list["order_num_disable"] = "disabled";
                    $list["return_num_disable"] = "disabled";
                    // 納品状況明細情報参照
                    $list["individual_chk"] = array();
                    $element = array();
                    $list["individual_ctrl_no"] = array();
                    $query_list = array();
                    array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
                    array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
                    array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_size_change_post['werer_cd']."'");
                    array_push($query_list, "t_delivery_goods_state_details.item_cd = '".$list["item_cd"]."'");
                    array_push($query_list, "t_delivery_goods_state_details.color_cd = '".$list["color_cd"]."'");
                    array_push($query_list, "t_delivery_goods_state_details.size_cd = '".$list["size_cd"]."'");
                    array_push($query_list, "t_delivery_goods_state_details.rtn_ok_flg = '1'");
                    $query = implode(' AND ', $query_list);

                    $arg_str = "";
                    $arg_str = "SELECT ";
                    $arg_str .= " * ";
                    $arg_str .= " FROM ";
                    $arg_str .= "t_delivery_goods_state_details";
                    $arg_str .= " WHERE ";
                    $arg_str .= $query;
                    $arg_str .= " ORDER BY individual_ctrl_no ASC";
                    $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
                    $t_delivery_goods_state_details_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
                    $result_obj = (array)$t_delivery_goods_state_details_results;
                    $results_cnt = $result_obj["\0*\0_count"];
                    $i = 0;
                    $return_num = 0;
                    foreach ($t_delivery_goods_state_details_results as $t_delivery_goods_state_details_result) {
                        array_push($list["individual_ctrl_no"], $t_delivery_goods_state_details_result->individual_ctrl_no);
                        $element["name_no"] = $list["arr_num"];
                        $element["individual_ctrl_no"] = $t_delivery_goods_state_details_result->individual_ctrl_no;
                        if ($results_cnt - 1 !== $i) {
                            $element["br"] = "<br/>";
                        } else {
                            $element["br"] = "";
                        }
                        array_push($list["individual_chk"], $element);
                    }
                    // 個体管理番号数
                    $list["individual_cnt"] = count($list["individual_ctrl_no"]);
                    // 個体管理番号(表示用)
                    $list["individual_ctrl_no"] = implode("<br/>", $list["individual_ctrl_no"]);
                }else{
                    // 交換可能枚数
                    $list["rtn_ok_cnt"] = $list["quantity"];

                }
                // 返却枚数
                if(!isset($list["return_num"])){
                    $list["return_num"] = '0';
                }
                // 発注数
                if(!isset($list["order_num"])){
                    $list["order_num"] = '0';
                }
                // 個体管理番号表示フラグ
                if ($individual_flg == "1") {
                    $list["individual_flg"] = true;
                } else {
                    $list["individual_flg"] = false;
                }
                //--その他の必要hiddenパラメータ--//
                // 部門コード
                $list["rntl_sect_cd"] = $wearer_size_change_post["rntl_sect_cd"];
                // 職種コード
                $list["job_type_cd"] = $wearer_size_change_post['job_type_cd'];
                // 職種アイテムコード
                $list["job_type_item_cd"] = $result->as_job_type_item_cd;

                array_push($all_list, $list);
            }
            // 返却総枚数(返却可能枚数)
            $json_list["sum_num"] = array();
            $sum_list = array();
            $sum_list["sum_return_num"] = '';
            $sum_list["sum_order_num"] = '';
            if (!empty($all_list)) {
                $sum_return_num = 0;
                $sum_order_num = 0;
                foreach ($all_list as $all_map) {
                    $sum_return_num += $all_map["return_num"];
                }
                $sum_list["sum_return_num"] = $sum_return_num;
                $sum_list["sum_order_num"] = $sum_return_num;
            }
            array_push($json_list["sum_num"], $sum_list);
        }
    }

    // 商品リスト件数による一覧表示制御
    $json_list["list_disp_flg"] = true;
    if (count($all_list) == 0) {
        $json_list["list_disp_flg"] = false;
    }

    // 商品リスト件数、リスト内容
    $json_list["list_cnt"] = count($all_list);
    $json_list["item_list"] = $all_list;

    // 個体管理番号表示フラグ
    if ($individual_flg == "1") {
        $json_list["individual_flg"] = true;
    } else {
        $json_list["individual_flg"] = false;
    }

    echo json_encode($json_list);
});


/*
 *  入力完了 or 発注送信
 */
$app->post('/wearer_other_change_insert', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');
    // 前画面セッション取得
    $wearer_size_change_post = $app->session->get("wearer_size_change_post");

    $item_list = $params["item"];
    $wearer_data_input = $params["wearer_data"];
    $query_list = array();
    $list = array();
    $json_list = array();
    $error_list = array();
    // DB更新エラーコード 0:正常 1:更新エラー
    $json_list["error_code"] = "0";
    $json_list['error_msg'] = array();
    //--入力内容確認--//
    // 共通
    if (empty($item_list)) {
        $json_list["error_code"] = "1";
        $error_msg = "対象商品がない為、その他交換登録を行うことができません。";
        array_push($json_list["error_msg"], $error_msg);
        echo json_encode($json_list);
        return;
    }
    if (byte_cnt($wearer_data_input['cster_emply_cd']) > 10) {
        array_push($error_list, '社員コードの文字数が多すぎます。');
    }

    if (byte_cnt($wearer_data_input['member_name']) > 22) {
        array_push($error_list, '着用者名の文字数が多すぎます。');
    }

    if (byte_cnt($wearer_data_input['member_name_kana']) > 22) {
        array_push($error_list, '着用者名(カナ)の文字数が多すぎます。');
    }
    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_size_change_post['werer_cd']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY upd_date DESC";
    $t_order_tran = new TOrderTran();
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if (!empty($results_cnt)) {
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
            $order_sts_kbn = $result->order_sts_kbn;
            $order_reason_kbn = $result->order_reason_kbn;

            // 発注情報トラン.発注状況区分 = 「終了」または「異動」情報がある際は発注NG
            if ($order_sts_kbn == "2" && ($order_reason_kbn == "05" || $order_reason_kbn == "06" || $order_reason_kbn == "08" || $order_reason_kbn == "20")) {
                $json_list["error_code"] = "2";
                $error_msg = "貸与終了の発注が登録されていた為、操作を完了できませんでした。貸与終了の発注を削除してから再度登録して下さい。";
                $json_list["error_msg"] = $error_msg;
                echo json_encode($json_list);
                return;
            }
            if ($order_sts_kbn == "5" && ($order_reason_kbn == "09" || $order_reason_kbn == "10" || $order_reason_kbn == "11" || $order_reason_kbn == "24")) {
                $json_list["error_code"] = "2";
                $error_msg = "職種変更または異動の発注が登録されていた為、操作を完了できませんでした。職種変更または異動の発注を削除してから再度登録して下さい。";
                $json_list["error_msg"] = $error_msg;
                echo json_encode($json_list);
                return;
            }
        }
    }
    //更新可否チェック（更新可否チェック仕様書）

    $query_list = array();
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
        $json_list['error_msg'] = $error_list;
        $json_list["error_code"] = "1";
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
        $json_list['error_msg'] = $error_list;
        $json_list["error_code"] = "1";
        return;
    }
    if (!empty($wearer_data_input["comment"])) {
        if (mb_strlen($wearer_data_input["comment"]) > 50) {
            array_push($error_list,'コメント欄は50文字以内で入力してください。');
            $json_list['error_msg'] = $error_list;
            $json_list["error_code"] = "1";
        }
    }
    if (!$wearer_data_input['return_date']) {
        array_push($error_list,'返却予定日を入力してください。');
        $json_list['error_msg'] = $error_list;
        $json_list["error_code"] = "1";

    }
    if (!$wearer_data_input['member_name']) {
        array_push($error_list,'着用者名を入力してください。');
        $json_list['error_msg'] = $error_list;
        $json_list["error_code"] = "1";

    }
    // 社員コード
//    if ($wearer_data_input['cster_emply_cd_chk']) {
//        if (mb_strlen($wearer_data_input['cster_emply_cd']) == 0) {
//            $json_list["error_code"] = "1";
//            $error_msg = "社員コードありにチェックしている場合、社員コードを入力してください。";
//            array_push($json_list["error_msg"], $error_msg);
//        }
//    }
//    if (!$wearer_data_input['cster_emply_cd_chk']) {
//        if (mb_strlen($wearer_data_input['cster_emply_cd']) > 0) {
//            $json_list["error_code"] = "1";
//            $error_msg = "社員コードありにチェックしていない場合、社員コードの入力は不要です。";
//            array_push($json_list["error_msg"], $error_msg);
//        }
//    }
    $query_list = array();
    if ($wearer_data_input['cster_emply_cd']) {
        //社員コードのマスタチェック(社員コードありの場合のみ)
        if(isset($wearer_size_change_post['cster_emply_cd'])&&$wearer_size_change_post['cster_emply_cd']==$wearer_data_input['cster_emply_cd']){
            //入力値と一緒なら
        }else{
            // 契約マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
            array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
            // 契約マスタ．レンタル契約No.　＝　画面で選択されている契約No.
            array_push($query_list, "rntl_cont_no = '" . $wearer_size_change_post['rntl_cont_no'] . "'");
            // 着用者基本マスタ．客先社員コード ＝ 画面で入力された社員コード AND
            array_push($query_list, "cster_emply_cd = '" . $wearer_data_input['cster_emply_cd'] . "'");

            //sql文字列を' AND 'で結合
            $query = implode(' AND ', $query_list);
            //--- クエリー実行・取得 ---//
            $m_wearer_std_count = MWearerStdTran::find(array(
                'conditions' => $query
            ))->count();
//            $cster_emply_cd_chk = true;
//            //存在する場合NG
//            if ($m_wearer_std_count > 0) {
//                array_push($error_list, '既に社員コードが使用されています。');
//                $cster_emply_cd_chk = false;
//            }
//            if($cster_emply_cd_chk){
//                // 着用者基本マスタ．着用者状況区分 ＝ 稼働
//                array_push($query_list, "werer_sts_kbn = '1'");
//                //--- クエリー実行・取得 ---//
//                $m_wearer_std_count = MWearerStd::find(array(
//                    'conditions' => $query
//                ))->count();
//                //存在する場合NG
//                if ($m_wearer_std_count > 0) {
//                    array_push($error_list, '既に社員コードが使用されています。');
//                }
//            }

        }
    }

    //返品商品がなかったらエラーにするためのカウンタ
    $rtn_cnt = 0;
    // 発注商品一覧
    foreach ($item_list as $item_map) {
        // 返却枚数フォーマットチェック
        if (!empty($item_map["return_num"])) {
            if (!ctype_digit(strval($item_map["return_num"]))) {
                if (empty($return_num_format_err)) {
                    $return_num_format_err = "err";
                    $json_list["error_code"] = "1";
                    $error_msg = "発注商品一覧の返却枚数には半角数字を入力してください。";
                    array_push($json_list["error_msg"], $error_msg);
                }
            }
        }
        if (!$item_map["individual_flg"]) {
            // ※個体管理番号表示フラグがOFFの場合
            // 商品毎返却可能チェック
            if (!empty($item_map["return_num"])) {
                if ($item_map["return_num"] > $item_map["rtn_ok_cnt"]) {
                    if (empty($return_num_possible_err)) {
                        $return_num_possible_err = "err";
                        $json_list["error_code"] = "1";
                        $error_msg = "発注商品一覧にて返却枚数が現在の所持枚数を超過している商品があります。";
                        array_push($json_list["error_msg"], $error_msg);
                    }
                }
            }
        } else {
            // ※個体管理番号表示フラグがONの場合
            // 商品毎返却可能チェック
            if (!empty($item_map["individual_data"])) {
                $target_cnt = 0;
                foreach ($item_map["individual_data"] as $individual_data) {
                    if ($individual_data["target_flg"] == "1") {
                        $target_cnt++;
                    }
                }
                if ($item_map["rtn_ok_cnt"] < $target_cnt) {
                    if (empty($return_num_possible_err)) {
                        $return_num_possible_err = "err";
                        $json_list["error_code"] = "1";
                        $error_msg = "発注商品一覧にて返却枚数が現在の所持枚数を超過している商品があります。";
                        array_push($json_list["error_msg"], $error_msg);
                    }
                }
            }
        }
        //発注NGパターン：同一商品を交換で発注かけようとした場合にエラーで戻す
        //発注情報トランに同じ着用者＋商品情報で検索
        if($item_map["return_num"]>0){
            $query_list=array();
            array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
            array_push($query_list, "rntl_cont_no = '" . $wearer_size_change_post['rntl_cont_no'] . "'");
            array_push($query_list, "werer_cd = '" . $wearer_size_change_post['werer_cd'] . "'");
            //商品情報
            array_push($query_list, "item_cd = '" . $item_map['item_cd'] . "'");
            array_push($query_list, "color_cd = '" . $item_map['color_cd'] . "'");
            //発注状況区分
            array_push($query_list, "order_sts_kbn = '3'");//サイズ交換のトラン

            //sql文字列を' AND 'で結合
            $query = implode(' AND ', $query_list);
            //--- クエリー実行・取得 ---//
            $t_order_tran_count = TOrderTran::find(array(
                'conditions' => $query
            ))->count();
            if($t_order_tran_count > 0){
                $json_list["error_code"] = "1";
                $error_msg = $item_map['item_cd']."-".$item_map['color_cd']."は既にサイズ交換の発注がされています。";
                array_push($json_list["error_msg"], $error_msg);
            }
        }
        $rtn_cnt += $item_map["return_num"];
    }
    //交換する商品がない場合
    if($rtn_cnt <= 0){
        $return_num_possible_err = "err";
        $json_list["error_code"] = "1";
        $error_msg = "交換する商品を選択してください。";
        array_push($json_list["error_msg"], $error_msg);

    }
    //エラーチェック
    if($json_list["error_code"]=="1"){
        echo json_encode($json_list);
        return true;
    }
    if ($params['mode'] == 'check') {
        $json_list['ok'] = 'ok';
        echo json_encode($json_list);
        return;
    }
    // 着用者基本マスタトラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "werer_cd = '".$wearer_size_change_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_size_change_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_size_change_post['job_type_cd']."'");
    array_push($query_list, "order_sts_kbn = '4'");//その他交換のトラン
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "order_sts_kbn,";
    $arg_str .= "order_req_no";
    $arg_str .= " FROM ";
    $arg_str .= "m_wearer_std_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $tran_results_cnt = $result_obj["\0*\0_count"];
    $order_sts_kbn = "";
    if (!empty($tran_results_cnt)) {
        $paginator_model = new PaginatorModel(
            array(
                "data"  => $results,
                "limit" => $tran_results_cnt,
                "page" => 1
            )
        );
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;
        foreach ($results as $result) {
            $order_sts_kbn = $result->order_sts_kbn;
            $order_req_no = $result->order_req_no;
        }
    }

    // トランザクション開始
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
    try {
        // 発注依頼No.生成
        //※シーケンス取得
        $arg_str = "";
        $arg_str = "SELECT NEXTVAL('t_order_seq')";
        $t_order_tran = new TOrderTran();
        $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
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
                $order_no_seq = $result->nextval;
            }
            //※次シーケンスをセット
            $arg_str = "";
            $arg_str = "SELECT SETVAL('t_order_seq',".$order_no_seq.")";
            $t_order_tran = new TOrderTran();
            $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
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
                    $order_no_seq = $result->setval;
                }
            }
        }
        $shin_order_req_no = "WB".str_pad($order_no_seq, 8, '0', STR_PAD_LEFT);
        if ($tran_results_cnt > 0) {
            $shin_order_req_no = $order_req_no;
            //--着用者基本マスタトランに情報がある場合、更新処理--//
            $src_query_list = array();
            array_push($src_query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($src_query_list, "werer_cd = '".$wearer_size_change_post['werer_cd']."'");
            array_push($src_query_list, "rntl_sect_cd = '".$wearer_size_change_post['rntl_sect_cd']."'");
            array_push($src_query_list, "job_type_cd = '".$wearer_size_change_post['job_type_cd']."'");
            // 発注状況区分(その他交換)
            array_push($src_query_list,"order_sts_kbn = '4'");
            $src_query = implode(' AND ', $src_query_list);

            $up_query_list = array();
            // 貸与パターン
            $job_type_cd = $wearer_data_input['job_type'];
            // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)

            // 返却伝票用パラメータ
            $json_list['param'] = '';
            $json_list['param'] .= $wearer_data_input['agreement_no'].':';
            $json_list['param'] .= $shin_order_req_no;
            // 客先社員コード
            if (isset($wearer_data_input['cster_emply_cd'])) {
                array_push($up_query_list, "cster_emply_cd = '".$wearer_data_input['cster_emply_cd']."'");
            } else {
                array_push($up_query_list, "cster_emply_cd = NULL");
            }
            // 着用者名
            array_push($up_query_list, "werer_name = '".$wearer_data_input['member_name']."'");
            // 着用者名かな
            if (isset($wearer_data_input['member_name_kana'])) {
                array_push($up_query_list, "werer_name_kana = '".$wearer_data_input['member_name_kana']."'");
            } else {
                array_push($up_query_list, "werer_name_kana = NULL");
            }
            // 性別区分
            array_push($up_query_list, "sex_kbn = '".$wearer_data_input['sex_kbn']."'");
            // 着用者状況区分(稼働）)
            array_push($up_query_list, "werer_sts_kbn = '1'");
            // 出荷先、出荷先支店コード
            if (!empty($wearer_size_change_post['ship_to_cd'])) {
                $ship_to_cd = $wearer_size_change_post['ship_to_cd'];
                $ship_to_brnch_cd = $wearer_size_change_post['ship_to_brnch_cd'];

                // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
                if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
                    $query_list = array();
                    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
                    array_push($query_list, "rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
                    array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
                    $query = implode(' AND ', $query_list);

                    $arg_str = '';
                    $arg_str = 'SELECT ';
                    $arg_str .= 'std_ship_to_cd,';
                    $arg_str .= 'std_ship_to_brnch_cd';
                    $arg_str .= ' FROM ';
                    $arg_str .= 'm_section';
                    $arg_str .= ' WHERE ';
                    $arg_str .= $query;
                    $m_section = new MSection();
                    $results = new Resultset(NULL, $m_section, $m_section->getReadConnection()->query($arg_str));
                    $results_array = (array) $results;
                    $results_cnt = $results_array["\0*\0_count"];
                    //ChromePhp::LOG($results_cnt);
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
                        $ship_to_cd = $result->std_ship_to_cd;
                        $ship_to_brnch_cd = $result->std_ship_to_brnch_cd;
                    }
                }
                array_push($up_query_list, "ship_to_cd = '".$ship_to_cd."'");
                array_push($up_query_list, "ship_to_brnch_cd = '".$ship_to_brnch_cd."'");
            }
            // 発注状況区分(その他交換)
            if ($order_sts_kbn !== "6") {
                array_push($up_query_list, "order_sts_kbn = '4'");
            }
            // 更新区分(WEB発注システム(その他交換）)
            array_push($up_query_list, "upd_kbn = '4'");
            // Web更新日時
            array_push($up_query_list, "web_upd_date = '".date("Y-m-d H:i:s", time())."'");
            // 送信区分
            if($params['snd_kbn']=='0'){
                //未送信
                array_push($up_query_list, "snd_kbn = '0'");
            }else{
                //送信
                array_push($up_query_list, "snd_kbn = '1'");
            }
            // 削除区分
            array_push($up_query_list, "del_kbn = '0'");
            // 更新日時
            array_push($up_query_list, "upd_date = '".date("Y-m-d H:i:s", time())."'");
            // 更新ユーザーID
            array_push($up_query_list, "upd_user_id = '".$auth['accnt_no']."'");
            // 更新PGID
            array_push($up_query_list, "upd_pg_id = '".$auth['accnt_no']."'");
            // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
            $m_job_type_comb_hkey = md5(
                $auth['corporate_id'].'-'
                .$wearer_data_input['agreement_no'].'-'
                .$job_type_cd
            );
            array_push($up_query_list, "m_job_type_comb_hkey = '".$m_job_type_comb_hkey."'");
            // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
            $m_section_comb_hkey = md5(
                $auth['corporate_id'].'-'
                .$wearer_data_input['agreement_no'].'-'
                .$wearer_data_input['section']
            );
            array_push($up_query_list, "m_section_comb_hkey = '".$m_section_comb_hkey."'");
            $up_query = implode(',', $up_query_list);

            $arg_str = "";
            $arg_str = "UPDATE m_wearer_std_tran SET ";
            $arg_str .= $up_query;
            $arg_str .= " WHERE ";
            $arg_str .= $src_query;
            //ChromePhp::LOG($arg_str);
            $m_wearer_std_tran = new MWearerStdTran();
            $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
            $result_obj = (array)$results;
            $results_cnt = $result_obj["\0*\0_count"];
            //--着用者基本マスタトラン更新処理 ここまで--//
        } else {
            //--着用者基本マスタトランに情報がない場合、登録処理--//
            $calum_list = array();
            $values_list = array();

            // 貸与パターン
//            $job_type_cd = explode(':', $wearer_data_input['job_type']);
            $job_type_cd = $wearer_data_input['job_type'];
            array_push($calum_list, "job_type_cd");
            array_push($values_list, "'".$job_type_cd."'");
            // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
            $m_wearer_std_comb_hkey = md5(
                $auth['corporate_id'].'-'
                .$wearer_size_change_post["werer_cd"].'-'
                .$wearer_data_input['agreement_no'].'-'
                .$wearer_data_input['section'].'-'
                .$job_type_cd
            );
            array_push($calum_list, "m_wearer_std_comb_hkey");
            array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
            // 発注No
            array_push($calum_list, "order_req_no");
            array_push($values_list, "'".$shin_order_req_no."'");
            // 企業ID
            array_push($calum_list, "corporate_id");
            array_push($values_list, "'".$auth['corporate_id']."'");
            // 着用者コード
            array_push($calum_list, "werer_cd");
            array_push($values_list, "'".$wearer_size_change_post['werer_cd']."'");
            // レンタル契約No
            array_push($calum_list, "rntl_cont_no");
            array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
            // レンタル部門コード
            array_push($calum_list, "rntl_sect_cd");
            array_push($values_list, "'".$wearer_data_input['section']."'");
            // 客先社員コード
            if (!empty($wearer_data_input['cster_emply_cd'])) {
                array_push($calum_list, "cster_emply_cd");
                array_push($values_list, "'".$wearer_data_input['cster_emply_cd']."'");
            }
            // 着用者名
            if (!empty($wearer_data_input['member_name'])) {
                array_push($calum_list, "werer_name");
                array_push($values_list, "'".$wearer_data_input['member_name']."'");
            }
            // 着用者名（かな）
            if (!empty($wearer_data_input['member_name_kana'])) {
                array_push($calum_list, "werer_name_kana");
                array_push($values_list, "'".$wearer_data_input['member_name_kana']."'");
            }
            // 性別区分
            array_push($calum_list, "sex_kbn");
            array_push($values_list, "'".$wearer_data_input['sex_kbn']."'");
            // 着用者状況区分(稼働)
            array_push($calum_list, "werer_sts_kbn");
            array_push($values_list, "'1'");
            // 出荷先、出荷先支店コード
            if (!empty($wearer_size_change_post['ship_to_cd'])) {
                $ship_to_cd = $wearer_size_change_post['ship_to_cd'];
                $ship_to_brnch_cd = $wearer_size_change_post['ship_to_brnch_cd'];

                // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
                if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
                    $query_list = array();
                    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
                    array_push($query_list, "rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
                    array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
                    $query = implode(' AND ', $query_list);

                    $arg_str = '';
                    $arg_str = 'SELECT ';
                    $arg_str .= 'std_ship_to_cd,';
                    $arg_str .= 'std_ship_to_brnch_cd';
                    $arg_str .= ' FROM ';
                    $arg_str .= 'm_section';
                    $arg_str .= ' WHERE ';
                    $arg_str .= $query;
                    $m_section = new MSection();
                    $results = new Resultset(NULL, $m_section, $m_section->getReadConnection()->query($arg_str));
                    $results_array = (array) $results;
                    $results_cnt = $results_array["\0*\0_count"];
                    //ChromePhp::LOG($results_cnt);
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
                        $ship_to_cd = $result->std_ship_to_cd;
                        $ship_to_brnch_cd = $result->std_ship_to_brnch_cd;
                    }
                }
                array_push($calum_list, "ship_to_cd");
                array_push($values_list, "'".$ship_to_cd."'");
                array_push($calum_list, "ship_to_brnch_cd");
                array_push($values_list, "'".$ship_to_brnch_cd."'");
            }
            // 発注状況区分(その他交換)
            array_push($calum_list, "order_sts_kbn");
            array_push($values_list, "'4'");
            // 更新区分(WEB発注システム(その他交換))
            array_push($calum_list, "upd_kbn");
            array_push($values_list, "'4'");
            // Web更新日時
            array_push($calum_list, "web_upd_date");
            array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
            // 送信区分
            // 送信区分
            if($params['snd_kbn']=='0'){
                //未送信
                array_push($calum_list, "snd_kbn");
                array_push($values_list, "'0'");
            }else{
                //送信
                array_push($calum_list, "snd_kbn");
                array_push($values_list, "'1'");
            }
            // 削除区分
            array_push($calum_list, "del_kbn");
            array_push($values_list, "'0'");
            // 登録日時
            array_push($calum_list, "rgst_date");
            array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
            // 登録ユーザーID
            array_push($calum_list, "rgst_user_id");
            array_push($values_list, "'".$auth['accnt_no']."'");
            // 更新日時
            array_push($calum_list, "upd_date");
            array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
            // 更新ユーザーID
            array_push($calum_list, "upd_user_id");
            array_push($values_list, "'".$auth['accnt_no']."'");
            // 更新PGID
            array_push($calum_list, "upd_pg_id");
            array_push($values_list, "'".$auth['accnt_no']."'");
            // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
            $m_job_type_comb_hkey = md5(
                $auth['corporate_id'].'-'
                .$wearer_data_input['agreement_no'].'-'
                .$job_type_cd
            );
            array_push($calum_list, "m_job_type_comb_hkey");
            array_push($values_list, "'".$m_job_type_comb_hkey."'");
            // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
            $m_section_comb_hkey = md5(
                $auth['corporate_id'].'-'
                .$wearer_data_input['agreement_no'].'-'
                .$wearer_data_input['section']
            );
            array_push($calum_list, "m_section_comb_hkey");
            array_push($values_list, "'".$m_section_comb_hkey."'");
            $calum_query = implode(',', $calum_list);
            $values_query = implode(',', $values_list);

            $arg_str = "";
            $arg_str = "INSERT INTO m_wearer_std_tran";
            $arg_str .= "(".$calum_query.")";
            $arg_str .= " VALUES ";
            $arg_str .= "(".$values_query.")";
            //ChromePhp::LOG($arg_str);
            $m_wearer_std_tran = new MWearerStdTran();
            $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
            $results_cnt = $result_obj["\0*\0_count"];
            //ChromePhp::LOG($results_cnt);
            //--着用者基本マスタトラン登録処理 ここまで--//
        }

        //--発注情報トラン登録--//
        $cnt = 1;
        // 発注商品一覧内容登録
        if (!empty($item_list)) {
            // 現在のその他交換発注の情報をクリーン
            if ($wearer_size_change_post['order_tran_flg'] == '1') {
                //ChromePhp::LOG("発注情報トランクリーン");
                $query_list = array();
                array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
                array_push($query_list, "rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
                array_push($query_list, "werer_cd = '".$wearer_size_change_post['werer_cd']."'");
                // 発注状況区分「その他交換」
                array_push($query_list, "order_sts_kbn = '4'");
                // 着用者状況区分「稼働」
                array_push($query_list, "werer_sts_kbn = '1'");
                $query = implode(' AND ', $query_list);

                $arg_str = "";
                $arg_str = "DELETE FROM ";
                $arg_str .= "t_order_tran";
                $arg_str .= " WHERE ";
                $arg_str .= $query;
                //ChromePhp::LOG($arg_str);
                $t_order_tran = new TOrderTran();
                $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
                $results_cnt = $result_obj["\0*\0_count"];
                //ChromePhp::LOG($results_cnt);
            }
            //ChromePhp::LOG("発注情報トラン登録");
            foreach ($item_list as $item_map) {
                if (
                    ($item_map["individual_flg"] == true && !empty($item_map["individual_data"])) ||
                    ($item_map["individual_flg"] == false && !empty($item_map["return_num"]))
                )
                {
                    // 個体管理番号単位の場合、商品毎の対象チェックが１つ以上あるもののみ登録する。それ以外は登録対象外
                    if (!empty($item_map["individual_data"])) {
                        $target_cnt = 0;
                        foreach ($item_map["individual_data"] as $individual_data) {
                            if ($individual_data["target_flg"] == "1") {
                                $target_cnt = $target_cnt + 1;
                            }
                        }
                        if ($target_cnt == 0) {
                            continue;
                        }
                    }

                    $calum_list = array();
                    $values_list = array();
                    // 発注依頼行No.生成
                    $order_req_line_no = $cnt++;
                    // 発注情報_統合ハッシュキー(企業ID、発注依頼No、発注依頼行No)
                    $t_order_comb_hkey = md5(
                        $auth['corporate_id'].'-'
                        .$shin_order_req_no.'-'
                        .$order_req_line_no
                    );
                    array_push($calum_list, "t_order_comb_hkey");
                    array_push($values_list, "'".$t_order_comb_hkey."'");
                    // 企業ID
                    array_push($calum_list, "corporate_id");
                    array_push($values_list, "'".$auth['corporate_id']."'");
                    // 発注依頼No.
                    array_push($calum_list, "order_req_no");
                    array_push($values_list, "'".$shin_order_req_no."'");
                    // 発注依頼行No.
                    array_push($calum_list, "order_req_line_no");
                    array_push($values_list, "'".$order_req_line_no."'");
                    // 発注依頼日
                    array_push($calum_list, "order_req_ymd");
                    array_push($values_list, "'".date('Ymd', time())."'");
                    // 発注状況区分(その他交換)
                    array_push($calum_list, "order_sts_kbn");
                    array_push($values_list, "'4'");
                    // レンタル契約No
                    array_push($calum_list, "rntl_cont_no");
                    array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
                    // レンタル部門コード
                    array_push($calum_list, "rntl_sect_cd");
                    array_push($values_list, "'".$wearer_data_input['section']."'");
                    // 貸与パターン
//                    $job_type_cd = explode(':', $wearer_data_input['job_type']);
                    $job_type_cd = $wearer_data_input['job_type'];
                    array_push($calum_list, "job_type_cd");
                    array_push($values_list, "'".$job_type_cd."'");
                    // 職種アイテムコード
                    array_push($calum_list, "job_type_item_cd");
                    array_push($values_list, "'".$item_map['job_type_item_cd']."'");
                    // 着用者コード
                    array_push($calum_list, "werer_cd");
                    array_push($values_list, "'".$wearer_size_change_post['werer_cd']."'");
                    // 商品コード
                    array_push($calum_list, "item_cd");
                    array_push($values_list, "'".$item_map['item_cd']."'");
                    // 色コード
                    array_push($calum_list, "color_cd");
                    array_push($values_list, "'".$item_map['color_cd']."'");
                    // サイズコード
                    array_push($calum_list, "size_cd");
                    array_push($values_list, "'".$item_map['size_cd']."'");
                    // サイズコード2
                    array_push($calum_list, "size_two_cd");
                    array_push($values_list, "' '");
                    // 出荷先、出荷先支店コード
                    if (!empty($wearer_size_change_post['ship_to_cd'])) {
                        $ship_to_cd = $wearer_size_change_post['ship_to_cd'];
                        $ship_to_brnch_cd = $wearer_size_change_post['ship_to_brnch_cd'];

                        // 出荷先が「支店店舗と同じ」の場合、部門マスタから標準出荷先、支店コードを設定
                        if ($ship_to_cd == "0" && $ship_to_brnch_cd == "0") {
                            $query_list = array();
                            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
                            array_push($query_list, "rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
                            array_push($query_list, "rntl_sect_cd = '".$wearer_data_input['section']."'");
                            $query = implode(' AND ', $query_list);

                            $arg_str = '';
                            $arg_str = 'SELECT ';
                            $arg_str .= 'std_ship_to_cd,';
                            $arg_str .= 'std_ship_to_brnch_cd';
                            $arg_str .= ' FROM ';
                            $arg_str .= 'm_section';
                            $arg_str .= ' WHERE ';
                            $arg_str .= $query;
                            $m_section = new MSection();
                            $results = new Resultset(NULL, $m_section, $m_section->getReadConnection()->query($arg_str));
                            $results_array = (array) $results;
                            $results_cnt = $results_array["\0*\0_count"];
                            //ChromePhp::LOG($results_cnt);
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
                                $ship_to_cd = $result->std_ship_to_cd;
                                $ship_to_brnch_cd = $result->std_ship_to_brnch_cd;
                            }
                        }
                        array_push($calum_list, "ship_to_cd");
                        array_push($values_list, "'".$ship_to_cd."'");
                        array_push($calum_list, "ship_to_brnch_cd");
                        array_push($values_list, "'".$ship_to_brnch_cd."'");
                    }
                    // 発注枚数
                    array_push($calum_list, "order_qty");
                    array_push($values_list, "'".$wearer_data_input['order_count']."'");
                    // 備考欄
                    if (!empty($wearer_data_input['comment'])) {
                        array_push($calum_list, "memo");
                        array_push($values_list, "'".$wearer_data_input['comment']."'");
                    }
                    // 着用者名
                    if (!empty($wearer_data_input['member_name'])) {
                        array_push($calum_list, "werer_name");
                        array_push($values_list, "'".$wearer_data_input['member_name']."'");
                    }
                    // 客先社員コード
                    if (!empty($wearer_data_input['cster_emply_cd'])) {
                        array_push($calum_list, "cster_emply_cd");
                        array_push($values_list, "'".$wearer_data_input['cster_emply_cd']."'");
                    }
                    // 着用者状況区分(稼働）)
                    array_push($calum_list, "werer_sts_kbn");
                    array_push($values_list, "'1'");
                    // 送信区分
                    if($params['snd_kbn']=='0'){
                        //未送信
                        array_push($calum_list, "snd_kbn");
                        array_push($values_list, "'0'");
                    }else{
                        //送信
                        array_push($calum_list, "snd_kbn");
                        array_push($values_list, "'1'");
                    }
                    // 削除区分
                    array_push($calum_list, "del_kbn");
                    array_push($values_list, "'0'");
                    // 登録日時
                    array_push($calum_list, "rgst_date");
                    array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
                    // 登録ユーザーID
                    array_push($calum_list, "rgst_user_id");
                    array_push($values_list, "'".$auth['accnt_no']."'");
                    // 更新日時
                    array_push($calum_list, "upd_date");
                    array_push($values_list, "'".date("Y-m-d H:i:s", time())."'");
                    // 更新ユーザーID
                    array_push($calum_list, "upd_user_id");
                    array_push($values_list, "'".$auth['accnt_no']."'");
                    // 更新PGID
                    array_push($calum_list, "upd_pg_id");
                    array_push($values_list, "'".$auth['accnt_no']."'");
                    // 発注ステータス(未出荷)
                    array_push($calum_list, "order_status");
                    array_push($values_list, "'1'");
                    // 理由区分
                    array_push($calum_list, "order_reason_kbn");
                    array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
                    // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
                    $m_item_comb_hkey = md5(
                        $auth['corporate_id'].'-'
                        .$item_map['item_cd'].'-'
                        .$item_map['color_cd'].'-'
                        .$item_map['size_cd']
                    );
                    array_push($calum_list, "m_item_comb_hkey");
                    array_push($values_list, "'".$m_item_comb_hkey."'");
                    // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
                    $m_job_type_comb_hkey = md5(
                        $auth['corporate_id'].'-'
                        .$wearer_data_input['agreement_no'].'-'
                        .$job_type_cd
                    );
                    array_push($calum_list, "m_job_type_comb_hkey");
                    array_push($values_list, "'".$m_job_type_comb_hkey."'");
                    // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
                    $m_section_comb_hkey = md5(
                        $auth['corporate_id'].'-'
                        .$wearer_data_input['agreement_no'].'-'
                        .$wearer_data_input['section']
                    );
                    array_push($calum_list, "m_section_comb_hkey");
                    array_push($values_list, "'".$m_section_comb_hkey."'");
                    // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
                    $m_wearer_std_comb_hkey = md5(
                        $auth['corporate_id']
                        .$wearer_size_change_post["werer_cd"].'-'
                        .$wearer_data_input['agreement_no'].'-'
                        .$wearer_data_input['section'].'-'
                        .$job_type_cd
                    );
                    array_push($calum_list, "m_wearer_std_comb_hkey");
                    array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
                    // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
                    $m_wearer_item_comb_hkey = md5(
                        $auth['corporate_id'].'-'
                        .$wearer_size_change_post["werer_cd"].'-'
                        .$wearer_data_input['agreement_no'].'-'
                        .$wearer_data_input['section'].'-'
                        .$job_type_cd.'-'
                        .$item_map['job_type_item_cd'].'-'
                        .$item_map['item_cd'].'-'
                        .$item_map['color_cd'].'-'
                        .$item_map['size_cd']
                    );
                    array_push($calum_list, "m_wearer_item_comb_hkey");
                    array_push($values_list, "'".$m_wearer_item_comb_hkey."'");
                    $calum_query = implode(',', $calum_list);
                    $values_query = implode(',', $values_list);

                    $arg_str = "";
                    $arg_str = "INSERT INTO t_order_tran";
                    $arg_str .= "(".$calum_query.")";
                    $arg_str .= " VALUES ";
                    $arg_str .= "(".$values_query.")";
                    $t_order_tran = new TOrderTran();
                    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
                    $results_cnt = $result_obj["\0*\0_count"];
                }
            }
        }
        //--返却予定情報トラン登録--//
        $cnt = 1;
        // 発注商品一覧内容登録
        if (!empty($item_list)) {
            // 現在の不要品返却発注の情報をクリーン
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "rntl_cont_no = '".$wearer_size_change_post['rntl_cont_no']."'");
            array_push($query_list, "werer_cd = '".$wearer_size_change_post['werer_cd']."'");
            // 発注状況区分「その他交換」
            array_push($query_list, "order_sts_kbn = '4'");
            $query = implode(' AND ', $query_list);

            $arg_str = "";
            $arg_str = "DELETE FROM ";
            $arg_str .= "t_returned_plan_info_tran";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
            $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
            $results_cnt = $result_obj["\0*\0_count"];
            foreach ($item_list as $item_map) {
                if ($item_map["individual_flg"] == true && !empty($item_map["individual_data"])) {
                    // ※個体管理番号単位での登録の場合
                    foreach ($item_map["individual_data"] as $individual_data) {
                        // 対象にチェックされている商品のみが登録対象、それ以外は以降処理しない
                        if ($individual_data["target_flg"] == "0") {
                            continue;
                        }
                        $calum_list = array();
                        $values_list = array();

                        // 発注依頼行No.生成
                        $order_req_line_no = $cnt++;

                        // 企業ID
                        array_push($calum_list, "corporate_id");
                        array_push($values_list, "'".$auth['corporate_id']."'");
                        // 発注依頼No.
                        array_push($calum_list, "order_req_no");
                        array_push($values_list, "'".$shin_order_req_no."'");
                        // 発注依頼行No.
                        array_push($calum_list, "order_req_line_no");
                        array_push($values_list, "'".$order_req_line_no."'");
                        // レンタル契約No
                        array_push($calum_list, "rntl_cont_no");
                        array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
                        // 商品コード
                        array_push($calum_list, "item_cd");
                        array_push($values_list, "'".$item_map['item_cd']."'");
                        // 色コード
                        array_push($calum_list, "color_cd");
                        array_push($values_list, "'".$item_map['color_cd']."'");
                        // サイズコード
                        array_push($calum_list, "size_cd");
                        array_push($values_list, "'".$item_map['size_cd']."'");
                        // 個体管理番号
                        array_push($calum_list, "individual_ctrl_no");
                        array_push($values_list, "'".$individual_data['individual_ctrl_no']."'");
                        // 着用者コード
                        array_push($calum_list, "werer_cd");
                        array_push($values_list, "'".$wearer_size_change_post['werer_cd']."'");
                        // 客先社員コード
                        if (isset($wearer_data_input['cster_emply_cd'])) {
                            array_push($calum_list, "cster_emply_cd");
                            array_push($values_list, "'".$wearer_data_input['cster_emply_cd']."'");
                        }
                        // レンタル部門コード
                        array_push($calum_list, "rntl_sect_cd");
                        array_push($values_list, "'".$wearer_data_input['section']."'");
                        // 貸与パターン
//                        $job_type_cd = explode(':', $wearer_data_input['job_type']);
                        $job_type_cd = $wearer_data_input['job_type'];
                        array_push($calum_list, "job_type_cd");
                        array_push($values_list, "'".$job_type_cd."'");
                        // 発注依頼日
                        array_push($calum_list, "order_date");
                        array_push($values_list, "'".date('Y-m-d H:i:s', time())."'");
                        // 返却日
                        array_push($calum_list, "return_date");
                        array_push($values_list, "'".date("Ymd", strtotime($wearer_data_input['return_date']))."'");
                        // 返却ステータス(未返却)
                        array_push($calum_list, "return_status");
                        array_push($values_list, "'1'");
                        // 発注状況区分(その他交換)
                        array_push($calum_list, "order_sts_kbn");
                        array_push($values_list, "'4'");
                        // 返却予定数
                        array_push($calum_list, "return_plan_qty");
                        array_push($values_list, "'".$individual_data['return_num']."'");
                        // 返却数
                        array_push($calum_list, "return_qty");
                        array_push($values_list, "'0'");
                        // 送信区分
                        if($params['snd_kbn']=='0'){
                            //未送信
                            array_push($calum_list, "snd_kbn");
                            array_push($values_list, "'0'");
                        }else{
                            //送信
                            array_push($calum_list, "snd_kbn");
                            array_push($values_list, "'1'");
                        }
                        // 理由区分
                        array_push($calum_list, "order_reason_kbn");
                        array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
                        // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
                        $m_section_comb_hkey = md5(
                            $auth['corporate_id'].'-'
                            .$wearer_data_input['agreement_no'].'-'
                            .$wearer_data_input['section']
                        );
                        array_push($calum_list, "m_section_comb_hkey");
                        array_push($values_list, "'".$m_section_comb_hkey."'");
                        // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
                        $m_item_comb_hkey = md5(
                            $auth['corporate_id'].'-'
                            .$item_map['item_cd'].'-'
                            .$item_map['color_cd'].'-'
                            .$item_map['size_cd']
                        );
                        array_push($calum_list, "m_item_comb_hkey");
                        array_push($values_list, "'".$m_item_comb_hkey."'");
                        $calum_query = implode(',', $calum_list);
                        $values_query = implode(',', $values_list);

                        $arg_str = "";
                        $arg_str = "INSERT INTO t_returned_plan_info_tran";
                        $arg_str .= "(".$calum_query.")";
                        $arg_str .= " VALUES ";
                        $arg_str .= "(".$values_query.")";
                        //ChromePhp::LOG($arg_str);
                        $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
                        $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
                        $results_cnt = $result_obj["\0*\0_count"];
                        //ChromePhp::LOG($results_cnt);
                    }
                } else if ($item_map["individual_flg"] == false && !empty($item_map["return_num"])) {
                    // ※商品単位での登録の場合
                    $calum_list = array();
                    $values_list = array();

                    // 発注依頼行No.生成
                    $order_req_line_no = $cnt++;

                    // 企業ID
                    array_push($calum_list, "corporate_id");
                    array_push($values_list, "'".$auth['corporate_id']."'");
                    // 発注依頼No.
                    array_push($calum_list, "order_req_no");
                    array_push($values_list, "'".$shin_order_req_no."'");
                    // 発注依頼行No.
                    array_push($calum_list, "order_req_line_no");
                    array_push($values_list, "'".$order_req_line_no."'");
                    // レンタル契約No
                    array_push($calum_list, "rntl_cont_no");
                    array_push($values_list, "'".$wearer_data_input['agreement_no']."'");
                    // 商品コード
                    array_push($calum_list, "item_cd");
                    array_push($values_list, "'".$item_map['item_cd']."'");
                    // 色コード
                    array_push($calum_list, "color_cd");
                    array_push($values_list, "'".$item_map['color_cd']."'");
                    // サイズコード
                    array_push($calum_list, "size_cd");
                    array_push($values_list, "'".$item_map['size_cd']."'");
                    // 着用者コード
                    array_push($calum_list, "werer_cd");
                    array_push($values_list, "'".$wearer_size_change_post['werer_cd']."'");
                    // 客先社員コード
                    if (isset($wearer_data_input['cster_emply_cd'])) {
                        array_push($calum_list, "cster_emply_cd");
                        array_push($values_list, "'".$wearer_data_input['cster_emply_cd']."'");
                    }
                    // レンタル部門コード
                    array_push($calum_list, "rntl_sect_cd");
                    array_push($values_list, "'".$wearer_data_input['section']."'");
                    // 貸与パターン
//                    $job_type_cd = explode(':', $wearer_data_input['job_type']);
                    $job_type_cd = $wearer_data_input['job_type'];
                    array_push($calum_list, "job_type_cd");
                    array_push($values_list, "'".$job_type_cd."'");
                    // 発注依頼日
                    array_push($calum_list, "order_date");
                    array_push($values_list, "'".date('Y-m-d H:i:s', time())."'");
                    // 返却日
                    array_push($calum_list, "return_date");
                    array_push($values_list, "'".date("Ymd", strtotime($wearer_data_input['return_date']))."'");
                    // 返却ステータス(未返却)
                    array_push($calum_list, "return_status");
                    array_push($values_list, "'1'");
                    // 発注状況区分(その他交換)
                    array_push($calum_list, "order_sts_kbn");
                    array_push($values_list, "'4'");
                    // 返却予定数
                    array_push($calum_list, "return_plan_qty");
                    array_push($values_list, "'".$item_map['return_num']."'");
                    // 返却数
                    array_push($calum_list, "return_qty");
                    array_push($values_list, "'0'");
                    // 送信区分
                    if($params['snd_kbn']=='0'){
                        //未送信
                        array_push($calum_list, "snd_kbn");
                        array_push($values_list, "'0'");
                    }else{
                        //送信
                        array_push($calum_list, "snd_kbn");
                        array_push($values_list, "'1'");
                    }
                    // 理由区分
                    array_push($calum_list, "order_reason_kbn");
                    array_push($values_list, "'".$wearer_data_input['reason_kbn']."'");
                    // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
                    $m_section_comb_hkey = md5(
                        $auth['corporate_id'].'-'
                        .$wearer_data_input['agreement_no'].'-'
                        .$wearer_data_input['section']
                    );
                    array_push($calum_list, "m_section_comb_hkey");
                    array_push($values_list, "'".$m_section_comb_hkey."'");
                    // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
                    $m_item_comb_hkey = md5(
                        $auth['corporate_id'].'-'
                        .$item_map['item_cd'].'-'
                        .$item_map['color_cd'].'-'
                        .$item_map['size_cd']
                    );
                    array_push($calum_list, "m_item_comb_hkey");
                    array_push($values_list, "'".$m_item_comb_hkey."'");
                    $calum_query = implode(',', $calum_list);
                    $values_query = implode(',', $values_list);

                    $arg_str = "";
                    $arg_str = "INSERT INTO t_returned_plan_info_tran";
                    $arg_str .= "(".$calum_query.")";
                    $arg_str .= " VALUES ";
                    $arg_str .= "(".$values_query.")";
                    $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
                    $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
                    $results_cnt = $result_obj["\0*\0_count"];
                }
            }
        }

        // トランザクションコミット
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
    } catch (Exception $e) {
        // トランザクションロールバック
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));

        $json_list["error_code"] = "1";
        $error_msg = "入力登録処理において、データ更新エラーが発生しました。";
        array_push($json_list["error_msg"], $error_msg);

        echo json_encode($json_list);
        return;
    }

    // 返却伝票用パラメータ
    $json_list['param'] = '';
    $json_list['param'] .= $wearer_data_input['agreement_no'].':';
    $json_list['param'] .= $shin_order_req_no;

    $app->session->remove("wearer_size_change_post");
    echo json_encode($json_list);
    return;
});

/**
 * 発注入力（その他交換）
 * 発注取消処理
 */
$app->post('/wearer_other_change_delete', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    // 前画面セッション取得
    $wearer_size_change_post = $app->session->get("wearer_size_change_post");
    // フロントパラメータ取得
    $cond = $params['data'];

    $json_list = array();
    // DB更新エラーコード 0:正常 1:更新エラー
    $json_list["error_code"] = "0";

    // トランザクション開始
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
    try {
        //--着用者基本マスタトラン削除--//
        // 発注情報トランを参照
//        $query_list = array();
//        array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
//        array_push($query_list, "t_order_tran.order_req_no <> '".$cond['order_req_no']."'");
//        array_push($query_list, "t_order_tran.werer_cd = '".$cond['werer_cd']."'");
//        $query = implode(' AND ', $query_list);
//
//        $arg_str = "";
//        $arg_str = "SELECT ";
//        $arg_str .= "*";
//        $arg_str .= " FROM ";
//        $arg_str .= "t_order_tran";
//        $arg_str .= " WHERE ";
//        $arg_str .= $query;
//        $t_order_tran = new TOrderTran();
//        $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
//        $result_obj = (array)$results;
//        $results_cnt = $result_obj["\0*\0_count"];
//        // 上記発注情報トラン件数が0の場合に着用者基本マスタトランのデータを削除する
//        if (empty($results_cnt)) {
            $query_list = array();
            array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "m_wearer_std_tran.werer_cd = '".$cond['werer_cd']."'");
            array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$cond['rntl_cont_no']."'");
            array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$cond['rntl_sect_cd']."'");
            array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$cond['job_type_cd']."'");
            array_push($query_list, "m_wearer_std_tran.order_sts_kbn = '4'");
            $query = implode(' AND ', $query_list);

            $arg_str = "";
            $arg_str = "DELETE FROM ";
            $arg_str .= "m_wearer_std_tran";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $m_wearer_std_tran = new MWearerStdTran();
            $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
            $result_obj = (array)$results;
            $results_cnt = $result_obj["\0*\0_count"];
//        }

        //--発注情報トラン削除--//
        $query_list = array();
        array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "t_order_tran.order_req_no = '".$cond['order_req_no']."'");
        // 発注区分「その他交換」
        array_push($query_list, "t_order_tran.order_sts_kbn = '4'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "DELETE FROM ";
        $arg_str .= "t_order_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;

        $t_order_tran = new TOrderTran();
        $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];

        //--返却予定情報トラン削除--//
        $query_list = array();
        array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "t_returned_plan_info_tran.order_req_no = '".$cond['return_req_no']."'");
        array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$cond['rntl_cont_no']."'");
        array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$cond['werer_cd']."'");
        // 発注区分「その他交換」
        array_push($query_list, "t_returned_plan_info_tran.order_sts_kbn = '4'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "DELETE FROM ";
        $arg_str .= "t_returned_plan_info_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;

        $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
        $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];

        // トランザクションコミット
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
    } catch (Exception $e) {
        // トランザクションロールバック
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));

        $json_list["error_code"] = "1";

        echo json_encode($json_list);
        return;
    }
    $app->session->remove("wearer_size_change_post");
    echo json_encode($json_list);
});