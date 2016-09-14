<?php

use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/*
 * アカウント検索
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

    //$results = $app->modelsManager->createBuilder()
    //->where($query)
    //->from('m_account')
    //->columns(array('m_account.*'))
    //->orderBy($sort_key.' '.$order)
    //->getQuery()
        //->execute();
    $all_list = array();
    $json_list = array();

    //$builder = $app->modelsManager->createBuilder()
    //	->where($query)
    //  ->orderBy($sort_key.' '.$order);

    //$paginator_model = new PaginatorQueryBuilder(
    //		array(
    //			"builder"  => $builder,
    //			"limit" => $page['records_per_page'],
    //			"page" => $page['page_number']
    //		)
    //	);
    //$paginator = $paginator_model->getPaginate();

    //$corporate_id_val = $cond['corporate_id'];
    //$user_id_val = $cond['user_id'];
    //$user_name_val = $cond['user_name'];
    //$mail_address = $cond['mail_address'];

    //$account = MAccount::find();
    //ChromePhp::log(count($account));カウント
    //count($robots);
    //$conditions = "name = :name: AND type = :type:";
    //全検索
    //$results = MSaleOrderItem::find(array(
    //    'order' => "$sort_key $order",
    //    'conditions' => '',
        //'conditions'  => "'$user_name_val%"
    //));

    $results = MSaleOrderItem::find();

    //リスト作成
    $list = array();
    $all_list = array();
    $json_list = array();

    foreach ($results as $result) {
        $list['corporate_id'] = $result->corporate_id;//コーポレートid
        $list['rntl_cont_no'] = $result->rntl_cont_no;
        $list['item_cd'] = $result->item_cd;//ユーザー名称
        $list['color_cd'] = $result->color_cd;//ログイン表示名
        $list['size_cd'] = $result->size_cd;
        $list['item_name'] = $result->item_name;
        $list['piece_rate'] = $result->piece_rate;
        $list['image_file_name'] = $result->image_file_name;
        array_push($all_list, $list);//$all_listに$listをpush
    }
    //ChromePhp::log($all_list);

    //$page_list['records_per_page'] = $page['records_per_page'];
    //$page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = count($results);
    //$json_list['page'] = $page_list;
    $json_list['list'] = $all_list;

    echo json_encode($json_list);
});
