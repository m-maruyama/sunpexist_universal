<?php
/*
 * システム定数設定ファイル
 * ※画面単位で各用途の定数を設定してください
 * ★☆基本無断での追加、及び変更は厳禁★☆
 */

//---共通PASS---//
// ダウンロード関連
define("COMMON_PASS", "/app/DATA/");

//---一括データ取込画面---//
// サンプルダウンロードファイル名称
define("IMPORT_SAMPLE_FILE", "import_sample.zip");

//ダイナム様企業ID
define("CORPORATE_ID_DYNAM", "10003681S010000");

define("DOCUMENT_UPLOAD", "/home/uni-doc-upload/tmpdir/DATA/");

//返却伝票印刷設定
//$boldFont = $font->addTTFfont('../app/library/tcpdf/fonts/migmix-1p-bold.ttf');

//返却伝票フォント設定
define("regular_font", "../app/library/tcpdf/fonts/migmix-1p-regular.ttf");
define("bold_font", "../app/library/tcpdf/fonts/migmix-1p-bold.ttf");

//返却伝票テンプレート
define("pdf_template", "template_none.pdf");

//返却伝票返却先住所
define("return_address00", "【返却先住所】");
define("return_address01", "〒374-0012  群馬県館林市羽附旭町  1210-1");
define("return_address02", "㈱サンペックスイスト物流センター内  レンタル部  宛");
define("return_address03", "TEL：0276-80-2733");


//個あり 一括取込 理由区分設定
//貸与
define('order_kbn1_list','["01","02","03","04"]');
//拠点異動のみ
define("order_kbn5_ido", "10");

//個なし 一括取込 理由区分設定
//返却
define("order_kbn2_list", '["05","06","07","08"]');
//サイズ交換
define("order_kbn3_reason", "13");
//その他交換
define("order_kbn4_reason", "14");
//異動
define('order_kbn5_list','["09","10","11"]');


//人員管理区分リスト
define('jinin_order_kbn_list', '{"kbn":
    {
    "1":["01","02","04"],
    "2":["05","06","08"],
    "3":["12","13"],
    "4":["14","15","16","17"],
    "5":["09","10","11"]
    }
}');

//枚数管理区分リスト
define('maisu_order_kbn_list', '{"kbn":
    {
    "1":["19"],
    "2":["20"],
    "3":["21","22"],
    "4":["23"],
    "5":["24"]
    }
}');

//人員管理区分リスト 追加貸与、不要品返却フラグ ON
define('addflg_jinin_order_kbn_list', '{"kbn":
    {
    "1":["03"],
    "2":["07"]
    }
}');

//枚数管理区分リスト 追加貸与、不要品返却フラグ ON
define('addflg_maisu_order_kbn_list', '{"kbn":
    {
    "1":["27"],
    "2":["28"]
    }
}');

//人員管理単位
define("JININTANI", "1");

//枚数管理単位
define("MAISUTANI", "2");


//define('jinin_order_kbn_list', '[
//    {"1":["01","02","03","04"]},
//    {"2":["05","06","07","08"]},
//    {"3":["12","13"]},
//    {"4":["14","15","16","17"]},
//    {"5":["09","10","11"]}
//]');
