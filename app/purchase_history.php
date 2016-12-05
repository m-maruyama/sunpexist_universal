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

    array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");

    if (!empty($params['agreement_no'])) {
        array_push($query_list, "rntl_cont_no = '" . $params['agreement_no'] . "'");
    } else {
        $corporate_id = $auth['corporate_id'];
        $accnt_no = $auth['accnt_no'];
        //sectionで一番若い契約Noを取得
        $rntl_cont_no_one = MContract::find(array(
            //'order' => "$sort_key $order",
            'conditions' => "corporate_id = '$corporate_id' AND purchase_cont_flg = '1'",
            "columns" => "rntl_cont_no",
            "limit" => 1
            //'conditions'  => "'$user_name_val%"
        ));
        foreach ($rntl_cont_no_one as $rntl_cont_value) {
            $params['rntl_cont_no'] = $rntl_cont_value->rntl_cont_no;
        }
        array_push($query_list, "rntl_cont_no = '" . $params['rntl_cont_no'] . "'");
    }
    $query = implode(' AND ', $query_list);
    //ChromePhp::log($params['agreement_no']);
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
        $login_corporate_id = $auth['corporate_id'];

        //sectionで一番若い契約Noを取得
        $rntl_cont_no_one = MContract::find(array(
            //'order' => "$sort_key $order",
            'conditions' => "corporate_id = '$login_corporate_id' AND purchase_cont_flg = '1'",
            "columns" => "rntl_cont_no",
            "limit" => 1
            //'conditions'  => "'$user_name_val%"
        ));
        foreach ($rntl_cont_no_one as $rntl_cont_value) {
            $params['rntl_cont_no'] = $rntl_cont_value->rntl_cont_no;
        }

        array_push($query_list, "rntl_cont_no = '" . $params['rntl_cont_no'] . "'");
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

    //削除の場合
    if (isset($params['del'])) {

        $line_no = $params['line_no'];
        $ac = TSaleOrderHistory::find(array(
            'conditions' => "line_no = '" . $line_no . "'",
        ));

        $transaction = $app->transactionManager->get();

        if ($ac[0]->delete() == false) {
            $error_list['delete'] = '注文の削除に失敗しました。';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            return true;
        } else {
            $transaction->commit();
            echo json_encode($json_list);

            return true;
        }
        echo json_encode($json_list);
        return true;
    }
    
    //検索の場合
    $cond = $params['cond'];
    $page = $params['page'];
    $query_list = array();//追加

    //初期表示は一番若い契約のnoを入れる
    if (isset($cond['rntl_cont_no'])) {
    } else {
        $login_corporate_id = $auth['corporate_id'];

        //sectionで一番若い契約Noを取得
        $rntl_cont_no_one = MContract::find(array(
            //'order' => "$sort_key $order",
            'conditions' => "corporate_id = '$login_corporate_id' AND purchase_cont_flg = '1'",
            "columns" => "rntl_cont_no",
            "limit" => 1
            //'conditions'  => "'$user_name_val%"
        ));
        foreach ($rntl_cont_no_one as $rntl_cont_value) {
            $rent['value'] = $rntl_cont_value->rntl_cont_no;
        }
        $cond['rntl_cont_no'] = $rent['value'];//データベース上で一番若い番号の契約no
    }

    //---契約リソースマスター 0000000000フラグ確認処理---//
    //ログインid
    $login_id_session = $auth['corporate_id'];
    //アカウントno
    $accnt_no = $auth['accnt_no'];
    //画面で選択された契約no
    $agreement_no = $cond['rntl_cont_no'];

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
    if(isset($all_list)){
        if (in_array("0000000000", $all_list)) {
            $rntl_sect_cd_zero_flg = 1;
        } else {
            $rntl_sect_cd_zero_flg = 0;
        }
    }else {
        $rntl_sect_cd_zero_flg = 0;
    }


    //
    if (isset($auth['corporate_id'])) {
        array_push($query_list, "t_sale_order_history.corporate_id = '" . $auth['corporate_id'] . "'");
    }
    //契約no
    if (isset($cond['rntl_cont_no'])) {
        array_push($query_list, "t_sale_order_history.rntl_cont_no = '" . $cond['rntl_cont_no'] . "'");
    }
    //
    if (isset($cond['section'])) {
        array_push($query_list, "t_sale_order_history.rntl_sect_cd = '" . $cond['section'] . "'");
    }
    //この日付から
    if (isset($cond['order_day_from'])) {
        array_push($query_list, "t_sale_order_history.sale_order_date >= '" . $cond['order_day_from'] . "'");
    }
    //この日付まで
    if (isset($cond['order_day_to'])) {
        array_push($query_list, "t_sale_order_history.sale_order_date <= '" . $cond['order_day_to'] . " 23:59:59'");
    }

    //商品コード
    if (isset($cond['item_cd'])) {
        array_push($query_list, "t_sale_order_history.item_cd = '" . $cond['item_cd'] . "'");
    }
    //色コード
    if (isset($cond['item_color'])) {
        array_push($query_list, "t_sale_order_history.color_cd = '" . $cond['item_color'] . "'");
    }
    //サイズ
    if (isset($cond['item_size'])) {
        array_push($query_list, "t_sale_order_history.size_cd = '" . $cond['item_size'] . "'");
    }

    //ゼロ埋めがない場合、ログインアカウントの条件追加
    if($rntl_sect_cd_zero_flg == 0){
        array_push($query_list,"m_contract_resource.accnt_no = '$accnt_no'");
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
            $q_sort_key = 'as_line_no';
        }
        if ($sort_key == 'sale_order_date') {
            $q_sort_key = 'as_sale_order_date';
        }
        if ($sort_key == 'rntl_sect_name') {
            $q_sort_key = 'as_rntl_sect_name';
        }
        if ($sort_key == 'item_name') {
            $q_sort_key = 'as_item_name';
        }
        if ($sort_key == 'color_cd') {
            $q_sort_key = 'as_color_cd';
        }
        if ($sort_key == 'size_cd') {
            $q_sort_key = 'as_size_cd';
        }
        if ($sort_key == 'quantity') {
            $q_sort_key = 'as_quantity';
        }
    } else {
        //指定がなければ社員番号
        $sort_key = 'as_line_no';
        //$order = 'asc';
    }
    if ($order == 'asc') {
        $order = 'desc';
    } elseif ($order == 'desc') {
        $order = 'asc';
    }

    $all_list = array();
    $json_list = array();


    $arg_str = "SELECT ";
    $arg_str .= "t_sale_order_history.line_no as as_line_no,";
    $arg_str .= "t_sale_order_history.sale_order_date as as_sale_order_date,";
    $arg_str .= "t_sale_order_history.item_name as as_item_name,";
    $arg_str .= "t_sale_order_history.color_cd as as_color_cd,";
    $arg_str .= "t_sale_order_history.size_cd as as_size_cd,";
    $arg_str .= "t_sale_order_history.quantity as as_quantity,";
    $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
    $arg_str .= "t_sale_order_history.snd_kbn as as_snd_kbn";
    $arg_str .= " FROM t_sale_order_history";

    if($rntl_sect_cd_zero_flg == 1){
        $arg_str .= " INNER JOIN m_section";
        $arg_str .= " ON (t_sale_order_history.corporate_id = m_section.corporate_id";
        $arg_str .= " AND t_sale_order_history.rntl_cont_no = m_section.rntl_cont_no";
        $arg_str .= " AND t_sale_order_history.rntl_sect_cd = m_section.rntl_sect_cd)";
    }elseif($rntl_sect_cd_zero_flg == 0){
        $arg_str .= " INNER JOIN (m_section INNER JOIN m_contract_resource";
        $arg_str .= " ON m_section.corporate_id = m_contract_resource.corporate_id";
        $arg_str .= " AND m_section.rntl_cont_no = m_contract_resource.rntl_cont_no";
        $arg_str .= " AND m_section.rntl_sect_cd = m_contract_resource.rntl_sect_cd) ";
        $arg_str .= " ON (t_sale_order_history.corporate_id = m_section.corporate_id";
        $arg_str .= " AND t_sale_order_history.rntl_cont_no = m_section.rntl_cont_no";
        $arg_str .= " AND t_sale_order_history.rntl_sect_cd = m_section.rntl_sect_cd)";
    }

    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= " ORDER BY " . $q_sort_key . " " . $order;

    $t_sale_order_history = new TSaleOrderHistory();

    $results = new Resultset(null, $t_sale_order_history, $t_sale_order_history->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    $paginator_model = new PaginatorModel(
        array(
            "data" => $results,
            "limit" => $page['records_per_page'],
            "page" => $page['page_number']
        )
    );

    $results_count = (count($results));//ページング処理２０個制限の前に数を数える

    //リスト作成
    $list = array();
    $all_list = array();
    $json_list = array();

    $paginator = $paginator_model->getPaginate();
    $results = $paginator->items;
    foreach ($results as $result) {
        //ChromePhp::log($result);
        $list['line_no'] = $result->as_line_no;//注文番号
        $list['sale_order_date'] = $result->as_sale_order_date;//注文日
        $list['rntl_sect_cd'] = $result->as_rntl_sect_name;//拠点ID
        $list['item_name'] = $result->as_item_name;//商品名
        $list['color_cd'] = $result->as_color_cd;//カラーコード
        $list['size_cd'] = $result->as_size_cd;//サイズコード
        $list['quantity'] = $result->as_quantity;//数量
        $list['snd_kbn'] = $result->as_snd_kbn;//送信区分
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