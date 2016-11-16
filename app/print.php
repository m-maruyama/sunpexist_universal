<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorArray;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
require_once ('library/tcpdf/tcpdf.php');
require_once ('library/fpdi/fpdi.php');

/**
 * 返却伝票PDF作成 (t_returned_plan_info) 返却伝票印刷画面
 */
$app->post('/print/pdf', function ()use($app){

    $params = json_decode($_POST['data'], true);
    $json_list = array();
    // アカウントセッション取得
    $auth = $app->session->get("auth");
    $cond = $params["cond"];
    //個別管理番号あるなし　1:あり 0:なし
    $individual_check = $cond['individual_number'];
    //$individual_check = 1;
    $query_list = array();

    //---検索条件---//
    //企業ID
    array_push($query_list,"t_returned_plan_info.corporate_id = '".$auth['corporate_id']."'");

    //発注No
    if(!empty($cond['order_req_no'])){
        array_push($query_list,"t_returned_plan_info.order_req_no LIKE '".$cond['order_req_no']."%'");
    }
    //契約No
    if(!empty($cond['rntl_cont_no'])){
        array_push($query_list,"t_returned_plan_info.rntl_cont_no LIKE '".$cond['rntl_cont_no']."%'");
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
    $arg_str .= "(SELECT distinct on (t_returned_plan_info.order_req_no, t_returned_plan_info.order_req_line_no) ";
    $arg_str .= "t_order.order_req_ymd as as_order_req_ymd,";
    $arg_str .= "t_order.order_reason_kbn as as_order_reason_kbn,";
    $arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
    $arg_str .= "m_job_type.job_type_name as as_job_type_name,";
    $arg_str .= "m_wearer_std.werer_name as as_werer_name,";
    $arg_str .= "m_input_item.input_item_name as as_input_item_name,";
    $arg_str .= "t_order.order_qty as as_order_qty,";
    $arg_str .= "t_order.size_two_cd as as_size_two_cd,";
    $arg_str .= "m_corporate.corporate_name as as_corporate_name,";
    $arg_str .= "t_returned_plan_info.cster_emply_cd as as_cster_emply_cd,";
    $arg_str .= "t_returned_plan_info.corporate_id as as_corporate_id,";
    $arg_str .= "t_returned_plan_info.rntl_cont_no as as_rntl_cont_no,";
    $arg_str .= "t_returned_plan_info.order_req_no as as_order_req_no,";
    $arg_str .= "t_returned_plan_info.item_cd as as_item_cd,";
    $arg_str .= "t_returned_plan_info.color_cd as as_color_cd,";
    $arg_str .= "t_returned_plan_info.size_cd as as_size_cd,";
    $arg_str .= "t_returned_plan_info.order_sts_kbn as as_order_sts_kbn,";
    $arg_str .= "t_returned_plan_info.order_date as as_re_order_date,";
    $arg_str .= "t_returned_plan_info.return_status as as_return_status,";
    $arg_str .= "t_returned_plan_info.return_date as as_return_date,";
    $arg_str .= "t_returned_plan_info.rntl_sect_cd as as_rntl_sect_cd,";
    $arg_str .= "t_returned_plan_info.job_type_cd as as_job_type_cd,";
    $arg_str .= "t_returned_plan_info.werer_cd as as_werer_cd,";
    $arg_str .= "t_returned_plan_info.return_plan_qty as as_return_plan_qty,";
    $arg_str .= "t_returned_plan_info.individual_ctrl_no as as_individual_ctrl_no,";
    $arg_str .= "m_contract.rntl_cont_name as as_rntl_cont_name";
    $arg_str .= " FROM t_order LEFT JOIN";
    $arg_str .= " (t_returned_plan_info LEFT JOIN";
    $arg_str .= " (t_order_state LEFT JOIN ";
    $arg_str .= " (t_delivery_goods_state LEFT JOIN t_delivery_goods_state_details ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no)"; //納品状況情報.出荷行No =  納品状況明細情報.出荷行No.
    $arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";//納品状況発注状況情報_統合ハッシュキー = 納品状況情報.発注状況情報_統合ハッシュキー
    $arg_str .= " ON t_returned_plan_info.order_req_no = t_order_state.order_req_no)";//返却予定情報.発注依頼No = 発注状況情報.発注依頼No
    $arg_str .= " ON t_order.order_req_no = t_returned_plan_info.order_req_no"; //発注情報.発注依頼No = 発注状況情報.発注依頼No
    $arg_str .= " INNER JOIN m_section";
    $arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";//発注情報.部門マスタ_統合ハッシュキー = 部門マスタ.部門マスタ_統合ハッシュキー
    $arg_str .= " INNER JOIN (m_job_type INNER JOIN m_input_item ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey)"; //職種マスタ.職種マスタ_統合ハッシュキー = 投入商品マスタ.職種マスタ_統合ハッシュキー
    $arg_str .= " ON t_order.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
    $arg_str .= " INNER JOIN m_wearer_std";
    $arg_str .= " ON t_order.werer_cd = m_wearer_std.werer_cd";
    $arg_str .= " INNER JOIN m_contract";
    $arg_str .= " ON t_order.rntl_cont_no = m_contract.rntl_cont_no";
    $arg_str .= " INNER JOIN m_corporate";
    $arg_str .= " ON t_returned_plan_info.corporate_id = m_corporate.corporate_id";

    $arg_str .= " WHERE ";
    $arg_str .= $query;
    $arg_str .= ") as distinct_table";
    if (!empty($q_sort_key)) {
        $arg_str .= " ORDER BY ";
        $arg_str .= $q_sort_key." ".$order;
    }
    $t_returned_plan_info = new TReturnedPlanInfo();
    $results = new Resultset(null, $t_returned_plan_info, $t_returned_plan_info->getReadConnection()->query($arg_str));
    $result_obj = (array)$results;
    $results_cnt = $result_obj["\0*\0_count"];

    $paginator_model = new PaginatorModel(
        array(
            "data"  => $results,
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
    $regularFont = $font->addTTFfont('../app/library/tcpdf/fonts/migmix-1p-regular.ttf');
    $boldFont = $font->addTTFfont('../app/library/tcpdf/fonts/migmix-1p-bold.ttf');
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
    $pdf -> setSourceFile('template_none.pdf');

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
                    $pdf->setSourceFile('template_none.pdf');
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
            if(isset($results[$i]->as_size_two_cd)){
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
                    $pdf->setSourceFile('template_none.pdf');
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







/**
 * 返却状況照会検索
 */
$app->post('/print/search', function ()use($app){

	$params = json_decode(file_get_contents("php://input"), true);

	// アカウントセッション取得
	$auth = $app->session->get("auth");
	$cond = $params['cond'];
	$page = $params['page'];
	$query_list = array();

	//---検索条件---//
	//企業ID
	array_push($query_list,"t_returned_plan_info.corporate_id = '".$auth['corporate_id']."'");
	//契約No
	if(!empty($cond['agreement_no'])){
		array_push($query_list,"t_returned_plan_info.rntl_cont_no = '".$cond['agreement_no']."'");
	}
	//発注No
	if(!empty($cond['no'])){
		array_push($query_list,"t_returned_plan_info.order_req_no LIKE '".$cond['no']."%'");
	}
	//お客様発注No
	if(!empty($cond['emply_order_no'])){
		array_push($query_list,"t_order.emply_order_req_no LIKE '".$cond['emply_order_no']."%'");
	}
	//社員番号
	if(!empty($cond['member_no'])){
		array_push($query_list,"m_wearer_std.cster_emply_cd LIKE '".$cond['member_no']."%'");
	}
	//着用者名
	if(!empty($cond['member_name'])){
		array_push($query_list,"m_wearer_std.werer_name LIKE '%".$cond['member_name']."%'");
	}
	//拠点
	if(!empty($cond['section'])){
		array_push($query_list,"t_returned_plan_info.rntl_sect_cd = '".$cond['section']."'");
	}
	//貸与パターン
	if(!empty($cond['job_type'])){
		array_push($query_list,"t_returned_plan_info.rent_pattern_code = '".$cond['job_type']."'");
	}
	//商品
	if(!empty($cond['input_item'])){
		array_push($query_list,"t_returned_plan_info.item_cd = '".$cond['input_item']."'");
	}
	//色
	if(!empty($cond['item_color'])){
		array_push($query_list,"t_returned_plan_info.color_cd = '".$cond['item_color']."'");
	}
	//サイズ
	if(!empty($cond['item_size'])){
		array_push($query_list,"t_returned_plan_info.size_cd = '".$cond['item_size']."'");
	}
	//発注日from
	if(!empty($cond['order_day_from'])){
		array_push($query_list,"TO_DATE(t_order.order_req_ymd,'YYYY/MM/DD') >= TO_DATE('".$cond['order_day_from']."','YYYY/MM/DD')");
	}
	//発注日to
	if(!empty($cond['order_day_to'])){
		array_push($query_list,"TO_DATE(t_order.order_req_ymd,'YYYY/MM/DD') <= TO_DATE('".$cond['order_day_to']."','YYYY/MM/DD')");
	}
	//返却日from
	if(!empty($cond['return_day_from'])){
		$cond['return_day_from'] = date('Y/m/d 00:00:00', strtotime($cond['return_day_from']));
		array_push($query_list,"t_returned_plan_info.return_date >= '".$cond['return_day_from']."'");
//		array_push($query_list,"TO_DATE(t_returned_results.return_date,'YYYY/MM/DD') >= TO_DATE('".$cond['return_day_from']."','YYYY/MM/DD')");
	}
	//返却日to
	if(!empty($cond['return_day_to'])){
		$cond['return_day_to'] = date('Y/m/d 23:59:59', strtotime($cond['return_day_to']));
		array_push($query_list,"t_returned_plan_info.return_date <= '".$cond['return_day_to']."'");
//		array_push($query_list,"TO_DATE(t_returned_results.return_date,'YYYY/MM/DD') <= TO_DATE('".$cond['return_day_to']."','YYYY/MM/DD')");
	}
	//個体管理番号
	if(!empty($cond['individual_number'])){
		array_push($query_list,"t_returned_plan_info.individual_ctrl_no LIKE '".$cond['individual_number']."%'");
	}
	// 着用者状況区分
	array_push($query_list,"m_wearer_std.werer_sts_kbn = '1'");

	$status_kbn_list = array();

	//ステータス
	$status_list = array();
	if($cond['status0']){
		// 未返却
		array_push($status_list,"1");
	}
	if($cond['status1']){
		// 返却済み
		array_push($status_list,"2");
	}
	if(!empty($status_list)) {
		$status_str = implode("','",$status_list);
//		$status_query = "order_status IN ('".$status_str."')";
		array_push($query_list,"t_returned_plan_info.return_status IN ('".$status_str."')");
//		array_push($status_kbn_list,$status_query);
	}
	//発注区分
	$order_kbn = array();
	if($cond['order_kbn0']){
		array_push($order_kbn,'3');
        array_push($order_kbn,'4');
    }
	if($cond['order_kbn1']){
		array_push($order_kbn,'5');
	}
	if($cond['order_kbn2']){
		array_push($order_kbn,'2');
	}
	if($cond['order_kbn3']){
		array_push($order_kbn,'9');
	}
	if(!empty($order_kbn)){
		$order_kbn_str = implode("','",$order_kbn);
		$order_kbn_query = "t_returned_plan_info.order_sts_kbn IN ('".$order_kbn_str."')";
//		array_push($query_list,"order_sts_kbn IN ('".$order_kbn_str."')");
		array_push($status_kbn_list,$order_kbn_query);
	}
	// 理由区分
	$reason_kbn = array();
	if($cond['reason_kbn0']){
		array_push($reason_kbn,'14');
	}
	if($cond['reason_kbn1']){
		array_push($reason_kbn,'15');
	}
	if($cond['reason_kbn2']){
		array_push($reason_kbn,'16');
	}
	if($cond['reason_kbn3']){
		array_push($reason_kbn,'17');
	}
	if($cond['reason_kbn4']){
		array_push($reason_kbn,'21');
	}
	if($cond['reason_kbn5']){
		array_push($reason_kbn,'22');
	}
	if($cond['reason_kbn6']){
		array_push($reason_kbn,'23');
	}
	if($cond['reason_kbn7']){
		array_push($reason_kbn,'09');
	}
	if($cond['reason_kbn8']){
		array_push($reason_kbn,'10');
	}
	if($cond['reason_kbn9']){
		array_push($reason_kbn,'11');
	}
	if($cond['reason_kbn10']){
		array_push($reason_kbn,'05');
	}
	if($cond['reason_kbn11']){
		array_push($reason_kbn,'06');
	}
	if($cond['reason_kbn12']){
		array_push($reason_kbn,'07');
	}
	if($cond['reason_kbn13']){
		array_push($reason_kbn,'08');
	}
	if($cond['reason_kbn14']){
		array_push($reason_kbn,'24');
	}
	if(!empty($reason_kbn)){
		$reason_kbn_str = implode("','",$reason_kbn);
		$reason_kbn_query = "t_order.order_reason_kbn IN ('".$reason_kbn_str."')";
//		array_push($query_list,"order_reason_kbn IN ('".$reason_kbn_str."')");
		array_push($status_kbn_list,$reason_kbn_query);
	}

	//各区分を' OR 'で結合
	if (!empty($status_kbn_list)) {
		$status_kbn_map = implode(' OR ', $status_kbn_list);
		array_push($query_list,"(".$status_kbn_map.")");
	}

	//sql文字列を' AND 'で結合
	$query = implode(' AND ', $query_list);
	$sort_key ='';
	$order ='';

	//ソート設定
	if(isset($page['sort_key'])){
		$sort_key = $page['sort_key'];
		$order = $page['order'];
		if($sort_key == 'order_req_no' || $sort_key == 'order_req_ymd' || $sort_key == 'return_status' || $sort_key == 'order_sts_kbn'){
			$q_sort_key = 'as_'.$sort_key;
		}
		if($sort_key == 'job_type_cd'){
			$q_sort_key = 'as_job_type_name';
		}
		if($sort_key == 'cster_emply_cd'){
			$q_sort_key = 'as_cster_emply_cd';
		}
		if($sort_key == 'rntl_sect_name'){
			$q_sort_key = 'as_rntl_sect_name';
		}
		if($sort_key == 'werer_name'){
			$q_sort_key = 'as_werer_name';
		}
		if($sort_key == 'item_name'){
			$q_sort_key = 'as_input_item_name';
		}
		if($sort_key == 'maker_rec_no'){
			$q_sort_key = 'as_rec_order_no';
		}
		if($sort_key == 'return_shd_ymd'){
			$q_sort_key = 'as_re_order_date';
		}
		if($sort_key == 'maker_send_no'){
			$q_sort_key = 'as_ship_no';
		}
		if($sort_key == 'ship_ymd'){
			$q_sort_key = 'as_ship_ymd';
		}
		if($sort_key == 'send_ymd'){
			$q_sort_key = 'as_ship_ymd';
		}
		if($sort_key == 'individual_num'){
			$q_sort_key = 'as_individual_ctrl_no';
		}
		if($sort_key == 'order_res_ymd'){
			$q_sort_key = 'as_receipt_date';
		}
		if($sort_key == 'rental_no'){
			$q_sort_key = 'as_rntl_cont_no';
		}
		if($sort_key == 'rental_name'){
			$q_sort_key = 'as_rntl_cont_name';
		}
	} else {
		//指定がなければ発注No
		$q_sort_key = "as_order_req_no";
		$order = 'asc';
	}

	//---SQLクエリー実行---//
	$arg_str = "SELECT ";
	$arg_str .= " * ";
	$arg_str .= " FROM ";
//	$arg_str .= "(SELECT ";
	$arg_str .= "(SELECT distinct on (t_returned_plan_info.order_req_no, t_returned_plan_info.order_req_line_no) ";
	$arg_str .= "t_returned_plan_info.order_req_no as as_order_req_no,";
	$arg_str .= "t_order.order_req_ymd as as_order_req_ymd,";
	$arg_str .= "t_returned_plan_info.order_sts_kbn as as_order_sts_kbn,";
	$arg_str .= "t_order.order_reason_kbn as as_order_reason_kbn,";
	$arg_str .= "m_section.rntl_sect_name as as_rntl_sect_name,";
	$arg_str .= "m_job_type.job_type_name as as_job_type_name,";
	$arg_str .= "m_wearer_std.cster_emply_cd as as_cster_emply_cd,";
	$arg_str .= "m_wearer_std.werer_name as as_werer_name,";
	$arg_str .= "t_returned_plan_info.item_cd as as_item_cd,";
	$arg_str .= "t_returned_plan_info.color_cd as as_color_cd,";
	$arg_str .= "t_returned_plan_info.size_cd as as_size_cd,";
	$arg_str .= "t_order.size_two_cd as as_size_two_cd,";
	$arg_str .= "m_input_item.input_item_name as as_input_item_name,";
	$arg_str .= "t_order.order_qty as as_order_qty,";
	$arg_str .= "t_returned_plan_info.order_date as as_re_order_date,";
	$arg_str .= "t_returned_plan_info.return_status as as_return_status,";
	$arg_str .= "t_returned_plan_info.return_date as as_return_date,";
    $arg_str .= "t_returned_plan_info.rntl_sect_cd as as_rntl_sect_cd,";
    $arg_str .= "t_returned_plan_info.job_type_cd as as_job_type_cd,";
    $arg_str .= "t_delivery_goods_state.rec_order_no as as_rec_order_no,";
    $arg_str .= "t_delivery_goods_state.ship_no as as_ship_no,";
	$arg_str .= "t_delivery_goods_state.ship_ymd as as_ship_ymd,";
	$arg_str .= "t_delivery_goods_state.ship_qty as as_ship_qty,";
	$arg_str .= "t_delivery_goods_state.return_qty as as_return_qty,";
	$arg_str .= "t_returned_plan_info.individual_ctrl_no as as_individual_ctrl_no,";
	$arg_str .= "t_delivery_goods_state_details.receipt_date as as_receipt_date,";
	$arg_str .= "t_returned_plan_info.rntl_cont_no as as_rntl_cont_no,";
	$arg_str .= "m_contract.rntl_cont_name as as_rntl_cont_name";
	$arg_str .= " FROM t_order LEFT JOIN";
	$arg_str .= " (t_returned_plan_info LEFT JOIN";
	$arg_str .= " (t_order_state LEFT JOIN ";
    $arg_str .= " (t_delivery_goods_state LEFT JOIN t_delivery_goods_state_details ON t_delivery_goods_state.ship_no = t_delivery_goods_state_details.ship_no)"; //納品状況情報.出荷行No =  納品状況明細情報.出荷行No.
	$arg_str .= " ON t_order_state.t_order_state_comb_hkey = t_delivery_goods_state.t_order_state_comb_hkey)";//納品状況発注状況情報_統合ハッシュキー = 納品状況情報.発注状況情報_統合ハッシュキー
	$arg_str .= " ON t_returned_plan_info.order_req_no = t_order_state.order_req_no)";//返却予定情報.発注依頼No = 発注状況情報.発注依頼No
	$arg_str .= " ON t_order.order_req_no = t_returned_plan_info.order_req_no"; //発注情報.発注依頼No = 発注状況情報.発注依頼No
	$arg_str .= " INNER JOIN m_section";
	$arg_str .= " ON t_order.m_section_comb_hkey = m_section.m_section_comb_hkey";//発注情報.部門マスタ_統合ハッシュキー = 部門マスタ.部門マスタ_統合ハッシュキー
	$arg_str .= " INNER JOIN (m_job_type INNER JOIN m_input_item ON m_job_type.m_job_type_comb_hkey = m_input_item.m_job_type_comb_hkey)"; //職種マスタ.職種マスタ_統合ハッシュキー = 投入商品マスタ.職種マスタ_統合ハッシュキー
	$arg_str .= " ON t_order.m_job_type_comb_hkey = m_job_type.m_job_type_comb_hkey";
	$arg_str .= " INNER JOIN m_wearer_std";
	$arg_str .= " ON t_order.werer_cd = m_wearer_std.werer_cd";
	$arg_str .= " INNER JOIN m_contract";
	$arg_str .= " ON t_order.rntl_cont_no = m_contract.rntl_cont_no";
	$arg_str .= " WHERE ";
	$arg_str .= $query;
	$arg_str .= ") as distinct_table";
	if (!empty($q_sort_key)) {
		$arg_str .= " ORDER BY ";
		$arg_str .= $q_sort_key." ".$order;
	}
	$t_order = new TOrder();
	$results = new Resultset(null, $t_order, $t_order->getReadConnection()->query($arg_str));
	$result_obj = (array)$results;
	$results_cnt = $result_obj["\0*\0_count"];

	$paginator_model = new PaginatorModel(
		array(
			"data"  => $results,
			"limit" => $page['records_per_page'],
			"page" => $page['page_number']
		)
	);

	$list = array();
	$all_list = array();
	$json_list = array();

	if(!empty($results_cnt)) {
		$paginator = $paginator_model->getPaginate();
		$results = $paginator->items;

		foreach($results as $result){
			// 発注依頼No.
			if (!empty($result->as_order_req_no)) {
				$list['order_req_no'] = $result->as_order_req_no;
			} else {
				$list['order_req_no'] = "-";
			}
			// 発注依頼日
			$list['order_req_ymd'] = $result->as_order_req_ymd;
			// 発注区分
			$list['order_sts_kbn'] = $result->as_order_sts_kbn;
			// 理由区分
			$list['order_reason_kbn'] = $result->as_order_reason_kbn;
			// 拠点
            if (!empty($result->as_rntl_sect_cd)) {
                $list['rntl_sect_cd'] = $result->as_rntl_sect_cd;
            } else {
                $list['rntl_sect_cd'] = "-";
            }
			if (!empty($result->as_rntl_sect_name)) {
				$list['rntl_sect_name'] = $result->as_rntl_sect_name;
			} else {
				$list['rntl_sect_name'] = "-";
			}
			// 貸与パターン
            if (!empty($result->as_job_type_cd)) {
                $list['job_type_cd'] = $result->as_job_type_cd;
            } else {
                $list['job_type_cd'] = "-";
            }

			if (!empty($result->as_job_type_name)) {
				$list['job_type_name'] = $result->as_job_type_name;
			} else {
				$list['job_type_name'] = "-";
			}
			// 社員番号
			if (!empty($result->as_cster_emply_cd)) {
				$list['cster_emply_cd'] = $result->as_cster_emply_cd;
			} else {
				$list['cster_emply_cd'] = "-";
			}
			// 着用者名
			if (!empty($result->as_werer_name)) {
				$list['werer_name'] = $result->as_werer_name;
			} else {
				$list['werer_name'] = "-";
			}
			// 商品コード
			$list['item_cd'] = $result->as_item_cd;
			// 色コード
			$list['color_cd'] = $result->as_color_cd;
			// サイズコード
			$list['size_cd'] = $result->as_size_cd;
			// サイズ2コード
			$list['size_two_cd'] = $result->as_size_two_cd;
			// 投入商品名
			if (!empty($result->as_input_item_name)) {
				$list['input_item_name'] = $result->as_input_item_name;
			} else {
				$list['input_item_name'] = "-";
			}
			// 発注数
			$list['order_qty'] = $result->as_order_qty;
			// メーカー受注番号
			if (!empty($result->as_rec_order_no)) {
				$list['rec_order_no'] = $result->as_rec_order_no;
			} else {
				$list['rec_order_no'] = "-";
			}
			// 返却日
			$list['re_order_date'] = $result->as_re_order_date;
			// 返却ステータス
			$list['return_status'] = $result->as_return_status;
			// 返却数
			$list['return_qty'] = $result->as_return_qty;
			// メーカー伝票番号
			if (!empty($result->as_ship_no)) {
				$list['ship_no'] = $result->as_ship_no;
			} else {
				$list['ship_no'] = "-";
			}
			// 出荷日
			$list['ship_ymd'] = $result->as_ship_ymd;
			// 出荷数
			$list['ship_qty'] = $result->as_ship_qty;
			// 契約No
			if (!empty($result->as_rntl_cont_no)) {
				$list['rntl_cont_no'] = $result->as_rntl_cont_no;
			} else {
				$list['rntl_cont_no'] = "-";
			}
			// 契約No
			if (!empty($result->as_rntl_cont_name)) {
				$list['rntl_cont_name'] = $result->as_rntl_cont_name;
			} else {
				$list['rntl_cont_name'] = "-";
			}

			//---日付設定---//
			// 発注依頼日
			if(!empty($list['order_req_ymd'])){
				$list['order_req_ymd'] = date('Y/m/d',strtotime($list['order_req_ymd']));
			}else{
				$list['order_req_ymd'] = '-';
			}
			// 依頼日（返却予定日）
			if(!empty($list['re_order_date'])){
				$list['re_order_date'] =  date('Y/m/d',strtotime($list['re_order_date']));
			}else{
				$list['re_order_date'] = '-';
			}
			// 出荷日
			if(!empty($list['ship_ymd'])){
				$list['ship_ymd'] =  date('Y/m/d',strtotime($list['ship_ymd']));
			}else{
				$list['ship_ymd'] = '-';
			}

			// 商品-色(サイズ-サイズ2)表示変換
			$list['shin_item_code'] = $list['item_cd']."-".$list['color_cd']."(".$list['size_cd']."-".$list['size_two_cd'].")";

			//---発注区分名称---//
			$query_list = array();
			// 汎用コードマスタ.分類コード
			array_push($query_list, "cls_cd = '001'");
			// 汎用コードマスタ. レンタル契約No
			array_push($query_list, "gen_cd = '".$list['order_sts_kbn']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $query_list);
			$gencode = MGencode::query()
				->where($query)
				->columns('*')
				->execute();
			foreach ($gencode as $gencode_map) {
				$list['order_sts_name'] = $gencode_map->gen_name;
			}

			//---理由区分名称---//
			$query_list = array();
			// 汎用コードマスタ.分類コード
			array_push($query_list, "cls_cd = '002'");
			// 汎用コードマスタ. レンタル契約No
			array_push($query_list, "gen_cd = '".$list['order_reason_kbn']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $query_list);
			$gencode = MGencode::query()
				->where($query)
				->columns('*')
				->execute();
			foreach ($gencode as $gencode_map) {
				$list['order_reason_name'] = $gencode_map->gen_name;
			}

			//---返却ステータス名称---//
			$query_list = array();
			// 汎用コードマスタ.分類コード
			array_push($query_list, "cls_cd = '008'");
			// 汎用コードマスタ. レンタル契約No
			array_push($query_list, "gen_cd = '".$list['return_status']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $query_list);
			$gencode = MGencode::query()
				->where($query)
				->columns('*')
				->execute();
			foreach ($gencode as $gencode_map) {
				$list['return_status_name'] = $gencode_map->gen_name;
			}


			//---個体管理番号・受領日時の取得---//
			$query_list = array();
			// 納品状況明細情報. 企業ID
			array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
			// 納品状況明細情報. 出荷No
			array_push($query_list, "ship_no = '".$list['ship_no']."'");
			//sql文字列を' AND 'で結合
			$query = implode(' AND ', $query_list);
			$del_gd_std = TDeliveryGoodsStateDetails::query()
				->where($query)
				->columns('*')
				->execute();
			if ($del_gd_std) {
				$num_list = array();
				$day_list = array();
				foreach ($del_gd_std as $del_gd_std_map) {
					array_push($num_list, $del_gd_std_map->individual_ctrl_no);
					array_push($day_list, date('Y/m/d',strtotime($del_gd_std_map->receipt_date)));
				}
				// 個体管理番号
				$individual_ctrl_no = implode("<br>", $num_list);
				$list['individual_num'] = $individual_ctrl_no;
				// 受領日
				$receipt_date = implode("<br>", $day_list);
				$list['order_res_ymd'] = $receipt_date;
			} else {
				$list['individual_num'] = "-";
				$list['order_res_ymd'] = "-";
			}

			array_push($all_list,$list);
		}
	}

	//ソート設定(配列ソート)
	// 商品-色(サイズ-サイズ2)
	if($sort_key == 'item_code'){
		if ($order == 'asc') {
			array_multisort(array_column($all_list, 'shin_item_code'), SORT_DESC, $all_list);
		} else {
			array_multisort(array_column($all_list, 'shin_item_code'), SORT_ASC, $all_list);
		}
	}

	// 個体管理番号表示/非表示フラグ設定
	if ($auth["individual_flg"] == 1) {
		$individual_flg = true;
	} else {
		$individual_flg = false;
	}
/*
	$query_list = array();
	array_push($query_list, "corporate_id = '".$auth['corporate_id']."'");
	array_push($query_list, "rntl_cont_no = '".$cond['agreement_no']."'");
	$query = implode(' AND ', $query_list);
	$m_contract = MContract::query()
		->where($query)
		->columns('*')
		->execute();
	$m_contract_obj = (array)$m_contract;
	$cnt = $m_contract_obj["\0*\0_count"];
	$individual_flg = "";
	if (!empty($cnt)) {
		foreach ($m_contract as $m_contract_map) {
			$individual_flg = $m_contract_map->individual_flg;
		}
		if ($individual_flg == 1) {
			$individual_flg = true;
		} else {
			$individual_flg = false;
		}
	}
*/

	$page_list['records_per_page'] = $page['records_per_page'];
	$page_list['page_number'] = $page['page_number'];
	$page_list['total_records'] = $results_cnt;
	$json_list['page'] = $page_list;
	$json_list['list'] = $all_list;
	$json_list['individual_flag'] = $individual_flg;
	echo json_encode($json_list);
});
