# リバーシ

Pythonで作成されたリバーシを参考に、フロントエンドをReact、バックエンドをPHP、保存先をSQLiteで作成したWeb版です。黒がプレイヤー、白がCOMです。

## 構成

- `reversi/`: 参考元のPython版
- `front/`: React + Vite の画面
- `back/`: PHP API とSQLite保存処理
- `back/data/reversi.sqlite`: 初回実行時に作成されるSQLiteデータベース

## 必要なもの

- Node.js
- PHP 8.1以上
- PHPのSQLite拡張

## 起動方法

1. バックエンドを起動します。

```bash
cd back
php -S 127.0.0.1:8001
```

2. 別のターミナルでフロントエンドを起動します。

```bash
cd front
npm install
npm run dev
```

3. ブラウザでViteが表示したURLを開きます。

開発中の `/api.php` リクエストはViteから `http://127.0.0.1:8001` に転送します。ポート8000で動いている別のAPIとの競合を避けるため、バックエンドには8001を使用します。Viteの設定変更後は `npm run dev` を再起動してください。以前 `VITE_API_BASE` を設定した場合は削除して既定の `/api.php` を使用してください。

## 遊び方

- 黒がプレイヤーです。
- 白はPython版の評価表を参考にしたCOMが自動で指します。
- 置けるマスには小さなガイドが表示されます。
- 置けるマスがない場合は自動でパスします。黒がパスした場合もCOMが続けて打ちます。
- 両者とも置けるマスがなくなると終了し、石数で勝敗が決まります。

## API

- `GET /api.php?action=new`: 新しい対局を作成
- `GET /api.php?action=state&id=1`: 対局状態を取得
- `POST /api.php?action=move`: 石を置く

`move` のリクエスト例:

```json
{
  "id": 1,
  "x": 2,
  "y": 3
}
```

## ビルド

```bash
cd front
npm run build
```

生成物は `front/dist/` に出力されます。
APIは画面と同じディレクトリの `./api.php` に接続します。

## レンタルサーバー用の配布物

```bash
cd front
npm install
npm run build:deploy
```

`release/reversi-日時/` に、ビルド済み画面・PHPを含む `public/`、保存先の `storage/`、設置手順の `README.md` を生成します。サーバーでNode.jsを動かす必要はありません。配布用ビルドでは、以前設定した `VITE_API_BASE` を削除して同一ディレクトリのAPIを使用してください。

アップロード先と保存先の設定は [レンタルサーバーへの設置手順](deploy/README.md) を参照してください。ドメイン直下とサブディレクトリの両方に対応します。
