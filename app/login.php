<?php
use Phalcon\Mvc\Model\Resultset;


/**
 * ログイン
 */
$app->post('/login', function ()use($app) {
    $params = json_decode(file_get_contents("php://input"), true);
    $json_list = array();
    $json_list['status'] = 0;
//    //アカウントチェック
//    $account = MAccount::query()
//        ->where("MAccount.user_id = '".$params['login_id']."' AND MAccount.corporate_id = '".$params['corporate_id']."'")
//        ->columns(array('MAccount.*','MContractResource.*'))
//        ->join('MContractResource','MContractResource.accnt_no = MAccount.accnt_no')
//        ->execute();
//
//    if($account->count() === 0){
//        //ログインIDが間違っている場合
//        // エラーメッセージ：企業名、ログイン名、パスワードのいずれかが正しくありません。
//        $json_list['status'] = 1;
//        echo json_encode($json_list);
//        return true;
//    }
    //アカウントマスタに企業ID、ログインID、仮パスワードが一致するデータが存在する場合（パスワード未発行時）
    $account = MAccount::query()
        ->where("MAccount.user_id = '".$params['login_id']."' AND MAccount.corporate_id = '".$params['corporate_id'].
            "' AND MAccount.tentative_pass_word = '".$params['password']."'")
        ->columns(array('MAccount.*'))
        ->execute();

    if($account->count() > 0){
        //仮パスワードが発行されている場合、パスワード
        $json_list['status'] = 3;
        $app->session->set("corporate_id",$account[0]->corporate_id);
        $app->session->set("user_id",$account[0]->user_id);
        echo json_encode($json_list);
        return true;
    }
    //アカウントチェック
    $account = MAccount::query()
        ->where("MAccount.user_id = '".$params['login_id']."' AND MAccount.corporate_id = '".$params['corporate_id']."'")
        ->columns('MAccount.*')
        ->execute();
    if (!$app->security->checkHash($params['password'], $account[0]->pass_word)) {
        // PWが間違っている場合
        // アカウントマスタのログインエラー回数をチェックする。
        // ログインエラー回数＋１＜５の場合
        if($account[0]->login_err_count + 1 < 5 ){

            // 当該ユーザーのアカウントマスタをログインエラー回数を＋１した値で更新し、画面に下記エラーメッセージを表示して処理を終了する。
            $account[0]->login_err_count = $account[0]->login_err_count + 1;
            $account[0]->save();
            // エラーメッセージ：ログイン名かパスワードが正しくありません。
            $json_list['status'] = 1;
            echo json_encode($json_list);
        } else {
            // 当該ユーザーのアカウントマスタをログインエラー回数を＋１した値で更新し、画面に下記エラーメッセージを表示して処理を終了する。
            $account[0]->login_err_count = $account[0]->login_err_count + 1;
            $account[0]->save();
            // エラーメッセージ：このアカウントはロックされています。サイト管理者にお問い合わせください。。
            $json_list['status'] = 2;
            echo json_encode($json_list);
        }
        return true;
    } else {
        //アカウントマスタに企業IDログインID、パスワードが一致するデータが存在する場合
        if($account[0]->login_err_count + 1 >= 5 ){
            // 当該ユーザーのアカウントマスタをログインエラー回数を＋１した値で更新し、画面に下記エラーメッセージを表示して処理を終了する。
            $account[0]->login_err_count = $account[0]->login_err_count + 1;
            $json_list['status'] = 2;
            $account[0]->save();
            // エラーメッセージ：このアカウントはロックされています。サイト管理者にお問い合わせください。。
            echo json_encode($json_list);
        } else {
            // ログインエラー回数が１以上、４回以下の場合
            // 更新処理：アカウントマスタのログインエラー回数を０に更新し、２－２の処理へ進む。
            $account[0]->login_err_count = 0;
            $account[0]->save();
            $now = time();
            $last = strtotime($account[0]->last_pass_word_upd_date."+ 90 day");
            if($now < $last) {
                // 現在日付が、アカウント管理テーブル．パスワード最終変更時間＋８９日以下の場合
                // ホーム画面を表示する。
                $json_list['status'] = 0;

                $contracts = MContractResource::query()
                    ->where("MContractResource.accnt_no = '".$account[0]->accnt_no."'")
                    ->columns(array('MContractResource.*','MContract.*'))
                    ->join('MContract','MContract.corporate_id = MContractResource.corporate_id AND MContract.rntl_cont_no = MContractResource.rntl_cont_no')
                    ->execute();
                $contract_set = array();
                $contract_array = array();
                foreach($contracts as $contract){
                    $contract_set['rntl_cont_no'] = $contract->mContractResource->rntl_cont_no;
                    $contract_set['rntl_sect_cd'] = $contract->mContractResource->rntl_sect_cd;
                    $contract_set['individual_flg'] = $contract->mContract->individual_flg;
                    $contract_set['receipt_flg'] = $contract->mContract->receipt_flg;
                    $contract_set['rntl_cont_flg'] = $contract->mContract->rntl_cont_flg;
                    $contract_set['purchase_cont_flg'] = $contract->mContract->purchase_cont_flg;
                    $contract_set['sub_cont_flg1'] = $contract->mContract->sub_cont_flg1;
                    $contract_set['sub_cont_flg2'] = $contract->mContract->sub_cont_flg2;
                    $contract_set['sub_cont_flg3'] = $contract->mContract->sub_cont_flg3;
                    array_push($contract_array,$contract_set);
                }
                //認証情報をセッションに格納
                $app->session->set("auth", array(
                    'accnt_no' => $account[0]->accnt_no,
                    'corporate_id' => $account[0]->corporate_id,
                    'user_id' => $account[0]->user_id,
                    'user_name' => $account[0]->user_name,
                    'user_type' => $account[0]->user_type,
                    'password' => $account[0]->pass_word,
                    'button1_use_flg' => $account[0]->button1_use_flg,
                    'button2_use_flg' => $account[0]->button2_use_flg,
                    'button3_use_flg' => $account[0]->button3_use_flg,
                    'button4_use_flg' => $account[0]->button4_use_flg,
                    'button5_use_flg' => $account[0]->button5_use_flg,
                    'button6_use_flg' => $account[0]->button6_use_flg,
                    'button7_use_flg' => $account[0]->button7_use_flg,
                    'button8_use_flg' => $account[0]->button8_use_flg,
                    'button9_use_flg' => $account[0]->button9_use_flg,
                    'button10_use_flg' => $account[0]->button10_use_flg,
                    'button11_use_flg' => $account[0]->button11_use_flg,
                    'button12_use_flg' => $account[0]->button12_use_flg,
                    'button13_use_flg' => $account[0]->button13_use_flg,
                    'button14_use_flg' => $account[0]->button14_use_flg,
                    'button15_use_flg' => $account[0]->button15_use_flg,
                    'button16_use_flg' => $account[0]->button16_use_flg,
                    'button17_use_flg' => $account[0]->button17_use_flg,
                    'button18_use_flg' => $account[0]->button18_use_flg,
                    'button19_use_flg' => $account[0]->button19_use_flg,
                    'button20_use_flg' => $account[0]->button20_use_flg,
                    'button21_use_flg' => $account[0]->button21_use_flg,
                    'button22_use_flg' => $account[0]->button22_use_flg,
                    'button23_use_flg' => $account[0]->button23_use_flg,
                    'button24_use_flg' => $account[0]->button24_use_flg,
                    'button25_use_flg' => $account[0]->button25_use_flg,
                    'button26_use_flg' => $account[0]->button26_use_flg,
                    'button27_use_flg' => $account[0]->button27_use_flg,
                    'button28_use_flg' => $account[0]->button28_use_flg,
                    'button29_use_flg' => $account[0]->button29_use_flg,
                    'button30_use_flg' => $account[0]->button30_use_flg,
                    'position_name' => $account[0]->position_name,
                    'login_disp_name' => $account[0]->login_disp_name,
                    'mail_address' => $account[0]->mail_address,
                    'contract' => $contract_array
                ));
                echo json_encode($json_list);

            } else {
                // 現在日付が、アカウント管理テーブル．パスワード最終変更時間＋９０日以上の場合
                // パスワードの有効期限が切れている為、新しいパスワードを設定するパスワード変更画面を表示する。
                $json_list['status'] = 3;
                $app->session->set("corporate_id",$account[0]->corporate_id);
                $app->session->set("user_id",$account[0]->user_id);
                echo json_encode($json_list);
            }
        }
    }
});