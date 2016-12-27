<?php
//use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/**
 * ホーム
 */
$app->post('/home', function ()use($app){
    $list = array();
    $all_list = array();
    $json_list = array();
    $corporate_id = $app->session->get("auth")['corporate_id'];

    //発注未送信件数

    // 発注区分=貸与で発注情報トランのデータが存在しない場合は対象外とする
    // パターン１ 発注区分 = 貸与 発注トランに発注番号を省いた件数 未送信
    $arg_str = "";
    $arg_str .= "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (T2.order_req_no) ";
    $arg_str .= "T2.order_req_no as as_wst_order_req_no,";
    $arg_str .= "T2.order_sts_kbn as as_wst_order_sts_kbn,";
    $arg_str .= "T2.snd_kbn as as_snd_kbn,";
    $arg_str .= "T1.corporate_id as as_corporate_id,";
    $arg_str .= "T1.rntl_cont_no as as_rntl_cont_no";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT * FROM m_wearer_std_tran WHERE order_sts_kbn = '1') as T2";
    $arg_str .= " INNER JOIN (SELECT * FROM t_order_tran) as T1";
    $arg_str .= " ON T2.order_req_no = T1.order_req_no";
    $arg_str .= " WHERE ";
    $arg_str .= "T2.snd_kbn = '0'";
    $arg_str .= " AND T1.corporate_id = '".$corporate_id."'";
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_wst_order_req_no ASC";
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];

    // パターン２　発注区分 = 貸与以外 未送信
    $arg_str = "";
    $arg_str .= "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT distinct on (T2.order_req_no) ";
    $arg_str .= "T2.order_req_no as as_wst_order_req_no,";
    $arg_str .= "T2.order_sts_kbn as as_wst_order_sts_kbn,";
    $arg_str .= "T2.snd_kbn as as_snd_kbn,";
    $arg_str .= "T1.order_req_no as as_order_req_no,";
    $arg_str .= "T1.corporate_id as as_corporate_id,";
    $arg_str .= "T1.rntl_cont_no as as_rntl_cont_no";
    $arg_str .= " FROM ";
    $arg_str .= "(SELECT * FROM m_wearer_std_tran WHERE NOT order_sts_kbn = '1') as T2";
    $arg_str .= " LEFT JOIN (SELECT * FROM t_order_tran) as T1";
    $arg_str .= " ON T2.order_req_no = T1.order_req_no";
    $arg_str .= " WHERE ";
    $arg_str .= "T2.snd_kbn = '0'";
    $arg_str .= " AND T1.corporate_id = '".$corporate_id."'";
    $arg_str .= ") as distinct_table";
    $arg_str .= " ORDER BY as_wst_order_req_no ASC";

    $m_wearer_std_tran2 = new MWearerStdTran();
    $results2 = new Resultset(null, $m_wearer_std_tran2, $m_wearer_std_tran2->getReadConnection()->query($arg_str));
    $result_obj2 = (array)$results2;
    $results_cnt2 = $result_obj2["\0*\0_count"];

    //発注未送信件数
    //パターン１とパターン２を足した件数
    $emply_cd_no_regist_cnt = $results_cnt + $results_cnt2;

    //未受領件数
    $no_recieve_cnt = TDeliveryGoodsStateDetails::find(array(
        "conditions" => "corporate_id = ?1 AND receipt_status = '2'",
        "bind"	=> array(1 => $corporate_id)
    ))->count();

    //未返却件数
    $no_return_cnt = TReturnedPlanInfo::find(array(
        "conditions" => "corporate_id = ?1 AND return_status = '1'",
        "bind"	=> array(1 => $corporate_id)
    ))->count();


    $json_list['emply_cd_no_regist_cnt'] = $emply_cd_no_regist_cnt;
    $json_list['no_recieve_cnt'] = $no_recieve_cnt;
    $json_list['no_return_cnt'] = $no_return_cnt;
    //お知らせ
    $now = date( "Y/m/d H:i:s", time() );
    $results = TInfo::find(array(
        "conditions" => "open_date < ?1 AND close_date > ?1",
        "bind"	=> array(1 => $now),
        'order'	  => "display_order asc"
    ));
    // $results = TInfo::find();
    foreach ($results as $result) {
        // $list['open_date'] = date('Y/m/d H:i',strtotime($result->open_date));
        // $list['message'] = '・'.nl2br(htmlspecialchars( $result->message, ENT_QUOTES, 'UTF-8'));
        $list['message'] = '・'.$result->message;
        array_push($all_list,$list);
    }
    $json_list['info_list'] = $all_list;

    //ボタン表示非表示制御
    if($app->session->get("auth")['button1_use_flg']==1){$json_list['button1_use_flg']=1;};
    if($app->session->get("auth")['button2_use_flg']==1){$json_list['button2_use_flg']=1;};
    if($app->session->get("auth")['button3_use_flg']==1){$json_list['button3_use_flg']=1;};
    if($app->session->get("auth")['button4_use_flg']==1){$json_list['button4_use_flg']=1;};
    if($app->session->get("auth")['button5_use_flg']==1){$json_list['button5_use_flg']=1;};
    if($app->session->get("auth")['button6_use_flg']==1){$json_list['button6_use_flg']=1;};
    if($app->session->get("auth")['button7_use_flg']==1){$json_list['button7_use_flg']=1;};
    if($app->session->get("auth")['button8_use_flg']==1){$json_list['button8_use_flg']=1;};
    if($app->session->get("auth")['button9_use_flg']==1){$json_list['button9_use_flg']=1;};
    if($app->session->get("auth")['button10_use_flg']==1){$json_list['button10_use_flg']=1;};
    if($app->session->get("auth")['button11_use_flg']==1){$json_list['button11_use_flg']=1;};
    if($app->session->get("auth")['button12_use_flg']==1){$json_list['button12_use_flg']=1;};
    if($app->session->get("auth")['button13_use_flg']==1){$json_list['button13_use_flg']=1;};
    if($app->session->get("auth")['button14_use_flg']==1){$json_list['button14_use_flg']=1;};
    if($app->session->get("auth")['button15_use_flg']==1){$json_list['button15_use_flg']=1;};
    if($app->session->get("auth")['button16_use_flg']==1){$json_list['button16_use_flg']=1;};
    if($app->session->get("auth")['button17_use_flg']==1){$json_list['button17_use_flg']=1;};
    if($app->session->get("auth")['button18_use_flg']==1){$json_list['button18_use_flg']=1;};
    if($app->session->get("auth")['button19_use_flg']==1){$json_list['button19_use_flg']=1;};
    if($app->session->get("auth")['button20_use_flg']==1){$json_list['button20_use_flg']=1;};
    if($app->session->get("auth")['button21_use_flg']==1){$json_list['button21_use_flg']=1;};
    if($app->session->get("auth")['button22_use_flg']==1){$json_list['button22_use_flg']=1;};
    if($app->session->get("auth")['button23_use_flg']==1){$json_list['button23_use_flg']=1;};
    if($app->session->get("auth")['button24_use_flg']==1){$json_list['button24_use_flg']=1;};
    if($app->session->get("auth")['button25_use_flg']==1){$json_list['button25_use_flg']=1;};
    if($app->session->get("auth")['button26_use_flg']==1){$json_list['button26_use_flg']=1;};
    if($app->session->get("auth")['button27_use_flg']==1){$json_list['button27_use_flg']=1;};
    if($app->session->get("auth")['button28_use_flg']==1){$json_list['button28_use_flg']=1;};
    if($app->session->get("auth")['button29_use_flg']==1){$json_list['button29_use_flg']=1;};
    if($app->session->get("auth")['button30_use_flg']==1){$json_list['button30_use_flg']=1;};

    //document処理
    //ChromePhp::log(DOCUMENT_UPLOAD.$corporate_id.'/meta.txt');
    //ChromePhp::log(file_exists(DOCUMENT_UPLOAD.$corporate_id.'/meta.txt'));
    if(file_exists(DOCUMENT_UPLOAD.$corporate_id.'/meta.txt')){

        //企業idディレクトリ内のメタ.txtを取得
        $fileName = DOCUMENT_UPLOAD.$corporate_id.'/meta.txt';

        $file = file($fileName);
        mb_convert_variables("UTF-8", "SJIS-win", $file);

        //$chk_file = $file;
        //unset($chk_file[0]); //チェック時はヘッダーを無視する
        $tmp_manual_list = array();
        $manual_list = array();

        if(count($file) > 0){
        foreach($file as $item){
            $tmp_manual_list[] = explode(',',$item);
        }
        foreach($tmp_manual_list as $list){
            $manual_list[] = array(
                'name' => $list[0],
                'file' => preg_replace('/\r\n/', '', $list[1]),
                'corporate' => $corporate_id
            );
        }
        //ChromePhp::log($manual_list);
        $json_list['manual_list'] = $manual_list;
        }
    }
    json_encode($json_list);
    echo json_encode($json_list);
});


$app->post('/home_manual', function ()use($app){

    $params = json_decode($_POST['data'], true);

    // アカウントセッション取得
    $auth = $app->session->get("auth");
    $cond = $params['cond'];

    $filename = attachmentFileName($cond['name']);

    //ファイルの存在を確認し処理を実行
    header("Content-Type: application/octet-stream");
    if(file_exists(DOCUMENT_UPLOAD.$cond['corporate']."/".$cond['file'])){
        //拡張子取り出し
        $ext = substr(strrchr($cond['file'], '.'), 0);
        //実体ファイルセット
        $fileName = DOCUMENT_UPLOAD.$cond['corporate']."/".$cond['file'];
        //ファイル名セット
        header("Content-Disposition: attachment; filename=".$filename.$ext);
    }else{
        //実体ファイルがない場合はこちらの処理
        $fileName = DOCUMENT_UPLOAD."/file_no.pdf";
        header("Content-Disposition: attachment; filename=nofile.pdf");
    }
    //ファイルのダウンロード
    readfile($fileName);
});


function attachmentFileName($fileName)
{
    $outputFilename = $fileName;
    $outputFilename = str_replace([' ', '\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $outputFilename);
    if(mb_convert_encoding($outputFilename, "US-ASCII", "UTF-8") == $outputFilename) {
        $outputFilename = rawurlencode($outputFilename);
    }else{
        $ua = $_SERVER['HTTP_USER_AGENT'];

        if (strpos($ua, 'MSIE') !== false && strpos($ua, 'Opera') === false) {
            $outputFilename = mb_convert_encoding($outputFilename, "SJIS-win", "UTF-8");
        } elseif (strpos($ua, 'Firefox') !== false ||
            strpos($ua, "Chrome") !== false ||
            strpos($ua, 'Opera') !== false
        ) {
            //$outputFilename = '=?UTF-8?B?' . base64_encode($outputFilename) . '?=';
        } elseif (strpos($ua, "Safari") !== false ) {
        } else {
            $outputFilename = mb_convert_encoding($outputFilename, "SJIS-win", "UTF-8");
        }
    }
    return $outputFilename;
}