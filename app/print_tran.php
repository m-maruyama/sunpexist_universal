<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
require_once ('library/tcpdf/tcpdf.php');
require_once ('library/fpdi/fpdi.php');

/**
 * 返却伝票PDF作成 (t_returned_plan_info_tran) 各画面からの
 */
$app->post('/print/pdf_tran', function ()use($app){

    $params = json_decode($_POST['data'], true);
    $json_list = array();
    // アカウントセッション取得
    $auth = $app->session->get("auth");
    $cond = $params["cond"];

    //個別管理番号あるなし　1:あり 0:なし
    $individual_check = individual_flg($auth['corporate_id'], $cond['rntl_cont_no']);

    $query_list = array();
    //---検索条件---//
    //企業ID
    array_push($query_list,"t_returned_plan_info_tran.corporate_id = '".$auth['corporate_id']."'");

    //発注No
    if(!empty($cond['order_req_no'])){
        array_push($query_list,"t_returned_plan_info_tran.order_req_no = '".$cond['order_req_no']."'");
    }
    //契約No
    if(!empty($cond['rntl_cont_no'])){
        array_push($query_list,"t_returned_plan_info_tran.rntl_cont_no = '".$cond['rntl_cont_no']."'");
    }
    //sql文字列を' AND 'で結合
    $query = implode(' AND ', $query_list);
    $q_sort_key = 'as_item_cd, as_color_cd, as_size_cd, as_individual_ctrl_no';
    $order = 'asc';
    //---SQLクエリー実行---//
    $arg_str = "SELECT ";
    $arg_str .= " * ";
    $arg_str .= " FROM ";
//	$arg_str .= "(SELECT ";
    $arg_str .= "(SELECT distinct on (t_returned_plan_info_tran.order_req_no, t_returned_plan_info_tran.order_req_line_no) ";
    $arg_str .= "t_order_tran.order_req_ymd as as_order_req_ymd,";
    $arg_str .= "t_order_tran.order_reason_kbn as as_order_reason_kbn,";
    $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
    $arg_str .= "m_job_type.job_type_name as as_job_type_name,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
    $arg_str .= "t_order_tran.order_qty as as_order_qty,";
    $arg_str .= "t_order_tran.size_two_cd as as_size_two_cd,";
    $arg_str .= "m_corporate.corporate_name as as_corporate_name,";
    $arg_str .= "t_returned_plan_info_tran.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "t_returned_plan_info_tran.corporate_id as as_corporate_id,";
    $arg_str .= "t_returned_plan_info_tran.rntl_cont_no as as_rntl_cont_no,";
    $arg_str .= "t_returned_plan_info_tran.order_req_no as as_order_req_no,";
    $arg_str .= "t_returned_plan_info_tran.item_cd as as_item_cd,";
    $arg_str .= "t_returned_plan_info_tran.color_cd as as_color_cd,";
    $arg_str .= "t_returned_plan_info_tran.size_cd as as_size_cd,";
    $arg_str .= "t_returned_plan_info_tran.order_sts_kbn as as_order_sts_kbn,";
    $arg_str .= "t_returned_plan_info_tran.order_date as as_re_order_date,";
    $arg_str .= "t_returned_plan_info_tran.return_status as as_return_status,";
    $arg_str .= "t_returned_plan_info_tran.return_date as as_return_date,";
    $arg_str .= "t_returned_plan_info_tran.rntl_sect_cd as as_rntl_sect_cd,";
    $arg_str .= "t_returned_plan_info_tran.job_type_cd as as_job_type_cd,";
    $arg_str .= "t_returned_plan_info_tran.werer_cd as as_werer_cd,";
    $arg_str .= "t_returned_plan_info_tran.return_plan_qty as as_return_plan_qty,";
    $arg_str .= "t_returned_plan_info_tran.individual_ctrl_no as as_individual_ctrl_no,";
    $arg_str .= "m_contract.rntl_cont_name as as_rntl_cont_name";
    $arg_str .= " FROM t_order_tran LEFT JOIN t_returned_plan_info_tran";
    //$arg_str .= " (t_returned_plan_info_tran LEFT JOIN";
    //$arg_str .= " (t_order_state LEFT JOIN ";
    //$arg_str .= " (t_delivery_goods_state LEFT JOIN t_delivery_goods_state_details ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no)"; //納品状況情報.出荷行No =  納品状況明細情報.出荷行No.
    //$arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";//納品状況発注状況情報_統合ハッシュキー = 納品状況情報.発注状況情報_統合ハッシュキー
    //$arg_str .= " ON t_returned_plan_info_tran.order_req_no = t_order_state.order_req_no)";//返却予定情報.発注依頼No = 発注状況情報.発注依頼No
    $arg_str .= " ON t_order_tran.order_req_no = t_returned_plan_info_tran.order_req_no"; //発注情報.発注依頼No = 発注状況情報.発注依頼No
    $arg_str .= " INNER JOIN m_section";
    $arg_str .= " ON t_order_tran.m_section_comb_hkey = m_section.m_section_comb_hkey";//発注情報.部門マスタ_統合ハッシュキー = 部門マスタ.部門マスタ_統合ハッシュキー
    $arg_str .= " INNER JOIN (m_job_type INNER JOIN m_input_item";
    $arg_str .= " ON m_job_type.corporate_id = m_input_item.corporate_id";
    $arg_str .= " AND m_job_type.rntl_cont_no = m_input_item.rntl_cont_no";
    $arg_str .= " AND m_job_type.job_type_cd = m_input_item.job_type_cd)";
    $arg_str .= " ON t_returned_plan_info_tran.job_type_cd = m_job_type.job_type_cd";
    $arg_str .= " AND t_returned_plan_info_tran.item_cd = m_input_item.item_cd";
    $arg_str .= " AND t_returned_plan_info_tran.color_cd = m_input_item.color_cd";
    $arg_str .= " INNER JOIN m_wearer_std";
    $arg_str .= " ON t_order_tran.werer_cd = m_wearer_std.werer_cd";
    $arg_str .= " INNER JOIN m_contract";
    $arg_str .= " ON t_order_tran.rntl_cont_no = m_contract.rntl_cont_no";
    $arg_str .= " INNER JOIN m_corporate";
    $arg_str .= " ON t_returned_plan_info_tran.corporate_id = m_corporate.corporate_id";
    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    //ChromePhp::log($arg_str);
    if (!empty($q_sort_key)) {
        $arg_str .= " ORDER BY ";
        $arg_str .= $q_sort_key." ".$order;
    }
    $t_returned_plan_info_tran = new TReturnedPlanInfoTran();
    $results = new Resultset(null, $t_returned_plan_info_tran, $t_returned_plan_info_tran->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];

    if($results_cnt == 0) {
        //FPDIのインスタンス化
        $pdf = new FPDI('L','mm','A4');
        //TCPDFフォントのインスタンス化
        $font = new TCPDF_FONTS();

        //フォント設定
        // フォントを登録
        //ttfフォントをTCPDF用に変換
        $regularFont = $font->addTTFfont(regular_font);
        $boldFont = $font->addTTFfont(bold_font);

        $pdf -> SetFont($regularFont, '', 8);

        // PDFの余白(上左右)を設定
        $pdf->SetMargins(0, 3.0, 0);

        //自動改ページをしない
        $pdf -> SetAutoPageBreak(false);
        //ヘッダ、フッダを使用しない
        $pdf -> setPrintHeader(false);
        $pdf -> setPrintFooter(false);

        //1ページ目を作成
        $pdf -> AddPage();

        //既存のテンプレート用PDFを読み込む
        $pdf -> setSourceFile(pdf_template);

        //既存のテンプレートの１枚目をテンプレートに設定する。
        $page = $pdf -> importPage(1);
        $pdf -> useTemplate($page);

        //作成したPDFをダウンロードする I:ブラウザ D:ダウンロード
        ob_end_clean();
        $pdf -> Output('no_data.pdf' , 'D');

        echo json_encode($json_list);
        return true;
    }


        $paginator_model = new PaginatorModel(
            array(
                "data" => $results,
                "limit" => $page['records_per_page'],
                "page" => $page['page_number']
            )
        );
        $item_check = "";
        $list_array = array();
        $each_array = array();
        $sum_return_qty = 0;


    //---発注区分名 取り出し---//
    $query_list = array();
    array_push($query_list, "cls_cd = '001'");
    array_push($query_list, "gen_cd = '" . $results[0]->as_order_sts_kbn . "'");
    $query_order = implode(' AND ', $query_list);
    $gencode = MGencode::query()
        ->where($query_order)
        ->columns('*')
        ->execute();
    foreach ($gencode as $gencode_map) {
        $list['order_sts_kbn_name'] = $gencode_map->gen_name;
    }

    //---理由区分名 取り出し---//
    $query_list = array();
    array_push($query_list, "cls_cd = '002'");
    array_push($query_list, "gen_cd = '" . $results[0]->as_order_reason_kbn . "'");
    $query_reason = implode(' AND ', $query_list);
    $gencode = MGencode::query()
        ->where($query_reason)
        ->columns('*')
        ->execute();
    foreach ($gencode as $gencode_map) {
        $list['order_reason_kbn_name'] = $gencode_map->gen_name;
    }



    //FPDIのインスタンス化
    $pdf = new FPDI('L','mm','A4');
    //TCPDFフォントのインスタンス化
    $font = new TCPDF_FONTS();

    //フォント設定
    // フォントを登録
    //ttfフォントをTCPDF用に変換
    $regularFont = $font->addTTFfont(regular_font);
    $boldFont = $font->addTTFfont(bold_font);
    //$boldFont = $font->addTTFfont('/library/tcpdf/fonts/migmix-1p-bold.ttf');

    $pdf -> SetFont($regularFont, '', 8);

    // PDFの余白(上左右)を設定
    $pdf->SetMargins(0, 3.0, 0);

    //自動改ページをしない
    $pdf -> SetAutoPageBreak(false);
    //ヘッダ、フッダを使用しない
    $pdf -> setPrintHeader(false);
    $pdf -> setPrintFooter(false);

    //1ページ目を作成
    $pdf -> AddPage();

    //既存のテンプレート用PDFを読み込む
    $pdf -> setSourceFile(pdf_template);

    //既存のテンプレートの１枚目をテンプレートに設定する。
    $page = $pdf -> importPage(1);
    $pdf -> useTemplate($page);


    //しきい値
    $headerX = 38; //ヘッダーのX軸スタート位置

    //HEADERエリア


    //タイトル
    //$now_date = date("Y/m/d");
    $pdf -> SetFont($boldFont, '', 16);
    $pdf -> Text(117, 5, "レンタル商品返却伝票");

    //全体のページのno計算
    $all_page_no = ceil(count($results) / 15);

    $pdf -> SetFont($regularFont, '', 10);
    $pdf -> Text(280, 5, "1/" . $all_page_no);



    $pdf -> SetFont($regularFont, '', 8);

    //企業名
    $pdf->SetFontSize(10);
    $pdf->Text($headerX, 14, $results[0]->as_corporate_name . " 様");
    //$pdf -> Text(72, 21, "１２３４５６７８９０１２３４５６７８９０１");

    //拠点名 + 拠点cd
    $pdf->SetFontSize(10);
    $pdf->Text($headerX, 22, $results[0]->as_rntl_sect_name . "    ( " . $results[0]->as_rntl_sect_cd . " )");

    //着用者名
    $pdf->SetFontSize(10);
    $pdf->Text($headerX, 29, $results[0]->as_werer_name);

    //客先社員コード
    $pdf->SetFontSize(10);
    $pdf->Text(105, 29, $results[0]->as_cster_emply_cd);

    //部門名 + 部門コード
    $pdf->SetFontSize(10);
    $pdf->Text($headerX, 37, $results[0]->as_job_type_name);


    /*
    //企業名
    $pdf -> SetFontSize(10);
    $pdf -> Text($headerX, 14, $results[0]->as_corporate_name . " 様");
    //$pdf -> Text(72, 21, "１２３４５６７８９０１２３４５６７８９０１");

    //契約no (企業id)
    $pdf -> SetFontSize(10);
    $pdf -> Text($headerX, 22, $results[0]->as_rntl_cont_no . "     ( " . $results[0]->as_corporate_id . " )");

    //拠点名 + 拠点cd
    $pdf -> SetFontSize(10);
    $pdf -> Text($headerX, 29, $results[0]->as_rntl_sect_name . "    ( " . $results[0]->as_rntl_sect_cd . " )");

    //着用者名
    $pdf -> SetFontSize(10);
    $pdf -> Text($headerX, 37, $results[0]->as_werer_name);

    //客先社員コード
    $pdf -> SetFontSize(10);
    $pdf -> Text(105, 37, $results[0]->as_cster_emply_cd);

    //部門名 + 部門コード
    $pdf -> SetFontSize(10);
    $pdf -> Text($headerX, 45, $results[0]->as_job_type_name . "    ( " . $results[0]->as_job_type_cd . " )");
    */
    //HEADERエリア


    //RIGHTエリア

    //発注番号
    $pdf -> SetFontSize(11);
    $pdf -> Text(170, 14, $results[0]->as_order_req_no);


    //発注日 日付にスラッシュを入れて出力
    $order_date = $results[0]->as_order_req_ymd;
    $pdf -> Text(250, 14, date('Y/m/d', strtotime($order_date)));


    //発注区分 理由区分
    $pdf -> Text(170, 22, $list['order_sts_kbn_name'] . "   " . $list['order_reason_kbn_name'] . " ");


    //バーコード３９生成
    $style = array(
        'position' => 'S',
        'border' => false,
        'padding' => 4,
        'fgcolor' => array(0,0,0), //0～255三元色
        'bgcolor' => false,
        'text' => false, //下に値を出す
        'font' => 'helvetica',
        'fontsize' => 8,
        'stretchtext' => 4
    );

    //$pdf->write1DBarcode(バーコード値, 'C39', x座標, y座標, 幅, 高さ, 0.4, $style, 'N');
    $pdf->write1DBarcode($results[0]->as_order_req_no, 'C39', 190, 26, 60, 28, 0.4, $style, 'N');
    //RIGHTエリア


    //個体管理番号ありの前処理
    if ($individual_check == '1') {
        foreach ($results as $item) {
            if ($item_check === $item->as_item_cd . $item->as_color_cd . $item->as_size_cd) {

                if (count($each_array) > 1) {
                    $group_array[] = $each_array;
                }
                $sum_return_qty = $sum_return_qty + $item->as_return_plan_qty;
                $group_array[] = array(
                    'item_cd' => "",
                    'color_cd' => "",
                    'size_cd' => "",
                    'return_plan_qty' => "",
                    'input_item_name' => "",
                    'individual_ctrl_no' => $item->as_individual_ctrl_no,
                    'border' => "LR",
                );
                $each_array = array();
            } else {

                //グループの配列があれば、リストの配列にグループを入れる
                if (count($group_array) > 0) {
                    $group_array[0]["return_plan_qty"] = $sum_return_qty;
                    $group_array[0]["border"] = "LR";

                    //商品ごとのグループを１行ずつ、出力用の配列に入れる
                    $i = 0;
                    foreach($group_array as $value){
                        array_push($list_array,$group_array[$i++]);
                    }

                    $group_array = array();
                    $sum_return_qty = 0;
                }
                //前の行と違う場合に、$each_arrayに値が入っていれば出力用の配列に入れる
                if (count($each_array) > 0) {
                    array_push($list_array, $each_array);
                    $each_array = array();
                    $sum_return_qty = 0;
                }


                //サイズコード2がある場合は連結
                if(mb_strlen(trim($item->as_size_two_cd)) !== 0){
                    $size_cd = $item->as_size_cd . "-" . $item->as_size_two_cd;
                }else{
                    $size_cd = $item->as_size_cd;
                }

                $each_array = array(
                    'item_cd' => $item->as_item_cd . "-" . $item->as_color_cd,
                    'color_cd' => $item->as_color_cd,
                    'size_cd' => $size_cd,
                    'return_plan_qty' => $item->as_return_plan_qty,
                    'individual_ctrl_no' => $item->as_individual_ctrl_no,
                    'input_item_name' => $item->as_input_item_name,
                    'border' => 1,
                );

                $sum_return_qty = $sum_return_qty + $item->as_return_plan_qty;
                $item_check = $item->as_item_cd . $item->as_color_cd . $item->as_size_cd;
            }

        }
        if (count($group_array) > 0) {
            $group_array[0]["return_plan_qty"] = count($group_array);
            $group_array[0]["border"] = "LR";

            //商品ごとのグループを１行ずつ、出力用の配列に入れる
            $i = 0;
            foreach($group_array as $value){
                array_push($list_array,$group_array[$i++]);
            }
            $group_array = array();
            $sum_return_qty = 0;
        }

        //最後の行を出力用の配列に入れる
        if (count($each_array) > 0) {
            array_push($list_array, $each_array);
        }
    }


    if ($individual_check == '1'){  //個体管理番号ありの出力
        //商品レコード見出しの高さ
        $header_height = 6;

        //角横幅(Width)しきい値
        $width01 = 12;//項番
        $width02 = 60;//商品-色
        $width03 = 60;//商品名
        $width04 = 30;//サイズ
        $width05 = 30;//返却する数量（枚）
        $width06 = 54;//個体管理番号
        $width07 = 25;//チェック欄

        //商品レコード欄
        $item_height = 7.8;//商品行Y幅
        $item_startX = 12.0;//商品行左側余白


        //しきい値（返却枚数合計）
        $returnSetX = 24.0; //X位置
        $returnTitleW = 150; //見出し枠の横幅
        $returnSumW = 30; //合計枠の横幅
        $returnSumH = 8; //見出し枠と合計枠の縦幅

        $no_list = 1;
        $i = 0;
        $page_no = 1;
        $sum_all_qty = 0;
        $i_page = 0;

        //受注情報エリア 返却商品の数だけforを回す
        for($count = 1; $count <= $results_cnt/*$results_cnt*/; $count++) {


            if ($count == 1) {       //返却商品が1個ある時
                //テンプレ $pdf->Cell(横幅, 縦幅, '文字列', ボーダー(0 or 1), 次の位置(0 or 1), 'C');
                //tableHeader
                $pdf->SetFontSize(11);
                $pdf->SetXY($item_startX, 53.0);
                $pdf->Cell($width01, $header_height, 'No.', 1, 0, 'C');
                $pdf->Cell($width02, $header_height, '商品コード', 1, 0, 'C');
                $pdf->Cell($width03, $header_height, '商品名', 1, 0, 'C');
                $pdf->Cell($width04, $header_height, 'サイズ', 1, 0, 'C');
                $pdf->Cell($width05, $header_height, '返却数', 1, 0, 'C');
                $pdf->Cell($width06, $header_height, '個体管理番号', 1, 0, 'C');
                $pdf->Cell($width07, $header_height, 'チェック欄', 1, 1, 'C');
                //tableHeader
            }


            //15行目の商品があればボーターあり、無しなら下線だけあり
            if (($count % 15) == 0) {
                if($list_array[$i]["input_item_name"] !== ""){
                    if($list_array[$i]["return_plan_qty"] == 1){
                        $list_array[$i]["border"] = 1;
                    }else{
                        $list_array[$i]["border"] = '1';
                    }
                }else {
                    $list_array[$i]["border"] = 'LRB';
                }
            }


            $pdf->SetX($item_startX);

            $pdf->SetFontSize(11);
            //1行目のセルを整形
            $pdf->Cell($width01, $item_height, $no_list++, 1, 0, 'C');
            $pdf->Cell($width02, $item_height, $list_array[$i]["item_cd"], $list_array[$i]["border"], 0, 'C');
            $pdf->Cell($width03, $item_height, $list_array[$i]["input_item_name"], $list_array[$i]["border"], 0, 'C');
            $pdf->Cell($width04, $item_height, $list_array[$i]["size_cd"], $list_array[$i]["border"], 0, 'C');
            $pdf->Cell($width05, $item_height, $list_array[$i]["return_plan_qty"], $list_array[$i]["border"], 0, 'C');
            $pdf->Cell($width06, $item_height, $list_array[$i]["individual_ctrl_no"], 1, 0, 'C');
            $pdf->Cell($width07, $item_height, '□', 1, 1, 'C');
            $sum_all_qty = $sum_all_qty + $list_array[$i]["return_plan_qty"];
            $i++;

            if (($count % 15) == 0) {
                //15,30,45,60などの15で割り切れる数の場合は処理をしない。
                $i_page++;
                if ($i_page < $all_page_no) {
                    //着用者コード
                    $pdf->SetFontSize(8);
                    $pdf->Text(13, 192, $results[0]->as_corporate_id."-".$results[0]->as_rntl_cont_no."-".$results[0]->as_rntl_sect_cd."-".$results[0]->as_job_type_cd."-".$results[0]->as_werer_cd);
                    //着用者コード

                    //2ページ目を作成
                    $pdf->AddPage();
                    //既存のテンプレート用PDFを読み込む
                    $pdf->setSourceFile(pdf_template);
                    //既存のテンプレートの１枚目をテンプレートに設定する。
                    $page = $pdf->importPage(1);
                    $pdf->useTemplate($page);

                    //HEADERエリア

                    //タイトル
                    //$now_date = date("Y/m/d");
                    $pdf->SetFont($boldFont, '', 16);
                    $pdf->Text(117, 5, "レンタル商品返却伝票");


                    $pdf->SetFont($regularFont, '', 10);

                    $pdf->Text(280, 5, ++$page_no . "/" . $all_page_no);

                    $pdf->SetFont($regularFont, '', 8);


                    //企業名
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 14, $results[0]->as_corporate_name . " 様");
                    //$pdf -> Text(72, 21, "１２３４５６７８９０１２３４５６７８９０１");

                    //拠点名 + 拠点cd
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 22, $results[0]->as_rntl_sect_name . "    ( " . $results[0]->as_rntl_sect_cd . " )");

                    //着用者名
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 29, $results[0]->as_werer_name);

                    //客先社員コード
                    $pdf->SetFontSize(10);
                    $pdf->Text(105, 29, $results[0]->as_cster_emply_cd);

                    //部門名 + 部門コード
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 37, $results[0]->as_job_type_name);
                    /*
                    //企業名
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 14, $results[0]->as_corporate_name . " 様");
                    //$pdf -> Text(72, 21, "１２３４５６７８９０１２３４５６７８９０１");

                    //契約no (企業id)
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 22, $results[0]->as_rntl_cont_no . "     ( " . $results[0]->as_corporate_id . " )");

                    //拠点名 + 拠点cd
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 29, $results[0]->as_rntl_sect_name . "    ( " . $results[0]->as_rntl_sect_cd . " )");

                    //着用者名
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 37, $results[0]->as_werer_name);

                    //客先社員コード
                    $pdf->SetFontSize(10);
                    $pdf->Text(105, 37, $results[0]->as_cster_emply_cd);

                    //部門名 + 部門コード
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 45, $results[0]->as_job_type_name . "    ( " . $results[0]->as_job_type_cd . " )");
                       */
                    //HEADERエリア


                    //RIGHTエリア

                    //発注番号
                    $pdf->SetFontSize(11);
                    $pdf->Text(170, 14, $results[0]->as_order_req_no);

                    //発注日 日付にスラッシュを入れて出力
                    $order_date = $results[0]->as_order_req_ymd;
                    $pdf->Text(250, 14, date('Y/m/d', strtotime($order_date)));


                    //発注区分 理由区分
                    $pdf->Text(170, 22, $list['order_sts_kbn_name'] . "   " . $list['order_reason_kbn_name'] . " ");


                    //バーコード３９生成
                    $style = array(
                        'position' => 'S',
                        'border' => false,
                        'padding' => 4,
                        'fgcolor' => array(0, 0, 0), //0～255三元色
                        'bgcolor' => false,
                        'text' => false, //下に値を出す
                        'font' => 'helvetica',
                        'fontsize' => 8,
                        'stretchtext' => 4
                    );


                    //$pdf->write1DBarcode(バーコード値, 'C39', x座標, y座標, 幅, 高さ, 0.4, $style, 'N');
                    $pdf->write1DBarcode($results[0]->as_order_req_no, 'C39', 190, 26, 60, 28, 0.4, $style, 'N');
                    //RIGHTエリア

                    //tableHeader
                    $pdf->SetFontSize(11);
                    $pdf->SetXY($item_startX, 53.0);
                    $pdf->Cell($width01, $header_height, 'No.', 1, 0, 'C');
                    $pdf->Cell($width02, $header_height, '商品コード', 1, 0, 'C');
                    $pdf->Cell($width03, $header_height, '商品名', 1, 0, 'C');
                    $pdf->Cell($width04, $header_height, 'サイズ', 1, 0, 'C');
                    $pdf->Cell($width05, $header_height, '返却数', 1, 0, 'C');
                    $pdf->Cell($width06, $header_height, '個体管理番号', 1, 0, 'C');
                    $pdf->Cell($width07, $header_height, 'チェック欄', 1, 1, 'C');
                    //tableHeader
                }
            }
        }
        //受注情報エリア

        //返却枚数合計
        $pdf->SetX($returnSetX);
        $pdf->Cell($returnTitleW, $returnSumH, '返却数合計', 1, 0, 'C');
        $pdf->Cell($returnSumW, $returnSumH, $sum_all_qty/*合計*/, 1, 0, 'C');



    }elseif($individual_check == '0'){ //個体管理番号なしの出力
        //商品レコード見出しの高さ
        $header_height = 6;

        //角横幅(Width)しきい値
        $width01 = 12;//項番
        $width02 = 80;//商品-色
        $width03 = 80;//商品名
        $width04 = 35;//サイズ
        $width05 = 40;//返却する数量（枚）
        $width06 = 25;//チェック欄


        //商品レコード欄
        $item_height = 7.8;//商品行Y幅
        $item_startX = 12.0;//商品行左側余白


        //しきい値（返却枚数合計）
        $returnSetX = 24.0; //X位置
        $returnTitleW = 195; //見出し枠の横幅
        $returnSumW = 40; //合計枠の横幅
        $returnSumH = 8; //見出し枠と合計枠の縦幅

        $no_list = 1;
        $i = 0;
        $page_no = 1;
        $i_page = 0;
        $sum_all_qty = 0;


        //受注情報エリア 返却商品の数だけforを回す
        for($count = 1; $count <= $results_cnt; $count++){

            if($count == 1) {       //返却商品が1個ある時
                //テンプレ $pdf->Cell(横幅, 縦幅, '文字列', ボーダー(0 or 1), 次の位置(0 or 1), 'C');
                //tableHeader
                $pdf->SetFontSize(11);
                $pdf->SetXY($item_startX, 53.0);
                $pdf->Cell($width01, $header_height, 'No.', 1, 0, 'C');
                $pdf->Cell($width02, $header_height, '商品コード', 1, 0, 'C');
                $pdf->Cell($width03, $header_height, '商品名', 1, 0, 'C');
                $pdf->Cell($width04, $header_height, 'サイズ', 1, 0, 'C');
                $pdf->Cell($width05, $header_height, '返却数', 1, 0, 'C');
                $pdf->Cell($width06, $header_height, 'チェック欄', 1, 1, 'C');
                //tableHeader
            }

            $pdf->SetX($item_startX);

            //サイズコード2がある場合は連結
            if(mb_strlen(trim($results[$i]->as_size_two_cd)) !== 0){
                $size_cd = $results[$i]->as_size_cd . "-" . $results[$i]->as_size_two_cd;
            }else{
                $size_cd = $results[$i]->as_size_cd;
            }

            $pdf->SetFontSize(11);
            //1行目
            $pdf->Cell($width01, $item_height, $no_list++, 1, 0, 'C');
            $pdf->Cell($width02, $item_height, $results[$i]->as_item_cd."-".$results[$i]->as_color_cd, 1, 0, 'C');
            $pdf->Cell($width03, $item_height, $results[$i]->as_input_item_name, 1, 0, 'C');
            $pdf->Cell($width04, $item_height, $size_cd, 1, 0, 'C');
            $pdf->Cell($width05, $item_height, $results[$i]->as_return_plan_qty, 1, 0, 'C');
            $pdf->Cell($width06, $item_height, '□', 1, 1, 'C');

            $sum_all_qty = $sum_all_qty + $results[$i]->as_return_plan_qty;
            $i++;

            if(($count % 15) == 0) {
                //15,30,45,60などの15で割り切れる数の場合は処理をしない。
                $i_page++;
                if ($i_page < $all_page_no) {
                    //着用者コード
                    $pdf->SetFontSize(8);
                    $pdf->Text(13, 192, $results[0]->as_corporate_id."-".$results[0]->as_rntl_cont_no."-".$results[0]->as_rntl_sect_cd."-".$results[0]->as_job_type_cd."-".$results[0]->as_werer_cd);
                    //着用者コード

                    //2ページ目を作成
                    $pdf->AddPage();
                    //既存のテンプレート用PDFを読み込む
                    $pdf->setSourceFile(pdf_template);
                    //既存のテンプレートの１枚目をテンプレートに設定する。
                    $page = $pdf->importPage(1);
                    $pdf->useTemplate($page);

                    //HEADERエリア

                    //タイトル
                    //$now_date = date("Y/m/d");
                    $pdf->SetFont($boldFont, '', 16);
                    $pdf->Text(117, 5, "レンタル商品返却伝票");


                    $pdf->SetFont($regularFont, '', 10);
                    $pdf->Text(280, 5, ++$page_no . "/" . $all_page_no);


                    $pdf->SetFont($regularFont, '', 8);
                    //企業名
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 14, $results[0]->as_corporate_name . " 様");
                    //$pdf -> Text(72, 21, "１２３４５６７８９０１２３４５６７８９０１");

                    //拠点名 + 拠点cd
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 22, $results[0]->as_rntl_sect_name . "    ( " . $results[0]->as_rntl_sect_cd . " )");

                    //着用者名
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 29, $results[0]->as_werer_name);

                    //客先社員コード
                    $pdf->SetFontSize(10);
                    $pdf->Text(105, 29, $results[0]->as_cster_emply_cd);

                    //部門名 + 部門コード
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 37, $results[0]->as_job_type_name);
                    /*
                    //契約no (企業id)
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 22, $results[0]->as_rntl_cont_no . "     ( " . $results[0]->as_corporate_id . " )");

                    //拠点名 + 拠点cd
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 29, $results[0]->as_rntl_sect_name . "    ( " . $results[0]->as_rntl_sect_cd . " )");

                    //着用者名
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 37, $results[0]->as_werer_name);

                    //客先社員コード
                    $pdf->SetFontSize(10);
                    $pdf->Text(105, 37, $results[0]->as_cster_emply_cd);

                    //部門名 + 部門コード
                    $pdf->SetFontSize(10);
                    $pdf->Text($headerX, 45, $results[0]->as_job_type_name . "    ( " . $results[0]->as_job_type_cd . " )");
                    */
                    //HEADERエリア


                    //RIGHTエリア

                    //発注番号
                    $pdf->SetFontSize(11);
                    $pdf->Text(170, 14, $results[0]->as_order_req_no);

                    //発注日 日付にスラッシュを入れて出力
                    $order_date = $results[0]->as_order_req_ymd;
                    $pdf->Text(250, 14, date('Y/m/d', strtotime($order_date)));


                    //発注区分 理由区分
                    $pdf->Text(170, 22, $list['order_sts_kbn_name'] . "   " . $list['order_reason_kbn_name'] . " ");


                    //バーコード３９生成
                    $style = array(
                        'position' => 'S',
                        'border' => false,
                        'padding' => 4,
                        'fgcolor' => array(0, 0, 0), //0～255三元色
                        'bgcolor' => false,
                        'text' => false, //下に値を出す
                        'font' => 'helvetica',
                        'fontsize' => 8,
                        'stretchtext' => 4
                    );


                    //$pdf->write1DBarcode(バーコード値, 'C39', x座標, y座標, 幅, 高さ, 0.4, $style, 'N');
                    $pdf->write1DBarcode($results[0]->as_order_req_no, 'C39', 190, 26, 60, 28, 0.4, $style, 'N');
                    //RIGHTエリア

                    //tableHeader
                    $pdf->SetFontSize(11);
                    $pdf->SetXY($item_startX, 53.0);
                    $pdf->Cell($width01, $header_height, 'No.', 1, 0, 'C');
                    $pdf->Cell($width02, $header_height, '商品コード', 1, 0, 'C');
                    $pdf->Cell($width03, $header_height, '商品名', 1, 0, 'C');
                    $pdf->Cell($width04, $header_height, 'サイズ', 1, 0, 'C');
                    $pdf->Cell($width05, $header_height, '返却数', 1, 0, 'C');
                    $pdf->Cell($width06, $header_height, 'チェック欄', 1, 1, 'C');
                    //tableHeader

                }
            }

        }
        //受注情報エリア
        //返却枚数合計
        $pdf->SetX($returnSetX);
        $pdf->Cell($returnTitleW, $returnSumH, '返却数合計', 1, 0, 'C');
        $pdf->Cell($returnSumW, $returnSumH, $sum_all_qty/*合計*/, 1, 0, 'C');

    }

    //着用者コード
    $pdf -> SetFontSize(8);
    $pdf -> Text(13, 192, $results[0]->as_corporate_id."-".$results[0]->as_rntl_cont_no."-".$results[0]->as_rntl_sect_cd."-".$results[0]->as_job_type_cd."-".$results[0]->as_werer_cd);
    //着用者コード

    //作成したPDFをダウンロードする I:ブラウザ D:ダウンロード
    ob_end_clean();
    $pdf -> Output('return_print.pdf' , 'D');


    echo json_encode($json_list);
    return true;
});