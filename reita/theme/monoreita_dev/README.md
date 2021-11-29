# ReitaのReactで使っているbabelのコンパイル

参照[既存のウェブサイトに React を追加する](https://ja.reactjs.org/docs/add-react-to-a-website.html)

## 環境構築

### node.jsのインストール

略

### jsxコンパイラのインストール

テーマディレクトリでターミナルを開いて、

```tarminal
npm init -y
npm install babel-cli@6 babel-preset-react-app@3
```

## コンパイル

テーマディレクトリでターミナルを開いて、

```
npx babel --watch lib --out-dir js --presets react-app/prod
```

jsxファイルが変更されるたびに、コンパイルされます。
