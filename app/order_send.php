<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;



/**
 * 発注送信処理
 */
$app->post('/order_send', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    $cond = $params['cond'];
    $page = $params['page'];
    $query_list = array();

    //---注文情報マスタ---//
    //企業ID
    array_push($query_list, "m_wearer_std_tran.corporate_id = '" . $auth['corporate_id'] . "'");
    //契約No
    if (!empty($cond['agreement_no'])) {
        array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '" . $cond['agreement_no'] . "'");
    }
    //客先社員コード
    if (!empty($cond['cster_emply_cd'])) {
        array_push($query_list, "m_wearer_std_tran.cster_emply_cd LIKE '" . $cond['cster_emply_cd'] . "%'");
    }
    //着用者名（漢字）
    if (!empty($cond['werer_name'])) {
        array_push($query_list, "m_wearer_std_tran.werer_name LIKE '%" . $cond['werer_name'] . "%'");
    }
    //性別
    if (!empty($cond['sex_kbn'])) {
        array_push($query_list, "m_wearer_std_tran.sex_kbn = '" . $cond['sex_kbn'] . "'");
    }
    //拠点
    if (!empty($cond['section'])) {
        array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '" . $cond['section'] . "'");
    }
    //貸与パターン
    if (!empty($cond['job_type'])) {
        array_push($query_list, "m_wearer_std_tran.job_type_cd = '" . $cond['job_type'] . "'");
    }
    // 発注状況区分（貸与）
    //array_push($query_list, "t_order_tran.order_sts_kbn = '1'");

    $query = implode(' AND ', $query_list);
    //ChromePhp::LOG($query);

    //発注情報トラン
    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    //ChromePhp::LOG($arg_str);
    //発注noを被らせないための重複削除
    $arg_str .= "(SELECT distinct on (t_order_tran.order_req_no) ";

    $arg_str .= "t_order_tran.order_req_no as as_order_req_no,";//発注no
    $arg_str .= "t_order_tran.order_req_ymd as as_order_req_ymd,";//発注日
    $arg_str .= "t_order_tran.cster_emply_cd as as_cster_emply_cd,";//客先社員コード
    $arg_str .= "t_order_tran.order_sts_kbn as as_order_sts_kbn,";//発注状況区分
    $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";//発注理由区分
    $arg_str .= "t_order_tran.snd_kbn as as_order_snd_kbn,";//送信区分
    $arg_str .= "t_order_tran.upd_date as as_order_upd_date,";//更新日時
    $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";//性別
    $arg_str .= "m_wearer_std_tran.corporate_id as as_corporate_id,";//会社概要
    $arg_str .= "m_wearer_std_tran.rntl_cont_no as as_rntl_cont_no,";//レンタル契約no
    $arg_str .= "m_wearer_std_tran.rntl_sect_cd as as_rntl_sect_cd,";//レンタル部門コード
    $arg_str .= "m_wearer_std_tran.job_type_cd as as_job_type_cd,";//職種コード
    $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";//着用者漢字
    $arg_str .= "m_wearer_std_tran.werer_cd as as_werer_cd,";//着用者cd
    $arg_str .= "wst.rntl_sect_name as wst_rntl_sect_name,";
    $arg_str .= "wjt.job_type_name as wjt_job_type_name";
    $arg_str .= " FROM ";
    $arg_str .= "(t_order_tran INNER JOIN m_section as os ON t_order_tran.m_section_comb_hkey = os.m_section_comb_hkey";
    $arg_str .= " INNER JOIN m_job_type as ojt ON t_order_tran.m_job_type_comb_hkey = ojt.m_job_type_comb_hkey)";
    $arg_str .= " LEFT JOIN ";
    $arg_str .= "(m_wearer_std_tran INNER JOIN m_section as wst ON m_wearer_std_tran.m_section_comb_hkey = wst.m_section_comb_hkey";
    $arg_str .= " INNER JOIN m_job_type as wjt ON m_wearer_std_tran.m_job_type_comb_hkey = wjt.m_job_type_comb_hkey)";
    $arg_str .= " ON m_wearer_std_tran.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
    $arg_str .= " WHERE ";
    $arg_str .= $query;

    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_order_req_no ASC,as_order_upd_date DESC";


    $t_order_tran = new TOrderTran();
    $results = new Resultset(null, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    $tran_result_obj = (array)$results;
    $tran_results_cnt = $tran_result_obj["\0*\0_count"];


    $paginator_model = new PaginatorModel(
        array(
            "data"  => $results,
            "limit" => $tran_results_cnt,
            "page" => 1
        )
    );
    $paginator = $paginator_model->getPaginate();
    $results = $paginator->items;



    array_push($query_list, "m_wearer_std_tran.order_sts_kbn = '6'");
    $query2 = implode(' AND ', $query_list);


    //着用者基本マスタトラン
    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    //ChromePhp::LOG($arg_str);
    //発注noを被らせないための重複削除
    $arg_str .= "(SELECT distinct on (m_wearer_std_tran.werer_cd) ";

    $arg_str .= "t_order_tran.order_req_no as as_order_req_no,";//発注no
    $arg_str .= "t_order_tran.order_req_ymd as as_order_req_ymd,";//発注日
    $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";//客先社員コード
    $arg_str .= "m_wearer_std_tran.order_sts_kbn as as_order_sts_kbn,";//発注状況区分
    $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";//発注理由区分
    $arg_str .= "m_wearer_std_tran.snd_kbn as as_order_snd_kbn,";//送信区分
    $arg_str .= "m_wearer_std_tran.upd_date as as_order_upd_date,";//更新日時
    $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";//性別
    $arg_str .= "m_wearer_std_tran.corporate_id as as_corporate_id,";//会社概要
    $arg_str .= "m_wearer_std_tran.rntl_cont_no as as_rntl_cont_no,";//レンタル契約no
    $arg_str .= "m_wearer_std_tran.rntl_sect_cd as as_rntl_sect_cd,";//レンタル部門コード
    $arg_str .= "m_wearer_std_tran.job_type_cd as as_job_type_cd,";//職種コード
    $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";//着用者漢字
    $arg_str .= "m_wearer_std_tran.werer_cd as as_werer_cd,";//着用者cd
    $arg_str .= "wst.rntl_sect_name as wst_rntl_sect_name,";
    $arg_str .= "wjt.job_type_name as wjt_job_type_name";
//    $arg_str .= "t_order_tran.snd_kbn as as_snd_kbn,";
    $arg_str .= " FROM ";
    $arg_str .= "(m_wearer_std_tran INNER JOIN m_section as wst ON m_wearer_std_tran.m_section_comb_hkey = wst.m_section_comb_hkey";
    $arg_str .= " INNER JOIN m_job_type as wjt ON m_wearer_std_tran.m_job_type_comb_hkey = wjt.m_job_type_comb_hkey)";
    $arg_str .= " LEFT JOIN ";
    $arg_str .= "(t_order_tran INNER JOIN m_section as os ON t_order_tran.m_section_comb_hkey = os.m_section_comb_hkey";
    $arg_str .= " INNER JOIN m_job_type as ojt ON t_order_tran.m_job_type_comb_hkey = ojt.m_job_type_comb_hkey)";
    $arg_str .= " ON m_wearer_std_tran.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
    $arg_str .= " WHERE ";
    $arg_str .= $query2;
    //ChromePhp::LOG($arg_str);

    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_order_req_no ASC,as_order_upd_date DESC";

    $m_wearer_std_tran = new MWearerStdTran();
    $results2 = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));

    $tran_result_obj = (array)$results2;
    $tran_results_cnt = $tran_result_obj["\0*\0_count"];

    $paginator_model = new PaginatorModel(
        array(
            "data"  => $results2,
            "limit" => $tran_results_cnt,
            "page" => 1
        )
    );
    $paginator = $paginator_model->getPaginate();
    $results2 = $paginator->items;

    $results = array_merge($results,$results2);


    $list = array();
    $all_list = array();
    $json_list = array();

    if (!empty($results_cnt)) {

        foreach ($results as $result) {
            //---着用者基本マスタトラン情報の既存データ重複参照---//

            // レンタル部門コード
            $list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
            // 職種コード
            $list['job_type_cd'] = $result->as_job_type_cd;
            // 発注No
            $list['order_req_no'] = $result->as_order_req_no;
            // 発注日
            $list['order_req_ymd'] = $result->as_order_req_ymd;
            // 発注状況区分
            $list['order_sts_kbn'] = $result->as_order_sts_kbn;
            //送信区分
            $list['order_snd_kbn'] = $result->as_order_snd_kbn;
            //コーポレートid
            $list['corporate_id'] = $result->as_corporate_id;
            //レンタル契約no
            $list['rntl_cont_no'] = $result->as_rntl_cont_no;
            //着用者コード
            $list['werer_cd'] = $result->as_werer_cd;


            // 理由区分
            if (isset($result->as_order_reason_kbn)) {
                $list['order_reason_kbn'] = $result->as_order_reason_kbn;
            } else {
                $list['order_reason_kbn'] = null;
            }
            // 発注ID
            $list['order_req_no'] = $result->as_order_req_no;
            // 社員番号
            if (isset($result->as_cster_emply_cd)) {
                $list['cster_emply_cd'] = $result->as_cster_emply_cd;
            } else {
                $list['cster_emply_cd'] = null;
            }
            // 性別区分
            $list['sex_kbn'] = $result->as_sex_kbn;

            // 着用者名
            if (!empty($result->as_werer_name)) {
                $list['werer_name'] = $result->as_werer_name;
            } else {
                $list['werer_name'] = "-";
            }

            //---性別名称---//
            $query_list = array();
            array_push($query_list, "cls_cd = '004'");
            array_push($query_list, "gen_cd = '" . $result->as_sex_kbn . "'");
            $query = implode(' AND ', $query_list);
            $gencode = MGencode::query()
                ->where($query)
                ->columns('*')
                ->execute();
            foreach ($gencode as $gencode_map) {
                $list['sex_kbn_name'] = $gencode_map->gen_name;
            }
            //---発注区分---//
            $query_list = array();
            array_push($query_list, "cls_cd = '001'");
            array_push($query_list, "gen_cd = '" . $result->as_order_sts_kbn . "'");
            $query = implode(' AND ', $query_list);
            $gencode = MGencode::query()
                ->where($query)
                ->columns('*')
                ->execute();
            foreach ($gencode as $gencode_map) {
                $list['order_sts_kbn_name'] = $gencode_map->gen_name;
            }
            //---理由区分---//
            $query_list = array();
            array_push($query_list, "cls_cd = '002'");
            array_push($query_list, "gen_cd = '" . $result->as_order_reason_kbn . "'");
            $query = implode(' AND ', $query_list);
            $gencode = MGencode::query()
                ->where($query)
                ->columns('*')
                ->execute();
            foreach ($gencode as $gencode_map) {
                $list['order_reason_kbn_name'] = $gencode_map->gen_name;
            }


            // 発注、発注情報トラン有無フラグ
            if (isset($result->as_order_sts_kbn)) {
                $list['order_kbn'] = "済";
                // 発注情報トラン有
                $list['order_tran_flg'] = '1';
            } else {
                $list['order_kbn'] = "未";
                // 発注情報トラン無
                $list['order_tran_flg'] = '0';
            }
            // 状態、着用者マスタトラン有無フラグ
            $list['snd_kbn'] = "-";
            if (isset($result->as_order_snd_kbn)) {
                // 状態
                if ($result->as_order_snd_kbn == '0') {
                    $list['snd_kbn'] = "未送信";
                } elseif ($result->as_order_snd_kbn == '1') {
                    $list['snd_kbn'] = "送信済";
                } elseif ($result->as_order_snd_kbn == '9') {
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


            array_push($all_list, $list);
        }
    }
    ChromePhp::LOG($all_list);

    $page_list['total_records'] = $results_cnt;
    $json_list['list'] = $all_list;

    echo json_encode($json_list);
});


/**
 * 発注送信ボタン押した時の
 *
 */
$app->post('/order_change', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // パラメータ取得
    $order_data = $params['data'];
    //リスト作成
    $json_list = array();
    $error_list = array();

    if ($error_list) {
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);

        return true;
    }

    $transaction = $app->transactionManager->get();

    foreach ($order_data as $order) {

        $corporate_id = $order['corporate_id'];
        $werer_cd = $order['werer_cd'];
        $rntl_cont_no = $order['rntl_cont_no'];
        $job_type_cd = $order['job_type_cd'];
        $order_req_no = $order['order_req_no'];


        //着用者情報マスタトランの更新設定
        $m_wearer_std_tran_results = MWearerStdTran::find(array(
            'conditions' => "corporate_id = '$corporate_id' AND werer_cd = '$werer_cd' AND rntl_cont_no = '$rntl_cont_no' AND job_type_cd = '$job_type_cd'"
        ));
        $m_wearer_std_tran_results[0]->snd_kbn = 1;//送信済
        $m_wearer_std_tran_results[0]->upd_date = date('Y/m/d H:i:s.sss', time()); //更新日時

        $m_wearer_std_tran = $m_wearer_std_tran_results[0];


        //発注者情報トランの更新設定
        $t_order_tran_results = TOrderTran::find(array(
            'conditions' => "corporate_id = '$corporate_id' AND werer_cd = '$werer_cd' AND rntl_cont_no = '$rntl_cont_no' AND job_type_cd = '$job_type_cd' AND order_req_no = '$order_req_no'"
        ));


        if ($m_wearer_std_tran->save() == false) {
            $error_list['order'] = '送信区分の変更が失敗しました。';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            $transaction->rollBack();
            return;
        }

        foreach ($t_order_tran_results as $t_order_tran_value) {
            //ChromePhp::LOG($t_order_tran_value);
            $t_order_tran_value->snd_kbn = 1;//送信済
            $t_order_tran_value->upd_date = date('Y/m/d H:i:s.sss', time()); //更新日時
            $t_order_tran = $t_order_tran_value;
            if ($t_order_tran->save() == false) {
                $error_list['order'] = '送信区分の変更が失敗しました。';
                $json_list['errors'] = $error_list;
                echo json_encode($json_list);
                $transaction->rollBack();
                return;
            }
        }
        //ChromePhp::LOG($t_order_tran_results);
        //ChromePhp::LOG($t_order_tran);

        //if ($t_order_tran->save() == false) {
        //    $error_list['order'] = '送信区分の変更が失敗しました。';
        //   $json_list['errors'] = $error_list;
        //   echo json_encode($json_list);
        //   $transaction->rollBack();
        //   return;
        // }

    }

    $transaction->commit();

    echo json_encode($json_list);
    return true;

});






/**
 * 発注送信キャンセルボタン押した時
 *
 */
$app->post('/order_send/cancel', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // パラメータ取得
    $order_data = $params['data'];
    //リスト作成
    $json_list = array();
    $error_list = array();

    if ($error_list) {
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);

        return true;
    }

    $transaction = $app->transactionManager->get();


        $corporate_id = $order_data['corporate_id'];
        $werer_cd = $order_data['werer_cd'];
        $rntl_cont_no = $order_data['rntl_cont_no'];
        $job_type_cd = $order_data['job_type_cd'];
        $order_req_no = $order_data['order_req_no'];


        //着用者情報マスタトランの更新設定
        $m_wearer_std_tran_results = MWearerStdTran::find(array(
            'conditions' => "corporate_id = '$corporate_id' AND werer_cd = '$werer_cd' AND rntl_cont_no = '$rntl_cont_no' AND job_type_cd = '$job_type_cd'"
        ));
        $m_wearer_std_tran_results[0]->snd_kbn = 0;//送信済
        $m_wearer_std_tran_results[0]->upd_date = date('Y/m/d H:i:s.sss', time()); //更新日時

        $m_wearer_std_tran = $m_wearer_std_tran_results[0];


        //発注者情報トランの更新設定
        $t_order_tran_results = TOrderTran::find(array(
            'conditions' => "corporate_id = '$corporate_id' AND werer_cd = '$werer_cd' AND rntl_cont_no = '$rntl_cont_no' AND job_type_cd = '$job_type_cd' AND order_req_no = '$order_req_no'"
        ));


        if ($m_wearer_std_tran->save() == false) {
            $error_list['order'] = '送信区分の変更が失敗しました。';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            $transaction->rollBack();
            return;
        }

        foreach ($t_order_tran_results as $t_order_tran_value) {
            //ChromePhp::LOG($t_order_tran_value);
            $t_order_tran_value->snd_kbn = 0;//送信済
            $t_order_tran_value->upd_date = date('Y/m/d H:i:s.sss', time()); //更新日時
            $t_order_tran = $t_order_tran_value;
            if ($t_order_tran->save() == false) {
                $error_list['order'] = '送信区分の変更が失敗しました。';
                $json_list['errors'] = $error_list;
                echo json_encode($json_list);
                $transaction->rollBack();
                return;
            }
        }
        //ChromePhp::LOG($t_order_tran_results);
        //ChromePhp::LOG($t_order_tran);

        //if ($t_order_tran->save() == false) {
        //    $error_list['order'] = '送信区分の変更が失敗しました。';
        //   $json_list['errors'] = $error_list;
        //   echo json_encode($json_list);
        //   $transaction->rollBack();
        //   return;
        // }


    $transaction->commit();

    echo json_encode($json_list);
    return true;

});

