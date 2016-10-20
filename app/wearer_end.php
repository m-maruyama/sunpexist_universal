<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 貸与終了検索
 */
$app->post('/wearer_end/search', function ()use($app){

    $params = json_decode(file_get_contents("php://input"), true);
    // アカウントセッション取得
    $auth = $app->session->get("auth");

    $cond = $params['cond'];
    $page = $params['page'];
    $query_list = array();

    //---検索条件---//
    //企業ID
    array_push($query_list,"m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
    //契約No
    if(!empty($cond['agreement_no'])){
        array_push($query_list,"m_wearer_std.rntl_cont_no = '".$cond['agreement_no']."'");
    }
    //客先社員コード
    if(!empty($cond['cster_emply_cd'])){
        array_push($query_list,"m_wearer_std.cster_emply_cd LIKE '".$cond['cster_emply_cd']."%'");
    }
    //着用者名（漢字）
    if(!empty($cond['werer_name'])){
        array_push($query_list,"m_wearer_std.werer_name LIKE '%".$cond['werer_name']."%'");
    }
    //性別
    if(!empty($cond['sex_kbn'])){
        array_push($query_list,"m_wearer_std.sex_kbn = '".$cond['sex_kbn']."'");
    }
    //拠点
    if(!empty($cond['section'])){
        array_push($query_list,"m_wearer_std.rntl_sect_cd = '".$cond['section']."'");
    }
    //貸与パターン
    if(!empty($cond['job_type'])){
        array_push($query_list,"m_wearer_std.job_type_cd = '".$cond['job_type']."'");
    }
    // 着用者状況区分(稼働)
    array_push($query_list,"m_wearer_std.werer_sts_kbn = '1'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //※着用基本マスタと着用基本マスタトランに「同じ企業ID」「同じ着用者コード」「同じレンタル契約No.」が存在した場合、
    //着用基本マスタトランの情報を優先して表示させる。（
    //---SQLクエリー実行---//
    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (m_wearer_std.werer_cd) ";
    $arg_str .= "m_wearer_std.corporate_id as as_corporate_id,";
    $arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
    $arg_str .= "m_wearer_std.rntl_cont_no as as_rntl_cont_no,";
    $arg_str .= "m_wearer_std.rntl_sect_cd as as_rntl_sect_cd,";
    $arg_str .= "m_wearer_std.job_type_cd as as_job_type_cd,";
    $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std.sex_kbn as as_sex_kbn,";
    $arg_str .= "m_wearer_std.snd_kbn as as_wearer_snd_kbn,";
    $arg_str .= "m_wearer_std.ship_to_cd as as_ship_to_cd,";
    $arg_str .= "m_wearer_std.ship_to_brnch_cd as as_ship_to_brnch_cd,";
    $arg_str .= "wst.rntl_sect_name as wst_rntl_sect_name,";
    $arg_str .= "wjt.job_type_name as wjt_job_type_name,";
    $arg_str .= "t_order_tran.order_req_no as as_order_req_no,";
    $arg_str .= "t_order_tran.order_sts_kbn as as_order_sts_kbn,";
    $arg_str .= "t_order_tran.snd_kbn as as_order_snd_kbn,";
    $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";
    $arg_str .= "t_order_tran.upd_date as as_order_upd_date,";
    $arg_str .= "t_returned_plan_info_tran.order_req_no as as_return_req_no";
    $arg_str .= " FROM ";
    $arg_str .= "(m_wearer_std INNER JOIN m_section as wst";
    $arg_str .= " ON (m_wearer_std.corporate_id = wst.corporate_id";
    $arg_str .= " AND m_wearer_std.rntl_cont_no = wst.rntl_cont_no";
    $arg_str .= " AND m_wearer_std.rntl_sect_cd = wst.rntl_sect_cd)";
    $arg_str .= " INNER JOIN m_job_type as wjt";
    $arg_str .= " ON (m_wearer_std.corporate_id = wjt.corporate_id";
    $arg_str .= " AND m_wearer_std.rntl_cont_no = wjt.rntl_cont_no";
    $arg_str .= " AND m_wearer_std.job_type_cd = wjt.job_type_cd))";
    $arg_str .= " LEFT JOIN ";
    $arg_str .= "(t_order_tran INNER JOIN m_section as os";
    $arg_str .= " ON (t_order_tran.corporate_id = os.corporate_id";
    $arg_str .= " AND t_order_tran.rntl_cont_no = os.rntl_cont_no";
    $arg_str .= " AND t_order_tran.rntl_sect_cd = os.rntl_sect_cd)";
    $arg_str .= " INNER JOIN m_job_type as ojt";
    $arg_str .= " ON (t_order_tran.corporate_id = ojt.corporate_id";
    $arg_str .= " AND t_order_tran.rntl_cont_no = ojt.rntl_cont_no";
    $arg_str .= " AND t_order_tran.job_type_cd = ojt.job_type_cd))";
    $arg_str .= " ON (m_wearer_std.corporate_id = t_order_tran.corporate_id";
    $arg_str .= " AND m_wearer_std.rntl_cont_no = t_order_tran.rntl_cont_no";
    $arg_str .= " AND m_wearer_std.rntl_sect_cd = t_order_tran.rntl_sect_cd";
    $arg_str .= " AND m_wearer_std.job_type_cd = t_order_tran.job_type_cd)";
    $arg_str .= " LEFT JOIN ";
    $arg_str .= "(t_returned_plan_info_tran INNER JOIN m_section as rs";
    $arg_str .= " ON (t_returned_plan_info_tran.corporate_id = rs.corporate_id";
    $arg_str .= " AND t_returned_plan_info_tran.rntl_cont_no = rs.rntl_cont_no";
    $arg_str .= " AND t_returned_plan_info_tran.rntl_sect_cd = rs.rntl_sect_cd)";
    $arg_str .= " INNER JOIN m_job_type as rjt";
    $arg_str .= " ON (t_returned_plan_info_tran.corporate_id = rjt.corporate_id";
    $arg_str .= " AND t_returned_plan_info_tran.rntl_cont_no = rjt.rntl_cont_no";
    $arg_str .= " AND t_returned_plan_info_tran.job_type_cd = rjt.job_type_cd))";
    $arg_str .= " ON (m_wearer_std.corporate_id = t_returned_plan_info_tran.corporate_id";
    $arg_str .= " AND m_wearer_std.rntl_cont_no = t_returned_plan_info_tran.rntl_cont_no";
    $arg_str .= " AND m_wearer_std.rntl_sect_cd = t_returned_plan_info_tran.rntl_sect_cd";
    $arg_str .= " AND m_wearer_std.job_type_cd = t_returned_plan_info_tran.job_type_cd)";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_cster_emply_cd ASC,as_order_upd_date DESC";

    $m_weare_std = new MWearerStd();
    $results = new Resultset(null, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];

    $paginator_model = new PaginatorModel(
        array(
            "data"  => $results,
            "limit" => $page['records_per_page'],
            "page" => $page['page_number']
        )
    );

    $all_list = array();
    $json_list = array();
    $list = array();
    if(!empty($results_cnt)){


        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;
        //ChromePhp::LOG("着用者基本マスタリスト");
        //ChromePhp::LOG($results);

        foreach($results as $result) {
            //---着用者基本マスタトラン情報の既存データ重複参照---//
            $query_list = array();
            //---検索条件---//
            //企業ID
            array_push($query_list,"m_wearer_std_tran.corporate_id = '".$result->as_corporate_id."'");
            //契約No
            array_push($query_list,"m_wearer_std_tran.rntl_cont_no = '".$result->as_rntl_cont_no."'");

            array_push($query_list,"m_wearer_std_tran.werer_cd = '".$result->as_werer_cd."'");

            //  ※着用者基本マスタトランを参照する場合は、＜検索条件＞に下記を追加する。
            //	発注情報トラン．発注状況区分 ＝ 貸与終了
            array_push($query_list,"t_order_tran.order_sts_kbn = '2'");

            $query = implode(' AND ', $query_list);

            $arg_str = "";
            $arg_str = "SELECT ";
            $arg_str .= "m_wearer_std_tran.corporate_id as as_corporate_id,";
            $arg_str .= "m_wearer_std_tran.werer_cd as as_werer_cd,";
            $arg_str .= "m_wearer_std_tran.rntl_cont_no as as_rntl_cont_no,";
            $arg_str .= "m_wearer_std_tran.rntl_sect_cd as as_rntl_sect_cd,";
            $arg_str .= "m_wearer_std_tran.job_type_cd as as_job_type_cd,";
            $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
            $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
            $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";
            $arg_str .= "m_wearer_std_tran.snd_kbn as as_wearer_snd_kbn,";
            $arg_str .= "m_wearer_std_tran.ship_to_cd as as_ship_to_cd,";
            $arg_str .= "m_wearer_std_tran.ship_to_brnch_cd as as_ship_to_brnch_cd,";
            $arg_str .= "wst.rntl_sect_name as wst_rntl_sect_name,";
            $arg_str .= "wjt.job_type_name as wjt_job_type_name,";
            $arg_str .= "t_order_tran.order_req_no as as_order_req_no,";
            $arg_str .= "t_order_tran.order_sts_kbn as as_order_sts_kbn,";
            $arg_str .= "t_order_tran.snd_kbn as as_order_snd_kbn,";
            $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";
            $arg_str .= "t_returned_plan_info_tran.order_req_no as as_return_req_no";
            $arg_str .= " FROM ";
            $arg_str .= "(m_wearer_std_tran INNER JOIN m_section as wst";
            $arg_str .= " ON (m_wearer_std_tran.corporate_id = wst.corporate_id";
            $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wst.rntl_cont_no";
            $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = wst.rntl_sect_cd)";
            $arg_str .= " INNER JOIN m_job_type as wjt";
            $arg_str .= " ON (m_wearer_std_tran.corporate_id = wjt.corporate_id";
            $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = wjt.rntl_cont_no";
            $arg_str .= " AND m_wearer_std_tran.job_type_cd = wjt.job_type_cd))";
            $arg_str .= " LEFT JOIN ";
            $arg_str .= "(t_order_tran INNER JOIN m_section as os";
            $arg_str .= " ON (t_order_tran.corporate_id = os.corporate_id";
            $arg_str .= " AND t_order_tran.rntl_cont_no = os.rntl_cont_no";
            $arg_str .= " AND t_order_tran.rntl_sect_cd = os.rntl_sect_cd)";
            $arg_str .= " INNER JOIN m_job_type as ojt";
            $arg_str .= " ON (t_order_tran.corporate_id = ojt.corporate_id";
            $arg_str .= " AND t_order_tran.rntl_cont_no = ojt.rntl_cont_no";
            $arg_str .= " AND t_order_tran.job_type_cd = ojt.job_type_cd))";
            $arg_str .= " ON (m_wearer_std_tran.corporate_id = t_order_tran.corporate_id";
            $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = t_order_tran.rntl_cont_no";
            $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = t_order_tran.rntl_sect_cd";
            $arg_str .= " AND m_wearer_std_tran.job_type_cd = t_order_tran.job_type_cd)";
            $arg_str .= " LEFT JOIN ";
            $arg_str .= "(t_returned_plan_info_tran INNER JOIN m_section as rs";
            $arg_str .= " ON (t_returned_plan_info_tran.corporate_id = rs.corporate_id";
            $arg_str .= " AND t_returned_plan_info_tran.rntl_cont_no = rs.rntl_cont_no";
            $arg_str .= " AND t_returned_plan_info_tran.rntl_sect_cd = rs.rntl_sect_cd)";
            $arg_str .= " INNER JOIN m_job_type as rjt";
            $arg_str .= " ON (t_returned_plan_info_tran.corporate_id = rjt.corporate_id";
            $arg_str .= " AND t_returned_plan_info_tran.rntl_cont_no = rjt.rntl_cont_no";
            $arg_str .= " AND t_returned_plan_info_tran.job_type_cd = rjt.job_type_cd))";
            $arg_str .= " ON (m_wearer_std_tran.corporate_id = t_returned_plan_info_tran.corporate_id";
            $arg_str .= " AND m_wearer_std_tran.rntl_cont_no = t_returned_plan_info_tran.rntl_cont_no";
            $arg_str .= " AND m_wearer_std_tran.rntl_sect_cd = t_returned_plan_info_tran.rntl_sect_cd";
            $arg_str .= " AND m_wearer_std_tran.job_type_cd = t_returned_plan_info_tran.job_type_cd)";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $arg_str .= " ORDER BY m_wearer_std_tran.upd_date DESC";

            $m_weare_std_tran = new MWearerStdTran();
            $tran_results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
            $tran_result_obj = (array)$tran_results;
            $tran_results_cnt = $tran_result_obj["\0*\0_count"];

            // 着用者基本マスタトラン情報に重複データがある場合、優先させて着用者基本マスタ情報リストを上書きする
            if (!empty($tran_results_cnt)) {
                // 着用者マスタトラン有フラグ
                $list['wearer_tran_flg'] = '1';

                $paginator_model = new PaginatorModel(
                    array(
                        "data"  => $tran_results,
                        "limit" => 1,
                        "page" => 1
                    )
                );
                $paginator = $paginator_model->getPaginate();
                $tran_results = $paginator->items;
                foreach($tran_results as $tran_result) {
                    $result->as_rntl_cont_no = $tran_result->as_rntl_cont_no;
                    $result->as_rntl_sect_cd = $tran_result->as_rntl_sect_cd;
                    $result->as_job_type_cd = $tran_result->as_job_type_cd;
                    $result->as_werer_cd = $tran_result->as_werer_cd;
                    $result->as_cster_emply_cd = $tran_result->as_cster_emply_cd;
                    $result->as_werer_name = $tran_result->as_werer_name;
                    $result->as_sex_kbn = $tran_result->as_sex_kbn;
                    $result->as_wearer_snd_kbn = $tran_result->as_wearer_snd_kbn;
                    $result->as_ship_to_cd = $tran_result->as_ship_to_cd;
                    $result->as_ship_to_brnch_cd = $tran_result->as_ship_to_brnch_cd;
                    $result->wst_rntl_sect_name = $tran_result->wst_rntl_sect_name;
                    $result->wjt_job_type_name = $tran_result->wjt_job_type_name;
                    $result->as_order_req_no = $tran_result->as_order_req_no;
                    $result->as_order_sts_kbn = $tran_result->as_order_sts_kbn;
                    $result->as_order_snd_kbn = $tran_result->as_order_snd_kbn;
                    $result->as_order_reason_kbn = $tran_result->as_order_reason_kbn;
                    $result->as_return_req_no = $tran_result->as_return_req_no;
                }
            } else {
                // 着用者マスタトラン無
                $list['wearer_tran_flg'] = '0';
            }
            $list['werer_cd'] = $result->as_werer_cd;
            $list['corporate_id'] = $result->as_corporate_id;
            $list['rntl_cont_no'] = $result->as_rntl_cont_no;
            // レンタル部門コード
            $list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
            // 職種コード
            $list['job_type_cd'] = $result->as_job_type_cd;
            // 社員番号
            if (!empty($result->as_cster_emply_cd)) {
                $list['cster_emply_cd'] = $result->as_cster_emply_cd;
            } else {
                $list['cster_emply_cd'] = "-";
            }
            // 出荷先コード
            $list['ship_to_cd'] = $result->as_ship_to_cd;
            // 出荷先支店コード
            $list['ship_to_brnch_cd'] = $result->as_ship_to_brnch_cd;
            // 着用者名
            if (!empty($result->as_werer_name)) {
                $list['werer_name'] = $result->as_werer_name;
            } else {
                $list['werer_name'] = "-";
            }
            //---性別名称---//
            $query_list = array();
            array_push($query_list, "cls_cd = '004'");
            array_push($query_list, "gen_cd = '".$result->as_sex_kbn."'");
            $query = implode(' AND ', $query_list);
            $gencode = MGencode::query()
                ->where($query)
                ->columns('*')
                ->execute();
            foreach ($gencode as $gencode_map) {
                $list['sex_kbn'] = $gencode_map->gen_name;
            }

            // 発注、発注情報トラン有無フラグ
            if (isset($result->as_order_sts_kbn)) {
                $list['order_kbn'] = "<font color='red'>済</font>";
                // 発注情報トラン有
                $list['order_tran_flg'] = '1';
            }else{
                $list['order_kbn'] = "未";
                // 発注情報トラン無
                $list['order_tran_flg'] = '0';
            }
            // 発注
            if (!empty($result->as_cster_emply_cd)) {
                $list['order_kbn'] = "済";
            }else{
                $list['order_kbn'] = "未";
            }
            // 状態
            $list['snd_kbn'] = "-";
            if (!empty($result->as_snd_kbn)) {
                if($result->as_snd_kbn == '0'){
                    $list['snd_kbn'] = "未送信";
                }elseif($result->as_snd_kbn == '1'){
                    $list['snd_kbn'] = "送信済";
                }elseif($result->as_snd_kbn == '9'){
                    $list['snd_kbn'] = "処理中";
                }
            }
            // 拠点
            if (!empty($result->wst_rntl_sect_name)) {
                $list['rntl_sect_name'] = $result->wst_rntl_sect_name;
            } else {
                $list['rntl_sect_name'] = "-";
            }
            // 貸与パターン
            if (!empty($result->wjt_job_type_name)) {
                $list['job_type_name'] = $result->wjt_job_type_name;
            } else {
                $list['job_type_name'] = "-";
            }
            // 発注No(返却予定情報トラン)
            $list['return_req_no'] = $result->as_return_req_no;
            //貸与終了ボタン
            // 発注情報トラン参照
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "rntl_cont_no = '".$list['rntl_cont_no']."'");
            array_push($query_list, "werer_cd = '".$list['werer_cd']."'");
            array_push($query_list, "rntl_sect_cd = '".$list['rntl_sect_cd']."'");
            array_push($query_list, "job_type_cd = '".$list['job_type_cd']."'");
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

            // チェックスタート
            $list['btnPattern'] = "";
            $patarn_flg = true;
            if (!empty($t_order_tran_cnt)) {
                $paginator_model = new PaginatorModel(
                    array(
                        "data" => $t_order_tran_results,
                        "limit" => $t_order_tran_cnt,
                        "page" => 1
                    )
                );
                $paginator = $paginator_model->getPaginate();
                $t_order_tran_results = $paginator->items;

                $a_flg = false;
                //貸与終了ボタン
                foreach ($t_order_tran_results as $t_order_tran_result) {
                    $order_req_no = $t_order_tran_result->order_req_no;
                    $order_sts_kbn = $t_order_tran_result->order_sts_kbn;
                    $order_reason_kbn = $t_order_tran_result->order_reason_kbn;
                    $snd_kbn = $t_order_tran_result->snd_kbn;
                    //パターンA：
                    if ($order_sts_kbn == '2' && ($order_reason_kbn != '05' && $order_reason_kbn != '06' && $order_reason_kbn != '08')) {
                        $a_flg = true;
                    } else {
                        $a_flg = false;
                        break;
                    }
                }
                if (!$a_flg) {
                    if ($order_sts_kbn == '2' && $snd_kbn == '0') {
                        //パターンB： 発注情報トラン．発注状況区分 = 貸与終了 かつ、発注情報トラン．理由区分 = 不要品返却以外のデータがある場合、かつ、
                        //発注情報トラン．送信区分 = 未送信の場合、ボタンの文言は「貸与終了[済]」で表示する。)
                        $list['wearer_end_button'] = "貸与終了";
                        $list['wearer_end_red'] = "[済]";
                        $list['order_req_no'] = $order_req_no;
                        $list['order_reason_kbn'] = $order_reason_kbn;
                        $list['return_reciept_button'] = "返却伝票ダウンロード";
                    } elseif ($order_sts_kbn == '2' && $snd_kbn == '1') {
                        //パターンC： 発注情報トラン．発注状況区分 = 貸与終了 かつ、発注情報トラン．理由区分 = 不要品返却以外のデータがある場合、かつ、
                        //発注情報トラン．送信区分 = 送信済の場合、ボタンの文言は「貸与終了[済]」で非活性表示する。
                        $list['wearer_end_button'] = "貸与終了[済]";
                        $list['wearer_end_red'] = "[済]";
                        $list['disabled'] = "disabled";
                        $list['return_reciept_button'] = "返却伝票ダウンロード";
                    } elseif ($order_sts_kbn != '2' && $snd_kbn == '1') {
                        //パターンD： 発注情報トラン．発注状況区分 = 貸与終了以外のデータがある場合、かつ、
                        //その発注の送信区分 = 送信済の場合、ボタンの文言は「貸与終了」で非活性表示する。
                        $list['wearer_end_button'] = "貸与終了";
                        $list['disabled'] = "disabled";
                    }
                } else {
                    //パターンA： 発注情報トラン．発注状況区分 = 貸与終了 かつ、発注情報トラン．理由区分 = 不要品返却以外のデータがない場合 、
                    //ボタンの文言は「貸与終了」で表示する。
                    $list['wearer_end_button'] = '貸与終了';
                    $list['order_req_no'] = $order_req_no;
                    $list['order_reason_kbn'] = $order_reason_kbn;
                }
            }
            // 発注入力へのパラメータ設定
            $list['param'] = '';
            $list['param'] .= $list['rntl_cont_no'].':';
            $list['param'] .= $list['werer_cd'].':';
            $list['param'] .= $list['cster_emply_cd'].':';
            $list['param'] .= $list['sex_kbn'].':';
            $list['param'] .= $list['rntl_sect_cd'].':';
            $list['param'] .= $list['job_type_cd'].':';
            $list['param'] .= $list['order_reason_kbn'].':';
            $list['param'] .= $list['ship_to_cd'].':';
            $list['param'] .= $list['ship_to_brnch_cd'].':';
            $list['param'] .= $list['order_tran_flg'].':';
            $list['param'] .= $list['wearer_tran_flg'].':';
            $list['param'] .= $list['order_req_no'].':';
            $list['param'] .= $list['return_req_no'];
            array_push($all_list,$list);
        }
    }

    $page_list['records_per_page'] = $page['records_per_page'];
    $page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = $results_cnt;
    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;
    echo json_encode($json_list);
});
/*
 * 着用者情報セッション登録
 */
$app->post('/wearer_end/session', function ()use($app){
    $json_list = array();
    $params = json_decode(file_get_contents("php://input"), true);
    // アカウントセッション取得
    $auth = $app->session->get("auth");

    echo json_encode($json_list);
});
