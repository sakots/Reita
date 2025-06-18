# Reita

Reitaは（昔一回諦めた）描画にReactを使ってやろうというお絵かき掲示板スクリプトです。 Reactの絵板(eita)なのでReitaです。

![php](https://img.shields.io/badge/php-7.4-green.svg "php 7.4")
![php](https://img.shields.io/badge/php-8.x-green.svg "php 8.x")

![Last commit](https://img.shields.io/github/last-commit/sakots/Reita "Last commit")
![version](https://img.shields.io/github/v/release/sakots/Reita "version")
![Downloads](https://img.shields.io/github/downloads/sakots/Reita/total "Downloads")
![License](https://img.shields.io/github/license/sakots/Reita "License MIT")

[PaintBBS NEO](https://github.com/funige/neo/)、
[ChickenPaint Be](https://github.com/satopian/ChickenPaint_Be)
あたりが動けばいいかなと思う。

## 概要

zennに記載しております。-> [Reactでお絵かき掲示板（の表示部分）を作る話](https://zenn.dev/sakots/articles/c9765457ff90ce)

### ビジョンとして

- レンタルサーバーに設置できるようにしたいので、通信まわり、バックエンドはPHP
  - なかなかサーバーサイドでnode.jsが走ってるレンタルサーバーはないため。
- データベースはSQLite
  - 手軽にレンタルサーバーに設置するため。
- Reactを使うのはスレッド表示や検索表示のみ
  - お絵描きのhtml5+javascript(jQuery)に干渉しないようにするため。絵がバグる原因がReactとかイヤでしょ。
- サーバーのPHPはjsonを返す
  - 妥当。

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

## License

Reitaのオリジナルのコードは MIT License により提供されます。 バンドルされた第三者によるソフトウェアやファイルについては、それぞれのライセンスにより提供されます。

## 履歴

### [2025/06/18]

- バックエンド作り直し

### [2024/09/22]

- cssがうまく適用できるようになった

### [2024/09/19]

- css切り替えをいったんオミット
- 画像の描写ができない

### [2024/05/03]

- addInfoの出力に成功
- css適用までできた
- スレッドの描写に成功

### [2024/05/02]

- フッター
- .env

### [2024/04/30]

- ヘッダー

### [2024/04/29]

- initでエラーのとき動かさないようにできた
- フロントエンドにLinkify追加 `npm install linkifyjs linkify-react linkify-html linkify-plugin-hashtag`
- ヘッダー途中まで

### [2024/04/28]

- フロントエンドのルート呼び出し

### [2024/04/21]

- バックエンドでconfigを呼び出すAPIできた
- バックエンドでスレッドを呼び出すAPIできた

### [2024/04/20]

- フロントエンドディレクトリ作成
- 初期画面のバックエンドができた
- フロントエンドにreact-router-dom追加 `npm install react-router-dom`

### [2024/04/12]

- フロントエンドにAxios追加 `npm install axios`

### [2024/04/06]

- フロントエンドをReact+Viteに `npm create vite@latest`

### [2024/04/05]

- 一旦リセット。ReactとTypescriptを勉強してきたので。
- `backend`ディレクトリ作成。コンフィグと仮データベース作成。

### [2022/08/27]

- なんかできそうなので再開
  - 参考：[ReactとFirebaseで掲示板作ってみた。ログインなしで誰でも書き込めます](http://shincode.info/2021/10/04/bbs-with-react-and-firebase/)
- まずはsqliteの簡単な掲示板を作る

### [2021/12/03]

- htmlが記述してあるphpファイルの中に直接jsxを埋め込んで、ってやるのしか思いつかなかったからコレBladeのままとかわんなくね？ってなったので一旦凍結。

### [2021/11/29]

- adminモードにバグがあったので修正
- テーマをもう少し

### [2021/11/29] v0.0.0

- age/sage処理に不具合があったの修正
- テーマの色修正
- その他

- テーマにCDNリンク埋め込む形にしようかなと
- Reactにしやすくするため、まずデータベースの形を変更した
- 「そろそろ消えます」はとりあえず要らない気がしたので予約だけして実装を消した

### [2021/11/10]

- dev

### [2021/10/25]

- リポジトリ生やした
