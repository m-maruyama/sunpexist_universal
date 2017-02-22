<?php
//use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Mvc\Model\Query;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

/*
 * 検索項目：契約No(入力系)
 */
$app->post('/agreement_no_input', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    $query_list = array();
    $list = array();
    $all_list = array();
    $json_list = array();
    $referrer = $params['referrer'];

    // アカウントセッション取得
    $auth = $app->session->get('auth');

    // 契約マスタ. 企業ID
    array_push($query_list, "m_contract.corporate_id = '".$auth['corporate_id']."'");
    // 契約マスタ. レンタル契約フラグ
    array_push($query_list, "m_contract.rntl_cont_flg = '1'");
    // 契約リソースマスタ. 企業ID
    array_push($query_list, "m_contract_resource.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ.企業ID
    array_push($query_list, "m_account.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ. ユーザーID
    array_push($query_list, "m_account.user_id = '".$auth['user_id']."'");
    $query = implode(' AND ', $query_list);
    $arg_str = 'SELECT ';
    $arg_str .= ' * ';
    $arg_str .= ' FROM ';
    $arg_str .= '(SELECT distinct on (m_contract.rntl_cont_no) ';
    $arg_str .= 'm_contract.rntl_cont_no as as_rntl_cont_no,';
    $arg_str .= 'm_contract.rntl_emply_cont_name as as_rntl_cont_name';
    $arg_str .= ' FROM ';
    $arg_str .= 'm_contract';
    $arg_str .= ' INNER JOIN m_contract_resource';
    $arg_str .= ' ON m_contract.corporate_id = m_contract_resource.corporate_id';
    $arg_str .= ' AND m_contract.rntl_cont_no = m_contract_resource.rntl_cont_no';
    $arg_str .= ' INNER JOIN m_account';
    $arg_str .= ' ON m_contract_resource.accnt_no = m_account.accnt_no';
    $arg_str .= ' AND m_contract_resource.corporate_id = m_account.corporate_id';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ') as distinct_table';
    $arg_str .= ' ORDER BY as_rntl_cont_no asc';
    $m_contract = new MContract();
    $results = new Resultset(null, $m_contract, $m_contract->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];

    $json_list['disp_flg'] = false;
    if ($results_cnt > 0) {
        $list['rntl_cont_no'] = null;
        $list['rntl_cont_name'] = null;
        // 前画面が着用者検索画面でない場合、セッションを削除
        if($referrer > -1){
            // 前画面セッション取得
            $wearer_odr_post = $app->session->get("wearer_odr_post");

            foreach ($results as $result) {
                $list['rntl_cont_no'] = $result->as_rntl_cont_no;
                $list['rntl_cont_name'] = $result->as_rntl_cont_name;
                if ($list['rntl_cont_no'] == $wearer_odr_post['rntl_cont_no']) {
                    $list['selected'] = 'selected';
                } else {
                    $list['selected'] = '';
                }
                array_push($all_list, $list);
            }
            $json_list['rntl_cont_no'] = $list['rntl_cont_no'];
            if(isset($wearer_odr_post)){
              $json_list['rntl_cont_no'] = $wearer_odr_post['rntl_cont_no'];
              $json_list['werer_cd'] = $wearer_odr_post['werer_cd'];
              $json_list['cster_emply_cd'] = $wearer_odr_post['cster_emply_cd'];
              $json_list['sex_kbn'] = $wearer_odr_post['sex_kbn'];
              $json_list['rntl_sect_cd'] = $wearer_odr_post['rntl_sect_cd'];
              $json_list['job_type_cd'] = $wearer_odr_post['job_type_cd'];
              $json_list['ship_to_cd'] = $wearer_odr_post['ship_to_cd'];
              $json_list['ship_to_brnch_cd'] = $wearer_odr_post['ship_to_brnch_cd'];
            }
        }else{
            if(count($results)==1){
                $json_list['disp_flg'] = true;
                $json_list['rntl_cont_no'] = $results[0]->as_rntl_cont_no;
                $list['rntl_cont_no'] = $results[0]->as_rntl_cont_no;
                $list['rntl_cont_name'] = $results[0]->as_rntl_cont_name;
                $list['selected'] = 'selected';
                array_push($all_list, $list);
            }else{
                //先頭に未選択値をセット
                array_push($all_list,array());
                foreach ($results as $result) {
                    $list['rntl_cont_no'] = $result->as_rntl_cont_no;
                    $list['rntl_cont_name'] = $result->as_rntl_cont_name;
                    array_push($all_list, $list);
                }
            }
            $app->session->remove("wearer_odr_post");
        }
    } else {
        $list['rntl_cont_no'] = null;
        $list['rntl_cont_name'] = '';
        $list['selected'] = '';
        array_push($all_list, $list);
    }

    $json_list['agreement_no_list'] = $all_list;
    echo json_encode($json_list);
});

/*
 * 着用者入力各フォーム
 *
 * @param disp_type
 * wearer_search 検索一覧画面
 * wearer_order 商品明細情報入力画面
 * wearer_order_search
 */
$app->post('/wearer_input', function () use ($app) {
    $params = json_decode(file_get_contents('php://input'), true);

    // アカウントセッション取得
    $auth = $app->session->get('auth');
    //ChromePhp::LOG($auth);

    // フロント側パラメータ
    $cond = $params['cond'];
    $referrer = $cond['referrer'];
    $disp_type = $cond['disp_type'];
    //ChromePhp::LOG($cond);
    //ChromePhp::LOG($referrer);
    //ChromePhp::LOG($disp_type);

    $json_list = array();
    $list = array();
    // 画面遷移により、以降の処理で使用するパラメータを設定
    if ($disp_type == "wearer_search"||$disp_type == "wearer_delete") {
      $wearer_odr_post = $app->session->get("wearer_odr_post");
      $query_list = array();
      $query_list[] = "corporate_id = '".$auth["corporate_id"]."'";
      $query_list[] = "rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'";
      $query_list[] = "werer_cd = '".$wearer_odr_post['werer_cd']."'";
      $query_list[] = "order_sts_kbn = '1'";
      $query = implode(' AND ', $query_list);
      $arg_str = "";
      $arg_str .= "SELECT ";
      $arg_str .= "*";
      $arg_str .= " FROM ";
      $arg_str .= "m_wearer_std_tran";
      $arg_str .= " WHERE ";
      $arg_str .= $query;
      $m_weare_std_tran= new MWearerStdTran();
      $results = new Resultset(null, $m_weare_std_tran, $m_weare_std_tran->getReadConnection()->query($arg_str));
      $result_obj = (array)$results;
      $results_cnt = $result_obj["\0*\0_count"];
      if ($results_cnt > 0) {
        $paginator_model = new PaginatorModel(
            array(
                "data"  => $results,
                "limit" => 1,
                "page" => 1
            )
        );
        $paginator = $paginator_model->getPaginate();
        $results = $paginator->items;
        //ChromePhp::LOG($results);
        foreach ($results as $result) {
          $wearer_odr_post['cster_emply_cd'] = $result->cster_emply_cd;
          $wearer_odr_post['cster_emply_flg'] = false;
          if (isset($wearer_odr_post['cster_emply_cd'])) {
            $wearer_odr_post['cster_emply_flg'] = true;
          }
          $wearer_odr_post['werer_name'] = $result->werer_name;
          $wearer_odr_post['werer_name_kana'] = $result->werer_name_kana;
          $wearer_odr_post['sex_kbn'] = $result->sex_kbn;
          $wearer_odr_post['resfl_ymd'] = $result->resfl_ymd;
          $wearer_odr_post['rntl_sect_cd'] = $result->rntl_sect_cd;
          $wearer_odr_post['job_type_cd'] = $result->job_type_cd;
          $wearer_odr_post['ship_to_cd'] = $result->ship_to_cd;
          $wearer_odr_post['ship_to_brnch_cd'] = $result->ship_to_brnch_cd;
        }
      }
    } else if ($disp_type == "wearer_input") {
      $wearer_odr_post = $app->session->get("wearer_odr_post");
    } else {
      $wearer_odr_post['rntl_cont_no'] = $cond['agreement_no'];
      $wearer_odr_post['werer_cd'] = "";
      $wearer_odr_post['cster_emply_cd'] = "";
      $wearer_odr_post['cster_emply_flg'] = false;
      $wearer_odr_post['werer_name'] = "";
      $wearer_odr_post['werer_name_kana'] = "";
      $wearer_odr_post['sex_kbn'] = "";
      $wearer_odr_post['resfl_ymd'] = "";
      $wearer_odr_post['rntl_sect_cd'] = "";
      $wearer_odr_post['job_type_cd'] = "";
      $wearer_odr_post['ship_to_cd'] = "";
      $wearer_odr_post['ship_to_brnch_cd'] = "";
      $wearer_odr_post['order_reason_kbn'] = "";
      $wearer_odr_post['order_tran_flg'] = "";
      $wearer_odr_post['wst_order_req_no'] = "";
      $wearer_odr_post['order_req_no'] = "";
    }

    // 着用者情報 --ここから
    $json_list['werer_cd'] = $wearer_odr_post['werer_cd'];
    $json_list['cster_emply_cd'] = $wearer_odr_post['cster_emply_cd'];
    $json_list['cster_emply_flg'] = $wearer_odr_post['cster_emply_flg'];
    $json_list['werer_name'] = $wearer_odr_post['werer_name'];
    $json_list['werer_name_kana'] = $wearer_odr_post['werer_name_kana'];
    $json_list['sex_kbn'] = $wearer_odr_post['sex_kbn'];
    $json_list['rntl_sect_cd'] = $wearer_odr_post['rntl_sect_cd'];
    $json_list['job_type_cd'] = $wearer_odr_post['job_type_cd'];
    $json_list['ship_to_cd'] = $wearer_odr_post['ship_to_cd'];
    $json_list['ship_to_brnch_cd'] = $wearer_odr_post['ship_to_brnch_cd'];
//    if(!empty($wearer_odr_post['appointment_ymd'])){
//      $json_list['appointment_ymd'] = date('Y/m/d', strtotime($wearer_odr_post['appointment_ymd']));
//    } else {
//      $json_list['appointment_ymd'] = "";
//    }
    if(!empty($wearer_odr_post['resfl_ymd'])){
      $json_list['resfl_ymd'] = date('Y/m/d', strtotime($wearer_odr_post['resfl_ymd']));
    } else {
      $json_list['resfl_ymd'] = "";
    }
    // 着用者情報 --ここまで

    //--性別ここから
    $query_list = array();
    $sex_kbn_list = array();
    //--- 検索条件 ---//
    // 汎用コードマスタ. 分類コード
    array_push($query_list, "cls_cd = '004'");
    $query = implode(' AND ', $query_list);
    $m_gencode_results = MGencode::query()
        ->where($query)
        ->columns('*')
        ->execute();
    foreach ($m_gencode_results as $m_gencode_result) {
      $list['gen_cd'] = $m_gencode_result->gen_cd;
      $list['gen_name'] = $m_gencode_result->gen_name;
      if ($list['gen_cd'] == $wearer_odr_post['sex_kbn']) {
          $list['sex_kbn_selected'] = 'selected';
      } else {
          $list['sex_kbn_selected'] = '';
      }

      array_push($sex_kbn_list, $list);
    }
    $json_list['sex_kbn_list'] = $sex_kbn_list;
    //--性別ここまで

    //拠点--ここから
    $query_list = array();
    // 契約マスタ. 企業ID
    array_push($query_list, "MContract.corporate_id = '".$auth['corporate_id']."'");
    // 契約リソースマスタ. 企業ID
    array_push($query_list, "MContractResource.corporate_id = '".$auth['corporate_id']."'");
    // 契約リソースマスタ. レンタル契約No = 画面で選択されている契約No.
    array_push($query_list, "MContractResource.rntl_cont_no = '".$cond['agreement_no']."'");
    // アカウントマスタ.企業ID
    array_push($query_list, "MAccount.corporate_id = '".$auth['corporate_id']."'");
    // アカウントマスタ. ユーザーID
    array_push($query_list, "MAccount.user_id = '".$auth['user_id']."'");
    $query = implode(' AND ', $query_list);
    $m_contract_resources = MContract::query()
        ->where($query)
        ->columns(array('MContractResource.rntl_sect_cd'))
        ->innerJoin('MContractResource', 'MContract.corporate_id = MContractResource.corporate_id')
        ->join('MAccount', 'MAccount.accnt_no = MContractResource.accnt_no')
        ->execute();
    $rntl_sect_cd = null;
    $all_zero = false;
    $sect_arr = array();
    foreach ($m_contract_resources as $m_contract_resource) {
      array_push($sect_arr, "'".$m_contract_resource->rntl_sect_cd."'");
      if($m_contract_resource->rntl_sect_cd == '0000000000'){
          $all_zero = true;
      }
    }

    //【前処理】で取得したレコードの中に、レンタル部門コード＝オール０「ゼロ」がセットされているレコードが存在しない場合、部門コードをセット
    $list = array();
    $all_list = array();
    $query_list = array();
    if(!$all_zero){
      array_push($query_list, "rntl_sect_cd in(".implode(',',$sect_arr).")");
    }
    array_push($query_list, "corporate_id = '".$auth["corporate_id"]."'");
    array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");
    $query = implode(' AND ', $query_list);
    $arg_str = 'SELECT ';
    $arg_str .= ' distinct on (rntl_sect_cd) *';
    $arg_str .= ' FROM m_section';
    if (!empty($query)) {
      $arg_str .= ' WHERE ';
      $arg_str .= $query;
    }
    $arg_str .= ' ORDER BY rntl_sect_cd asc';
    $m_section = new MSection();
    $results = new Resultset(null, $m_section, $m_section->getReadConnection()->query($arg_str));
    $results_array = (array) $results;
    $results_cnt = $results_array["\0*\0_count"];
    if(count($results)>1){
        $list['rntl_sect_cd'] = '';
        $list['rntl_sect_name'] ='';
        $json_list['section_disabled'] = '';
        array_push($all_list, $list);
    }else{
        $json_list['section_disabled'] = 'disabled';
    }
    foreach ($results as $result) {
      $list['rntl_sect_cd'] = $result->rntl_sect_cd;
      $list['rntl_sect_name'] = $result->rntl_sect_name;
      if ($list['rntl_sect_cd'] == $wearer_odr_post['rntl_sect_cd']) {
          $list['rntl_sect_cd_selected'] = 'selected';
      } else {
          $list['rntl_sect_cd_selected'] = '';
      }

      array_push($all_list, $list);
    }
    $json_list['m_section_list'] = $all_list;
    //--拠点ここまで

    //貸与パターン--ここから
    $list = array();
    $all_list = array();
    $job_type_list = array();
    $query_list = array();
    // 職種マスタ. 企業ID
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    // 職種マスタ. レンタル契約No
    array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");
    $query = implode(' AND ', $query_list);
    $m_job_type_results = MJobType::query()
        ->where($query)
        ->columns('*')
        ->orderby('CAST(job_type_cd AS INTEGER) asc')
        ->execute();
    if(count($m_job_type_results)>1){
        $list['job_type_cd'] = '';
        $list['job_type_name'] = '';
        $list['sp_job_type_flg'] = '';
        $json_list['job_type_cd_disabled'] = '';
        array_push($job_type_list, $list);
    }else{
        $json_list['job_type_cd_disabled'] = 'disabled';

    }
    foreach ($m_job_type_results as $m_job_type_result) {
      $list['job_type_cd'] = $m_job_type_result->job_type_cd;
      $list['job_type_name'] = $m_job_type_result->job_type_name;
      $list['sp_job_type_flg'] = $m_job_type_result->sp_job_type_flg;
      if (($list['job_type_cd'] == $wearer_odr_post['job_type_cd'])&&($referrer>-1)) {
        $list['job_type_cd_selected'] = 'selected';
      } else {
        $list['job_type_cd_selected'] = '';
      }

      array_push($job_type_list, $list);
    }
    $json_list['job_type_list'] = $job_type_list;
    //貸与パターン--ここまで

    //出荷先--ここから
    $list = array();
    $m_shipment_to_list = array();
    $query_list = array();
    // 出荷先マスタ. 企業ID
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    // 出荷先マスタ. レンタル契約No
    array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");
    $query = implode(' AND ', $query_list);
    $m_shipment_to_results = MShipmentTo::query()
        ->where($query)
        ->columns('*')
        ->execute();
    //一件目に「拠点と同じ:部門マスタ.標準出荷先コード:部門マスタ.標準出荷先支店コード」という選択肢とセレクトボックスを表示。
    if(count($m_shipment_to_results)>1){
        $list['ship_to_cd'] = '';
        $list['cust_to_brnch_name1'] = '拠点と同じ';
        $list['cust_to_brnch_name2'] = '';
        array_push($m_shipment_to_list, $list);
        $json_list['ship_to_cd_disabled'] = '';
    }else{
        $wearer_odr_post['rntl_cont_no'] = $cond['agreement_no'];
        $wearer_odr_post['ship_to_cd'] = $m_shipment_to_results[0]->ship_to_cd;
        $wearer_odr_post['ship_to_brnch_cd'] = $m_shipment_to_results[0]->ship_to_brnch_cd;
        $json_list['ship_to_cd_disabled'] = 'disabled';

    }
    foreach ($m_shipment_to_results as $m_shipment_to_result) {
        $list['ship_to_cd'] = $m_shipment_to_result->ship_to_cd.','.$m_shipment_to_result->ship_to_brnch_cd;
        $list['cust_to_brnch_name1'] = $m_shipment_to_result->cust_to_brnch_name1;
        $list['cust_to_brnch_name2'] = $m_shipment_to_result->cust_to_brnch_name2;
        $list['zip_no'] = preg_replace('/^(\d{3})(\d{4})$/', '$1-$2', $m_shipment_to_result->zip_no);
        $list['address1'] = $m_shipment_to_result->address1;
        $list['address2'] = $m_shipment_to_result->address2;
        $list['address3'] = $m_shipment_to_result->address3;
        $list['address4'] = $m_shipment_to_result->address4;
        if (($list['ship_to_cd'] == $wearer_odr_post['ship_to_cd'].','.$wearer_odr_post['ship_to_brnch_cd'])) {
            $list['ship_to_cd_selected'] = 'selected';
        } else {
            $list['ship_to_cd_selected'] = '';
        }

        array_push($m_shipment_to_list, $list);
    }
    $json_list['m_shipment_to_list'] = $m_shipment_to_list;
    //出荷先--ここまで

    // 郵便番号、住所--ここから
    $query_list = array();
    array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
    array_push($query_list, "rntl_cont_no = '".$wearer_odr_post['rntl_cont_no']."'");
    array_push($query_list, "ship_to_cd = '".$wearer_odr_post['ship_to_cd']."'");
    array_push($query_list, "ship_to_brnch_cd = '".$wearer_odr_post['ship_to_brnch_cd']."'");
    $query = implode(' AND ', $query_list);
    $arg_str = '';
    $arg_str .= 'SELECT ';
    $arg_str .= ' distinct on (ship_to_cd,ship_to_brnch_cd) *';
    $arg_str .= ' FROM m_shipment_to';
    $arg_str .= ' WHERE ';
    $arg_str .= $query;
    $arg_str .= ' ORDER BY ship_to_cd asc,ship_to_brnch_cd asc';
    $m_shipment_to = new MShipmentTo();
    $m_shipment_to_results = new Resultset(NULL, $m_shipment_to, $m_shipment_to->getReadConnection()->query($arg_str));
    $results_array = (array) $m_shipment_to_results;
    $results_cnt = $results_array["\0*\0_count"];
    if ($results_cnt > 0) {
      foreach ($m_shipment_to_results as $m_shipment_to_result) {
        //$json_list['ship_to_cd'] = $m_shipment_to_result->ship_to_cd.','.$m_shipment_to_result->ship_to_brnch_cd;
        //$json_list['cust_to_brnch_name'] = $m_shipment_to_result->cust_to_brnch_name1.$m_shipment_to_result->cust_to_brnch_name2;
        $json_list['zip_no'] = $m_shipment_to_result->zip_no;
        if (!empty($json_list['zip_no'])) {
          $json_list['zip_no'] = preg_replace('/^(\d{3})(\d{4})$/', '$1-$2', $m_shipment_to_result->zip_no);
        }
        if($m_shipment_to_result->address1){
            $json_list['address1'] = $m_shipment_to_result->address1;
        }else{
            $json_list['address1'] = '';
        };
        if($m_shipment_to_result->address2){
            $json_list['address2'] = $m_shipment_to_result->address2;
        }else{
            $json_list['address2'] = '';
        };
        if($m_shipment_to_result->address3){
            $json_list['address3'] = $m_shipment_to_result->address3;
        }else{
            $json_list['address3'] = '';
        };
        if($m_shipment_to_result->address4){
            $json_list['address4'] = $m_shipment_to_result->address4;
        }else{
            $json_list['address4'] = '';
        };
      }
      $json_list['address'] = $json_list['address1'].$json_list['address2'].$json_list['address3'].$json_list['address4'];
    } else {
      $json_list['zip_no'] = "";
      $json_list['address'] = "";
    }
    // 郵便番号、住所--ここまで

    $param_list = "";
    $param_list .= $wearer_odr_post['rntl_cont_no'].':';
    $param_list .= $wearer_odr_post['werer_cd'].':';
    $param_list .= $wearer_odr_post['cster_emply_cd'].':';
    $param_list .= $wearer_odr_post['sex_kbn'].':';
    $param_list .= $wearer_odr_post['rntl_sect_cd'].':';
    $param_list .= $wearer_odr_post['job_type_cd'].':';
    $param_list .= $wearer_odr_post['ship_to_cd'].':';
    $param_list .= $wearer_odr_post['ship_to_brnch_cd'].':';
    $param_list .= $wearer_odr_post['order_reason_kbn'].':';
    $param_list .= $wearer_odr_post['order_tran_flg'].':';
    $param_list .= $wearer_odr_post['wst_order_req_no'].':';
    $param_list .= $wearer_odr_post['order_req_no'];
    $json_list['param'] = $param_list;

    echo json_encode($json_list);
});

/*
 * 「拠点」のセレクトボックス変更時
 */
$app->post('/change_section', function () use ($app) {

    $params = json_decode(file_get_contents('php://input'), true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');
    $cond = $params['cond'];

    $query_list = array();
    $list = array();
    $json_list = array();
    $m_shipment_to_list = array();
    if(!$cond['rntl_sect_cd']&&!$cond['m_shipment_to']){
        $list['ship_to_cd'] = '';
        $list['cust_to_brnch_name1'] = '';
        $list['cust_to_brnch_name2'] = '';
        $list['zip_no'] = '';
        $list['address'] = '';
        array_push($m_shipment_to_list, $list);
        $json_list['change_m_shipment_to_list'] = $m_shipment_to_list;
        echo json_encode($json_list);
        return;
    }elseif($cond['rntl_sect_cd']){
        //画面の「郵便番号」欄、「住所」欄の内容を動的に書き換える
        //--- 検索条件 ---//
        // 出荷先マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
        array_push($query_list, "MShipmentTo.corporate_id = '".$auth['corporate_id']."'");
        // 出荷先マスタ．レンタル契約No.　＝　画面で選択されている契約No.
        array_push($query_list, "MShipmentTo.rntl_cont_no = '".$cond['agreement_no']."'");

        //出荷先」のセレクトボックスが「拠点と同じ」以外が選択状態の場合
        if ($cond['m_shipment_to_name'] != '拠点と同じ') {
            $m_shipment_to = explode(',', $cond['m_shipment_to']);
            //出荷先マスタ．出荷先コード　＝　画面で選択されている出荷先の出荷先コード　AND
            array_push($query_list, "MShipmentTo.ship_to_cd = '".$m_shipment_to[0]."'");
            //出荷先マスタ．出荷先支店コード　＝　画面で選択されている出荷先の出荷先支店コード
            array_push($query_list, "MShipmentTo.ship_to_brnch_cd = '".$m_shipment_to[1]."'");
        }else{
            //出荷先」のセレクトボックスが「拠点と同じ」が選択状態の場合
            //部門マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
            array_push($query_list, "MSection.corporate_id = '".$auth['corporate_id']."'");
            //部門マスタ．レンタル契約No.　＝　画面で選択されている契約No.　AND
            array_push($query_list, "MSection.rntl_cont_no = '".$cond['agreement_no']."'");
            //部門マスタ．レンタル部門コード　＝　画面で選択されている拠点 AND
            array_push($query_list, "MSection.rntl_sect_cd = '".$cond['rntl_sect_cd']."'");
            //出荷先マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
            array_push($query_list, "MShipmentTo.corporate_id = '".$auth['corporate_id']."'");
            //出荷先マスタ．レンタル契約No.　＝　画面で選択されている契約No.　AND
            array_push($query_list, "MShipmentTo.rntl_cont_no = '".$cond['agreement_no']."'");
        }
    }else{
        //--- 検索条件 ---//
        // 出荷先マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
        array_push($query_list, "MShipmentTo.corporate_id = '".$auth['corporate_id']."'");
        // 出荷先マスタ．レンタル契約No.　＝　画面で選択されている契約No.
        array_push($query_list, "MShipmentTo.rntl_cont_no = '".$cond['agreement_no']."'");

        //出荷先」のセレクトボックスが「拠点と同じ」以外が選択状態の場合
        $m_shipment_to = explode(',', $cond['m_shipment_to']);
        //出荷先マスタ．出荷先コード　＝　画面で選択されている出荷先の出荷先コード　AND
        array_push($query_list, "MShipmentTo.ship_to_cd = '".$m_shipment_to[0]."'");
        //出荷先マスタ．出荷先支店コード　＝　画面で選択されている出荷先の出荷先支店コード
        array_push($query_list, "MShipmentTo.ship_to_brnch_cd = '".$m_shipment_to[1]."'");

    }
    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //--- クエリー実行・取得 ---//
    $q_str = MShipmentTo::query()
        ->where($query)
        ->columns(array('MShipmentTo.*'));
    // 「出荷先」のセレクトボックスが「拠点と同じ」が選択状態の場合
    if ($cond['m_shipment_to_name'] == '拠点と同じ') {
        //出荷先マスタ．出荷先コード　＝　部門マスタ．標準出荷先コード　AND
        //出荷先マスタ．出荷先支店コード　＝　部門マスタ．標準出荷先支店コード
        //出荷先マスタ．出荷先コード　＝　画面で選択されている出荷先の出荷先コード　AND
        $q_str->join('MSection', 'MShipmentTo.ship_to_cd = MSection.std_ship_to_cd AND MShipmentTo.ship_to_brnch_cd = MSection.std_ship_to_brnch_cd');
    }
    // 出荷先マスタ．出荷先コード　＝　部門マスタ．標準出荷先コード AND 出荷先マスタ．出荷先支店コード　＝　部門マスタ．標準出荷先支店コード
    $results = $q_str->execute();

    foreach ($results as $result) {
        $list['ship_to_cd'] = $result->ship_to_cd.','.$result->ship_to_brnch_cd;
        $list['cust_to_brnch_name1'] = $result->cust_to_brnch_name1;
        $list['cust_to_brnch_name2'] = $result->cust_to_brnch_name2;
        $list['zip_no'] = $result->zip_no;
        if (!empty($list['zip_no'])) {
          $list['zip_no'] = preg_replace('/^(\d{3})(\d{4})$/', '$1-$2', $list['zip_no']);
        }
        $list['address'] = $result->address1.$result->address2.$result->address3.$result->address4;
        array_push($m_shipment_to_list, $list);
    }
    $json_list['change_m_shipment_to_list'] = $m_shipment_to_list;
    echo json_encode($json_list);
});

/*
 *  着用者のみ登録して終了
 */
$app->post('/input_insert', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);
//    $params = json_decode($_POST['data'], true);
    // アカウントセッション取得
    $auth = $app->session->get('auth');
    $cond = $params['cond'];
    //ChromePhp::LOG($cond);

    $query_list = array();
    $list = array();
    $json_list = array();
    $error_list = array();

    //契約Noのマスタチェック
    $query_list = array();
    // 契約マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
    // 契約マスタ．レンタル契約No.　＝　画面で選択されている契約No.
    array_push($query_list, "rntl_cont_no = '" . $cond['agreement_no'] . "'");

    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //--- クエリー実行・取得 ---//
    $mc_count = MContract::find(array(
        'conditions' => $query
    ))->count();
    //存在しない場合NG
    if ($mc_count == 0) {
        array_push($error_list, '契約Noの値が不正です。');
    }
    if ($cond['cster_emply_cd']&&!is_alnum($cond['cster_emply_cd'])) {
        array_push($error_list, '社員コードは半角英数字で入力してください。');
    }
    // 社員コード
    if ($cond['cster_emply_cd_chk']) {
        if (mb_strlen($cond['cster_emply_cd']) == 0) {
            $json_list["error_code"] = "1";
            $error_msg = "社員コードありにチェックしている場合、社員コードを入力してください。";
            array_push($error_list, $error_msg);
        }
    }
    if (!$cond['cster_emply_cd_chk']) {
        if (mb_strlen($cond['cster_emply_cd']) > 0) {
            $json_list["error_code"] = "1";
            $error_msg = "社員コードありにチェックしていない場合、社員コードの入力は不要です。";
            array_push($error_list, $error_msg);
        }
    }
    $query_list = array();
    if (mb_strlen($cond['cster_emply_cd']) > 0) {
        if (strlen(mb_convert_encoding($cond['cster_emply_cd'], "SJIS")) > 10) {
            $json_list["error_code"] = "1";
            $error_msg = "社員コードが規定の文字数をオーバーしています。";
            array_push($json_list["error_msg"], $error_msg);
        }
        // 社員コード重複チェック
        $member_no_overlap_err = "";
        $query_list = array();
        array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
        array_push($query_list, "rntl_cont_no = '" . $cond['agreement_no'] . "'");
        array_push($query_list, "cster_emply_cd = '" . $cond['cster_emply_cd'] . "'");
        if ($cond['werer_cd']) {
            //検索画面から来た場合
            array_push($query_list, "werer_cd <> '" . $cond['werer_cd'] . "'");
        }

        $query = implode(' AND ', $query_list);
        $arg_str = '';
        $arg_str .= 'SELECT ';
        $arg_str .= '*';
        $arg_str .= ' FROM ';
        $arg_str .= 'm_wearer_std';
        $arg_str .= ' WHERE ';
        $arg_str .= $query;
        $m_wearer_std = new MWearerStd();
        $results = new Resultset(NULL, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
        $results_array = (array)$results;
        $results_cnt = $results_array["\0*\0_count"];
        if ($results_cnt > 0) {
            array_push($error_list, '既に社員コードが使用されています。');
        }
        if (empty($member_no_overlap_err)) {
            $arg_str = '';
            $arg_str .= 'SELECT ';
            $arg_str .= '*';
            $arg_str .= ' FROM ';
            $arg_str .= 'm_wearer_std_tran';
            $arg_str .= ' WHERE ';
            $arg_str .= $query;
            $m_wearer_std_tran = new MWearerStdTran();
            $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
            $results_array = (array)$results;
            $results_cnt = $results_array["\0*\0_count"];
            if ($results_cnt > 0) {
                array_push($error_list, '既に社員コードが使用されています。');
            }
        }
    }
    if (byte_cnt($cond['cster_emply_cd']) > 10) {
        array_push($error_list, '社員コードの文字数が多すぎます。（最大半角10文字）');
    }

    if (!$cond['werer_name']) {
        array_push($error_list, '着用者名が未入力です。');
    }
    if (byte_cnt($cond['werer_name']) > 22) {
        array_push($error_list, '着用者名の文字数が多すぎます。（最大全角11文字）');
    }
    //SJISにない文字を?に変換
    //着用者名
    if(!empty($cond['werer_name'])) {
        $str_utf8 = $cond['werer_name'];
        if (convert_not_sjis($str_utf8) !== true) {
            $output_text = convert_not_sjis($str_utf8);
            array_push($error_list, '着用者名に使用できない文字が含まれています。' . "$output_text");
        };
    }

    if (byte_cnt($cond['werer_name_kana']) > 25) {
        array_push($error_list, '着用者名(カナ)の文字数が多すぎます。（最大全角12文字）');
    }

    //全角カタカナ 全角スペースチェック
    if(!empty($cond['werer_name_kana'])){
        $kana = $cond['werer_name_kana'];
        if (kana_check($kana) === false){
            array_push($error_list, '着用者名（カナ）に全角カタカナまたは全角スペース以外が入力されています');
        }
    }
    //SJISにない文字を?に変換
    //着用者カナ
    if(!empty($cond['werer_name_kana'])) {
        $str_utf8 = $cond['werer_name_kana'];
        if (convert_not_sjis($str_utf8) !== true) {
            $output_text = convert_not_sjis($str_utf8);
            array_push($error_list, '着用者名（カナ）に使用できない文字が含まれています。' . "$output_text");
        };
    }
    if (!$cond['sex_kbn']) {
        array_push($error_list, '性別が未選択です。');
    }
    if (!$cond['resfl_ymd']) {
        array_push($error_list, '着用開始日が未入力です。');
    }
    $m_section_count = 0;
    if (!$cond['rntl_sect_cd']) {
        array_push($error_list, '拠点が未選択です。');
    }else{
        //拠点のマスタチェック
        $query_list = array();
        // 部門マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
        array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
        // 部門マスタ．レンタル契約No.　＝　画面で選択されている契約No.
        array_push($query_list, "rntl_cont_no = '" . $cond['agreement_no'] . "'");
        // 部門マスタ．レンタル部門コード　＝　画面で選択されている拠点
        array_push($query_list, "rntl_sect_cd = '" . $cond['rntl_sect_cd'] . "'");

        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $query_list);
        //--- クエリー実行・取得 ---//
        $m_section = MSection::find(array(
            'conditions' => $query
        ));
        $m_section_count = $m_section->count();
        //存在しない場合NG
        if ($m_section_count == 0) {
            array_push($error_list, '拠点の値が不正です。');
        }
    }
    if (!$cond['job_type']) {
        array_push($error_list, '貸与パターンが未選択です。');
    }else{
        //貸与パターンのマスタチェック
        $query_list = array();
        // 職種マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
        array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
        // 職種マスタ．レンタル契約No.　＝　画面で選択されている契約No.
        array_push($query_list, "rntl_cont_no = '" . $cond['agreement_no'] . "'");
        $deli_job = explode(',', $cond['job_type']);
        // 職種マスタ．レンタル部門コード　＝　画面で選択されている貸与パターン
        array_push($query_list, "job_type_cd = '" . $deli_job[0] . "'");

        //sql文字列を' AND 'で結合
        $query = implode(' AND ', $query_list);
        //--- クエリー実行・取得 ---//
        $m_job_type = MJobType::find(array(
            'conditions' => $query
        ));
        $m_job_type_cnt = $m_job_type->count();
        //存在しない場合NG
        if ($m_job_type_cnt == 0) {
            array_push($error_list, '貸与パターンの値が不正です。');
        }
    }
    //出荷先のマスタチェック
    $query_list = array();
    // 出荷先マスタ．企業ID　＝　ログインしているアカウントの企業ID　AND
    array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");

    if ($cond['ship_to_cd'] && $cond['ship_to_brnch_cd']) {
        // 出荷先マスタ．出荷先コード　＝　画面で選択されている出荷先コード
        array_push($query_list, "ship_to_cd = '" . $cond['ship_to_cd'] . "'");
        // 出荷先マスタ．出荷先支店コード　＝　画面で選択されている出荷先支店コード
        array_push($query_list, "ship_to_brnch_cd = '" . $cond['ship_to_brnch_cd'] . "'");
    } else {
        if($m_section_count > 0){
            // 部門マスタ．標準出荷先コード
            array_push($query_list, "ship_to_cd = '" . $m_section[0]->std_ship_to_cd . "'");
            // 部門マスタ．標準出荷先支店コード
            array_push($query_list, "ship_to_brnch_cd = '" . $m_section[0]->std_ship_to_brnch_cd . "'");
            $cond['ship_to_cd'] = $m_section[0]->std_ship_to_cd;
            $cond['ship_to_brnch_cd'] = $m_section[0]->std_ship_to_brnch_cd;
        }
    }
    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    //--- クエリー実行・取得 ---//
    $m_shipment_to_cnt = MShipmentTo::find(array(
        'conditions' => $query
    ))->count();

    //存在しない場合NG
    if ($m_shipment_to_cnt == 0) {
        array_push($error_list, '出荷先の値が不正です。');
    }
//    DB登録
    if ($error_list) {
        $json_list['errors'] = $error_list;
        echo json_encode($json_list);
        return true;
    }
    if ($params['mode'] == 'check') {
        $json_list['ok'] = 'ok';
        echo json_encode($json_list);
        return true;
    }
    $transaction = $app->transactionManager->get();
    try {
        //着用者基本マスタトラン
        $m_wearer_std_trans = new MWearerStdTran();
        $results = new Resultset(NULL, $m_wearer_std_trans, $m_wearer_std_trans->getReadConnection()->query('begin'));
        $now = date('Y/m/d H:i:s.sss');

        //着用者基本マスタ_統合ハッシュキー(企業ID、着用者コード、レンタル契約No.、レンタル部門コード、職種コード)
        $wearer_odr_post = $app->session->get("wearer_odr_post");
        //着用者基本情報トラン
        $now = date('Y/m/d H:i:s.sss');
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query("select nextval('werer_cd_seq')"));
        if(!$wearer_odr_post){
            $werer_cd = str_pad($results[0]->nextval, 6, '0', STR_PAD_LEFT); //着用者コード
            $m_wearer_std_comb_hkey = md5($auth['corporate_id'] .  '-' . str_pad($results[0]->nextval, 10, '0', STR_PAD_LEFT) .  '-' . $cond['agreement_no'] . '-' . $cond['rntl_sect_cd'] .  '-' . $deli_job[0]);
            $order_req_no = ''; //発注No
        }else{
            $werer_cd = $wearer_odr_post['werer_cd'];
            $m_wearer_std_comb_hkey = $wearer_odr_post['m_wearer_std_comb_hkey'];
            if(isset($wearer_odr_post['m_wearer_std_comb_hkey'])){
                $m_wearer_std_tran = MWearerStdTran::find(array(
                    'conditions' => 'm_wearer_std_comb_hkey = '."'".$m_wearer_std_comb_hkey."'"
                ));
                if($m_wearer_std_tran->count()==0){
                    $m_wearer_std_comb_hkey = md5($auth['corporate_id'] .  '-' . str_pad($results[0]->nextval, 10, '0', STR_PAD_LEFT) .  '-' . $cond['agreement_no'] . '-' . $cond['rntl_sect_cd'] .  '-' . $deli_job[0]);
                    $order_req_no = ''; //発注No
                }else{
                    $m_wearer_std_tran = $m_wearer_std_tran[0];
                    $order_req_no = $m_wearer_std_tran->getOrderReqNo();//発注No
                }
            }
        }
        $corporate_id = $auth['corporate_id']; //企業ID
        $rntl_cont_no = $cond['agreement_no']; //レンタル契約No.
        $rntl_cont_no_bef = ''; //レンタル契約No.（前）
        $rntl_sect_cd_bef = '';//レンタル部門コード（前）
        $job_type_cd_bef = ''; //職種コード（前）
        $werer_sts_kbn_bef = ''; //着用者状況区分（前）
        $resfl_ymd_bef = ''; //異動日（前）
        $order_sts_kbn = '1'; //発注状況区分 汎用コード：貸与
        $upd_kbn = '1';//更新区分　汎用コード：web発注システム（新規登録）
        $web_upd_date = $now;//WEB更新日付
        $snd_kbn = '0';//送信区分
        $snd_date = $now;//送信日時
        $del_kbn = '0';//削除区分
        $rgst_date = $now;//登録日時
        $rgst_user_id = $auth['accnt_no'];//登録ユーザーID
        $rntl_sect_cd = $cond['rntl_sect_cd']; //レンタル部門コード
        $job_type_cd = $deli_job[0];//職種コード
        $cster_emply_cd = $cond['cster_emply_cd'];//客先社員コード
        $werer_name = $cond['werer_name'];//着用者名
        $werer_name_kana = $cond['werer_name_kana']; //着用者名（カナ）
        $sex_kbn = $cond['sex_kbn'];//性別区分
        $werer_sts_kbn = '7';//着用者状況区分
//        $appointment_ymd = date("Ymd", strtotime($cond['appointment_ymd']));//発令日
        $resfl_ymd = date("Ymd", strtotime($cond['resfl_ymd']));//着用開始日
        $ship_to_cd = $cond['ship_to_cd']; //出荷先コード
        $ship_to_brnch_cd = $cond['ship_to_brnch_cd']; //出荷先支店コード
        $upd_date = $now;//更新日時
        $upd_user_id = $auth['accnt_no'];//更新ユーザーID
        $upd_pg_id = $auth['accnt_no'];//更新プログラムID
        $m_job_type_comb_hkey = $m_job_type[0]->m_job_type_comb_hkey;//職種マスタ_統合ハッシュキー
        $m_section_comb_hkey = $m_section[0]->m_section_comb_hkey;//部門マスタ_統合ハッシュキー

        // 着用者登録
        if ($wearer_odr_post) {
            // 現発注Noの発注情報トランをクリーン
//            if ($wearer_odr_post['order_tran_flg'] == '1') {
            $query_list = array();
            array_push($query_list, "corporate_id = '" . $auth['corporate_id'] . "'");
            array_push($query_list, "m_wearer_std_comb_hkey = '" . $wearer_odr_post['m_wearer_std_comb_hkey'] . "'");
            array_push($query_list, "werer_sts_kbn = '7'");
            array_push($query_list, "order_sts_kbn = '1'");
            $query = implode(' AND ', $query_list);
            $arg_str = "";
            $arg_str = "DELETE FROM ";
            $arg_str .= "m_wearer_std_tran";
            $arg_str .= " WHERE ";
            $arg_str .= $query;
            $m_wearer_std = new MWearerStdTran();
            $results = new Resultset(NULL, $m_wearer_std, $m_wearer_std->getReadConnection()->query($arg_str));
        }
        //更新もしくは新規追加

        $calum_list = array();
        $values_list = array();

        array_push($calum_list, "m_wearer_std_comb_hkey");
        array_push($values_list, "'" . $m_wearer_std_comb_hkey . "'");
        // 企業ID
        array_push($calum_list, "corporate_id");
        array_push($values_list, "'" . $corporate_id . "'");
        // 発注依頼No.
        array_push($calum_list, "order_req_no");
        array_push($values_list, "'" . $order_req_no . "'");
        // レンタル契約No
        array_push($calum_list, "rntl_cont_no");
        array_push($values_list, "'" . $rntl_cont_no . "'");
        // 着用者コード
        array_push($calum_list, "werer_cd");
        array_push($values_list, "'" . $werer_cd . "'");
        // レンタル契約No.（前）
        array_push($calum_list, "rntl_cont_no_bef");
        array_push($values_list, "'" . $rntl_cont_no_bef . "'");
        // レンタル部門コード（前）
        array_push($calum_list, "rntl_sect_cd_bef");
        array_push($values_list, "'" . $rntl_sect_cd_bef . "'");
        // 職種コード（前）
        array_push($calum_list, "job_type_cd_bef");
        array_push($values_list, "'" . $job_type_cd_bef . "'");
        // 着用者状況区分（前）
        array_push($calum_list, "werer_sts_kbn_bef");
        array_push($values_list, "'" . $werer_sts_kbn_bef . "'");
        // 異動日（前）
        array_push($calum_list, "resfl_ymd_bef");
        array_push($values_list, "'" . $resfl_ymd_bef . "'");
        // 発注状況区分 汎用コード：貸与
        array_push($calum_list, "order_sts_kbn");
        array_push($values_list, "'" . $order_sts_kbn . "'");
        // 更新区分　汎用コード：web発注システム（新規登録）
        array_push($calum_list, "upd_kbn");
        array_push($values_list, "'" . $upd_kbn . "'");
        // WEB更新日付
        array_push($calum_list, "web_upd_date");
        array_push($values_list, "'" . $web_upd_date . "'");
        // 送信区分
        array_push($calum_list, "snd_kbn");
        array_push($values_list, "'" . $snd_kbn . "'");
        // 送信日時
        array_push($calum_list, "snd_date");
        array_push($values_list, "'" . $snd_date . "'");
        // 削除区分
        array_push($calum_list, "del_kbn");
        array_push($values_list, "'" . $del_kbn . "'");
        // 登録日時
        array_push($calum_list, "rgst_date");
        array_push($values_list, "'" . $rgst_date . "'");
        // 登録ユーザーID
        array_push($calum_list, "rgst_user_id");
        array_push($values_list, "'" . $rgst_user_id . "'");
        // レンタル部門コード
        array_push($calum_list, "rntl_sect_cd");
        array_push($values_list, "'" . $rntl_sect_cd . "'");
        // 職種コード
        array_push($calum_list, "job_type_cd");
        array_push($values_list, "'" . $job_type_cd . "'");
        // 客先社員コード
        array_push($calum_list, "cster_emply_cd");
        array_push($values_list, "'" . $cster_emply_cd . "'");
        // 着用者名
        array_push($calum_list, "werer_name");
        array_push($values_list, "'" . $werer_name . "'");
        // 着用者名（カナ）
        array_push($calum_list, "werer_name_kana");
        array_push($values_list, "'" . $werer_name_kana . "'");
        // 性別区分
        array_push($calum_list, "sex_kbn");
        array_push($values_list, "'" . $sex_kbn . "'");
        // 着用者状況区分
        array_push($calum_list, "werer_sts_kbn");
        array_push($values_list, "'" . $werer_sts_kbn . "'");
        // 発令日
//        array_push($calum_list, "appointment_ymd");
//        array_push($values_list, "'" . $appointment_ymd . "'");
        // 着用開始日
        array_push($calum_list, "resfl_ymd");
        array_push($values_list, "'" . $resfl_ymd . "'");
        // 出荷先コード
        array_push($calum_list, "ship_to_cd");
        array_push($values_list, "'" . $ship_to_cd . "'");
        // 出荷先支店コード
        array_push($calum_list, "ship_to_brnch_cd");
        array_push($values_list, "'" . $ship_to_brnch_cd . "'");
        // 更新日時
        array_push($calum_list, "upd_date");
        array_push($values_list, "'" . $upd_date . "'");
        // 更新ユーザーID
        array_push($calum_list, "upd_user_id");
        array_push($values_list, "'" . $upd_user_id . "'");
        // 更新プログラムID
        array_push($calum_list, "upd_pg_id");
        array_push($values_list, "'" . $upd_pg_id . "'");
        // 職種マスタ_統合ハッシュキー
        array_push($calum_list, "m_job_type_comb_hkey");
        array_push($values_list, "'" . $m_job_type_comb_hkey . "'");
        // 部門マスタ_統合ハッシュキー
        array_push($calum_list, "m_section_comb_hkey");
        array_push($values_list, "'" . $m_section_comb_hkey . "'");

        $calum_query = implode(',', $calum_list);
        $values_query = implode(',', $values_list);

        $arg_str = "";
        $arg_str = "INSERT INTO m_wearer_std_tran";
        $arg_str .= "(" . $calum_query . ")";
        $arg_str .= " VALUES ";
        $arg_str .= "(" . $values_query . ")";
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
    } catch (Exception $e) {
        // トランザクションロールバック
        $m_wearer_std_tran = new MWearerStdTran();
        $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('rollback'));
        $json_list["error_code"] = "1";
        array_push($error_list, '着用者の登録に失敗しました。');
        $json_list['errors'] = $error_list;

        echo json_encode($json_list);
        return;
    }
    // トランザクションコミット
    $m_wearer_std_tran = new MWearerStdTran();
    $results = new Resultset(NULL, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query('commit'));
    $app->session->remove("wearer_odr_post");
    echo json_encode($json_list);
    return;
});

/*
 *  着用者取消チェック
 */
$app->post('/input_delete_check', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);
    $wearer_odr_post = $app->session->get("wearer_odr_post");
    $json_list = array();
    $error_list = array();
    //--着用者基本マスタトラン削除--//
    // 発注情報トランを参照
    $query_list = array();
    array_push($query_list, "t_order_tran.m_wearer_std_comb_hkey = '".$wearer_odr_post['m_wearer_std_comb_hkey']."'");
    $query = implode(' AND ', $query_list);

    $arg_str = "";
    $arg_str = "SELECT ";
    $arg_str .= "*";
    $arg_str .= " FROM ";
    $arg_str .= "t_order_tran";
    $arg_str .= " WHERE ";
    $arg_str .= $query;

    $t_order_tran = new TOrderTran();
    $results = new Resultset(null, $t_order_tran, $t_order_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];
    if ($results_cnt == 0) {
        $json_list['ok'] = 'ok';
        echo json_encode($json_list);

        return true;
    } else {
        array_push($error_list, '先に商品明細を削除してください。');
        $json_list['error_msg'] = $error_list;
        echo json_encode($json_list);

        return true;
    }

});

/*
 *  着用者取消
 */
$app->post('/input_delete', function () use ($app) {

    $params = json_decode(file_get_contents("php://input"), true);
    try{
        $query_list = array();
        $wearer_odr_post = $app->session->get("wearer_odr_post");
        array_push($query_list, "m_wearer_std_tran.m_wearer_std_comb_hkey = '".$wearer_odr_post['m_wearer_std_comb_hkey']."'");
        // 発注区分「着用者編集」ではない
        array_push($query_list, "m_wearer_std_tran.order_sts_kbn <> '6'");
        $query = implode(' AND ', $query_list);

        $arg_str = "";
        $arg_str = "DELETE FROM ";
        $arg_str .= "m_wearer_std_tran";
        $arg_str .= " WHERE ";
        $arg_str .= $query;

        $transaction = $app->transactionManager->get();
        //着用者基本マスタトラン
        $m_wearer_std_tran = new MWearerStdTran();
        $m_wearer_std_tran->setTransaction($transaction);
        $results = new Resultset(null, $m_wearer_std_tran, $m_wearer_std_tran->getReadConnection()->query($arg_str));
        $result_obj = (array)$results;
        $results_cnt = $result_obj["\0*\0_count"];
        if ($results_cnt == 0) {
            array_push($error_list, '着用者の取消に失敗しました。');
            $json_list['error_msg'] = $error_list;
            echo json_encode($json_list);
            $transaction->rollback();
            return;
        }else{
            $json_list['ok'] = 'ok';
            echo json_encode($json_list);
            $app->session->remove("wearer_odr_post");
            $transaction->commit();
            return;
        }
    } catch (Exception $e) {
        array_push($error_list, '着用者の取消に失敗しました。');
        $json_list['error_msg'] = $error_list;
        echo json_encode($json_list);
        $transaction->rollback();
    }
    return;
});
