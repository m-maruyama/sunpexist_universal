<?php
/**
 * パスワード
 */
$app->post('/password', function () use ($app) {
    ChromePhp::log('passwordに来たよ');

    try {
        $params = json_decode(file_get_contents("php://input"), true);
        $json_list = array();
        $json_list['status'] = 0;
        if ($params['password'] != $params['password_c']) {
            //新規パスワード入力欄、パスワード確認入力欄が同じ値か
            // エラーメッセージを表示して処理を終了する。
            $json_list['status'] = 1;
            echo json_encode($json_list);
            return true;
        }
        if (strlen($params['password']) < 8) {
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
        $transaction = $app->transactionManager->get();
        $user_id = $app->session->get("user_id");
        //ログインIDチェック
        $account = MAccount::find(array(
            "conditions" => "user_id = ?1",
            "bind" => array(1 => $user_id)
        ));
        if ($app->security->checkHash($params['password'], $account[0]->pass_word)) {
            //前回と同じパスワードを受け付けない
            // エラーメッセージを表示して処理を終了する。
            $json_list['status'] = 4;
            echo json_encode($json_list);
            return true;
        }
        $old_pass_list = array();
        $old_pass_list = json_decode($account[0]->old_pass_word, true);
        foreach ($old_pass_list as $old_pass) {
            if ($app->security->checkHash($params['password'], $old_pass)) {
                //過去のパスワード10回分チェック、同じパスワードがあったらエラー
                // エラーメッセージを表示して処理を終了する。
                $json_list['status'] = 5;
                echo json_encode($json_list);
                return true;
            }
        }
        //パスワード更新
        //パスワード
        $hash_pass = $app->security->hash($params['password']);
        $account[0]->pass_word = $hash_pass;
        //履歴パスワード
        if ($old_pass_list) {
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

        $account[0]->old_pass_word = json_encode($old_pass_list);
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
 * パスワード
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


        //ハッシュコード用の文字列生成
        $date = date('ymdHis');
        //生成した文字列からハッシュコード生成
        $tmp_password = md5($date);
        ChromePhp::log($tmp_password);

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

        $mail_address = $account[0]->mail_address;
        ChromePhp::log($mail_address);

        $url = $_SERVER['HTTP_HOST'];
        ChromePhp::log($url);

        mb_language("Japanese");
        mb_internal_encoding("UTF-8");

        $to = "mirainonakamura@gmail.com";
        $header = "From: "."tetsu_nakamura@pressman.ne.jp";
        $subject = "パスワード変更URLの発行";
        $body = "下記のURLをクリックしてパスワードを変更してください。\n"."http://"."$url"."/universal/password.html?dp="."$tmp_password";

        if(mb_send_mail($to,$subject,$body,$header)){
            ChromePhp::log('送信完了');
        }else{
            ChromePhp::log('送信失敗');
        }
        //if ($app->security->checkHash($params['password'], $account[0]->pass_word)) {
            //前回と同じパスワードを受け付けない
            // エラーメッセージを表示して処理を終了する。
        //    $json_list['status'] = 4;
        //    echo json_encode($json_list);
        //    return true;
       // }
        //$old_pass_list = array();
        //$old_pass_list = json_decode($account[0]->old_pass_word, true);
        //foreach($old_pass_list as $old_pass){
        //    if ($app->security->checkHash($params['password'], $old_pass)) {
                //過去のパスワード10回分チェック、同じパスワードがあったらエラー
                // エラーメッセージを表示して処理を終了する。
        //        $json_list['status'] = 5;
        //        echo json_encode($json_list);
        //        return true;
        //    }
        //}
        //パスワード更新
        //パスワード
        //$hash_pass = $app->security->hash($params['password']);
        $account[0]->hash = $tmp_password;
        ChromePhp::log($account[0]);

        //履歴パスワード
        //if ($old_pass_list) {
            //パスワードが変更されたら
            //履歴は10まで
         //   if (count($old_pass_list) >= 10) {
         //       unset($old_pass_list[0]);
        //   }
         //   array_push($old_pass_list, $hash_pass);
        //} else {
        //    $old_pass_list = array();
        //    //パスワード履歴がない場合はパスワード登録
        //    array_push($old_pass_list, $hash_pass);
        //}

        //$account[0]->old_pass_word = json_encode($old_pass_list);
        //$account[0]->last_pass_word_upd_date = date("Y/m/d H:i:s.sss", time()); //パスワード変更日時

            if ($account[0]->save() == false) {
                $error_list['update'] = 'アカウント情報の更新に失敗しました。';
                $json_list['errors'] = $error_list;
                echo json_encode($json_list);
                $transaction->rollBack();
                return true;
            } else {
                ChromePhp::log('commit');
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
