<?php
//--------------------------------------------------
//  おえかきけいじばん「Reita」
//  by sakots & OekakiBBS reDev.Team  https://oekakibbs.moe/
//--------------------------------------------------

//スクリプトのバージョン
define('REITA_VER', 'v0.0.0'); //lot.250618.0

//phpのバージョンが古い場合動かさせない
if (($php_ver = phpversion()) < "7.3.0") {
	die("PHP version 7.3 or higher is required for this program to work. <br>\n(Current PHP version:{$php_ver})");
}

//言語判定
$lang = ($http_langs = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
  ? explode( ',', $http_langs )[0] : '';
$en= (stripos($lang,'ja')!== 0);

//ファイルが足りない場合
if(!is_file(__DIR__.'/functions.php')){
	die(__DIR__.'/functions.php'.($en ? ' does not exist.':'がありません。'));
}
if(!is_file(__DIR__.'/config.php')){
	die(__DIR__.'/config.php'.($en ? ' does not exist.':'がありません。'));
}

//コンフィグ
require(__DIR__ . '/config.php');
//コンフィグのバージョンが古くて互換性がない場合動かさせない
if (CONF_VER < 20250611 || !defined('CONF_VER')) {
	die("コンフィグファイルに互換性がないようです。再設定をお願いします。<br>\n The configuration file is incompatible. Please reconfigure it.");
}

require_once(__DIR__.'/functions.php');
if(!isset($functions_ver) || $functions_ver < 20250610) {
	die($en ? 'Please update functions.php to the latest version.' : 'functions.phpを最新版に更新してください。');
}

check_file(__DIR__.'/misskey_note.inc.php');
require_once(__DIR__.'/misskey_note.inc.php');
if(!isset($misskey_note_ver) || $misskey_note_ver < 20250326){
	die($en ? 'Please update misskey_note.inc.php to the latest version.' : 'misskey_note.inc.phpを最新版に更新してください。');
}

//タイムゾーン設定
date_default_timezone_set(DEFAULT_TIMEZONE);


//管理パスが初期値(admin_pass)の場合は動作させない
if ($admin_pass === 'admin_pass') {
	die("管理パスが初期設定値のままです！危険なので動かせません。<br>\n The admin pass is still at its default value! This program can't run it until you fix it.");
}

$dat = array();

//CheerpJ 4.1
define('CHEERPJ_URL','https://cjrtnc.leaningtech.com/4.1/loader.js');

$dat['cheerpj_url'] = CHEERPJ_URL;

//var_dump($_POST);

//絶対パス取得
$path = realpath("./") . '/' . IMG_DIR;
$temp_path = realpath("./") . '/' . TEMP_DIR;

define('IMG_PATH', $path);
define('TMP_PATH', $temp_path);

$message = "";

$dat['path'] = IMG_DIR;

$dat['neo_dir'] = NEO_DIR;
$dat['chicken_dir'] = CHICKEN_DIR;
$dat['shi_painter_dir'] = SHI_PAINTER_DIR;

$dat['ver'] = REITA_VER;
$dat['base'] = BASE;
$dat['board_title'] = TITLE;
$dat['home'] = HOME;
$dat['message'] = $message;
$dat['paint_def_w'] = PAINT_DEF_W;
$dat['paint_def_h'] = PAINT_DEF_H;
$dat['paint_max_w'] = PAINT_MAX_W;
$dat['paint_max_h'] = PAINT_MAX_H;

$dat['max_name'] = MAX_NAME;
$dat['max_email'] = MAX_EMAIL;
$dat['max_sub'] = MAX_SUB;
$dat['max_url'] = MAX_URL;
$dat['max_com'] = MAX_COM;

$dat['switch_sns'] = SWITCH_SNS;

$dat['use_shi_painter'] = USE_SHI_PAINTER;
$dat['use_chicken'] = USE_CHICKENPAINT;

$dat['select_palettes'] = USE_SELECT_PALETTES;
$dat['pallets_dat'] = $pallets_dat;

$dat['display_id'] = DISPLAY_ID;
$dat['update_mark'] = "*"; // 更新マーク
$dat['use_re_sub'] = USE_RE_SUB;

$dat['use_anime'] = USE_ANIME;
$dat['def_anime'] = DEF_ANIME;
$dat['use_continue'] = USE_CONTINUE;
$dat['new_post_no_password'] = !CONTINUE_PASS;

$dat['use_name'] = USE_NAME;
$dat['use_com'] = USE_COM;
$dat['use_sub'] = USE_SUB;

$dat['add_info'] = $add_info;

$dat['display_paint_time'] = DISPLAY_PAINT_TIME;

$dat['share_button'] = SHARE_BUTTON;

$dat['use_hashtag'] = USE_HASHTAG;

defined('ADMIN_CAP') or define('ADMIN_CAP', '(ではない)');

$dat['sodane'] = "そうだね";

//さん
defined('A_NAME_SAN') or define('A_NAME_SAN', 'さん');
$dat['a_name_san'] = A_NAME_SAN;

//ペイント画面の$pwdの暗号化
define('CRYPT_METHOD', 'aes-128-cbc');
define('CRYPT_IV', 'T3pkYxNydN7Wz4pq'); //半角英数16文字

//日付フォーマット
defined('DATE_FORMAT') or define('DATE_FORMAT', 'Y/m/d H:i:s');

//NSFW画像機能を使う
defined('USE_NSFW') or define('USE_NSFW', 1);
$dat['use_nsfw'] = USE_NSFW;

//データベース接続PDO
define('DB_PDO', 'sqlite:' . DB_NAME . '.db');

defined("SNS_WINDOW_WIDTH") or define("SNS_WINDOW_WIDTH","600");
defined("SNS_WINDOW_HEIGHT") or define("SNS_WINDOW_HEIGHT","600");

//misskey
$dat['use_misskey_note'] = USE_MISSKEY_NOTE;

//初期設定
init();

del_temp();

$message = "";

//var_dump($_COOKIE);

$pwd_cookie = filter_input(INPUT_COOKIE, 'pwd_cookie');
$user_code = filter_input(INPUT_COOKIE, 'user_code'); //nullならuser-codeを発行

//$_SERVERから変数を取得
//var_dump($_SERVER);

$req_method = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "";
//INPUT_SERVER が動作しないサーバがあるので$_SERVERを使う。

//ユーザーip
function get_uip():	string {
	if ($user_ip = getenv("HTTP_CLIENT_IP")) {
		return $user_ip;
	} elseif ($user_ip = getenv("HTTP_X_FORWARDED_FOR")) {
		return $user_ip;
	} elseif ($user_ip = getenv("REMOTE_ADDR")) {
		return $user_ip;
	} else {
		return $user_ip;
	}
}

$https_only = (bool)($_SERVER['HTTPS'] ?? '');
//user-codeの発行
$user_code = t(filter_input_data('COOKIE', 'user_code')); //user-codeを取得

session_sta();
$session_user_code = $_SESSION['user_code'] ?? "";
$session_user_code = t($session_user_code);

$user_code = $user_code ? $user_code : $session_user_code;
if(!$user_code){ //user-codeがなければ発行
	$user_ip = get_uip();
	$user_code = hash('sha256', $user_ip.random_bytes(16));
}
setcookie("user_code", $user_code, time()+(86400*365),"","",$https_only,true); //1年間
$_SESSION['user_code'] = $user_code;

//var_dump($_GET);

/*-----------mode-------------*/

$mode = (string)filter_input_data('POST','mode');
$mode = $mode ?: (string)filter_input_data('GET','mode');

// モード → 実行関数 のマップ
$modeMap = [
	'regist' => 'regist',
	'reply' => 'reply',
	'res' => 'res',
	'sodane' => 'sodane',
	'paint' => fn() => paint_form(""),
	'pic_com' => fn() => paint_com(""),
	'pic_tmp' => fn() => paint_com("tmp"),
	'anime' => fn() => open_pch($sp ?? ""),
	'continue' => 'in_continue',
	'cont_paint' => function() {
		$type = filter_input(INPUT_POST, 'type');
		if (CONTINUE_PASS || $type === 'rep') usr_chk();
		return paint_form($type);
	},
	'pic_rep' => 'pic_replace',
	'catalog' => 'catalog',
	'search' => 'search',
	'edit' => 'edit_form',
	'edit_exec' => 'edit_exec',
	'del' => 'del_mode',
	'admin_in' => 'admin_in',
	'admin' => 'admin_in',
	'set_share_server' => 'set_share_server',
	'post_share_server' => 'post_share_server',
	'before_misskey_note' => [misskey_note::class, 'before_misskey_note'],
	'misskey_note_edit_form' => [misskey_note::class, 'misskey_note_edit_form'],
	'create_misskey_note_session_data' => [misskey_note::class, 'create_misskey_note_session_data'],
	'create_misskey_auth_request_url' => [misskey_note::class, 'create_misskey_auth_request_url'],
	'misskey_success' => [misskey_note::class, 'misskey_success'],
];

// 実行
if (isset($modeMap[$mode])) {
	$handler = $modeMap[$mode];
	if (is_callable($handler)) {
		return $handler();
	} elseif (is_string($handler) && function_exists($handler)) {
		return $handler();
	}
}

// デフォルト
return def();
exit;

/*-----------Main-------------*/

function init(): void {
	// セキュリティヘッダーの設定
	header('X-Content-Type-Options: nosniff');
	header('X-Frame-Options: DENY');
	header('X-XSS-Protection: 1; mode=block');
	header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cjrtnc.leaningtech.com; style-src \'self\' \'unsafe-inline\' https://cjrtnc.leaningtech.com; img-src \'self\' data: blob:; media-src \'self\' blob:; connect-src \'self\' https://cjrtnc.leaningtech.com; worker-src \'self\' blob: https://cjrtnc.leaningtech.com; frame-src \'self\' https://cjrtnc.leaningtech.com;');
	header('Referrer-Policy: strict-origin-when-cross-origin');
	header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
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
		echo "DB接続エラー:" . $e->getMessage();
	}
	$err = '';
	if (!is_writable(realpath("./"))) error("カレントディレクトリに書けません<br>");
	if (!is_dir(IMG_DIR)) {
		mkdir(IMG_DIR, PERMISSION_FOR_DIR);
		chmod(IMG_DIR, PERMISSION_FOR_DIR);
	}
	if (!is_dir(IMG_DIR)) $err .= IMG_DIR . "がありません<br>";
	if (!is_writable(IMG_DIR)) $err .= IMG_DIR . "を書けません<br>";
	if (!is_readable(IMG_DIR)) $err .= IMG_DIR . "を読めません<br>";

	if (!is_dir(TEMP_DIR)) {
		mkdir(TEMP_DIR, PERMISSION_FOR_DIR);
		chmod(TEMP_DIR, PERMISSION_FOR_DIR);
	}
	if (!is_dir(__DIR__ . '/session/')) {
		mkdir(__DIR__ . '/session/', PERMISSION_FOR_DIR);
		chmod(__DIR__ . '/session/', PERMISSION_FOR_DIR);
	}
	if (!is_dir(TEMP_DIR)) $err .= TEMP_DIR . "がありません<br>";
	if (!is_writable(TEMP_DIR)) $err .= TEMP_DIR . "を書けません<br>";
	if (!is_readable(TEMP_DIR)) $err .= TEMP_DIR . "を読めません<br>";
	if ($err) error($err);
	if (is_file(DB_NAME . '.db')) {
		// データベースファイルのパーミッションを明示的に設定
		chmod(DB_NAME . '.db', 0600);
	}
}


//投稿があればデータベースへ保存する
/* 記事書き込み スレ立て */
function regist(): void {
	global $bad_ip, $admin_pass, $admin_name;
	global $req_method;
	global $dat, $en;

	//CSRFトークンをチェック
	if (CHECK_CSRF_TOKEN) {
		check_csrf_token();
	}

	$sub = (string)filter_input(INPUT_POST, 'sub');
	$name = (string)filter_input(INPUT_POST, 'name');
	$mail = (string)filter_input(INPUT_POST, 'mail');
	$url = (string)filter_input(INPUT_POST, 'url');
	$com = (string)filter_input(INPUT_POST, 'com');
	$pic_file = filter_input(INPUT_POST, 'pic_file');
	$invz = trim(filter_input(INPUT_POST, 'invz'));
	$img_w = trim(filter_input(INPUT_POST, 'img_w', FILTER_VALIDATE_INT));
	$img_h = trim(filter_input(INPUT_POST, 'img_h', FILTER_VALIDATE_INT));
	$pwd = (string)trim(filter_input(INPUT_POST, 'pwd'));
	$pwd_hash = password_hash($pwd, PASSWORD_DEFAULT);
	$exid = trim(filter_input(INPUT_POST, 'exid', FILTER_VALIDATE_INT));
	$palettes = filter_input(INPUT_POST, 'palettes');
	$nsfw_flag = (string)filter_input(INPUT_POST, 'nsfw', FILTER_VALIDATE_INT);

	if ($req_method !== "POST") {
		error($en ? "The contribution excluding 'POST' is not accepted. Please do not do an illegal contribution." : "不正な投稿です。POST以外での投稿は受け付けません");
	}

	//NGワードがあれば拒絶
	Reject_if_NGword_exists_in_the_post($com, $name, $mail, $url, $sub);
	if (USE_NAME && !$name) {
		error($en ? "The name is required. Please enter your name." : "名前が必須です。名前を入力してください。");
	}
	//レスの時は本文必須
	//if(filter_input(INPUT_POST, 'modid') && !$com) {error(MSG008);}
	if (USE_COM && !$com) {
		error($en ? "The comment is required. Please enter your comment." : "コメントが必須です。コメントを入力してください。");
	}
	if (USE_SUB && !$sub) {
		error($en ? "The subject is required. Please enter your subject." : "タイトルが必須です。タイトルを入力してください。");
	}

	if (strlen($com) > MAX_COM) {
		error($en ? "The comment is too long. Please enter a shorter comment." : "コメントが長すぎます。より短いコメントを入力してください。");
	}
	if (strlen($name) > MAX_NAME) {
		error($en ? "The name is too long. Please enter a shorter name." : "名前が長すぎます。より短い名前を入力してください。");
	}
	if (strlen($mail) > MAX_EMAIL) {
		error($en ? "The email is too long. Please enter a shorter email." : "メールアドレスが長すぎます。より短いメールアドレスを入力してください。");
	}
	if (strlen($sub) > MAX_SUB) {
		error($en ? "The subject is too long. Please enter a shorter subject." : "タイトルが長すぎます。より短いタイトルを入力してください。");
	}
	if (strlen($url) > MAX_URL) {
		error($en ? "The URL is too long. Please enter a shorter URL." : "URLが長すぎます。より短いURLを入力してください。");
	}

	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach ($bad_ip as $value) { //拒絶host
		if (preg_match("/$value$/i", $host)) {
			error($en ? "The post was rejected. Post from the 'HOST' is not accepted." : "投稿が拒絶されました。そのHOSTからの投稿は受け付けません。");
		}
	}
	//セキュリティ関連ここまで

	try {
		$db = new PDO(DB_PDO);
		if (isset($_POST["send"])) {

			$strlen_com = strlen($com);

			if ($name   === "") $name = DEF_NAME;
			if ($com  === "") $com  = DEF_COM;
			if ($sub  === "") $sub  = DEF_SUB;

			// 二重投稿チェック
			//最新コメント取得
			$sql_w = "SELECT * FROM tlog WHERE thread=1 ORDER BY tid DESC LIMIT 1";
			$msg_w = $db->prepare($sql_w);
			$msg_w->execute();
			$msg_wc = $msg_w->fetch();
			if (!empty($msg_wc)) {
				$msg_sub = $msg_wc["sub"]; //最新タイトル
				$msg_w_com = $msg_wc["com"]; //最新コメント取得できた
				$msg_w_host = $msg_wc["host"]; //最新ホスト取得できた
				//どれも一致すれば二重投稿だと思う
				if ($strlen_com > 0 && $com == $msg_w_com && $host == $msg_w_host && $sub == $msg_sub) {
					$msg_w = null;
					$db = null; //db切断
					error($en ? "The post was rejected. The post is a duplicate." : "二重投稿ですか？");
				}
				//画像番号が一致の場合(投稿してブラウザバック、また投稿とか)
				//二重投稿と判別(画像がない場合は処理しない)
				if (!empty($_POST["mod_id"])) {
					if ($msg_wc["picfile"] !== "" && $pic_file == $msg_wc["picfile"]) {
						$db = null; //db切断
						error($en ? "The post was rejected. The post is a duplicate." : "二重投稿ですか？");
					}
				}
			}
			//↑ 二重投稿チェックおわり

			//画像ファイルとか処理
			if ($pic_file) {
				$path_filename = pathinfo($pic_file, PATHINFO_FILENAME);
				$temp_file = TEMP_DIR . $pic_file;

				// アップロードファイルの検証を追加
				if (!validate_upload_file($temp_file)) {
					error($en ? "The file is invalid. Please upload a valid file." : "無効なファイルです。");
				}

				// ファイルの移動とパーミッション設定
				if (!rename($temp_file, IMG_DIR . $pic_file)) {
					error($en ? "The file could not be saved. Please try again." : "ファイルの保存に失敗しました。");
				}
				chmod(IMG_DIR . $pic_file, PERMISSION_FOR_DEST);

				// 既存の処理を保持
				$fp = fopen(TEMP_DIR . $path_filename . ".dat", "r");
				$userdata = fread($fp, 1024);
				fclose($fp);
				list($uip, $u_host,,, $u_code,, $start_time, $posted_time, $u_res_to, $tool) = explode("\t", rtrim($userdata) . "\t");

				// ctypeを取得して画像から続きを描いたかどうかを判定
				$ctype = filter_input(INPUT_POST, 'ctype');
				
				// user_codeからctypeを取得（POSTデータにない場合）
				if ($ctype === null) {
					$user_code = filter_input(INPUT_POST, 'user_code');
					if ($user_code) {
						parse_str($user_code, $user_code_params);
						if (isset($user_code_params['ctype'])) {
							$ctype = $user_code_params['ctype'];
						}
					}
				}
				
				// send_headerパラメータからuser_codeを取得（POSTデータにない場合）
				if ($ctype === null) {
					$send_header = filter_input(INPUT_POST, 'send_header');
					if ($send_header) {
						parse_str($send_header, $header_params);
						if (isset($header_params['user_code'])) {
							$user_code = $header_params['user_code'];
							parse_str($user_code, $user_code_params);
							if (isset($user_code_params['ctype'])) {
								$ctype = $user_code_params['ctype'];
							}
						}
					}
				}
				
				// HTTPヘッダーからuser_codeを取得（POSTデータにない場合）
				if ($ctype === null) {
					$http_user_code = filter_input(INPUT_SERVER, 'HTTP_X_USERCODE');
					if ($http_user_code) {
						parse_str($http_user_code, $user_code_params);
						if (isset($user_code_params['ctype'])) {
							$ctype = $user_code_params['ctype'];
						}
					}
				}
				
				// セッション変数からuser_codeを取得（POSTデータにない場合）
				if ($ctype === null) {
					if (session_status() !== PHP_SESSION_ACTIVE) {
						session_start();
					}
					if (isset($_SESSION['user_code'])) {
						$user_code = $_SESSION['user_code'];
						parse_str($user_code, $user_code_params);
						if (isset($user_code_params['ctype'])) {
							$ctype = $user_code_params['ctype'];
						}
					}
				}
				
				// ctypeがnullの場合は新規投稿として扱う（動画ファイルを処理する）
				if ($ctype === null) {
					$ctype = 'new';
				}
				
				// 描画時間の計算
				if ($start_time && DISPLAY_PAINT_TIME) {
					$paint_sec = $posted_time - $start_time; //内部保存用
					$utime = calcPtime($paint_sec);
				}

				// ツールの判定
				if ($tool === 'neo') {
					$used_tool = 'PaintBBS NEO';
				} elseif ($tool === 'shi') {
					$used_tool = 'Shi Painter';
				} elseif ($tool === 'chicken') {
					$used_tool = 'Chicken Paint';
				} else {
					$used_tool = '???';
				}

				// 画像サイズの取得
				list($img_w, $img_h) = getimagesize(IMG_DIR . $pic_file);

				$pic_dat = $path_filename . '.dat';

				$chi_file = $path_filename . '.chi';
				$spch_file = $path_filename . '.spch';
				$pch_file = $path_filename . '.pch';

				// 画像から続きを描いた場合のみ動画ファイルを処理しない
				if ($ctype === 'img') {
					$pch_file = "";
				} else {
					// 新規投稿または動画から続きを描く場合は動画ファイルを処理
					if (is_file(TEMP_DIR . $pch_file)) {
						$success = rename(TEMP_DIR . $pch_file, IMG_DIR . $pch_file);
						if ($success) {
							chmod(IMG_DIR . $pch_file, PERMISSION_FOR_DEST);
						} else {
							$pch_file = "";
						}
					} elseif (is_file(TEMP_DIR . $spch_file)) {
						$success = rename(TEMP_DIR . $spch_file, IMG_DIR . $spch_file);
						if ($success) {
							chmod(IMG_DIR . $spch_file, PERMISSION_FOR_DEST);
							$pch_file = $spch_file;
						} else {
							$pch_file = "";
						}
					} elseif (is_file(TEMP_DIR . $chi_file)) {
						$success = rename(TEMP_DIR . $chi_file, IMG_DIR . $chi_file);
						if ($success) {
							chmod(IMG_DIR . $chi_file, PERMISSION_FOR_DEST);
							$pch_file = $chi_file;
						} else {
							$pch_file = "";
						}
					} else {
						$pch_file = "";
					}
				}
				error_log("regist関数 - 最終的なpch_file: " . $pch_file);
				chmod(TEMP_DIR . $pic_dat, PERMISSION_FOR_DEST);
				unlink(TEMP_DIR . $pic_dat);

				//nsfw
				if (USE_NSFW == 1 && $nsfw_flag == 1) {
					$nsfw = true;
				} else {
					$nsfw = false;
				}
			} else {
				$img_w = 0;
				$img_h = 0;
				$pch_file = "";
				$utime = "";
				$used_tool = "";
			}

			// 値を追加する

			//不要改行圧縮
			$com = preg_replace("/(\n|\r|\r\n){3,}/us", "\n\n", $com);

			//id生成
			$id = gen_id($host, $utime);

			//管理者名は管理パスじゃないと使えない
			if ($name === $admin_name && $pwd !== $admin_pass) {
				$name = $name . ADMIN_CAP;
			}

			//管理者名の投稿でパスワードが管理パスなら管理者バッジつける
			$admins = ($pwd === $admin_pass && $name === $admin_name) ? 1 : 0;

			// 'のエスケープ(入りうるところがありそうなとこだけにしといた)
			$name = str_replace("'", "''", $name);
			$sub = str_replace("'", "''", $sub);
			$com = str_replace("'", "''", $com);
			$mail = str_replace("'", "''", $mail);
			$url = str_replace("'", "''", $url);
			$host = str_replace("'", "''", $host);
			$id = str_replace("'", "''", $id);

			//age値取得
			$sql_age = "SELECT MAX(age) FROM tlog";
			$age = $db->exec("$sql_age");
			$tree = time() * 100000000;

			//スレ建て
			$thread = 1;
			$shd = 0;
			$age = 0;
			$parent = NULL;
			$sql = "INSERT INTO tlog (created, modified, thread, parent, comid, tree, a_name, sub, com, mail, a_url, picfile, pchfile, img_w, img_h, psec, utime, pwd, id, exid, age, invz, host, tool, admins, shd, ext01, ext02) VALUES (datetime('now', 'localtime'), datetime('now', 'localtime'), :thread, :parent, :tree, :tree, :a_name, :sub, :com, :mail, :a_url, :picfile, :pchfile, :img_w, :img_h, :psec, :utime, :pwdh, :id, :exid, :age, :invz, :host, :used_tool, :admins, :shd, :nsfw, :ctype)";

			$stmt = $db->prepare($sql);
			$stmt->execute(
				[
					'thread'=>$thread, 'parent'=>$parent, 'tree'=>$tree, 'a_name'=>$name,'sub'=>$sub,'com'=>$com,'mail'=>$mail,'a_url'=>$url,'picfile'=> $picfile,'pchfile'=> $pchfile, 'img_w'=>$img_w,'img_h'=> $img_h, 'psec'=>$psec,'utime'=> $utime,'pwdh'=> $pwdh,'id'=> $id,'exid'=> $exid,'age'=> $age,'invz'=> $invz,'host'=> $host,'used_tool'=> $used_tool,'admins'=> $admins,'shd'=> $shd,'nsfw'=> $nsfw,'ctype'=> $ctype,
				]
			);
			//$db->exec($sql);

			$c_pass = $pwd;
			//-- クッキー保存 --
			//クッキー項目："クッキー名 クッキー値"
			$cookies = [["name_c",$name],["email_c",$mail] , ["url_c", $url], ["pwd_c", $c_pass] ,[ "palette_c" , $palettes]];
			foreach ($cookies as $cookie) {
				list($c_name, $c_cookie) = $cookie;
				$c_name = (string)$c_name;
				$c_cookie = (string)$c_cookie;
				setcookie($c_name, $c_cookie, time() + (SAVE_COOKIE * 24 * 3600));
			}

			$dat['message'] = '書き込みに成功しました。';
			$msg_w = null;
			$db = null; //db切断
		}
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	unset($name, $mail, $sub, $com, $url, $pwd, $pwd_hash, $res_to, $pic_tmp, $pic_file, $mode);
	//header('Location:'.PHP_SELF);
	//ログ行数オーバー処理
	//スレ数カウント
	try {
		$db = new PDO(DB_PDO);
		$sql_th = "SELECT SUM(thread) as cnt FROM tlog";
		$th_cnt_sql = $db->query("$sql_th");
		$th_cnt_sql = $th_cnt_sql->fetch();
		$th_cnt = $th_cnt_sql["cnt"];
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	if ($th_cnt > LOG_MAX_T) {
		log_del();
	}

	//そろそろ消えるスレッドのフラグを設定
	$th_id = (int)round(LOG_MAX_T * LOG_LIMIT / 100); //閾値 … 新しい方からこの件数以降がもうすぐ消える
	if ($th_cnt > $th_id) {
		// そろそろ消えるスレッドにshdフラグを設定
		try {
			$db = new PDO(DB_PDO);
			// 古いスレッドから順番にshdフラグを設定
			$sql = "UPDATE tlog SET shd = '1' WHERE thread = 1 AND shd = '0' ORDER BY tid ASC LIMIT ?";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(1, $th_cnt - $th_id, PDO::PARAM_INT);
			$stmt->execute();
			$db = null; //db切断
		} catch (PDOException $e) {
			echo "DB接続エラー:" . $e->getMessage();
		}
	}

	// そろそろ消えるスレッドの情報をテンプレートに渡す
	$dat['log_limit'] = LOG_LIMIT;
	$dat['log_max_t'] = LOG_MAX_T;
	$dat['th_cnt'] = $th_cnt;
	$dat['th_id'] = $th_id;
	$dat['will_delete_count'] = max(0, $th_cnt - $th_id);

	ok('書き込みに成功しました。画面を切り替えます。');
}

//記事書き込み - リプライ
function reply(): void {
	global $bad_ip, $admin_pass, $admin_name, $en;
	global $req_method;
	global $dat;

	//CSRFトークンをチェック
	if (CHECK_CSRF_TOKEN) {
		check_csrf_token();
	}

	$sub = (string)filter_input(INPUT_POST, 'sub');
	$name = (string)filter_input(INPUT_POST, 'name');
	$mail = (string)filter_input(INPUT_POST, 'mail');
	$url = (string)filter_input(INPUT_POST, 'url');
	$com = (string)filter_input(INPUT_POST, 'com');
	$parent = trim(filter_input(INPUT_POST, 'parent', FILTER_VALIDATE_INT));
	$invz = trim(filter_input(INPUT_POST, 'invz', FILTER_VALIDATE_INT));
	$pwd = trim(filter_input(INPUT_POST, 'pwd'));
	$pwd_hash = password_hash($pwd, PASSWORD_DEFAULT);
	$exid = trim(filter_input(INPUT_POST, 'exid', FILTER_VALIDATE_INT));
	$palettes = filter_input(INPUT_POST, 'palettes');
	$pic_file = filter_input(INPUT_POST, 'pic_file');
	$img_w = trim(filter_input(INPUT_POST, 'img_w', FILTER_VALIDATE_INT));
	$img_h = trim(filter_input(INPUT_POST, 'img_h', FILTER_VALIDATE_INT));

	if ($req_method !== "POST") {
		error($en ? "The contribution excluding 'POST' is not accepted. Please do not do an illegal contribution." : "不正な投稿です。POST以外での投稿は受け付けません");
	}

	//NGワードがあれば拒絶
	Reject_if_NGword_exists_in_the_post($com, $name, $mail, $url, $sub);
	if (USE_NAME && !$name) {
		error($en ? "The name is required. Please enter your name." : "名前が必須です。名前を入力してください。");
	}
	//レスの時は本文必須
	if (!$com) {
		error($en ? "The comment is required. Please enter your comment." : "コメントが必須です。コメントを入力してください。");
	}
	if (USE_SUB && !$sub) {
		error($en ? "The subject is required. Please enter your subject." : "タイトルが必須です。タイトルを入力してください。");
	}

	if (strlen($com) > MAX_COM) {
		error($en ? "The comment is too long. Please enter a shorter comment." : "コメントが長すぎます。より短いコメントを入力してください。");
	}
	if (strlen($name) > MAX_NAME) {
		error($en ? "The name is too long. Please enter a shorter name." : "名前が長すぎます。より短い名前を入力してください。");
	}
	if (strlen($mail) > MAX_EMAIL) {
		error($en ? "The email is too long. Please enter a shorter email." : "メールアドレスが長すぎます。より短いメールアドレスを入力してください。");
	}
	if (strlen($sub) > MAX_SUB) {
		error($en ? "The subject is too long. Please enter a shorter subject." : "タイトルが長すぎます。より短いタイトルを入力してください。");
	}
	if (strlen($url) > MAX_URL) {
		error($en ? "The URL is too long. Please enter a shorter URL." : "URLが長すぎます。より短いURLを入力してください。");
	}

	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach ($bad_ip as $value) { //拒絶host
		if (preg_match("/$value$/i", $host)) {
			error($en ? "The post was rejected. Post from the 'HOST' is not accepted." : "投稿が拒絶されました。そのHOSTからの投稿は受け付けません。");
		}
	}
	//セキュリティ関連ここまで

	try {
		$db = new PDO(DB_PDO);
		if (isset($_POST["send"])) {

			$strlen_com = strlen($com);

			if ($name  === "") $name = DEF_NAME;
			if ($com  === "") $com  = DEF_COM;
			if ($sub  === "") $sub  = DEF_SUB;

			// 二重投稿チェック
			//最新コメント取得
			$sql_w = "SELECT * FROM tlog WHERE thread=0 ORDER BY tid DESC LIMIT 1";
			$msg_w = $db->prepare($sql_w);
			$msg_w->execute();
			$msg_wc = $msg_w->fetch() ?: [];
			if (!empty($msg_wc)) {
				$msg_w_sub = $msg_wc["sub"]; //最新タイトル
				$msg_w_com = $msg_wc["com"]; //最新コメント取得できた
				$msg_w_host = $msg_wc["host"]; //最新ホスト取得できた
				//どれも一致すれば二重投稿だと思う
				if ($strlen_com > 0 && $com == $msg_w_com && $host == $msg_w_host && $sub == $msg_w_sub) {
					$msg_w = null;
					$db = null; //db切断
					error($en ? "The post was rejected. The post is a duplicate." : "二重投稿ですか？");
				}
			} else {
				//最初のレスのage処理対策
				$msg_wc["tid"] = 0;
				$msg_wc["age"] = 0;
				$msg_wc["tree"] = 0;
			}
			//↑ 二重投稿チェックおわり

			//画像ファイルとか処理
			if ($pic_file) {
				$path_filename = pathinfo($pic_file, PATHINFO_FILENAME);
				$temp_file = TEMP_DIR . $pic_file;

				// アップロードファイルの検証を追加
				if (!validate_upload_file($temp_file)) {
					error($en ? "The file is invalid. Please upload a valid file." : "無効なファイルです。");
				}

				// ファイルの移動とパーミッション設定
				if (!rename($temp_file, IMG_DIR . $pic_file)) {
					error($en ? "The file could not be saved. Please try again." : "ファイルの保存に失敗しました。");
				}
				chmod(IMG_DIR . $pic_file, PERMISSION_FOR_DEST);

				// 既存の処理を保持
				$fp = fopen(TEMP_DIR . $path_filename . ".dat", "r");
				$userdata = fread($fp, 1024);
				fclose($fp);
				list($uip, $u_host,,, $u_code,, $start_time, $posted_time, $u_res_to, $tool) = explode("\t", rtrim($userdata) . "\t");

				// ctypeを取得して画像から続きを描いたかどうかを判定
				$ctype = filter_input(INPUT_POST, 'ctype');
				
				// user_codeからctypeを取得（POSTデータにない場合）
				if ($ctype === null) {
					$user_code = filter_input(INPUT_POST, 'user_code');
					if ($user_code) {
						parse_str($user_code, $user_code_params);
						if (isset($user_code_params['ctype'])) {
							$ctype = $user_code_params['ctype'];
						}
					}
				}
				
				// send_headerパラメータからuser_codeを取得（POSTデータにない場合）
				if ($ctype === null) {
					$send_header = filter_input(INPUT_POST, 'send_header');
					if ($send_header) {
						parse_str($send_header, $header_params);
						if (isset($header_params['user_code'])) {
							$user_code = $header_params['user_code'];
							parse_str($user_code, $user_code_params);
							if (isset($user_code_params['ctype'])) {
								$ctype = $user_code_params['ctype'];
							}
						}
					}
				}
				
				// HTTPヘッダーからuser_codeを取得（POSTデータにない場合）
				if ($ctype === null) {
					$http_user_code = filter_input(INPUT_SERVER, 'HTTP_X_USERCODE');
					if ($http_user_code) {
						parse_str($http_user_code, $user_code_params);
						if (isset($user_code_params['ctype'])) {
							$ctype = $user_code_params['ctype'];
						}
					}
				}
				
				// セッション変数からuser_codeを取得（POSTデータにない場合）
				if ($ctype === null) {
					if (session_status() !== PHP_SESSION_ACTIVE) {
						session_start();
					}
					if (isset($_SESSION['user_code'])) {
						$user_code = $_SESSION['user_code'];
						parse_str($user_code, $user_code_params);
						if (isset($user_code_params['ctype'])) {
							$ctype = $user_code_params['ctype'];
						}
					}
				}
				
				// ctypeがnullの場合は新規投稿として扱う（動画ファイルを処理する）
				if ($ctype === null) {
					$ctype = 'new';
				}
				
				// 描画時間の計算
				if ($start_time && DISPLAY_PAINT_TIME) {
					$paint_sec = $posted_time - $start_time; //内部保存用
					$utime = calcPtime($paint_sec);
				}

				// ツールの判定
				if ($tool === 'neo') {
					$used_tool = 'PaintBBS NEO';
				} elseif ($tool === 'shi') {
					$used_tool = 'Shi Painter';
				} elseif ($tool === 'chicken') {
					$used_tool = 'Chicken Paint';
				} else {
					$used_tool = '???';
				}

				// 画像サイズの取得
				list($img_w, $img_h) = getimagesize(IMG_DIR . $pic_file);

				$pic_dat = $path_filename . '.dat';

				$chi_file = $path_filename . '.chi';
				$spch_file = $path_filename . '.spch';
				$pch_file = $path_filename . '.pch';

				// 画像から続きを描いた場合のみ動画ファイルを処理しない
				if ($ctype === 'img') {
					$pch_file = "";
				} else {
					// 新規投稿または動画から続きを描く場合は動画ファイルを処理
					if (is_file(TEMP_DIR . $pch_file)) {
						$success = rename(TEMP_DIR . $pch_file, IMG_DIR . $pch_file);
						if ($success) {
							chmod(IMG_DIR . $pch_file, PERMISSION_FOR_DEST);
						} else {
							$pch_file = "";
						}
					} elseif (is_file(TEMP_DIR . $spch_file)) {
						$success = rename(TEMP_DIR . $spch_file, IMG_DIR . $spch_file);
						if ($success) {
							chmod(IMG_DIR . $spch_file, PERMISSION_FOR_DEST);
							$pch_file = $spch_file;
						} else {
							$pch_file = "";
						}
					} elseif (is_file(TEMP_DIR . $chi_file)) {
						$success = rename(TEMP_DIR . $chi_file, IMG_DIR . $chi_file);
						if ($success) {
							chmod(IMG_DIR . $chi_file, PERMISSION_FOR_DEST);
							$pch_file = $chi_file;
						} else {
							$pch_file = "";
						}
					} else {
						$pch_file = "";
					}
				}
				error_log("reply関数 - 最終的なpch_file: " . $pch_file);
				chmod(TEMP_DIR . $pic_dat, PERMISSION_FOR_DEST);
				unlink(TEMP_DIR . $pic_dat);
			} else {
				$img_w = 0;
				$img_h = 0;
				$pch_file = "";
				$utime = "";
				$used_tool = "";
				$ctype = null;
			}

			// 値を追加する

			//不要改行圧縮
			$com = preg_replace("/(\n|\r|\r\n){3,}/us", "\n\n", $com);

			//id生成
			$id = gen_id($host, $utime ?? time());

			//管理者名は管理パスじゃないと使えない
			if ($name === $admin_name && $pwd !== $admin_pass) {
				$name = $name . ADMIN_CAP;
			}
			//管理者名の投稿でパスワードが管理パスなら管理者バッジつける
			$admins = ($pwd === $admin_pass && $name === $admin_name) ? 1 : 0;

			// 'のエスケープ(入りうるところがありそうなとこだけにしといた)
			$name = str_replace("'", "''", $name);
			$sub = str_replace("'", "''", $sub);
			$com = str_replace("'", "''", $com);
			$mail = str_replace("'", "''", $mail);
			$url = str_replace("'", "''", $url);
			$host = str_replace("'", "''", $host);
			$id = str_replace("'", "''", $id);

			//レスの位置
			$tree = time() - $parent - (int)$msg_wc["tid"];
			$com_id = $tree + time();

			//メール欄にsageが含まれるならageない
			$age = (int)$msg_wc["age"];
			if (strpos($mail, 'sage') !== false) {
				//sage
				$age = $age;
			} else {
				//age
				$age++;
				$age_tree = $age + (time() * 100000000);
				$sql_age = "UPDATE tlog SET age = $age, tree = $age_tree WHERE tid = $parent";
				$db->exec($sql_age);
			}

			//リプ処理
			$thread = 0;
			$sql = "INSERT INTO tlog (created, modified, thread, parent, comid, tree, a_name, sub, com, mail, a_url, picfile, pchfile, img_w, img_h, psec, utime, pwd, id, exid, age, invz, host, tool, admins, ext02) VALUES (datetime('now', 'localtime'), datetime('now', 'localtime'), :thread, :parent, :comid, :tree, :a_name, :sub, :com, :mail, :a_url, :picfile, :pchfile, :img_w, :img_h, :psec, :utime, :pwdh, :id, :exid, :age, :invz, :host, :used_tool, :admins, :ctype)";

			// プレースホルダ
			$stmt = $db->prepare($sql);
			$stmt->execute(
				[
					'thread'=>$thread, 'parent'=>$parent, 'comid'=>$com_id,'tree'=>$tree, 'a_name'=>$name,'sub'=>$sub,'com'=>$com,'mail'=>$mail,'a_url'=>$url,'picfile'=> $pic_file,'pchfile'=> $pch_file, 'img_w'=>$img_w,'img_h'=> $img_h, 'psec'=>$paint_sec,'utime'=> $utime,'pwdh'=> $pwd_hash,'id'=> $id,'exid'=> $exid,'age'=> $age,'invz'=> $invz,'host'=> $host,'used_tool'=> $used_tool,'admins'=> $admins,'ctype'=> $ctype,
				]
			);
			//$db->exec($sql);

			$c_pass = $pwd;
			//-- クッキー保存 --
			//クッキー項目："クッキー名 クッキー値"
			$cookies = [["name_c",$name],["email_c",$mail] , ["url_c", $url], ["pwd_c", $c_pass]];
			foreach ($cookies as $cookie) {
				list($c_name, $c_cookie) = $cookie;
				$c_name = (string)$c_name;
				$c_cookie = (string)$c_cookie;
				setcookie($c_name, $c_cookie, time() + (SAVE_COOKIE * 24 * 3600));
			}

			$dat['message'] = $en ? "The contribution was successful. The screen will be switched." : "書き込みに成功しました。画面を切り替えます。";
			$msg_w = null;
			$db = null; //db切断
		}
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	unset($name, $mail, $sub, $com, $url, $pwd, $pwd_hash, $resto, $pictmp, $pic_file, $mode);
	//header('Location:'.PHP_SELF);
	ok($en ? "The contribution was successful. The screen will be switched." : "書き込みに成功しました。画面を切り替えます。");
}

//通常表示モード
function def(): void {
	global $dat, $blade;
	$dsp_res = DSP_RES;
	$page_def = PAGE_DEF;

	//ログ行数オーバー処理
	//スレ数カウント
	try {
		$db = new PDO(DB_PDO);
		$sql_th = "SELECT SUM(thread) as cnt FROM tlog";
		$th_cnt_sql = $db->query("$sql_th");
		$th_cnt_sql = $th_cnt_sql->fetch();
		$th_cnt = $th_cnt_sql["cnt"];
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	if ($th_cnt > LOG_MAX_T) {
		log_del();
	}

	//古いスレのレスボタンを表示しない
	$elapsed_time = ELAPSED_DAYS * 86400; //デフォルトの1年だと31536000
	$now_time = time(); //いまのunixタイムスタンプを取得
	//あとはテーマ側で計算する
	$dat['now_time'] = $now_time;
	$dat['elapsed_time'] = $elapsed_time;

	//ページング
	try {
		$db = new PDO(DB_PDO);
		$sql_cnt = "SELECT SUM(thread) as cnt FROM tlog WHERE invz=0";
		$th_cnt_sql = $db->query("$sql_cnt");
		$th_cnt_sql = $th_cnt_sql->fetch();
		$count = $th_cnt_sql["cnt"];
		if (isset($_GET['page']) && is_numeric($_GET['page'])) {
			$page = $_GET['page'];
			$page = max($page, 1);
		} else {
			$page = 1;
		}
		$start = $page_def * ($page - 1);

		//最大何ページあるのか
		$max_page = floor($count / $page_def) + 1;
		//最後にスレ数0のページができたら表示しない処理
		if (($count % $page_def) == 0) {
			$max_page = $max_page - 1;
			//ただしそれが1ページ目なら困るから表示
			$max_page = max($max_page, 1);
		}
		$dat['max_page'] = $max_page;

		//リンク作成用
		$dat['now_page'] = $page;
		$p = 1;
		$pp = array();
		$paging = array();
		while ($p <= $max_page) {
			$paging[($p)] = compact('p');
			$pp[] = $paging;
			$p++;
		}
		$dat['paging'] = $paging;
		$dat['pp'] = $pp;

		$dat['back'] = ($page - 1);
		$dat['next'] = ($page + 1);

		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}

	//読み込み
	try {
		$db = new PDO(DB_PDO);
		//1ページの全スレッド取得
		$sql = "SELECT * FROM tlog WHERE invz=0 AND thread=1 ORDER BY tree DESC LIMIT ?, ?";
		$posts = $db->prepare($sql);
		$posts->bindValue(1, $start, PDO::PARAM_INT);
		$posts->bindValue(2, $page_def, PDO::PARAM_INT);
		$posts->execute();

		$i = 0;
		$j = 0;
		while ($i < PAGE_DEF) {
			$bbs_line = $posts->fetch();
			if (empty($bbs_line)) {
				break;
			} //スレがなくなったら抜ける
			$oya_id = $bbs_line["tid"]; //スレのtid(親番号)を取得
			$sql_i = "SELECT * FROM tlog WHERE parent = $oya_id AND invz=0 AND thread=0 ORDER BY comid ASC";
			//レス取得
			$post_i = $db->query($sql_i);
			$j = 0;
			$flag = true;
			while ($flag == true) {
				$_pch_ext = pathinfo($bbs_line['pchfile'], PATHINFO_EXTENSION);
				if ($_pch_ext === 'chi') {
					$bbs_line['pchfile'] = ''; //ChickenPaintは動画リンクを出さない
				}
				// 拡張子がない場合やext02がimgの場合は動画リンクを出さない
				if ($_pch_ext === '' || $bbs_line['pchfile'] === '' || (isset($bbs_line['ext02']) && $bbs_line['ext02'] === 'img')) {
					$bbs_line['pchfile'] = '';
				}
				$res = $post_i->fetch();
				if (empty($res)) { //レスがなくなったら
					$bbs_line['ressu'] = $j; //スレのレス数
					$bbs_line['res_d_su'] = $j - DISPLAY_RES; //スレのレス省略数
					if ($j > DISPLAY_RES) { //スレのレス数が規定より多いと
						$bbs_line['rflag'] = true; //省略フラグtrue
					} else {
						$bbs_line['rflag'] = false; //省略フラグfalse
					}
					$flag = false;
					break;
				} //抜ける
				$res['resno'] = ($j + 1); //レス番号
				// http、https以外のURLの場合表示しない
				if (!filter_var($res['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $res['a_url'])) {
					$res['a_url'] = "";
				}
				$res['com'] = htmlspecialchars($res['com'], ENT_QUOTES | ENT_HTML5);

				//オートリンク
				if (AUTOLINK) {
					$res['com'] = auto_link($res['com']);
				}
				//ハッシュタグ
				if (USE_HASHTAG) {
					$res['com'] = hashtag_link($res['com']);
				}
				//空行を縮める
				$res['com'] = preg_replace('/(\n|\r|\r\n|\n\r){3,}/us', "\n\n", $res['com']);
				//<br>に
				$res['com'] = tobr($res['com']);
				//引用の色
				$res['com'] = quote($res['com']);
				//日付をUNIX時間に変換して設定どおりにフォーマット
				$res['created'] = date(DATE_FORMAT, strtotime($res['created']));
				$res['modified'] = date(DATE_FORMAT, strtotime($res['modified']));
				$bbs_line['res'][$j] = $res;
				$j++;
			}
			// http、https以外のURLの場合表示しない
			if (!filter_var($bbs_line['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $bbs_line['a_url'])) {
				$bbs_line['a_url'] = "";
			}
			$bbs_line['com'] = htmlspecialchars($bbs_line['com'], ENT_QUOTES | ENT_HTML5);

			//オートリンク
			if (AUTOLINK) {
				$bbs_line['com'] = auto_link($bbs_line['com']);
			}
			//ハッシュタグ
			if (USE_HASHTAG) {
				$bbs_line['com'] = hashtag_link($bbs_line['com']);
			}
			//空行を縮める
			$bbs_line['com'] = preg_replace('/(\n|\r|\r\n){3,}/us', "\n\n", $bbs_line['com']);
			//<br>に
			$bbs_line['com'] = tobr($bbs_line['com']);
			//引用の色
			$bbs_line['com'] = quote($bbs_line['com']);
			//日付をUNIX時間にしたあと整形
			$bbs_line['past'] = strtotime($bbs_line['created']); // このスレは古いので用
			$bbs_line['created'] = date(DATE_FORMAT, strtotime($bbs_line['created']));
			$bbs_line['modified'] = date(DATE_FORMAT, strtotime($bbs_line['modified']));

			$bbs_line['encoded_t'] = urlencode('['.$bbs_line['tid'].']'.$bbs_line['sub'].($bbs_line['a_name'] ? ' by '.$bbs_line['a_name'] : '').' - '.TITLE);
		

			// そろそろ消えるスレッドのフラグを設定
			$bbs_line['will_delete'] = ($bbs_line['shd'] === '1');

			$dat['oya'][$i] = $bbs_line;
			$i++;
		}

		$dat['dsp_res'] = DISPLAY_RES;
		$dat['path'] = IMG_DIR;

		echo $blade->run(MAINFILE, $dat);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

//カタログモード
function catalog(): void {
	global $blade, $dat;
	$page_def = CATALOG_N;

	//ページング
	try {
		$db = new PDO(DB_PDO);
		if (isset($_GET['page']) && is_numeric($_GET['page'])) {
			$page = $_GET['page'];
			$page = max($page, 1);
		} else {
			$page = 1;
		}
		$start = $page_def * ($page - 1);

		//最大何ページあるのか
		$sqlth = "SELECT SUM(thread) as cnt FROM tlog WHERE invz=0";
		$th_cnt_sql = $db->query("$sqlth");
		$th_cnt_sql = $th_cnt_sql->fetch();
		$th_cnt = $th_cnt_sql["cnt"];
		$max_page = floor($th_cnt / $page_def) + 1;
		//最後にスレ数0のページができたら表示しない処理
		if (($th_cnt % $page_def) == 0) {
			$max_page = $max_page - 1;
			//ただしそれが1ページ目なら困るから表示
			$max_page = max($max_page, 1);
		}
		$dat['max_page'] = $max_page;

		//リンク作成用
		$dat['nowpage'] = $page;
		$p = 1;
		$pp = array();
		$paging = array();
		while ($p <= $max_page) {
			$paging[($p)] = compact('p');
			$pp[] = $paging;
			$p++;
		}
		$dat['paging'] = $paging;
		$dat['pp'] = $pp;

		$dat['back'] = ($page - 1);

		$dat['next'] = ($page + 1);

		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	//読み込み

	try {
		$db = new PDO(DB_PDO);
		//1ページの全スレッド取得
		$sql = "SELECT tid, created, modified, a_name, mail, sub, com, a_url, host, exid, id, pwd, utime, picfile, pchfile, img_w, img_h, utime, tree, parent, age, utime FROM tlog WHERE thread=1 AND invz=0 ORDER BY age DESC, tree DESC LIMIT :start, :page_def";
		$posts = $db->prepare($sql);
		$posts->bindValue(':start', $start, PDO::PARAM_INT);
		$posts->bindValue(':page_def', $page_def, PDO::PARAM_INT);
		$posts->execute();


		$oya = array();

		$i = 0;
		while ($i < CATALOG_N) {
			$bbs_line = $posts->fetch();
			if (empty($bbs_line)) {
				break;
			} //スレがなくなったら抜ける
			$bbs_line['com'] = nl2br(htmlspecialchars($bbs_line['com'], ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbs_line;
			$i++;
		}

		$dat['oya'] = $oya;
		$dat['path'] = IMG_DIR;

		//$smarty->debugging = true;
		$dat['catalog_mode'] = 'catalog';
		echo $blade->run(CATALOGFILE, $dat);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

//検索モード 現在全件表示のみ対応
function search(): void {
	global $blade, $dat;

	$search_f = filter_input(INPUT_GET, 'search');
	$search = str_replace("'", "''", $search_f); //SQL
	//部分一致検索
	$bubun =  filter_input(INPUT_GET, 'bubun');
	//本文検索
	$tag = filter_input(INPUT_GET, 'tag');

	//読み込み
	try {
		$db = new PDO(DB_PDO);
		//全スレッド取得
		//まずtagがあれば全文検索
		if ($tag == 'tag') {
			$sql = "SELECT * FROM tlog WHERE com LIKE ? AND invz=0 ORDER BY age DESC, tree DESC";
			$posts = $db->prepare($sql);
			$posts->execute(["%$search%"]);
			$dat['catalogmode'] = 'hashsearch';
			$dat['tag'] = $search_f;
		} else {
			//tagがなければ作者名検索(スレッドのみ)
			if ($bubun == "bubun") {
				$sql = "SELECT * FROM tlog WHERE a_name LIKE ? AND invz=0 AND thread=1 ORDER BY age DESC, tree DESC";
				$posts = $db->prepare($sql);
				$posts->execute(["%$search%"]);
			} else {
				//完全一致
				$sql = "SELECT * FROM tlog WHERE a_name LIKE ? AND invz=0 AND thread=1 ORDER BY age DESC, tree DESC";
				$posts = $db->prepare($sql);
				$posts->execute([$search]);
			}
			$dat['catalog_mode'] = 'search';
			$dat['author'] = $search_f;
		}

		$oya = array();

		$i = 0;
		while ($bbsline = $posts->fetch()) {
			$bbsline['com'] = nl2br(htmlspecialchars($bbsline['com'], ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbsline;
			$i++;
		}

		$dat['oya'] = $oya;
		$dat['path'] = IMG_DIR;

		$dat['s_result'] = $i;
		echo $blade->run(CATALOGFILE, $dat);
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

//そうだね
function sodane(): void {
	global $en;
	$res_to = filter_input(INPUT_GET, 'res_to', FILTER_VALIDATE_INT);

	// Ajaxリクエストかどうかをチェック
	$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

	try {
		$db = new PDO(DB_PDO);
		$stmt = $db->prepare("UPDATE tlog SET exid = exid + 1 WHERE tid = ?");
		$stmt->execute([$res_to]);

		// 更新後のそうだね数を取得
		$stmt = $db->prepare("SELECT exid FROM tlog WHERE tid = ?");
		$stmt->execute([$res_to]);
		$result = $stmt->fetch();
		$new_ex_id = $result['exid'] ?? 0;

		$db = null;

		if ($is_ajax) {
			// Ajaxリクエストの場合はJSONレスポンス
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'exid' => $new_ex_id,
				'message' => $en ? 'Sodane done.' : 'そうだねしました'
			]);
			return;
		}

	} catch (PDOException $e) {
		if ($is_ajax) {
			header('Content-Type: application/json');
			echo json_encode([
				'success' => false,
				'error' => 'DB接続エラー:' . $e->getMessage()
			]);
			return;
		} else {
			echo "DB接続エラー:" . $e->getMessage();
		}
	}

	// 通常のリクエストの場合は従来通りリダイレクト
	header('Location:' . "./");
	def();
}

//レス画面
function res(): void {
	global $blade, $dat;
	$resno = filter_input(INPUT_GET, 'res',FILTER_VALIDATE_INT);
	$dat['resno'] = $resno;

	//csrfトークンをセット
	$dat['token'] = '';
	if (CHECK_CSRF_TOKEN) {
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$dat['token'] = $token;
	}

	//古いスレのレスフォームを表示しない
	$elapsed_time = ELAPSED_DAYS * 86400; //デフォルトの1年だと31536000
	$nowtime = time(); //いまのunixタイムスタンプを取得
	//あとはテーマ側で計算する
	$dat['elapsed_time'] = $elapsed_time;
	$dat['nowtime'] = $nowtime;

	try {
		$db = new PDO(DB_PDO);
		$sql = "SELECT * FROM tlog WHERE tid = ? ORDER BY tree DESC";
		$posts = $db->prepare($sql);
		$posts->execute([$resno]);

		$oya = array();
		$ko = array();
		while ($bbs_line = $posts->fetch()) {
			//スレッドの記事を取得
			$sql_i = "SELECT * FROM tlog WHERE parent = $resno AND invz = 0 ORDER BY comid ASC";
			$post_i = $db->query($sql_i);
			$rresname = array();
			while ($res = $post_i->fetch()) {
				$res['com'] = htmlspecialchars($res['com'], ENT_QUOTES | ENT_HTML5);

				if (AUTOLINK) {
					$res['com'] = auto_link($res['com']);
				}
				//ハッシュタグ
				if (USE_HASHTAG) {
					$res['com'] = hashtag_link($res['com']);
				}
				//空行を縮める
				$res['com'] = preg_replace('/(\n|\r|\r\n){3,}/us', "\n\n", $res['com']);
				//<br>に
				$res['com'] = tobr($res['com']);
				//引用の色
				$res['com'] = quote($res['com']);
				//日付をUNIX時間に
				$bbs_line['past'] = strtotime($bbs_line['created']); // このスレは古いので用
				$res['created'] = date(DATE_FORMAT, strtotime($res['created']));
				$res['modified'] = date(DATE_FORMAT, strtotime($res['modified']));
				$ko[] = $res;
				//投稿者名取得
				if (!in_array($res['a_name'], $rresname)) { //重複除外
					$rresname[] = $res['a_name']; //投稿者名を配列に入れる
				}
				// http、https以外のURLの場合表示しない
				if (!filter_var($res['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $res['a_url'])) {
					$res['a_url'] = "";
				}
			}
			$bbs_line['com'] = htmlspecialchars($bbs_line['com'], ENT_QUOTES | ENT_HTML5);

			if (AUTOLINK) {
				$bbs_line['com'] = auto_link($bbs_line['com']);
			}
			//ハッシュタグ
			if (USE_HASHTAG) {
				$bbs_line['com'] = hashtag_link($bbs_line['com']);
			}
			//空行を縮める
			$bbs_line['com'] = preg_replace('/(\n|\r|\r\n){3,}/us', "\n", $bbs_line['com']);
			//<br>に
			$bbs_line['com'] = tobr($bbs_line['com']);
			//引用の色
			$bbs_line['com'] = quote($bbs_line['com']);
			//日付をUNIX時間に
			$bbs_line['past'] = strtotime($bbs_line['created']); //古いので用
			$bbs_line['created'] = date(DATE_FORMAT, strtotime($bbs_line['created']));
			$bbs_line['modified'] = date(DATE_FORMAT, strtotime($bbs_line['modified']));
			if (!in_array($bbs_line['a_name'], $rresname)) {
				$rresname[] = $bbs_line['a_name'];
			}
			// http、https以外のURLの場合表示しない
			if (!filter_var($bbs_line['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $bbs_line['a_url'])) {
				$bbs_line['a_url'] = "";
			}
			//名前付きレス用
			$resname = implode(A_NAME_SAN . ' ', $rresname);
			$dat['resname'] = $resname;

			$bbs_line['encoded_t'] = urlencode('['.$bbs_line['tid'].']'.$bbs_line['sub'].($bbs_line['a_name'] ? ' by '.$bbs_line['a_name'] : '').' - '.TITLE);


			$dat['oya'] = $oya;
			$dat['ko'] = $ko;
		}
		$db = null;
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}

	$dat['path'] = IMG_DIR;

	echo $blade->run(RESFILE, $dat);
}

//お絵描き画面
function paint_form($rep): void {
	global $message, $usercode, $quality, $qualitys, $no;
	global $mode, $ctype, $pch, $type;
	global $blade, $dat;
	global $pallets_dat;

	$pwd = (string)filter_input(INPUT_POST, 'pwd');
	$imgfile = filter_input(INPUT_POST, 'img');

	//ツール
	if (isset($_POST["tools"])) {
		$tool = filter_input(INPUT_POST, 'tools');
	} else {
		$tool = "neo";
	}
	$dat['tool'] = $tool;

	$dat['message'] = $message;

	$pic_w = filter_input(INPUT_POST, 'pic_w', FILTER_VALIDATE_INT);
	$pic_h = filter_input(INPUT_POST, 'pic_h', FILTER_VALIDATE_INT);

	if ($mode === "contpaint" && (!$pic_w || !$pic_h)) {
		$imgfile = filter_input(INPUT_POST, 'img'); // 先にimgfileを取得
	  if ($imgfile && is_file(IMG_DIR . $imgfile)) {
      list($picw, $pich) = getimagesize(IMG_DIR . $imgfile); //キャンバスサイズ
    }
	}

	$anime = isset($_POST["anime"]) ? true : false;
	$dat['anime'] = $anime;

	if ($picw < 300) $picw = 300;
	if ($pich < 300) $pich = 300;
	if ($pic_w > PAINT_MAX_W) $pic_w = PAINT_MAX_W;
	if ($pich > PAINT_MAX_H) $pich = PAINT_MAX_H;

	$dat['pic_w'] = $pic_w;
	$dat['pic_h'] = $pic_h;

	if ($tool == "shi") { //しぃペインターの時の幅と高さ
		$ww = $picw + 510;
		$hh = $pich + 172;
	} else { //NEOのときの幅と高さ
		$ww = $picw + 150;
		$hh = $pich + 172;
	}
	if ($hh < 560) {
		$hh = 560;
	} //共通の最低高
	$dat['w'] = $ww;
	$dat['h'] = $hh;

	$dat['undo'] = UNDO;
	$dat['undo_in_mg'] = UNDO_IN_MG;

	$dat['use_anime'] = USE_ANIME;

	$dat['path'] = IMG_DIR;

	$dat['start_time'] = time();

	$userip = get_uip();

	//続きから
	if ($rep !== "") {
		$ctype = filter_input(INPUT_POST, 'ctype');
		$type = $rep;
		$pwd_f = filter_input(INPUT_POST, 'pwd');

		// 動画ファイルの存在をチェックしてctypeを自動設定
		if ($ctype === null || $ctype === '') {
			$pch = filter_input(INPUT_POST, 'pch');
			if ($pch) {
				$pch_filename = pathinfo($pch, PATHINFO_FILENAME);
				if (is_file(IMG_DIR . $pch_filename . '.pch') || is_file(IMG_DIR . $pch_filename . '.spch') || is_file(IMG_DIR . $pch_filename . '.chi')) {
					$ctype = 'pch'; // 動画ファイルが存在する場合
				} else {
					$ctype = 'img'; // 動画ファイルが存在しない場合
				}
			} else {
				$ctype = 'img'; // pchが指定されていない場合
			}
		}

		// デバッグ用：ctypeの値を確認
		error_log("paintform関数 - ctype: " . $ctype);
		error_log("paintform関数 - rep: " . $rep);

		session_sta();

		// 続きから描く場合は一時画像を除外するフラグを設定
		$dat['exclude_temp_images'] = true;

		$dat['no'] = $no;
		$dat['pwd'] = $pwd_f;
		$dat['ctype'] = $ctype;
		if (is_file(IMG_DIR . $pch . '.pch')) {
			$dat['useneo'] = true;
		} elseif (is_file(IMG_DIR . $pch . '.spch')) {
			$dat['useneo'] = false;
			$dat['use_shi_painter'] = true;
		}
		if ((C_SECURITY_CLICK || C_SECURITY_TIMER) && SECURITY_URL) {
			$dat['security'] = true;
			$dat['security_click'] = C_SECURITY_CLICK;
			$dat['security_timer'] = C_SECURITY_TIMER;
		}
	} else {
		if ((SECURITY_CLICK || SECURITY_TIMER) && SECURITY_URL) {
			$dat['security'] = true;
			$dat['security_click'] = SECURITY_CLICK;
			$dat['security_timer'] = SECURITY_TIMER;
		}
		$dat['newpaint'] = true;
	}
	$dat['security_url'] = SECURITY_URL;

	//パレット設定
	//初期パレット
	$lines = array();
	$initial_palette = 'Palettes[0] = "#000000\n#FFFFFF\n#B47575\n#888888\n#FA9696\n#C096C0\n#FFB6FF\n#8080FF\n#25C7C9\n#E7E58D\n#E7962D\n#99CB7B\n#FCECE2\n#F9DDCF";';
	foreach ($pallets_dat as $p_value) {
		if ($p_value[1] == filter_input(INPUT_POST, 'palettes')) { // キーと入力された値が同じなら
			$set_palettec = $p_value[1];
			setcookie("palettec", $set_palettec, time() + (86400 * SAVE_COOKIE)); // Cookie保存
			if (is_array($p_value)) {
				$lines = file($p_value[1]);
			} else {
				$lines = file($p_value);
			}
			break;
		}
	}

	$pal = array();
	$DynP = array();
	$p_cnt = 0;
	$arr_pal=[];
	foreach ($lines as $i => $line) {
		$line = charconvert(str_replace(["\r", "\n", "\t"], "", $line));
		list($pid, $pname, $pal[0], $pal[2], $pal[4], $pal[6], $pal[8], $pal[10], $pal[1], $pal[3], $pal[5], $pal[7], $pal[9], $pal[11], $pal[12], $pal[13]) = explode(",", $line);
		$DynP[] = $pname;
		$p_cnt = $i + 1;
		$palettes = 'Palettes[' . $p_cnt . '] = "#';
		ksort($pal);
		$palettes .= implode('\n#', $pal);
		$palettes .= '";';
		$arr_pal[$i] = $palettes;
	}
	$user_pallete_i = $initial_palette . implode('', $arr_pal);
	$dat['palettes'] = $user_pallete_i;

	$count_dynp = count($DynP) + 1;

	$dat['palsize'] = $count_dynp;

	//パスワード暗号化
	$pwdf = openssl_encrypt($pwd, CRYPT_METHOD, CRYPT_PASS, true, CRYPT_IV); //暗号化
	$pwdf = bin2hex($pwdf); //16進数に
	$arr_dynp=[];
	foreach ($DynP as $p) {
		$arr_dynp[] = '<option>' . $p . '</option>';
	}
	$dat['dynp'] = implode('', $arr_dynp);

	if ($ctype == 'pch' || $ctype == 'spch') {
		$pchfile = filter_input(INPUT_POST, 'pch');
		$dat['pchfile'] = IMG_DIR . $pchfile;
	} elseif ($ctype == 'img') {
		$dat['animeform'] = false;
		$dat['anime'] = false;
		$dat['useanime'] = false; // 動画機能を無効化
		$imgfile = filter_input(INPUT_POST, 'img');
		$dat['imgfile'] = IMG_DIR . $imgfile;
		// 画像から続きを描く場合はpchfileを設定しない
		$dat['pchfile'] = null;
	} else {
		// 新規投稿の場合はpchfileを設定しない（動画ファイルは後で生成される）
		$dat['pchfile'] = null;
	}
	$usercode .= '&tool=' . $tool . '&stime=' . time(); //拡張ヘッダにツールと描画開始時間をセット
	
	// ctypeが設定されている場合はusercodeに含める
	if ($ctype !== null) {
		$usercode .= '&ctype=' . $ctype;
	}

	//差し換え時の認識コード追加
	if ($type === 'rep') {
		$no = filter_input(INPUT_POST, 'no', FILTER_VALIDATE_INT);
		$user_ip = get_uip();

		session_sta();
		$time = time();
		$repcode = substr(crypt(md5($no . $user_ip . $pwd_f . date("Ymd", $time)), $time), -8);
		//念の為にエスケープ文字があればアルファベットに変換
		$repcode = strtr($repcode, "!\"#$%&'()+,/:;<=>?@[\\]^`/{|}~", "ABCDEFGHIJKLMNOabcdefghijklmn");
		$datmode = 'picrep&no=' . $no . '&pwd=' . $pwd_f . '&repcode=' . $repcode;
		$user_code .= '&repcode=' . $repcode;
	}
	$dat['user_code'] = $user_code; //usercodeにいろいろくっついたものをまとめて出力

	// デバッグ用：usercodeの内容を確認
	error_log("paintform関数 - usercode: " . $user_code);
	
	// usercodeをセッション変数に保存
	if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
	}
	$_SESSION['user_code'] = $user_code;

	//出口
	if ($type === 'rep') {
		//差し替え
		$dat['mode'] = $datmode;
	} else {
		//新規投稿
		$dat['mode'] = 'piccom';
	}
	//出力
	if ($tool === 'chicken') {
		echo $blade->run(PAINTFILE_BE, $dat);
	} elseif ($tool === 'shi' || $tool === 'neo') {
		echo $blade->run(PAINTFILE, $dat);
	} else {
		echo $blade->run(PAINTFILE, $dat);
	}
}

//アニメ再生

function open_pch($pch, $sp = ""): void {
	global $blade, $dat;
	$message = "";

	$pch = filter_input(INPUT_GET, 'pch');
	$pchh = str_replace(strrchr($pch, "."), "", $pch); //拡張子除去
	$extn = substr($pch, strrpos($pch, '.') + 1); //拡張子取得

	$picfile = IMG_DIR . $pchh . ".png";

	if ($extn == 'spch') {
		$pchfile = IMG_DIR . $pch;
		$dat['tool'] = 'shi'; //拡張子がspchのときはしぃぺ
	} elseif ($extn == 'pch') {
		$pchfile = IMG_DIR . $pch;
		$dat['tool'] = 'neo'; //拡張子がpchのときはNEO
		//}elseif($extn=='chi'){
		//	$pchfile = IMG_DIR.$pch;
		//	$dat['tool'] = 'chicken'; //拡張子がchiのときはChickenPaint 対応してくれるといいな
	} else {
		$w = $h = $picw = $pich = $datasize = ""; //動画が無い時は処理しない
		$dat['tool'] = 'neo';
		$pchfile = null; // pchfileを明示的にnullに設定
	}
	
	// pchfileが定義されている場合のみfilesizeを実行
	if ($pchfile !== null && is_file($pchfile)) {
		$datasize = filesize($pchfile);
	} else {
		$datasize = 0;
	}

	$size = getimagesize($picfile);
	if (!$sp) $sp = PCH_SPEED;
	$pic_w = $size[0];
	$pic_h = $size[1];
	$w = $pic_w;
	$h = $pic_h + 26;
	if ($w < 300) {
		$w = 300;
	}
	if ($h < 326) {
		$h = 326;
	}

	$dat['pic_w'] = $pic_w;
	$dat['pic_h'] = $pic_h;
	$dat['w'] = $w;
	$dat['h'] = $h;
	$dat['pchfile'] = './' . $pch;
	$dat['datasize'] = $datasize;

	$dat['speed'] = PCH_SPEED;

	$dat['path'] = IMG_DIR;
	$dat['a_stime'] = time();

	echo $blade->run(ANIMEFILE, $dat);
}

//お絵かき投稿
function paint_com($tmpmode): void {
	global $user_code, $ptime;
	global $blade, $dat;

	$start_time = filter_input(INPUT_GET, 'start_time', FILTER_VALIDATE_INT);
	$res_to = filter_input(INPUT_GET, 'res_to', FILTER_VALIDATE_INT);

	$dat['parent'] = $_SERVER['REQUEST_TIME'];
	$dat['user_code'] = $user_code;

	//----------

	//csrfトークンをセット
	$dat['token'] = '';
	if (CHECK_CSRF_TOKEN) {
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$dat['token'] = $token;
	}

	//投稿途中一覧 or 画像新規投稿 or 画像差し替え
	if ($tmpmode == "tmp") {
		$dat['picmode'] = 'is_temp';
	} elseif ($tmpmode == "rep") {
		$dat['picmode'] = 'pict_rep';
	} else {
		$dat['picmode'] = 'pict_up';
	}

	//----------

	//var_dump($_POST);
	$user_ip = get_uip();
	//テンポラリ画像リスト作成
	$tmp_list = array();
	$handle = opendir(TEMP_DIR);
	while (false !== ($file = readdir($handle))) {
		if (!is_dir($file) && preg_match("/\.(dat)\z/i", $file)) {
			$fp = fopen(TEMP_DIR . $file, "r");
			$userdata = fread($fp, 1024);
			fclose($fp);
			list($u_ip, $u_host, $u_agent, $imgext, $u_code,, $start_time, $posted_time,, $tool) = explode("\t", rtrim($userdata) . "\t");
			$file_name = preg_replace("/\.(dat)\z/i", "", $file); //拡張子除去
			if (is_file(TEMP_DIR . $file_name . $imgext)) //画像があればリストに追加
				//描画時間を$userdataをもとに計算
				//(表示用)
				$utime = calcPtime((int)$posted_time - (int)$start_time);
			//描画時間(内部用)
			$psec = (int)$posted_time - (int)$start_time;
			$tmp_list[] = $u_code . "\t" . $u_ip . "\t" . $file_name . $imgext . "\t" . $u_time . "\t" . $p_sec . "\t" . $tool;
		}
	}
	closedir($handle);
	$tmp = array();
	if (count($tmp_list) != 0) {
		//user-codeとipアドレスでチェック
		foreach ($tmp_list as $tmp_img) {
			list($u_code, $u_ip, $u_filename, $u_time, $p_sec, $tool) = explode("\t", $tmp_img);
			if ($u_code == $user_code || $u_ip == $user_ip) {
				// 続きから描く場合は一時画像を除外
				if (isset($dat['exclude_temp_images']) && $dat['exclude_temp_images']) {
					continue;
				}
				$tmp[] = $u_filename;
			}
		}
	}

	$post_mode = true;
	$regist = true;
	$ip_check = true;
	if (count($tmp) == 0) {
		$no_tmp = true;
		$pictmp = 1;
	} else {
		$pictmp = 2;
		sort($tmp);
		reset($tmp);
		$temp = array();
		foreach ($tmp as $tmpfile) {
			$src = TEMP_DIR . $tmpfile;
			$srcname = $tmpfile;
			$date = gmdate("Y/m/d H:i", filemtime($src) + 9 * 60 * 60);
			$utime = $utime;
			$psec = $psec;
			$temp[] = compact('src', 'srcname', 'date', 'tool', 'utime', 'psec');
		}
		$dat['temp'] = $temp;
	}

	$tmp2 = array();
	$dat['tmp'] = $tmp2;

	echo $blade->run(PICFILE, $dat);
}

//コンティニュー画面in
function in_continue(): void {
	global $blade, $dat;

	$no = filter_input(INPUT_GET, 'no'); // 画像ファイル名なので文字列として取得
	$dat['othermode'] = 'incontinue';
	$dat['continue_mode'] = true;

	if (isset($_POST["tools"])) {
		$tool = filter_input(INPUT_POST, 'tools');
	} else {
		$tool = "neo";
	}
	$dat['tool'] = $tool;

	//コンティニュー時は削除キーを常に表示
	$dat['passflag'] = true;
	//新規投稿で削除キー不要の時 true
	if (!CONTINUE_PASS) $dat['newpost_nopassword'] = true;

	try {
		$db = new PDO(DB_PDO);
		$sql = "SELECT *, ext02 as ctype FROM tlog WHERE picfile=? ORDER BY tree DESC";
		$posts = $db->prepare($sql);
		$posts->execute([$no]);
		$oya = array();
		while ($bbs_line = $posts->fetch()) {
			$bbs_line['com'] = nl2br(htmlentities($bbs_line['com'], ENT_QUOTES | ENT_HTML5), false);
			$oya[] = $bbs_line;
			$dat['oya'] = $oya; //配列に格納
		}
		$hist_ope = pathinfo($no, PATHINFO_FILENAME); //拡張子除去
		$hist_file_name = IMG_DIR . $hist_ope;
		
		// データベースからctypeを取得
		$db_ctype = $oya[0]['ctype'] ?? null;
		
		if (is_file($hist_file_name . '.pch')) {
			//$pchfile = IMG_DIR.$pch;
			$dat['tool'] = 'neo'; //拡張子がpchのときはNEO
			$dat['useshi'] = false;
			$dat['useneo'] = true;
			$dat['ctype_pch'] = true;
			$dat['ctype_img'] = false;
		} elseif (is_file($hist_file_name . '.spch')) {
			$dat['tool'] = 'shi'; //拡張子がspchのときはしぃぺ
			$dat['useshi'] = true;
			$dat['useneo'] = false;
			$dat['ctype_pch'] = true;
			$dat['ctype_img'] = false;
		} elseif (is_file($hist_file_name . '.chi')) {
			$dat['tool'] = 'chicken'; //拡張子がchiのときはChickenPaint
			$dat['useshi'] = false;
			$dat['useneo'] = false;
			$dat['ctype_pch'] = true;
			$dat['ctype_img'] = false;
		} else { // どれでもない＝動画が無い時
			//$w=$h=$picw=$pich=$datasize="";
			$dat['useneo'] = true;
			$dat['useshi'] = true;
			$dat['ctype_pch'] = false;
			$dat['ctype_img'] = true;
		}
		// useshi, useneoは互換のためにいちおう残してある
		
		// データベースのctypeを優先する
		if ($db_ctype === 'img') {
			$dat['ctype_img'] = true;
			$dat['ctype_pch'] = false;
		} elseif ($db_ctype === 'pch' || $db_ctype === 'spch') {
			$dat['ctype_img'] = false;
			$dat['ctype_pch'] = true;
		}

		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}

	echo $blade->run(OTHERFILE, $dat);
}

//削除くん

function del_mode(): void {
	global $admin_pass;
	global $dat;
	$del_no = filter_input(INPUT_POST, 'del_no',FILTER_VALIDATE_INT);

	$pwd_f = filter_input(INPUT_POST, 'pwd');

	//記事呼び出し
	try {
		$db = new PDO(DB_PDO);

		//パスワードを取り出す
		$sql = "SELECT pwd FROM tlog WHERE tid = ?";
		$msgs = $db->prepare($sql);
		if ($msgs == false) {
			error('そんな記事ない気がします。');
		}
		$msgs->execute([$del_no]);
		$msg = $msgs->fetch();
		if (empty($msg)) {
			error('そんな記事ない気がします。');
		}

		//削除記事の画像を取り出す
		$sqlp = "SELECT picfile FROM tlog WHERE tid = ?";
		$msgsp = $db->prepare($sqlp);
		$msgsp->execute([$del_no]);
		$msgsp->execute();
		$msgp = $msgsp->fetch();
		if (empty($msgp)) {
			error('画像が見当たりません。');
		}
		$msgpic = $msgp['picfile']; //画像の名前取得できた

		if (isset($_POST["admindel"])) {
			$admindelmode = 1;
		} else {
			$admindelmode = 0;
		}

		if (password_verify($pwd_f, $msg['pwd'])) {
			//画像とかファイル削除
			if (is_file(IMG_DIR . $msgpic)) {
				$msgdat = str_replace(strrchr($msgpic, "."), "", $msgpic); //拡張子除去
				safe_unlink(IMG_DIR . $msgdat . '.png');
				safe_unlink(IMG_DIR . $msgdat . '.jpg'); //一応jpgも
				safe_unlink(IMG_DIR . $msgdat . '.pch');
				safe_unlink(IMG_DIR . $msgdat . '.spch');
				safe_unlink(IMG_DIR . $msgdat . '.dat');
				safe_unlink(IMG_DIR . $msgdat . '.chi');
			}
			//↑画像とか削除処理完了
			//データベースから削除
			$sql = "DELETE FROM tlog WHERE tid = ?";
			$stmt = $db->prepare($sql);
			$stmt->execute([$del_no]);
			$dat['message'] = '削除しました。';
		} elseif ($admin_pass == $pwd_f && $admindelmode == 1) {
			//画像とかファイル削除
			if (is_file(IMG_DIR . $msgpic)) {
				$msgdat = str_replace(strrchr($msgpic, "."), "", $msgpic); //拡張子除去
				safe_unlink(IMG_DIR . $msgdat . '.png');
				safe_unlink(IMG_DIR . $msgdat . '.jpg'); //一応jpgも
				safe_unlink(IMG_DIR . $msgdat . '.pch');
				safe_unlink(IMG_DIR . $msgdat . '.spch');
				safe_unlink(IMG_DIR . $msgdat . '.dat');
				safe_unlink(IMG_DIR . $msgdat . '.chi');
			}
			//↑画像とか削除処理完了
			//データベースから削除
			$sql = "DELETE FROM tlog WHERE tid = ? OR parent = ?";
			$stmt = $db->prepare($sql);
			$stmt->execute([$del_no, $del_no]);
			$dat['message'] = '削除しました。';
		} elseif ($admin_pass == $pwd_f && $admindelmode != 1) {
			//管理モード以外での管理者削除は
			//データベースから削除はせずに非表示
			$sql = "UPDATE tlog SET invz=1 WHERE tid = ?";
			$stmt = $db->prepare($sql);
			$stmt->execute([$del_no]);
			$dat['message'] = '非表示にしました。';
		} else {
			error('パスワードまたは記事番号が違います。');
		}
		$msgp = null;
		$msg = null;
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	//変数クリア
	unset($del_no, $del_t);
	//header('Location:'.PHP_SELF);
	ok('削除しました。画面を切り替えます。');
}

//画像差し替え
function picreplace(): void {
	global $type, $en;
	global $path, $badip;

	$start_time = filter_input(INPUT_GET, 'start_time', FILTER_VALIDATE_INT);
	$no = filter_input(INPUT_GET, 'no', FILTER_VALIDATE_INT);
	$rep_code = filter_input(INPUT_GET, 'rep_code');
	$pwd_f = filter_input(INPUT_GET, 'pwd');
	$pwd_f = hex2bin($pwd_f); //バイナリに
	$pwd_f = openssl_decrypt($pwd_f, CRYPT_METHOD, CRYPT_PASS, true, CRYPT_IV); //復号化
	$nsfw_flag = filter_input(INPUT_POST, 'nsfw');

	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach ($badip as $value) { //拒絶host
		if (preg_match("/$value$/i", $host)) error(MSG016);
	}

	/*--- テンポラリ捜査 ---*/
	$find = false;
	$handle = opendir(TEMP_DIR);
	while (false !== ($file = readdir($handle))) {
		if (!is_dir($file) && preg_match("/\.(dat)\z/i", $file)) {
			$fp = fopen(TEMP_DIR . $file, "r");
			$userdata = fread($fp, 1024);
			fclose($fp);
			list($u_ip, $u_host, $u_agent, $imgext, $u_code, $u_rep_code, $start_time, $posted_time,, $tool) = explode("\t", rtrim($userdata) . "\t"); //区切りの"\t"を行末にして配列へ格納
			$file_name = pathinfo($file, PATHINFO_FILENAME); //拡張子除去
			if ($file_name && is_file(TEMP_DIR . $file_name . $imgext) && $u_rep_code === $rep_code) {
				$find = true;
				break;
			}
		}
	}
	closedir($handle);
	if (!$find) {
		error($en ? 'No temporary image found.' : '画像がありません。');
	}

	// ログ読み込み
	try {
		$db = new PDO(DB_PDO);
		//記事を取り出す
		$sql = "SELECT * FROM tlog WHERE tid = ?";
		$msgs = $db->prepare($sql);
		$msgs->execute([$no]);
		$msg_d = $msgs->fetch();
		//パスワード照合
		// $flag = false;
		if (password_verify($pwd_f, $msg_d["pwd"])) {
			//パスワードがあってたら画像アップロード処理
			$up_picfile = TEMP_DIR . $file_name . $imgext;
			$dest = IMG_DIR . $start_time . '.tmp';
			copy($up_picfile, $dest);

			if (!is_file($dest)) error($en ? 'Failed to upload image.' : '画像のアップロードに失敗しました。');
			chmod($dest, PERMISSION_FOR_DEST);
			//元ファイル削除
			safe_unlink(IMG_DIR . $msg_d["picfile"]);

			$img_type = mime_content_type($dest);
			$imgext = get_image_type($img_type, $dest);

			//新しい画像の名前(DB保存用)
			$new_picfile = $file_name . $imgext;

			chmod($dest, PERMISSION_FOR_DEST);
			rename($dest, IMG_DIR . $new_picfile);

			//ワークファイル削除
			safe_unlink($up_picfile);
			safe_unlink(TEMP_DIR . $file_name . ".dat");

			//動画ファイルアップロード
			//拡張子チェック
			$pchext = '';
			if (is_file(TEMP_DIR . $file_name . '.chi')) {
				$pchext = '.chi';
			} elseif (is_file(TEMP_DIR . $file_name . '.spch')) {
				$pchext = '.spch';
			} elseif (is_file(TEMP_DIR . $file_name . '.pch')) {
				$pchext = '.pch';
			}
			//元ファイル削除
			safe_unlink(IMG_DIR . $msg_d["pchfile"]);

			//新しい動画ファイルの名前(DB保存用)
			$new_pchfile = $file_name . $pchext;

			//動画ファイルアップロード本編
			if (is_file(TEMP_DIR . $file_name . $pchext)) {
				$pchsrc = TEMP_DIR . $file_name . $pchext;
				$dst = IMG_DIR . $new_pchfile;
				if (copy($pchsrc, $dst)) {
					chmod($dst, PERMISSION_FOR_DEST);
					safe_unlink($pchsrc);
				}
			}

			//描画時間を$userdataをもとに計算
			$psec = (int)$msg_d['psec'] + ((int)$posted_time - (int)$start_time);
			$utime = calcPtime($psec);

			//ホスト名取得
			$host = gethostbyaddr(get_uip());

			//id生成
			$id = gen_id($host, $psec);

			// 念のため'のエスケープ
			$host = str_replace("'", "''", $host);

			//nsfw
			if (USE_NSFW == 1 && $nsfw_flag == 1) {
				$nsfw = true;
			} else {
				$nsfw = false;
			}

			//db上書き
			$sqlrep = "UPDATE tlog set modified = datetime('now', 'localtime'), host = :host, picfile = :new_picfile, pchfile = :new_pchfile, id = :id, psec = :psec, utime = :utime, ext01 = :nsfw WHERE tid = :no";
			// プレースホルダ
			try {
				$stmt = $db->prepare($sqlrep);
				$stmt->execute(
					[
						':host'=>$host, ':new_picfile'=>$new_picfile, ':new_pchfile'=>$new_pchfile, ':id'=>$id,':psec'=>$psec,':utime'=>$utime,':nsfw'=>$nsfw,':no'=>$no,
					]
				);
			} catch(PDOException $e) {
				echo "DB接続エラー:" . $e->getMessage();
			}
			$db = $db->exec($sqlrep);
		} else {
			error($en ? 'Failed to edit.' : '編集に失敗しました。');
		}
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	ok('編集に成功しました。画面を切り替えます。');
}

//編集モードくん入口
function editform(): void {
	global $admin_pass;
	global $blade, $dat, $en;

	//csrfトークンをセット
	$dat['token'] = '';
	if (CHECK_CSRF_TOKEN) {
		$token = get_csrf_token();
		$_SESSION['token'] = $token;
		$dat['token'] = $token;
	}

	//入力されたパスワード
	$postpwd = filter_input(INPUT_POST, 'pwd');

	$edit_no = filter_input(INPUT_POST, 'del_no',FILTER_VALIDATE_INT);
	if ($edit_no == "") {
		error($en ? 'Please enter the article number.' : '記事番号を入力してください');
	}

	//記事呼び出し
	try {
		$db = new PDO(DB_PDO);

		//パスワードを取り出す
		$sql = "SELECT pwd FROM tlog WHERE tid = ?";
		$stmt = $db->prepare($sql);
		$stmt->execute([$edit_no]);
		$msg = $stmt->fetch();
		if (empty($msg)) {
			error('そんな記事ないです。');
		}
		if (password_verify($postpwd, $msg['pwd'])) {
			//パスワードがあってたら
			$sqli = "SELECT * FROM tlog WHERE tid = $edit_no";
			$posts = $db->query($sqli);
			$oya = array();
			while ($bbs_line = $posts->fetch()) {
				$bbs_line['com'] = nl2br(htmlentities($bbs_line['com'], ENT_QUOTES | ENT_HTML5), false);
				$oya[] = $bbs_line;
				$dat['oya'] = $oya;
			}
			$dat['message'] = '編集モード...';
		} elseif ($admin_pass == $postpwd) {
			//管理者編集モード
			$sqli = "SELECT * FROM tlog WHERE tid = $edit_no";
			$posts = $db->query($sqli);
			$oya = array();
			while ($bbs_line = $posts->fetch()) {
				$bbs_line['com'] = nl2br(htmlentities($bbs_line['com'], ENT_QUOTES | ENT_HTML5), false);
				$oya[] = $bbs_line;
				$dat['oya'] = $oya;
			}
			$dat['message'] = '管理者編集モード...';
		} else {
			$db = null;
			$msgs = null;
			$db = null; //db切断
			error($en ? 'Password or article number is incorrect.' : 'パスワードまたは記事番号が違います。');
		}
		$db = null;
		$msgs = null;
		$posts = null;
		$db = null; //db切断

		$dat['othermode'] = 'edit'; //編集モード
		echo $blade->run(OTHERFILE, $dat);
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

//編集モードくん本体
function editexec(): void {
	global $badip;
	global $req_method;
	global $dat, $en;

	//CSRFトークンをチェック
	if (CHECK_CSRF_TOKEN) {
		check_csrf_token();
	}

	$resedit = trim((string)filter_input(INPUT_POST, 'resedit'));
	$e_no = trim((string)filter_input(INPUT_POST, 'e_no'));

	if ($req_method !== "POST") {
		error($en ? 'Invalid request method.' : '無効なリクエストメソッドです。');
	}

	$sub = (string)filter_input(INPUT_POST, 'sub');
	$name = (string)filter_input(INPUT_POST, 'name');
	$mail = (string)filter_input(INPUT_POST, 'mail');
	$url = (string)filter_input(INPUT_POST, 'url');
	$com = (string)filter_input(INPUT_POST, 'com');
	$picfile = trim((string)filter_input(INPUT_POST, 'picfile'));
	$pwd = (string)trim(filter_input(INPUT_POST, 'pwd'));
	$pwdh = password_hash($pwd, PASSWORD_DEFAULT);
	$exid = trim((string)filter_input(INPUT_POST, 'exid', FILTER_VALIDATE_INT));

	//NGワードがあれば拒絶
	Reject_if_NGword_exists_in_the_post($com, $name, $mail, $url, $sub);

	if (USE_NAME && !$name) {
		error($en ? 'Please enter your name.' : '名前を入力してください。');
	}
	//本文必須でいいだろ
	if (!$com) {
		error($en ? 'Please enter your comment.' : 'コメントを入力してください。');
	}
	if (USE_SUB && !$sub) {
		error($en ? 'Please enter the subject.' : '題名を入力してください。');
	}

	if (strlen($com) > MAX_COM) {
		error($en ? 'The comment is too long.' : 'コメントが長すぎます。');
	}
	if (strlen($name) > MAX_NAME) {
		error($en ? 'The name is too long.' : '名前が長すぎます。');
	}
	if (strlen($mail) > MAX_EMAIL) {
		error($en ? 'The email address is too long.' : 'メールアドレスが長すぎます。');
	}
	if (strlen($sub) > MAX_SUB) {
		error($en ? 'The subject is too long.' : '題名が長すぎます。');
	}

	//ホスト取得
	$host = gethostbyaddr(get_uip());

	foreach ($badip as $value) { //拒絶host
		if (preg_match("/$value$/i", $host)) {
			error($en ? 'The host is blocked.' : 'ホストがブロックされています。');
		}
	}
	//↑セキュリティ関連ここまで

	// 'のエスケープ(入りうるところがありそうなとこだけにしといた)
	$name = str_replace("'", "''", $name);
	$sub = str_replace("'", "''", $sub);
	$com = str_replace("'", "''", $com);
	$mail = str_replace("'", "''", $mail);
	$url = str_replace("'", "''", $url);
	$host = str_replace("'", "''", $host);

	try {
		$db = new PDO(DB_PDO);
		$sql = "UPDATE tlog set modified = datetime('now', 'localtime'), a_name = :name, mail = :mail, sub = :sub, com = :com, a_url = :url, host = :host, exid = :exid, pwd = :pwdh where tid = :e_no";

		// プレースホルダ
		try {
			$stmt = $db->prepare($sql);
			$stmt->execute(
				[
					':name'=>$name, ':mail'=>$mail, ':sub'=>$sub, ':com'=>$com,':url'=>$url,':host'=>$host,':exid'=> $exid,':pwdh'=> $pwdh, ':e_no'=>$e_no,
					]
			);
			} catch(PDOException $e) {
				echo "DB接続エラー:" . $e->getMessage();
			}

		$db = $db->exec($sql);
		$db = null;
		$dat['message'] = '編集完了しました。';
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	unset($name, $mail, $sub, $com, $url, $pwd, $pwdh, $resto, $pictmp, $picfile, $mode);
	//header('Location:'.PHP_SELF);
	ok($en ? 'Edit completed. Switching to the next screen.' : '編集に成功しました。画面を切り替えます。');
}

//管理モードin
function admin_in(): void {
	global $blade, $dat;
	$dat['othermode'] = 'admin_in';

	echo $blade->run(OTHERFILE, $dat);
}

//管理モード
function admin(): void {
	global $admin_pass;
	global $blade, $dat;

	$dat['path'] = IMG_DIR;

	//最大何ページあるのか
	//記事呼び出しから
	try {
		$db = new PDO(DB_PDO);
		//読み込み
		$adminpass = filter_input(INPUT_POST, 'adminpass');
		if ($adminpass === $admin_pass) {
			$sql = "SELECT * FROM tlog WHERE thread=1 ORDER BY age DESC,tree DESC";
			$oya = array();
			$posts = $db->prepare($sql);
			$posts->execute();
			while ($bbs_line = $posts->fetch()) {
				if (empty($bbs_line)) {
					break;
				} //スレがなくなったら抜ける
				//$oya_id = $bbsline["tid"]; //スレのtid(親番号)を取得
				$bbs_line['com'] = htmlentities($bbs_line['com'], ENT_QUOTES | ENT_HTML5);
				$oya[] = $bbs_line;
			}
			$dat['oya'] = $oya;

			//スレッドの記事を取得
			$sqli = "SELECT * FROM tlog WHERE thread=0 ORDER BY tree ASC";
			$ko = array();
			$postsi = $db->query($sqli);
			while ($res = $postsi->fetch()) {
				$res['com'] = htmlentities($res['com'], ENT_QUOTES | ENT_HTML5);
				$ko[] = $res;
			}
			$dat['ko'] = $ko;
			echo $blade->run(ADMINFILE, $dat);
		} else {
			$db = null; //db切断
			error('管理パスを入力してください');
		}
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

// コンティニュー認証 (画像)
function usr_chk(): void {
	global $en;
	$no = filter_input(INPUT_POST, 'no', FILTER_VALIDATE_INT);
	$pwd_f = filter_input(INPUT_POST, 'pwd');
	$flag = FALSE;
	try {
		$db = new PDO(DB_PDO);
		//パスワードを取り出す
		$sql = "SELECT pwd FROM tlog WHERE tid = ?";
		$msgs = $db->prepare($sql);
		$msgs->execute([$no]);
		$msg = $msgs->fetch();
		if (password_verify($pwd_f, $msg['pwd'])) {
			$flag = true;
		} else {
			$flag = false;
		}
		$db = null; //切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
	if (!$flag) {
		error($en ? 'Password is incorrect.' : 'パスワードが違います。');
	}
}

//OK画面
function ok($mes): void {
	global $blade, $dat;
	$dat['okmes'] = $mes;
	$dat['othermode'] = 'ok';
	$async_flag = (bool)filter_input(INPUT_POST,'asyncflag',FILTER_VALIDATE_BOOLEAN);
	$http_x_requested_with = (bool)(isset($_SERVER['HTTP_X_REQUESTED_WITH']));
	if($http_x_requested_with || $async_flag){
		die("OK!\n$mes");
	}
	echo $blade->run(OTHERFILE, $dat);
}

//Asyncリクエストの時は処理を中断
function check_AsyncRequest($picfile=''): void {
	//ヘッダーが確認できなかった時の保険
	$asyncflag = (bool)filter_input(INPUT_POST,'asyncflag',FILTER_VALIDATE_BOOLEAN);
	$http_x_requested_with = (bool)(isset($_SERVER['HTTP_X_REQUESTED_WITH']));
	if($http_x_requested_with || $asyncflag){
		safe_unlink($picfile);
		exit;
	}
}

/* テンポラリ内のゴミ除去 */
function del_temp(): void {
	$handle = opendir(TEMP_DIR);
	while ($file = readdir($handle)) {
		if (!is_dir($file)) {
			$lapse = time() - filemtime(TEMP_DIR . $file);
			if ($lapse > (TEMP_LIMIT * 24 * 3600)) {
				safe_unlink(TEMP_DIR . $file);
			}
			//pchアップロードペイントファイル削除
			if (preg_match("/\A(pchup-.*-tmp\.s?pch)\z/i", $file)) {
				$lapse = time() - filemtime(TEMP_DIR . $file);
				if ($lapse > (300)) { //5分
					safe_unlink(TEMP_DIR . $file);
				}
			}
		}
	}
	closedir($handle);
}

//ログの行数が最大値を超えていたら削除
function log_del(): void {
	//オーバーした行の画像とスレ番号を取得
	try {
		$db = new PDO(DB_PDO);
		$sqlimg = "SELECT * FROM tlog ORDER BY tid LIMIT 1";
		$msgs = $db->prepare($sqlimg);
		$msgs->execute();
		$msg = $msgs->fetch();

		$del_tid = (int)$msg["tid"]; //消す行のスレ番号
		$msgpic = $msg["picfile"]; //画像の名前取得できた
		//画像とかの削除処理
		if (is_file(IMG_DIR . $msgpic)) {
			$msgdat = pathinfo($msgpic, PATHINFO_FILENAME); //拡張子除去
			safe_unlink(IMG_DIR . $msgdat . '.png');
			safe_unlink(IMG_DIR . $msgdat . '.jpg'); //一応jpgも
			safe_unlink(IMG_DIR . $msgdat . '.pch');
			safe_unlink(IMG_DIR . $msgdat . '.spch');
			safe_unlink(IMG_DIR . $msgdat . '.dat');
			safe_unlink(IMG_DIR . $msgdat . '.chi');
		}

		//レスあれば削除
		//カウント
		$sqlc = "SELECT COUNT(*) as cnti FROM tlog WHERE parent = $del_tid";
		$countres = $db->query("$sqlc");
		$countres = $countres->fetch();
		$logcount = $countres["cnti"];
		//削除
		if ($logcount !== 0) {
			$delres = "DELETE FROM tlog WHERE parent = $del_tid";
			$db->exec($delres);
		}
		//スレ削除
		$delths = "DELETE FROM tlog WHERE tid = $del_tid";
		$db->exec($delths);

		$sqlimg = null;
		$delths = null;
		$msg = null;
		$del_tid = null;
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

//misskeyにノート
function misskey_note(): void {
	global $blade, $dat;
	//スレの画像取得
	$no = filter_input(INPUT_GET, 'no',FILTER_VALIDATE_INT);
	try {
		$db = new PDO(DB_PDO);
		$sql = "SELECT * FROM tlog WHERE id=? ORDER BY tree DESC";
		$posts = $db->prepare($sql);
		$posts->execute([$no]);
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

//エラー画面
function error($mes): void {
	global $db;
	global $blade, $dat;
	$db = null; //db切断
	$dat['errmes'] = $mes;
	$dat['othermode'] = 'err';
	$async_flag = (bool)filter_input(INPUT_POST,'asyncflag',FILTER_VALIDATE_BOOLEAN);
	$http_x_requested_with = (bool)(isset($_SERVER['HTTP_X_REQUESTED_WITH']));
	if($http_x_requested_with || $async_flag){
		die("error\n$mes");
	}
	echo $blade->run(OTHERFILE, $dat);
	exit;
}

//画像差し替え失敗
function error2(): void {
	global $db;
	global $blade, $dat;
	global $self;
	$db = null; //db切断
	$dat['othermode'] = 'err2';
	$async_flag = (bool)filter_input(INPUT_POST,'asyncflag',FILTER_VALIDATE_BOOLEAN);
	$http_x_requested_with = (bool)(isset($_SERVER['HTTP_X_REQUESTED_WITH']));
	if($http_x_requested_with || $async_flag){
		die("error?\n画像が見当たりません。投稿に失敗している可能性があります。<a href=\"{{$self}}?mode=piccom\">アップロード途中の画像</a>に残っているかもしれません。");
	}
	echo $blade->run(OTHERFILE, $dat);
	exit;
}

// ファイルアップロードのセキュリティ検証関数
function validate_upload_file($file_path, $allowed_types = ['image/jpeg', 'image/png', 'image/gif']): bool {
    // ファイルの存在確認
    if (!file_exists($file_path)) {
        return false;
    }

    // MIMEタイプの検証
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        return false;
    }

    // ファイルサイズの検証（デフォルト10MB）
    if (filesize($file_path) > 10 * 1024 * 1024) {
        return false;
    }

    // 画像の整合性チェック
    $image_info = getimagesize($file_path);
    if ($image_info === false) {
        return false;
    }

    // 画像の幅と高さの検証
    if ($image_info[0] > PAINT_MAX_W || $image_info[1] > PAINT_MAX_H) {
        return false;
    }

    return true;
}
