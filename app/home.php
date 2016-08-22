<?php
use Phalcon\Mvc\Model\Resultset;

/**
 * ホーム
 */
$app->post('/home', function ()use($app){
    $list = array();
    $all_list = array();
    $json_list = array();
    $corporate_id = $app->session->get("auth")['corporate_id'];
    //正社員番号未登録件数
    $emply_cd_no_regist_cnt = MWearerStdTran::find(array(
        "conditions" => "corporate_id = ?1 AND werer_sts_kbn = '7'",
        "bind"	=> array(1 => $corporate_id)
    ))->count();

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
    json_encode($json_list);
    echo json_encode($json_list);
});