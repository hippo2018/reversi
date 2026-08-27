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
php -S 127.0.0.1:8000
```

2. 別のターミナルでフロントエンドを起動します。

```bash
cd front
npm install
npm run dev
```

3. ブラウザでViteが表示したURLを開きます。

## 遊び方

- 黒がプレイヤーです。
- 白はPython版の評価表を参考にしたCOMが自動で指します。
- 置けるマスには小さなガイドが表示されます。
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
