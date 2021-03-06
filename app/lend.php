<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * 貸与リスト検索
 */
$app->post('/lend/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();

    //---契約リソースマスター 0000000000フラグ確認処理---//
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
                "data"  => $results,
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
    }else{
        $rntl_sect_cd_zero_flg = 0;
    }

	//---検索条件---//
	//企業ID
	array_push($query_list,"m_wearer_std.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	if(!empty($cond['agreement_no'])){
		array_push($query_list,"m_wearer_std.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//社員番号
	if(!empty($cond['member_no'])){
		array_push($query_list,"m_wearer_std.cster_emply_cd LIKE '".$cond['member_no']."%'");
	}
	//着用者名
	if(!empty($cond['member_name'])){
		array_push($query_list,"m_wearer_std.werer_name LIKE '%".$cond['member_name']."%'");
	}
	//拠点
	if(!empty($cond['section'])){
		array_push($query_list,"m_wearer_std.rntl_sect_cd = '".$cond['section']."'");
	}
	//貸与パターン
	if(!empty($cond['job_type'])){
		array_push($query_list,"m_wearer_std.job_type_cd = '".$cond['job_type']."'");
	}
	//商品
	if(!empty($cond['input_item'])){
		array_push($query_list,"t_delivery_goods_state_details.item_cd = '".$cond['input_item']."'");
	}
	//色
	if(!empty($cond['item_color'])){
		array_push($query_list,"t_delivery_goods_state_details.color_cd = '".$cond['item_color']."'");
	}
	//サイズ
	if(!empty($cond['item_size'])){
		array_push($query_list,"t_delivery_goods_state_details.size_cd = '".$cond['item_size']."'");
	}
	//個体管理番号
	if(!empty($cond['individual_number'])){
		array_push($query_list,"t_delivery_goods_state_details.individual_ctrl_no LIKE '%".$cond['individual_number']."%'");
	}
	// 着用者状況区分
  //array_push($query_list,"m_wearer_std.werer_sts_kbn = '1'";
  array_push($query_list,"(m_wearer_std.werer_sts_kbn = '1' OR m_wearer_std.werer_sts_kbn = '4' OR  m_wearer_std.werer_sts_kbn = '2' OR m_wearer_std.werer_sts_kbn = '3' OR m_wearer_std.werer_sts_kbn = '5'  OR m_wearer_std.werer_sts_kbn = '6' OR m_wearer_std.werer_sts_kbn = '7')");

    //納品状況明細情報 数量　<> 納品状況明細情報 返却済数
    array_push($query_list,"NOT EXISTS (SELECT * FROM t_delivery_goods_state_details as TS WHERE t_delivery_goods_state_details.quantity = t_delivery_goods_state_details.returned_qty)");

    //ゼロ埋めがない場合、ログインアカウントの条件追加
    if($rntl_sect_cd_zero_flg == 0){
        array_push($query_list,"m_contract_resource.accnt_no = '$accnt_no'");
    }


	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';

	//第一ソート設定
	if(!empty($page['sort_key'])){
		$sort_key = $page['sort_key'];
		$order = $page['order'];
		// 社員番号
		if($sort_key == 'cster_emply_cd'){
			$q_sort_key = 'as_cster_emply_cd';
		}
		// 着用者名
		if($sort_key == 'werer_name'){
			$q_sort_key = 'as_werer_name';
		}
		// 商品コード
		if($sort_key == 'item_code'){
			$q_sort_key = 'as_item_cd';
		}
		// 個体管理番号
		if($sort_key == 'individual_num'){
			$q_sort_key = 'as_individual_ctrl_no';
		}
		// 出荷日
		if($sort_key == 'send_ymd'){
			$q_sort_key = 'as_ship_ymd';
		}
		// 発注No
		if($sort_key == 'order_req_no'){
			$q_sort_key = 'as_order_req_no';
		}
		//発注区分 order_kbn
    if($sort_key == 'order_kbn'){
        $q_sort_key = 'as_order_sts_kbn';
    }

		// メーカー伝票番号
		if($sort_key == 'maker_send_no'){
			$q_sort_key = 'as_ship_no';
		}
	} else {
		//指定がなければ社員番号
		$q_sort_key = "as_werer_name";
		$order = 'asc';
	}
    //商品cd、色cd、着用者cd単位でdistinct
    //---SQLクエリー実行---//
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (t_delivery_goods_state_details.werer_cd,t_delivery_goods_state_details.item_cd,t_delivery_goods_state_details.color_cd,t_delivery_goods_state_details.size_cd) ";
    $arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_wearer_std.rntl_sect_cd as as_now_rntl_sect_cd,";
    $arg_str .= "m_wearer_std.job_type_cd as as_now_job_type_cd,";
    $arg_str .= "t_order.rntl_sect_cd as as_old_rntl_sect_cd,";
    $arg_str .= "t_order.job_type_cd as as_old_job_type_cd,";
    $arg_str .= "m_wearer_item.size_two_cd as as_size_two_cd,";
    $arg_str .= "m_wearer_item.job_type_item_cd as as_job_type_item_cd,";
    $arg_str .= "t_delivery_goods_state.ship_qty as as_ship_qty,";
    $arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
    $arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no,";
    $arg_str .= "t_delivery_goods_state.ship_no as as_ship_no,";
    $arg_str .= "t_delivery_goods_state_details.item_cd as as_item_cd,";
    $arg_str .= "t_delivery_goods_state_details.color_cd as as_color_cd,";
    $arg_str .= "t_delivery_goods_state_details.size_cd as as_size_cd,";
    $arg_str .= "t_delivery_goods_state_details.individual_ctrl_no as as_individual_ctrl_no,";
    $arg_str .= "t_delivery_goods_state_details.quantity as as_quantity,";
    $arg_str .= "t_delivery_goods_state_details.return_plan__qty as as_return_plan__qty,";
    $arg_str .= "t_delivery_goods_state_details.returned_qty as as_returned_qty,";
    $arg_str .= "t_delivery_goods_state_details.werer_cd as as_werer_cd,";
    $arg_str .= "t_order.order_sts_kbn as as_order_sts_kbn,";
    $arg_str .= "t_delivery_goods_state_details.order_req_no as as_order_req_no";

    $arg_str .= " FROM t_delivery_goods_state_details LEFT JOIN";
    $arg_str .= " t_delivery_goods_state";
    $arg_str .= " ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no";
    $arg_str .= " AND t_delivery_goods_state.ship_line_no = t_delivery_goods_state_details.ship_line_no";
    $arg_str .= " INNER JOIN t_order_state";
    $arg_str .= " ON t_delivery_goods_state.t_order_state_comb_hkey = t_order_state.t_order_state_comb_hkey";
    $arg_str .= " INNER JOIN t_order";
    $arg_str .= " ON t_order.order_req_no = t_delivery_goods_state_details.order_req_no";
    $arg_str .= " AND t_order.rntl_cont_no = t_delivery_goods_state_details.rntl_cont_no";
    $arg_str .= " AND t_order.item_cd = t_delivery_goods_state_details.item_cd";
    $arg_str .= " AND t_order.color_cd = t_delivery_goods_state_details.color_cd";
    $arg_str .= " AND t_order.size_cd = t_delivery_goods_state_details.size_cd";

    $arg_str .= " INNER JOIN m_wearer_std";
    $arg_str .= " ON t_delivery_goods_state_details.corporate_id = m_wearer_std.corporate_id";
    $arg_str .= " AND t_delivery_goods_state_details.rntl_cont_no = m_wearer_std.rntl_cont_no";
    $arg_str .= " AND t_delivery_goods_state_details.werer_cd = m_wearer_std.werer_cd";
    if ($rntl_sect_cd_zero_flg == 1) {
        $arg_str .= " INNER JOIN m_section";
        $arg_str .= " ON m_wearer_std.m_section_comb_hkey = m_section.m_section_comb_hkey";
    } elseif ($rntl_sect_cd_zero_flg == 0) {
        $arg_str .= " INNER JOIN m_section";
        $arg_str .= " ON m_wearer_std.m_section_comb_hkey = m_section.m_section_comb_hkey";
        $arg_str .= " INNER JOIN m_contract_resource";
        $arg_str .= " ON m_wearer_std.corporate_id = m_contract_resource.corporate_id";
        $arg_str .= " AND m_wearer_std.rntl_cont_no = m_contract_resource.rntl_cont_no";
        $arg_str .= " AND m_wearer_std.rntl_sect_cd = m_contract_resource.rntl_sect_cd";
    }
    $arg_str .= " LEFT JOIN m_wearer_item";
    $arg_str .= " ON t_order.m_wearer_item_comb_hkey = m_wearer_item.m_wearer_item_comb_hkey";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    if (!empty($q_sort_key)) {
        $arg_str .= " ORDER BY ";
        $arg_str .= $q_sort_key . " " . $order;
    }
    //ChromePhp::log($arg_str);
    $t_order = new TOrder();
    $results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
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
	if(!empty($results_cnt)) {
		$paginator = $paginator_model->getPaginate();
		$results = $paginator->items;

		foreach($results as $result){
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
			// 現在の拠点コード
			$list['now_rntl_sect_cd'] = $result->as_now_rntl_sect_cd;
			// 現在の貸与パターン
			$list['now_job_type_cd'] = $result->as_now_job_type_cd;

			// 商品コード
			$list['item_cd'] = $result->as_item_cd;
			// 色コード
			$list['color_cd'] = $result->as_color_cd;
			// サイズコード
			$list['size_cd'] = $result->as_size_cd;
			// サイズコード２
			$list['size_two_cd'] = $result->as_size_two_cd;
			// 職種アイテムコード
			$list['job_type_item_cd'] = $result->as_job_type_item_cd;
			// 個体管理番号
			if (!empty($result->as_individual_ctrl_no)) {
				$list['individual_ctrl_no'] = $result->as_individual_ctrl_no;
			} else {
				$list['individual_ctrl_no'] = "-";
			}

			// 商品-色(サイズ-サイズ2)表示変換
			$list['shin_item_code'] = $list['item_cd']."-".$list['color_cd']."(".$list['size_cd']."-".trim(mb_convert_kana( $list['size_two_cd'], "s")).")";

			// 現在の拠点
			$search_q = array();
			array_push($search_q, "corporate_id = '".$auth['corporate_id']."'");
			array_push($search_q, "rntl_cont_no = '".$cond['agreement_no']."'");
			array_push($search_q, "rntl_sect_cd = '".$list['now_rntl_sect_cd']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $search_q);
			$section = MSection::query()
				->where($query)
				->columns('*')
				->execute();
			// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
			$section_obj = (array)$section;
			$cnt = $section_obj["\0*\0_count"];
			if (!empty($cnt)) {
				foreach ($section as $section_map) {
					$list['now_rntl_sect_name'] = $section_map->rntl_sect_name;
				}
			} else {
				$list['now_rntl_sect_name'] = "-";
			}


			// 現在の貸与パターン
			$search_q = array();
			array_push($search_q, "corporate_id = '".$auth['corporate_id']."'");
			array_push($search_q, "rntl_cont_no = '".$cond['agreement_no']."'");
			array_push($search_q, "job_type_cd = '".$list['now_job_type_cd']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $search_q);
			$job_type = MJobType::query()
				->where($query)
				->columns('*')
				->execute();
			// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
			$job_type_obj = (array)$job_type;
			$cnt = $job_type_obj["\0*\0_count"];
			if (!empty($cnt)) {
				foreach ($job_type as $job_type_map) {
					$list['now_job_type_name'] = $job_type_map->job_type_name;
				}
			} else {
				$list['now_job_type_name'] = "-";
			}

			// 投入商品名
			$search_q = array();
			array_push($search_q, "corporate_id = '".$auth['corporate_id']."'");
			array_push($search_q, "rntl_cont_no = '".$cond['agreement_no']."'");
			array_push($search_q, "job_type_cd = '".$result->as_old_job_type_cd."'");
			//array_push($search_q, "job_type_item_cd = '".$result->as_job_type_item_cd."'");
			array_push($search_q, "item_cd = '".$list['item_cd']."'");
			array_push($search_q, "color_cd = '".$list['color_cd']."'");
        //サイズ2が空だったらサイズ2を検索条件に入れない
//      if($list['size_two_cd'] != null) {
//          array_push($search_q, "size_two_cd = '".$list['size_two_cd']."'");
//      }
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $search_q);
        //ChromePhp::log($query);
        //ChromePhp::log($query);
			$input_item = MInputItem::query()
				->where($query)
				->columns('*')
				->execute();
			// 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
			$input_item_obj = (array)$input_item;
			$cnt = $input_item_obj["\0*\0_count"];
        //ChromePhp::log($cnt);
			if (!empty($cnt)) {
				foreach ($input_item as $input_item_map) {
					$list['input_item_name'] = $input_item_map->input_item_name;
				}
			} else {
				$list['input_item_name'] = "-";
			}

      //---個体管理番号・受領日時の取得---//
      $list['individual_num'] = "-";
      $list['order_res_ymd'] = "-";
      $query_list = array();
      array_push($query_list, "t_delivery_goods_state_details.corporate_id = '".$auth['corporate_id']."'");
      array_push($query_list, "t_delivery_goods_state_details.werer_cd = '".$result->as_werer_cd."'");
      array_push($query_list, "t_delivery_goods_state_details.item_cd = '".$list['item_cd']."'");
      array_push($query_list, "t_delivery_goods_state_details.color_cd = '".$list['color_cd']."'");
      array_push($query_list, "t_delivery_goods_state_details.size_cd = '".$list['size_cd']."'");
      $query = implode(' AND ', $query_list);
      $arg_str = "";
      $arg_str .= "SELECT ";
      $arg_str .= "t_delivery_goods_state_details.individual_ctrl_no as as_individual_ctrl_no,";
      $arg_str .= "t_delivery_goods_state_details.quantity as as_quantity,";
      $arg_str .= "t_delivery_goods_state_details.return_plan__qty as as_return_plan__qty,";
      $arg_str .= "t_delivery_goods_state_details.returned_qty as as_returned_qty,";
      $arg_str .= "t_delivery_goods_state_details.ship_no as as_ship_no,";
      $arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
      $arg_str .= "t_delivery_goods_state_details.order_req_no as as_order_req_no,";
      $arg_str .= "t_delivery_goods_state_details.order_sts_kbn as as_order_sts_kbn,";
      $arg_str .= "t_order.job_type_cd as as_job_type_cd,";
      $arg_str .= "t_order.rntl_sect_cd as as_rntl_sect_cd";
      $arg_str .= " FROM ";
      $arg_str .= "t_delivery_goods_state_details";
      $arg_str .= " INNER JOIN (t_delivery_goods_state";
      $arg_str .= " INNER JOIN (t_order_state";
      $arg_str .= " INNER JOIN t_order";
      $arg_str .= " ON t_order.t_order_comb_hkey = t_order_state.t_order_comb_hkey)";
      $arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";
      $arg_str .= " ON t_delivery_goods_state.corporate_id = t_delivery_goods_state_details.corporate_id";
      $arg_str .= " AND t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no";
      $arg_str .= " AND t_delivery_goods_state.ship_line_no = t_delivery_goods_state_details.ship_line_no";
      $arg_str .= " WHERE ";
      $arg_str .= $query;
      $t_delivery_goods_state_details = new TDeliveryGoodsStateDetails();
      $del_gd_results = new Resultset(null, $t_delivery_goods_state_details, $t_delivery_goods_state_details->getReadConnection()->query($arg_str));
      $result_obj = (array)$del_gd_results;
      $results_cnt2 = $result_obj["\0*\0_count"];
      if ($results_cnt2 > 0) {
          $paginator_model = new PaginatorModel(
              array(
                  "data"  => $del_gd_results,
                  "limit" => $results_cnt2,
                  "page" => 1
              )
          );
          $paginator = $paginator_model->getPaginate();
          $del_gd_results = $paginator->items;

          $num_list = array();
          $ship_ymd_list = array();
          $ship_list = array();
          $quantity_list = array();
          $return_plan_qty_list = array();
          $order_req_no_list = array();
          $old_sect_no_list = array();
          $old_job_type_list = array();
          $order_kbn_list = array();
          foreach ($del_gd_results as $del_gd_result) {
              //数量計算 : 数量 - 返却済み数
              $quantity = $del_gd_result->as_quantity - $del_gd_result->as_returned_qty;
              if($quantity > 0) {
                  $not_display_flg = true;
              }
              else{
                  $not_display_flg = false;
              }

              if($not_display_flg){

                  //個体管理番号
                  array_push($num_list, $del_gd_result->as_individual_ctrl_no);
                  //出荷no
                  array_push($ship_list, $del_gd_result->as_ship_no);
                  //数量
                  array_push($quantity_list, $quantity);
                  //返却予定数計算  : 返却予定数 - 返却済み数
                  $return_plan__qty = $del_gd_result->as_return_plan__qty - $del_gd_result->as_returned_qty;
                  array_push($return_plan_qty_list, $return_plan__qty);
                  array_push($order_req_no_list, $del_gd_result->as_order_req_no);

                  //出荷日
                  if ($del_gd_result->as_ship_ymd !== null) {
                    //日付フォーマットチェック
                    if (date_check($del_gd_result->as_ship_ymd)) {
                      array_push($ship_ymd_list, date('Y/m/d',strtotime($del_gd_result->as_ship_ymd)));
                    } else {
                      array_push($ship_ymd_list, "-");
                    }
                  } else {
                      array_push($ship_ymd_list, "-");
                  }

                  // 納品時の拠点
                  $search_q = array();
                  array_push($search_q, "corporate_id = '".$auth['corporate_id']."'");
                  array_push($search_q, "rntl_cont_no = '".$cond['agreement_no']."'");
                  array_push($search_q, "rntl_sect_cd = '".$del_gd_result->as_rntl_sect_cd."'");
                  //sql文字列を' AND 'で結合
                  $query = implode(' AND ', $search_q);
                  $section = MSection::query()
                      ->where($query)
                      ->columns('*')
                      ->execute();
                  // 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
                  $section_obj = (array)$section;
                  $cnt = $section_obj["\0*\0_count"];
                  if (!empty($cnt)) {
                      foreach ($section as $section_map) {
                          array_push($old_sect_no_list, $section_map->rntl_sect_name);
                      }
                  } else {
                      $list['old_rntl_sect_name'] = "-";
                  }
                  // 納品時の貸与パターン
                  $search_q = array();
                  array_push($search_q, "corporate_id = '".$auth['corporate_id']."'");
                  array_push($search_q, "rntl_cont_no = '".$cond['agreement_no']."'");
                  array_push($search_q, "job_type_cd = '".$del_gd_result->as_job_type_cd."'");
                  //sql文字列を' AND 'で結合
                  $query = implode(' AND ', $search_q);
                  $job_type = MJobType::query()
                      ->where($query)
                      ->columns('*')
                      ->execute();
                  // 取得オブジェクトを配列化→クラス内propety：protected値を取得する→リストカウント
                  $job_type_obj = (array)$job_type;
                  $cnt = $job_type_obj["\0*\0_count"];
                  if (!empty($cnt)) {
                      foreach ($job_type as $job_type_map) {
                          array_push($old_job_type_list, $job_type_map->job_type_name);
                      }
                  } else {
                      $list['old_job_type_name'] = "-";
                  }

                  //---発注区分名称---//
                  $search_q = array();
                  // 汎用コードマスタ.分類コード
                  array_push($search_q, "cls_cd = '001'");
                  // 汎用コードマスタ. レンタル契約No
                  array_push($search_q, "gen_cd = '".$del_gd_result->as_order_sts_kbn."'");
                  //sql文字列を' AND 'で結合
                  $query = implode(' AND ', $search_q);
                  $gencode = MGencode::query()
                      ->where($query)
                      ->columns('*')
                      ->execute();
                  foreach ($gencode as $gencode_map) {
                      array_push($order_kbn_list, $gencode_map->gen_name);
                  }
              }
          }

          // 個体管理番号
          $individual_ctrl_no = implode("<br>", $num_list);
          $list['individual_num'] = $individual_ctrl_no;
          //出荷no
          $ship_no = implode("<br>", $ship_list);
          $list['ship_no'] = $ship_no;
          //出荷日
          $ship_ymd = implode("<br>", $ship_ymd_list);
          $list['ship_ymd'] = $ship_ymd;
          //貸与数
          $quantity = implode("<br>", $quantity_list);
          $list['quantity'] = $quantity;
          //返却予定数
          $return_plan__qty = implode("<br>", $return_plan_qty_list);
          $list['return_plan__qty'] = $return_plan__qty;
          //発注no
          $order_req_no = implode("<br>", $order_req_no_list);
          $list['order_req_no'] = $order_req_no;
          //納品時の拠点
          $old_rntl_sect_name = implode("<br>", $old_sect_no_list);
          $list['old_rntl_sect_name'] = $old_rntl_sect_name;
          //納品時の職種
          $old_job_type_name = implode("<br>", $old_job_type_list);
          $list['old_job_type_name'] = $old_job_type_name;
          //発注区分名
          $order_kbn = implode("<br>", $order_kbn_list);
          $list['order_kbn'] = $order_kbn;

      }
      //数量 - 返却済み数 の合計が 0 になる場合は、表示をしない
        array_push($all_list,$list);
		}
	}

	// 第二ソートキー(配列ソート)
	// 現在の拠点
	if($sort_key == 'now_rntl_sect_name'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'now_rntl_sect_name'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'now_rntl_sect_name'), SORT_ASC, $all_list);
		}
	}
	// 現在の貸与パターン
	if($sort_key == 'now_job_type_cd'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'now_job_type_name'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'now_job_type_name'), SORT_ASC, $all_list);
		}
	}
	// 納品時の拠点
	if($sort_key == 'old_rntl_sect_name'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'old_rntl_sect_name'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'old_rntl_sect_name'), SORT_ASC, $all_list);
		}
	}
	// 納品時の貸与パターン
	if($sort_key == 'old_job_type_cd'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'old_job_type_name'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'old_job_type_name'), SORT_ASC, $all_list);
		}
	}
	// 商品名（投入商品）
	if($sort_key == 'item_name'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'input_item_name'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'input_item_name'), SORT_ASC, $all_list);
		}
	}

    // 個体管理番号表示/非表示フラグ設定
    if (individual_flg($auth['corporate_id'], $cond['agreement_no']) == 1) {
        $individual_flg = true;
    } else {
        $individual_flg = false;
    }

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $results_cnt;
	$json_list['page'] = $page_list;
    //ChromePhp::log($page_list);
	$json_list['list'] = $all_list;
	$json_list['individual_flag'] = $individual_flg;
	echo json_encode($json_list);
});
