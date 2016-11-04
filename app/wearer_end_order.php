<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
/**
 * 発注入力
 * 入力項目：理由区分
 */
$app->post('/wearer_end/reason_kbn', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_end_post = $app->session->get("wearer_end_post");
    //ChromePhp::LOG($wearer_end_post);

    //--発注管理単位取得--//
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "job_type_cd = '".$wearer_end_post['job_type_cd']."'");
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
    $list = array();
    $all_list = array();
    $json_list = array();

    array_push($query_list, "cls_cd = '002'");
    array_push($query_list, "relation_cls_cd = '001'");
    array_push($query_list, "relation_gen_cd = '2'");
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
            $list['reason_kbn'] = $result->gen_cd;
            $list['reason_kbn_name'] = $result->gen_name;

            // 発注情報トランフラグ有の場合は初期選択状態版を生成
            if ($wearer_end_post['order_req_no']) {
                if ($list['reason_kbn'] == $wearer_end_post['order_reason_kbn']) {
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
    echo json_encode($json_list);
});

/**
 * 発注入力 着用者情報
 * 入力項目：拠点
 */
$app->post('/section_wearer_end', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    // 前画面セッション取得
    $wearer_end_post = $app->session->get("wearer_end_post");
    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (rntl_sect_cd) *';
    $arg_str .= ' FROM m_section';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY rntl_sect_cd asc';

    $m_section = new MSection();
    $results = new Resultset(null, $m_section, $m_section->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

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
            $list['rntl_sect_cd'] = $result->rntl_sect_cd;
            $list['rntl_sect_name'] = $result->rntl_sect_name;

            // 発注情報トランフラグ有の場合は初期選択状態版を生成
            if ($wearer_end_post['order_req_no']) {
                if ($list['rntl_sect_cd'] == $wearer_end_post['rntl_sect_cd']) {

                    $list['selected'] = 'selected';
                } else {
                    $list['selected'] = '';
                }
            } else {
                $list['selected'] = '';
            }

            array_push($all_list, $list);
        }
    } else {
        $list['rntl_sect_cd'] = null;
        $list['rntl_sect_name'] = '';
        $list['selected'] = '';
        array_push($all_list, $list);
    }
    $json_list['disabled'] = 'disabled';
    $json_list['m_section_list'] = $all_list;
    echo json_encode($json_list);
});

/**
 * 発注入力（貸与終了）
 * 入力項目：貸与パターン
 */
$app->post('/job_type_wearer_end', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_end_post = $app->session->get("wearer_end_post");

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (job_type_cd) *';
    $arg_str .= ' FROM m_job_type';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY job_type_cd asc';

    $m_job_type = new MJobType();
    $results = new Resultset(NULL, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
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
            $list['job_type_cd'] = $result->job_type_cd;
            $list['job_type_name'] = $result->job_type_name;
            $list['sp_job_type_flg'] = $result->sp_job_type_flg;

            // 初期選択状態版を生成
            if ($list['job_type_cd'] == $wearer_end_post['job_type_cd']) {
                $list['selected'] = 'selected';
                $json_list['disabled'] = 'disabled';
            } else {
                $list['selected'] = '';
            }
            array_push($all_list, $list);
        }
    } else {
        $list['job_type_cd'] = NULL;
        $list['job_type_name'] = '';
        $list['sp_job_type_flg'] = '0';
        $list['selected'] = '';
        array_push($all_list, $list);
    }

    $json_list['job_type_list'] = $all_list;
    echo json_encode($json_list);
});

/**
 * 発注入力
 * 入力項目：異動日、コメント
 */
$app->post('/wearer_end_order_info', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_end_post = $app->session->get("wearer_end_post");
    $json_list['rntl_cont_no'] = $wearer_end_post['rntl_cont_no'];
    $json_list['werer_cd'] = $wearer_end_post['werer_cd'];
    $json_list['cster_emply_cd'] = $wearer_end_post['cster_emply_cd'];
    $json_list['sex_kbn'] = $wearer_end_post['sex_kbn'];
    $json_list['rntl_sect_cd'] = $wearer_end_post['rntl_sect_cd'];
    $json_list['job_type_cd'] = $wearer_end_post['job_type_cd'];
    $json_list['order_reason_kbn'] = $wearer_end_post['order_reason_kbn'];
    $json_list['order_tran_flg'] = $wearer_end_post['order_tran_flg'];
    $json_list['wearer_tran_flg'] = $wearer_end_post['wearer_tran_flg'];

    $query_list = array();
    $list = array();
    $all_list = array();
    $list['resfl_ymd'] = null;
    $list['memo'] = null;
    if ($wearer_end_post['wearer_tran_flg'] == '1') {
        //--着用者基本マスタトラン有の場合--//
        array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
        array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
        array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_end_post['job_type_cd']."'");
        array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_end_post['rntl_sect_cd']."'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "m_wearer_std_tran.resfl_ymd as as_resfl_ymd,";
        $arg_str .= "t_order_tran.memo as as_memo";
        $arg_str .= " FROM ";
        $arg_str .= "m_wearer_std_tran LEFT JOIN t_order_tran";
        $arg_str .= " ON m_wearer_std_tran.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
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
                // 移動日
                $list['resfl_ymd'] = date("Y/m/d",strtotime($result->as_resfl_ymd));
                // 備考欄
                $list['memo'] = $result->as_memo;
            }

            array_push($all_list, $list);
        }

        $json_list['wearer_info'] = $all_list;
    } elseif ($wearer_end_post['wearer_tran_flg'] == '0') {
        //--着用者基本マスタトラン無の場合--//
        array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "m_wearer_std.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
        array_push($query_list,"m_wearer_std.werer_cd = '".$wearer_end_post['werer_cd']."'");
        array_push($query_list, "m_wearer_std.job_type_cd = '".$wearer_end_post['job_type_cd']."'");
        array_push($query_list, "m_wearer_std.rntl_sect_cd = '".$wearer_end_post['rntl_sect_cd']."'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "m_wearer_std.resfl_ymd as as_resfl_ymd,";
        $arg_str .= "t_order_tran.memo as as_memo";
        $arg_str .= " FROM ";
        $arg_str .= "m_wearer_std LEFT JOIN t_order_tran";
        $arg_str .= " ON m_wearer_std.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
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
                // 移動日
                $list['resfl_ymd'] = date("Y/m/d",$result->as_resfl_ymd);
                // 備考欄
                $list['memo'] = $result->as_memo;
            }

            array_push($all_list, $list);
        }

        $json_list['wearer_info'] = $all_list;
    }
    $param_list = '';
    $param_list .= $wearer_end_post['rntl_cont_no'].':';
    $param_list .= $wearer_end_post['werer_cd'].':';
    $param_list .= $wearer_end_post['cster_emply_cd'].':';
    $param_list .= $wearer_end_post['sex_kbn'].':';
    $param_list .= $wearer_end_post['rntl_sect_cd'].':';
    $param_list .= $wearer_end_post['job_type_cd'].':';
    $param_list .= $wearer_end_post['ship_to_cd'].':';
    $param_list .= $wearer_end_post['ship_to_brnch_cd'].':';
    $param_list .= $wearer_end_post['order_reason_kbn'].':';
    $param_list .= $wearer_end_post['order_tran_flg'].':';
    $param_list .= $wearer_end_post['wearer_tran_flg'].':';
    $param_list .= $list['resfl_ymd'];
    $json_list['param'] = $param_list;
    $json_list['selected_job'] = $wearer_end_post['job_type_cd'];
    $json_list['order_req_no'] = $wearer_end_post['order_req_no'];
    echo json_encode($json_list);
});

/**
 * 発注入力
 * 入力項目：返却商品一覧
 */
$app->post('/wearer_end_order_list', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_end_post = $app->session->get("wearer_end_post");

    // フロントパラメータ取得
    $cond = $params['data'];

    //--一覧生成用の主要部門コード・職種コード取得--//
    // 着用者基本マスタ参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
    array_push($query_list, "werer_sts_kbn = '1'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "rntl_sect_cd,";
    $arg_str .= "job_type_cd";
    $arg_str .= " FROM ";
    $arg_str .= "m_wearer_std";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY upd_date DESC";

    $m_weare_std = new MWearerStd();
    $results = new Resultset(NULL, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    $m_wearer_cnt = $results_cnt;
    $m_wearer_rntl_sect_cd = NULL;
    $m_wearer_job_type_cd = NULL;
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
            //--以降、【発注前】職種コードとして使用--//
            // 着用者基本マスタ.レンタル部門コード
            $m_wearer_rntl_sect_cd = $result->rntl_sect_cd;
            // 着用者基本マスタ.職種コード
            $m_wearer_job_type_cd = $result->job_type_cd;
        }
    }

    // 発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_end_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_end_post['job_type_cd']."'");
    array_push($query_list, "order_req_no = '".$wearer_end_post['order_req_no']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "rntl_sect_cd,";
    $arg_str .= "job_type_cd";
    $arg_str .= " FROM ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY upd_date DESC";

    $t_order_tran = new TOrderTran();
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];

    $t_order_tran_cnt = $results_cnt;
    $t_order_tran_rntl_sect_cd = NULL;
    $t_order_tran_job_type_cd = NULL;
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
        //ChromePhp::LOG($results);
        foreach ($results as $result) {
            // 発注情報トラン.レンタル部門コード
            $t_order_tran_rntl_sect_cd = $result->rntl_sect_cd;
            // 発注情報トラン.職種コード
            $t_order_tran_job_type_cd = $result->job_type_cd;
        }
    }

    // 返却予定情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_end_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_end_post['job_type_cd']."'");
    array_push($query_list, "order_req_no = '".$wearer_end_post['return_req_no']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "rntl_sect_cd,";
    $arg_str .= "job_type_cd";
    $arg_str .= " FROM ";
    $arg_str .= "t_returned_plan_info_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY order_req_no DESC";

    $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
    $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    $t_returned_plan_info_tran_cnt = $results_cnt;
    $t_returned_plan_info_tran_rntl_sect_cd = NULL;
    $t_returned_plan_info_tran_job_type_cd = NULL;
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
        //ChromePhp::LOG($results);
        foreach ($results as $result) {
            // 発注情報トラン.レンタル部門コード
            $t_returned_plan_info_tran_rntl_sect_cd = $result->rntl_sect_cd;
            // 発注情報トラン.職種コード
            $t_returned_plan_info_tran_job_type_cd = $result->job_type_cd;
        }
    }


    //--返却商品の取得--//
    $query_list = array();
    $list = array();
    $now_wearer_list = array();
    array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_end_post['werer_cd']."'");
    array_push($query_list, "t_delivery_goods_state_details.rtn_ok_flg = '1'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (m_item.item_cd, m_item.color_cd, m_item.size_cd) ";
    $arg_str .= "t_delivery_goods_state_details.quantity as as_quantity,";
    $arg_str .= "t_delivery_goods_state_details.return_plan__qty as as_return_plan_qty,";
    $arg_str .= "t_delivery_goods_state_details.returned_qty as as_returned_qty,";
    $arg_str .= "m_item.item_cd as as_item_cd,";
    $arg_str .= "m_item.color_cd as as_color_cd,";
    $arg_str .= "m_item.size_cd as as_size_cd,";
    $arg_str .= "m_item.item_name as as_item_name,";
    $arg_str .= "m_input_item.job_type_item_cd as as_job_type_item_cd,";
    $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
    $arg_str .= "m_input_item.size_two_cd as as_size_two_cd,";
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
    $arg_str .= " ORDER BY as_item_cd ASC, as_color_cd ASC";
    $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
    $results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
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
        //ChromePhp::LOG($results);
        foreach ($results as $result) {
            // レンタル契約No
            $list["rntl_cont_no"] = $wearer_end_post['rntl_cont_no'];
            // 商品コード
            $list["item_cd"] = $result->as_item_cd;
            // 色コード
            $list["color_cd"] = $result->as_color_cd;
            // サイズコード
            $list["size_cd"] = $result->as_size_cd;
            // 商品名
            $list["item_name"] = $result->as_item_name;
            // 職種コード
            $list["job_type_cd"] = $m_wearer_job_type_cd;
            // 部門コード
            $list["rntl_sect_cd"] = $m_wearer_rntl_sect_cd;
            // 職種アイテムコード
            $list["job_type_item_cd"] = $result->as_job_type_item_cd;
            // サイズコード2
            $list["size_two_cd"] = $result->as_size_two_cd;
            // 標準投入数
            $list["std_input_qty"] = $result->as_std_input_qty;
            // 投入商品名
            $list["input_item_name"] = $result->as_input_item_name;
            // 数量
            $list["quantity"] = $result->as_quantity;
            // 返却予定数
            $list["return_plan_qty"] = $result->as_return_plan_qty;
            // 返却済数
            $list["returned_qty"] = $result->as_returned_qty;
            // 商品単位の返却可能枚数(所持枚数)
            $list["possible_num"] = $list["quantity"] - $list["return_plan_qty"] - $list["returned_qty"];

            array_push($now_wearer_list, $list);
        }
    }

    //--現在貸与中アイテム一覧リストの生成--//
    $chk_list = array();
    $now_list = array();
    if (!empty($now_wearer_list)) {
        $arr_cnt = 0;
        $list_cnt = 1;
        foreach ($now_wearer_list as $now_wearer_map) {
            $list = array();
            // name属性用カウント値
            $list["arr_num"] = $arr_cnt++;
            // No
            $list["list_no"] = $list_cnt++;
            // アイテム
            $list["item_name"] = $now_wearer_map["item_name"];
            // 選択方法
            //※着用者の職種マスタ.職種コードに紐づく投入商品マスタの職種アイテムコード単位で単一or複数判断
            $query_list = array();
            array_push($query_list, "m_job_type.corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "m_job_type.rntl_cont_no = '".$now_wearer_map['rntl_cont_no']."'");
            array_push($query_list, "m_job_type.job_type_cd = '".$now_wearer_map['job_type_cd']."'");
            array_push($query_list, "m_input_item.job_type_cd = '".$now_wearer_map['job_type_cd']."'");
            array_push($query_list, "m_input_item.item_cd = '".$now_wearer_map['item_cd']."'");
//       array_push($query_list, "m_input_item.color_cd = '".$now_wearer_map['color_cd']."'");
//       array_push($query_list, "m_input_item.size_two_cd = '".$now_wearer_map['size_two_cd']."'");
            $query = implode(' AND ', $query_list);

            $arg_str = "";
            $arg_str = "SELECT ";
            $arg_str .= "m_input_item.job_type_item_cd";
            $arg_str .= " FROM ";
            $arg_str .= "m_input_item";
            $arg_str .= " INNER JOIN ";
            $arg_str .= "m_job_type";
            $arg_str .= " ON ";
            $arg_str .= "m_input_item.m_job_type_comb_hkey=m_job_type.m_job_type_comb_hkey";
            $arg_str .= " WHERE ";
            $arg_str .= $query;

            $m_input_item = new MInputItem();
            $results = new Resultset(NULL, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
            $result_obj = (array)$results;
            $results_cnt = $result_obj["\0*\0_count"];
            if ($results_cnt > 1) {
                $list["choice"] = "複数選択";
                $list["choice_type"] = "2";
            } else {
                $list["choice"] = "単一選択";
                $list["choice_type"] = "1";
            }
            // 標準枚数
            $list["std_input_qty"] = $now_wearer_map['std_input_qty'];
            // 商品-色
            $list["item_and_color"] = $now_wearer_map['item_cd']."-".$now_wearer_map['color_cd'];
            // 商品名
            $list["input_item_name"] = $now_wearer_map['input_item_name'];
            // サイズ
            $list["size_cd"] = $now_wearer_map['size_cd'];
            // 個体管理番号
            // ※個体管理番号リスト、対象チェックボックス値の生成
            if ($auth["individual_flg"] == "1") {
                $list["individual_ctrl_no"] = "";
                $list["individual_chk"] = array();
                $individual_ctrl_no = array();
                $query_list = array();
                array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
                array_push($query_list, "t_delivery_goods_state_details.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
                array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$wearer_end_post['werer_cd']."'");
                array_push($query_list, "t_delivery_goods_state_details.item_cd = '".$now_wearer_map['item_cd']."'");
                array_push($query_list, "t_delivery_goods_state_details.color_cd = '".$now_wearer_map['color_cd']."'");
                array_push($query_list, "t_delivery_goods_state_details.size_cd = '".$list["size_cd"]."'");
                $query = implode(' AND ', $query_list);

                $arg_str = "";
                $arg_str = "SELECT ";
                $arg_str .= "individual_ctrl_no,";
                $arg_str .= "rtn_ok_flg";
                $arg_str .= " FROM ";
                $arg_str .= "t_delivery_goods_state_details";
                $arg_str .= " WHERE ";
                $arg_str .= $query;
                $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
                $results = new Resultset(NULL, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
                $result_obj = (array)$results;
                $results_cnt = $result_obj["\0*\0_count"];
                //ChromePhp::LOG($t_delivery_goods_state_details->getReadConnection()->query($arg_str));
                //ChromePhp::LOG($results_cnt);
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
                    $last_val = count($results);
                    $cnt = 1;
                    //ChromePhp::LOG($results);
                    foreach ($results as $result) {
                        $cnt = $cnt++;
                        array_push($individual_ctrl_no, $result->individual_ctrl_no);

                        // 返却可能フラグによるdisable制御
                        $individual = array();
                        $individual["individual_ctrl_no"] = $result->individual_ctrl_no;
                        if ($result->rtn_ok_flg == '0') {
                            $individual["disabled"] = "disabled";
                        } else {
                            $individual["disabled"] = "";
                        }

                        // 表示時チェックON/OFF設定
                        $query_list = array();
                        array_push($query_list, "t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");
                        array_push($query_list, "t_returned_plan_info_tran.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
                        array_push($query_list, "t_returned_plan_info_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
                        array_push($query_list, "t_returned_plan_info_tran.order_req_no = '".$wearer_end_post["order_req_no"]."'");
                        array_push($query_list, "t_returned_plan_info_tran.item_cd = '".$now_wearer_map['item_cd']."'");
                        array_push($query_list, "t_returned_plan_info_tran.color_cd = '".$now_wearer_map['color_cd']."'");
                        array_push($query_list, "t_returned_plan_info_tran.size_cd = '".$list["size_cd"]."'");
                        array_push($query_list, "t_returned_plan_info_tran.individual_ctrl_no = '".$individual["individual_ctrl_no"]."'");
                        $query = implode(' AND ', $query_list);

                        $arg_str = "";
                        $arg_str = "SELECT ";
                        $arg_str .= "individual_ctrl_no";
                        $arg_str .= " FROM ";
                        $arg_str .= "t_returned_plan_info_tran";
                        $arg_str .= " WHERE ";
                        $arg_str .= $query;

                        $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
                        $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
                        $result_obj = (array)$results;
                        $results_cnt = $result_obj["\0*\0_count"];
                        if ($results_cnt > 0) {
                            $individual["checked"] = "checked";
                        } else {
                            $individual["checked"] = "";
                        }
                        // 対象チェックname属性No
                        $individual["name_no"] = $list["arr_num"];
                        // 対象チェックボックス改行設定
                        if ($last_val == $cnt) {
                            $individual["br"] = "";
                        } else {
                            $individual["br"] = "<br/>";
                        }
                        // 対象チェックボックス値
                        array_push($list["individual_chk"], $individual);
                    }
                    // 表示個体管理番号数
                    $list["individual_cnt"] = count($individual_ctrl_no);
                    // 個体管理番号
                    $list["individual_ctrl_no"] = implode("<br>", $individual_ctrl_no);
                }
            }
            // 返却枚数
            $list["return_num"] = "";
            for ($i=0; $i<count($now_wearer_list); $i++) {
                if (
                    $now_wearer_map['item_cd'] == $now_wearer_list[$i]['item_cd']
                    && $now_wearer_map['color_cd'] == $now_wearer_list[$i]['color_cd']
                )
                {
                    if ($now_wearer_list[$i]['std_input_qty'] < $now_wearer_map['std_input_qty']) {
                        $list["return_num"] =$now_wearer_map['possible_num'];
                    }
                } else {
                    $list["return_num"] = $now_wearer_map['possible_num'];
                }
            }
            $list["return_num_disable"] = "disabled";
            // 返却可能枚数（所持数）
            $list["possible_num"] = $now_wearer_map['possible_num'];

            //--その他の必要hiddenパラメータ--//
            // 部門コード
            $list["rntl_sect_cd"] = $now_wearer_map['rntl_sect_cd'];
            // 職種コード
            $list["job_type_cd"] = $now_wearer_map['job_type_cd'];
            // 商品コード
            $list["item_cd"] = $now_wearer_map['item_cd'];
            // 色コード
            $list["color_cd"] = $now_wearer_map['color_cd'];
            // 職種アイテムコード
            $list["job_type_item_cd"] = $now_wearer_map['job_type_item_cd'];

            // 発注商品一覧」の入力項目「サイズ」作成
            //サイズコードセレクトボックス作成
            $size_list = array();
            $size_list_to = array();
            $size_list['size_cd'] = $list["size_cd"];
            array_push($size_list_to , $size_list);
            // サイズコードセレクトボックス
            $list['size_cd_list'] = $size_list_to;
            array_push($now_list, $list);
        }
    }
    // 現在貸与中アイテム一覧内容の表示フラグ
    if (!empty($now_list)) {
        $json_list["now_list_disp_flg"] = true;
    } else {
        $json_list["now_list_disp_flg"] = false;
    }
    // 貸与中アイテム一覧の「対象」、「個体管理番号」列の表示/非表示の制御フラグ
    $json_list["individual_flg"] = $auth['individual_flg'];
    $json_list["now_list_cnt"] = count($now_list);
    $json_list['list'] = $now_list;
    echo json_encode($json_list);

});

/*
 *  入力完了 or 発注送信
 */
$app->post('/wearer_end_order_insert', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');

    // 前画面セッション取得
    $wearer_end_post = $app->session->get("wearer_end_post");

    $cond = $params['cond'];
    $query_list = array();
    $list = array();
    $json_list = array();
    $error_list = array();
    // DB更新エラーコード 0:正常 1:更新エラー
    $json_list["error_code"] = "0";
    $json_list['error_msg'] = array();

    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_end_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_end_post['job_type_cd']."'");
    array_push($query_list, "order_sts_kbn != '2'");
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

    if ($results_cnt > 0) {
        $json_list["error_code"] = "2";
        $error_msg = "既に発注が入力されています。".PHP_EOL."貸与終了を行う場合は発注をキャンセルしてください。";
        $json_list["error_msg"] = $error_msg;
        echo json_encode($json_list);
        return;
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
    if (!empty($cond["comment"])) {
        if (mb_strlen($cond["comment"]) > 50) {
            array_push($error_list,'コメント欄は50文字以内で入力してください。');
            $json_list['error_msg'] = $error_list;
            $json_list["error_code"] = "1";
        }
    }
    if (!$cond['resfl_ymd']) {
        array_push($error_list,'異動日を入力してください。');
        $json_list['error_msg'] = $error_list;
        $json_list["error_code"] = "1";

    }
    $now_item_input = $params["now_item"];
    //DB登録
    if($json_list["error_code"]=="1"){
        echo json_encode($json_list);
        return true;
    }
    $transaction = $app->transactionManager->get();

    try {
        $query_list = array();
        array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
        array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
        array_push($query_list, "rntl_sect_cd = '".$cond['rntl_sect_cd']."'");
        array_push($query_list, "job_type_cd = '".$cond['job_type']."'");

        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $query_list);

        //--- クエリー実行・取得 ---//
        $m_wearer_std_tran = MWearerStdTran::query()
            ->where($query)
            ->columns(array('MWearerStdTran.*'))
            ->execute();


        //着用者基本情報トラン
        $now = date('Y/m/d H:i:s.sss');

        $m_wearer_std_tran = $m_wearer_std_tran[0];
        //--- クエリー実行・取得 ---//
        $m_wearer_std_tran->resfl_ymd  = $cond['resfl_ymd'];//異動日
        $m_wearer_std_tran->werer_sts_kbn  = '3';//着用者状況区分
        $m_wearer_std_tran->order_sts_kbn = '2'; //発注状況区分 汎用コード：終了
        $m_wearer_std_tran->upd_date  = $now;//更新日時
        $m_wearer_std_tran->upd_user_id = $auth['accnt_no'];//更新ユーザーID
        $m_wearer_std_tran->upd_pg_id = $auth['accnt_no'];//更新プログラムID
        $m_wearer_std_tran->m_job_type_comb_hkey = 1;//職種マスタ_統合ハッシュキー
        $m_wearer_std_tran->m_section_comb_hkey = 1;//部門マスタ_統合ハッシュキー

        // トランザクション開始
        $t_order_tran = new TOrderTran();
        $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query('begin'));

        //着用者基本情報トラン更新
        if ($m_wearer_std_tran->update() == false) {
            // トランザクションロールバック
            $m_wearer_std_tran = new MWearerStdTran();
            $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));
            $json_list["error_code"] = "1";
            $error_msg = "入力登録処理において、データ更新エラーが発生しました。";
            array_push($json_list["error_msg"], $error_msg);

            echo json_encode($json_list);
            return;
        }
        //--発注情報トラン登録--//
        $cnt = 1;
//        $now_item_input = $params["now_item"];

        // 着用アイテム内容登録
        if (!empty($now_item_input)) {
            // 現発注Noの発注情報トランをクリーン
//            if ($wearer_end_post['order_tran_flg'] == '1') {
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "order_req_no = '".$wearer_end_post["order_req_no"]."'");
            $query = implode(' AND ', $query_list);
            $arg_str = "";
            $arg_str = "DELETE FROM ";
            $arg_str .= "t_order_tran";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $t_order_tran = new TOrderTran();
            $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
//            }

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
            foreach ($now_item_input as $now_item_input_map) {
                $calum_list = array();
                $values_list = array();

                // 発注依頼行No.生成
                $order_req_line_no = $cnt++;

                // 発注情報_統合ハッシュキー(企業ID、発注依頼No、発注依頼行No)
                $t_order_comb_hkey = md5(
                    $auth['corporate_id']
                    .$shin_order_req_no
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
                // 発注状況区分(終了)
                array_push($calum_list, "order_sts_kbn");
                array_push($values_list, "'2'");
                // レンタル契約No
                array_push($calum_list, "rntl_cont_no");
                array_push($values_list, "'".$wearer_end_post['rntl_cont_no']."'");
                // レンタル部門コード
                array_push($calum_list, "rntl_sect_cd");
                array_push($values_list, "'".$cond['rntl_sect_cd']."'");
                // 貸与パターン
                array_push($calum_list, "job_type_cd");
                array_push($values_list, "'".$cond['job_type']."'");
                // 職種アイテムコード
                array_push($calum_list, "job_type_item_cd");
                array_push($values_list, "''");
                // 着用者コード
                array_push($calum_list, "werer_cd");
                array_push($values_list, "'".$m_wearer_std_tran->werer_cd."'");
                // 商品コード
                array_push($calum_list, "item_cd");
                array_push($values_list, "''");
                // 色コード
                array_push($calum_list, "color_cd");
                array_push($values_list, "''");
                // サイズコード
                array_push($calum_list, "size_cd");
                array_push($values_list, "''");
                // サイズコード2
                array_push($calum_list, "size_two_cd");
                array_push($values_list, "' '");
                // 出荷先、出荷先支店コード
                array_push($calum_list, "ship_to_cd");
                array_push($values_list, "''");
                array_push($calum_list, "ship_to_brnch_cd");
                array_push($values_list, "''");
                // 発注枚数
                array_push($calum_list, "order_qty");
                array_push($values_list, "'0'");
                // 備考欄
                array_push($calum_list, "memo");
                array_push($values_list, "'".$cond['comment']."'");
                // 着用者名
                array_push($calum_list, "werer_name");
                array_push($values_list, "'".$wearer_end_post['werer_name']."'");
                // 客先社員コード
                if (!empty($wearer_end_post['cster_emply_cd'])) {
                    array_push($calum_list, "cster_emply_cd");
                    array_push($values_list, "'".$wearer_end_post['cster_emply_cd']."'");
                }
                // 着用者状況区分(終了)
                array_push($calum_list, "werer_sts_kbn");
                array_push($values_list, "'3'");
                // 発令日
                if (!empty($wearer_end_post['appointment_ymd'])) {
                    $appointment_ymd = date('Ymd', strtotime($wearer_end_post['appointment_ymd']));
                    array_push($calum_list, "appointment_ymd");
                    array_push($values_list, "'".$appointment_ymd."'");
                } else {
                    array_push($calum_list, "appointment_ymd");
                    array_push($values_list, "NULL");
                }
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
                array_push($values_list, "'".date("Y/m/d H:i:s", time())."'");
                // 登録ユーザーID
                array_push($calum_list, "rgst_user_id");
                array_push($values_list, "'".$auth['accnt_no']."'");
                // 更新日時
                array_push($calum_list, "upd_date");
                array_push($values_list, "'".date("Y/m/d H:i:s", time())."'");
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
                array_push($values_list, "'".$cond['reason_kbn']."'");
                // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
                $m_item_comb_hkey = '1';
                array_push($calum_list, "m_item_comb_hkey");
                array_push($values_list, "'".$m_item_comb_hkey."'");
                // 職種マスタ_統合ハッシュキー(企業ID、レンタル契約No.、職種コード)
                $m_job_type_comb_hkey = '1';
                array_push($calum_list, "m_job_type_comb_hkey");
                array_push($values_list, "'".$m_job_type_comb_hkey."'");
                // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
                $m_section_comb_hkey = '1';
                array_push($calum_list, "m_section_comb_hkey");
                array_push($values_list, "'".$m_section_comb_hkey."'");
                // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
                $m_wearer_std_comb_hkey = $m_wearer_std_tran->m_wearer_std_comb_hkey;
                array_push($calum_list, "m_wearer_std_comb_hkey");
                array_push($values_list, "'".$m_wearer_std_comb_hkey."'");
                // 着用者商品マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード、職種アイテムコード、商品コード、色コード、サイズコード)
                $m_wearer_item_comb_hkey = '1';
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

                // 現発注Noの返却予定情報トランをクリーン
                if (!empty($wearer_end_post['return_req_no'])) {
                    //ChromePhp::LOG("返却予定情報トランクリーン");
                    $query_list = array();
                    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
                    array_push($query_list, "order_req_no = '".$wearer_end_post['return_req_no']."'");
                    // 発注区分「異動」
                    //array_push($query_list, "order_sts_kbn = '5'");
                    $query = implode(' AND ', $query_list);

                    $arg_str = "";
                    $arg_str = "DELETE FROM ";
                    $arg_str .= "t_returned_plan_info_tran";
                    $arg_str .= " WHERE ";
                    $arg_str .= $query;
                    //ChromePhp::LOG($arg_str);
                    $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
                    $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
                    $results_cnt = $result_obj["\0*\0_count"];
                    //ChromePhp::LOG($results_cnt);
                }
                if ($now_item_input_map["individual_flg"] == true && !empty($now_item_input_map["individual_data"])) {
                    // ※個体管理番号単位での登録の場合
                    foreach ($now_item_input_map["individual_data"] as $individual_data) {
                        // 対象にチェックされている商品のみが登録対象、それ以外は以降処理しない
                        if ($individual_data["now_target_flg"] == "0") {
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
                        array_push($values_list, "'".$wearer_end_post['rntl_cont_no']."'");
                        // 商品コード
                        array_push($calum_list, "item_cd");
                        array_push($values_list, "'".$now_item_input_map['now_item_cd']."'");
                        // 色コード
                        array_push($calum_list, "color_cd");
                        array_push($values_list, "'".$now_item_input_map['now_color_cd']."'");
                        // サイズコード
                        array_push($calum_list, "size_cd");
                        array_push($values_list, "'".$now_item_input_map['now_size_cd']."'");
                        // 個体管理番号
                        array_push($calum_list, "individual_ctrl_no");
                        array_push($values_list, "'".$individual_data['individual_ctrl_no']."'");
                        // 着用者コード
                        array_push($calum_list, "werer_cd");
                        array_push($values_list, "'".$wearer_end_post['werer_cd']."'");
                        // 客先社員コード
                        if (isset($wearer_end_post['cster_emply_cd'])) {
                            array_push($calum_list, "cster_emply_cd");
                            array_push($values_list, "'".$wearer_end_post['cster_emply_cd']."'");
                        }
                        // レンタル部門コード
                        array_push($calum_list, "rntl_sect_cd");
                        array_push($values_list, "'".$cond['rntl_sect_cd']."'");
                        // 貸与パターン
                        array_push($calum_list, "job_type_cd");
                        array_push($values_list, "'".$cond['job_type']."'");
                        // 発注依頼日
                        array_push($calum_list, "order_date");
                        array_push($values_list, "'".date('Y-m-d H:i:s', time())."'");
                        // 返却日
                        array_push($calum_list, "return_date");
                        array_push($values_list, "'".date('Y-m-d H:i:s', time())."'");
                        // 返却ステータス(未返却)
                        array_push($calum_list, "return_status");
                        array_push($values_list, "'1'");
                        // 発注状況区分(貸与終了)
                        array_push($calum_list, "order_sts_kbn");
                        array_push($values_list, "'2'");
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
                        array_push($values_list, "'".$cond['reason_kbn']."'");
                        // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
                        $m_section_comb_hkey = md5(
                            $auth['corporate_id']
                            .$wearer_end_post['rntl_cont_no']
                            .$cond['rntl_sect_cd']
                        );
                        array_push($calum_list, "m_section_comb_hkey");
                        array_push($values_list, "'".$m_section_comb_hkey."'");
                        // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
                        $m_item_comb_hkey = md5(
                            $auth['corporate_id']
                            .$now_item_input_map['now_item_cd']
                            .$now_item_input_map['now_color_cd']
                            .$now_item_input_map['now_size_cd']
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
                        ChromePhp::LOG($arg_str);
                        $results = new Resultset(NULL, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
                        $results_cnt = $result_obj["\0*\0_count"];
                    }
                } else if ($now_item_input_map["individual_flg"] == false && !empty($now_item_input_map["return_num"])) {
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
                    array_push($values_list, "'".$wearer_end_post['rntl_cont_no']."'");
                    // 商品コード
                    array_push($calum_list, "item_cd");
                    array_push($values_list, "'".$now_item_input_map['now_item_cd']."'");
                    // 色コード
                    array_push($calum_list, "color_cd");
                    array_push($values_list, "'".$now_item_input_map['now_color_cd']."'");
                    // サイズコード
                    array_push($calum_list, "size_cd");
                    array_push($values_list, "'".$now_item_input_map['now_size_cd']."'");
                    // 着用者コード
                    array_push($calum_list, "werer_cd");
                    array_push($values_list, "'".$wearer_end_post['werer_cd']."'");
                    // 客先社員コード
                    if (isset($wearer_end_post['cster_emply_cd'])) {
                        array_push($calum_list, "cster_emply_cd");
                        array_push($values_list, "'".$wearer_end_post['cster_emply_cd']."'");
                    }
                    // レンタル部門コード
                    array_push($calum_list, "rntl_sect_cd");
                    array_push($values_list, "'".$cond['rntl_sect_cd']."'");
                    // 貸与パターン
                    array_push($calum_list, "job_type_cd");
                    array_push($values_list, "'".$cond['job_type']."'");
                    // 発注依頼日
                    array_push($calum_list, "order_date");
                    array_push($values_list, "'".date('Y-m-d H:i:s', time())."'");
                    // 返却日
                    array_push($calum_list, "return_date");
                    array_push($values_list, "'".date('Y-m-d H:i:s', time())."'");
                    // 返却ステータス(未返却)
                    array_push($calum_list, "return_status");
                    array_push($values_list, "'1'");
                    // 発注状況区分(貸与終了)
                    array_push($calum_list, "order_sts_kbn");
                    array_push($values_list, "'2'");
                    // 返却予定数
                    array_push($calum_list, "return_plan_qty");
                    array_push($values_list, "'".$now_item_input_map['return_num']."'");
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
                    array_push($values_list, "'".$cond['reason_kbn']."'");
                    // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
                    $m_section_comb_hkey = md5(
                        $auth['corporate_id']
                        .$wearer_end_post['rntl_cont_no']
                        .$cond['rntl_sect_cd']
                    );
                    array_push($calum_list, "m_section_comb_hkey");
                    array_push($values_list, "'".$m_section_comb_hkey."'");
                    // 商品マスタ_統合ハッシュキー(企業ID、商品コード、色コード、サイズコード)
                    $m_item_comb_hkey = md5(
                        $auth['corporate_id']
                        .$now_item_input_map['now_item_cd']
                        .$now_item_input_map['now_color_cd']
                        .$now_item_input_map['now_size_cd']
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
        $transaction->commit();
        $json_list["error_code"] = "1";
        $error_msg = "入力登録処理において、データ更新エラーが発生しました。";
        array_push($json_list["error_msg"], $error_msg);

        echo json_encode($json_list);
        return;
    }
    $app->session->remove("wearer_end_post");
    echo json_encode($json_list);
    return;
});
/**
 * 発注入力
 * 発注取消処理
 */
$app->post('/wearer_end_order_delete', function ()use($app){

    $params = json_decode(file_get_contents("php://input"), true);

    $json_list = array();
    // アカウントセッション
    $auth = $app->session->get('auth');
    // 前画面セッション
    $wearer_end_post = $app->session->get("wearer_end_post");
    // フロントパラメータ
    if (!empty($params['data'])) {
      $cond = $params['data'];
    }

    $query_list = array();
    $list = array();
    $json_list = array();
    $error_list = array();
    // DB更新エラーコード 0:正常 1:更新エラー
    $json_list["error_code"] = "0";
    $json_list['error_msg'] = array();
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
    //DB登録
    if($json_list["error_code"]=="1"){
        echo json_encode($json_list);
        return true;
    }
    // トランザクション開始
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('begin'));
    try {
        //--着用者基本マスタトラン削除--//
        // 発注情報トランを参照
        $query_list = array();
        array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
        if (!empty($cond["order_req_no"])) {
          array_push($query_list, "t_order_tran.order_req_no <> '".$cond['order_req_no']."'");
        } else {
          array_push($query_list, "t_order_tran.order_req_no <> '".$wearer_end_post['order_req_no']."'");
        }
        if (!empty($cond["werer_cd"])) {
          array_push($query_list, "t_order_tran.werer_cd = '".$cond['werer_cd']."'");
        } else {
          array_push($query_list, "t_order_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
        }
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "*";
        $arg_str .= " FROM ";
        $arg_str .= "t_order_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $t_order_tran = new TOrderTran();
        $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];

        // 上記発注情報トラン件数が0の場合に着用者基本マスタトランのデータを削除する
        if (empty($results_cnt)) {
            $query_list = array();
            array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_end_post['werer_cd']."'");
            array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
            array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_end_post['rntl_sect_cd']."'");
            array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_end_post['job_type_cd']."'");
            // 発注区分「着用者編集」ではない
            array_push($query_list, "m_wearer_std_tran.order_sts_kbn <> '6'");
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
        }

        //--発注情報トラン削除--//
        $query_list = array();
        array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "t_order_tran.order_req_no = '".$wearer_end_post['order_req_no']."'");
        // 発注区分「異動」
        array_push($query_list, "t_order_tran.order_sts_kbn = '2'");
        // 理由区分「貸与終了」系ステータス
        $reason_kbn = array();
        array_push($reason_kbn, '05');
        array_push($reason_kbn, '06');
        array_push($reason_kbn, '07');
        array_push($reason_kbn, '08');
        if(!empty($reason_kbn)) {
            $reason_kbn_str = implode("','",$reason_kbn);
            $reason_kbn_query = "t_order_tran.order_reason_kbn IN ('".$reason_kbn_str."')";
            array_push($query_list, $reason_kbn_query);
        }
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
        array_push($query_list, "t_returned_plan_info_tran.order_req_no = '".$wearer_end_post['return_req_no']."'");
        // 発注区分「異動」
        array_push($query_list, "t_returned_plan_info_tran.order_sts_kbn = '2'");
        // 理由区分「貸与終了」系ステータス
        $reason_kbn = array();
        array_push($reason_kbn, '05');
        array_push($reason_kbn, '06');
        array_push($reason_kbn, '07');
        array_push($reason_kbn, '08');
        if(!empty($reason_kbn)) {
            $reason_kbn_str = implode("','",$reason_kbn);
            $reason_kbn_query = "t_returned_plan_info_tran.order_reason_kbn IN ('".$reason_kbn_str."')";
            array_push($query_list, $reason_kbn_query);
        }
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
        $error_msg = "発注送信処理において、データ更新エラーが発生しました。";
        array_push($json_list["error_msg"], $error_msg);

        echo json_encode($json_list);
        return;
    }
    echo json_encode($json_list);
});
/**
 * 発注入力
 * NGパターンチェック
 */
$app->post('/wearer_end_order_check', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');

    // 前画面セッション取得
    $wearer_end_post = $app->session->get("wearer_end_post");

    $json_list = array();
    $error_list = array();
    // DB更新エラーコード 0:正常 1:更新エラー
    $json_list["error_code"] = "0";
    $json_list['error_msg'] = array();

    //※発注情報トラン参照
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_end_post['rntl_cont_no']."'");
    array_push($query_list, "werer_cd = '".$wearer_end_post['werer_cd']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_end_post['rntl_sect_cd']."'");
    array_push($query_list, "job_type_cd = '".$wearer_end_post['job_type_cd']."'");
    array_push($query_list, "order_sts_kbn != '2'");
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

    if ($results_cnt > 0) {
        $json_list["error_code"] = "1";
        array_push($error_list,'既に発注が入力されています。貸与終了を行う場合は発注をキャンセルしてください。');
        $json_list['error_msg'] = $error_list;
        echo json_encode($json_list);
        return;
    }

    echo json_encode($json_list);
    return;
});
