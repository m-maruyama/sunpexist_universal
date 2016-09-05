<?php
//use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;


/**
 * 検索項目：契約No(入力系)
 */
$app->post('/agreement_no_input', function ()use($app) {
    $params = json_decode(file_get_contents("php://input"), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get("auth");

    //--- 検索条件 ---//
    // 契約マスタ. 企業ID
    array_push($query_list,"MContract.corporate_id = '".$auth['corporate_id']."'");
    // 契約マスタ. レンタル契約フラグ
    array_push($query_list,"MContract.rntl_cont_flg = '1'");
    // 契約リソースマスタ. 企業ID
    array_push($query_list,"MContractResource.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ.企業ID
    array_push($query_list,"MAccount.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ. ユーザーID
    array_push($query_list,"MAccount.user_id = '".$auth['user_id']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $results = MContract::query()
        ->where($query)
        ->columns(array('MContract.*','MContractResource.*','MAccount.*'))
        ->leftJoin('MContractResource','MContract.corporate_id = MContractResource.corporate_id')
        ->join('MAccount','MAccount.accnt_no = MContractResource.accnt_no')
        ->execute();

    // デフォルトは空を設定
    $list['rntl_cont_no'] = null;
    $list['rntl_cont_name'] = null;
    array_push($all_list,$list);

    foreach ($results as $result) {
        $list['rntl_cont_no'] = $result->mContract->rntl_cont_no;
        $list['rntl_cont_name'] = $result->mContract->rntl_cont_name;
        array_push($all_list,$list);
    }

    $json_list['agreement_no_list'] = $all_list;
    echo json_encode($json_list);
});

/**
 * 着用者入力各フォーム
 */
$app->post('/wearer_input', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);
	// アカウントセッション取得
	$auth = $app->session->get("auth");
	$cond = $params['cond'];

    $query_list = array();
    $list = array();
    $json_list = array();

    //--性別ここから
    $sex_kbn_list = array();
    //--- 検索条件 ---//
    // 汎用コードマスタ. 分類コード
    array_push($query_list, "cls_cd = '004'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $m_gencode_results = MGencode::query()
        ->where($query)
        ->columns('*')
        ->execute();
    foreach ($m_gencode_results as $m_gencode_result) {
        $list['cls_cd'] = $m_gencode_result->cls_cd;
        $list['gen_name'] = $m_gencode_result->gen_name;
        array_push($sex_kbn_list,$list);
    }
    //--性別ここまで

    //拠点--ここから
    $query_list = array();
    //--- 検索条件 ---//
    // 契約マスタ. 企業ID
    array_push($query_list,"MContract.corporate_id = '".$auth['corporate_id']."'");
    // 契約リソースマスタ. 企業ID
    array_push($query_list,"MContractResource.corporate_id = '".$auth['corporate_id']."'");
    // 契約リソースマスタ. レンタル契約No = 画面で選択されている契約No.
    array_push($query_list,"MContractResource.rntl_cont_no = '".$cond['agreement_no']."'");
    // アカウントマスタ.企業ID
    array_push($query_list,"MAccount.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ. ユーザーID
    array_push($query_list,"MAccount.user_id = '".$auth['user_id']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $m_contract_resources = MContract::query()
        ->where($query)
        ->columns(array('MContractResource.rntl_sect_cd'))
        ->leftJoin('MContractResource','MContract.corporate_id = MContractResource.corporate_id')
        ->join('MAccount','MAccount.accnt_no = MContractResource.accnt_no')
        ->execute();
    $rntl_sect_cd = null;
    foreach ($m_contract_resources as $m_contract_resource) {
        $rntl_sect_cd = $m_contract_resource->rntl_sect_cd;
    }
    $query_list = array();
    $list = array();
    $m_section_list = array();
    //--- 検索条件 ---//
    // 部門マスタ. 企業ID
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    // 部門マスタ. レンタル契約No
    array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");
    if($rntl_sect_cd){
        // 部門マスタ. レンタル部門コード
        array_push($query_list, "rntl_sect_cd = '".$rntl_sect_cd."'");
    }

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $m_section_results = MSection::query()
        ->where($query)
        ->columns('*')
        ->execute();

    foreach ($m_section_results as $m_section_result) {
        $list['rntl_sect_cd'] = $m_section_result->rntl_sect_cd;
        $list['rntl_sect_name'] = $m_section_result->rntl_sect_name;
        array_push($m_section_list,$list);
    }
    //--拠点ここまで

    //貸与パターン--ここから
    $query_list = array();
    $list = array();
    $job_type_list = array();
    //--- 検索条件 ---//
    // 職種マスタ. 企業ID
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    // 職種マスタ. レンタル契約No
    array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $m_job_type_results = MJobType::query()
        ->where($query)
        ->columns('*')
        ->execute();

    foreach ($m_job_type_results as $m_job_type_result) {
        $list['job_type_cd'] = $m_job_type_result->job_type_cd;
        $list['job_type_name'] = $m_job_type_result->job_type_name;
        $list['sp_job_type_flg'] = $m_job_type_result->sp_job_type_flg;
        array_push($job_type_list,$list);
    }
    //貸与パターン--ここまで

    //出荷先--ここから
    $query_list = array();
    $list = array();
    $m_shipment_to_list = array();
    //--- 検索条件 ---//
    // 出荷先マスタ. 企業ID
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    // 出荷先マスタ. レンタル契約No
    array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $m_shipment_to_results = MShipmentTo::query()
        ->where($query)
        ->columns('*')
        ->execute();
    //一件目に「支店店舗と同じ:部門マスタ.標準出荷先コード:部門マスタ.標準出荷先支店コード」という選択肢とセレクトボックスを表示。
    foreach ($m_shipment_to_results as $m_shipment_to_result) {
        $list['ship_to_cd'] = $m_shipment_to_result->ship_to_cd;
        $list['ship_to_brnch_cd'] = $m_shipment_to_result->ship_to_brnch_cd;
        $list['cust_to_brnch_name1'] = $m_shipment_to_result->cust_to_brnch_name1;
        $list['cust_to_brnch_name2'] = $m_shipment_to_result->cust_to_brnch_name2;
        $list['zip_no'] = $m_shipment_to_result->zip_no;
        $list['address1'] = $m_shipment_to_result->address1;
        $list['address2'] = $m_shipment_to_result->address2;
        $list['address3'] = $m_shipment_to_result->address3;
        $list['address4'] = $m_shipment_to_result->address4;
        array_push($m_shipment_to_list,$list);
    }
    //出荷先--ここまで
    $json_list['m_shipment_to_list'] = $m_shipment_to_list;
    $json_list['job_type_list'] = $job_type_list;
    $json_list['sex_kbn_list'] = $sex_kbn_list;
    $json_list['m_section_list'] = $m_section_list;
    echo json_encode($json_list);
});


/**
 *  着用者のみ登録して終了
 */
$app->post('/input_insert', function ()use($app) {

    $params = json_decode(file_get_contents("php://input"), true);
    // アカウントセッション取得
    $auth = $app->session->get("auth");
    $cond = $params['cond'];

    $query_list = array();
    $list = array();
    $json_list = array();
    $m_shipment_to_list = array();
    //画面の「郵便番号」欄、「住所」欄の内容を動的に書き換える
    //--- 検索条件 ---//
    // 出荷先マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list,"MShipmentTo.corporate_id = '".$auth['corporate_id']."'");
    // 出荷先マスタ．レンタル契約No.　＝　画面で選択されている契約No.
    array_push($query_list,"MShipmentTo.rntl_cont_no = '".$cond['agreement_no']."'");

    //出荷先」のセレクトボックスが「支店店舗と同じ」以外が選択状態の場合
    if($cond['m_shipment_to_name']!='支店店舗と同じ'){
        $m_shipment_to = explode(',',$cond['m_shipment_to']);
        //出荷先マスタ．出荷先コード　＝　画面で選択されている出荷先の出荷先コード　AND
        array_push($query_list,"MShipmentTo.ship_to_cd = '".$m_shipment_to[0]."'");
        //出荷先マスタ．出荷先支店コード　＝　画面で選択されている出荷先の出荷先支店コード
        array_push($query_list,"MShipmentTo.ship_to_brnch_cd = '".$m_shipment_to[1]."'");
    }
    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //--- クエリー実行・取得 ---//
    $q_str = MShipmentTo::query()
        ->where($query)
        ->columns(array('MShipmentTo.*'));
        // 「出荷先」のセレクトボックスが「支店店舗と同じ」が選択状態の場合
    if($cond['m_shipment_to_name']=='支店店舗と同じ'){
        $q_str->join('MSection','MShipmentTo.ship_to_cd = MSection.std_ship_to_cd AND MShipmentTo.ship_to_brnch_cd = MSection.std_ship_to_brnch_cd');

    }
    ChromePhp::LOG($q_str);
    die();
    // 出荷先マスタ．出荷先コード　＝　部門マスタ．標準出荷先コード AND 出荷先マスタ．出荷先支店コード　＝　部門マスタ．標準出荷先支店コード
    $results = $q_str->execute();

    foreach ($results as $result) {
        $list['ship_to_cd'] = $result->ship_to_cd;
        $list['ship_to_brnch_cd'] = $result->ship_to_brnch_cd;
        $list['cust_to_brnch_name1'] = $result->cust_to_brnch_name1;
        $list['cust_to_brnch_name2'] = $result->cust_to_brnch_name2;
        $list['zip_no'] = $result->zip_no;
        $list['address'] = $result->address1.$result->address2.$result->address3.$result->address4;
        array_push($m_shipment_to_list,$list);
    }
    $json_list['change_m_shipment_to_list'] = $m_shipment_to_list;
    echo json_encode($json_list);
});

/**
 * 着用者入力各フォーム
 */
$app->post('/wearer_input_init', function ()use($app){
    $json_list['referrer'] = $_SERVER['HTTP_REFERRER'];
    echo json_encode($json_list);
});
