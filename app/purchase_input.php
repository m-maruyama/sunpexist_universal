<?php

use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/*
 * 商品入力
 */
$app->post('/purchase_input', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);

    // アカウントセッション取得
    $auth = $app->session->get('auth');
    if ($auth['user_type'] == '1') {
        $json_list['redirect'] = $auth['user_type'];
        echo json_encode($json_list);
        return;
    }


    $cond = $params['cond'];
    //$page = $params['page'];
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
    }


    $all_list = array();
    $json_list = array();

    $login_corporate_id = $auth['corporate_id'];

    $results = MSaleOrderItem::find(array(
        //'order' => "$sort_key $order",
        'conditions' => "corporate_id LIKE '$login_corporate_id'",
        //'conditions'  => "'$user_name_val%"
    ));
    //$results = MSaleOrderItem::find();



    //ChromePhp::log($results);


    $results_count = (count($results));
    //リスト作成
    $list = array();
    $all_list = array();
    $json_list = array();
    $roop_count = 1;
    foreach ($results as $result) {
        $list['counter'] = $roop_count++;
        $list['corporate_id'] = $result->corporate_id;//コーポレートid
        $list['rntl_cont_no'] = $result->rntl_cont_no;
        $list['item_cd'] = $result->item_cd;//アイテム
        $list['color_cd'] = $result->color_cd;//ログイン表示名
        $list['size_cd'] = $result->size_cd;
        $list['item_name'] = $result->item_name;
        $list['piece_rate'] = $result->piece_rate;
        $list['image_file_name'] = $result->image_file_name;
        array_push($all_list, $list);//$all_listに$listをpush
    }

    //$page_list['records_per_page'] = $page['records_per_page'];
    //$page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = $results_count;
    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;

    echo json_encode($json_list);
});
