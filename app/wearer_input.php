<?php
//use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Mvc\Model\Query;

/*
 * 検索項目：契約No(入力系)
 */
$app->post('/agreement_no_input', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //--- 検索条件 ---//
    // 契約マスタ. 企業ID
    array_push($query_list, "MContract.corporate_id = '".$auth['corporate_id']."'");
    // 契約マスタ. レンタル契約フラグ
    array_push($query_list, "MContract.rntl_cont_flg = '1'");
    // 契約リソースマスタ. 企業ID
    array_push($query_list, "MContractResource.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ.企業ID
    array_push($query_list, "MAccount.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ. ユーザーID
    array_push($query_list, "MAccount.user_id = '".$auth['user_id']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $results = MContract::query()
        ->where($query)
        ->columns(array('MContract.*', 'MContractResource.*', 'MAccount.*'))
        ->leftJoin('MContractResource', 'MContract.corporate_id = MContractResource.corporate_id AND MContract.rntl_cont_no = MContractResource.rntl_cont_no')
        ->join('MAccount', 'MAccount.accnt_no = MContractResource.accnt_no')
        ->execute();

    // デフォルトは空を設定
    $list['rntl_cont_no'] = null;
    $list['rntl_cont_name'] = null;
    array_push($all_list, $list);
    foreach ($results as $result) {
        $list['rntl_cont_no'] = $result->mContract->rntl_cont_no;
        $list['rntl_cont_name'] = $result->mContract->rntl_cont_name;
        array_push($all_list, $list);
    }

    $json_list['agreement_no_list'] = $all_list;
    echo json_encode($json_list);
});

/*
 * 着用者入力各フォーム
 */
$app->post('/wearer_input', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');
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
        array_push($sex_kbn_list, $list);
    }
    //--性別ここまで

    //拠点--ここから
    $query_list = array();
    //--- 検索条件 ---//
    // 契約マスタ. 企業ID
    array_push($query_list, "MContract.corporate_id = '".$auth['corporate_id']."'");
    // 契約リソースマスタ. 企業ID
    array_push($query_list, "MContractResource.corporate_id = '".$auth['corporate_id']."'");
    // 契約リソースマスタ. レンタル契約No = 画面で選択されている契約No.
    array_push($query_list, "MContractResource.rntl_cont_no = '".$cond['agreement_no']."'");
    // アカウントマスタ.企業ID
    array_push($query_list, "MAccount.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ. ユーザーID
    array_push($query_list, "MAccount.user_id = '".$auth['user_id']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
    $m_contract_resources = MContract::query()
        ->where($query)
        ->columns(array('MContractResource.rntl_sect_cd'))
        ->leftJoin('MContractResource', 'MContract.corporate_id = MContractResource.corporate_id')
        ->join('MAccount', 'MAccount.accnt_no = MContractResource.accnt_no')
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
    if ($rntl_sect_cd) {
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
        array_push($m_section_list, $list);
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
        array_push($job_type_list, $list);
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
        array_push($m_shipment_to_list, $list);
    }
    //出荷先--ここまで
    $json_list['m_shipment_to_list'] = $m_shipment_to_list;
    $json_list['job_type_list'] = $job_type_list;
    $json_list['sex_kbn_list'] = $sex_kbn_list;
    $json_list['m_section_list'] = $m_section_list;
    echo json_encode($json_list);
});

/*
 * 「拠点」のセレクトボックス変更時
 */
$app->post('/change_section', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');
    $cond = $params['cond'];

    $query_list = array();
    $list = array();
    $json_list = array();
    $m_shipment_to_list = array();
    //画面の「郵便番号」欄、「住所」欄の内容を動的に書き換える
    //--- 検索条件 ---//
    // 出荷先マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MShipmentTo.corporate_id = '".$auth['corporate_id']."'");
    // 出荷先マスタ．レンタル契約No.　＝　画面で選択されている契約No.
    array_push($query_list, "MShipmentTo.rntl_cont_no = '".$cond['agreement_no']."'");

    //出荷先」のセレクトボックスが「支店店舗と同じ」以外が選択状態の場合
    if ($cond['m_shipment_to_name'] != '支店店舗と同じ') {
        $m_shipment_to = explode(',', $cond['m_shipment_to']);
        //出荷先マスタ．出荷先コード　＝　画面で選択されている出荷先の出荷先コード　AND
        array_push($query_list, "MShipmentTo.ship_to_cd = '".$m_shipment_to[0]."'");
        //出荷先マスタ．出荷先支店コード　＝　画面で選択されている出荷先の出荷先支店コード
        array_push($query_list, "MShipmentTo.ship_to_brnch_cd = '".$m_shipment_to[1]."'");
    }
    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //--- クエリー実行・取得 ---//
    $q_str = MShipmentTo::query()
        ->where($query)
        ->columns(array('MShipmentTo.*'));
    // 「出荷先」のセレクトボックスが「支店店舗と同じ」が選択状態の場合
    if ($cond['m_shipment_to_name'] == '支店店舗と同じ') {
        $q_str->join('MSection', 'MShipmentTo.ship_to_cd = MSection.std_ship_to_cd AND MShipmentTo.ship_to_brnch_cd = MSection.std_ship_to_brnch_cd');
    }
    // 出荷先マスタ．出荷先コード　＝　部門マスタ．標準出荷先コード AND 出荷先マスタ．出荷先支店コード　＝　部門マスタ．標準出荷先支店コード
    $results = $q_str->execute();

    foreach ($results as $result) {
        $list['ship_to_cd'] = $result->ship_to_cd;
        $list['ship_to_brnch_cd'] = $result->ship_to_brnch_cd;
        $list['cust_to_brnch_name1'] = $result->cust_to_brnch_name1;
        $list['cust_to_brnch_name2'] = $result->cust_to_brnch_name2;
        $list['zip_no'] = $result->zip_no;
        $list['address'] = $result->address1.$result->address2.$result->address3.$result->address4;
        array_push($m_shipment_to_list, $list);
    }
    $json_list['change_m_shipment_to_list'] = $m_shipment_to_list;
    echo json_encode($json_list);
});

/*
 *  着用者のみ登録して終了
 */
$app->post('/input_insert', function () use ($app) {

//    $params = json_decode(file_get_contents("php://input"), true);
    $params = json_decode($_POST['data'], true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');
    $cond = $params['cond'];
    $query_list = array();
    $list = array();
    $json_list = array();
    $error_list = array();

    //更新可否チェック（更新可否チェック仕様書）

    //  入力された内容を元に、着用者基本マスタトラン、着用者商品マスタトラン、発注情報トランに登録を行う。
    //--- 検索条件 ---//
    //  アカウントマスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MAccount.corporate_id = '".$auth['corporate_id']."'");
    //  アカウントマスタ．ユーザーID　＝　ログインしているアカウントのユーザーID　AND
    array_push($query_list, "MAccount.user_id = '".$auth['user_id']."'");
    //　契約マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MContract.corporate_id = '".$auth['corporate_id']."'");
    //　契約マスタ．レンタル契約フラグ　＝　契約対象 AND
    array_push($query_list, "MContract.rntl_cont_flg = '1'");
    //  契約リソースマスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "MContractResource.corporate_id = '".$auth['corporate_id']."'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);

    //--- クエリー実行・取得 ---//
//    $results = MContract::query()
//        ->where($query)
//        ->columns(array('MContractResource.*'))
//        ->leftJoin('MContractResource','MContract.corporate_id = MContractResource.corporate_id')
//        ->join('MAccount','MAccount.accnt_no = MContractResource.accnt_no')
//        ->execute();
//    if($results[0]->update_ok_flg == '0'){
//      array_push($error_list,'こちらの契約リソースは更新出来ません。');
//        $json_list['errors'] = $error_list;
//        return;
//    }
//    //汎用コードマスタから更新不可時間を取得
//    //--- 検索条件 ---//
//    $query_list = array();
//    // 汎用コードマスタ．分類コード　＝　更新不可時間
//    array_push($query_list, "cls_cd = '015'");
//
//    //sql文字列を' AND 'で結合
//    $query = implode(' AND ', $query_list);
//
//    //--- クエリー実行・取得 ---//
//    $m_gencode_results = MGencode::query()
//        ->where($query)
//        ->columns('*')
//        ->execute();
//    foreach ($m_gencode_results as $m_gencode_result) {
//        if($m_gencode_result->gen_cd =='1'){
//            //更新不可開始時間
//            $start = $m_gencode_result->gen_name;
//        }elseif($m_gencode_result->gen_cd =='2'){
//            //経過時間
//            $hour = $m_gencode_result->gen_name;
//
//        }
//    }
//    $now_datetime = date("YmdHis");
//    $now_date = date("Ymd");
//    $start_datetime = $now_date.$start;
//    $end_datetime = date("YmdHis", strtotime($start_datetime." + ".$hour." hour"));;
//    if(strtotime($start_datetime) <= strtotime($now_datetime)||strtotime($now_datetime) >= strtotime($end_datetime)){
//        array_push($error_list,'現在の時間は更新出来ません。');
//        $json_list['errors'] = $error_list;
//        return;
//    }
//
//    //契約Noのマスタチェック
//    $query_list = array();
//    // 契約マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
//    array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
//    // 契約マスタ．レンタル契約No.　＝　画面で選択されている契約No.
//    array_push($query_list,"rntl_cont_no = '".$cond['agreement_no']."'");
//
//    //sql文字列を' AND 'で結合
//    $query = implode(' AND ', $query_list);
//    //--- クエリー実行・取得 ---//
//    $mc_count = MContract::find(array(
//        'conditions' => $query
//	))->count();
//    //存在しない場合NG
//    if($mc_count == 0){
//        array_push($error_list,'契約Noの値が不正です。');
//    }
//    $query_list = array();
//    //社員コードのマスタチェック(社員コードありの場合のみ)
//    if($cond['cster_emply_cd']){
//        // 着用者基本マスタ．客先社員コード ＝ 画面で入力された社員コード AND
//        array_push($query_list,"cster_emply_cd = '".$cond['cster_emply_cd']."'");
//        // 着用者基本マスタ．着用者状況区分 ＝ 稼働
//        array_push($query_list,"werer_sts_kbn = '1'");
//
//        //sql文字列を' AND 'で結合
//        $query = implode(' AND ', $query_list);
//        //--- クエリー実行・取得 ---//
//        $m_wearer_std_count = MWearerStd::find(array(
//            'conditions' => $query
//        ))->count();
//        //存在する場合NG
//        if($m_wearer_std_count > 0){
//          array_push($error_list,'社員コードの値が不正です。');
//        }
//    }
//    //拠点のマスタチェック
//    $query_list = array();
//    // 部門マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
//    array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
//    // 部門マスタ．レンタル契約No.　＝　画面で選択されている契約No.
//    array_push($query_list,"rntl_cont_no = '".$cond['agreement_no']."'");
//    // 部門マスタ．レンタル部門コード　＝　画面で選択されている拠点
//    array_push($query_list,"rntl_sect_cd = '".$cond['rntl_sect_cd']."'");
//
//    //sql文字列を' AND 'で結合
//    $query = implode(' AND ', $query_list);
//    //--- クエリー実行・取得 ---//
//    $m_section = MSection::find(array(
//        'conditions' => $query
//    ));
//    $m_section_count = $m_section->count();
//    //存在しない場合NG
//    if($m_section_count == 0){
//          array_push($error_list,'拠点の値が不正です。');
//    }
//    //貸与パターンのマスタチェック
//    $query_list = array();
//    // 職種マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
//    array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
//    // 職種マスタ．レンタル契約No.　＝　画面で選択されている契約No.
//    array_push($query_list,"rntl_cont_no = '".$cond['agreement_no']."'");
//    // 職種マスタ．レンタル部門コード　＝　画面で選択されている貸与パターン
//    array_push($query_list,"job_type_cd = '".$cond['job_type']."'");
//
//    //sql文字列を' AND 'で結合
//    $query = implode(' AND ', $query_list);
//    //--- クエリー実行・取得 ---//
//    $m_job_type_cnt = MJobType::find(array(
//        'conditions' => $query
//    ))->count();
//    //存在しない場合NG
//    if($m_job_type_cnt == 0){
//          array_push($error_list,'貸与パターンの値が不正です。');
//    }
//    //出荷先のマスタチェック
//    $query_list = array();
//    // 出荷先マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
//    array_push($query_list,"corporate_id = '".$auth['corporate_id']."'");
//
//    if($cond['ship_to_cd']){
//        // 出荷先マスタ．出荷先コード　＝　画面で選択されている出荷先コード
//        array_push($query_list,"ship_to_cd = '".$cond['ship_to_cd']."'");
//        // 出荷先マスタ．出荷先支店コード　＝　画面で選択されている出荷先支店コード
//        array_push($query_list,"ship_to_brnch_cd = '".$cond['ship_to_brnch_cd']."'");
//    }else{
//        // 部門マスタ．標準出荷先コード
//        array_push($query_list,"ship_to_cd = '".$m_section->std_ship_to_cd."'");
//        // 部門マスタ．標準出荷先支店コード
//        array_push($query_list,"ship_to_cd = '".$m_section->std_ship_to_brnch_cd."'");
//    }
//    //sql文字列を' AND 'で結合
//    $query = implode(' AND ', $query_list);
//    //--- クエリー実行・取得 ---//
//    $m_shipment_to_cnt = MShipmentTo::find(array(
//        'conditions' => $query
//    ))->count();
//
//    //存在しない場合NG
//    if($m_shipment_to_cnt == 0){
//          array_push($error_list,'出荷先の値が不正です。');
//    }

    if (byte_cnt($cond['cster_emply_cd']) > 10) {
        array_push($error_list, '社員コードの文字数が多すぎます。');
    }

    if (byte_cnt($cond['werer_name']) > 10) {
        array_push($error_list, '着用者名の文字数が多すぎます。');
    }

    if (byte_cnt($cond['werer_name_kana']) > 10) {
        array_push($error_list, '着用者名(カナ)の文字数が多すぎます。');
    }

//    DB登録
//    if($error_list){
//        $json_list['errors'] = $error_list;
//        echo json_encode($json_list);
//        return true;
//    }
    $transaction = $app->transactionManager->get();
    //着用者基本マスタトラン
    $m_wearer_std_tran = new MWearerStdTran();
    $m_wearer_std_tran->corporate_id = $auth['corporate_id']; //企業ID

    $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("select nextval('werer_cd')"));
    $m_wearer_std_tran->werer_cd = str_pad($results[0]->nextval, 10, '0', STR_PAD_LEFT); //着用者コード

    $m_wearer_std_tran->rntl_cont_no = $cond['agreement_no']; //レンタル契約No.
    $m_wearer_std_tran->rntl_sect_cd = $cond['rntl_sect_cd']; //レンタル部門コード
    if ($m_wearer_std_tran->save() == false) {
        array_push($error_list, '着用者の登録に失敗しました。');
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);

        return true;
    } else {
        $transaction->commit();
    }
    echo json_encode($json_list);
});

function byte_cnt($data)
{
    //変換前文字コード
    $bf = 'UTF-8';
    //変換後文字コード
    $af = 'Shift-JIS';

    return strlen(bin2hex(mb_convert_encoding($data, $af, $bf))) / 2;
}
