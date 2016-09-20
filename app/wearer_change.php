<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 職種変更または異動検索
 */
$app->post('/wearer_change/search', function ()use($app){

    $params = json_decode(file_get_contents("php://input"), true);
    // アカウントセッション取得
    $auth = $app->session->get("auth");

    $cond = $params['cond'];
    $page = $params['page'];
    $query_list = array();
    $query_list2 = array();
    ChromePhp::LOG($cond);
    //---検索条件---//
    //企業ID
    array_push($query_list,"m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list2,"m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
    //契約No
    if(!empty($cond['agreement_no'])){
        array_push($query_list,"m_wearer_std_tran.rntl_cont_no = '".$cond['agreement_no']."'");
        array_push($query_list2,"m_wearer_std.rntl_cont_no = '".$cond['agreement_no']."'");
    }
    //客先社員コード
    if(!empty($cond['cster_emply_cd'])){
        array_push($query_list,"m_wearer_std_tran.cster_emply_cd LIKE '".$cond['cster_emply_cd']."%'");
        array_push($query_list2,"m_wearer_std.cster_emply_cd LIKE '".$cond['cster_emply_cd']."%'");
    }
    //着用者名（漢字）
    if(!empty($cond['werer_name'])){
        array_push($query_list,"m_wearer_std_tran.werer_name LIKE '%".$cond['werer_name']."%'");
        array_push($query_list2,"m_wearer_std.werer_name LIKE '%".$cond['werer_name']."%'");
    }
    //性別
    if(!empty($cond['sex_kbn'])){
        array_push($query_list,"m_wearer_std_tran.sex_kbn = '".$cond['sex_kbn']."'");
        array_push($query_list2,"m_wearer_std.sex_kbn = '".$cond['sex_kbn']."'");
    }
    //拠点
    if(!empty($cond['section'])){
        array_push($query_list,"m_wearer_std_tran.rntl_sect_cd = '".$cond['section']."'");
        array_push($query_list2,"m_wearer_std.rntl_sect_cd = '".$cond['section']."'");
    }
    //貸与パターン
    if(!empty($cond['job_type'])){
        array_push($query_list,"m_wearer_std_tran.job_type_cd = '".$cond['job_type']."'");
        array_push($query_list2,"m_wearer_std.job_type_cd = '".$cond['job_type']."'");
    }
    // 着用者状況区分(稼働)
    array_push($query_list,"m_wearer_std.werer_sts_kbn = '1'");
    array_push($query_list2,"m_wearer_std.werer_sts_kbn = '1'");


    //  ※着用者基本マスタトランを参照する場合は、＜検索条件＞に下記を追加する。
    //	発注情報トラン．発注状況区分 ＝ 貸与終了
    array_push($query_list,"t_order_tran.order_sts_kbn = '2'");
    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    $query2 = implode(' AND ', $query_list2);
    $sort_key ='';
    $order ='';

    //ソート設定（指定がないので社員番号）
    $q_sort_key = "as_cster_emply_cd";
    $order = 'asc';

    //---SQLクエリー実行---//
    $arg_str = "(SELECT ";
    $arg_str .= "t_order_tran.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "t_order_tran.order_sts_kbn as as_order_sts_kbn,";
    $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";
    $arg_str .= "t_order_tran.snd_kbn as as_snd_kbn,";
    $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";
    $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
    $arg_str .= "m_job_type.job_type_name as as_job_type_name";
    $arg_str .= " FROM m_wearer_std_tran LEFT JOIN t_order_tran";
    $arg_str .= " ON m_wearer_std_tran.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
    $arg_str .= " LEFT JOIN m_wearer_std";
    $arg_str .= " ON m_wearer_std_tran.m_wearer_std_comb_hkey = m_wearer_std.m_wearer_std_comb_hkey";
    $arg_str .= " INNER JOIN m_section";
    $arg_str .= " ON m_wearer_std_tran.m_section_comb_hkey = m_section.m_section_comb_hkey";
    $arg_str .= " INNER JOIN m_job_type";
    $arg_str .= " ON t_order_tran.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
    $arg_str .= " LEFT JOIN m_item";
    $arg_str .= " ON t_order_tran.m_item_comb_hkey = m_item.m_item_comb_hkey";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") UNION";
    $arg_str .= "( SELECT ";
    $arg_str .= "t_order_tran.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "t_order_tran.order_sts_kbn as as_order_sts_kbn,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std.sex_kbn as as_sex_kbn,";
    $arg_str .= "t_order_tran.snd_kbn as as_snd_kbn,";
    $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";
    $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
    $arg_str .= "m_job_type.job_type_name as as_job_type_name";
    $arg_str .= " FROM m_wearer_std LEFT JOIN t_order_tran";
    $arg_str .= " ON m_wearer_std.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
    $arg_str .= " INNER JOIN m_section";
    $arg_str .= " ON t_order_tran.m_section_comb_hkey = m_section.m_section_comb_hkey";
    $arg_str .= " INNER JOIN m_job_type";
    $arg_str .= " ON t_order_tran.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
    $arg_str .= " LEFT JOIN m_item";
    $arg_str .= " ON t_order_tran.m_item_comb_hkey = m_item.m_item_comb_hkey";
    $arg_str .= " WHERE ";
    $arg_str .= $query2.")";
    if (!empty($q_sort_key)) {
        $arg_str .= " ORDER BY ";
        $arg_str .= $q_sort_key." ".$order;
    }

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

    if(!empty($results_cnt)){
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach($results as $result) {
            $list = array();
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
            //貸与終了ボタン
            //パターンA： 発注情報トラン．発注状況区分 = 貸与終了 かつ、発注情報トラン．理由区分 = 不要品返却以外のデータが無い場合、
            //ボタンの文言は「貸与終了」で表示する。
            if ($result->as_order_sts_kbn == '2' && $result->as_order_reason_kbn == '7') {
                $list['wearer_end_button'] = '貸与終了';
                //パターンB： 発注情報トラン．発注状況区分 = 貸与終了 かつ、発注情報トラン．理由区分 = 不要品返却以外のデータがある場合、かつ、
                //発注情報トラン．送信区分 = 未送信の場合、ボタンの文言は「貸与終了[済]」で表示する。
            } elseif ($result->as_order_sts_kbn == '2' && $result->as_order_reason_kbn != '7'&& $result->as_snd_kbn == '0') {
                $list['wearer_end_button'] = "貸与終了";
                $list['wearer_end_red'] = "[済]";
                //パターンC： 発注情報トラン．発注状況区分 = 貸与終了 かつ、発注情報トラン．理由区分 = 不要品返却以外のデータがある場合、かつ、
                //発注情報トラン．送信区分 = 送信済の場合、ボタンの文言は「貸与終了[済]」で非活性表示する。
            } elseif ($result->as_order_sts_kbn == '2' && $result->as_order_reason_kbn != '7'&& $result->as_snd_kbn == '1') {
                $list['wearer_end_button'] = "貸与終了[済]";
                $list['wearer_end_red'] = "[済]";
                $list['disabled'] = "disabled";
                //パターンD： 発注情報トラン．発注状況区分 = 貸与終了以外のデータがある場合、かつ、
                //その発注の送信区分 = 送信済の場合、ボタンの文言は「貸与終了」で非活性表示する。
            } elseif ($result->as_order_sts_kbn != '2' && $result->as_snd_kbn == '1') {
                $list['wearer_end_button'] = "貸与終了";
                $list['disabled'] = "disabled";
            }

            //返却伝票ダウンロード(上記パターンBかCの場合表示)
            if (($result->as_order_sts_kbn == '2' && $result->as_order_reason_kbn != '7'&& $result->as_snd_kbn == '0')||
                ($result->as_order_sts_kbn == '2' && $result->as_order_reason_kbn != '7'&& $result->as_snd_kbn == '1')){
                $list['return_reciept_button'] = "返却伝票ダウンロード";
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

    $page_list['records_per_page'] = $page['records_per_page'];
    $page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = $results_cnt;
    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;
    echo json_encode($json_list);
});
