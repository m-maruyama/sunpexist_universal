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
