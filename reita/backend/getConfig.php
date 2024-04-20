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

