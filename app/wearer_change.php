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
//    $query_list2 = array();
//    ChromePhp::LOG($cond);

    //---既存着用者基本マスタ情報リスト取得---//
    //企業ID
    array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
    //契約No
    if(!empty($cond['agreement_no'])){
      array_push($query_list, "m_wearer_std.rntl_cont_no = '".$cond['agreement_no']."'");
    }
    //客先社員コード
    if(!empty($cond['cster_emply_cd'])){
      array_push($query_list,"m_wearer_std.cster_emply_cd LIKE '".$cond['cster_emply_cd']."%'");
    }
    //着用者名（漢字）
    if(!empty($cond['werer_name'])){
      array_push($query_list, "m_wearer_std.werer_name LIKE '%".$cond['werer_name']."%'");
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
      array_push($query_list, "m_wearer_std.job_type_cd = '".$cond['job_type']."'");
    }
    // 着用者状況区分(稼働)
    array_push($query_list,"m_wearer_std.werer_sts_kbn = '1'");

    $query = implode(' AND ', $query_list);
/*
    //ソート設定（指定がないので社員番号）
    $q_sort_key = "ws_cster_emply_cd";
    $order = 'asc';
*/
/* UNIONでの実行は保留
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
    $arg_str .= ") UNION ";
    $arg_str .= "(SELECT ";
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
*/
    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= " * ";
  	$arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (m_wearer_std.werer_cd) ";
    $arg_str .= "m_wearer_std.corporate_id as as_corporate_id,";
    $arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
    $arg_str .= "m_wearer_std.rntl_cont_no as as_rntl_cont_no,";
    $arg_str .= "m_wearer_std.rntl_sect_cd as as_rntl_sect_cd,";
    $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std.sex_kbn as as_sex_kbn,";
    $arg_str .= "wst.rntl_sect_name as wst_rntl_sect_name,";
    $arg_str .= "wjt.job_type_name as wjt_job_type_name,";
    $arg_str .= "t_order_tran.order_sts_kbn as as_order_sts_kbn,";
    $arg_str .= "t_order_tran.snd_kbn as as_snd_kbn,";
    $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";
    $arg_str .= "t_order_tran.upd_date as as_order_upd_date";
    //$arg_str .= "os.rntl_sect_name as os_rntl_sect_name,";
    //$arg_str .= "ojt.job_type_name as ojt_job_type_name";
    $arg_str .= " FROM ";
    $arg_str .= "(m_wearer_std INNER JOIN m_section as wst ON m_wearer_std.m_section_comb_hkey = wst.m_section_comb_hkey";
    $arg_str .= " INNER JOIN m_job_type as wjt ON m_wearer_std.m_job_type_comb_hkey = wjt.m_job_type_comb_hkey)";
    $arg_str .= " LEFT JOIN ";
    $arg_str .= "(t_order_tran INNER JOIN m_section as os ON t_order_tran.m_section_comb_hkey = os.m_section_comb_hkey";
    $arg_str .= " INNER JOIN m_job_type as ojt ON t_order_tran.m_job_type_comb_hkey = ojt.m_job_type_comb_hkey)";
    $arg_str .= " ON m_wearer_std.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_cster_emply_cd ASC,as_order_upd_date DESC";
/*
    if (!empty($q_sort_key)) {
        $arg_str .= " ORDER BY ";
        $arg_str .= $q_sort_key." ".$order;

    }
*/
    $m_weare_std = new MWearerStd();
    $results = new Resultset(null, $m_weare_std, $m_weare_std->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    $paginator_model = new PaginatorModel(
        array(
            "data"  => $results,
            "limit" => $page['records_per_page'],
            "page" => $page['page_number']
        )
    );

    $list = array();
    $all_list = array();
    $json_list = array();

    if(!empty($results_cnt)){
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;
        //ChromePhp::LOG($results);

        foreach($results as $result) {
          //---着用者基本マスタトラン情報の既存データ重複参照---//
          $query_list = array();
          // 企業ID
          array_push($query_list, "m_wearer_std_tran.corporate_id = '".$result->as_corporate_id."'");
          // レンタル契約No
          array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$result->as_rntl_cont_no."'");
          // 着用者コード
          array_push($query_list,"m_wearer_std_tran.werer_cd = '".$result->as_werer_cd."'");

          $query = implode(' AND ', $query_list);

          $arg_str = "";
          $arg_str = "SELECT ";
          $arg_str .= "m_wearer_std_tran.corporate_id as as_corporate_id,";
          $arg_str .= "m_wearer_std_tran.werer_cd as as_werer_cd,";
          $arg_str .= "m_wearer_std_tran.rntl_cont_no as as_rntl_cont_no,";
          $arg_str .= "m_wearer_std_tran.rntl_sect_cd as as_rntl_sect_cd,";
          $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
          $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
          $arg_str .= "m_wearer_std_tran.sex_kbn as as_sex_kbn,";
          $arg_str .= "wst.rntl_sect_name as wst_rntl_sect_name,";
          $arg_str .= "wjt.job_type_name as wjt_job_type_name,";
          $arg_str .= "t_order_tran.order_sts_kbn as as_order_sts_kbn,";
          $arg_str .= "t_order_tran.snd_kbn as as_snd_kbn,";
          $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn";
          //$arg_str .= "os.rntl_sect_name as os_rntl_sect_name,";
          //$arg_str .= "ojt.job_type_name as ojt_job_type_name";
          $arg_str .= " FROM ";
          $arg_str .= "(m_wearer_std_tran INNER JOIN m_section as wst ON m_wearer_std_tran.m_section_comb_hkey = wst.m_section_comb_hkey";
          $arg_str .= " INNER JOIN m_job_type as wjt ON m_wearer_std_tran.m_job_type_comb_hkey = wjt.m_job_type_comb_hkey)";
          $arg_str .= " LEFT JOIN ";
          $arg_str .= "(t_order_tran INNER JOIN m_section as os ON t_order_tran.m_section_comb_hkey = os.m_section_comb_hkey";
          $arg_str .= " INNER JOIN m_job_type as ojt ON t_order_tran.m_job_type_comb_hkey = ojt.m_job_type_comb_hkey)";
          $arg_str .= " ON m_wearer_std_tran.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
          $arg_str .= " WHERE ";
          $arg_str .= $query;
          $arg_str .= " ORDER BY m_wearer_std_tran.upd_date DESC";

          $m_weare_std_tran = new MWearerStdTran();
          $tran_results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
          $tran_result_obj = (array)$tran_results;
          $tran_results_cnt = $tran_result_obj["\0*\0_count"];

          // 着用者基本マスタトラン情報に重複データがある場合、優先させて着用者基本マスタ情報リストを上書きする
          if (!empty($tran_results_cnt)) {
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
              $result->as_werer_cd = $tran_result->as_werer_cd;
              $result->as_cster_emply_cd = $tran_result->as_cster_emply_cd;
              $result->as_werer_name = $tran_result->as_werer_name;
              $result->as_sex_kbn = $tran_result->as_sex_kbn;
              $result->wst_rntl_sect_name = $tran_result->wst_rntl_sect_name;
              $result->wjt_job_type_name = $tran_result->wjt_job_type_name;
              $result->as_order_sts_kbn = $tran_result->as_order_sts_kbn;
              $result->as_snd_kbn = $tran_result->as_snd_kbn;
              $result->as_order_reason_kbn = $tran_result->as_order_reason_kbn;
            }
          }
          // レンタル契約No
          $list['rntl_cont_no'] = $result->as_rntl_cont_no;
          // レンタル部門コード
          $list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
          // 着用者コード
          $list['werer_cd'] = $result->as_werer_cd;
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
          if (isset($result->as_snd_kbn)) {
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
          //職種変更または異動ボタンの生成
          if (
            $result->as_order_sts_kbn == '1'
            && $result->as_order_reason_kbn !== '4'
            && $result->as_order_reason_kbn !== '8'
            && $result->as_order_reason_kbn !== '9'
            && $result->as_order_reason_kbn !== '11')
          {
            //パターンA： 発注情報トラン．発注状況区分 = 貸与 かつ、発注情報トラン．理由区分 = 職種変更または異動のデータが無い場合、
            //ボタンの文言は「職種変更または異動」で表示する。
            $list['wearer_change_button'] = '職種変更または異動';
          } elseif (
            $result->as_order_sts_kbn == '1'
            && ($result->as_order_reason_kbn == '4' || $result->as_order_reason_kbn == '8' || $result->as_order_reason_kbn == '9' || $result->as_order_reason_kbn == '11')
            && $result->as_snd_kbn == '0')
          {
            //パターンB： 発注情報トラン．発注状況区分 = 貸与 かつ、発注情報トラン．理由区分 = 職種変更または異動のデータがある場合、かつ、
            //発注情報トラン．送信区分 = 未送信の場合、ボタンの文言は「職種変更または異動[済]」で表示する。
            $list['wearer_change_button'] = "職種変更または異動";
            $list['wearer_change_red'] = "[済]";
          } elseif (
            $result->as_order_sts_kbn == '2'
            && ($result->as_order_reason_kbn == '4' || $result->as_order_reason_kbn == '8' || $result->as_order_reason_kbn == '9' || $result->as_order_reason_kbn == '11')
            && $result->as_snd_kbn == '1')
          {
            //パターンC： 発注情報トラン．発注状況区分 = 貸与 かつ、発注情報トラン．理由区分 = 職種変更または異動のデータがある場合、かつ、
            //発注情報トラン．送信区分 = 送信済の場合、ボタンの文言は「職種変更または異動[済]」で非活性表示する。
            $list['wearer_change_button'] = "職種変更または異動";
            $list['wearer_change_red'] = "[済]";
            $list['disabled'] = "disabled";
          } elseif (
            $result->as_order_sts_kbn !== '1'
            || ($result->as_order_sts_kbn == '1' && ($result->as_order_reason_kbn !== '4' && $result->as_order_reason_kbn !== '8' && $result->as_order_reason_kbn !== '9' && $result->as_order_reason_kbn !== '11'))
            && $result->as_snd_kbn == '1')
          {
            //パターンD： 発注情報トラン．発注状況区分 = 貸与以外、もしくは、発注情報トラン．発注状況区分 = 貸与 かつ、発注情報トラン．理由区分 = 職種変更または異動以外のデータがある場合、かつ、
            //その発注の送信区分 = 送信済の場合、ボタンの文言は「職種変更または異動」で非活性表示する。
            $list['wearer_end_button'] = "職種変更または異動";
            $list['disabled'] = "disabled";
          }

          //返却伝票ダウンロード生成
          if (
            ($result->as_order_sts_kbn == '1'
            && ($result->as_order_reason_kbn == '4' || $result->as_order_reason_kbn == '8' || $result->as_order_reason_kbn == '9' || $result->as_order_reason_kbn == '11')
            && $result->as_snd_kbn == '0') ||
            ($result->as_order_sts_kbn == '2'
            && ($result->as_order_reason_kbn == '4' || $result->as_order_reason_kbn == '8' || $result->as_order_reason_kbn == '9' || $result->as_order_reason_kbn == '11')
            && $result->as_snd_kbn == '1'))
          {
            //上記パターンBかCの場合表示
            $list['return_reciept_button'] = "返却伝票ダウンロード";
          }

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
