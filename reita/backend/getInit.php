<?php
//--------------------------------------------------
//  おえかきけいじばん「Reita」
//  by sakots https://oekakibbs.moe/
//--------------------------------------------------

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type');

//設定の読み込み
require(__DIR__ . '/config.php');

//BladeOne v4.12
include(__DIR__ . '/BladeOne/lib/BladeOne.php');

use eftec\bladeone\BladeOne;
$views = __DIR__ . '/theme/'; // テンプレートフォルダ
$cache = __DIR__ . '/cache'; // キャッシュフォルダ

//タイムゾーン設定
date_default_timezone_set(DEFAULT_TIMEZONE);

$init = [];

//phpのバージョンが古い場合動かさせない
if (($phpVersion = phpversion()) < "7.3.0") {
	$phpErrorString = ["phpError" => "PHP version 7.3 or higher is required for this program to work. <br>\n(Current PHP version:".$phpVersion];
	$init = array_merge($init, $phpErrorString);
}
//コンフィグのバージョンが古くて互換性がない場合動かさせない
if (CONFIG_VER < 240406 ) {
	$configErrorString = ["configError" => "コンフィグファイルに互換性がないようです。再設定をお願いします。<br>\n The configuration file is incompatible. Please reconfigure it."];
	$init = array_merge($init, $configErrorString);
}

//管理パスが初期値(admin)の場合は動作させない
if ($adminPass === 'admin') {
	$adminPassErrorString = ["adminPassError" => "管理パスが初期設定値のままです！危険なので動かせません。<br>\n The admin pass is still at its default value! This program can't run it until you fix it."];
	$init = array_merge($init, $adminPassErrorString);
}

$initData = json_encode($init, JSON_UNESCAPED_UNICODE);

//キャッシュフォルダがなかったら作成
if (!file_exists($cache)) {
	mkdir($cache, PERMISSION_FOR_DIR);
}

print $initData;
