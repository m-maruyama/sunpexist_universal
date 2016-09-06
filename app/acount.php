<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Mvc\Model\Query;

/**
 * アカウント検索
 */
$app->post('/acount/search', function ()use($app) {

    $params = json_decode(file_get_contents("php://input"), true);
    //ChromePhp::log($params);



    // アカウントセッション取得
    $auth = $app->session->get("auth");
    if($auth['user_type'] == '1'){
        $json_list['redirect'] = $auth['user_type'];
        echo json_encode($json_list);
        return;
    }

    $cond = $params['cond'];
    $page = $params['page'];
    $query_list = array();//追加
    //ChromePhp::log($cond);


    //---検索条件---//
    //企業ID
  	array_push($query_list,"m_account.corporate_id = '".$auth['corporate_id']."'");

    //契約No
  	if(!empty($cond['agreement_no'])){
  		array_push($query_list,"m_account.rntl_cont_no = '".$cond['agreement_no']."'");
  	}

    //ログインid
  	if(!empty($cond['user_id'])){
  		array_push($query_list,"m_account.user_id = '".$cond['user_id']."'");
  	}

    //ユーザー名称
  	if(!empty($cond['user_name'])){
  		array_push($query_list,"m_account.user_name = '".$cond['user_name']."'");
  	}

    //ユーザー名称
  	if(!empty($cond['mail_address'])){
  		array_push($query_list,"m_account.mail_address = '".$cond['mail_address']."'");
  	}

    $user_name_val = $cond['user_name'];
    ChromePhp::log($user_name_val);


    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
  	$sort_key ='';
  	$order ='';


    //$results = $app->modelsManager->createBuilder()
    //->where($query)
    //->from('m_account')
    //->columns(array('m_account.*'))
    //->orderBy($sort_key.' '.$order)
    //->getQuery()
		//->execute();
    $all_list = array();
  	$json_list = array();

    //$builder = $app->modelsManager->createBuilder()
  	//	->where($query)
    //  ->orderBy($sort_key.' '.$order);
    //ChromePhp::log($builder);

    //$paginator_model = new PaginatorQueryBuilder(
    //		array(
    //			"builder"  => $builder,
    //			"limit" => $page['records_per_page'],
    //			"page" => $page['page_number']
    //		)
    //	);
    //$paginator = $paginator_model->getPaginate();


    //全検索
    $results = MAccount::find(array(
        'order'	  => "rgst_date desc",
        'conditions'  => "user_name LIKE '$user_name_val%'"
        //'conditions'  => "'$user_name_val%"
    ));





    //リスト作成
    $list = array();
    $all_list = array();
    $json_list = array();

    foreach ($results as $result) {
        $list['accnt_no'] = $result->accnt_no;//アカウントno
        $list['corporate_id'] = $result->corporate_id;//コーポレートid
        //$list['agreement_no'] = $result->agreement_no;//契約id

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
        array_push($all_list,$list);//$all_listaに$listをpush
    }
    //ChromePhp::log($all_list);
    //ChromePhp::log($list);

    $page_list['records_per_page'] = $page['records_per_page'];
    $page_list['page_number'] = $page['page_number'];
    $page_list['total_records'] = count($results);
    $json_list['page'] = $page_list;
    $json_list['list'] = $all_list;
    //ChromePhp::log($json_list);
    //ChromePhp::log(json_encode($json_list));

    echo json_encode($json_list);
});

/**
 * アカウントモーダル機能
 */
$app->post('/acount/modal', function ()use($app) {

    $params = json_decode(file_get_contents("php://input"), true);

    $cond = $params['cond'];
    $json_list = array();
    $error_list = array();
    $error = false;
    $ac = MAccount::find(array(
        'conditions' => "user_id = '".$cond['user_id']."'"
    ));
    $m_account = new MAccount();
    $auth = $app->session->get("auth");
    $transaction = $app->transactionManager->get();
    if($params['type'] == '1'){
        //編集の場合
        $m_account = $ac[0];
    }elseif($params['type'] == '2'){
        //削除の場合
        if ($ac[0]->delete() == false) {
            $error_list['delete'] = 'アカウントの削除に失敗しました。';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            return true;
        } else {
            $transaction->commit();
            echo json_encode($json_list);
            return true;
        }
    }elseif($params['type'] == '3'){
        //ロック解除の場合
        $ac[0]->login_err_count = 0;
        if ($ac[0]->save() == false) {
            $error_list['acount'] = 'アカウントロックの解除に失敗しました。';
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
        if(!$cond['password']){
            $error_list['no_password'] = 'パスワードを入力してください';
            $json_list['errors'] = $error_list;
            echo json_encode($json_list);
            return true;
        }
        //user_idの重複チェック
        if(count($ac) > 0){
            $error_list['user_id'] = 'ログインIDが重複しています。';
        }
        if(!preg_match("/(?=.{8,})(?=.*\d+.*)(?=.*[a-zA-Z]+.*).*[!#$%&*+@?]+.*/",$cond['password'])){
            $error_list['password_preg'] = 'パスワードは半角英数字、半角記号(!#$%&*+@?)混合、8文字以上で入力してください。';
        }
        if(!$error_list){
            $m_account->rgst_user_id = $auth['user_id']; //更新ユーザー
            $m_account->rgst_date = date( "Y/m/d H:i:s.sss", time() ); //更新日時
        }
    }

    if(!preg_match("/(?=.{8,})(?=.*\d+.*)(?=.*[a-zA-Z]+.*).*+.*/",$cond['user_id'])){
        $error_list['user_id_preg'] = 'ログインIDは半角英数字混合、8文字以上で入力してください。';
    }

    //パスワード
    if($cond['password']){
        //パスワード
        $hash_pass = $app->security->hash($cond['password']);
        $old_pass_list = array();
        if($m_account->pass_word){
            if ($app->security->checkHash($cond['password'], $m_account->pass_word)) {
                //前回と同じパスワードを受け付けない
                // エラーメッセージを表示して処理を終了する。
                $error_list['before_pass'] = '前回と同じパスワードは使用出来ません。';
            }
            if(!$error_list){
                $old_pass_list = json_decode($m_account->old_pass_word, true);
                foreach($old_pass_list as $old_pass){
                    if ($app->security->checkHash($cond['password'], $old_pass)) {
                        //過去のパスワード10回分チェック、同じパスワードがあったらエラー
                        // エラーメッセージを表示して処理を終了する。
                        $error_list['old_pass'] = '過去に設定したことのあるパスワードは使用出来ません。';
                    }
                }
                //パスワード更新
                //履歴パスワード
                if($old_pass_list){
                    //パスワードが変更されたら
                    //履歴は10まで
                    if(count($old_pass_list) >= 10){
                        unset($old_pass_list[0]);
                    }
                    array_push($old_pass_list,$hash_pass);
                }
            }
        }else{
            $old_pass_list = array();
            //パスワード履歴がない場合はパスワード登録
            array_push($old_pass_list,$hash_pass);
        }
        if(!$error_list){
            $m_account->pass_word = $hash_pass;
            $m_account->old_pass_word = json_encode($old_pass_list);
            $m_account->last_pass_word_upd_date = date( "Y/m/d H:i:s.sss", time() ); //パスワード変更日時
        }
    }
    if($error_list){
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);
        return true;
    }
    $m_account->user_id = $cond['user_id']; //ユーザ名
    $m_account->user_name = $cond['user_name']; //ユーザ名
    $m_account->position_name = $cond['position_name']; //所属
    $m_account->user_type = $cond['user_type']; //管理権限(ユーザ区分)
    $m_account->upd_user_id = $auth['user_id']; //更新ユーザー
    $m_account->upd_date = date( "Y/m/d H:i:s.sss", time() ); //更新日時
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
