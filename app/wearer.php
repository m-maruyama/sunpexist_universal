<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 着用者照会検索
 */
$app->post('/wearer/search', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    $cond = $params['cond'];
    $page = $params['page'];
    $query_list = array();

    //---契約リソースマスター 0000000000フラグ確認処理---//
    $all_list = array();
    //ログインid
    $login_id_session = $auth['corporate_id'];
    //アカウントno
    $accnt_no = $auth['accnt_no'];
    //画面で選択された契約no
    $agreement_no = $cond['agreement_no'];
    //前処理 契約リソースマスタ参照 拠点ゼロ埋め確認
    $arg_str = "";
    $arg_str .= "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "m_contract_resource";
    $arg_str .= " WHERE ";
    $arg_str .= "corporate_id = '$login_id_session'";
    $arg_str .= " AND rntl_cont_no = '$agreement_no'";
    $arg_str .= " AND accnt_no = '$accnt_no'";
    $m_contract_resource = new MContractResource();
    $results = new Resultset(null, $m_contract_resource, $m_contract_resource->getReadConnection()->query($arg_str));
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
        foreach ($results as $result) {
            $all_list[] = $result->rntl_sect_cd;
        }
    }
    if (in_array("0000000000", $all_list)) {
        $rntl_sect_cd_zero_flg = 1;
    } else {
        $rntl_sect_cd_zero_flg = 0;
    }

    $t_delivery_goods_state_details_join_flg = false;
    //---検索条件---//
    //企業ID
    array_push($query_list, "m_wearer_std.corporate_id = '" . $auth['corporate_id'] . "'");
    //契約No
    if (!empty($cond['agreement_no'])) {
        array_push($query_list, "m_wearer_std.rntl_cont_no = '" . $cond['agreement_no'] . "'");
    }
    //社員番号
    if (!empty($cond['member_no'])) {
        array_push($query_list, "m_wearer_std.cster_emply_cd LIKE '" . $cond['member_no'] . "%'");
    }
    //着用者名
    // ※前方一致/部分一致
    if ($cond['wearer_name_src1']) {
        if (!empty($cond['member_name'])) {
            array_push($query_list, "m_wearer_std.werer_name LIKE '" . $cond['member_name'] . "%'");
        }
    } else {
        if (!empty($cond['member_name'])) {
            array_push($query_list, "m_wearer_std.werer_name LIKE '%" . $cond['member_name'] . "%'");
        }
    }
    //拠点
    if (!empty($cond['section'])) {
        array_push($query_list, "m_wearer_std.rntl_sect_cd = '" . $cond['section'] . "'");
    }
    //貸与パターン
    if (!empty($cond['job_type'])) {
        array_push($query_list, "m_wearer_std.job_type_cd = '" . $cond['job_type'] . "'");
    }
//  //商品
//  if (!empty($cond['input_item'])) {
//    array_push($query_list, "t_order.item_cd = '" . $cond['input_item'] . "'");
//  }
//  //色
//  if (!empty($cond['item_color'])) {
//    array_push($query_list, "t_order.color_cd = '" . $cond['item_color'] . "'");
//  }
//  //サイズ
//  if (!empty($cond['item_size'])) {
//    array_push($query_list, "t_order.size_cd = '" . $cond['item_size'] . "'");
//  }
    //個体管理番号
    if (!empty($cond['individual_number'])) {
        array_push($query_list, "(m_wearer_std.werer_sts_kbn ='1' OR m_wearer_std.werer_sts_kbn ='2' OR m_wearer_std.werer_sts_kbn ='3' OR m_wearer_std.werer_sts_kbn ='4')");
    }
    if (!empty($cond['input_item']) || !empty($cond['item_color']) || !empty($cond['item_size']) || !empty($cond['individual_number'])) {
        $t_delivery_goods_state_details_join_flg = true;
    }


    //ゼロ埋めがない場合、ログインアカウントの条件追加
    if ($rntl_sect_cd_zero_flg == 0) {
        array_push($query_list, "m_contract_resource.accnt_no = '$accnt_no'");
    }

    $status_kbn_list = array();

    //着用者区分
    $wearer_kbn = array();
    if ($cond['wearer_kbn0']) {
        array_push($wearer_kbn, '1');
    }
    if ($cond['wearer_kbn1']) {
        array_push($wearer_kbn, '2');
    }
    if ($cond['wearer_kbn2']) {
        array_push($wearer_kbn, '4');
    }
    if ($cond['wearer_kbn3']) {
        array_push($wearer_kbn, '8');
    }
    if ($cond['wearer_kbn4']) {
        array_push($wearer_kbn, '3');
    }
    if (!empty($wearer_kbn)) {
        $wearer_kbn_str = implode("','", $wearer_kbn);
        $wearer_kbn_query = "m_wearer_std.werer_sts_kbn IN ('" . $wearer_kbn_str . "')";
        array_push($status_kbn_list, $wearer_kbn_query);
    }

    if (!empty($status_kbn_list)) {
        $status_kbn_map = implode(' OR ', $status_kbn_list);
        array_push($query_list, "(" . $status_kbn_map . ")");
    }

    $query = implode(' AND ', $query_list);
    $sort_key = '';
    $order = '';

    //ソート設定
    if (!empty($page['sort_key'])) {
        $sort_key = $page['sort_key'];
        $order = $page['order'];

        if ($sort_key == 'cster_emply_cd') {
            $q_sort_key = 'as_cster_emply_cd';
        }
        if ($sort_key == 'werer_name') {
            $q_sort_key = 'as_werer_name';
        }
        if ($sort_key == 'job_type_cd') {
            $q_sort_key = 'as_job_type_name';
        }
        if ($sort_key == 'rntl_sect_name') {
            $q_sort_key = 'as_rntl_sect_name';
        }
        if ($sort_key == 'werer_sts_kbn') {
            $q_sort_key = 'as_werer_sts_kbn';
        }
    } else {
        // デフォルトソート
        $q_sort_key = "as_cster_emply_cd";
        $order = 'asc';
    }

    //---SQLクエリー実行---//
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (m_wearer_std.werer_cd,m_wearer_std.werer_sts_kbn,m_wearer_std.rntl_cont_no,m_wearer_std.rntl_sect_cd,m_wearer_std.job_type_cd) ";
    $arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
    $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std.werer_sts_kbn as as_werer_sts_kbn,";
    $arg_str .= "m_wearer_std.rntl_sect_cd as as_rntl_sect_cd,";
    $arg_str .= "m_wearer_std.job_type_cd as as_job_type_cd,";
    $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
    $arg_str .= "m_job_type.job_type_name as as_job_type_name";
    $arg_str .= " FROM m_wearer_std";
//    $arg_str .= " INNER JOIN";
//    $arg_str .= " ((t_order";
//  $arg_str .= " LEFT JOIN (t_order_state LEFT JOIN (t_delivery_goods_state LEFT JOIN t_delivery_goods_state_details ON t_delivery_goods_state.corporate_id = t_delivery_goods_state_details.corporate_id AND t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no AND t_delivery_goods_state.ship_line_no = t_delivery_goods_state_details.ship_line_no)";
//  $arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";
//  $arg_str .= " ON t_order.t_order_comb_hkey = t_order_state.t_order_comb_hkey))";
//  $arg_str .= " ON t_order.werer_cd = m_wearer_std.werer_cd";
//  $arg_str .= " AND t_order.corporate_id = m_wearer_std.corporate_id";
//  $arg_str .= " AND t_order.rntl_cont_no = m_wearer_std.rntl_cont_no";
    if ($rntl_sect_cd_zero_flg == 1) {
        $arg_str .= " INNER JOIN m_section";
        $arg_str .= " ON m_wearer_std.m_section_comb_hkey = m_section.m_section_comb_hkey";
    } elseif ($rntl_sect_cd_zero_flg == 0) {
        $arg_str .= " INNER JOIN (m_section INNER JOIN m_contract_resource";
        $arg_str .= " ON m_section.corporate_id = m_contract_resource.corporate_id";
        $arg_str .= " AND m_section.rntl_cont_no = m_contract_resource.rntl_cont_no";
        $arg_str .= " AND m_section.rntl_sect_cd = m_contract_resource.rntl_sect_cd";
        $arg_str .= " ) ON m_wearer_std.m_section_comb_hkey = m_section.m_section_comb_hkey";
    }
    $arg_str .= " INNER JOIN m_job_type ON m_wearer_std.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
    if ($t_delivery_goods_state_details_join_flg) {
        $arg_str .= " INNER JOIN t_delivery_goods_state_details";
        $arg_str .= " ON m_wearer_std.corporate_id = t_delivery_goods_state_details.corporate_id";
        $arg_str .= " AND m_wearer_std.rntl_cont_no = t_delivery_goods_state_details.rntl_cont_no";
        $arg_str .= " AND m_wearer_std.werer_cd = t_delivery_goods_state_details.werer_cd";
        //商品
        if (!empty($cond['input_item'])) {
            $arg_str .= " AND t_delivery_goods_state_details.item_cd = '" . $cond['input_item'] . "'";
        }
        //色
        if (!empty($cond['item_color'])) {
            $arg_str .= " AND t_delivery_goods_state_details.color_cd = '" . $cond['item_color'] . "'";
        }
        //サイズ
        if (!empty($cond['item_size'])) {
            $arg_str .= " AND t_delivery_goods_state_details.size_cd = '" . $cond['item_size'] . "'";
        }
        if (!empty($cond['individual_number'])) {
            $arg_str .= " AND t_delivery_goods_state_details.individual_ctrl_no LIKE '%" . $cond['individual_number'] . "%'";
        }
    }
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    if (!empty($q_sort_key)) {
        $arg_str .= " ORDER BY ";
        $arg_str .= $q_sort_key . " " . $order;
    }
    $m_wearer_std = new MWearerStd();
    $results = new Resultset(null, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    $paginator_model = new PaginatorModel(
    array(
    "data" => $results,
    "limit" => $page['records_per_page'],
    "page" => $page['page_number']
    )
    );

    $list = array();
    $all_list = array();
    $json_list = array();

    if (!empty($results_cnt)) {
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;

        foreach ($results as $result) {
            // 選択契約No
            $list['agreement_no'] = $cond['agreement_no'];

            // 着用者コード
            if (!empty($result->as_werer_cd)) {
                $list['werer_cd'] = $result->as_werer_cd;
            } else {
                $list['werer_cd'] = "";
            }
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
            // 貸与パターンcd
            if (!empty($result->as_job_type_cd)) {
                $list['job_type_cd'] = $result->as_job_type_cd;
            } else {
                $list['job_type_cd'] = "-";
            }
            // 拠点cd
            if (!empty($result->as_rntl_sect_cd)) {
                $list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
            } else {
                $list['rntl_sect_cd'] = "-";
            }

            //着用者状況区分
            // 貸与パターン
            if (!empty($result->as_job_type_name)) {
                $list['werer_sts_kbn'] = $result->as_werer_sts_kbn;

                //---着用者状況区分名称---//
                $query_list = array();
                array_push($query_list, "cls_cd = '009'");
                array_push($query_list, "gen_cd = '" . $list['werer_sts_kbn'] . "'");
                $query = implode(' AND ', $query_list);

                $arg_str = "";
                $arg_str = 'SELECT ';
                $arg_str .= ' * ';
                $arg_str .= ' FROM ';
                $arg_str .= 'm_gencode ';
                $arg_str .= ' WHERE ';
                $arg_str .= $query;

                $m_gencode = new MGencode();
                $results_wearer_pattern = new Resultset(null, $m_gencode, $m_gencode->getReadConnection()->query($arg_str));
                $results_wearer_pattern_array = (array)$results_wearer_pattern;
                $results_wearer_pattern_cnt = $results_wearer_pattern_array["\0*\0_count"];
                if (!empty($results_wearer_pattern_cnt)) {
                    foreach ($results_wearer_pattern as $result_wearer_pattern) {
                        $list['order_sts_name'] = $result_wearer_pattern->gen_name;
                    }
                } else {
                    $list['order_sts_name'] = "";
                }
            } else {
                $list['werer_sts_kbn'] = "-";
            }
            array_push($all_list, $list);
        }
    }

    /*
      // 個体管理番号表示/非表示フラグ設定
      if ($auth["individual_flg"] == 1) {
        $individual_flg = true;
      } else {
        $individual_flg = false;
      }
      $query_list = array();
      array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");
      $query = implode(' AND ', $query_list);
      $m_contract = MContract::query()
        ->where($query)
        ->columns('*')
        ->execute();
      $m_contract_obj = (array)$m_contract;
      $cnt = $m_contract_obj["\0*\0_count"];
      $individual_flg = "";
      if (!empty($cnt)) {
        foreach ($m_contract as $m_contract_map) {
          $individual_flg = $m_contract_map->individual_flg;
        }
        if ($individual_flg == 1) {
          $individual_flg = true;
        } else {
          $individual_flg = false;
        }
      }
    */

    $page_list['records_per_page'] = $page['records_per_page'];
    $page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = $results_cnt;
    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;
//	$json_list['individual_flag'] = $individual_flg;
    echo json_encode($json_list);
});


/**
 * 着用者詳細検索
 */
$app->post('/wearer/detail', function () use ($app) {

  $params = json_decode(file_get_contents("php://input"), true);


  // アカウントセッション取得
  $auth = $app->session->get("auth");
  //ChromePhp::log($auth);

  // フロントパラメータ取得
  $cond = $params;

  // json返却値
  $json_list = array();

  //---着用者個人情報---//
  $query_list = array();
  //企業ID
  array_push($query_list, "m_wearer_std.corporate_id = '" . $auth['corporate_id'] . "'");
  //契約No
  if (!empty($cond['agreement_no'])) {
    array_push($query_list, "m_wearer_std.rntl_cont_no = '" . $cond['agreement_no'] . "'");
  }
  //着用者コード
  if (!empty($cond['wearer_cd'])) {
    array_push($query_list, "m_wearer_std.werer_cd = '" . $cond['wearer_cd'] . "'");
  }
  //社員番号
  if (!empty($cond['cster_emply_cd']) && $cond['cster_emply_cd'] !== "-") {
    array_push($query_list, "m_wearer_std.cster_emply_cd = '" . $cond['cster_emply_cd'] . "'");
  }
  //拠点cd
  if (!empty($cond['rntl_sect_cd'])) {
    array_push($query_list, "m_wearer_std.rntl_sect_cd = '" . $cond['rntl_sect_cd'] . "'");
  }
  //職種cd
  if (!empty($cond['job_type_cd'])) {
    array_push($query_list, "m_wearer_std.job_type_cd = '" . $cond['job_type_cd'] . "'");
  }
  //着用者状況区分
  if (!empty($cond['werer_sts_kbn'])) {
    array_push($query_list, "m_wearer_std.werer_sts_kbn = '" . $cond['werer_sts_kbn'] . "'");
  }

  $query = implode(' AND ', $query_list);

  $arg_str = "";
  $arg_str = "SELECT ";
  $arg_str .= " * ";
  $arg_str .= " FROM ";
  $arg_str .= "(SELECT distinct on (m_wearer_std.werer_cd,m_wearer_std.werer_sts_kbn,m_wearer_std.rntl_cont_no,m_wearer_std.rntl_sect_cd,m_wearer_std.job_type_cd) ";
  $arg_str .= "m_wearer_std.rntl_cont_no as as_rntl_cont_no,";
  $arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
  $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
  $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
  $arg_str .= "m_wearer_std.werer_name_kana as as_werer_name_kana,";
  $arg_str .= "m_wearer_std.sex_kbn as as_sex_kbn,";
  $arg_str .= "m_wearer_std.resfl_ymd as as_resfl_ymd,";
  $arg_str .= "m_wearer_std.werer_sts_kbn as as_werer_sts_kbn,";
  $arg_str .= "m_wearer_std.job_type_cd as as_job_type_cd,";
  $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
  $arg_str .= "m_job_type.job_type_name as as_job_type_name,";
  $arg_str .= "m_shipment_to.ship_to_cd as as_ship_to_cd,";
  $arg_str .= "m_shipment_to.ship_to_brnch_cd as as_ship_to_brnch_cd,";
  $arg_str .= "m_shipment_to.cust_to_brnch_name1 as as_cust_to_brnch_name1,";
  $arg_str .= "m_shipment_to.cust_to_brnch_name2 as as_cust_to_brnch_name2,";
  $arg_str .= "m_shipment_to.zip_no as as_zip_no,";
  $arg_str .= "m_shipment_to.address1 as as_address1,";
  $arg_str .= "m_shipment_to.address2 as as_address2,";
  $arg_str .= "m_shipment_to.address3 as as_address3,";
  $arg_str .= "m_shipment_to.address4 as as_address4";
  $arg_str .= " FROM m_wearer_std";
  $arg_str .= " INNER JOIN m_section ON m_wearer_std.m_section_comb_hkey = m_section.m_section_comb_hkey";
  $arg_str .= " INNER JOIN m_job_type ON m_wearer_std.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
  $arg_str .= " INNER JOIN m_shipment_to ON m_wearer_std.ship_to_cd = m_shipment_to.ship_to_cd";
  $arg_str .= " AND m_wearer_std.ship_to_brnch_cd = m_shipment_to.ship_to_brnch_cd";
  $arg_str .= " WHERE ";
  $arg_str .= $query;
  $arg_str .= ") as distinct_table";
  $m_wearer_std = new MWearerStd();
  $results = new Resultset(null, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
  $result_obj = (array)$results;
  $results_cnt = $result_obj["\0*\0_count"];
  //ChromePhp::log($m_wearer_std->getReadConnection()->query($arg_str));
  $list = array();
  $all_list = array();

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

    foreach ($results as $result) {
      // レンタル契約No
      if (!empty($result->as_rntl_cont_no)) {
        $list['rntl_cont_no'] = $result->as_rntl_cont_no;
      } else {
        $list['rntl_cont_no'] = "";
      }
      // 着用者コード
      if (!empty($result->as_werer_cd)) {
        $list['werer_cd'] = $result->as_werer_cd;
      } else {
        $list['werer_cd'] = "";
      }
      // 社員番号
      if (!empty($result->as_cster_emply_cd)) {
        $list['cster_emply_cd'] = $result->as_cster_emply_cd;
      } else {
        $list['cster_emply_cd'] = "";
      }
      // 着用者名
      if (!empty($result->as_werer_name)) {
        $list['werer_name'] = $result->as_werer_name;
      } else {
        $list['werer_name'] = "";
      }
      // 着用者名かな
      if (!empty($result->as_werer_name_kana)) {
        $list['werer_name_kana'] = $result->as_werer_name_kana;
      } else {
        $list['werer_name_kana'] = "";
      }
      // 性別区分
      $list['sex_kbn'] = $result->as_sex_kbn;
      // 異動日
      $list['resfl_ymd'] = $result->as_resfl_ymd;
      // 着用者状況区分
      $list['werer_sts_kbn'] = $result->as_werer_sts_kbn;
      // 職種cd
      $list['job_type_cd'] = $result->as_job_type_cd;

      // 拠点
      if (!empty($result->as_rntl_sect_name)) {
        $list['rntl_sect_name'] = $result->as_rntl_sect_name;
      } else {
        $list['rntl_sect_name'] = "";
      }
      // 貸与パターン
      if (!empty($result->as_job_type_name)) {
        $list['job_type_name'] = $result->as_job_type_name;
      } else {
        $list['job_type_name'] = "";
      }
      // 出荷先コード
      if (!empty($result->as_ship_to_cd)) {
        $list['ship_to_cd'] = $result->as_ship_to_cd;
      } else {
        $list['ship_to_cd'] = "";
      }
      // 出荷先支店コード
      if (!empty($result->as_ship_to_brnch_cd)) {
        $list['ship_to_brnch_cd'] = $result->as_ship_to_brnch_cd;
      } else {
        $list['ship_to_brnch_cd'] = "";
      }
      // 取引先支店名1
      if (!empty($result->as_cust_to_brnch_name1)) {
        $list['cust_to_brnch_name1'] = $result->as_cust_to_brnch_name1;
      } else {
        $list['cust_to_brnch_name1'] = "";
      }
      // 取引先支店名2
      if (!empty($result->as_cust_to_brnch_name2)) {
        $list['cust_to_brnch_name2'] = $result->as_cust_to_brnch_name2;
      } else {
        $list['cust_to_brnch_name2'] = "";
      }
      // 郵便番号
      if (!empty($result->as_zip_no)) {
        $list['zip_no'] = $result->as_zip_no;
      } else {
        $list['zip_no'] = "";
      }
      // 住所1
      $list['address1'] = $result->as_address1;
      // 住所2
      $list['address2'] = $result->as_address2;
      // 住所3
      $list['address3'] = $result->as_address3;
      // 住所4
      $list['address4'] = $result->as_address4;

      //---日付設定---//
      // 異動日
      if (!empty($list['resfl_ymd'])) {
        $list['resfl_ymd'] = date('Y/m/d', strtotime($list['resfl_ymd']));
      } else {
        $list['resfl_ymd'] = '';
      }

      //---性別区分名称---//
      $query_list = array();
      array_push($query_list, "cls_cd = '004'");
      array_push($query_list, "gen_cd = '" . $list['sex_kbn'] . "'");
      $query = implode(' AND ', $query_list);

      $arg_str = "";
      $arg_str = 'SELECT ';
      $arg_str .= ' * ';
      $arg_str .= ' FROM ';
      $arg_str .= 'm_gencode ';
      $arg_str .= ' WHERE ';
      $arg_str .= $query;

      $m_gencode = new MGencode();
      $results = new Resultset(null, $m_gencode, $m_gencode->getReadConnection()->query($arg_str));
      $results_array = (array)$results;
      $results_cnt = $results_array["\0*\0_count"];
      if (!empty($results_cnt)) {
        foreach ($results as $result) {
          $list['sex_kbn_name'] = $result->gen_name;
        }
      } else {
        $list['sex_kbn_name'] = "";
      }

      //---着用者状況区分名称---//
      $query_list = array();
      array_push($query_list, "cls_cd = '009'");
      array_push($query_list, "gen_cd = '" . $list['werer_sts_kbn'] . "'");
      $query = implode(' AND ', $query_list);

      $arg_str = "";
      $arg_str = 'SELECT ';
      $arg_str .= ' * ';
      $arg_str .= ' FROM ';
      $arg_str .= 'm_gencode ';
      $arg_str .= ' WHERE ';
      $arg_str .= $query;

      $m_gencode = new MGencode();
      $results = new Resultset(null, $m_gencode, $m_gencode->getReadConnection()->query($arg_str));
      $results_array = (array)$results;
      $results_cnt = $results_array["\0*\0_count"];
      if (!empty($results_cnt)) {
        foreach ($results as $result) {
          $list['order_sts_name'] = $result->gen_name;
        }
      } else {
        $list['order_sts_name'] = "";
      }

      // 住所
      $list['wearer_address'] = $list['address1'] . $list['address2'] . $list['address3'] . $list['address4'];

      array_push($all_list, $list);
    }

    $json_list['kozin_list'] = $all_list;
  } else {
    $json_list['kozin_list'] = null;
  }

  //---着用者貸与情報---//
  $json_list['taiyo_list'] = null;
  //'1'稼働, '2'休職, '4'退社, '3'その他(着用終了)

  if ($list['werer_sts_kbn'] != 8) {
    // 現在着用中情報抽出
    $query_list = array();
    array_push($query_list, "m_wearer_std.corporate_id = '" . $auth['corporate_id'] . "'");
    array_push($query_list, "m_wearer_std.rntl_cont_no = '" . $cond['agreement_no'] . "'");
    array_push($query_list, "m_wearer_std.werer_cd = '" . $cond['wearer_cd'] . "'");
    $query = implode(' AND ', $query_list);

    //商品cd、色cd単位でdistinct
    //---SQLクエリー実行---//
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (t_delivery_goods_state_details.item_cd,t_delivery_goods_state_details.color_cd,t_delivery_goods_state_details.size_cd,t_delivery_goods_state_details.werer_cd) ";
    $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std.werer_cd as as_werer_cd,";
    $arg_str .= "m_wearer_std.rntl_sect_cd as as_now_rntl_sect_cd,";
    $arg_str .= "m_wearer_std.job_type_cd as as_now_job_type_cd,";
    $arg_str .= "m_wearer_std.rntl_cont_no as as_rntl_cont_no,";
    $arg_str .= "t_order.rntl_sect_cd as as_old_rntl_sect_cd,";
    $arg_str .= "t_order.job_type_cd as as_old_job_type_cd,";
    $arg_str .= "t_delivery_goods_state_details.order_req_no as as_order_req_no,";
    $arg_str .= "t_delivery_goods_state_details.item_cd as as_item_cd,";
    $arg_str .= "t_delivery_goods_state_details.color_cd as as_color_cd,";
    $arg_str .= "t_delivery_goods_state_details.size_cd as as_size_cd,";
    $arg_str .= "m_wearer_item.size_two_cd as as_size_two_cd,";
    $arg_str .= "m_wearer_item.job_type_item_cd as as_job_type_item_cd,";
    $arg_str .= "t_delivery_goods_state_details.individual_ctrl_no as as_individual_ctrl_no,";
    $arg_str .= "t_delivery_goods_state_details.quantity as as_quantity,";
    $arg_str .= "t_delivery_goods_state_details.returned_qty as as_returned_qty,";
    $arg_str .= "t_delivery_goods_state.ship_qty as as_ship_qty,";
    $arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
    $arg_str .= "t_returned_plan_info.order_date as as_re_order_date,";
    $arg_str .= "t_returned_plan_info.return_plan_qty as as_return_plan_qty,";
    $arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no,";
    $arg_str .= "t_delivery_goods_state_details.ship_no as as_ship_no";
    $arg_str .= " FROM t_delivery_goods_state_details";
    $arg_str .= " LEFT JOIN t_delivery_goods_state";
    $arg_str .= " ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no";
    $arg_str .= " AND t_delivery_goods_state.ship_line_no = t_delivery_goods_state_details.ship_line_no";
    $arg_str .= " LEFT JOIN t_order_state";
    $arg_str .= " ON t_delivery_goods_state.t_order_state_comb_hkey = t_order_state.t_order_state_comb_hkey";
    $arg_str .= " LEFT JOIN t_order";
    $arg_str .= " ON t_order.order_req_no = t_delivery_goods_state_details.order_req_no";
    $arg_str .= " AND t_order.rntl_cont_no = t_delivery_goods_state_details.rntl_cont_no";
    $arg_str .= " AND t_order.item_cd = t_delivery_goods_state_details.item_cd";
    $arg_str .= " AND t_order.color_cd = t_delivery_goods_state_details.color_cd";
    $arg_str .= " AND t_order.size_cd = t_delivery_goods_state_details.size_cd";
    $arg_str .= " LEFT JOIN t_returned_plan_info";
    $arg_str .= " ON t_delivery_goods_state_details.corporate_id = t_returned_plan_info.corporate_id";
    $arg_str .= " AND t_delivery_goods_state_details.rntl_cont_no = t_returned_plan_info.rntl_cont_no";
    $arg_str .= " AND t_delivery_goods_state_details.order_req_no = t_returned_plan_info.order_req_no";
    $arg_str .= " AND t_delivery_goods_state_details.individual_ctrl_no = t_returned_plan_info.individual_ctrl_no";
    $arg_str .= " INNER JOIN m_wearer_std";
    $arg_str .= " ON t_delivery_goods_state_details.corporate_id = m_wearer_std.corporate_id";
    $arg_str .= " AND t_delivery_goods_state_details.rntl_cont_no = m_wearer_std.rntl_cont_no";
    $arg_str .= " AND t_delivery_goods_state_details.werer_cd = m_wearer_std.werer_cd";
    $arg_str .= " LEFT JOIN m_wearer_item";
    $arg_str .= " ON t_order.m_wearer_item_comb_hkey = m_wearer_item.m_wearer_item_comb_hkey";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table ORDER BY as_order_req_no asc";
    $t_order = new TOrder();
    $results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //$json_list = array();
    $wearer_item_list = array();
    $list = array();

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

      //表No用
      $no_num = 1;
      foreach ($results as $result) {
        // 商品コード
        if (!empty($result->as_item_cd)) {
          $list['item_cd'] = $result->as_item_cd;
        } else {
          $list['item_cd'] = "";
        }
        // 色コード
        if (!empty($result->as_color_cd)) {
          $list['color_cd'] = $result->as_color_cd;
        } else {
          $list['color_cd'] = "";
        }
        // サイズコード
        if (!empty($result->as_size_cd)) {
          $list['size_cd'] = $result->as_size_cd;
        } else {
          $list['size_cd'] = "";
        }
        // サイズコード2
        if (!empty($result->as_size_two_cd)) {
          $list['size_two_cd'] = $result->as_size_two_cd;
        } else {
          $list['size_two_cd'] = "";
        }

        // 投入商品名
        $search_q = array();
        array_push($search_q, "corporate_id = '" . $auth['corporate_id'] . "'");
        array_push($search_q, "rntl_cont_no = '" . $result->as_rntl_cont_no . "'");
        array_push($search_q, "job_type_cd = '" . $result->as_old_job_type_cd . "'");
        //array_push($search_q, "job_type_item_cd = '".$result->as_job_type_item_cd."'");
        array_push($search_q, "item_cd = '" . $list['item_cd'] . "'");
        array_push($search_q, "color_cd = '" . $list['color_cd'] . "'");
        //サイズ2が空だったらサイズ2を検索条件に入れない
        //if($list['size_two_cd'] !== '') {
        //    array_push($search_q, "size_two_cd = '".$list['size_two_cd']."'");
        //}
        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $search_q);
        $input_item = MInputItem::query()
        ->where($query)
        ->columns('*')
        ->execute();
        // 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
        $input_item_obj = (array)$input_item;
        $cnt = $input_item_obj["\0*\0_count"];
        if (!empty($cnt)) {
          //ChromePhp::log($input_item);
          foreach ($input_item as $input_item_map) {
            $list['item_name'] = $input_item_map->input_item_name;
          }
        } else {
          $list['item_name'] = "-";
        }
        // 商品-色(サイズ-サイズ2)変換
        $list['shin_item_code'] = $list['item_cd'] . "-" . $list['color_cd'] . "(" . $list['size_cd'] . "-" . trim(mb_convert_kana( $list['size_two_cd'], "s")) . ")";


        if (individual_flg($auth['corporate_id'], $cond['agreement_no']) == 1) {
          // 個体管理番号(バーコード)
          //---個体管理番号・受領日時の取得---//
          $list['individual_num'] = "-";
          $list['order_res_ymd'] = "-";
          $query_list = array();
          array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
          //array_push($query_list, "ship_no = '" . $result->as_ship_no . "'");
          array_push($query_list, "werer_cd = '" . $result->as_werer_cd . "'");
          array_push($query_list, "item_cd = '" . $list['item_cd'] . "'");
          array_push($query_list, "color_cd = '" . $list['color_cd'] . "'");
          array_push($query_list, "size_cd = '" . $list['size_cd'] . "'");
          $query = implode(' AND ', $query_list);
          $arg_str = "";
          $arg_str .= "SELECT ";
          $arg_str .= "quantity,";
          $arg_str .= "returned_qty,";
          $arg_str .= "individual_ctrl_no,";
          $arg_str .= "receipt_date";
          $arg_str .= " FROM ";
          $arg_str .= "t_delivery_goods_state_details";
          $arg_str .= " WHERE ";
          $arg_str .= $query;
          $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
          $del_gd_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
          $result_obj = (array)$del_gd_results;
          $results_cnt = $result_obj["\0*\0_count"];
          if ($results_cnt > 0) {
            $paginator_model = new PaginatorModel(
            array(
            "data" => $del_gd_results,
            "limit" => $results_cnt,
            "page" => 1
            )
            );
            $paginator = $paginator_model->getPaginate();
            $del_gd_results = $paginator->items;

            $num_list = array();
            $return_plan_qty_list = array();
            $rental_qty_list = array();
            //$day_list = array();
            foreach ($del_gd_results as $del_gd_result) {
              //返却予定数と数量の総数を計算する。
              $parameter = array(
              "corporate_id" => $auth['corporate_id'],
              "rntl_cont_no" => $cond['agreement_no'],
              "werer_cd" => $result->as_werer_cd,
              "individual_ctrl_no" => $del_gd_result->individual_ctrl_no
              );
              //返却予定数の総数
              $TDeliveryGoodsStateDetails = TDeliveryGoodsStateDetails::find(array(
              'conditions' => "corporate_id = :corporate_id: AND rntl_cont_no = :rntl_cont_no: AND werer_cd = :werer_cd: AND individual_ctrl_no = :individual_ctrl_no:",
              "bind" => $parameter
              ));
              if ($TDeliveryGoodsStateDetails->count() > 0) {
                foreach ($TDeliveryGoodsStateDetails as $TDeliveryGoodsStateDetailsResult) {
                  if ($TDeliveryGoodsStateDetailsResult->quantity - $TDeliveryGoodsStateDetailsResult->returned_qty > 0) {
                    //貸与枚数
                    array_push($rental_qty_list, $TDeliveryGoodsStateDetails->count());
                    //返却予定数
                    array_push($return_plan_qty_list, $TDeliveryGoodsStateDetailsResult->return_plan__qty);
                  }
                }
              }
              //返却済み数
              if ($del_gd_result->quantity - $del_gd_result->returned_qty > 0) {
                array_push($num_list, $del_gd_result->individual_ctrl_no);
              }
            }
            if (count($num_list) > 0) {
              // 個体管理番号
              $individual_ctrl_no = implode("<br>", $num_list);
              $list['individual_num'] = $individual_ctrl_no;
              // 返却予定数
              $return_plan_qty = implode("<br>", $return_plan_qty_list);
              $list['return_plan_qty'] = (string)$return_plan_qty;
              //貸与枚数
              $rental_qty = implode("<br>", $rental_qty_list);
              $list['rental_qty'] = $rental_qty;

              $list['item_exist_flg'] = true;
            } else {
              $list['item_exist_flg'] = false;
            }
          }
          if ($list['item_exist_flg']) {
            // 表No
            $list['list_no'] = $no_num++;
            array_push($wearer_item_list, $list);
          }

        } else {
          //納品状況明細情報に、出荷番号違いで同一商品が複数入ることを想定し、数量を計算
          //返却予定数と数量の総数を計算する。
          $parameter = array(
          "corporate_id" => $auth['corporate_id'],
          "rntl_cont_no" => $cond['agreement_no'],
          "werer_cd" => $result->as_werer_cd,
          "item_cd" => $list['item_cd'],
          "size_cd" => $list['size_cd']);
          //返却予定数の総数
          $TDeliveryGoodsStateDetails = TDeliveryGoodsStateDetails::find(array(
          'conditions' => "corporate_id = :corporate_id: AND rntl_cont_no = :rntl_cont_no:  AND werer_cd = :werer_cd: AND item_cd = :item_cd: AND size_cd = :size_cd:",
          "bind" => $parameter
          ));
          $each_item_count = $TDeliveryGoodsStateDetails->count();
          // foreachでまわす
          $each_item_return_plan_qty = 0;
          $each_item_quantity = 0;
          $each_item_returned_qty = 0;
          for ($i = 0; $i < $each_item_count; $i++) {
            //返却予定数の合計
            $each_item_return_plan_qty = $each_item_return_plan_qty + $TDeliveryGoodsStateDetails[$i]->return_plan__qty;
            //数量の合計
            $each_item_quantity = $each_item_quantity + $TDeliveryGoodsStateDetails[$i]->quantity;
            //返却済み数の合計
            $each_item_returned_qty = $each_item_returned_qty + $TDeliveryGoodsStateDetails[$i]->returned_qty;
          }
          // 数量 納品状況明細情報の商品ごとの数量を合計した数
          $list['rental_qty'] = $each_item_quantity - $each_item_returned_qty;
          // 返却予定数 納品状況明細情報の商品ごとの返却予定数を合計した数
          $list["return_plan_qty"] = $each_item_return_plan_qty - $each_item_returned_qty;
          // 返却済数
          $list["returned_qty"] = $each_item_returned_qty;

          //返却済み数
          if ($list['rental_qty'] > 0) {
            // 表No
            $list['list_no'] = $no_num++;
            array_push($wearer_item_list, $list);
          }
        }
      }
    }


  } else {
    //移動の場合
    // 着用者基本マスタトランの情報がない場合
    $query_list = array();
    $list = array();

    array_push($query_list, "mi.corporate_id = '" . $auth['corporate_id'] . "'");
    array_push($query_list, "mii.rntl_cont_no = '" . $cond['agreement_no'] . "'");
    array_push($query_list, "mii.job_type_cd = '" . $cond['job_type_cd'] . "'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (mi.item_cd,mi.color_cd) ";
    $arg_str .= "mi.item_cd as as_item_cd,";
    $arg_str .= "mi.color_cd as_color_cd,";
    $arg_str .= "mi.size_cd as_size_cd,";
    $arg_str .= "mi.item_name as_item_name,";
    $arg_str .= "mii.rntl_cont_no as_rntl_cont_no,";
    $arg_str .= "mii.job_type_cd as_job_type_cd,";
    $arg_str .= "mii.job_type_item_cd as_job_type_item_cd,";
    $arg_str .= "mii.size_two_cd as_size_two_cd,";
    $arg_str .= "mii.std_input_qty as_std_input_qty,";
    $arg_str .= "mii.input_item_name as as_input_item_name";
    $arg_str .= " FROM ";
    $arg_str .= "m_input_item as mii";
    $arg_str .= " INNER JOIN ";
    $arg_str .= "m_job_type as mj";
    $arg_str .= " ON (mii.corporate_id=mj.corporate_id AND mii.rntl_cont_no=mj.rntl_cont_no AND mii.job_type_cd=mj.job_type_cd)";
    $arg_str .= " INNER JOIN ";
    $arg_str .= "m_item as mi";
    $arg_str .= " ON (mii.item_cd=mi.item_cd AND mii.color_cd=mi.color_cd)";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_item_cd,as_color_cd ASC";

    $m_input_item = new MInputItem();
    $results = new Resultset(NULL, $m_input_item, $m_input_item->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    //ChromePhp::LOG($results_cnt);

    //$json_list = array();
    $wearer_item_list = array();
    $list = array();

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

      //表No用
      $no_num = 1;
      foreach ($results as $result) {

        // 商品コード
        if (!empty($result->as_item_cd)) {
          $list['item_cd'] = $result->as_item_cd;
        } else {
          $list['item_cd'] = "";
        }
        // 色コード
        if (!empty($result->as_color_cd)) {
          $list['color_cd'] = $result->as_color_cd;
        } else {
          $list['color_cd'] = "";
        }
        // サイズコード
        if (!empty($result->as_size_cd)) {
          $list['size_cd'] = $result->as_size_cd;
        } else {
          $list['size_cd'] = "";
        }
        // サイズコード2
        if (!empty($result->as_size_two_cd)) {
          $list['size_two_cd'] = $result->as_size_two_cd;
        } else {
          $list['size_two_cd'] = "";
        }

        // 投入商品名
        $search_q = array();
        array_push($search_q, "corporate_id = '" . $auth['corporate_id'] . "'");
        array_push($search_q, "rntl_cont_no = '" . $result->as_rntl_cont_no . "'");
        array_push($search_q, "job_type_cd = '" . $result->as_job_type_cd . "'");
        array_push($search_q, "job_type_item_cd = '" . $result->as_job_type_item_cd . "'");
        array_push($search_q, "item_cd = '" . $list['item_cd'] . "'");
        array_push($search_q, "color_cd = '" . $list['color_cd'] . "'");
        //サイズ2が空だったらサイズ2を検索条件に入れない
//        if ($list['size_two_cd'] !== '') {
//          array_push($search_q, "size_two_cd = '" . $list['size_two_cd'] . "'");
//        }
        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $search_q);
        $input_item = MInputItem::query()
        ->where($query)
        ->columns('*')
        ->execute();
        // 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
        $input_item_obj = (array)$input_item;
        $cnt = $input_item_obj["\0*\0_count"];
        if (!empty($cnt)) {
          foreach ($input_item as $input_item_map) {
            $list['item_name'] = $input_item_map->input_item_name;
            //$list['rental_qty'] = $input_item_map->std_input_qty;
          }
        } else {
          $list['item_name'] = "-";
        }
        // 商品-色(サイズ-サイズ2)変換
        $list['shin_item_code'] = $list['item_cd'] . "-" . $list['color_cd'];

        // 表No
        $list['list_no'] = $no_num++;
        array_push($wearer_item_list, $list);
      }
    }
  }

  // 個体管理番号表示/非表示フラグ設定
  if (individual_flg($auth['corporate_id'], $cond['agreement_no']) == 1) {
    $individual_flg = true;
  } else {
    $individual_flg = false;
  }


  $json_list['individual_flg'] = $individual_flg;
  $json_list['taiyo_list'] = $wearer_item_list;

  echo json_encode($json_list);
});
