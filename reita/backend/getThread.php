<?php
//--------------------------------------------------
//  おえかきけいじばん「Reita」
//  by sakots https://oekakibbs.moe/
//--------------------------------------------------

//通常表示モード

//設定の読み込み
require(__DIR__ . '/config.php');

$displayReply = DSP_RES;
$pagingDefault = PAGE_DEF;

//データベース接続PDO
define('DB_PDO', 'sqlite:' . DB_NAME . '.db');

//ログの行数が最大値を超えていたら削除
function logDelete()
{
	//オーバーした行の画像とスレ番号を取得
	try {
		$db = new PDO(DB_PDO);
		$sql = "SELECT * FROM tlog ORDER BY tid LIMIT 1";
		$msgs = $db->prepare($sql);
		$msgs->execute();
		$msg = $msgs->fetch();

		$del_tid = (int)$msg["tid"]; //消す行のスレ番号
		$msgPic = $msg["picfile"]; //画像の名前取得できた
		//画像とかの削除処理
		if (is_file(IMG_DIR . $msgPic)) {
			$msgDat = pathinfo($msgPic, PATHINFO_FILENAME); //拡張子除去
			if (is_file(IMG_DIR . $msgDat . '.png')) {
				unlink(IMG_DIR . $msgDat . '.png');
			}
			if (is_file(IMG_DIR . $msgDat . '.jpg')) {
				unlink(IMG_DIR . $msgDat . '.jpg'); //一応jpgも
			}
			if (is_file(IMG_DIR . $msgDat . '.pch')) {
				unlink(IMG_DIR . $msgDat . '.pch');
			}
			if (is_file(IMG_DIR . $msgDat . '.dat')) {
				unlink(IMG_DIR . $msgDat . '.dat');
			}
			if (is_file(IMG_DIR . $msgDat . '.chi')) {
				unlink(IMG_DIR . $msgDat . '.chi');
			}
		}

		//レスあれば削除
		//カウント
		$sql = "SELECT COUNT(*) as count FROM tlog WHERE parent = $del_tid";
		$countRes = $db->query("$sql");
		$countRes = $countRes->fetch();
		$logCount = $countRes["count"];
		//削除
		if ($logCount !== 0) {
			$delRes = "DELETE FROM tlog WHERE parent = $del_tid";
			$db->exec($delRes);
		}
		//スレ削除
		$delThreads = "DELETE FROM tlog WHERE tid = $del_tid";
		$db->exec($delThreads);

		$sql = null;
		$delThreads = null;
		$msg = null;
		$del_tid = null;
		$db = null; //db切断
	} catch (PDOException $e) {
		echo "DB接続エラー:" . $e->getMessage();
	}
}

/* '>'色設定 */
function quote($quote)
{
	$quote = preg_replace("/(^|>)((&gt;|＞)[^<]*)/i", "\\1" . "<span class="."res".">" . "\\2" . "</span>", $quote);
	return $quote;
}

/* オートリンク */
function auto_link($proto)
{
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
function hashtag_link($hashtag)
{
	$pattern = "/(?:^|[^ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9&_\/]+)[#＃]([ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z]+[ｦ-ﾟー゛゜々ヾヽぁ-ヶ一-龠ａ-ｚＡ-Ｚ０-９a-zA-Z0-9_]*)/u";
	$replace = " <a href=\"search.php&amp;tag=tag&amp;search=\\1\">#\\1</a>";
	$hashtag = preg_replace($pattern, $replace, $hashtag);
	return $hashtag;
}

$threads = [];

//ログ行数オーバー処理
//スレ数カウント
try {
  $db = new PDO(DB_PDO);
  $sql = "SELECT SUM(thread) as cnt FROM tlog";
  $threadCountSql = $db->query("$sql");
  $threadCountSql = $threadCountSql->fetch();
  $threadCount = $threadCountSql["cnt"];
} catch (PDOException $e) {
  echo "DB接続エラー:" . $e->getMessage();
}
if ($threadCount > LOG_MAX_T) {
  logDelete();
}

//古いスレのレスボタンを表示しない
$elapsedTime = ELAPSED_DAYS * 86400; //デフォルトの1年だと31536000
$nowTime = time(); //いまのunixタイムスタンプを取得
//あとはテーマ側で計算する
$threads['nowTime'] = $nowTime;
$threads['elapsedTime'] = $elapsedTime;

//ページング
try {
  $db = new PDO(DB_PDO);
  $sql = "SELECT SUM(thread) as cnt FROM tlog WHERE invz=0";
  $threadsCountSql = $db->query("$sql");
  $threadsCountSql = $threadsCountSql->fetch();
  $count = $threadsCountSql["cnt"];
  if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page = $_GET['page'];
    $page = max($page, 1);
  } else {
    $page = 1;
  }
  $start = $pagingDefault * ($page - 1);

  //最大何ページあるのか
  $maxPage = floor($count / $pagingDefault) + 1;
  //最後にスレ数0のページができたら表示しない処理
  if (($count % $pagingDefault) == 0) {
    $maxPage = $maxPage - 1;
    //ただしそれが1ページ目なら困るから表示
    $maxPage = max($maxPage, 1);
  }
  $threads['maxPage'] = $maxPage;

  //リンク作成用
  $threads['nowPage'] = $page;
  $p = 1;
  $pp = array();
  $paging = array();
  while ($p <= $maxPage) {
    $paging[($p)] = compact('p');
    $pp[] = $paging;
    $p++;
  }
  $threads['paging'] = $paging;
  $threads['pp'] = $pp;

  $threads['back'] = ($page - 1);
  $threads['next'] = ($page + 1);

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
  $posts->bindValue(2, $pagingDefault, PDO::PARAM_INT);
  $posts->execute();

  $ko = array();
  $oya = array();

  $i = 0;
  $j = 0;
  while ($i < PAGE_DEF) {
    $bbsLine = $posts->fetch();
    if (empty($bbsLine)) {
      break;
    } //スレがなくなったら抜ける
    $oyaId = $bbsLine["tid"]; //スレのtid(親番号)を取得
    $sqlRes = "SELECT * FROM tlog WHERE parent = $oyaId AND invz=0 AND thread=0 ORDER BY comid ASC";
    //レス取得
    $postsRes = $db->query($sqlRes);
    $j = 0;
    $flag = true;
    while ($flag == true) {
      $_pchExt = pathinfo($bbsLine['pchfile'], PATHINFO_EXTENSION);
      if ($_pchExt === 'chi') {
        $bbsLine['pchfile'] = ''; //ChickenPaintは動画リンクを出さない
      }
      $res = $postsRes->fetch();
      if (empty($res)) { //レスがなくなったら
        $bbsLine['ressu'] = $j; //スレのレス数
        $bbsLine['res_d_su'] = $j - DSP_RES; //スレのレス省略数
        if ($j > DSP_RES) { //スレのレス数が規定より多いと
          $bbsline['rflag'] = true; //省略フラグtrue
        } else {
          $bbsline['rflag'] = false; //省略フラグfalse
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
      $res['com'] = nl2br($res['com'], false);;
      //引用の色
      $res['com'] = quote($res['com']);
      //日付をUNIX時間に変換して設定どおりにフォーマット
      $res['created'] = date(DATE_FORMAT, strtotime($res['created']));
      $res['modified'] = date(DATE_FORMAT, strtotime($res['modified']));
      $oya[$i][] = $res;
      $j++;
    }
    // http、https以外のURLの場合表示しない
    if (!filter_var($bbsLine['a_url'], FILTER_VALIDATE_URL) || !preg_match('|^https?://.*$|', $bbsline['a_url'])) {
      $bbsLine['a_url'] = "";
    }
    $bbsLine['com'] = htmlspecialchars($bbsLine['com'], ENT_QUOTES | ENT_HTML5);

    //オートリンク
    if (AUTOLINK) {
      $bbsLine['com'] = auto_link($bbsLine['com']);
    }
    //ハッシュタグ
    if (USE_HASHTAG) {
      $bbsLine['com'] = hashtag_link($bbsLine['com']);
    }
    //空行を縮める
    $bbsLine['com'] = preg_replace('/(\n|\r|\r\n){3,}/us', "\n\n", $bbsLine['com']);
    //<br>に
    $bbsLine['com'] = nl2br($bbsLine['com'], false);
    //引用の色
    $bbsLine['com'] = quote($bbsLine['com']);
    //日付をUNIX時間にしたあと整形
    $bbsLine['past'] = strtotime($bbsLine['created']); // このスレは古いので用
    $bbsLine['created'] = date(DATE_FORMAT, strtotime($bbsLine['created']));
    $bbsLine['modified'] = date(DATE_FORMAT, strtotime($bbsLine['modified']));
    $oya[] = $bbsLine;
    $i++;
  }

  $threads['oya'] = $oya;
  $threads['dsp_res'] = DSP_RES;
  $threads['path'] = IMG_DIR;

  //書き出し

  $threads = json_encode($threads, JSON_UNESCAPED_UNICODE);

  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json");
  echo($threads);
  $db = null; //db切断
} catch (PDOException $e) {
  echo "DB接続エラー:" . $e->getMessage();
}
