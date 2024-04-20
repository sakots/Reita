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
	$configErrorString = ["configError" => "コンフィグファイルに互換性がないようです。再設定をお願いします。<br>\n The configuration file is incompatible. Please reconfigure it.<br>\n"];
	$init = array_merge($init, $configErrorString);
}

//管理パスが初期値(admin)の場合は動作させない
if ($adminPass === 'admin') {
	$adminPassErrorString = ["adminPassError" => "管理パスが初期設定値のままです！危険なので動かせません。<br>\n The admin pass is still at its default value! This program can't run it until you fix it.<br>\n"];
	$init = array_merge($init, $adminPassErrorString);
}

//キャッシュフォルダがなかったら作成
if (!file_exists($cache)) {
	mkdir($cache, PERMISSION_FOR_DIR);
}

//データベース接続PDO
define('DB_PDO', 'sqlite:' . DB_NAME . '.db');

try {
	if (!is_file(DB_NAME . '.db')) {
		// はじめての実行なら、テーブルを作成
		// id, 書いた日時, 修正日時, スレ親orレス, 親スレ, コメントid, スレ構造ID,
		// 名前, メール, タイトル, 本文, url, ホスト,
		// そうだね, 投稿者ID, パスワード, 絵の時間(内部), 絵の時間, 絵のurl, pchのurl, 絵の幅, 絵の高さ,
		// age/sage記憶, 表示/非表示, 絵のツール, 認証マーク, そろそろ消える, nsfw, 予備2, 予備3, 予備4
		$db = new PDO(DB_PDO);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "CREATE TABLE tlog (tid integer primary key autoincrement, created TIMESTAMP, modified TIMESTAMP, thread VARCHAR(1), parent INT, comid BIGINT, tree BIGINT, a_name TEXT, mail TEXT, sub TEXT, com TEXT, a_url TEXT, host TEXT, exid TEXT, id TEXT, pwd TEXT, psec INT, utime TEXT, picfile TEXT, pchfile TEXT, img_w INT, img_h INT, age INT, invz VARCHAR(1), tool TEXT, admins VARCHAR(1), shd VARCHAR(1), ext01 TEXT, ext02 TEXT, ext03 TEXT, ext04 TEXT)";
		$db = $db->query($sql);
		$db = null; //db切断
	}
} catch (PDOException $e) {
	$init = array_merge($init, ["databaseError" => "DB接続エラー:".$e->getMessage()]);
}

if (!is_writable(realpath("./"))) {
	$init = array_merge($init, ["directoryWriteError" => "カレントディレクトリに書けません<br>\n"] );
}

$error = "";

if (!is_dir(IMG_DIR)) {
	mkdir(IMG_DIR, PERMISSION_FOR_DIR);
	chmod(IMG_DIR, PERMISSION_FOR_DIR);
}
if (!is_dir(IMG_DIR)) $error .= IMG_DIR . "がありません<br>\n";
if (!is_writable(IMG_DIR)) $error .= IMG_DIR . "を書けません<br>\n";
if (!is_readable(IMG_DIR)) $error .= IMG_DIR . "を読めません<br>\n";

if (!is_dir(TEMP_DIR)) {
	mkdir(TEMP_DIR, PERMISSION_FOR_DIR);
	chmod(TEMP_DIR, PERMISSION_FOR_DIR);
}
if (!is_dir(__DIR__ . '/session/')) {
	mkdir(__DIR__ . '/session/', PERMISSION_FOR_DIR);
	chmod(__DIR__ . '/session/', PERMISSION_FOR_DIR);
}
if (!is_dir(TEMP_DIR)) $error .= TEMP_DIR . "がありません<br>\n";
if (!is_writable(TEMP_DIR)) $error .= TEMP_DIR . "を書けません<br>\n";
if (!is_readable(TEMP_DIR)) $error .= TEMP_DIR . "を読めません<br>\n";
if ($error) {
	$init = array_merge($init, ["directoryError" => $err]);
}

$initData = json_encode($init, JSON_UNESCAPED_UNICODE);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
echo($initData);
