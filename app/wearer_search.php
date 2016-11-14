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

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    $cond = $params['cond'];
    $page = $params['page'];
    $query_list = array();
    //---既存着用者基本マスタ情報リスト取得---//
    //企業ID
    array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
    //契約No
    if(!empty($cond['agreement_no'])){
        array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$cond['agreement_no']."'");
    }
    //客先社員コード
    if(!empty($cond['cster_emply_cd'])){
        array_push($query_list,"m_wearer_std_tran.cster_emply_cd LIKE '".$cond['cster_emply_cd']."%'");
    }
    //着用者名（漢字）
    if(!empty($cond['werer_name'])){
        array_push($query_list, "m_wearer_std_tran.werer_name LIKE '%".$cond['werer_name']."%'");
    }
    //性別
    if(!empty($cond['sex_kbn'])){
        array_push($query_list,"m_wearer_std_tran.sex_kbn = '".$cond['sex_kbn']."'");
    }
    //拠点
    if(!empty($cond['section'])){
        array_push($query_list,"m_wearer_std_tran.rntl_sect_cd = '".$cond['section']."'");
    }
    //貸与パターン
    if(!empty($cond['job_type'])){
        array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$cond['job_type']."'");
    }
    // 発注情報トラン．発注状況区分 = 貸与
    array_push($query_list,"(t_order_tran.order_sts_kbn = '1' or t_order_tran.order_sts_kbn IS NULL)");
    // 発注情報トラン．理由区分 <> 追加貸与
    array_push($query_list,"(t_order_tran.order_reason_kbn != '03' or t_order_tran.order_reason_kbn IS NULL)");

    $query = implode(' AND ', $query_list);

    //---SQLクエリー実行---//
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (m_wearer_std_tran.m_wearer_std_comb_hkey) ";
    $arg_str .= "m_wearer_std_tran.m_wearer_std_comb_hkey as as_m_wearer_std_comb_hkey,";
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
    $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
    $arg_str .= "m_job_type.job_type_name as as_job_type_name";
    $arg_str .= " FROM m_wearer_std_tran";
    $arg_str .= " LEFT JOIN m_section";
    $arg_str .= " ON m_wearer_std_tran.m_section_comb_hkey = m_section.m_section_comb_hkey";
    $arg_str .= " LEFT JOIN m_job_type";
    $arg_str .= " ON m_wearer_std_tran.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
    $arg_str .= " LEFT JOIN t_order_tran";
    $arg_str .= " ON m_wearer_std_tran.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";

    $m_weare_std_tran= new MWearerStdTran();
    $results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
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
            if (isset($result->as_order_reason_kbn)) {
                $list['order_reason_kbn'] = $result->as_order_reason_kbn;
            } else {
                $list['order_reason_kbn'] = '7';
            }
            // 着用者コード
            $list['werer_cd'] = $result->as_werer_cd;
            // 社員番号
            if (isset($result->as_cster_emply_cd)) {
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
                ChromePhp::LOG($result->as_rntl_sect_name);
                $list['rntl_sect_name'] = "-";
            }
            // 貸与パターン
            if (!empty($result->as_job_type_name)) {
                $list['job_type_name'] = $result->as_job_type_name;
            } else {
                $list['job_type_name'] = "-";
            }

            //---「貸与開始」ボタンの生成---//
            if ($result->as_wearer_order_sts_kbn == '1')
            {
                //パターンA： 発注情報トラン．着用者基本マスタトラン．発注区分 = 貸与
                //ボタンの文言は「貸与開始」で表示する。
                $list['wearer_input_button'] = '貸与開始';
            } elseif (
                ($result->as_order_sts_kbn == '1'
                    && $result->as_order_reason_kbn != '3') && ($result->as_snd_kbn == '0'))
            {
                //パターンB： 発注情報トラン．発注状況区分 = 貸与 かつ、発注情報トラン．理由区分 = 追加貸与以外のデータがある場合、
                //かつ、着用者基本マスタトラン．送信区分 = 未送信の場合、ボタンの文言は「貸与開始[済]」で表示する。

                $list['wearer_input_button'] = "貸与開始";
                $list['wearer_input_red'] = "[済]";
            } elseif (
                ($result->as_order_sts_kbn == '1'
                    && $result->as_order_reason_kbn != '3') && ($result->as_snd_kbn == '1'))
            {
                //パターンC： 発注情報トラン．発注状況区分 = 貸与 かつ、
                //発注情報トラン．理由区分 = 追加貸与以外のデータがある場合、かつ、
                //着用者基本マスタトラン．送信区分 = 送信済の場合、
                //ボタンの文言は「貸与開始[済]」で非活性表示する。

                $list['wearer_input_button'] = "貸与開始";
                $list['wearer_input_red'] = "[済]";
                $list['disabled'] = "disabled";
            }

            //「返却伝票ダウンロード」ボタン生成
            if (
                ($result->as_order_sts_kbn == '1'
                    && ($result->as_order_reason_kbn == '4' || $result->as_order_reason_kbn == '8' || $result->as_order_reason_kbn == '9' || $result->as_order_reason_kbn == '11')
                    && $result->as_snd_kbn == '0') ||
                ($result->as_order_sts_kbn == '2'
                    && ($result->as_order_reason_kbn == '4' || $result->as_order_reason_kbn == '8' || $result->as_order_reason_kbn == '9' || $result->as_order_reason_kbn == '11')
                    && $result->as_snd_kbn == '1'))
            {
                //「貸与開始」ボタン生成のパターンBかCの場合に表示
                $list['return_reciept_button'] = "返却伝票ダウンロード";
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
            $list['m_wearer_std_comb_hkey'] = $result->as_m_wearer_std_comb_hkey;
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
            $list['param'] .= $list['wearer_tran_flg'].':';
            $list['param'] .= $list['appointment_ymd'].':';
            $list['param'] .= $list['resfl_ymd'].':';
            $list['param'] .= $list['m_wearer_std_comb_hkey'].':';
            $list['param'] .= $list['order_req_no'];
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



/**
 * 「貸与開始」ボタンの押下時のパラメータのセッション保持
 * →発注入力（貸与開始）にてパラメータ利用
 */
$app->post('/wearer_search/req_param', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // パラメータ取得
    $cond = $params['data'];
    $wearer_odr_post = $app->session->get("wearer_odr_post");

    if(isset($wearer_odr_post['order_reason_kbn'])){
        $order_reason_kbn = $wearer_odr_post["order_reason_kbn"];

    }elseif(isset($cond["order_reason_kbn"])){
        $order_reason_kbn = $cond["order_reason_kbn"];
    }else{
        $order_reason_kbn = '7';
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

    }elseif(isset($cond["wearer_tran_flg"])){
        $wearer_tran_flg = $cond["wearer_tran_flg"];
    }else{
        $wearer_tran_flg = '0';
    }

    if(!$cond['ship_to_cd']){
        // アカウントセッション取得
        $auth = $app->session->get("auth");
        //拠点のマスタチェック
        $query_list = array();
        // 部門マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
        array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
        // 部門マスタ．レンタル契約No.　＝　画面で選択されている契約No.
        array_push($query_list,"rntl_cont_no = '".$cond['rntl_cont_no']."'");
        // 部門マスタ．レンタル部門コード　＝　画面で選択されている拠点
        array_push($query_list,"rntl_sect_cd = '".$cond['rntl_sect_cd']."'");

        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $query_list);
        //--- クエリー実行・取得 ---//
        $m_section = MSection::find(array(
            'conditions' => $query
        ));
        $cond['ship_to_cd'] = $m_section[0]->std_ship_to_cd;
        $cond['ship_to_brnch_cd'] = $m_section[0]->std_ship_to_brnch_cd;
    }
    if(!isset($cond["werer_name"])){
        $cond["werer_name"] = '';
    }
    if(!isset($cond["werer_name_kana"])){
        $cond["werer_name_kana"] = '';
    }
    if(!isset($cond["m_wearer_std_comb_hkey"])&&!isset($wearer_odr_post['m_wearer_std_comb_hkey'])){
        $cond["m_wearer_std_comb_hkey"] = '';
    }elseif($wearer_odr_post['m_wearer_std_comb_hkey']){
        $cond["m_wearer_std_comb_hkey"] = $wearer_odr_post['m_wearer_std_comb_hkey'];
    }else{
        $cond["m_wearer_std_comb_hkey"] = $cond["m_wearer_std_comb_hkey"];
    }

    if(!isset($cond["order_req_no"])){
        $cond["order_req_no"] = $wearer_odr_post['order_req_no'];
    }
    // POSTパラメータのセッション格納
    $app->session->set("wearer_odr_post", array(
        'rntl_cont_no' => $cond["rntl_cont_no"],
        'werer_cd' => $cond["werer_cd"],
        'werer_name' => $cond["werer_name"],
        'werer_name_kana' => $cond["werer_name_kana"],
        'cster_emply_cd' => $cond["cster_emply_cd"],
        'sex_kbn' => $cond["sex_kbn"],
        'rntl_sect_cd' => $cond["rntl_sect_cd"],
        'job_type_cd' => $cond["job_type"],
        'ship_to_cd' => $cond["ship_to_cd"],
        'ship_to_brnch_cd' => $cond["ship_to_brnch_cd"],
        'order_reason_kbn' => $order_reason_kbn,
        'order_tran_flg' => $order_tran_flg,
        'wearer_tran_flg' => $wearer_tran_flg,
        'appointment_ymd' => $cond["appointment_ymd"],
        'resfl_ymd' => $cond["resfl_ymd"],
        'm_wearer_std_comb_hkey' => $cond["m_wearer_std_comb_hkey"],
        'order_req_no' => $cond["order_req_no"],
    ));
    $json_list = array();
    $json_list = $cond;
    echo json_encode($json_list);
});
