<?php

use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

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

    //sectionで一番若い契約Noを取得
    $rntl_cont_no_one = MSection::find(array(
        //'order' => "$sort_key $order",
        'conditions' => "corporate_id LIKE '$login_corporate_id'",
        "columns" => "rntl_cont_no",
        "limit" => 1
        //'conditions'  => "'$user_name_val%"
    ));
    foreach ($rntl_cont_no_one as $rntl_cont_value) {
        $rent['value'] = $rntl_cont_value->rntl_cont_no;
    }

    $rntl_cont_no_value = $rent['value'];//データベース上で一番若い番号の契約no

    if (!$cond['agreement_no'] == null) {
        $rntl_cont_no_value = $cond['agreement_no'];
    }//$cond['agreement_no']

    //ChromePhp::log($rntl_cont_no_value);

    $results = MSaleOrderItem::find(array(
        //'order' => "$sort_key $order",
        'conditions' => "corporate_id LIKE '$login_corporate_id' AND rntl_cont_no LIKE '$rntl_cont_no_value'",
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
    //ChromePhp::log($results_count);
    echo json_encode($json_list);
});


/*
 * 注文入力
 */
$app->post('/purchase_update', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);


    $cond = $params['cond'];
    $item = $params['item'];
    $total_record = $params['total_record'];


    $json_list = array();
    $error_list = array();
    $error = false;

    $auth = $app->session->get('auth');


    $transaction = $app->transactionManager->get();


    if ($error_list) {
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);

        return true;
    }

    $lastval = array();
    //注文商品をデータベースに登録
    for ($i = 1; $i <= $total_record; $i++) {
        if ($item[$i]['quantity'] >= 1) {//数量が１以上の場合
            $t_sale_order_history[$i] = new TSaleOrderHistory();
            $t_sale_order_history[$i]->setTransaction($transaction);

            $t_sale_order_history[$i]->corporate_id = $auth['corporate_id']; //コーポレートid
            $t_sale_order_history[$i]->rntl_cont_no = $item[$i]['rntl_cont_no'];
            $t_sale_order_history[$i]->rntl_sect_cd = $item[$i]['rntl_sect_cd'];
            $t_sale_order_history[$i]->sale_order_date = date('Y/m/d H:i:s.sss', time());
            $t_sale_order_history[$i]->item_cd = $item[$i]['item_cd'];
            $t_sale_order_history[$i]->color_cd = $item[$i]['color_cd'];
            $t_sale_order_history[$i]->size_cd = $item[$i]['size_cd'];
            $t_sale_order_history[$i]->item_name = $item[$i]['item_name'];
            $t_sale_order_history[$i]->piece_rate = $item[$i]['piece_rate'];
            $t_sale_order_history[$i]->quantity = $item[$i]['quantity'];
            $t_sale_order_history[$i]->total_amount = $item[$i]['total_amount'];
            $t_sale_order_history[$i]->accnt_no = $auth['accnt_no'];
            $t_sale_order_history[$i]->snd_kbn = 0;
            $t_sale_order_history[$i]->rgst_date = date('Y/m/d H:i:s.sss', time());
            $t_sale_order_history[$i]->rgst_user_id = $auth['user_id'];
            $t_sale_order_history[$i]->upd_user_id = $auth['user_id'];
            $t_sale_order_history[$i]->upd_pg_id = $auth['user_id'];
            $t_sale_order_history[$i]->upd_date = date('Y/m/d H:i:s.sss', time());

            if ($t_sale_order_history[$i]->save() == false) {
                $error_list['update'] = '注文入力に失敗しました。';
                $json_list['errors'] = $error_list;
                echo json_encode($json_list);
                $transaction->rollBack();
                return true;
            }
            $arg_str = "SELECT LASTVAL()";
            $results[$i] = new Resultset(null, $t_sale_order_history[$i], $t_sale_order_history[$i]->getReadConnection()->query($arg_str));

            //ChromePhp::log($results[$i][0]->lastval);
            array_push($lastval, $results[$i][0]->lastval);//最後に送ったシーケンス番号を配列にする
            //ChromePhp::log($lastval);
        }
    }
    $transaction->commit();
    $json_list['seq'] = $lastval;
    echo json_encode($json_list);

});
