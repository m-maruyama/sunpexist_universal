<?php

use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;


/*
 * 注文履歴
 */
$app->post('/purchase_history/search', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);

    // アカウントセッション取得
    $auth = $app->session->get('auth');
    if ($auth['user_type'] == '1') {
        $json_list['redirect'] = $auth['user_type'];
        echo json_encode($json_list);

        return;
    }

    $cond = $params['cond'];
    $page = $params['page'];
    $query_list = array();//追加

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



    //$user_id_val = $cond['user_id'];
    //$user_name_val = $cond['user_name'];
    //$mail_address = $cond['mail_address'];
    ChromePhp::log($sort_key);

    //全検索
    $results = TSaleOrderHistory::find(array(
        'order' => "$sort_key $order",
        //'order' => "$sort_key $order",
        //'conditions' => "corporate_id LIKE '$corporate_id_val' AND user_name LIKE '%$user_name_val%' AND user_id LIKE '$user_id_val%' AND mail_address LIKE '$mail_address%' AND del_flg LIKE '0'",
        //'conditions'  => "'$user_name_val%"
    ));
    //ChromePhp::log($results);
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
        $list['line_no'] = $result->line_no;//注文番号
        $list['sale_order_date'] = $result->sale_order_date;//注文日
        $list['rntl_sect_cd'] = $result->rntl_sect_cd;//拠点ID
        $list['item_name'] = $result->item_name;//商品名
        $list['color_cd'] = $result->color_cd;//カラーコード
        $list['size_cd'] = $result->size_cd;//サイズコード
        $list['quantity'] = $result->quantity;//数量
        array_push($all_list, $list);//$all_listaに$listをpush
    }
    $page_list['records_per_page'] = $page['records_per_page'];
    $page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = $results_count;//全件数をアップ
    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;
    ChromePhp::log($all_list);

    echo json_encode($json_list);
});

