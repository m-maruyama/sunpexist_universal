<?php

use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;


/*
　* 検索項目：商品
　*/
$app->post('/purchase/input_item', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "rntl_cont_no = '" . $params['agreement_no'] . "'");
    } else {
        array_push($query_list, "rntl_cont_no = '" . $auth['rntl_cont_no'] . "'");
    }
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (item_cd) *';
    $arg_str .= ' FROM m_sale_order_item';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY item_cd asc';

    $m_sale_order_item = new MSaleOrderItem();
    $results = new Resultset(null, $m_sale_order_item, $m_sale_order_item->getReadConnection()->query($arg_str));
    $results_array = (array)$results;
    $results_cnt = $results_array["\0*\0_count"];
    /*
        // デフォルト「全て」を設定
        if ($results_cnt > 1) {
            $list['item_cd'] = null;
            $list['input_item_name'] = '全て';
            array_push($all_list, $list);
        }
    */
    if ($results_cnt > 0) {
        if ($results_cnt > 1) {
            $list['item_cd'] = null;
            $list['item_name'] = '全て';
            array_push($all_list, $list);
        }

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
            $list['item_cd'] = $result->item_cd;
            $list['item_name'] = $result->item_name;
            array_push($all_list, $list);
        }
        //ChromePhp::log($result);

    } else {
        $list['item_cd'] = null;
        $list['item_name'] = '';
        array_push($all_list, $list);
    }

    $json_list['sale_item_list'] = $all_list;
    echo json_encode($json_list);
});

/*
　* 検索項目：色
　*/
$app->post('/purchase/item_color', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
    if (!empty($params['agreement_no'])) {
        array_push($query_list, "rntl_cont_no = '" . $params['agreement_no'] . "'");
    } else {
        array_push($query_list, "rntl_cont_no = '" . $auth['rntl_cont_no'] . "'");
    }
    if (!empty($params['job_type'])) {
        array_push($query_list, "job_type_cd = '" . $params['job_type'] . "'");
    }
    if (!empty($params['input_item'])) {
        array_push($query_list, "item_cd = '" . $params['input_item'] . "'");
    }
    $query = implode(' AND ', $query_list);

    // SQLクエリー実行
    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (color_cd) *';
    $arg_str .= ' FROM m_sale_order_item';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY color_cd asc';

    $m_sale_order_item = new MSaleOrderItem();
    $results = new Resultset(null, $m_sale_order_item, $m_sale_order_item->getReadConnection()->query($arg_str));
    $results_array = (array)$results;
    $results_cnt = $results_array["\0*\0_count"];
    /*
        // デフォルト「全て」を設定
        if ($results_cnt > 1) {
            $list['color_cd_id'] = null;
            $list['color_cd_name'] = '全て';
            array_push($all_list, $list);
        }
    */
    if ($results_cnt > 0) {
        $list['color_cd_id'] = null;
        $list['color_cd_name'] = '全て';
        array_push($all_list, $list);

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
            $list['color_cd_id'] = $result->color_cd;
            $list['color_cd_name'] = $result->color_cd;
            array_push($all_list, $list);
        }
    } else {
        $list['color_cd_id'] = null;
        $list['color_cd_name'] = '';
        array_push($all_list, $list);
    }

    $json_list['item_color_list'] = $all_list;
    echo json_encode($json_list);
});


/*
 * 注文履歴
 */
$app->post('/purchase_history/search', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);
    $json_list = array();
    // アカウントセッション取得
    $auth = $app->session->get('auth');
    if ($auth['user_type'] == '1') {
        $json_list['redirect'] = $auth['user_type'];
        echo json_encode($json_list);
        return;
    }

    //削除の場合
    if (isset($params['del'])) {

        $line_no = $params['line_no'];
        $ac = TSaleOrderHistory::find(array(
            'conditions' => "line_no = '" . $line_no . "'",
        ));

        ChromePhp::log($ac[0]);
        $transaction = $app->transactionManager->get();

        if ($ac[0]->delete() == false) {
            $error_list['delete'] = 'アカウントの削除に失敗しました。';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            return true;
        } else {
            $transaction->commit();
            echo json_encode($json_list);

            return true;
        }
        ChromePhp::log('ここまできちゃった');
        echo json_encode($json_list);
        return true;
    }


    //の場合
    $cond = $params['cond'];
    $page = $params['page'];
    $query_list = array();//追加
    //ChromePhp::log($params);
    // ChromePhp::log($cond);

    $corporate_id = $auth['corporate_id'];
    //$item_cd = $cond['item_cd'];
    //$item_color = $cond['item_color'];
    //$item_size = $cond['item_size'];
    //$order_day_from = $cond['order_day_from'];
    //if(!isset($order_day_from)){
    //    $order_day_from = date('Y-m-d', strtotime('20010101'));
    //    ChromePhp::log($order_day_from);
    //}
    // $order_day_to = $cond['order_day_to'];
    //$rntl_cont_no = $cond['rntl_cont_no'];
    //$section = $cond['section'];
    //date("Y/m/d", strtotime("-1 day"  ));


    //
    if (isset($auth['corporate_id'])) {
        array_push($query_list, "TSaleOrderHistory.corporate_id = '" . $auth['corporate_id'] . "'");
    }
    //契約no
    if (isset($cond['rntl_cont_no'])) {
        array_push($query_list, "TSaleOrderHistory.rntl_cont_no = '" . $cond['rntl_cont_no'] . "'");
    }
    //
    if (isset($cond['section'])) {
        array_push($query_list, "TSaleOrderHistory.rntl_sect_cd = '" . $cond['section'] . "'");
    }
    //この日付から
    if (isset($cond['order_day_from'])) {
        array_push($query_list, "TSaleOrderHistory.sale_order_date >= '" . $cond['order_day_from'] . "'");
    }
    //この日付まで
    if (isset($cond['order_day_to'])) {
        array_push($query_list, "TSaleOrderHistory.sale_order_date <= '" . $cond['order_day_to'] . " 23:59:59'");
    }

    //商品コード
    if (isset($cond['item_cd'])) {
        array_push($query_list, "TSaleOrderHistory.item_cd = '" . $cond['item_cd'] . "'");
    }
    //色コード
    if (isset($cond['item_color'])) {
        array_push($query_list, "TSaleOrderHistory.color_cd = '" . $cond['item_color'] . "'");
    }
    //サイズ
    if (isset($cond['item_size'])) {
        array_push($query_list, "TSaleOrderHistory.size_cd = '" . $cond['item_size'] . "'");
    }

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    $sort_key = '';
    $order = '';


    //第一ソート設定
    if (!empty($page['sort_key'])) {
        $sort_key = $page['sort_key'];
        if (!empty($page['order'])) {
            $order = $page['order'];
        }
        if ($sort_key == 'line_no') {
            $q_sort_key = 'line_no';
        }
        if ($sort_key == 'sale_order_date') {
            $q_sort_key = 'sale_order_date';
        }
        if ($sort_key == 'rntl_sect_cd') {
            $q_sort_key = 'rntl_sect_cd';
        }
        if ($sort_key == 'item_name') {
            $q_sort_key = 'item_name';
        }
        if ($sort_key == 'color_cd') {
            $q_sort_key = 'color_cd';
        }
        if ($sort_key == 'size_cd') {
            $q_sort_key = 'size_cd';
        }
        if ($sort_key == 'quantity') {
            $q_sort_key = 'quantity';
        }


    } else {
        //指定がなければ社員番号
        $sort_key = 'line_no';
        $order = 'asc';
    }


    $all_list = array();
    $json_list = array();

    $results = $app->modelsManager->createBuilder()
        ->where($query)
        ->from('TSaleOrderHistory')
        ->columns('*')
        //->columns(array('TSaleOrderHistory.*','MSection.*'))
        ->join('MSection', 'TSaleOrderHistory.rntl_sect_cd = MSection.rntl_sect_cd')
        ->orderBy($sort_key . ' ' . $order)
        ->getQuery()
        ->execute();

    //->columns(array('TReturnedPlanInfo.*','TReturnedResults.*','MSection.*','MJobType.*','MItem.*'))
    //->leftJoin('TReturnedPlanInfo','TReturnedPlanInfo.t_returned_plan_info_comb_hkey = TReturnedResults.t_returned_plan_info_comb_hkey')
    //->join('MJobType','MJobType.rntl_cont_no = TReturnedResults.rntl_cont_no AND MJobType.job_type_cd = TReturnedResults.rent_pattern_code')
    //->join('MSection','MSection.m_section_comb_hkey = TReturnedResults.m_section_comb_hkey')
    //->join('MItem','MItem.m_item_comb_hkey = TReturnedResults.m_item_comb_hkey')
    //->orderBy($sort_key.' '.$order)


    //$user_id_val = $cond['user_id'];
    //$user_name_val = $cond['user_name'];
    //$mail_address = $cond['mail_address'];

    //全検索
    //$results = TSaleOrderHistory::find(array(
    //    'order' => "$sort_key $order",
    //'order' => "$sort_key $order",
    //   'conditions' =>
    //       "corporate_id = '$corporate_id'
    //        ",
    //'conditions'  => "'$user_name_val%"
    //));
    $results_count = (count($results));//ページング処理２０個制限の前に数を数える
    $paginator_model = new PaginatorModel(
        array(
            'data' => $results,
            'limit' => $page['records_per_page'],
            'page' => $page['page_number'],
        )
    );

    //リスト作成
    $list = array();
    $all_list = array();
    $json_list = array();

    $paginator = $paginator_model->getPaginate();
    $results = $paginator->items;
    foreach ($results as $result) {
        //ChromePhp::log($result);
        $list['line_no'] = $result->tSaleOrderHistory->line_no;//注文番号
        $list['sale_order_date'] = $result->tSaleOrderHistory->sale_order_date;//注文日
        $list['rntl_sect_cd'] = $result->mSection->rntl_sect_name;//拠点ID
        $list['item_name'] = $result->tSaleOrderHistory->item_name;//商品名
        $list['color_cd'] = $result->tSaleOrderHistory->color_cd;//カラーコード
        $list['size_cd'] = $result->tSaleOrderHistory->size_cd;//サイズコード
        $list['quantity'] = $result->tSaleOrderHistory->quantity;//数量
        array_push($all_list, $list);//$all_listaに$listをpush
    }
    $page_list['records_per_page'] = $page['records_per_page'];
    $page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = $results_count;//全件数をアップ
    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;
    //ChromePhp::log($all_list);

    echo json_encode($json_list);
});



