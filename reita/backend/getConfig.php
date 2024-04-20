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

//BladeOne v4.12
include(__DIR__ . '/BladeOne/lib/BladeOne.php');

use eftec\bladeone\BladeOne;
$views = __DIR__ . '/theme/'; // テンプレートフォルダ
$cache = __DIR__ . '/cache'; // キャッシュフォルダ

$blade = new BladeOne($views, $cache, BladeOne::MODE_AUTO); // MODE_DEBUGだと開発モード MODE_AUTOが速い。
$blade->pipeEnable = true; // パイプのフィルターを使えるようにする

//タイムゾーン設定
date_default_timezone_set(DEFAULT_TIMEZONE);

//絶対パス取得
$path = realpath("./");
$imgPath = realpath("./") . '/backend/' . IMG_DIR;
$tempPath = realpath("./") . '/backend/' . TEMP_DIR;

define('PATH', $path);
define('IMG_PATH', $imgPath);
define('TEMP_PATH', $tempPath);

$config = [];

$config['imgPath'] = IMG_DIR;

$config['neoDir'] = NEO_DIR;
$config['chickenDir'] = CHICKEN_DIR;

$config['ver'] = REITA_VER;
$config['base'] = BASE;
$config['boardTitle'] = TITLE;
$config['home'] = HOME;
$config['paintDefaultWidth'] = PAINT_DEF_W;
$config['paintDefaultHeight'] = PAINT_DEF_H;
$config['paintMaxWidth'] = PAINT_MAX_W;
$config['paintMaxHeight'] = PAINT_MAX_H;

$config['maxNameLength'] = MAX_NAME;
$config['maxEmailLength'] = MAX_EMAIL;
$config['maxSubjectLength'] = MAX_SUB;
$config['maxUrlLength'] = MAX_URL;
$config['maxCommentLength'] = MAX_COM;

$config['useChicken'] = USE_CHICKENPAINT;

$config['selectPalettes'] = USE_SELECT_PALETTES;
$config['palletsDat'] = $palletsData;

$config['displayId'] = DISPLAY_ID;
$config['updateMark'] = UPDATE_MARK;
$config['useRepSubject'] = USE_RE_SUBJECT;

$config['useAnime'] = USE_ANIME;
$config['defaultAnime'] = DEF_ANIME;
$config['useContinue'] = USE_CONTINUE;
$config['newPostNoPassword'] = !CONTINUE_PASS;

$config['useName'] = USE_NAME;
$config['useComment'] = USE_COM;
$config['useSubject'] = USE_SUB;

$config['addInfo'] = $addInfo;

$config['displayPaintTime'] = DISPLAY_PAINT_TIME;

$config['shareButton'] = SHARE_BUTTON;

$config['useHashtag'] = USE_HASHTAG;

$configData = json_encode($config, JSON_UNESCAPED_UNICODE);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
echo($configData);