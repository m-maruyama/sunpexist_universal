<?php
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

// $app->after(function()use($app){
//
// });
/**

/**
 * CSV取込
 */
$app->post('/import_csv', function()use($app){
	$json_list = array();
	$error_list = array();
	try{
		$file = file($_FILES['file']['tmp_name']);
		mb_convert_variables("UTF-8", "SJIS-win", $file);
		$chk_file = $file;
		unset($chk_file[0]); //チェック時はヘッダーを無視する
	} catch(Exception $e){
		array_push($error_list ,'取り込んだファイルの形式が不正です。');
		$json_list['errors'] = $error_list;
		echo json_encode($json_list);
		return true;
	}
	$line_cnt = 1; //行数
	$new_list = array();
	$no_chk_list = array();
	$no_list = array(); //よろず発注Noリスト
	$auth = $app->session->get("auth");
	try{
		foreach ($chk_file as $line) {
			$upflg = false;
			//csvの１行を配列に変換する
			$line_list = str_getcsv($line,',','"');
			// 項目数チェック: 行単位の項目数が、仕様通りの項目数(12)かをチェックする。
			if(count($line_list)!=12){
				$cnt_list = array();
				//項目数が不正な場合、エラーメッセージを配列に格納
				array_push($error_list, $line_cnt.'行目の項目数が不正です');
				continue;
			}
			// 必須チェック: 行単位の発注区分毎の必須値が、それぞれ仕様通り設定されているかをチェックする。
			$item_cnt = 0;
			switch ($line_list[6]){//よろず発注区分の値
				case 1:
					//貸与の場合
					foreach($line_list as $item){
						if($item_cnt != 5&&$item_cnt != 11){
							if(!$item){
								array_push($error_list, $line_cnt.'行目の必須項目が設定されていません');
							}
						}
						$item_cnt++;
					}
				break;
				case 2:
					//返却の場合
					foreach($line_list as $item){
						// if($item_cnt != 2||$item_cnt != 3||$item_cnt != 4||$item_cnt != 7||$item_cnt != 8||$item_cnt != 9||$item_cnt != 10){
						if($item_cnt != 4&&$item_cnt != 7&&$item_cnt != 8&&$item_cnt != 9&&$item_cnt != 10&&$item_cnt != 11){
							if(!$item){
								array_push($error_list, $line_cnt.'行目の必須項目が設定されていません');
							}
						}
						$item_cnt++;
					}
				break;
				case 3:
					//サイズ交換の場合
					foreach($line_list as $item){
						if($item_cnt != 5&&$item_cnt != 11){
							if(!$item){
								array_push($error_list, $line_cnt.'行目の必須項目が設定されていません');
							}
						}
						$item_cnt++;
					}
				break;
				case 4:
					//消耗交換の場合
					foreach($line_list as $item){
						if($item_cnt != 5&&$item_cnt != 11){
							if(!$item){
								array_push($error_list, $line_cnt.'行目の必須項目が設定されていません');
							}
						}
						$item_cnt++;
					}
				break;
				case 5:
					//異動の場合
					foreach($line_list as $item){
						if($item_cnt != 5&&$item_cnt != 11){
							if(!$item){
								array_push($error_list, $line_cnt.'行目の必須項目が設定されていません');
							}
						}
						$item_cnt++;
					}
				break;
				default:
				//式がいずれの値にも等しくない時の処理;
				array_push($error_list, $line_cnt.'行目のよろず発注区分が不正です');
			}
			//フォーマットチェック: 行単位の各項目のフォーマット形式が、それぞれ仕様通りのフォーマットであるかチェックする。
			$error_list = chk_format($error_list,$line_list,$line_cnt);
			if($line_list[6]=='1'&&$line_list[3]!='13'){//設計書の①
				//貸与の場合
				//マスタ存在チェック(社員番号)
				$result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'" . ' AND werer_sts_kbn = '."'1'" ));
				if(count($result) > 0){
					array_push($error_list ,error_msg_master($line_cnt,'社員番号'));
				}
			}elseif($line_list[6]=='1'&&$line_list[3]=='13'){//設計書の③
				//マスタ存在チェック(社員番号)
				$result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'"));
				if(count($result) > 0){
					//存在した場合は、稼働中である事
					$result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'" . ' AND werer_sts_kbn = '."'1'" ));
					if(count($result) == 0){
						array_push($error_list ,error_msg_master($line_cnt,'社員番号'));
					}
				}
			} else {//設計書の②
				//マスタ存在チェック(社員番号)
				$result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'" . ' AND werer_sts_kbn = '."'1'" ));
				if(count($result) == 0){
					array_push($error_list ,error_msg_master($line_cnt,'社員番号'));
				}
			}
			//マスタ存在チェック(支店コード)
			// $result = MSection::find(array('conditions' => 'rntl_sect_cd = '."'".$line_list[2]."'"));
			// if(count($result) <= 0){
				// array_push($error_list ,error_msg_master($line_cnt,'支店コード'));
			// }
			//マスタ存在チェック(貸与パターン)
			$result = MJobType::find(array('conditions' => 'job_type_cd = '."'".$line_list[3]."'"));
			if(count($result) <= 0){
				array_push($error_list ,error_msg_master($line_cnt,'貸与パターン'));
			}
			//マスタ存在チェック(商品コード)
			if($line_list[6]!='2'){
				$result = MItem::find(array('conditions' => 'item_cd = '."'".$line_list[7]."'"));
				if(count($result) <= 0){
					array_push($error_list ,error_msg_master($line_cnt,'商品コード'));
				}
				//マスタ存在チェック(サイズコード)
				$result = MItem::find(array('conditions' => 'size_cd = '."'".$line_list[8]."'"));
				if(count($result) <= 0){
					array_push($error_list ,error_msg_master($line_cnt,'サイズコード'));
				}
				//マスタ存在チェック(色コード)
				$result = MItem::find(array('conditions' => 'color_cd = '."'".$line_list[9]."'"));
				if(count($result) <= 0){
					array_push($error_list ,error_msg_master($line_cnt,'色コード'));
				}
			}
			//よろず発注Noファイル内重複チェック
			//同じよろず発注Noがあるか
			if(array_search($line_list[0],$no_list)){
				//あったら発注No+社員番号でチェック、社員番号が違う場合、よろず発注Noが重複
				if(!array_search(strval($line_list[0]).strval($line_list[1]),$no_chk_list)){
					array_push($error_list , $line_cnt.'行目のよろず発注Noが、重複して使用されています。');
				}
			}else{
				//よろず発注Noが見つからない場合、チェック用の配列につめる
				array_push($no_list,strval($line_list[0]));
				array_push($no_chk_list,strval($line_list[0]).strval($line_list[1]));
			}

			//よろず発注No DB重複チェック
			$result = TOrder::find(array('conditions' => 'order_req_no = '."'".$line_list[0]."'"));
			if(count($result) > 0){
				  array_push($error_list , $line_cnt.'行目のよろず発注Noは、発注で既に使用されています。');
			}

			// インポートログテーブル重複チェック
			// CSVファイル内の「よろず発注No」が、インポートログテーブルの送信フラグ＝1：送信済のレコードで存在しない事。
			$result = TImportLog::find(array('conditions' => 'order_req_no = '."'".$line_list[0]."'".' AND send_flg = 1'));
			if(count($result) > 0){
				  array_push($error_list , $line_cnt.'行目のよろず発注Noは、過去のCSV取込で既に使用されています。');
			}
			//DB登録用のリストに格納
			array_push($new_list,$line_list);
			$line_cnt++;
		}
		// 登録処理
		if(empty($error_list)){
			//新規データと既存データをよろず発注Noで並べ替え
			array_multisort($new_list, array_column($new_list, 0));

			$before_no='';
			$no_line = 1;
			$transaction = $app->transactionManager->get();
			//既存データ削除
			$i_log = TImportLog::find(array('conditions' => 'send_flg = 0'));
			foreach ($i_log as $del) {
				if ($del->delete() == false) {
					$json_list['errors'] = '発注情報が登録出来ませんでした'.$e;
					echo json_encode($json_list);
					return true;
				}
			}
			$auth = $app->session->get("auth");
			$cnt = 1;
			$order_no_list = array();
			foreach ($new_list as $line_new) {
				//行番号
				if($before_no==$line_new[0]){
					$no_line++;
				} else{
					$no_line = 1;
				}
				$before_no = $line_new[0];
				//同じよろず発注Noが、インポートログテーブルに存在しない場合、よろず発注No単位にインポートログテーブルへ新規登録を行う。
				data_save($line_new,$cnt,$no_line,$auth,$order_no_list);
			}
		}else{
			//エラーがあったら画面にエラーメッセージ
			$json_list['errors'] = $error_list;
			echo json_encode($json_list);
			return true;
		}
	} catch(Exception $e){
		array_push($error_list,'プログラム内でエラーが発生しました。('.$e->getMessage().')');
		$json_list['errors'] = $error_list;
		echo json_encode($json_list);
		return true;
	}
	// エラーがなければコミット
	if(!empty($error_list)){
		//エラーがあったら画面にエラーメッセージ
		$json_list['errors'] = $error_list;
		echo json_encode($json_list);
		return true;
	} else {
		$json_list['ok'] = 'ok';
		$transaction->commit();
	}
	echo json_encode($json_list);
	return true;
});
/**
 *  ・データ登録
 *
 *  関数の詳細:
 *  インポートされたCSVのデータが正常な場合、DBに登録する。
 *
 * @param array $line_list １行データ
 * @param integer $cnt 着用者コード新規発行時の連番
 * @param integer $no_line よろず発注行No
 * @param array  $auth アカウント情報
 * @param array  $no_list よろず発注Noリスト
 * @return なし
 */
function data_save($line_list,&$cnt,$no_line,$auth,&$order_no_list){
	//未送信のデータのみ入れ替え処理を行う
	//ChromePhp::log('未送信のデータのみ入れ替え処理を行う');

	$t_i_job = new TImportJob();
	//ChromePhp::log($t_i_job);

	$result = MWearerStd::find(array('conditions' => 'cster_emply_cd = '."'".$line_list[1]."'"));

	$i = 1;
	$i = $i++;

	if(count($result) <= 0){

		if(in_array($line_list[0],$order_no_list)){
			$t_i_job->werer_cd = array_search($line_list[0],$order_no_list);

		}else{
			$day = sprintf("%02d", date("j"));
			$num = sprintf("%04d", $cnt);
			$t_i_job->werer_cd = $day.$num;
			$cnt++;
		}

	}else{
		//社員番号を着用者コードに
		$t_i_job->werer_cd = $result[0]->werer_cd; //着用者コード
	}

	//よろず発注Noと着用者コードを詰めていく
	if(!in_array($line_list[0],$order_no_list)){
		$order_no_list[$t_i_job->werer_cd] = $line_list[0];
	}
	$t_i_job->job_no = '1'; //よろず発注No
	$t_i_job->line_no = $line_list[15]; // よろず発注行No
	//ChromePhp::log('t_i_jobのline_no');
	//ChromePhp::log($t_i_job->line_no);


	$t_i_job->order_req_no = $line_list[0]; //よろず発注No
	$t_i_job->order_req_line_no = $no_line; // よろず発注行No
	$t_i_job->cster_emply_cd = $line_list[1]; //社員番号
	$t_i_job->rntl_sect_cd = $line_list[2]; //支店コード
	$t_i_job->rent_pattern_code = $line_list[3]; //貸与パターン
	$t_i_job->wear_start = $line_list[4]; //着用開始日
	$t_i_job->wear_end = $line_list[5]; //着用終了日
	$t_i_job->order_kbn = $line_list[6]; //よろず発注区分
	$t_i_job->item_cd = $line_list[7]; //商品コード
	$t_i_job->size_cd = $line_list[8]; //サイズコード
	$t_i_job->color_cd = $line_list[9]; //色コード
	if($line_list[10]){
		$t_i_job->quantity = $line_list[10]; //数量
	}
	$t_i_job->message = $line_list[11]; //伝言欄
	$t_i_job->user_id = $auth['user_id']; //インポートユーザーID
	$t_i_job->import_time = date( "Y/m/d H:i:s.sss", time() ); //インポート日時
	$t_i_job->upd_user_id = $auth['user_id']; //更新ユーザー
	$t_i_job->upd_date = date( "Y/m/d H:i:s.sss", time() ); //更新日時
	$t_i_job->rgst_user_id = $auth['user_id']; //登録ユーザーID
	$t_i_job->rgst_date = date( "Y/m/d H:i:s.sss", time() ); //登録日時

	ChromePhp::log($t_i_job);

	if ($t_i_job->create() == false) {
		$json_list['errors'] = array('csvファイルの登録に失敗しました。');
		echo json_encode($json_list);
		return true;
	}
}

/**
 * 納品実績照会検索
 */
$app->post('/delivery/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	$cond = $params['cond'];
	$page = $params['page'];

	$query_list = array();
	//出荷日from
	if(isset($cond['send_day_from'])){
		array_push($query_list,"TO_DATE(TDeliveryGoodsState.ship_ymd,'YYYYMMDD') >= TO_DATE('".$cond['send_day_from']."','YYYY/MM/DD')");
	}
	//出荷日to
	if(isset($cond['send_day_to'])){
		array_push($query_list,"TO_DATE(TDeliveryGoodsState.ship_ymd,'YYYYMMDD') <= TO_DATE('".$cond['send_day_to']."','YYYY/MM/DD')");
	}
	//よろず発注区分
	$order_kbn = array();
	if($cond['order_kbn0']){
		array_push($order_kbn,'1');
	}
	if($cond['order_kbn1']){
		array_push($order_kbn,'3');
	}
	if($cond['order_kbn2']){
		array_push($order_kbn,'4');
	}
	if($cond['order_kbn3']){
		array_push($order_kbn,'5');
	}
	$order_kbn_str = implode("','",$order_kbn);

	//発注情報テーブルを検索(副問い合わせ風)
	$order_status = '';
	$order_sts_kbn = '';
	$order_list = array();
	//よろず発注No
	if(isset($cond['no'])){
		array_push($query_list,"TOrder.order_req_no LIKE '%".$cond['no']."%'");
	}
	//社員番号
	if(isset($cond['member_no'])){
		array_push($query_list,"TOrder.cster_emply_cd LIKE '%".$cond['member_no']."%'");
	}
	//拠点
	if(isset($cond['office'])){
		array_push($query_list,"MSection.rntl_sect_name LIKE '%".$cond['office']."%'");
	}

	//貸与パターン
	if(isset($cond['job_type'])){
		array_push($query_list,"MJobType.job_type_cd = '".$cond['job_type']."'");
	}
	//発注日from
	if(isset($cond['order_day_from'])){
		array_push($query_list,"TO_DATE(TOrder.order_req_ymd,'YYYYMMDD') >= TO_DATE('".$cond['order_day_from']."','YYYY/MM/DD')");
	}
	//発注日to
	if(isset($cond['order_day_to'])){
		array_push($query_list,"TO_DATE(TOrder.order_req_ymd,'YYYYMMDD') <= TO_DATE('".$cond['order_day_to']."','YYYY/MM/DD')");
	}
	if($order_kbn_str){
		$order_sts_kbn = "TOrder.order_sts_kbn in ('".$order_kbn_str."')";
		array_push($query_list,$order_sts_kbn);
	}
	//ステータス
	$status = array();
	$status_list = array();
	if($cond['status0']){
		array_push($status_list,"TOrder.order_status = '1'");
	}
	if($cond['status1']){
		array_push($status_list,"TOrder.order_status = '2'");
	}
	//受領ステータス
	$r_status = '';
	if($cond['status2']){
		//未受領のみ
		array_push($status_list,"(TDeliveryGoodsState.receipt_status = '1' or TDeliveryGoodsState.receipt_status IS NULL)");
	}
	if($cond['status3']){
		array_push($status_list,"TDeliveryGoodsState.receipt_status = '2'");
	}
	if($status_list){
		$status_query = implode(' OR ', $status_list);
		array_push($query_list,'('.$status_query.')');
	}
	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';
	//ソートキー
	if(isset($page['sort_key'])){
		$sort_key = $page['sort_key'];
		if($sort_key=='order_req_no'){
			$sort_key = 'TOrder.'.$sort_key.' '.$page['order'].', TOrder.order_req_line_no';
		}
		if($sort_key=='order_req_line_no'||$sort_key=='order_req_ymd'||$sort_key=='order_status'
			||$sort_key=='order_sts_kbn'||$sort_key=='cster_emply_cd'){
			$sort_key = 'TOrder.'.$sort_key;
		}
		if($sort_key=='item_cd'||$sort_key=='color_cd'||$sort_key=='size_cd'){
			$sort_key = 'TOrderState.'.$sort_key;
		}
		if($sort_key=='ship_ymd'||$sort_key=='ship_qty'||$sort_key=='rec_order_no'||$sort_key=='ship_no'){
			$sort_key = 'TDeliveryGoodsState.'.$sort_key;
		}
		if($sort_key=='rntl_sect_name'){
			$sort_key = 'MSection.'.$sort_key;
		}
		if($sort_key=='job_type_cd'){
			$sort_key = 'MJobType.'.$sort_key;
		}
		$order = $page['order'];
	} else {
		$sort_key = 'TOrder.order_req_no asc, TOrder.order_req_line_no';
		$order = 'asc';
	}
	$builder = $app->modelsManager->createBuilder()
		->where($query)
		->from('TDeliveryGoodsState')
		->columns(array('TDeliveryGoodsState.*','TOrderState.*','TOrder.*','MItem.*','MSection.*','MJobType.*'))
		->join('TOrderState','TOrderState.t_order_state_comb_hkey = TDeliveryGoodsState.t_order_state_comb_hkey')
		->join('TOrder','TOrder.t_order_comb_hkey = TOrderState.t_order_comb_hkey')
		->join('MItem','MItem.m_item_comb_hkey = TOrderState.m_item_comb_hkey')
		->join('MSection','MSection.m_section_comb_hkey = TOrderState.m_section_comb_hkey')
		->join('MJobType','MJobType.m_job_type_comb_hkey = TOrderState.m_job_type_comb_hkey')
		->orderBy($sort_key.' '.$order);
	$paginator_model = new PaginatorQueryBuilder(
		array(
			"builder"  => $builder,
			"limit" => $page['records_per_page'],
			"page" => $page['page_number']
		)
	);
	$paginator = $paginator_model->getPaginate();
	$results = $paginator->items;
	$list = array();
	$all_list = array();
	$json_list = array();
	$start = ($page['page_number'] - 1) * $page['records_per_page'];
	$end = $page['page_number'] * $page['records_per_page'];

	foreach($results as $result){
		if(!isset($result)){
			break;
		}
		$list['order_req_no'] = $result->tOrder->order_req_no;
		$list['order_req_line_no'] = $result->tOrder->order_req_line_no;
		$list['cster_emply_cd'] = $result->tOrder->cster_emply_cd;
		$list['rntl_sect_name'] = $result->mSection->rntl_sect_name;
		$list['job_type_name'] = $result->mJobType->job_type_name;
		if($result->tOrder->order_req_ymd){
			$list['order_req_ymd'] =  date('Y/m/d',strtotime($result->tOrder->order_req_ymd));
		}else{
			$list['order_req_ymd'] = '-';
		}
		$list['order_status'] = $result->tOrder->order_status;
		//受領ステータス
		// if(!in_array($result->receipt_status, $receipt_status)){
		// array_push($receipt_status,$result->receipt_status);
		// }
		$list['receipt_status'] = $result->tDeliveryGoodsState->receipt_status;
		$list['order_sts_kbn'] = $result->tOrder->order_sts_kbn;
		//納品状況情報．出荷日
		if($result->tDeliveryGoodsState->ship_ymd){
			$list['ship_ymd'] =  date('Y/m/d',strtotime($result->tDeliveryGoodsState->ship_ymd));
		}else{
			$list['ship_ymd'] = '-';
		}
		$list['rec_order_no'] = $result->tDeliveryGoodsState->rec_order_no;//納品状況情報．受注No.
		$list['ship_no'] = $result->tDeliveryGoodsState->ship_no;//納品状況情報．配送伝票No.
		$list['item_name'] = $result->mItem->item_name;//商品マスタ．商品名（漢字）
		$list['item_cd'] = $result->tOrderState->item_cd;//発注状況情報．商品コード
		$list['color_cd'] = $result->tOrderState->color_cd;//発注状況情報．色コード
		$list['size_cd'] = $result->tOrderState->size_cd;//発注状況情報．サイズコード
		$list['ship_qty'] = $result->tDeliveryGoodsState->ship_qty;//出荷数
		//受領数
		if($result->tDeliveryGoodsState->receipt_status == 2){
			$list['receipt_num'] = $result->tDeliveryGoodsState->ship_qty;
		} else {
			$list['receipt_num'] = 0;
		}
		array_push($all_list,$list);
	}

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $paginator->total_items;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;

	echo json_encode($json_list);

});

/**
 * 納品実績照会検索
 */
$app->post('/delivery/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	$cond = $params['cond'];
	$page = $params['page'];

	$query_list = array();
	//出荷日from
	if(isset($cond['send_day_from'])){
		array_push($query_list,"TO_DATE(TDeliveryGoodsState.ship_ymd,'YYYYMMDD') >= TO_DATE('".$cond['send_day_from']."','YYYY/MM/DD')");
	}
	//出荷日to
	if(isset($cond['send_day_to'])){
		array_push($query_list,"TO_DATE(TDeliveryGoodsState.ship_ymd,'YYYYMMDD') <= TO_DATE('".$cond['send_day_to']."','YYYY/MM/DD')");
	}
	//よろず発注区分
	$order_kbn = array();
	if($cond['order_kbn0']){
		array_push($order_kbn,'1');
	}
	if($cond['order_kbn1']){
		array_push($order_kbn,'3');
	}
	if($cond['order_kbn2']){
		array_push($order_kbn,'4');
	}
	if($cond['order_kbn3']){
		array_push($order_kbn,'5');
	}
	$order_kbn_str = implode("','",$order_kbn);

	//発注情報テーブルを検索(副問い合わせ風)
	$order_status = '';
	$order_sts_kbn = '';
	$order_list = array();
	//よろず発注No
	if(isset($cond['no'])){
		array_push($query_list,"TOrder.order_req_no LIKE '%".$cond['no']."%'");
	}
	//社員番号
	if(isset($cond['member_no'])){
		array_push($query_list,"TOrder.cster_emply_cd LIKE '%".$cond['member_no']."%'");
	}
	//拠点
	if(isset($cond['office'])){
		array_push($query_list,"MSection.rntl_sect_name LIKE '%".$cond['office']."%'");
	}

	//貸与パターン
	if(isset($cond['job_type'])){
		array_push($query_list,"MJobType.job_type_cd = '".$cond['job_type']."'");
	}
	//発注日from
	if(isset($cond['order_day_from'])){
		array_push($query_list,"TO_DATE(TOrder.order_req_ymd,'YYYYMMDD') >= TO_DATE('".$cond['order_day_from']."','YYYY/MM/DD')");
	}
	//発注日to
	if(isset($cond['order_day_to'])){
		array_push($query_list,"TO_DATE(TOrder.order_req_ymd,'YYYYMMDD') <= TO_DATE('".$cond['order_day_to']."','YYYY/MM/DD')");
	}
	if($order_kbn_str){
		$order_sts_kbn = "TOrder.order_sts_kbn in ('".$order_kbn_str."')";
		array_push($query_list,$order_sts_kbn);
	}
	//ステータス
	$status = array();
	$status_list = array();
	if($cond['status0']){
		array_push($status_list,"TOrder.order_status = '1'");
	}
	if($cond['status1']){
		array_push($status_list,"TOrder.order_status = '2'");
	}
	//受領ステータス
	$r_status = '';
	if($cond['status2']){
		//未受領のみ
		array_push($status_list,"(TDeliveryGoodsState.receipt_status = '1' or TDeliveryGoodsState.receipt_status IS NULL)");
	}
	if($cond['status3']){
		array_push($status_list,"TDeliveryGoodsState.receipt_status = '2'");
	}
	if($status_list){
		$status_query = implode(' OR ', $status_list);
		array_push($query_list,'('.$status_query.')');
	}
	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';
	//ソートキー
	if(isset($page['sort_key'])){
		$sort_key = $page['sort_key'];
		if($sort_key=='order_req_no'){
			$sort_key = 'TOrder.'.$sort_key.' '.$page['order'].', TOrder.order_req_line_no';
		}
		if($sort_key=='order_req_line_no'||$sort_key=='order_req_ymd'||$sort_key=='order_status'
		||$sort_key=='order_sts_kbn'||$sort_key=='cster_emply_cd'){
			$sort_key = 'TOrder.'.$sort_key;
		}
		if($sort_key=='item_cd'||$sort_key=='color_cd'||$sort_key=='size_cd'){
			$sort_key = 'TOrderState.'.$sort_key;
		}
		if($sort_key=='ship_ymd'||$sort_key=='ship_qty'||$sort_key=='rec_order_no'||$sort_key=='ship_no'){
			$sort_key = 'TDeliveryGoodsState.'.$sort_key;
		}
		if($sort_key=='rntl_sect_name'){
			$sort_key = 'MSection.'.$sort_key;
		}
		if($sort_key=='job_type_cd'){
			$sort_key = 'MJobType.'.$sort_key;
		}
		$order = $page['order'];
	} else {
		$sort_key = 'TOrder.order_req_no asc, TOrder.order_req_line_no';
		$order = 'asc';
	}
	$builder = $app->modelsManager->createBuilder()
		->where($query)
		->from('TDeliveryGoodsState')
		->columns(array('TDeliveryGoodsState.*','TOrderState.*','TOrder.*','MItem.*','MSection.*','MJobType.*'))
		->join('TOrderState','TOrderState.t_order_state_comb_hkey = TDeliveryGoodsState.t_order_state_comb_hkey')
		->join('TOrder','TOrder.t_order_comb_hkey = TOrderState.t_order_comb_hkey')
		->join('MItem','MItem.m_item_comb_hkey = TOrderState.m_item_comb_hkey')
		->join('MSection','MSection.m_section_comb_hkey = TOrderState.m_section_comb_hkey')
		->join('MJobType','MJobType.m_job_type_comb_hkey = TOrderState.m_job_type_comb_hkey')
		->orderBy($sort_key.' '.$order);
	$paginator_model = new PaginatorQueryBuilder(
		array(
			"builder"  => $builder,
			"limit" => $page['records_per_page'],
			"page" => $page['page_number']
		)
	);
	$paginator = $paginator_model->getPaginate();
	$results = $paginator->items;
	$list = array();
	$all_list = array();
	$json_list = array();
	$start = ($page['page_number'] - 1) * $page['records_per_page'];
	$end = $page['page_number'] * $page['records_per_page'];

	foreach($results as $result){
		if(!isset($result)){
			break;
		}
		$list['order_req_no'] = $result->tOrder->order_req_no;
		$list['order_req_line_no'] = $result->tOrder->order_req_line_no;
		$list['cster_emply_cd'] = $result->tOrder->cster_emply_cd;
		$list['rntl_sect_name'] = $result->mSection->rntl_sect_name;
		$list['job_type_name'] = $result->mJobType->job_type_name;
		if($result->tOrder->order_req_ymd){
			$list['order_req_ymd'] =  date('Y/m/d',strtotime($result->tOrder->order_req_ymd));
		}else{
			$list['order_req_ymd'] = '-';
		}
		$list['order_status'] = $result->tOrder->order_status;
		//受領ステータス
		// if(!in_array($result->receipt_status, $receipt_status)){
			// array_push($receipt_status,$result->receipt_status);
		// }
		$list['receipt_status'] = $result->tDeliveryGoodsState->receipt_status;
		$list['order_sts_kbn'] = $result->tOrder->order_sts_kbn;
		//納品状況情報．出荷日
		if($result->tDeliveryGoodsState->ship_ymd){
			$list['ship_ymd'] =  date('Y/m/d',strtotime($result->tDeliveryGoodsState->ship_ymd));
		}else{
			$list['ship_ymd'] = '-';
		}
		$list['rec_order_no'] = $result->tDeliveryGoodsState->rec_order_no;//納品状況情報．受注No.
		$list['ship_no'] = $result->tDeliveryGoodsState->ship_no;//納品状況情報．配送伝票No.
		$list['item_name'] = $result->mItem->item_name;//商品マスタ．商品名（漢字）
		$list['item_cd'] = $result->tOrderState->item_cd;//発注状況情報．商品コード
		$list['color_cd'] = $result->tOrderState->color_cd;//発注状況情報．色コード
		$list['size_cd'] = $result->tOrderState->size_cd;//発注状況情報．サイズコード
		$list['ship_qty'] = $result->tDeliveryGoodsState->ship_qty;//出荷数
		//受領数
		if($result->tDeliveryGoodsState->receipt_status == 2){
			$list['receipt_num'] = $result->tDeliveryGoodsState->ship_qty;
		} else {
			$list['receipt_num'] = 0;
		}
		array_push($all_list,$list);
	}

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $paginator->total_items;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;

	echo json_encode($json_list);

});

/**
 * 納品実績照会ダウンロード
 */
$app->post('/delivery/download', function ()use($app){
	$params = json_decode($_POST['data'], true);
	$cond = $params['cond'];
	$page = $params['page'];

	$query_list = array();
	//出荷日from
	if(isset($cond['send_day_from'])){
		array_push($query_list,"TO_DATE(TDeliveryGoodsState.ship_ymd,'YYYYMMDD') >= TO_DATE('".$cond['send_day_from']."','YYYY/MM/DD')");
	}
	//出荷日to
	if(isset($cond['send_day_to'])){
		array_push($query_list,"TO_DATE(TDeliveryGoodsState.ship_ymd,'YYYYMMDD') <= TO_DATE('".$cond['send_day_to']."','YYYY/MM/DD')");
	}
	//よろず発注区分
	$order_kbn = array();
	if($cond['order_kbn0']){
		array_push($order_kbn,'1');
	}
	if($cond['order_kbn1']){
		array_push($order_kbn,'3');
	}
	if($cond['order_kbn2']){
		array_push($order_kbn,'4');
	}
	if($cond['order_kbn3']){
		array_push($order_kbn,'5');
	}
	$order_kbn_str = implode("','",$order_kbn);

	//発注情報テーブルを検索(副問い合わせ風)
	$order_status = '';
	$order_sts_kbn = '';
	$order_list = array();
	//よろず発注No
	if(isset($cond['no'])){
		array_push($query_list,"TOrder.order_req_no LIKE '%".$cond['no']."%'");
	}
	//社員番号
	if(isset($cond['member_no'])){
		array_push($query_list,"TOrder.cster_emply_cd LIKE '%".$cond['member_no']."%'");
	}
	//拠点
	if(isset($cond['office'])){
		array_push($query_list,"MSection.rntl_sect_name LIKE '%".$cond['office']."%'");
	}

	//貸与パターン
	if(isset($cond['job_type'])){
		array_push($query_list,"MJobType.job_type_cd = '".$cond['job_type']."'");
	}
	//発注日from
	if(isset($cond['order_day_from'])){
		array_push($query_list,"TO_DATE(TOrder.order_req_ymd,'YYYYMMDD') >= TO_DATE('".$cond['order_day_from']."','YYYY/MM/DD')");
	}
	//発注日to
	if(isset($cond['order_day_to'])){
		array_push($query_list,"TO_DATE(TOrder.order_req_ymd,'YYYYMMDD') <= TO_DATE('".$cond['order_day_to']."','YYYY/MM/DD')");
	}
	if($order_kbn_str){
		$order_sts_kbn = "TOrder.order_sts_kbn in ('".$order_kbn_str."')";
		array_push($query_list,$order_sts_kbn);
	}
	//ステータス
	$status = array();
	$status_list = array();
	if($cond['status0']){
		array_push($status_list,"TOrder.order_status = '1'");
	}
	if($cond['status1']){
		array_push($status_list,"TOrder.order_status = '2'");
	}
	//受領ステータス
	$r_status = '';
	if($cond['status2']){
		//未受領のみ
		array_push($status_list,"(TDeliveryGoodsState.receipt_status = '1' or TDeliveryGoodsState.receipt_status IS NULL)");
	}
	if($cond['status3']){
		array_push($status_list,"TDeliveryGoodsState.receipt_status = '2'");
	}
	if($status_list){
		$status_query = implode(' OR ', $status_list);
		array_push($query_list,'('.$status_query.')');
	}
	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';
	//ソートキー
	if(isset($page['sort_key'])){
		$sort_key = $page['sort_key'];
		if($sort_key=='order_req_no'){
			$sort_key = 'TOrder.'.$sort_key.' '.$page['order'].', TOrder.order_req_line_no';
		}
		if($sort_key=='order_req_line_no'||$sort_key=='order_req_ymd'||$sort_key=='order_status'
		||$sort_key=='order_sts_kbn'||$sort_key=='cster_emply_cd'){
			$sort_key = 'TOrder.'.$sort_key;
		}
		if($sort_key=='item_cd'||$sort_key=='color_cd'||$sort_key=='size_cd'){
			$sort_key = 'TOrderState.'.$sort_key;
		}
		if($sort_key=='ship_ymd'||$sort_key=='ship_qty'||$sort_key=='rec_order_no'||$sort_key=='ship_no'){
			$sort_key = 'TDeliveryGoodsState.'.$sort_key;
		}
		if($sort_key=='rntl_sect_name'){
			$sort_key = 'MSection.'.$sort_key;
		}
		if($sort_key=='job_type_cd'){
			$sort_key = 'MJobType.'.$sort_key;
		}
		$order = $page['order'];
	} else {
		$sort_key = 'TOrder.order_req_no asc, TOrder.order_req_line_no';
		$order = 'asc';
	}
	$results = $app->modelsManager->createBuilder()
		->where($query)
		->from('TDeliveryGoodsState')
		->columns(array('TDeliveryGoodsState.*','TOrderState.*','TOrder.*','MItem.*','MSection.*','MJobType.*'))
		->join('TOrderState','TOrderState.t_order_state_comb_hkey = TDeliveryGoodsState.t_order_state_comb_hkey')
		->join('TOrder','TOrder.t_order_comb_hkey = TOrderState.t_order_comb_hkey')
		->join('MItem','MItem.m_item_comb_hkey = TOrderState.m_item_comb_hkey')
		->join('MSection','MSection.m_section_comb_hkey = TOrderState.m_section_comb_hkey')
		->join('MJobType','MJobType.m_job_type_comb_hkey = TOrderState.m_job_type_comb_hkey')
		->orderBy($sort_key.' '.$order)
		->getQuery()
		->execute();
	$all_list = array();
	$json_list = array();
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=delivery_".$now = date('YmdHis').".csv");
	$fp = fopen('php://output','w');
	$header = array('よろず発注No','よろず発注行No','社員番号','拠点','貸与パターン','発注日','ステータス','よろず発注区分','出荷日','メーカー受注番号','メーカー伝票番号','商品名','商品コード','色コード','サイズ','出荷数','受領数');
	_fputcsv($fp,$header);
	foreach ($results as $result) {
		$list = array();
		if(!isset($result)){
			break;
		}
		$list['order_req_no'] = $result->tOrder->order_req_no;
		$list['order_req_line_no'] = $result->tOrder->order_req_line_no;
		$list['cster_emply_cd'] = $result->tOrder->cster_emply_cd;
		$list['rntl_sect_name'] = $result->mSection->rntl_sect_name;
		$list['job_type_name'] = $result->mJobType->job_type_name;
		// $list['order_req_ymd'] = $result->TOrderState->TOrder->order_req_ymd;

		if($result->tOrder->order_req_ymd){
			$list['order_req_ymd'] =  date('Y/m/d',strtotime($result->tOrder->order_req_ymd));
		}else{
			$list['order_req_ymd'] = '-';
		}

		$list['statusText'] = statusText($result->tOrder->order_status,$result->tDeliveryGoodsState->receipt_status);
		$list['kubunText'] = kubunText($result->tOrder->order_sts_kbn);
		//受領ステータス
		// if(!in_array($result->receipt_status, $receipt_status)){
			// array_push($receipt_status,$result->receipt_status);
		// }
		// $list['receipt_status'] = $result->receipt_status;
		// $list['order_sts_kbn'] = $result->TOrderState->TOrder->order_sts_kbn;
		//納品状況情報．出荷日
		if($result->tDeliveryGoodsState->ship_ymd){
			$list['ship_ymd'] =  date('Y/m/d',strtotime($result->tDeliveryGoodsState->ship_ymd));
		}else{
			$list['ship_ymd'] = '-';
		}

		$list['rec_order_no'] = $result->tDeliveryGoodsState->rec_order_no;//納品状況情報．受注No.
		$list['ship_no'] = $result->tDeliveryGoodsState->ship_no;//納品状況情報．配送伝票No.
		$list['item_name'] = $result->mItem->item_name;//商品マスタ．商品名（漢字）
		$list['item_cd'] = $result->tOrderState->item_cd;//発注状況情報．商品コード
		$list['color_cd'] = $result->tOrderState->color_cd;//発注状況情報．色コード
		$list['size_cd'] = $result->tOrderState->size_cd;//発注状況情報．サイズコード
		$list['ship_qty'] = $result->tDeliveryGoodsState->ship_qty;//出荷数
		//受領数
		if($result->tDeliveryGoodsState->receipt_status == 2){
			$list['receipt_num'] = $result->tDeliveryGoodsState->ship_qty;
		} else {
			$list['receipt_num'] = '0';
		}
		_fputcsv($fp,$list);
	}
	fclose($fp);
});
/**
 * 返却実績照会検索
 */
$app->post('/unreturned/search',function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();

	//よろず発注No
	if(isset($cond['no'])){
		array_push($query_list,"TReturnedResults.order_req_no LIKE '%".$cond['no']."%'");
	}
	//社員番号
	if(isset($cond['member_no'])){
		array_push($query_list,"TReturnedResults.cster_emply_cd LIKE '%".$cond['member_no']."%'");
	}
	//拠点
	if(isset($cond['office'])){
		array_push($query_list,"MSection.rntl_sect_name LIKE '%".$cond['office']."%'");
	}

	//貸与パターン
	if(isset($cond['job_type'])){
		array_push($query_list,"MJobType.job_type_cd = '".$cond['job_type']."'");
	}
	//発注日from
	if(isset($cond['order_day_from'])){
		array_push($query_list,"TReturnedResults.order_date >= '".$cond['order_day_from']."'");
	}
	//発注日to
	if(isset($cond['order_day_to'])){
		array_push($query_list,"TReturnedResults.order_date <= '".$cond['order_day_to']."'");
	}
	//返却日from
	if(isset($cond['return_day_from'])){
		array_push($query_list,"TReturnedResults.return_date >= '".$cond['return_day_from']."'");
	}
	//返却日to
	if(isset($cond['return_day_to'])){
		array_push($query_list,"TReturnedResults.return_date <= '".$cond['return_day_to']."'");
	}
	//ステータス
	$status = array();
	if($cond['status0']){
		array_push($status,'1');
	}
	if($cond['status1']){
		array_push($status,'2');
	}
	if($status){
		$status_str = implode("','",$status);
		array_push($query_list,"TReturnedPlanInfo.return_status IN ('".$status_str."')");
	}
	//よろず発注区分
	$order_kbn = array();
	if($cond['order_kbn0']){
		array_push($order_kbn,'2');
	}
	if($cond['order_kbn1']){
		array_push($order_kbn,'3');
	}
	if($cond['order_kbn2']){
		array_push($order_kbn,'4');
	}
	if($cond['order_kbn3']){
		array_push($order_kbn,'5');
	}
	if($order_kbn){
		$order_kbn_str = implode("','",$order_kbn);
		array_push($query_list,"TReturnedPlanInfo.order_sts_kbn IN ('".$order_kbn_str."')");
	}
	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';
	//ソートキー
	if(isset($page['sort_key'])){
		$sort_key = $page['sort_key'];
		if($sort_key=='order_date'||$sort_key=='return_status'||$sort_key=='order_sts_kbn'){
			$sort_key = 'TReturnedPlanInfo.'.$sort_key;
		}
		if($sort_key=='order_req_no'||$sort_key=='return_date'||$sort_key=='cster_emply_cd'||$sort_key=='rent_pattern_code'){
			$sort_key = 'TReturnedResults.'.$sort_key;
		}
		if($sort_key=='item_cd'||$sort_key=='color_cd'||$sort_key=='size_cd'){
			$sort_key = 'TReturnedResults.'.$sort_key;
		}
		if($sort_key=='rntl_sect_name'){
			$sort_key = 'MSection.'.$sort_key;
		}
		$order = $page['order'];
	} else {
		//なければよろず発注No
		$sort_key = "TReturnedPlanInfo.order_req_no";
		$order = 'asc';
	}
	$builder = $app->modelsManager->createBuilder()
		->where($query)
		->from('TReturnedResults')
		->columns(array('TReturnedPlanInfo.*','TReturnedResults.*','MSection.*','MJobType.*','MItem.*'))
		->leftJoin('TReturnedPlanInfo','TReturnedPlanInfo.t_returned_plan_info_comb_hkey = TReturnedResults.t_returned_plan_info_comb_hkey')
		->join('MJobType','MJobType.rntl_cont_no = TReturnedResults.rntl_cont_no AND MJobType.job_type_cd = TReturnedResults.rent_pattern_code')
		->join('MSection','MSection.m_section_comb_hkey = TReturnedResults.m_section_comb_hkey')
		->join('MItem','MItem.m_item_comb_hkey = TReturnedResults.m_item_comb_hkey')
		->orderBy($sort_key.' '.$order);
	$paginator_model = new PaginatorQueryBuilder(
		array(
			"builder"  => $builder,
			"limit" => $page['records_per_page'],
			"page" => $page['page_number']
		)
	);
	$paginator = $paginator_model->getPaginate();
	$results = $paginator->items;
	$list = array();
	$all_list = array();
	$json_list = array();
	foreach($results as $result){
		if(!isset($result)){
			break;
		}
		$list['order_req_no'] = $result->tReturnedResults->order_req_no;
		$list['cster_emply_cd'] = $result->tReturnedResults->cster_emply_cd;
		$list['rntl_sect_name'] = $result->mSection->rntl_sect_name;
		$list['job_type_name'] = $result->mJobType->job_type_name;
		if($result->tReturnedResults->order_date){
			$list['order_date'] =  date('Y/m/d',strtotime($result->tReturnedResults->order_date));
		}else{
			$list['order_date'] = '-';
		}
		if($result->tReturnedResults->return_date){
			$list['return_date'] = date('Y/m/d',strtotime($result->tReturnedResults->return_date));
		}else{
			$list['return_date'] = '-';
		}
		$list['kubun'] = $result->tReturnedPlanInfo->order_sts_kbn;
		$list['item_name'] = $result->mItem->item_name;
		$list['item_cd'] = $result->mItem->item_cd;
		$list['color_cd'] = $result->mItem->color_cd;
		$list['size_cd'] = $result->mItem->size_cd;
		$list['return_qty'] = $result->tReturnedResults->return_qty;
		array_push($all_list,$list);
	}
	if(isset($cond['mode'])&&$cond['mode'] == 'download'){
		$json_list['mode'] = $cond['mode'];
		$json_list['csv_list'] = $all_list;
	}
	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $paginator->total_items;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;
	echo json_encode($json_list);
});

/**
 * 返却実績照会ダウンロード
 */
$app->post('/unreturned/download',function ()use($app){
	$params = json_decode($_POST['data'], true);

	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();

	//よろず発注No
	if(isset($cond['no'])){
		array_push($query_list,"TReturnedResults.order_req_no LIKE '%".$cond['no']."%'");
	}
	//社員番号
	if(isset($cond['member_no'])){
		array_push($query_list,"TReturnedResults.cster_emply_cd LIKE '%".$cond['member_no']."%'");
	}
	//拠点
	if(isset($cond['office'])){
		array_push($query_list,"MSection.rntl_sect_name LIKE '%".$cond['office']."%'");
	}

	//貸与パターン
	if(isset($cond['job_type'])){
		array_push($query_list,"MJobType.job_type_cd = '".$cond['job_type']."'");
	}
	//発注日from
	if(isset($cond['order_day_from'])){
		array_push($query_list,"TReturnedResults.order_date >= '".$cond['order_day_from']."'");
	}
	//発注日to
	if(isset($cond['order_day_to'])){
		array_push($query_list,"TReturnedResults.order_date <= '".$cond['order_day_to']."'");
	}
	//返却日from
	if(isset($cond['return_day_from'])){
		array_push($query_list,"TReturnedResults.return_date >= '".$cond['return_day_from']."'");
	}
	//返却日to
	if(isset($cond['return_day_to'])){
		array_push($query_list,"TReturnedResults.return_date <= '".$cond['return_day_to']."'");
	}
	//ステータス
	$status = array();
	if($cond['status0']){
		array_push($status,'1');
	}
	if($cond['status1']){
		array_push($status,'2');
	}
	if($status){
		$status_str = implode("','",$status);
		array_push($query_list,"TReturnedPlanInfo.return_status IN ('".$status_str."')");
	}
	//よろず発注区分
	$order_kbn = array();
	if($cond['order_kbn0']){
		array_push($order_kbn,'2');
	}
	if($cond['order_kbn1']){
		array_push($order_kbn,'3');
	}
	if($cond['order_kbn2']){
		array_push($order_kbn,'4');
	}
	if($cond['order_kbn3']){
		array_push($order_kbn,'5');
	}
	if($order_kbn){
		$order_kbn_str = implode("','",$order_kbn);
		array_push($query_list,"TReturnedPlanInfo.order_sts_kbn IN ('".$order_kbn_str."')");
	}
	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';
	//ソートキー
	if(isset($page['sort_key'])){
		$sort_key = $page['sort_key'];
		if($sort_key=='order_date'||$sort_key=='return_status'||$sort_key=='order_sts_kbn'){
			$sort_key = 'TReturnedPlanInfo.'.$sort_key;
		}
		if($sort_key=='order_req_no'||$sort_key=='return_date'||$sort_key=='cster_emply_cd'||$sort_key=='rent_pattern_code'){
			$sort_key = 'TReturnedResults.'.$sort_key;
		}
		if($sort_key=='item_cd'||$sort_key=='color_cd'||$sort_key=='size_cd'){
			$sort_key = 'TReturnedResults.'.$sort_key;
		}
		if($sort_key=='rntl_sect_name'){
			$sort_key = 'MSection.'.$sort_key;
		}
		$order = $page['order'];
	} else {
		//なければよろず発注No
		$sort_key = "TReturnedPlanInfo.order_req_no";
		$order = 'asc';
	}
	$results = $app->modelsManager->createBuilder()
		->where($query)
		->from('TReturnedResults')
		->columns(array('TReturnedPlanInfo.*','TReturnedResults.*','MSection.*','MJobType.*','MItem.*'))
		->leftJoin('TReturnedPlanInfo','TReturnedPlanInfo.t_returned_plan_info_comb_hkey = TReturnedResults.t_returned_plan_info_comb_hkey')
		->join('MJobType','MJobType.rntl_cont_no = TReturnedResults.rntl_cont_no AND MJobType.job_type_cd = TReturnedResults.rent_pattern_code')
		->join('MSection','MSection.m_section_comb_hkey = TReturnedResults.m_section_comb_hkey')
		->join('MItem','MItem.m_item_comb_hkey = TReturnedResults.m_item_comb_hkey')
		->orderBy($sort_key.' '.$order)
		->getQuery()
		->execute();
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=unreturned_".$now = date('YmdHis').".csv");
	$fp = fopen('php://output','w');
	$header = array('よろず発注No','社員番号','拠点','貸与パターン','発注日','返却日','よろず発注区分','商品名','商品コード','色コード','サイズ','出荷数');
	_fputcsv($fp,$header);
	$list = array();
	$all_list = array();
	$json_list = array();
	foreach($results as $result){
		if(!isset($result)){
			break;
		}

		$list['order_req_no'] = $result->tReturnedResults->order_req_no;
		$list['cster_emply_cd'] = $result->tReturnedResults->cster_emply_cd;
		$list['rntl_sect_name'] = $result->mSection->rntl_sect_name;
		$list['job_type_name'] = $result->mJobType->job_type_name;
		if($result->tReturnedResults->order_date){
			$list['order_date'] =  date('Y/m/d',strtotime($result->tReturnedResults->order_date));
		}else{
			$list['order_date'] = '-';
		}
		if($result->tReturnedResults->return_date){
			$list['return_date'] = date('Y/m/d',strtotime($result->tReturnedResults->return_date));
		}else{
			$list['return_date'] = '-';
		}
		$list['kubun'] = kubunRdText($result->tReturnedPlanInfo->order_sts_kbn);
		$list['item_name'] = $result->mItem->item_name;
		$list['item_cd'] = $result->mItem->item_cd;
		$list['color_cd'] = $result->mItem->color_cd;
		$list['size_cd'] = $result->mItem->size_cd;
		$list['return_qty'] = $result->tReturnedResults->return_qty;
		_fputcsv($fp,$list);
	}
	fclose($fp);
});

/**
 * 貸与リストダウンロード
 */
$app->post('/lend/download', function ()use($app) {
	$params = json_decode($_POST['data'], true);
	$cond = $params['cond'];
	$page = $params['page'];

	$query_list = array();
	//社員番号
	if(isset($cond['member_no'])){
		array_push($query_list,"MWearerStd.cster_emply_cd LIKE '%".$cond['member_no']."%'");
	}
	//拠点
	if(isset($cond['office'])){
		array_push($query_list,"MSection.rntl_sect_name LIKE '%".$cond['office']."%'");
	}
	//貸与パターン
	if(isset($cond['job_type'])){
		array_push($query_list,"MJobType.job_type_cd = '".$cond['job_type']."'");
	}

	//商品コード
	if(isset($cond['item_cd'])){
		array_push($query_list,"MWearerItem.item_cd LIKE '%".$cond['item_cd']."%'");
	}
	//色
	if(isset($cond['color_cd'])){
		array_push($query_list,"MWearerItem.color_cd LIKE '%".$cond['color_cd']."%'");
	}
	//サイズ
	if(isset($cond['size'])){
		array_push($query_list,"MWearerItem.size_cd LIKE '%".$cond['size']."%'");
	}
	array_push($query_list,"MWearerItem.input_qty > 0");
	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);

	//ソートキー
	if(isset($page['sort_key'])){
		$sort_key = $page['sort_key'];
		if($sort_key=='rec_order_no'||$sort_key=='ship_no'||$sort_key=='ship_ymd'){
			$sort_key = 'TDeliveryGoodsState.'.$sort_key;
		}
		if($sort_key=='item_cd'||$sort_key=='color_cd'||$sort_key=='size_cd'){
			$sort_key = 'MWearerItem.'.$sort_key;
		}
		if($sort_key=='cster_emply_cd'){
			$sort_key = 'MWearerStd.'.$sort_key;
		}
		if($sort_key=='order_req_no'){
			$sort_key = 'TOrderState.'.$sort_key;
		}
		if($sort_key=='rntl_sect_name'){
			$sort_key = 'MSection.'.$sort_key;
		}
		if($sort_key=='job_type_cd'){
			$sort_key = 'MJobType.'.$sort_key;
		}
		if($sort_key=='item_name'){
			$sort_key = 'MItem.'.$sort_key;
		}
		$order = $page['order'];
	} else {
		//なければ社員番号
		$sort_key = "MWearerStd.cster_emply_cd";
		$order = 'asc';
	}
	$results = $app->modelsManager->createBuilder()
		->where($query)
		->from('MWearerItem')
		// ->from('MWearerStd')
		->columns(array('MWearerItem.*','MWearerStd.*','TOrderState.*','TDeliveryGoodsState.*','MItem.*','MSection.*','MJobType.*'))
		->leftJoin('MWearerStd','MWearerItem.m_wearer_std_comb_hkey = MWearerStd.m_wearer_std_comb_hkey')
		->join('TOrderState','TOrderState.m_wearer_item_comb_hkey = MWearerItem.m_wearer_item_comb_hkey')
		->leftJoin('TDeliveryGoodsState','TDeliveryGoodsState.t_order_state_comb_hkey = TOrderState.t_order_state_comb_hkey')
		->join('MItem','MItem.m_item_comb_hkey = MWearerItem.m_item_comb_hkey')
		->join('MSection','MSection.m_section_comb_hkey = MWearerItem.m_section_comb_hkey')
		->join('MJobType','MJobType.m_job_type_comb_hkey = MWearerItem.m_job_type_comb_hkey')
		->orderBy($sort_key.' '.$order)
		->getQuery()
		->execute();

	$all_list = array();
	$json_list = array();
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=lend_".$now = date('YmdHis').".csv");
	$fp = fopen('php://output','w');
	$header = array('社員番号','拠点','貸与パターン','商品名','商品コード','色コード','サイズ','出荷数','メーカー受注番号','メーカー伝票番号','出荷日','よろず発注No');
	_fputcsv($fp,$header);
	foreach ($results as $result) {
		$list = array();
		$list['member_no'] = $result->mWearerStd->cster_emply_cd;
		$list['rntl_sect_name'] = $result->mSection->rntl_sect_name;
		$list['job_type_name'] = $result->mJobType->job_type_name;
		$list['item_name'] = $result->mItem->item_name;
		$list['item_cd'] = $result->mWearerItem->item_cd;
		$list['color_cd'] = $result->mWearerItem->color_cd;
		$list['size'] = $result->mWearerItem->size_cd;
		if( $result->tDeliveryGoodsState->ship_qty){
			$list['send_num'] = $result->tDeliveryGoodsState->ship_qty;
		}else{
			$list['send_num'] = '-';
		}
		if($result->tDeliveryGoodsState->rec_order_no){
			$list['rec_order_no'] = $result->tDeliveryGoodsState->rec_order_no;
		}else{
			$list['rec_order_no'] = '-';
		}
		if($result->tDeliveryGoodsState->ship_no){
			$list['ship_no'] = $result->tDeliveryGoodsState->ship_no;
		}else{
			$list['ship_no'] = '-';
		}
		if($result->tDeliveryGoodsState->ship_ymd){
			$list['send_day'] =  date('Y/m/d',strtotime($result->tDeliveryGoodsState->ship_ymd));
		}else{
			$list['send_day'] = '-';
		}
		$list['order_req_no'] = $result->tOrderState->order_req_no;
		_fputcsv($fp,$list);
	}
	fclose($fp);
});

/**
 * お知らせモーダル機能
 */
$app->post('/info/modal', function ()use($app) {

	$params = json_decode(file_get_contents("php://input"), true);

	$cond = $params['cond'];
	$json_list = array();
	$error_list = array();
	$error = false;
	//公開開始日時が公開終了日時より未来ならエラー
	if(strtotime($cond['open_date'])>=strtotime($cond['close_date'])){
		$error_list['delete'] = '公開開始日時は公開終了日時より過去に設定してください。';
		$json_list['errors'] = $error_list;
		echo json_encode($json_list);
		return true;
	}
	$transaction = $app->transactionManager->get();
	$t_info = new TInfo();
	 $auth = $app->session->get("auth");
	if($params['type'] == '1'){
		//編集の場合
		$in = TInfo::find(array(

		'conditions' => "index = ".$cond['index']
		));
		$t_info = $in[0];
	}elseif($params['type'] == '2'){
		$in = TInfo::find(array(
		'conditions' => "index = ".$cond['index']
		));
		//削除の場合
		if ($in[0]->delete() == false) {
			$error_list['delete'] = 'お知らせの削除に失敗しました。';
			$json_list['errors'] = $error_list;
			echo json_encode($json_list);
			return true;
		} else {
			$transaction->commit();
			echo json_encode($json_list);
			return true;
		}
	} else {
		//追加の場合
		if(!$error_list){
			$t_info->rgst_user_id = $auth['user_id']; //登録ユーザー
			$t_info->rgst_date = date( "Y/m/d H:i:s.sss", time() ); //登録日時
		}
	}
	if($error_list){
		$json_list['errors'] = $error_list;
		echo json_encode($json_list);
		return true;
	}
	$t_info->display_order = $cond['display_order']; //表示順
	$t_info->open_date = $cond['open_date']; //公開開始日時
	$t_info->close_date = $cond['close_date']; //公開終了日時
	$t_info->message = $cond['message']; //表示メッセージ
	$t_info->upd_user_id = $auth['user_id']; //更新ユーザー
	$t_info->upd_date = date( "Y/m/d H:i:s.sss", time() ); //更新日時
	if ($t_info->save() == false) {
			$error_list['update'] = 'お知らせ情報の更新に失敗しました。';
			$json_list['errors'] = $error_list;
			echo json_encode($json_list);
			return true;
	} else {
		$transaction->commit();
	}
	echo json_encode($json_list);

});

/**
 * 在庫専用貸与パターン取得
 */
$app->post('/job_type_zaiko', function () {
	$params = json_decode(file_get_contents("php://input"), true);
	$results = MRentPatternForSdmzk::find(array(
		'order'	  => "rent_pattern_data asc"
	));
	$list = array();
	$all_list = array();
	$json_list = array();
	//初っ端は空データ
	$list['job_type_cd'] = null;
	$list['job_type_name'] = null;
	array_push($all_list,$list);
	foreach ($results as $result) {
		$list['job_type_cd'] = $result->rent_pattern_data;
		$list['job_type_name'] = $result->rent_pattern_name;
		// $list['sort'] = $result->sort;
		array_push($all_list,$list);
	}
	$json_list['job_type_list'] = $all_list;
	echo json_encode($json_list);
});
/**
 * 拠点候補取得
 */
$app->post('/suggest', function () {
	$params = json_decode(file_get_contents("php://input"), true);

	//拠点
	if(isset($params['text'])){
		$query = "rntl_sect_name LIKE '%".$params['text']."%'";
		$results = MSection::find(array(
		'conditions' => $query
		));
		$json_list = array();
		$i = 0;
		foreach ($results as $result) {
		$json_list[$i]['office_cd'] = $result->rntl_sect_cd;
		$json_list[$i]['office_name'] = $result->rntl_sect_name;
		$i++;
		}
		echo json_encode($json_list);
	} else {
		return true;
	}
});
/**
 * 詳細画面
 */
$app->post('/detail', function ()use($app) {

	$params = json_decode(file_get_contents("php://input"), true);

	$order_list = array();
	$sort_key ='';
	$order ='';
	//ソートキー
	$sort_key = 'TOrder.order_req_no asc, TOrder.order_req_line_no';
	$order = 'asc';

	$results = TOrder::query()
		->where("TOrder.order_req_no = '".$params['no']."'")
		->columns(array('TDeliveryGoodsState.*','TOrderState.*','TOrder.*','MItem.*','MSection.*','MJobType.*'))
		->join('TOrderState','TOrderState.t_order_comb_hkey = TOrder.t_order_comb_hkey')
		->leftjoin('TDeliveryGoodsState','TOrderState.t_order_state_comb_hkey = TDeliveryGoodsState.t_order_state_comb_hkey')
		->join('MItem','MItem.m_item_comb_hkey = TOrderState.m_item_comb_hkey')
		->join('MSection','MSection.m_section_comb_hkey = TOrderState.m_section_comb_hkey')
		->join('MJobType','MJobType.m_job_type_comb_hkey = TOrderState.m_job_type_comb_hkey')
		->orderBy($sort_key.' '.$order)
		->execute();
	// $results = TDeliveryGoodsState::query()
		// ->where("TOrder.order_req_no = '".$params['no']."'")
		// ->columns(array('TDeliveryGoodsState.*','TOrderState.*','TOrder.*','MItem.*','MSection.*','MJobType.*'))
		// ->join('TOrderState','TOrderState.t_order_state_comb_hkey = TDeliveryGoodsState.t_order_state_comb_hkey')
		// ->join('TOrder','TOrder.t_order_comb_hkey = TOrderState.t_order_comb_hkey')
		// ->join('MItem','MItem.m_item_comb_hkey = TOrderState.m_item_comb_hkey')
		// ->join('MSection','MSection.m_section_comb_hkey = TOrderState.m_section_comb_hkey')
		// ->join('MJobType','MJobType.m_job_type_comb_hkey = TOrderState.m_job_type_comb_hkey')
		// ->orderBy($sort_key.' '.$order)
		// ->execute();
	$list = array();
	$all_list = array();
	$json_list = array();
	$receipt_status = array();

	foreach($results as $result){
		$receipt_status = array();
		if(!isset($result)){
			break;
		}
		$list['order_req_no'] = $result->tOrder->order_req_no;
		$list['order_req_line_no'] = $result->tOrder->order_req_line_no;
		$list['cster_emply_cd'] = $result->tOrder->cster_emply_cd;
		$list['rntl_sect_name'] = $result->mSection->rntl_sect_name;
		$list['job_type_name'] = $result->mJobType->job_type_name;
		if($result->tOrder->order_req_ymd){
			$list['order_req_ymd'] =  date('Y/m/d',strtotime($result->tOrder->order_req_ymd));
		}else{
			$list['order_req_ymd'] = '-';
		}
		$list['order_status'] = $result->tOrder->order_status;
		//受領ステータス
		// if(!in_array($result->receipt_status, $receipt_status)){
			// array_push($receipt_status,$result->receipt_status);
		// }
		$list['statusText'] = statusText($result->tOrder->order_status,$receipt_status);
		$list['kubunText'] = kubunText($result->tOrder->order_sts_kbn);
		//納品状況情報．出荷日
		if($result->tDeliveryGoodsState->ship_ymd){
			$list['ship_ymd'] =  date('Y/m/d',strtotime($result->tDeliveryGoodsState->ship_ymd));
		}else{
			$list['ship_ymd'] = '-';
		}
		$list['rec_order_no'] = '-';
		if($result->tOrderState->rec_order_no){
			$list['rec_order_no'] = $result->tOrderState->rec_order_no;//納品状況情報．受注No.
		}
		$list['ship_no'] = '-';
		if($result->tDeliveryGoodsState->ship_no){
			$list['ship_no'] = $result->tDeliveryGoodsState->ship_no;//納品状況情報．配送伝票No.
		}
		$list['item_name'] = $result->tOrderState->TOrder->MItem->item_name;//商品マスタ．商品名（漢字）
		$list['item_cd'] = $result->tOrderState->item_cd;//発注状況情報．商品コード
		$list['color_cd'] = $result->tOrderState->color_cd;//発注状況情報．色コード
		$list['size_cd'] = $result->tOrderState->size_cd;//発注状況情報．サイズコード
		$list['ship_qty'] = 0;
		if($result->tDeliveryGoodsState->ship_qty){
			$list['ship_qty'] = $result->tDeliveryGoodsState->ship_qty;//出荷数
		}
		//受領数
		if($result->tDeliveryGoodsState->receipt_status == 2){
			$list['receipt_num'] = $result->tDeliveryGoodsState->ship_qty;
		} else {
			$list['receipt_num'] = 0;
		}
		array_push($all_list,$list);
	}
	$json_list['list'] = $all_list;

	echo json_encode($json_list);
});
/**
 * パスワード
 */
$app->post('/password', function ()use($app) {
	try{
		$params = json_decode(file_get_contents("php://input"), true);
		$json_list = array();
		$json_list['status'] = 0;
		if($params['password'] != $params['password_c']){
			//新規パスワード入力欄、パスワード確認入力欄が同じ値か
			// エラーメッセージを表示して処理を終了する。
			$json_list['status'] = 1;
			echo json_encode($json_list);
			return true;
		}
		if(strlen($params['password'])<8){
			//パスワード桁数は8文字以上であるか
			// エラーメッセージを表示して処理を終了する。
			$json_list['status'] = 2;
			echo json_encode($json_list);
			return true;
		}
		if(!preg_match("/(?=.{8,})(?=.*\d+.*)(?=.*[a-zA-Z]+.*).*[!#$%&*+@?]+.*/",$params['password'])){
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
		"bind"	=> array(1 => $user_id)
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
		foreach($old_pass_list as $old_pass){
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
		if($old_pass_list){
			//パスワードが変更されたら
			//履歴は10まで
			if(count($old_pass_list) >= 10){
				unset($old_pass_list[0]);
			}
			array_push($old_pass_list,$hash_pass);
		}else{
			$old_pass_list = array();
			//パスワード履歴がない場合はパスワード登録
			array_push($old_pass_list,$hash_pass);
		}

		$account[0]->old_pass_word = json_encode($old_pass_list);
		$account[0]->last_pass_word_upd_date = date( "Y/m/d H:i:s.sss", time() ); //パスワード変更日時
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

	} catch(Exception $e){
		$error_list['update'] = 'アカウント情報の更新に失敗しました。';
		$json_list['errors'] = $error_list;
		echo json_encode($json_list);
		return true;
	}

	});
/*}
 * 操作ログ
 */
$app->post('/log', function () {
	$params = json_decode(file_get_contents("php://input"), true);
	echo true;
});
/*}
 * csv取込
 */
$app->post('/csv', function ()use($app) {
	$params = json_decode(file_get_contents("php://input"), true);
	$start_time = '18:00:00';
	$end_time = '6:00:00';
	$now = date('H:i:s');
	$auth = $app->session->get("auth");
	if($auth['user_type']!=3){//スーパーユーザーは操作可能
		if(strtotime($now) > strtotime($start_time) || strtotime($now) < strtotime($end_time)){
				$json_list['errors'] = array('18時〜6時まではCSV取込不可となります。');
				echo json_encode($json_list);
				return true;
		}
	}
	echo true;
});
$app->post('/api/CM9010', function () {
	//ダミー
	echo true;
});

/**
 * ・ステータス名変換
 *
 * 引数で受け取ったステータスを、ステータス名に変換する
 *
 * @param string $order_status 発注ステータス
 * @param string $receipt_status 受領ステータス
 * @return string ステータス名
 */
function statusText($order_status,$receipt_status){
	$data = $order_status;
	$retunr_str = '';
	if ($data == 1) {
		$retunr_str = $retunr_str . "未出荷";
	} else if ($data == 2) {
		$retunr_str = $retunr_str . "出荷済";
	} else if ($data == 9) {
		$retunr_str = $retunr_str . "キャンセル";
	}
	$data2 = $receipt_status;
	if($data2){
		if ($data2 == 1) {
			$retunr_str = $retunr_str . " 未受領";
		} else if ($data2 == 2) {
			$retunr_str = $retunr_str . " 受領済";
		}
	}
	return $retunr_str;
}
/**
 * ・よろず発注区分名変換
 *
 * 引数で受け取ったよろず発注区分を、よろず発注区分名に変換する
 *
 * @param string $order_sts_kbn よろず発注区分
 * @return string よろず発注区分名
 */
function kubunText($order_sts_kbn){
	$data = $order_sts_kbn;
	if ($data == 1) {
		return "貸与";
	} else if ($data == 3) {
		return "サイズ交換";
	} else if ($data == 4) {
		return "消耗交換";
	} else if ($data == 5) {
		return "異動";
	}
}
/**
 * ・よろず発注区分名変換(返却実績用)
 *
 * 引数で受け取ったよろず発注区分を、よろず発注区分名に変換する
 *
 * @param string $order_sts_kbn よろず発注区分
 * @return string よろず発注区分名
 */
function kubunRdText($order_sts_kbn){
	$data = $order_sts_kbn;
	if ($data == 2) {
		return "返却";
	} else if ($data == 3) {
		return "サイズ交換";
	} else if ($data == 4) {
		return "消耗交換";
	} else if ($data == 5) {
		return "異動";
	}
}
/**
 * ・在庫状態名変換
 *
 * 引数で受け取った在庫状態を、在庫状態名に変換する
 *
 * @param string $zk_status_cd 在庫状態
 * @return string 在庫状態名
 */
function zaikoText($zk_status_cd){
	$data = $zk_status_cd;
	if ($data == 1) {
		return "新品";
	} else if ($data == 2) {
		return "中古A";
	} else if ($data == 3) {
		return "中古B";
	}
}
/**
 *
 * ・fputcsv風の自前関数
 *
 * 1と2番目の引数はオリジナルのfputcsvと同じ。最初がファイルポインタ、次が値の配列
 * 項目は全てダブルクォーテーションで括られます
 * 項目内のダブルクォーテーションはダブルクォーテーションでエスケープされます
 *
 * @param file $fp ファイルポインタ
 * @param array $data データ配列
 * @return string 在庫状態名
 */

function _fputcsv($fp, $data) {
	require_once 'mb_str_replace.php';

	mb_convert_variables("SJIS-win", "UTF-8", $data);
	$csv = '';
	foreach ($data as $col) {
		$col = mb_str_replace('"', '""', $col);
		$csv .= "\"$col\",";
	}
	$csv = preg_replace("/,$/", "", $csv);

	fwrite($fp, "$csv\r\n");
}
?>
