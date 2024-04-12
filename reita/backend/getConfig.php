<?php
//--------------------------------------------------
//  おえかきけいじばん「Reita」
//  by sakots https://oekakibbs.moe/
//--------------------------------------------------

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type');


//スクリプトのバージョン
define('REITA_VER', 'v0.0.0'); //lot.240412.0

//設定の読み込み
require(__DIR__ . '/config.php');

//タイムゾーン設定
date_default_timezone_set(DEFAULT_TIMEZONE);

//phpのバージョンが古い場合動かさせない
if (($phpVer = phpversion()) < "7.3.0") {
	die("PHP version 7.3 or higher is required for this program to work. <br>\n(Current PHP version:{$phpVer})");
}
//コンフィグのバージョンが古くて互換性がない場合動かさせない
if (CONFIG_VER < 240406 ) {
	die("コンフィグファイルに互換性がないようです。再設定をお願いします。<br>\n The configuration file is incompatible. Please reconfigure it.");
}

//管理パスが初期値(admin)の場合は動作させない
if ($admin_pass === 'admin') {
	die("管理パスが初期設定値のままです！危険なので動かせません。<br>\n The admin pass is still at its default value! This program can't run it until you fix it.");
}

//キャッシュフォルダがなかったら作成
if (!file_exists($cache)) {
	mkdir($cache, PERMISSION_FOR_DIR);
}

