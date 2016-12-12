<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

/**
 * パスワード
 */
$app->post('/password', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);


    if (isset($params['hashid'])) {
        //ハッシュタグ付の場合ここを通ります。
        $json_list = array();
        $hash = $params['hashid'];

        //$transaction = $app->transactionManager->get();
        //ログインIDチェック
        $account = MAccount::find(array(
            "conditions" => "hash = '$hash'",
        ));

        if (isset($account[0])) {
            $list['corporate_id'] = $account[0]->corporate_id;
            $list['user_id'] = $account[0]->user_id;
            $json_list['list'] = $list;
        } else {
            $error_list['hashcheck'] = '不正なURLです。';
            $json_list['errors'] = $error_list;
        }
        echo json_encode($json_list);
        return true;
    }


    try {
        //$params = json_decode(file_get_contents("php://input"), true);


        $json_list = array();
        $json_list['status'] = 0;
        if ($params['password'] != $params['password_c']) {
            //新規パスワード入力欄、パスワード確認入力欄が同じ値か
            // エラーメッセージを表示して処理を終了する。
            $json_list['status'] = 1;
            echo json_encode($json_list);
            return true;
        }
        if ((strlen($params['password']) < 8) || (strlen($params['password']) > 16)){
            //パスワード桁数は8文字以上であるか
            // エラーメッセージを表示して処理を終了する。
            $json_list['status'] = 2;
            echo json_encode($json_list);
            return true;
        }
        if (!preg_match("/(?=.{8,})(?=.*\d+.*)(?=.*[a-zA-Z]+.*).*[!#$%&*+@?]+.*/", $params['password'])) {
            //パスワードは半角英数字、半角記号(!#$%&*+@?)3種以上混合で入力
            // エラーメッセージを表示して処理を終了する。
            $json_list['status'] = 3;
            echo json_encode($json_list);
            return true;
        }

        //トランザクション
        $transaction = $app->transactionManager->get();


        $user_id = $app->session->get("user_id");


        if ($params['from'] == 'mail') {//
            if (isset($params['tmp_user_id'])) {
                $user_id = $params['tmp_user_id'];
            }
            if (isset($params['tmp_corporate_id'])) {
                $corporate_id = $params['tmp_corporate_id'];
            }
            //ログインIDチェック
            $account = MAccount::find(array(
                "conditions" => "user_id = ?1 AND corporate_id = '$corporate_id'",
                "bind" => array(1 => $user_id)
            ));
        } elseif ($params['from'] == 'account') {//アカウント管理からの遷移
            if (isset($params['accn_no'])) {
                $accnt_no = $params['accn_no'];
            }

            $account = MAccount::find(array(
                "conditions" => "accnt_no = ?1",
                "bind" => array(1 => $accnt_no)
            ));
        } elseif ($params['from'] == 'login90day') {//
            if (isset($params['user_id'])) {
                $user_id = $params['user_id'];
            }
            if (isset($params['corporate_id'])) {
                $corporate_id = $params['corporate_id'];
            }
            //ログインIDチェック
            $account = MAccount::find(array(
                "conditions" => "user_id = ?1 AND corporate_id = '$corporate_id'",
                "bind" => array(1 => $user_id)
            ));
        }


        if (md5($params['password']) == $account[0]->pass_word) {
            //前回と同じパスワードを受け付けない
            // エラーメッセージを表示して処理を終了する。
            $json_list['status'] = 4;
            echo json_encode($json_list);
            return true;
        }
        //過去のパスワードがあった場合、下記の処理を実施
        if (json_decode($account[0]->old_pass_word, true) !== null){
            $old_pass_list = array();
            $old_pass_list = json_decode($account[0]->old_pass_word, true);

            foreach ($old_pass_list as $old_pass) {
                if (md5($params['password']) == $old_pass) {
                    //過去のパスワード10回分チェック、同じパスワードがあったらエラー
                    // エラーメッセージを表示して処理を終了する。
                    $json_list['status'] = 5;
                    echo json_encode($json_list);
                    return true;
                }
            }
        }
        //パスワード更新

        //以前のファルコンhash
        //$hash_pass = $app->security->hash($params['password']);
        $hash_pass = md5($params['password']);

        $account[0]->pass_word = $hash_pass;
        //履歴パスワード
        if (isset($old_pass_list)) {
            //パスワードが変更されたら
            //履歴は10まで
            if (count($old_pass_list) >= 10) {
                unset($old_pass_list[0]);
            }
            array_push($old_pass_list, $hash_pass);
        } else {
            $old_pass_list = array();
            //パスワード履歴がない場合はパスワード登録
            array_push($old_pass_list, $hash_pass);
        }

        $auth = $app->session->get('auth');

        if (isset($auth['user_id'])) {
            $account[0]->upd_user_id = $auth['user_id'];
            $account[0]->upd_pg_id = $auth['user_id'];
        } else {
            $account[0]->upd_user_id = $account[0]->rgst_user_id;
            $account[0]->upd_pg_id = $account[0]->rgst_user_id;
        }
        $account[0]->tentative_pass_word = null;
        $account[0]->old_pass_word = json_encode($old_pass_list);
        $account[0]->upd_date = date("Y/m/d H:i:s.sss", time()); //パスワード変更日時
        $account[0]->last_pass_word_upd_date = date("Y/m/d H:i:s.sss", time()); //パスワード変更日時
        if ($account[0]->save() == false) {
            $error_list['update'] = 'アカウント情報の更新に失敗しました。';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            return true;
        } else {
            $transaction->commit();
        }
        $app->session->remove("user_id");

        echo json_encode($json_list);

    } catch (Exception $e) {
        $error_list['update'] = 'アカウント情報の更新に失敗しました。';
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);
        return true;
    }

});


/**
 * パスワードを忘れた方へ
 */
$app->post('/login_password', function () use ($app) {
    try {
        $params = json_decode(file_get_contents("php://input"), true);


        $json_list = array();
        $json_list['status'] = 0;
        //if($params['password'] != $params['password_c']){
        //新規パスワード入力欄、パスワード確認入力欄が同じ値か
        // エラーメッセージを表示して処理を終了する。
        //   $json_list['status'] = 1;
        //   echo json_encode($json_list);
        //    return true;
        //}



        $corporate_id = $params['corporate_id'];
        $user_id = $params['user_id'];

        if (!preg_match("/(?=.{8,})(?=.*\d+.*)(?=.*[a-zA-Z]+.*).*+.*/", $user_id)) {
            $error_list['user_id_preg'] = 'ログインIDは半角英数字混合、8文字以上で入力してください。';
            $json_list['status'] = 5;
            echo json_encode($json_list);
            return true;
        }



        //ハッシュコード用の文字列生成
        $date = date('ymdHis');
        //生成した文字列からハッシュコード生成
        $tmp_password = md5($date);

        //if(strlen($params['password'])<8){
        //パスワード桁数は8文字以上であるか
        // エラーメッセージを表示して処理を終了する。
        //     $json_list['status'] = 2;
        //     echo json_encode($json_list);
        //    return true;
        //}
        //if(!preg_match("/(?=.{8,})(?=.*\d+.*)(?=.*[a-zA-Z]+.*).*[!#$%&*+@?]+.*/",$params['password'])){
        //パスワードは半角英数字、半角記号(!#$%&*+@?)3種以上混合で入力
        // エラーメッセージを表示して処理を終了する。
        //    $json_list['status'] = 3;
        //   echo json_encode($json_list);
        //  return true;
        //}
        $transaction = $app->transactionManager->get();
        //$user_id = $app->session->get("user_id");
        //ログインIDチェック
        $account = MAccount::find(array(
            "conditions" => "corporate_id = '$corporate_id' AND user_id = '$user_id'",
            //"bind" => array(1 => $user_id)
        ));

        if (isset($account[0])) {

            //メール
            $mail_address = $account[0]->mail_address;
            $url = $_SERVER['HTTP_HOST'];

            mb_language("Japanese");
            mb_internal_encoding("UTF-8");

            $to = "$mail_address";
            $header = "From: " . "tetsu_nakamura@pressman.ne.jp";//本番時、客先メールアドレスに修正
            $subject = "パスワード変更URLの発行";
            $body = "下記のURLをクリックしてパスワードを変更してください。\n" . "http://" . "$url" . "/universal/password.html?dp=" . "$tmp_password";

            if (mb_send_mail($to, $subject, $body, $header)) {

            } else {
                $json_list['status'] = 1;
            }


            $account[0]->hash = $tmp_password;

            if ($account[0]->save() == false) {
                $error_list['update'] = 'アカウント情報の更新に失敗しました。';
                $json_list['errors'] = $error_list;
                echo json_encode($json_list);
                $transaction->rollBack();
                return true;
            } else {
                $transaction->commit();
            }
            $app->session->remove("user_id");
            echo json_encode($json_list);


        } else {
            $error_list['search'] = '該当するアカウントが見つかりませんでした。';
            $json_list['errors'] = $error_list;
            $json_list['status'] = 4;

            echo json_encode($json_list);
            return true;
        }

    } catch (Exception $e) {
        $error_list['update'] = 'アカウント情報の更新に失敗しました。';
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);
        return true;
    }


});
