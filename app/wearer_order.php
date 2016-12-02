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
$app->post('/reason_kbn_order', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_odr_post = $app->session->get("wearer_odr_post");

    //--発注管理単位取得--//
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
    array_push($query_list, "job_type_cd = '".$wearer_odr_post['job_type_cd']."'");
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
        $order_control_unit = $result->order_control_unit;
      }
    }

    //--理由区分リスト取得--//
    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    array_push($query_list, "cls_cd = '002'");
    array_push($query_list, "relation_cls_cd = '001'");
    array_push($query_list, "relation_gen_cd = '1'");
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
            if($result->gen_cd!='03'){
                $list['reason_kbn'] = $result->gen_cd;
                $list['reason_kbn_name'] = $result->gen_name;
                $list['selected'] = '';
                // 発注情報トランフラグ有の場合は初期選択状態版を生成
                if ($list['reason_kbn'] == $wearer_odr_post['order_reason_kbn']) {
                    $list['selected'] = 'selected';
                }
                array_push($all_list, $list);
            }
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
$app->post('/section_order', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    // 前画面セッション取得
    $wearer_odr_post = $app->session->get("wearer_odr_post");
    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_sect_cd = '".$wearer_odr_post['rntl_sect_cd']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
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
        if ($wearer_odr_post['order_req_no']) {
          if ($list['rntl_sect_cd'] == $wearer_odr_post['rntl_sect_cd']) {
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

    $json_list['m_section_list'] = $all_list;
    echo json_encode($json_list);
});
/**
 * 発注入力
 * 入力項目：社員コード、着用者名、着用者名（かな）
 */
$app->post('/wearer_order_info', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_odr_post = $app->session->get("wearer_odr_post");

    $json_list['rntl_cont_no'] = $wearer_odr_post['rntl_cont_no'];
    $json_list['werer_cd'] = $wearer_odr_post['werer_cd'];
    $json_list['cster_emply_cd'] = $wearer_odr_post['cster_emply_cd'];
    $json_list['sex_kbn'] = $wearer_odr_post['sex_kbn'];
    $json_list['rntl_sect_cd'] = $wearer_odr_post['rntl_sect_cd'];
    $json_list['job_type_cd'] = $wearer_odr_post['job_type_cd'];
    $json_list['order_reason_kbn'] = $wearer_odr_post['order_reason_kbn'];
    $json_list['order_tran_flg'] = $wearer_odr_post['order_tran_flg'];
    $json_list['wearer_tran_flg'] = $wearer_odr_post['wearer_tran_flg'];
    $json_list['comment'] = $wearer_odr_post['comment'];

    //出荷先リスト
    //--出荷先選択ボックス生成--//
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
    //一件目に「支店店舗と同じ:部門マスタ.標準出荷先コード:部門マスタ.標準出荷先支店コード」という選択肢とセレクトボックスを表示。
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

    $query_list = array();
    $list = array();
    $all_list = array();
    if($wearer_odr_post){
        if(!$wearer_odr_post['cster_emply_cd']){
            $cster_emply_cd = '-';
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
            $list['appointment_ymd'] = '-';
        }
        array_push($all_list, $list);
        $json_list['wearer_info'] = $all_list;

    } elseif ($wearer_odr_post['wearer_tran_flg'] == '1') {
        //--着用者基本マスタトラン有の場合--//
        array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
        array_push($query_list,"m_wearer_std_tran.werer_cd = '".$wearer_odr_post['werer_cd']."'");
        array_push($query_list,"m_wearer_std_tran.cster_emply_cd = '".$wearer_odr_post['cster_emply_cd']."'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "SELECT ";
        $arg_str .= "m_wearer_std_tran.cster_emply_cd as as_cster_emply_cd,";
        $arg_str .= "m_wearer_std_tran.werer_name as as_werer_name,";
        $arg_str .= "m_wearer_std_tran.werer_name_kana as as_werer_name_kana,";
        $arg_str .= "m_wearer_std_tran.appointment_ymd as as_appointment_ymd";
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
                if($result->as_cster_emply_cd){
                    $list['cster_emply_cd'] = $result->as_cster_emply_cd;
                }else{
                    $list['cster_emply_cd'] = '-';
                }
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
                if($result->as_cster_emply_cd){
                    $list['cster_emply_cd'] = $result->as_cster_emply_cd;
                }else{
                    $list['cster_emply_cd'] = '-';
                }
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
            }

            array_push($all_list, $list);
        }

        $json_list['wearer_info'] = $all_list;
    }
    $param_list = '';
/*
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
    $json_list['selected_job'] = $wearer_odr_post['job_type_cd'];
    $json_list['order_req_no'] = $wearer_odr_post['order_req_no'];
*/
    echo json_encode($json_list);
});

/**
 * 発注入力
 * 入力項目：発注送信一覧
 */
$app->post('/wearer_order_list', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    // 前画面セッション取得
    $wearer_odr_post = $app->session->get("wearer_odr_post");

    // フロントパラメータ取得
    $cond = $params['data'];
    //貸与パターン変更時
    if(isset($cond['job_type'])){
        $wearer_odr_post['job_type_cd'] = $cond['job_type'];
    }
    //発注情報トランを参照し、「発注商品一覧」を生成する。
    $json_list = array();
    $all_list = array();
    $query_list = array();
    // 着用者基本マスタトラン．企業ID　＝　　ログインしているアカウントの企業ID　AND
    array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
    //着用者基本マスタトラン．着用者コード　＝　　「①着用者入力」画面の表示の際に使用した着用者コード　AND
    array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_odr_post['werer_cd']."'");
    //着用者基本マスタトラン．レンタル契約No.　＝　前画面で選択された契約No. AND
    array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
    //着用者基本マスタトラン．レンタル部門コード　＝　前画面で選択された拠点の部門コード AND
    array_push($query_list,"m_wearer_std_tran.rntl_sect_cd = '".$wearer_odr_post['rntl_sect_cd']."'");
    //着用者基本マスタトラン．職種コード　＝　前画面で選択された貸与パターンの職種コード AND
    array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_odr_post['job_type_cd']."'");

    $query = implode(' AND ', $query_list);

    //---SQLクエリー実行---//
    $arg_str = "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (t_order_tran.item_cd,t_order_tran.color_cd) ";
    $arg_str .= "m_input_item.job_type_item_name as as_item_name,";
    $arg_str .= "t_order_tran.item_cd as as_item_cd,";
    $arg_str .= "t_order_tran.color_cd as as_color_cd,";
    $arg_str .= "m_input_item.std_input_qty as as_std_input_qty,";
    $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
    $arg_str .= "m_input_item.size_two_cd as as_size_two_cd,";
    $arg_str .= "t_order_tran.job_type_cd as as_job_type_cd,";
    $arg_str .= "m_input_item.job_type_item_cd as as_job_type_item_cd,";
    $arg_str .= "t_order_tran.size_cd as as_size_cd_tran,";
    $arg_str .= "t_order_tran.order_qty as as_order_qty_tran";
    $arg_str .= " FROM m_wearer_std_tran INNER JOIN t_order_tran";
    $arg_str .= " ON m_wearer_std_tran.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
    $arg_str .= " AND m_wearer_std_tran.job_type_cd = t_order_tran.job_type_cd";
    $arg_str .= " INNER JOIN m_section";
    $arg_str .= " ON m_wearer_std_tran.m_section_comb_hkey = m_section.m_section_comb_hkey";
    $arg_str .= " INNER JOIN m_job_type";
    $arg_str .= " ON m_wearer_std_tran.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
    $arg_str .= " INNER JOIN m_input_item";
    $arg_str .= " ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey";
    $arg_str .= " AND m_input_item.job_type_item_cd = t_order_tran.job_type_item_cd";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " GROUP BY as_item_name,as_item_cd,as_color_cd, as_std_input_qty,
        as_input_item_name,as_size_two_cd,as_input_item_name,as_size_cd_tran,as_order_qty_tran,as_job_type_cd,as_job_type_item_cd";
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_item_cd,as_color_cd ASC";
    $m_weare_std_tran= new MWearerStdTran();
    $results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    $t_order_tran_flg = false;
    if (!empty($results_cnt)) {
        $paginator_model = new PaginatorModel(
            array(
                "data" => $results,
                "limit" => $results_cnt,
                "page" => 1
            )
        );

        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;
        $t_order_tran_flg = true;
    }else{
        //発注情報トランにデータが存在しない場合
        //職種マスタを参照し、「発注商品一覧」を生成する。
        $query_list = array();
        // 職種マスタ．企業ID　＝　　ログインしているアカウントの企業ID　AND
        array_push($query_list, "m_job_type.corporate_id = '".$auth['corporate_id']."'");
        //職種マスタ．レンタル契約No.　＝　前画面で選択された契約No. AND
        array_push($query_list, "m_job_type.rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
        //職種マスタ．職種コード　＝　前画面で選択された貸与パターンの職種コード AND
        array_push($query_list, "m_job_type.job_type_cd = '".$wearer_odr_post['job_type_cd']."'");

        $query = implode(' AND ', $query_list);

        //---SQLクエリー実行---//
        $arg_str = "SELECT ";
        $arg_str .= "*";
        $arg_str .= " FROM ";
        $arg_str .= "(SELECT distinct on (m_input_item.item_cd,m_input_item.color_cd) ";
        $arg_str .= "m_input_item.job_type_item_name as as_item_name,";
        $arg_str .= "m_input_item.item_cd as as_item_cd,";
        $arg_str .= "m_input_item.color_cd as as_color_cd,";
        $arg_str .= "m_input_item.std_input_qty as as_std_input_qty,";
        $arg_str .= "m_input_item.size_two_cd as as_size_two_cd,";
        $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
        $arg_str .= "m_input_item.job_type_cd as as_job_type_cd,";
        $arg_str .= "m_input_item.job_type_item_cd as as_job_type_item_cd";
        $arg_str .= " FROM m_job_type";
        $arg_str .= " INNER JOIN m_input_item";
        $arg_str .= " ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= " GROUP BY as_item_name, as_item_cd,as_color_cd, as_std_input_qty,
        as_input_item_name,as_size_two_cd,as_input_item_name,as_job_type_cd,as_job_type_item_cd";
        $arg_str .= ") as distinct_table";
        $arg_str .= " ORDER BY as_item_cd,as_color_cd ASC";

        $m_job_type = new MJobType();
        $results = new Resultset(null, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
        if ($results_cnt > 0) {
          $paginator_model = new PaginatorModel(
              array(
                  "data" => $results,
                  "limit" => $results_cnt,
                  "page" => 1
              )
          );

          $paginator = $paginator_model->getPaginate();
          $results = $paginator->items;
        }
    }
    $arr_cnt = 0;
    $list_cnt = 1;
    $add_item = $wearer_odr_post['add_item'];
    foreach ($results as $result) {
        $list = array();
        // name属性用カウント値
        $list["arr_num"] = $arr_cnt++;
        // No
        $list["list_no"] = $list_cnt++;
        //商品名
        $list['item_name'] = $result->as_item_name;
        // 商品コード
        $list['item_cd'] = $result->as_item_cd;
        // 色コード
        $list['color_cd'] = $result->as_color_cd;
        // 標準投入数
        $list['std_input_qty'] = $result->as_std_input_qty;
        // 投入商品名
        $list['input_item_name'] = $result->as_input_item_name;
        // 職種アイテムコード
        $list["job_type_item_cd"] = $result->as_job_type_item_cd;
        // 職種コード
        $list["job_type_cd"] = $result->as_job_type_cd;
        // 部門コード
        $list["rntl_sect_cd"] = $wearer_odr_post['rntl_cont_no'];
        //※着用者の職種マスタ.職種コードに紐づく投入商品マスタの職種アイテムコード単位で単一or複数判断
        $query_list = array();
        array_push($query_list, "m_job_type.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "m_job_type.rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
        array_push($query_list, "m_job_type.job_type_cd = '".$wearer_odr_post['job_type_cd']."'");
        array_push($query_list, "m_input_item.job_type_cd = '".$wearer_odr_post['job_type_cd']."'");
        array_push($query_list, "m_input_item.item_cd = '".$result->as_item_cd."'");
//        array_push($query_list, "m_input_item.color_cd = '".$result->as_color_cd."'");
//        array_push($query_list, "m_input_item.size_two_cd = '".$result->as_size_two_cd."'");
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

        // 発注商品一覧」の入力項目「サイズ」作成
        //サイズコードセレクトボックス作成
        $m_item_query_list = array();
        // 商品コード　AND
        array_push($m_item_query_list,"item_cd = '".$result->as_item_cd."'");
        // 色コード
//        array_push($m_item_query_list,"color_cd = '".$result->as_color_cd."'");
        $query = implode(' AND ', $m_item_query_list);
        //--- クエリー実行・取得 ---//
        $m_item_results = MItem::find(array(
            'conditions' => $query,
            "columns" => "size_cd"
        ));
        $size_list = array();
        $size_list_to = array();
//        ChromePhp::LOG($add_item[$arr_cnt-1]);
//        ||$add_item[$arr_cnt-1]['add_size_cd']==$m_item_result->size_cd
        if($add_item){
            foreach ($m_item_results as $m_item_result){
                if($add_item[$arr_cnt-1]['add_size_cd'] == $m_item_result->size_cd){
                    $size_list['selected'] = 'selected';
                }else{
                    $size_list['selected'] = '';
                }
                $size_list['size_cd'] = $m_item_result->size_cd;
                array_push($size_list_to , $size_list);
            }
            // 発注数(単一選択=入力不可、複数選択=入力可)
            //「単一選択」の場合は、投入商品マスタ．標準投入数（入力不可）。
            if ($list["choice_type"] == "1") {
                $list["order_num"] = $add_item[$arr_cnt - 1]['add_std_input_qty'];
                $list["order_num_disable"] = "disabled";
            } else {
                $list["order_num"] = $add_item[$arr_cnt - 1]['add_order_num'];
                $list["order_num_disable"] = "";
            }
        }else{
            foreach ($m_item_results as $m_item_result){
                if((isset($result->as_size_cd_tran)&&$result->as_size_cd_tran == $m_item_result->size_cd)){
                    $size_list['selected'] = 'selected';
                }else{
                    $size_list['selected'] = '';
                }
                $size_list['size_cd'] = $m_item_result->size_cd;
                array_push($size_list_to , $size_list);
            }
            // 発注数(単一選択=入力不可、複数選択=入力可)
            //「単一選択」の場合は、投入商品マスタ．標準投入数（入力不可）。
            if ($list["choice_type"] == "1") {
                $list["order_num"] = $result->as_std_input_qty;
                $list["order_num_disable"] = "disabled";
            } else {
                $list["order_num_disable"] = "";
            }
        }
        // サイズコードセレクトボックス
        $list['size_cd_list'] = $size_list_to;
        // 商品-色
        $list["item_and_color"] = $list['item_cd']."-".$list['color_cd'];
        array_push($all_list,$list);
    }
    // 発注総枚数
    $list["order_count"] = 0;
    $cnt = 0;
    if (!empty($all_list)) {
        $multiples = array();
        foreach ($all_list as $add_map) {
            if ($add_map["choice_type"] == "2") {
                if (in_array($add_map["item_cd"], $multiples)) {
                    continue;
                } else {
                    $list["order_count"] += $add_map["std_input_qty"];
                    array_push($multiples, $add_map["item_cd"]);
                }
            } else {
                $list["order_count"] += $add_map["std_input_qty"];
            }
        }
    }
    // 発注枚数ここまで
    $json_list['order_count'] = $list["order_count"];
    $json_list["tran_flg"] = $t_order_tran_flg;
    $json_list["add_list_cnt"] = count($all_list);
    $json_list['list'] = $all_list;
    echo json_encode($json_list);

});

/*
 *  保存（後で送信）or 発注送信
 */
$app->post('/wearer_order_insert', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');

    // 前画面セッション取得
    $wearer_odr_post = $app->session->get("wearer_odr_post");

    $cond = $params['cond'];
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
    if (!empty($cond["comment"])) {
        if (mb_strlen($cond["comment"]) > 50) {
            array_push($error_list,'コメント欄は50文字以内で入力してください。');
            $json_list['error_msg'] = $error_list;
            $json_list["error_code"] = "1";
        }
    }
    $add_item_input = $params["add_item"];
    $order_count = 0;
    // 貸与されるアイテム
    foreach ($add_item_input as $add_item_input_map) {
        // 発注枚数フォーマットチェック
        if (empty($add_item_input_map["add_order_num_disable"])) {
            if (!$add_item_input_map["add_order_num"]) {
                array_push($error_list,'発注枚数を入力してください。');
                $json_list["error_code"] = "1";
            }
        }
        if (empty($add_item_input_map["add_order_num_disable"])) {
            if (!ctype_digit(strval($add_item_input_map["add_order_num"]))) {
                array_push($error_list,'発注枚数には半角数字を入力してください。');
                $json_list["error_code"] = "1";
            }
        }
        $order_count = intval($order_count) + intval($add_item_input_map["add_order_num"]);
        if (intval($cond["order_count"])<$order_count) {
            array_push($error_list,'発注可能枚数を超えています。');
            $json_list["error_code"] = "1";
        }
        // サイズチェック
        if (!$add_item_input_map["add_size_cd"]) {
            array_push($error_list,'サイズを入力してください。');
            $json_list["error_code"] = "1";
        }
    }
    //DB登録
    if($json_list["error_code"]=="1"){
        $json_list['error_msg'] = $error_list;
        echo json_encode($json_list);
        return true;
    }
    $transaction = $app->transactionManager->get();


    // トランザクション開始
    $t_order_tran = new TOrderTran();
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query('begin'));

    try {

        //着用者基本情報トラン
        $m_wearer_std_tran = new MWearerStdTran();
        $now = date('Y/m/d H:i:s.sss');
        $no_flg = false;
        if($wearer_odr_post['m_wearer_std_comb_hkey']){
            $m_wearer_std_tran = MWearerStdTran::find(array(
                'conditions' => 'm_wearer_std_comb_hkey = '."'".$wearer_odr_post['m_wearer_std_comb_hkey']."'"
            ));
            $m_wearer_std_tran_one = $m_wearer_std_tran[0];
            $shin_order_req_no = $m_wearer_std_tran_one->getOrderReqNo();//発注No
            if($shin_order_req_no){
                $no_flg = true;
            }
        }
        if(!$no_flg){
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
                        "data" => $results,
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
                $arg_str = "SELECT SETVAL('t_order_seq'," . $order_no_seq . ")";
                $t_order_tran = new TOrderTran();
                $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
                $result_obj = (array)$results;
                $results_cnt = $result_obj["\0*\0_count"];
                if (!empty($results_cnt)) {
                    $paginator_model = new PaginatorModel(
                        array(
                            "data" => $results,
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
        }
        //貸与パターン
        $query_list = array();
        // 職種マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
        array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
        // 職種マスタ．レンタル契約No.　＝　画面で選択されている契約No.
        array_push($query_list,"rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
        $deli_job = explode(',',$cond['job_type']);
        // 職種マスタ．レンタル部門コード　＝　画面で選択されている貸与パターン
        array_push($query_list,"job_type_cd = '".$cond['job_type']."'");

        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $query_list);
        //--- クエリー実行・取得 ---//
        $m_job_type = MJobType::find(array(
            'conditions' => $query
        ));
        //拠点のマスタチェック
        $query_list = array();
        // 部門マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
        array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
        // 部門マスタ．レンタル契約No.　＝　画面で選択されている契約No.
        array_push($query_list,"rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
        // 部門マスタ．レンタル部門コード　＝　画面で選択されている拠点
        array_push($query_list,"rntl_sect_cd = '".$cond['rntl_sect_cd']."'");

        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $query_list);
        //--- クエリー実行・取得 ---//
        $m_section = MSection::find(array(
            'conditions' => $query
        ));
        $create_flg = false;
        //--- クエリー実行・取得 ---//
        if($wearer_odr_post['m_wearer_std_comb_hkey']&&count($m_wearer_std_tran)>0){
            $m_wearer_std_tran = $m_wearer_std_tran[0];
            //データを引き継いでいる場合
            $werer_cd = $wearer_odr_post['werer_cd'];
            $m_wearer_std_comb_hkey = $wearer_odr_post['m_wearer_std_comb_hkey'];
            $corporate_id = $auth['corporate_id']; //企業ID
            $rntl_sect_cd = $cond['rntl_sect_cd']; //レンタル部門コード
            $cster_emply_cd = $wearer_odr_post['cster_emply_cd'];//客先社員コード
            $werer_name = $wearer_odr_post['werer_name'];//着用者名（漢字）
            $werer_name_kana = $wearer_odr_post['werer_name_kana']; //着用者名（カナ）
            $sex_kbn = $wearer_odr_post['sex_kbn'];//性別区分
            $appointment_ymd = date("Ymd", strtotime($wearer_odr_post['appointment_ymd']));//発令日
            $resfl_ymd = date("Ymd", strtotime($wearer_odr_post['resfl_ymd']));//着用開始日
            $ship_to_cd = $wearer_odr_post['ship_to_cd']; //出荷先コード
            $ship_to_brnch_cd = $wearer_odr_post['ship_to_brnch_cd']; //出荷先支店コード
            $web_upd_date = $m_wearer_std_tran->web_upd_date;//WEB更新日付
            $order_req_no  = $shin_order_req_no;//発注No
            $snd_date = $m_wearer_std_tran->snd_date;//送信日時
            $rgst_date = $m_wearer_std_tran->rgst_date;//送信日時
            $rgst_user_id = $m_wearer_std_tran->rgst_user_id;//登録ユーザーID
            $create_flg = true;
        }else{
            //新規登録の場合
            $m_wearer_std_tran = new MWearerStdTran();
            $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("select nextval('werer_cd_seq')"));
            $werer_cd = str_pad($results[0]->nextval, 6, '0', STR_PAD_LEFT); //着用者コード
            $corporate_id = $auth['corporate_id']; //企業ID
            $m_wearer_std_comb_hkey = md5($auth['corporate_id']. '-' . str_pad($results[0]->nextval, 10, '0', STR_PAD_LEFT). '-' . $wearer_odr_post['rntl_cont_no']. '-' . $cond['rntl_sect_cd']. '-' . $cond['job_type']);
            $cster_emply_cd = $wearer_odr_post['cster_emply_cd'];//客先社員コード
            $werer_name = $wearer_odr_post['werer_name'];//着用者名（漢字）
            $werer_name_kana = $wearer_odr_post['werer_name_kana']; //着用者名（カナ）
            $sex_kbn = $wearer_odr_post['sex_kbn'];//性別区分
            $appointment_ymd = date("Ymd", strtotime($wearer_odr_post['appointment_ymd']));//発令日
            $resfl_ymd = date("Ymd", strtotime($wearer_odr_post['resfl_ymd']));//着用開始日
            $ship_to_cd = $wearer_odr_post['ship_to_cd']; //出荷先コード
            $ship_to_brnch_cd = $wearer_odr_post['ship_to_brnch_cd']; //出荷先支店コード
            $web_upd_date = $now;//WEB更新日付
            $snd_date  = $now;//送信日時
            $rgst_date  = $now;//登録日時
            $rgst_user_id = $auth['accnt_no'];//登録ユーザーID
            $order_req_no  = $shin_order_req_no;//発注No
            $create_flg = false;
        }
        $werer_sts_kbn  = '7';//着用者状況区分
        $del_kbn ='0';//削除区分
        $rntl_cont_no_bef = ''; //レンタル契約No.（前）
        $rntl_sect_cd_bef = '';//レンタル部門コード（前）
        $job_type_cd_bef = ''; //職種コード（前）
        $werer_sts_kbn_bef = ''; //着用者状況区分（前）
        $resfl_ymd_bef = ''; //異動日（前）
        $order_sts_kbn = '1'; //発注状況区分 汎用コード：貸与
        $upd_kbn = '1';//更新区分　汎用コード：web発注システム（新規登録）
        // 送信区分
        if($params['snd_kbn']=='0'){
            //未送信
            $snd_kbn = '0';//送信区分
        }else{
            //送信
            $snd_kbn = '1';//送信区分
        }
        $rntl_cont_no = $wearer_odr_post['rntl_cont_no']; //レンタル契約No.
        $rntl_sect_cd = $cond['rntl_sect_cd']; //レンタル部門コード
        $job_type_cd = $cond['job_type'];//職種コード
        $upd_date  = $now;//更新日時
        $upd_user_id = $auth['accnt_no'];//更新ユーザーID
        $upd_pg_id = $auth['accnt_no'];//更新プログラムID
        $m_job_type_comb_hkey = $m_job_type[0]->m_job_type_comb_hkey;//職種マスタ_統合ハッシュキー
        $m_section_comb_hkey = $m_section[0]->m_section_comb_hkey;//部門マスタ_統合ハッシュキー

        // 着用者登録
        if ($wearer_odr_post) {
            // 現発注Noの発注情報トランをクリーン
//            if ($wearer_odr_post['order_tran_flg'] == '1') {
            $query_list = array();
            array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
            array_push($query_list, "m_wearer_std_comb_hkey = '" . $wearer_odr_post['m_wearer_std_comb_hkey'] . "'");
            array_push($query_list, "werer_sts_kbn = '7'");
            array_push($query_list, "order_sts_kbn = '1'");
            $query = implode(' AND ', $query_list);
            $arg_str = "";
            $arg_str = "DELETE FROM ";
            $arg_str .= "m_wearer_std_tran";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $m_wearer_std = new MWearerStdTran();
            $results = new Resultset(NULL, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
        }
        //更新もしくは新規追加
        $calum_list = array();
        $values_list = array();

        array_push($calum_list, "m_wearer_std_comb_hkey");
        array_push($values_list, "'" . $m_wearer_std_comb_hkey . "'");
        // 企業ID
        array_push($calum_list, "corporate_id");
        array_push($values_list, "'" . $corporate_id . "'");
        // 発注依頼No.
        array_push($calum_list, "order_req_no");
        array_push($values_list, "'" . $order_req_no . "'");
        // レンタル契約No
        array_push($calum_list, "rntl_cont_no");
        array_push($values_list, "'" . $rntl_cont_no . "'");
        // 着用者コード
        array_push($calum_list, "werer_cd");
        array_push($values_list, "'" . $werer_cd . "'");
        // レンタル契約No.（前）
        array_push($calum_list, "rntl_cont_no_bef");
        array_push($values_list, "'" . $rntl_cont_no_bef . "'");
        // レンタル部門コード（前）
        array_push($calum_list, "rntl_sect_cd_bef");
        array_push($values_list, "'" . $rntl_sect_cd_bef . "'");
        // 職種コード（前）
        array_push($calum_list, "job_type_cd_bef");
        array_push($values_list, "'" . $job_type_cd_bef . "'");
        // 着用者状況区分（前）
        array_push($calum_list, "werer_sts_kbn_bef");
        array_push($values_list, "'" . $werer_sts_kbn_bef . "'");
        // 異動日（前）
        array_push($calum_list, "resfl_ymd_bef");
        array_push($values_list, "'" . $resfl_ymd_bef . "'");
        // 発注状況区分 汎用コード：貸与
        array_push($calum_list, "order_sts_kbn");
        array_push($values_list, "'" . $order_sts_kbn . "'");
        // 更新区分　汎用コード：web発注システム（新規登録）
        array_push($calum_list, "upd_kbn");
        array_push($values_list, "'" . $upd_kbn . "'");
        // WEB更新日付
        array_push($calum_list, "web_upd_date");
        array_push($values_list, "'" . $web_upd_date . "'");
        // 送信区分
        array_push($calum_list, "snd_kbn");
        array_push($values_list, "'" . $snd_kbn . "'");
        // 送信日時
        array_push($calum_list, "snd_date");
        array_push($values_list, "'" . $snd_date . "'");
        // 削除区分
        array_push($calum_list, "del_kbn");
        array_push($values_list, "'" . $del_kbn . "'");
        // 登録日時
        array_push($calum_list, "rgst_date");
        array_push($values_list, "'" . $rgst_date . "'");
        // 登録ユーザーID
        array_push($calum_list, "rgst_user_id");
        array_push($values_list, "'" . $rgst_user_id . "'");
        // レンタル部門コード
        array_push($calum_list, "rntl_sect_cd");
        array_push($values_list, "'" . $rntl_sect_cd . "'");
        // 職種コード
        array_push($calum_list, "job_type_cd");
        array_push($values_list, "'" . $job_type_cd . "'");
        // 客先社員コード
        array_push($calum_list, "cster_emply_cd");
        array_push($values_list, "'" . $cster_emply_cd . "'");
        // 着用者名（漢字）
        array_push($calum_list, "werer_name");
        array_push($values_list, "'" . $werer_name . "'");
        // 着用者名（カナ）
        array_push($calum_list, "werer_name_kana");
        array_push($values_list, "'" . $werer_name_kana . "'");
        // 性別区分
        array_push($calum_list, "sex_kbn");
        array_push($values_list, "'" . $sex_kbn . "'");
        // 着用者状況区分
        array_push($calum_list, "werer_sts_kbn");
        array_push($values_list, "'" . $werer_sts_kbn . "'");
        // 発令日
        array_push($calum_list, "appointment_ymd");
        array_push($values_list, "'" . $appointment_ymd . "'");
        // 着用開始日
        array_push($calum_list, "resfl_ymd");
        array_push($values_list, "'" . $resfl_ymd . "'");
        // 出荷先コード
        array_push($calum_list, "ship_to_cd");
        array_push($values_list, "'" . $ship_to_cd . "'");
        // 出荷先支店コード
        array_push($calum_list, "ship_to_brnch_cd");
        array_push($values_list, "'" . $ship_to_brnch_cd . "'");
        // 更新日時
        array_push($calum_list, "upd_date");
        array_push($values_list, "'" . $upd_date . "'");
        // 更新ユーザーID
        array_push($calum_list, "upd_user_id");
        array_push($values_list, "'" . $upd_user_id . "'");
        // 更新プログラムID
        array_push($calum_list, "upd_pg_id");
        array_push($values_list, "'" . $upd_pg_id . "'");
        // 職種マスタ_統合ハッシュキー
        array_push($calum_list, "m_job_type_comb_hkey");
        array_push($values_list, "'" . $m_job_type_comb_hkey . "'");
        // 部門マスタ_統合ハッシュキー
        array_push($calum_list, "m_section_comb_hkey");
        array_push($values_list, "'" . $m_section_comb_hkey . "'");

        $calum_query = implode(',', $calum_list);
        $values_query = implode(',', $values_list);

        $arg_str = "";
        $arg_str = "INSERT INTO m_wearer_std_tran";
        $arg_str .= "(" . $calum_query . ")";
        $arg_str .= " VALUES ";
        $arg_str .= "(" . $values_query . ")";
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));

        //--発注情報トラン登録--//
        $cnt = 1;
//        $add_item_input = $params["add_item"];

        // 着用アイテム内容登録
        if (!empty($add_item_input)) {
            // 現発注Noの発注情報トランをクリーン
//            if ($wearer_odr_post['order_tran_flg'] == '1') {
            $query_list = array();
            array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
            array_push($query_list, "m_wearer_std_comb_hkey = '".$m_wearer_std_comb_hkey."'");
            array_push($query_list, "werer_sts_kbn = '7'");
            array_push($query_list, "order_sts_kbn = '1'");
            $query = implode(' AND ', $query_list);
            $arg_str = "";
            $arg_str = "DELETE FROM ";
            $arg_str .= "t_order_tran";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $t_order_tran = new TOrderTran();
            $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
//            }
            foreach ($add_item_input as $add_item_map) {
                $calum_list = array();
                $values_list = array();

                // 発注依頼行No.生成
                $order_req_line_no = $cnt++;

                // 発注情報_統合ハッシュキー(企業ID、発注依頼No、発注依頼行No)
                $t_order_comb_hkey = md5(
                    $auth['corporate_id']
                    . '-' . $shin_order_req_no
                    . '-' . $order_req_line_no
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
                // 発注状況区分(貸与)
                array_push($calum_list, "order_sts_kbn");
                array_push($values_list, "'1'");
                // レンタル契約No
                array_push($calum_list, "rntl_cont_no");
                array_push($values_list, "'".$wearer_odr_post['rntl_cont_no']."'");
                // レンタル部門コード
                array_push($calum_list, "rntl_sect_cd");
                array_push($values_list, "'".$cond['rntl_sect_cd']."'");
                // 貸与パターン
                array_push($calum_list, "job_type_cd");
                array_push($values_list, "'".$cond['job_type']."'");
                // 送信区分
                array_push($calum_list, "snd_kbn");
                array_push($values_list, "'" . $snd_kbn . "'");
                // 職種アイテムコード
                array_push($calum_list, "job_type_item_cd");
                array_push($values_list, "'".$add_item_map['add_job_type_item_cd']."'");
                // 着用者コード
                array_push($calum_list, "werer_cd");
                array_push($values_list, "'".$werer_cd."'");
                // 商品コード
                array_push($calum_list, "item_cd");
                array_push($values_list, "'".$add_item_map['add_item_cd']."'");
                // 色コード
                array_push($calum_list, "color_cd");
                array_push($values_list, "'".$add_item_map['add_color_cd']."'");
                // サイズコード
                array_push($calum_list, "size_cd");
                array_push($values_list, "'".$add_item_map['add_size_cd']."'");
                // サイズコード2
                array_push($calum_list, "size_two_cd");
                array_push($values_list, "' '");
                // 倉庫コード
                //rray_push($calum_list, "whse_cd");
                //array_push($values_list, "NULL");
                // 在庫USRコード
                //array_push($calum_list, "stk_usr_cd");
                //array_push($values_list, "NULL");
                // 在庫USR支店コード
                //array_push($calum_list, "stk_usr_brnch_cd");
                //array_push($values_list, "NULL");
                // 出荷先、出荷先支店コード
                array_push($calum_list, "ship_to_cd");
                array_push($values_list, "'".$wearer_odr_post['ship_to_cd']."'");
                array_push($calum_list, "ship_to_brnch_cd");
                array_push($values_list, "'".$wearer_odr_post['ship_to_brnch_cd']."'");
                // 発注枚数
                array_push($calum_list, "order_qty");
                array_push($values_list, "'".$add_item_map['add_order_num']."'");
                // 備考欄
                array_push($calum_list, "memo");
                array_push($values_list, "'".$cond['comment']."'");
                // 着用者名
                array_push($calum_list, "werer_name");
                array_push($values_list, "'".$wearer_odr_post['werer_name']."'");
                // 客先社員コード
                if (!empty($wearer_odr_post['cster_emply_cd'])) {
                    array_push($calum_list, "cster_emply_cd");
                    array_push($values_list, "'".$wearer_odr_post['cster_emply_cd']."'");
                }
                // 着用者状況区分(着用開始)
                array_push($calum_list, "werer_sts_kbn");
                array_push($values_list, "'7'");
                // 発令日
                if (!empty($wearer_odr_post['appointment_ymd'])) {
                    $appointment_ymd = date('Ymd', strtotime($wearer_odr_post['appointment_ymd']));
                    array_push($calum_list, "appointment_ymd");
                    array_push($values_list, "'".$appointment_ymd."'");
                } else {
                    array_push($calum_list, "appointment_ymd");
                    array_push($values_list, "NULL");
                }
                // 異動日
                if (!empty($wearer_odr_post['resfl_ymd'])) {
                    $resfl_ymd = date('Ymd', strtotime($wearer_odr_post['resfl_ymd']));
                    array_push($calum_list, "resfl_ymd");
                    array_push($values_list, "'".$resfl_ymd."'");
                } else {
                    array_push($calum_list, "resfl_ymd");
                    array_push($values_list, "NULL");
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
                array_push($calum_list, "m_job_type_comb_hkey");
                array_push($values_list, "'".$m_job_type[0]->m_job_type_comb_hkey."'");
                // 部門マスタ_統合ハッシュキー(企業ID、レンタル契約No.、レンタル部門コード)
                array_push($calum_list, "m_section_comb_hkey");
                array_push($values_list, "'".$m_section[0]->m_section_comb_hkey."'");
                // 着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
                $m_wearer_std_comb_hkey = $m_wearer_std_comb_hkey;
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
    $app->session->remove("wearer_odr_post");
    echo json_encode($json_list);
    return;
});
/**
 * 発注入力
 * 発注取消処理
 */
$app->post('/wearer_order_delete', function ()use($app){
  $params = json_decode(file_get_contents("php://input"), true);

  // アカウントセッション
  $auth = $app->session->get("auth");
  // 前画面セッション
  $wearer_odr_post = $app->session->get("wearer_odr_post");
  // フロントパラメータ
  if (!empty($params['data'])) {
    $cond = $params['data'];
  }

  $json_list = array();
  // DB更新エラーコード 0:正常 1:更新エラー
  $json_list["error_code"] = "0";
  try {
      //--発注情報トラン削除--//
      $query_list = array();
      array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
      if (!empty($cond["order_req_no"])) {
        array_push($query_list, "t_order_tran.order_req_no = '".$cond['order_req_no']."'");
      } else {
        array_push($query_list, "t_order_tran.order_req_no = '".$wearer_odr_post['order_req_no']."'");
      }
      // 発注区分「貸与」
      array_push($query_list, "t_order_tran.order_sts_kbn = '1'");
      $query = implode(' AND ', $query_list);

      $arg_str = "";
      $arg_str = "DELETE FROM ";
      $arg_str .= "t_order_tran";
      $arg_str .= " WHERE ";
      $arg_str .= $query;

      $t_order_tran = new TOrderTran();
      $transaction = $app->transactionManager->get();
      $t_order_tran->setTransaction($transaction);
      $results = new Resultset(null, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
      $result_obj = (array)$results;
      $results_cnt = $result_obj["\0*\0_count"];

//    $transaction->commit();
  } catch (Exception $e) {
      $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query('rollback'));
      $transaction->commit();
      $json_list["error_code"] = "1";
      $error_msg = "削除処理において、データ更新エラーが発生しました。";
      array_push($json_list["error_msg"], $error_msg);
      $json_list["error_code"] = "1";
      echo json_encode($json_list);
      return;
  }
    // トランザクションコミット
    $results = new Resultset(NULL, $t_order_tran, $t_order_tran->getReadConnection()->query('commit'));
    $app->session->remove("wearer_odr_post");
  echo json_encode($json_list);
});
/**
 * 発注入力（貸与開始）着用者情報
 * 入力項目：貸与パターン
 */
$app->post('/job_type_order', function ()use($app){
    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    //ChromePhp::LOG($auth);

    // 前画面セッション取得
    $wearer_odr_post = $app->session->get("wearer_odr_post");
    //ChromePhp::LOG($wearer_odr_post);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
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
            $list['job_type_cd'] = $result->job_type_cd;
            $list['job_type_name'] = $result->job_type_name;
            $list['sp_job_type_flg'] = $result->sp_job_type_flg;

            // 初期選択状態版を生成
            if ($list['job_type_cd'] == $wearer_odr_post['job_type_cd']) {
                $list['selected'] = 'selected';
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
