<?php
use Phalcon\Mvc\Model\Resultset;

/**
 * ホーム
 */
$app->post('/home', function ()use($app){
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

    //お知らせ
    $now = date( "Y/m/d H:i:s", time() );
    $results = TInfo::find(array(
        "conditions" => "open_date < ?1 AND close_date > ?1",
        "bind"	=> array(1 => $now),
        'order'	  => "display_order asc"
    ));
    // $results = TInfo::find();
    $list = array();
    $all_list = array();
    $json_list = array();
    foreach ($results as $result) {
        // $list['open_date'] = date('Y/m/d H:i',strtotime($result->open_date));
        // $list['message'] = '・'.nl2br(htmlspecialchars( $result->message, ENT_QUOTES, 'UTF-8'));
        $list['message'] = '・'.$result->message;
        array_push($all_list,$list);
    }
    $json_list['info_list'] = $all_list;
    $json_list['emply_cd_no_regist_cnt'] = $emply_cd_no_regist_cnt;
    $json_list['no_recieve_cnt'] = $no_recieve_cnt;
    $json_list['no_return_cnt'] = $no_return_cnt;
    json_encode($json_list);
    echo json_encode($json_list);
});