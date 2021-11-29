# Reita

Reitaは描画にReactを使ってやろうというお絵かき掲示板スクリプトです。 Reactの絵板(eita)なのでReitaです。

[![Maintenance](https://img.shields.io/maintenance/yes/2021.svg)]()
[![composer](https://img.shields.io/badge/composer-%3E1.6-blue.svg)]()
[![php](https://img.shields.io/badge/php->5.6-green.svg)]()
[![php](https://img.shields.io/badge/php-7.x-green.svg)]()
[![php](https://img.shields.io/badge/php-8.0-green.svg)]()

[PaintBBS NEO](https://github.com/funige/neo/)、
[ChickenPaint](https://github.com/thenickdude/chickenpaint/)
あたりが動けばいいかなと思う。

## 概要

zennに記載しております。-> [Reactでお絵かき掲示板（の表示部分）を作る話](https://zenn.dev/sakots/articles/c9765457ff90ce)

### ビジョンとして

- レンタルサーバーに設置できるようにしたいので、通信まわりはPHP
  - なかなかサーバーサイドでnode.jsが走ってるレンタルサーバーはないため。
- データベースはSQLite
  - 手軽にレンタルサーバーに設置するため。
- Reactを使うのはスレッド表示や検索表示のみ
  - お絵描きのhtml5+javascript(jQuery)に干渉しないようにするため。絵がバグる原因がReactとかイヤでしょ。
- サーバーのPHPはjsonを返す
  - 妥当。
- 初期設定をwebブラウザ上でできるようにする
  - 管理者パスワードも暗号化できる。初期設定用パスワードを設定し、それが一致すれば設定が開始できる形が妥当？

## サンプルとサポート

まだない。形ができたら作る予定。

## 同梱のパレットについて

`p_PCCS.txt`(PCCS:日本色研配色体系パレット)は、
[色彩とイメージの情報サイト IROUE](https://tee-room.info/color/database.html) を参考に、
`p_munsellHVC.txt`(マンセルHV/Cパレット)は、
[マンセル表色系とRGB値](http://k-ichikawa.blog.enjoy.jp/etc/HP/js/Munsell/MSL2RGB0.html) を参照して作成いたしました。

パレットデータの再配布等自由にしていただいて構いません。
ただの文字列なので著作権の主張はしませんが、書くのにそれなりの苦労はしましたので、
再配布の際はどこかに私の名前を書いていただければと思います。

## 履歴

### [2021/11/29]

- テーマにCDNリンク埋め込む形にしようかなと
- Reactにしやすくするため、まずデータベースの形を変更した
- 「そろそろ消えます」はとりあえず要らない気がしたので予約だけして実装を消した

### [2021/11/10]

- dev

### [2021/10/25]

- リポジトリ生やした
