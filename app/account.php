<?php

use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/*
 * アカウント検索
 */
$app->post('/account/search', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');

    //一般ユーザーは検索できない
    /*
    if ($auth['user_type'] == '1') {
        $json_list['redirect'] = $auth['user_type'];
        echo json_encode($json_list);
        return;
    }
    */
    $cond = $params['cond'];
    $page = $params['page'];
    $query_list = array();//追加
    if(isset($cond['page_no'])){
        $page['page_number'] = $cond['page_no'];
    }
    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    $sort_key = '';
    $order = '';

    //第一ソート設定
    if (!empty($page['sort_key'])) {
        $sort_key = $page['sort_key'];
        if (!empty($page['order'])) {
            $order = $page['order'];
        }
      //ChromePhp::log($page['order']);
        // 社員番号
        if ($sort_key == 'accnt_no') {
            $q_sort_key = 'accnt_no';
        }
        // 着用者名
        if ($sort_key == 'corporate_id') {
            $q_sort_key = 'corporate_id';
        }
        // 商品コード
        if ($sort_key == 'rntl_cntl_no') {
            $q_sort_key = 'rntl_cntl_no';
        }
        // 出荷日
        if ($sort_key == 'user_id') {
            $q_sort_key = 'user_id';
        }
        // 返却予定日
        if ($sort_key == 'user_name') {
            $q_sort_key = 'user_name';
        }
        // 発注No
        if ($sort_key == 'login_name') {
            $q_sort_key = 'login_name';
        }
        // メーカー受注番号
        if ($sort_key == 'position_name') {
            $q_sort_key = 'position_name';
        }
        // メーカー伝票番号
        if ($sort_key == 'user_type') {
            $q_sort_key = 'user_type';
        }
      // メーカー伝票番号
      if ($sort_key == 'mail_address') {
          $q_sort_key = 'mail_address';
      }
      // メーカー伝票番号
      if ($sort_key == 'last_pass_word_upd_date') {
          $q_sort_key = 'last_pass_word_upd_date';
      }
    } else {
        //指定がなければ社員番号
        $sort_key = 'accnt_no';
        $order = 'asc';
    }

    $all_list = array();
    $json_list = array();

    $corporate_id_val = $cond['corporate_id'];

    //企業idが指定の時
    if (!$corporate_id_val == null) {
        $search_corporate = "corporate_id = '$corporate_id_val'";
    } else {
        //企業idが全ての時
        $search_corporate = 'corporate_id LIKE "%%"';
    }

    $user_id_val = $cond['user_id'];
    $user_name_val = $cond['user_name'];
    $mail_address = $cond['mail_address'];

    //全検索
    $results = MAccount::find(array(
        'order' => "$sort_key $order",
        'conditions' => "$search_corporate AND user_name LIKE '%$user_name_val%' AND user_id LIKE '$user_id_val%' AND mail_address LIKE '$mail_address%' AND del_flg LIKE '0'",
        //'conditions'  => "'$user_name_val%"
    ));
    //ChromePhp::log($results);
    $results_count = (count($results));//ページング処理２０個制限の前に数を数える
    //ChromePhp::log($results_count);
    $paginator_model = new PaginatorModel(
        array(
            'data' => $results,
            //'limit' => $page['records_per_page'],
            'limit' => '5',
            'page' => $page['page_number'],
        )
    );

    //リスト作成
    $list = array();
    $all_list = array();
    $json_list = array();

    $paginator = $paginator_model->getPaginate();
    $results = $paginator->items;
    foreach ($results as $result) {
        $list['accnt_no'] = $result->accnt_no;//アカウントno
        $list['corporate_id'] = $result->corporate_id;//コーポレートid
        $list['user_id'] = $result->user_id;
        $list['user_name'] = $result->user_name;//ユーザー名称
        $list['login_disp_name'] = $result->login_disp_name;//ログイン表示名
        $list['password'] = $result->pass_word;
        $list['user_name'] = $result->user_name;
        $list['position_name'] = $result->position_name;
        $list['user_type'] = $result->user_type;
        $list['mail_address'] = $result->mail_address;
        $list['last_pass_word_upd_date'] = $result->last_pass_word_upd_date;
        $list['login_err_count'] = $result->login_err_count;
        array_push($all_list, $list);//$all_listaに$listをpush
    }
    //$page_list['records_per_page'] = $page['records_per_page'];
    $page_list['records_per_page'] = '5';
    $page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = $results_count;//全件数をアップ
    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;

    echo json_encode($json_list);
});

/*
 * アカウントモーダル機能
 */
$app->post('/account/modal', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);
    $cond = $params['cond'];

    $json_list = array();
    $error_list = array();
    $error = false;
    $ac = MAccount::find(array(
        'conditions' => "user_id = '".$cond['user_id']."'",
    ));

    $transaction = $app->transactionManager->get();

    $m_account = new MAccount();
    $m_account->setTransaction($transaction);

    $auth = $app->session->get('auth');


    if ($params['type'] == '1') {
        //編集の場合
        $m_account = $ac[0];
    } elseif ($params['type'] == '2') {
        //削除ボタンが押された場合に、削除フラグ１
        $ac[0]->del_flg = '1';
        $m_account = $ac[0];

        //削除の場合
        //if ($ac[0]->delete() == false) {
        //    $error_list['delete'] = 'アカウントの削除に失敗しました。';
          //  $json_list['errors'] = $error_list;
        //    echo json_encode($json_list);
//
        //    return true;
        //} else {
        //    $transaction->commit();
        //    echo json_encode($json_list);
//
        //    return true;
        //}
    } elseif ($params['type'] == '3') {
        //ロック解除の場合
        $ac[0]->login_err_count = 0;
        if ($ac[0]->save() == false) {
            $error_list['account'] = 'アカウントロックの解除に失敗しました。';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);

            return true;
        }
        $transaction->commit();
        echo json_encode($json_list);
        return true;
    } else {
        //追加の場合
        //パスワードバリデーション
        if (!$cond['password']) {
            $error_list['no_password'] = 'パスワードを入力してください';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);

            return true;
        }
        //user_idの重複チェック（削除フラグがゼロのレコードがない場合。）
        if (count($ac) > 0) {
            foreach ($ac as $item) {
                if ($item->del_flg == 0) {
                    $error_list['user_id'] = 'ログインIDが重複しています。';
                }
            }
        }

        if (!preg_match("/(?=.*\d+.*)(?=.*[a-zA-Z]+.*).*+.*/", $cond['user_id'])) {
            $error_list['user_id_preg'] = 'ログインIDは半角英数字混合で入力してください。';
        }
        //user_id_preg
        if((strlen($cond['user_id']) > 20) || (strlen($cond['user_id']) < 8)){
            $error_list['user_id_strlen'] = 'ログインIDは8文字以上20文字以下で入力してください。';
        }

        if (!preg_match("/(?=.*\d+.*)(?=.*[a-zA-Z]+.*).*[!#$%&*+@?]+.*/", $cond['password'])) {
            $error_list['password_preg'] = 'パスワードは半角英数字、半角記号(!#$%&*+@?)混合で入力してください。';
        }
        //user_id_preg
        if((strlen($cond['password']) > 16) || (strlen($cond['password']) < 8)){
            $error_list['password_strlen'] = 'パスワードは8文字以上16文字以下で入力してください。';
        }


        if (!$error_list) {
            $m_account->rgst_user_id = $auth['user_id']; //更新ユーザー
            $m_account->rgst_date = date('Y/m/d H:i:s.sss', time()); //更新日時
        }
    }

    //メールアドレスパターン
    if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $cond['mail_address'])) {
        $error_list['mail_address_preg'] = 'メールアドレスの形式が不正です。';
    }
    //メールアドレス文字数
    if(strlen($cond['user_name']) > 100){
        $error_list['user_name_strlen'] = 'メールアドレスは半角100文字以下で入力してください。';
    }
    //ユーザー名称文字数
    if(strlen($cond['user_name']) > 22){
        $error_list['user_name_strlen'] = 'ユーザー名称は22文字以下(全角11文字)で入力してください。';
    }
    //所属名文字数
    if(strlen($cond['position_name']) > 22){
        $error_list['position_name_strlen'] = '所属名は22文字以下(全角11文字)で入力してください。';
    }
    //ログイン表示名文字数
    if(strlen($cond['login_disp_name']) > 22){
        $error_list['login_disp_name_strlen'] = 'ログイン表示名は22文字以下(全角11文字)で入力してください。';
    }


    //パスワード
    if ($cond['password']) {
        //パスワード md5化
        //$hash_pass = $app->security->hash($cond['password']);
        $hash_pass = md5($cond['password']);
        $old_pass_list = array();
        if ($m_account->pass_word) {
            //if ($app->security->checkHash($cond['password'], $m_account->pass_word)) {
            if ($hash_pass == $m_account->pass_word) {
                //前回と同じパスワードを受け付けない
                // エラーメッセージを表示して処理を終了する。
                $error_list['before_pass'] = '前回と同じパスワードは使用出来ません。';
            }
            if (!$error_list) {
                $old_pass_list = json_decode($m_account->old_pass_word, true);
                foreach ($old_pass_list as $old_pass) {
                    if ($hash_pass == $old_pass) {
                        //過去のパスワード10回分チェック、同じパスワードがあったらエラー
                        // エラーメッセージを表示して処理を終了する。
                        $error_list['old_pass'] = '過去に設定したことのあるパスワードは使用出来ません。';
                    }
                }
                //パスワード更新
                //履歴パスワード
                if ($old_pass_list) {
                    //パスワードが変更されたら
                    //履歴は10まで
                    if (count($old_pass_list) >= 10) {
                        unset($old_pass_list[0]);
                    }
                    array_push($old_pass_list, $hash_pass);
                }
            }
        } else {
            $old_pass_list = array();
            //パスワード履歴がない場合はパスワード登録
            array_push($old_pass_list, $hash_pass);
        }
        if (!$error_list) {
            $m_account->pass_word = $hash_pass;
            $m_account->old_pass_word = json_encode($old_pass_list);
            $m_account->last_pass_word_upd_date = date('Y/m/d H:i:s.sss', time()); //パスワード変更日時
        }
    }
    if ($error_list) {
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);

        return true;
    }
    $m_account->corporate_id = $cond['corporate_id']; //コーポレートid
    $m_account->user_id = $cond['user_id']; //ユーザid
    $m_account->user_name = $cond['user_name']; //ユーザ名
    $m_account->position_name = $cond['position_name']; //所属
    $m_account->mail_address = $cond['mail_address']; //メールアドレス
    $m_account->user_type = $cond['user_type']; //管理権限(ユーザ区分)
    $m_account->login_disp_name = $cond['login_disp_name']; //表示ユーザー名
    $m_account->upd_user_id = $auth['user_id']; //更新ユーザー
    $m_account->upd_date = date('Y/m/d H:i:s.sss', time()); //更新日時
    //ChromePhp::log($m_account);

    if ($m_account->save() == false) {
        $error_list['update'] = 'アカウント情報の更新に失敗しました。';
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);
        return true;
    } else {
        $transaction->commit();
    }

    echo json_encode($json_list);
});
