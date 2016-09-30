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

    //仮パスワードが存在する場合はログイン処理までいかない
    $kari_check = MAccount::query()
        ->where("MAccount.user_id = '".$params['login_id']."' AND MAccount.corporate_id = '".$params['corporate_id'].
            "' AND MAccount.tentative_pass_word IS NOT NULL")
        //->columns(array('MAccount.*','MContractResource.*'))
        //->join('MContractResource','MContractResource.accnt_no = MAccount.accnt_no')
        ->execute();

    if($kari_check->count() > 0) {
        //0文字以上だたら
        //アカウントマスタに企業ID、ログインID、仮パスワードが一致するデータが存在する場合（パスワード未発行時）
        $account = MAccount::query()
            ->where("MAccount.user_id = '" . $params['login_id'] . "' AND MAccount.corporate_id = '" . $params['corporate_id'] .
                "' AND MAccount.tentative_pass_word = '" . $params['password'] . "'")
            ->columns(array('MAccount.*', 'MContractResource.*'))
            ->join('MContractResource', 'MContractResource.accnt_no = MAccount.accnt_no')
            ->execute();
        ChromePhp::log('仮パスワードが一致するデータが存在する場合');
        ChromePhp::log($account);

        if ($account->count() > 0) {
            ChromePhp::log('仮パスワードが発行されている場合、パスワード');

            //仮パスワードが発行されている場合、パスワード
            $json_list['status'] = 4;
            $app->session->set("corporate_id", $account[0]->mAccount->corporate_id);
            $app->session->set("user_id", $account[0]->mAccount->user_id);
            echo json_encode($json_list);
            return true;
        }
        //仮パスワードが存在して、仮パスワードと違うパスワードの場合
        ChromePhp::log('仮パスワードが存在して、仮パスワードと違うパスワードの場合');
        $json_list['status'] = 1;
        echo json_encode($json_list);
        return true;
    }



    ChromePhp::log($params['login_id']);
    ChromePhp::log($params['corporate_id']);

    //アカウントチェック
    $account = MAccount::query()
        ->where("MAccount.user_id = '".$params['login_id']."' AND MAccount.corporate_id = '".$params['corporate_id']."'")
        ->columns(array('MAccount.*','MContractResource.*','MContract.*'))
        ->join('MContractResource','MContractResource.accnt_no = MAccount.accnt_no')
        ->join('MContract','MContract.corporate_id = MContractResource.corporate_id AND MContract.rntl_cont_no = MContractResource.rntl_cont_no')
        ->execute();
    ChromePhp::log('login通ってる2');
    ChromePhp::log($account);

    ChromePhp::log(md5($params['password']));
    ChromePhp::log($account[0]->mAccount->pass_word);

    if (md5($params['password']) != $account[0]->mAccount->pass_word) {
            ChromePhp::log('login通ってる3');

        //if (!$app->security->checkHash($params['password'], $account[0]->mAccount->pass_word)) {
        // PWが間違っている場合
        // アカウントマスタのログインエラー回数をチェックする。
        // ログインエラー回数＋１＜５の場合
        if($account[0]->mAccount->login_err_count + 1 < 5 ){

            // 当該ユーザーのアカウントマスタをログインエラー回数を＋１した値で更新し、画面に下記エラーメッセージを表示して処理を終了する。
            $account[0]->mAccount->login_err_count = $account[0]->mAccount->login_err_count + 1;
            $account[0]->mAccount->save();
            // エラーメッセージ：ログイン名かパスワードが正しくありません。
            $json_list['status'] = 1;
            echo json_encode($json_list);
        } else {
            // 当該ユーザーのアカウントマスタをログインエラー回数を＋１した値で更新し、画面に下記エラーメッセージを表示して処理を終了する。
            $account[0]->mAccount->login_err_count = $account[0]->mAccount->login_err_count + 1;
            $account[0]->mAccount->save();
            // エラーメッセージ：このアカウントはロックされています。サイト管理者にお問い合わせください。。
            $json_list['status'] = 2;
            echo json_encode($json_list);
        }
        return true;
    } else {
        ChromePhp::log('login通ってる4');
        //アカウントマスタに企業IDログインID、パスワードが一致するデータが存在する場合
        if($account[0]->mAccount->login_err_count + 1 >= 5 ){
            ChromePhp::log('login通ってる5');
            // 当該ユーザーのアカウントマスタをログインエラー回数を＋１した値で更新し、画面に下記エラーメッセージを表示して処理を終了する。
            $account[0]->mAccount->login_err_count = $account[0]->mAccount->login_err_count + 1;
            $json_list['status'] = 2;
            $account[0]->mAccount->save();
            // エラーメッセージ：このアカウントはロックされています。サイト管理者にお問い合わせください。。
            echo json_encode($json_list);
        } else {
            ChromePhp::log('login通ってる6');

            // ログインエラー回数が１以上、４回以下の場合
            // 更新処理：アカウントマスタのログインエラー回数を０に更新し、２－２の処理へ進む。
            $account[0]->mAccount->login_err_count = 0;
            $account[0]->mAccount->save();
            $now = time();
            $last = strtotime($account[0]->mAccount->last_pass_word_upd_date."+ 90 day");
            if($now < $last) {
                // 現在日付が、アカウント管理テーブル．パスワード最終変更時間＋８９日以下の場合
                // ホーム画面を表示する。
                $json_list['status'] = 0;
                //認証情報をセッションに格納
                $app->session->set("auth", array(
                    'accnt_no' => $account[0]->mAccount->accnt_no,
                    'corporate_id' => $account[0]->mAccount->corporate_id,
                    'user_id' => $account[0]->mAccount->user_id,
                    'user_name' => $account[0]->mAccount->user_name,
                    'user_type' => $account[0]->mAccount->user_type,
                    'password' => $account[0]->mAccount->pass_word,
                    'button1_use_flg' => $account[0]->mAccount->button1_use_flg,
                    'button2_use_flg' => $account[0]->mAccount->button2_use_flg,
                    'button3_use_flg' => $account[0]->mAccount->button3_use_flg,
                    'button4_use_flg' => $account[0]->mAccount->button4_use_flg,
                    'button5_use_flg' => $account[0]->mAccount->button5_use_flg,
                    'button6_use_flg' => $account[0]->mAccount->button6_use_flg,
                    'button7_use_flg' => $account[0]->mAccount->button7_use_flg,
                    'button8_use_flg' => $account[0]->mAccount->button8_use_flg,
                    'button9_use_flg' => $account[0]->mAccount->button9_use_flg,
                    'button10_use_flg' => $account[0]->mAccount->button10_use_flg,
                    'button11_use_flg' => $account[0]->mAccount->button11_use_flg,
                    'button12_use_flg' => $account[0]->mAccount->button12_use_flg,
                    'button13_use_flg' => $account[0]->mAccount->button13_use_flg,
                    'button14_use_flg' => $account[0]->mAccount->button14_use_flg,
                    'button15_use_flg' => $account[0]->mAccount->button15_use_flg,
                    'button16_use_flg' => $account[0]->mAccount->button16_use_flg,
                    'button17_use_flg' => $account[0]->mAccount->button17_use_flg,
                    'button18_use_flg' => $account[0]->mAccount->button18_use_flg,
                    'button19_use_flg' => $account[0]->mAccount->button19_use_flg,
                    'button20_use_flg' => $account[0]->mAccount->button20_use_flg,
                    'button21_use_flg' => $account[0]->mAccount->button21_use_flg,
                    'button22_use_flg' => $account[0]->mAccount->button22_use_flg,
                    'button23_use_flg' => $account[0]->mAccount->button23_use_flg,
                    'button24_use_flg' => $account[0]->mAccount->button24_use_flg,
                    'button25_use_flg' => $account[0]->mAccount->button25_use_flg,
                    'button26_use_flg' => $account[0]->mAccount->button26_use_flg,
                    'button27_use_flg' => $account[0]->mAccount->button27_use_flg,
                    'button28_use_flg' => $account[0]->mAccount->button28_use_flg,
                    'button29_use_flg' => $account[0]->mAccount->button29_use_flg,
                    'button30_use_flg' => $account[0]->mAccount->button30_use_flg,
                    'position_name' => $account[0]->mAccount->position_name,
                    'login_disp_name' => $account[0]->mAccount->login_disp_name,
                    'mail_address' => $account[0]->mAccount->mail_address,
                    'rntl_cont_no' => $account[0]->mContractResource->rntl_cont_no,
                    'rntl_sect_cd' => $account[0]->mContractResource->rntl_sect_cd,
                    'individual_flg' => $account[0]->mContract->individual_flg,
                    'receipt_flg' => $account[0]->mContract->receipt_flg,
                    'rntl_cont_flg' => $account[0]->mContract->rntl_cont_flg,
                    'purchase_cont_flg' => $account[0]->mContract->purchase_cont_flg,
                    'sub_cont_flg1' => $account[0]->mContract->sub_cont_flg1,
                    'sub_cont_flg2' => $account[0]->mContract->sub_cont_flg2,
                    'sub_cont_flg3' => $account[0]->mContract->sub_cont_flg3,
                ));
                echo json_encode($json_list);

            } else {
                // 現在日付が、アカウント管理テーブル．パスワード最終変更時間＋９０日以上の場合
                // パスワードの有効期限が切れている為、新しいパスワードを設定するパスワード変更画面を表示する。

                $json_list['status'] = 3;
                $app->session->set("corporate_id",$account[0]->mAccount->corporate_id);
                $app->session->set("user_id",$account[0]->mAccount->user_id);
                $app->session->set("trans_src","reset");
                echo json_encode($json_list);
            }
        }
    }
});