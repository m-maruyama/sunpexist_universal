<?php
use Phalcon\Mvc\Model\Resultset;

/**
 * ログイン
 */
$app->post('/login', function ()use($app) {
    $params = json_decode(file_get_contents("php://input"), true);
    $json_list = array();
    $json_list['status'] = 0;
    //企業IDチェック
    $account = MAccount::find(array(
        "conditions" => "corporate_id = ?1",
        "bind"	=> array(1 => $params['corporate_id'])
    ));
    if($account->count() === 0){
        //ログインIDが間違っている場合（アカウントマスタにログインIDが存在しない）
        // エラーメッセージ：ログイン名かパスワードが正しくありません。
        $json_list['status'] = 1;
        echo json_encode($json_list);
        return true;
    }

    //ログインIDチェック
    $account = MAccount::find(array(
        "conditions" => "user_id = ?1",
        "bind"	=> array(1 => $params['login_id'])
    ));
    if($account->count() === 0){
        //ログインIDが間違っている場合（アカウントマスタにログインIDが存在しない）
        // エラーメッセージ：ログイン名かパスワードが正しくありません。
        $json_list['status'] = 1;
        echo json_encode($json_list);
        return true;
    }

    if (!$app->security->checkHash($params['password'], $account[0]->pass_word)) {
        // ログインIDがあっているが、PWが間違っている場合（アカウントマスタにログインIDが存在する）
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
        //アカウントマスタにログインID、パスワードが一致するデータが存在する場合
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
                //認証情報をセッションに格納
                $app->session->set("auth", array(
                    'user_id' => $account[0]->user_id,
                    'user_name' => $account[0]->user_name,
                    'user_type' => $account[0]->user_type,
                    'password' => $account[0]->pass_word
                ));
                echo json_encode($json_list);

            } else {
                // 現在日付が、アカウント管理テーブル．パスワード最終変更時間＋９０日以上の場合
                // パスワードの有効期限が切れている為、新しいパスワードを設定するパスワード変更画面を表示する。
                $json_list['status'] = 3;
                $app->session->set("user_id",$account[0]->user_id);
                echo json_encode($json_list);
            }
        }
    }
});