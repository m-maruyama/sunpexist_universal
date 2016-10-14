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
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    //ChromePhp::LOG($wearer_chg_post);

    //--発注管理単位取得--//
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    array_push($query_list, "job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
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
        $list['reason_kbn'] = $result->gen_cd;
        $list['reason_kbn_name'] = $result->gen_name;

        // 発注情報トランフラグ有の場合は初期選択状態版を生成
        if ($wearer_chg_post['order_tran_flg'] == '1') {
          if ($list['reason_kbn'] == $wearer_chg_post['order_reason_kbn']) {
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
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
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
        if ($wearer_chg_post['order_tran_flg'] == '1') {
          if ($list['rntl_sect_cd'] == $wearer_chg_post['rntl_sect_cd']) {
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
    $wearer_chg_post = $app->session->get("wearer_chg_post");
    $json_list['rntl_cont_no'] = $wearer_chg_post['rntl_cont_no'];
    $json_list['werer_cd'] = $wearer_chg_post['werer_cd'];
    $json_list['cster_emply_cd'] = $wearer_chg_post['cster_emply_cd'];
    $json_list['sex_kbn'] = $wearer_chg_post['sex_kbn'];
    $json_list['rntl_sect_cd'] = $wearer_chg_post['rntl_sect_cd'];
    $json_list['job_type_cd'] = $wearer_chg_post['job_type_cd'];
    $json_list['order_reason_kbn'] = $wearer_chg_post['order_reason_kbn'];
    $json_list['order_tran_flg'] = $wearer_chg_post['order_tran_flg'];
    $json_list['wearer_tran_flg'] = $wearer_chg_post['wearer_tran_flg'];

    // 発注枚数
    $query_list = array();
    array_push($query_list, "m_input_item.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "SUM(m_input_item.std_input_qty) as_std_input_qty";
    $arg_str .= " FROM ";
    $arg_str .= "m_input_item";
    $arg_str .= " WHERE ";
    $arg_str .= "m_input_item.job_type_cd = '".$wearer_chg_post['job_type_cd']."'";
    $arg_str .= " GROUP BY job_type_cd";

    $m_input_item = new MInputItem();
    $results = new Resultset(NULL, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    foreach ($results as $result) {
        $json_list['order_count'] = $result->as_std_input_qty;
    }
    // 発注枚数ここまで


    //出荷先リスト
    //--出荷先選択ボックス生成--//
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    array_push($query_list, "ship_to_cd = '".$wearer_chg_post['ship_to_cd']."'");
    array_push($query_list, "ship_to_brnch_cd = '".$wearer_chg_post['ship_to_brnch_cd']."'");
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
    if ($wearer_chg_post['wearer_tran_flg'] == '1') {
        //--着用者基本マスタトラン有の場合--//
        array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
        array_push($query_list,"m_wearer_std_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
        array_push($query_list,"m_wearer_std_tran.cster_emply_cd = '".$wearer_chg_post['cster_emply_cd']."'");
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
    } elseif ($wearer_chg_post['wearer_tran_flg'] == '0') {
        //--着用者基本マスタトラン無の場合--//
        array_push($query_list, "m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "m_wearer_std.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
        array_push($query_list,"m_wearer_std.werer_cd = '".$wearer_chg_post['werer_cd']."'");
        array_push($query_list,"m_wearer_std.cster_emply_cd = '".$wearer_chg_post['cster_emply_cd']."'");
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
            }

            array_push($all_list, $list);
        }

        $json_list['wearer_info'] = $all_list;
    }
    if(empty( $json_list['wearer_info'])){
        if(!$wearer_chg_post['cster_emply_cd']){
            $cster_emply_cd = '';
        }else{
            $cster_emply_cd = $wearer_chg_post['cster_emply_cd'];
        }
        // 社員コード
        $list['cster_emply_cd'] = $cster_emply_cd;
        // 着用者名
        $list['werer_name'] = $wearer_chg_post['werer_name'];
        // 着用者名（読み仮名）
        $list['werer_name_kana'] = $wearer_chg_post['werer_name_kana'];
        // 発令日
        $list['appointment_ymd'] = $wearer_chg_post['appointment_ymd'];
        if (!empty($list['appointment_ymd'])) {
            $list['appointment_ymd'] = date('Y/m/d', strtotime($list['appointment_ymd']));
        } else {
            $list['appointment_ymd'] = '';
        }
        array_push($all_list, $list);
        $json_list['wearer_info'] = $all_list;
    }
    $param_list = '';
    $param_list .= $wearer_chg_post['rntl_cont_no'].':';
    $param_list .= $wearer_chg_post['werer_cd'].':';
    $param_list .= $wearer_chg_post['cster_emply_cd'].':';
    $param_list .= $wearer_chg_post['sex_kbn'].':';
    $param_list .= $wearer_chg_post['rntl_sect_cd'].':';
    $param_list .= $wearer_chg_post['job_type_cd'].':';
    $param_list .= $wearer_chg_post['ship_to_cd'].':';
    $param_list .= $wearer_chg_post['ship_to_brnch_cd'].':';
    $param_list .= $wearer_chg_post['order_reason_kbn'].':';
    $param_list .= $wearer_chg_post['order_tran_flg'].':';
    $param_list .= $wearer_chg_post['wearer_tran_flg'].':';
    $param_list .= $wearer_chg_post['appointment_ymd'].':';
    $param_list .= $wearer_chg_post['resfl_ymd'];
    $json_list['param'] = $param_list;
    $json_list['selected_job'] = $wearer_chg_post['job_type_cd'];
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
    $wearer_chg_post = $app->session->get("wearer_chg_post");

    // フロントパラメータ取得
    $cond = $params['data'];

    //貸与パターン変更時
    if(isset($cond['job_type'])){
        $wearer_chg_post['job_type_cd'] = $cond['job_type'];
    }

    //発注情報トランを参照し、「発注商品一覧」を生成する。
    $json_list = array();
    $all_list = array();
    $query_list = array();
    // 着用者基本マスタトラン．企業ID　＝　　ログインしているアカウントの企業ID　AND
    array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
    //着用者基本マスタトラン．着用者コード　＝　　「①着用者入力」画面の表示の際に使用した着用者コード　AND
    array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
    //着用者基本マスタトラン．レンタル契約No.　＝　前画面で選択された契約No. AND
    array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
    //着用者基本マスタトラン．レンタル部門コード　＝　前画面で選択された拠点の部門コード AND
    array_push($query_list,"m_wearer_std_tran.rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
    //着用者基本マスタトラン．職種コード　＝　前画面で選択された貸与パターンの職種コード AND
    array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");

    $query = implode(' AND ', $query_list);

    //---SQLクエリー実行---//
    $arg_str = "SELECT ";
    $arg_str .= "m_item.item_name as as_item_name,";
    $arg_str .= "m_item.item_cd as as_item_cd,";
    $arg_str .= "m_item.color_cd as as_color_cd,";
    $arg_str .= "m_input_item.std_input_qty as as_std_input_qty,";
    $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
    $arg_str .= "m_input_item.size_two_cd as as_size_two_cd,";
    $arg_str .= "t_order_tran.size_cd as as_size_cd_tran,";
    $arg_str .= "t_order_tran.order_qty as as_order_qty_tran";
    $arg_str .= " FROM m_wearer_std_tran INNER JOIN t_order_tran";
    $arg_str .= " ON m_wearer_std_tran.m_wearer_std_comb_hkey = t_order_tran.m_wearer_std_comb_hkey";
    $arg_str .= " INNER JOIN m_section";
    $arg_str .= " ON m_wearer_std_tran.m_section_comb_hkey = m_section.m_section_comb_hkey";
    $arg_str .= " INNER JOIN m_job_type";
    $arg_str .= " ON m_wearer_std_tran.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
    $arg_str .= " INNER JOIN m_input_item";
    $arg_str .= " ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey";
    $arg_str .= " INNER JOIN m_item";
    $arg_str .= " ON m_input_item.item_cd = m_item.item_cd AND
                    m_input_item.color_cd = m_item.color_cd";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " GROUP BY as_item_name, as_item_cd,as_color_cd, as_std_input_qty,
        as_input_item_name,as_size_two_cd,as_input_item_name,as_size_cd_tran,as_order_qty_tran";

    $m_weare_std_tran= new MWearerStdTran();
    $results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];

    $m_weare_std_tran_flg = false;
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
        $m_weare_std_tran_flg = true;
    }else{
        //発注情報トランにデータが存在しない場合
        //職種マスタを参照し、「発注商品一覧」を生成する。
        $query_list = array();
        // 職種マスタ．企業ID　＝　　ログインしているアカウントの企業ID　AND
        array_push($query_list, "m_job_type.corporate_id = '".$auth['corporate_id']."'");
        //職種マスタ．レンタル契約No.　＝　前画面で選択された契約No. AND
        array_push($query_list, "m_job_type.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
        //職種マスタ．職種コード　＝　前画面で選択された貸与パターンの職種コード AND
        array_push($query_list, "m_job_type.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");

        $query = implode(' AND ', $query_list);

        //---SQLクエリー実行---//
        $arg_str = "SELECT ";
        $arg_str .= "m_item.item_name as as_item_name,";
        $arg_str .= "m_item.item_cd as as_item_cd,";
        $arg_str .= "m_item.color_cd as as_color_cd,";
        $arg_str .= "m_input_item.std_input_qty as as_std_input_qty,";
        $arg_str .= "m_input_item.size_two_cd as as_size_two_cd,";
        $arg_str .= "m_input_item.input_item_name as as_input_item_name";
        $arg_str .= " FROM m_job_type";
        $arg_str .= " INNER JOIN m_input_item";
        $arg_str .= " ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey";
        $arg_str .= " INNER JOIN m_item";
        $arg_str .= " ON m_input_item.item_cd = m_item.item_cd AND
                    m_input_item.color_cd = m_item.color_cd";
        $arg_str .= " WHERE ";
        $arg_str .= $query;
        $arg_str .= " GROUP BY as_item_name, as_item_cd,as_color_cd, as_std_input_qty,
        as_input_item_name,as_size_two_cd,as_input_item_name";

        $m_job_type = new MJobType();
        $results = new Resultset(null, $m_job_type, $m_job_type->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
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
    $arr_cnt = 0;
    $list_cnt = 1;
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
        //※着用者の職種マスタ.職種コードに紐づく投入商品マスタの職種アイテムコード単位で単一or複数判断
        $query_list = array();
        array_push($query_list, "m_job_type.corporate_id = '".$auth['corporate_id']."'");
        array_push($query_list, "m_job_type.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
        array_push($query_list, "m_job_type.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
        array_push($query_list, "m_input_item.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
        array_push($query_list, "m_input_item.item_cd = '".$result->as_item_cd."'");
        array_push($query_list, "m_input_item.color_cd = '".$result->as_color_cd."'");
        array_push($query_list, "m_input_item.size_two_cd = '".$result->as_size_two_cd."'");
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
        array_push($m_item_query_list,"color_cd = '".$result->as_color_cd."'");
        $query = implode(' AND ', $m_item_query_list);
        //--- クエリー実行・取得 ---//
        $m_item_results = MItem::find(array(
            'conditions' => $query,
            "columns" => "size_cd"
        ));
        $size_list = array();
        $size_list_to = array();
        foreach ($m_item_results as $m_item_result){
            $size_list['size_cd'] = $m_item_result->size_cd;
            array_push($size_list_to , $size_list);
        }
        // サイズコードセレクトボックス
        $list['size_cd_list'] = $size_list_to;
        // 発注情報トラン.サイズコード（発注情報トランにレコードが存在する場合は、発注情報トラン．サイズコードを初期選択状態で表示する。）
        //	発注情報トランにレコードが存在する場合は、発注情報トラン．投入枚数を初期値で表示する。
        if($m_weare_std_tran_flg){
            $list['size_cd_tran'] = $result->as_size_cd_tran;
            $list['order_qty_tran'] = $result->as_order_qty_tran;
        }else{
            $list['size_cd_tran'] = '';
            $list['order_qty_tran'] = '0';
        }

        // 発注数(単一選択=入力不可、複数選択=入力可)
        if(isset($result->as_order_qty_tran)){
            $list["order_num"] = $result->as_order_qty_tran;
        }else{
            $list["order_num"] = $result->as_std_input_qty;
        }
        if ($list["choice_type"] == "1") {
            $list["order_num_disable"] = "disabled";
        } else {
            $list["order_num_disable"] = "";
        }
        // 商品-色
        $list["item_and_color"] = $list['item_cd']."-".$list['color_cd'];
        array_push($all_list,$list);
//
//
//A-６．	「保存（後で送信）」ボタン
//	アカウントセッション内の下記いずれかの条件を満たす場合のみ表示。
//
//	A	契約リソースマスタ．発注入力可否フラグ ＝ 発注入力可
//	B	契約リソースマスタ．発注送信可否フラグ ＝ 発注送信可
//
//A-７．	「発注送信」ボタン
//	アカウントセッション内の下記いずれかの条件を満たす場合のみ表示。
//
//		契約リソースマスタ．発注送信可否フラグ ＝ 発注送信可

    }
    $json_list["tran_flg"] = $m_weare_std_tran_flg;
    $json_list["list_cnt"] = count($all_list);
    $json_list['list'] = $all_list;
    echo json_encode($json_list);

});

///**
// * 発注入力
// * 発注取消処理
// */
//$app->post('/wearer_delete', function ()use($app){
//  $params = json_decode(file_get_contents("php://input"), true);
//
//  // アカウントセッション取得
//  $auth = $app->session->get("auth");
//  //ChromePhp::LOG($auth);
//  // 前画面セッション取得
//  $wearer_chg_post = $app->session->get("wearer_chg_post");
//  //ChromePhp::LOG($wearer_chg_post);
//  // フロントパラメータ取得
//  //$cond = $params['data'];
//  //ChromePhp::LOG("フロント側パラメータ");
//  //ChromePhp::LOG($cond);
//
//  $json_list = array();
//  // DB更新エラーコード 0:正常 1:更新エラー
//  $json_list["error_code"] = "0";
//
//  // トランザクション開始
////  $transaction = $app->transactionManager->get();
//
//  try {
//    //--着用者商品マスタトラン削除--//
//    ChromePhp::LOG("着用者商品マスタトラン削除");
//    $query_list = array();
//    array_push($query_list, "m_wearer_item_tran.corporate_id = '".$auth['corporate_id']."'");
//    array_push($query_list, "t_order_tran.order_req_no = '".$wearer_chg_post['order_req_no']."'");
//    // 発注区分「終了」
//    array_push($query_list, "t_order_tran.order_sts_kbn = '2'");
//    $query = implode(' AND ', $query_list);
//
//    $arg_str = "";
//    $arg_str = "DELETE FROM ";
//    $arg_str .= "m_wearer_item_tran";
//    $arg_str .= " USING ";
//    $arg_str .= "t_order_tran";
//    $arg_str .= " WHERE ";
//    $arg_str .= "m_wearer_item_tran.werer_cd = t_order_tran.werer_cd";
//    $arg_str .= " AND m_wearer_item_tran.rntl_cont_no = t_order_tran.rntl_cont_no";
//    $arg_str .= " AND m_wearer_item_tran.rntl_sect_cd = t_order_tran.rntl_sect_cd";
//    $arg_str .= " AND m_wearer_item_tran.job_type_cd = t_order_tran.job_type_cd";
//    $arg_str .= " AND m_wearer_item_tran.job_type_item_cd = t_order_tran.job_type_item_cd";
//    $arg_str .= " AND m_wearer_item_tran.item_cd = t_order_tran.item_cd";
//    $arg_str .= " AND m_wearer_item_tran.color_cd = t_order_tran.color_cd";
//    $arg_str .= " AND m_wearer_item_tran.size_cd = t_order_tran.size_cd";
//    $arg_str .= " AND m_wearer_item_tran.size_two_cd = t_order_tran.size_two_cd";
//    $arg_str .= " AND ";
//    $arg_str .= $query;
//    //ChromePhp::LOG($arg_str);
//
//    $m_wearer_item_tran = new MWearerItemTran();
//    $results = new Resultset(null, $m_wearer_item_tran, $m_wearer_item_tran->getReadConnection()->query($arg_str));
//    $result_obj = (array)$results;
//    $results_cnt = $result_obj["\0*\0_count"];
//    //ChromePhp::LOG($results_cnt);
//
//    //--着用者基本マスタトラン削除--//
//    // 発注情報トランを参照
//    //ChromePhp::LOG("発注情報トラン参照");
//    $query_list = array();
//    array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
//    array_push($query_list, "t_order_tran.order_req_no <> '".$wearer_chg_post['order_req_no']."'");
//    array_push($query_list, "t_order_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
//    $query = implode(' AND ', $query_list);
//
//    $arg_str = "";
//    $arg_str = "SELECT ";
//    $arg_str .= "*";
//    $arg_str .= " FROM ";
//    $arg_str .= "t_order_tran";
//    $arg_str .= " WHERE ";
//    $arg_str .= $query;
//    //ChromePhp::LOG($arg_str);
//
//    $t_order_tran = new TOrderTran();
//    $results = new Resultset(null, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
//    $result_obj = (array)$results;
//    $results_cnt = $result_obj["\0*\0_count"];
//    //ChromePhp::LOG($results_cnt);
//
//    // 上記発注情報トラン件数が0の場合に着用者基本マスタトランのデータを削除する
//    if (empty($results_cnt)) {
//      //ChromePhp::LOG("着用者基本マスタトラン削除");
//      $query_list = array();
//      array_push($query_list, "m_wearer_std_tran.corporate_id = '".$auth['corporate_id']."'");
//      array_push($query_list, "m_wearer_std_tran.werer_cd = '".$wearer_chg_post['werer_cd']."'");
//      array_push($query_list, "m_wearer_std_tran.rntl_cont_no = '".$wearer_chg_post['rntl_cont_no']."'");
//      array_push($query_list, "m_wearer_std_tran.rntl_sect_cd = '".$wearer_chg_post['rntl_sect_cd']."'");
//      array_push($query_list, "m_wearer_std_tran.job_type_cd = '".$wearer_chg_post['job_type_cd']."'");
//      // 発注区分「着用者編集」ではない
//      array_push($query_list, "m_wearer_std_tran.order_sts_kbn <> '6'");
//      $query = implode(' AND ', $query_list);
//
//      $arg_str = "";
//      $arg_str = "DELETE FROM ";
//      $arg_str .= "m_wearer_std_tran";
//      $arg_str .= " WHERE ";
//      $arg_str .= $query;
//      //ChromePhp::LOG($arg_str);
//
//      $m_wearer_std_tran = new MWearerStdTran();
//      $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
//      $result_obj = (array)$results;
//      $results_cnt = $result_obj["\0*\0_count"];
//      //ChromePhp::LOG($results_cnt);
//    }
//
//    //--発注情報トラン削除--//
//    //ChromePhp::LOG("発注情報トラン削除");
//    $query_list = array();
//    array_push($query_list, "t_order_tran.corporate_id = '".$auth['corporate_id']."'");
//    array_push($query_list, "t_order_tran.order_req_no = '".$wearer_chg_post['order_req_no']."'");
//    // 発注区分「貸与」
//    array_push($query_list, "t_order_tran.order_sts_kbn = '1'");
//    // 理由区分「職種変更または異動」系ステータス
//    $reason_kbn = array();
//    array_push($reason_kbn, '4');
//    array_push($reason_kbn, '8');
//    array_push($reason_kbn, '9');
//    array_push($reason_kbn, '10');
//    array_push($reason_kbn, '11');
//    if(!empty($reason_kbn)) {
//      $reason_kbn_str = implode("','",$reason_kbn);
//      $reason_kbn_query = "t_order_tran.order_reason_kbn IN ('".$reason_kbn_str."')";
//      array_push($query_list, $reason_kbn_query);
//    }
//    $query = implode(' AND ', $query_list);
//
//    $arg_str = "";
//    $arg_str = "DELETE FROM ";
//    $arg_str .= "t_order_tran";
//    $arg_str .= " WHERE ";
//    $arg_str .= $query;
//    //ChromePhp::LOG($arg_str);
//
//    $t_order_tran = new TOrderTran();
//    $results = new Resultset(null, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
//    $result_obj = (array)$results;
//    $results_cnt = $result_obj["\0*\0_count"];
//    //ChromePhp::LOG($results_cnt);
//
////    $transaction->commit();
//  } catch (Exception $e) {
////    $transaction->rollback();
//
//    $json_list["error_code"] = "1";
//    echo json_encode($json_list);
//    //ChromePhp::LOG("発注取消処理コード");
//    //ChromePhp::LOG($json_list["error_code"]);
//
//    return;
//  }
//
//  //ChromePhp::LOG("発注取消処理コード");
//  //ChromePhp::LOG($json_list["error_code"]);
//  echo json_encode($json_list);
//});
//
///**
// * 発注入力
// * 入力完了処理
// */
//$app->post('/wearer_complete', function ()use($app){
//   $params = json_decode(file_get_contents("php://input"), true);
//
//   // アカウントセッション取得
//   $auth = $app->session->get("auth");
//   //ChromePhp::LOG($auth);
//
//   // 前画面セッション取得
//   $wearer_chg_post = $app->session->get("wearer_chg_post");
//   //ChromePhp::LOG($wearer_chg_post);
//
//   // フロントパラメータ取得
//   //$cond = $params['data'];
//   //ChromePhp::LOG("フロント側パラメータ");
//   //ChromePhp::LOG($cond);
//
//   $json_list = array();
//
//   // DB更新エラーコード 0:正常 その他:要因エラー
//   $json_list["error_code"] = "0";
//
//   echo json_encode($json_list);
//});
//
///**
// * 発注入力（職種変更または異動）
// * 発注送信処理
// */
// $app->post('/wearer_send', function ()use($app){
//   $params = json_decode(file_get_contents("php://input"), true);
//
//   // アカウントセッション取得
//   $auth = $app->session->get("auth");
//   //ChromePhp::LOG($auth);
//
//   // 前画面セッション取得
//   $wearer_chg_post = $app->session->get("wearer_chg_post");
//   //ChromePhp::LOG($wearer_chg_post);
//
//   // フロントパラメータ取得
//   //$cond = $params['data'];
//   //ChromePhp::LOG("フロント側パラメータ");
//   //ChromePhp::LOG($cond);
//
//   $json_list = array();
//
//   // DB更新エラーコード 0:正常 その他:要因エラー
//   $json_list["error_code"] = "0";
//
//   echo json_encode($json_list);
//});
