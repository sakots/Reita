<?php
$functions_ver = 20250610;

//ページのコンテキストをセッションに保存
function set_page_context_to_session(): void {
	session_sta();
	// セッションに保存
	$_SESSION['current_page_context'] = [
		'page' => (int)filter_input_data('GET', 'page', FILTER_VALIDATE_INT),
		'resno' => filter_input_data('GET', 'resno', FILTER_VALIDATE_INT),//未設定時はnull。intでキャストしない事。
		'catalog' => (bool)(filter_input_data('GET', 'mode') === 'catalog'),
		'res_catalog' => (bool)filter_input_data('GET', 'res_catalog', FILTER_VALIDATE_BOOLEAN),
		'misskey_note' => (bool)filter_input_data('GET', 'misskey_note', FILTER_VALIDATE_BOOLEAN),
		'search' => (bool)(filter_input_data('GET', 'mode') === 'search'),
		'radio' => (int)filter_input_data('GET', 'radio', FILTER_VALIDATE_INT),
		'imgsearch' => (bool)filter_input_data('GET', 'imgsearch', FILTER_VALIDATE_BOOLEAN),
		'q' => (string)filter_input_data('GET', 'q'),
	];
	$_SESSION['current_id'] = null;
}

//管理者パスワードを確認
function is_admin_pass($pwd): bool {
	global $admin_pass,$second_pass;
	$pwd=(string)$pwd;
	return ($admin_pass && $pwd && $second_pass !== $admin_pass && $pwd === $admin_pass);
}

// 文字コード変換
function charconvert($str): string {
	mb_language(LANG);
	return mb_convert_encoding($str, "UTF-8", "auto");
}

/* NGワードがあれば拒絶 */
function Reject_if_NGword_exists_in_the_post($com, $name, $email, $url, $sub): void {
	global $bad_string, $bad_name, $bad_str_A, $bad_str_B, $pwd, $admin_pass, $en;
	//チェックする項目から改行・スペース・タブを消す
	$chk_com  = preg_replace("/\s/u", "", $com);
	$chk_name = preg_replace("/\s/u", "", $name);
	$chk_email = preg_replace("/\s/u", "", $email);
	$chk_sub = preg_replace("/\s/u", "", $sub);

	//本文に日本語がなければ拒絶
	if (USE_JAPANESE_FILTER) {
		mb_regex_encoding("UTF-8");
		if (strlen($com) > 0 && !preg_match("/[ぁ-んァ-ヶー一-龠]+/u", $chk_com)) error($en ? 'The comment does not contain Japanese.' : 'コメントに日本語が含まれていません。');
	}

	//本文へのURLの書き込みを禁止
	if (!($pwd === $admin_pass)) { //どちらも一致しなければ
		if (DENY_COMMENTS_URL && preg_match('/:\/\/|\.co|\.ly|\.gl|\.net|\.org|\.cc|\.ru|\.su|\.ua|\.gd/i', $com)) error($en ? 'URL is not allowed.' : 'URLは禁止されています。');
	}

	// 使えない文字チェック
	if (is_ng_word($bad_string, [$chk_com, $chk_sub, $chk_name, $chk_email])) {
		error($en ? 'The comment contains prohibited words.' : 'コメントに禁止された単語が含まれています。');
	}

	// 使えない名前チェック
	if (is_ng_word($bad_name, $chk_name)) {
		error($en ? 'The name contains prohibited words.' : '名前に禁止された単語が含まれています。');
	}

	//指定文字列が2つあると拒絶
	$bstr_A_find = is_ng_word($bad_str_A, [$chk_com, $chk_sub, $chk_name, $chk_email]);
	$bstr_B_find = is_ng_word($bad_str_B, [$chk_com, $chk_sub, $chk_name, $chk_email]);
	if ($bstr_A_find && $bstr_B_find) {
		error($en ? 'The comment contains prohibited words.' : 'コメントに禁止された単語が含まれています。');
	}
}

//念のため画像タイプチェック
function get_image_type($img_type, $dest = null): string {
	// 既にMIMEタイプが渡されている場合はそのまま使用
	if (strpos($img_type, 'image/') === 0) {
		$mime_type = $img_type;
	} else {
		// ファイルパスが渡されている場合はMIMEタイプを取得
		$mime_type = mime_content_type($img_type);
	}
	
	$map = [
		"image/gif" => ".gif",
		"image/jpeg" => ".jpg",
		"image/png" => ".png",
		"image/webp" => ".webp",
	];

	if (isset($map[$mime_type])) {
		return $map[$mime_type];
	}
	error($en ? 'Invalid image type.' : '無効な画像タイプです。', $dest);
	return ''; // この行は実際には実行されないが、リンターを満足させるために必要
}

/**
 * NGワードチェック
 * @param $ngwords
 * @param string|array $strs
 * @return bool
 */
function is_ng_word($ng_words, $strs): bool {
	if (empty($ng_words)) {
		return false;
	}
	if (!is_array($strs)) {
		$strs = [$strs];
	}
	foreach ($strs as $str) {
		foreach ($ng_words as $ng_word) { //拒絶する文字列
			if ($ng_word !== '' && preg_match("/{$ng_word}/ui", $str)) {
				return true;
			}
		}
	}
	return false;
}

/**
 * 描画時間を計算
 * @param $starttime
 * @return string
 */
function calc_ptime($psec): string {

	$D = floor($psec / 86400);
	$H = floor($psec % 86400 / 3600);
	$M = floor($psec % 3600 / 60);
	$S = $psec % 60;

	return ($D ? $D . PAINT_TIME_D : '') . ($H ? $H . PAINT_TIME_H : '') . ($M ? $M . PAINT_TIME_M : '') . ($S ? $S . PAINT_TIME_S : '');
}
/**
 * ファイルがあれば削除
 * @param $path
 * @return bool
 */
function safe_unlink($path): bool {
	if ($path && is_file($path)) {
		try {
			return @unlink($path);
		} catch (Exception $e) {
			// エラーをログに記録するか、静かに失敗する
			error_log("Failed to delete file: {$path} - " . $e->getMessage());
			return false;
		}
	}
	return false;
}

/* オートリンク */
function auto_link($proto): string {
	if (!(stripos($proto, "script") !== false)) { //scriptがなければ続行
		$pattern = "{(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)}";
		$replace = "<a href=\"\\1\\2\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">\\1\\2</a>";
		$proto = preg_replace($pattern, $replace, $proto);
		return $proto;
	} else {
		return $proto;
	}
}

/* ハッシュタグリンク */
function hashtag_link($hashtag): string {
	$pattern = "/(?:^|[^ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9&_\/]+)[#＃]([ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z]+[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*)/u";
	$replace = " <a href=\"?mode=search&amp;tag=tag&amp;search=\\1\">#\\1</a>";
	$hashtag = preg_replace($pattern, $replace, $hashtag);
	return $hashtag;
}

/* '>'色設定 */
function quote($quote): string {
	$quote = preg_replace("/(^|>)((&gt;|＞)[^<]*)/i", "\\1" . RE_START . "\\2" . RE_END, $quote);
	return $quote;
}

/* 改行を<br>に */
function tobr($com): string {
	$com = nl2br($com, false);
	return $com;
}

/* ID生成 */
function gen_id($user_ip, $time): string {
	if (ID_CYCLE === '0') {
		return substr(crypt(md5($user_ip . ID_SEED), 'id'), -8);
	} elseif (ID_CYCLE === '1') {
		return substr(crypt(md5($user_ip . ID_SEED . date("Ymd", $time)), 'id'), -8);
	} elseif (ID_CYCLE === '2') {
		$week = ceil(date("d", $time) / 7);
		return substr(crypt(md5($user_ip . ID_SEED . date("Ym", $time) . $week), 'id'), -8);
	} elseif (ID_CYCLE === '3') {
		return substr(crypt(md5($user_ip . ID_SEED . date("Ym", $time)), 'id'), -8);
	} elseif (ID_CYCLE === '4') {
		return substr(crypt(md5($user_ip . ID_SEED . date("Y", $time)), 'id'), -8);
	} else {
		return substr(crypt(md5($user_ip . ID_SEED), 'id'), -8);
	}
}

//リダイレクト
function redirect($url): void {
	header("Location: {$url}");
	exit();
}

//シェアするserverの選択画面
function set_share_server(): void {
	global $servers,$blade,$dat, $en;

	//ShareするServerの一覧
	//｢"ラジオボタンに表示するServer名","snsのserverのurl"｣
	$servers = $servers ??
	[
		["X","https://x.com"],
		["Bluesky","https://bsky.app"],
		["Threads","https://www.threads.net"],
		["pawoo.net","https://pawoo.net"],
		["fedibird.com","https://fedibird.com"],
		["misskey.io","https://misskey.io"],
		["xissmie.xfolio.jp","https://xissmie.xfolio.jp"],
		["misskey.design","https://misskey.design"],
		["nijimiss.moe","https://nijimiss.moe"],
		["sushi.ski","https://sushi.ski"],
	];
	//設定項目ここまで
	$servers[]=["直接入力","direct"];//直接入力の箇所はそのまま。
	$dat['servers'] = $servers;

	$dat['encoded_t'] = filter_input_data('GET',"encoded_t");
	$dat['encoded_u'] = filter_input_data('GET',"encoded_u");
	$dat['sns_server_radio_cookie'] = (string)filter_input_data('COOKIE',"sns_server_radio_cookie");
	$dat['sns_server_direct_input_cookie'] = (string)filter_input_data('COOKIE',"sns_server_direct_input_cookie");

	$dat['admin_pass'] = null;
	$dat['token'] = get_csrf_token();
	//HTML出力
	echo $blade->run(SET_SHARE_SERVER, $dat);
}

//SNSへ共有リンクを送信
function post_share_server(): void {
	global $en;

	$sns_server_radio = (string)filter_input_data('POST',"sns_server_radio",FILTER_VALIDATE_URL);
	$sns_server_radio_for_cookie = (string)filter_input_data('POST',"sns_server_radio");//directを判定するためurlでバリデーションしていない
	$sns_server_radio_for_cookie = ($sns_server_radio_for_cookie === 'direct') ? 'direct' : $sns_server_radio;
	$sns_server_direct_input = (string)filter_input_data('POST',"sns_server_direct_input",FILTER_VALIDATE_URL);
	$encoded_t = (string)filter_input_data('POST',"encoded_t");
	$encoded_t = urlencode($encoded_t);
	$encoded_u = (string)filter_input_data('POST',"encoded_u");
	$encoded_u = urlencode($encoded_u);
	setcookie("sns_server_radio_cookie",$sns_server_radio_for_cookie, time() + (86400*30),"","",false,true);
	setcookie("sns_server_direct_input_cookie",$sns_server_direct_input, time() + (86400*30),"","",false,true);
	$share_url='';
	if($sns_server_radio) {
		$share_url = $sns_server_radio."/share?text=";
	} elseif($sns_server_direct_input) { //直接入力時
		$share_url = $sns_server_direct_input."/share?text=";
		if($sns_server_direct_input === "https://bsky.app") {
			$share_url = "https://bsky.app/intent/compose?text=";
		} elseif($sns_server_direct_input === "https://www.threads.net") {
			$share_url = "https://www.threads.net/intent/post?text=";
		}
	}
	if(in_array($sns_server_radio,["https://x.com","https://twitter.com"])) {
		// $share_url="https://x.com/intent/post?text=";
		$share_url = "https://twitter.com/intent/tweet?text=";
	} elseif($sns_server_radio === "https://bsky.app") {
		$share_url = "https://bsky.app/intent/compose?text=";
	}	elseif($sns_server_radio === "https://www.threads.net") {
		$share_url = "https://www.threads.net/intent/post?text=";
	}
	$share_url .= $encoded_t.'%20'.$encoded_u;
	$share_url = filter_var($share_url, FILTER_VALIDATE_URL) ? $share_url : '';
	if(!$share_url) {
		error($en ? 'Please select an SNS to share.' : 'SNSの共有先を選択してください。');
	}
	redirect($share_url);
}

//filter_input のラッパー関数
function filter_input_data(string $input, string $key, int $filter=0): mixed {
	// $_GETまたは$_POSTからデータを取得
	$value = null;
	if ($input === 'GET') {
			$value = $_GET[$key] ?? null;
	} elseif ($input === 'POST') {
			$value = $_POST[$key] ?? null;
	} elseif ($input === 'COOKIE') {
			$value = $_COOKIE[$key] ?? null;
	}

	// データが存在しない場合はnullを返す
	if ($value === null) {
			return null;
	}

	// フィルタリング処理
	switch ($filter) {
		case FILTER_VALIDATE_BOOLEAN:
			return  filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		case FILTER_VALIDATE_INT:
			return filter_var($value, FILTER_VALIDATE_INT);
		case FILTER_VALIDATE_URL:
			return filter_var($value, FILTER_VALIDATE_URL);
		default:
			return $value;  // 他のフィルタはそのまま返す
	}
}

//csrfトークンを作成
function get_csrf_token(): string {
	if (!isset($_SESSION)) {
		session_sta();
	}
	header('Expires:');
	header('Cache-Control:');
	header('Pragma:');
	if (!isset($_SESSION['token'])) {
		$_SESSION['token'] = hash('sha256', session_id(), false);
	}
	return $_SESSION['token'];
}
//csrfトークンをチェック
function check_csrf_token(): void {
	global $en;
	if(($_SERVER["REQUEST_METHOD"]) !== "POST"){
		error($en ? "This operation has failed." : "この操作は失敗しました。");
	}
	check_same_origin();

	session_sta();
	$token = (string)filter_input_data('POST','token');
	$session_token = isset($_SESSION['token']) ? (string)$_SESSION['token'] : '';
	if(!$token || !$session_token || !hash_equals($session_token,$token)) {
		error($en ? "CSRF token mismatch.\nPlease reload." : "CSRFトークンが一致しません。\nリロードしてください。");
	}
}

//session開始

function session_sta(): void {
	global $session_name;
	if (session_status() === PHP_SESSION_NONE) {
		$session_name = SESSION_NAME ?? 'reita_session';
		session_name($session_name);
		session_save_path(__DIR__ . '/session/');
		$https_only = (bool)($_SERVER['HTTPS'] ?? '');
		ini_set('session.use_strict_mode', 1);
		session_set_cookie_params(
			0,"","",$https_only,true
		);
		session_start();
		header('Expires:');
		header('Cache-Control:');
		header('Pragma:');
	}
}

//エスケープ
function h($str): string {
	if(zero_check($str)){
		return '0';
	}
	if(!$str){
		return '';
	}
	return htmlspecialchars($str,ENT_QUOTES,"utf-8",false);
}
//タブ除去
function t($str): string {
	if(zero_check($str)){
		return '0';
	}
	if(!$str){
		return '';
	}
	return str_replace("\t","",(string)$str);
}
//タグ除去
function s($str): string {
	if(zero_check($str)){
		return '0';
	}
	if(!$str){
		return '';
	}
	return strip_tags((string)$str);
}

// 0 または "0" かどうか
function zero_check($str): bool {
	return($str === 0 || $str === '0');
}

// ファイル存在チェック
function check_file ($path): void {
	$msg = initial_error_message();

	if (!is_file($path)){
		die(h($path) . $msg['001']);
	}
	if (!is_readable($path)){
		die(h($path) . $msg['002']);
	}
}

//PaintBBS NEOのpchかどうか調べる
function is_neo($src): bool {
	$fp = fopen("$src", "rb");
	$is_neo=(fread($fp,3) === "NEO");
	fclose($fp);
	return $is_neo;
}
//pchデータから幅と高さを取得
function get_pch_size($src): ?array {
	if(!$src){
		return null;
	}
	$fp = fopen("$src", "rb");
	$is_neo=(fread($fp,3) === "NEO");//ファイルポインタが3byte移動
	$pch_data=(string)bin2hex(fread($fp,5));
	fclose($fp);
	if(!$is_neo || !$pch_data){
		return null;
	}
	$width = null;
	$height = null;
	$w0 = hexdec(substr($pch_data,2,2));
	$w1 = hexdec(substr($pch_data,4,2));
	$h0 = hexdec(substr($pch_data,6,2));
	$h1 = hexdec(substr($pch_data,8,2));
	if( !is_numeric($w0) || !is_numeric($w1) || !is_numeric($h0) || !is_numeric($h1)){
		return null;
	}
	$width = (int)$w0 + ((int)$w1 * 256);
	$height = (int)$h0 + ((int)$h1 * 256);
	if( !$width || !$height) {
		return null;
	}
	return[(int)$width,(int)$height];
}

function initial_error_message(): array {
	global $en;
	$msg['001'] = $en ? ' does not exist.':'がありません。';
	$msg['002'] = $en ? ' is not readable.':'を読めません。';
	$msg['003'] = $en ? ' is not writable.':'を書けません。';
return $msg;
}

function check_same_origin(): void {
	global $en, $user_code;

	session_sta();
	$c_user_code = t(filter_input_data('COOKIE', 'user_code'));//user-codeを取得
	$session_user_code = isset($_SESSION['user_code']) ? t($_SESSION['user_code']) : "";
	if(!$c_user_code){
		error( $en ? 'Cookie check failed.':'Cookieが確認できません。');
	}
	if(!$user_code || ($user_code !== $c_user_code) && ($user_code !== $session_user_code)){
		error( $en ? "User code mismatch.":"ユーザーコードが一致しません。");
	}
	// POSTリクエストの場合のみHTTP_ORIGINをチェックする
	if(($_SERVER["REQUEST_METHOD"]) === "POST"){
		if(!isset($_SERVER['HTTP_ORIGIN']) || !isset($_SERVER['HTTP_HOST'])){
				error( $en ? 'Your browser is not supported. ':'お使いのブラウザはサポートされていません。');
		}
		if(parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST) !== $_SERVER['HTTP_HOST']){
				error( $en ? "The post has been rejected.":'拒絶されました。');
		}
}
}

function switch_tool($tool): string {
	switch($tool){
	case 'neo':
		$tool='PaintBBS NEO';
		break;
	case 'PaintBBS':
		$tool='PaintBBS';
		break;
	case 'shi-Painter':
		$tool='Shi-Painter';
		break;
	case 'chi':
		$tool='ChickenPaint';
		break;
	default:
		$tool='';
		break;
	}
	return $tool;
}

//sessionの確認
function admin_post_valid(): bool {
	global $second_pass, $en;
	session_sta();
	return isset($_SESSION['admin_post']) && ($second_pass && $_SESSION['admin_post'] === $second_pass);
}
function admin_del_valid(): bool {
	global $second_pass, $en;
	session_sta();
	return isset($_SESSION['admin_del']) && ($second_pass && $_SESSION['admin_del'] === $second_pass);
}
function user_del_valid(): bool {
	global $en;
	session_sta();
	return isset($_SESSION['user_del']) && ($_SESSION['user_del'] === 'user_del_mode');
}
