# ec-api-sandbox

簡易的なECサイトのバックエンドAPIを試作・学習するためのリポジトリ。

## 技術スタック

- 言語：PHP
- フレームワーク：Laravel
- 役割：バックエンドAPIのみ

## API設計ルール

| メソッド | 用途 |
|----------|------|
| GET | 取得 |
| POST | 作成・ログイン・ログアウト・論理削除 |
| PUT | 更新 |

- PATCH は使用禁止（CORS設定による）
- 削除は `POST /リソース/{id}/delete` で論理削除（`is_invalid=1`）に統一

## アクター

| アクター | 説明 |
|----------|------|
| ゲスト | 未ログインユーザー |
| 一般会員 | ログイン済みユーザー |
| 出品者会員 | 承認制。商品の出品が可能 |
| 管理者 | サイト全体の管理 |

## エンドポイント一覧

### users（ユーザー）
```
POST   /users                        会員登録
POST   /users/login                  ログイン
POST   /users/logout                 ログアウト
GET    /users                        会員一覧（管理者）
GET    /users/{id}                   会員情報取得
PUT    /users/{id}                   会員情報更新
POST   /users/{id}/delete            アカウント削除
POST   /users/{id}/seller-apply      出品者申請
POST   /users/{id}/seller-approve    出品者承認（管理者）
POST   /users/{id}/seller-reject     出品者却下（管理者）
```

### products（商品）
```
GET    /products                     商品一覧・検索
GET    /products/{id}                商品詳細
POST   /products                     商品登録
PUT    /products/{id}                商品編集
POST   /products/{id}/delete         商品削除
```

### categories（カテゴリ）
```
GET    /categories                   カテゴリ一覧
POST   /categories                   カテゴリ登録（管理者）
PUT    /categories/{id}              カテゴリ編集（管理者）
POST   /categories/{id}/delete       カテゴリ削除（管理者）
```

### orders（注文）
```
POST   /orders                       注文作成（購入）
GET    /orders                       注文一覧
GET    /orders/{id}                  注文詳細
POST   /orders/{id}/cancel           注文キャンセル
POST   /orders/{id}/return           返品申請
POST   /orders/{id}/return-approve   返品承認（管理者）
POST   /orders/{id}/return-reject    返品却下（管理者）
```

### comments（コメント）
```
GET    /comments                     コメント一覧
GET    /comments/{id}                コメント詳細
POST   /comments                     コメント投稿
PUT    /comments/{id}                コメント編集
POST   /comments/{id}/delete         コメント削除
```

### reviews（評価）
```
GET    /reviews                      評価一覧
POST   /reviews                      評価投稿
PUT    /reviews/{id}                 評価編集
POST   /reviews/{id}/delete          評価削除
```

### addresses（住所）
```
GET    /users/{id}/address           住所取得
POST   /users/{id}/address           住所登録
PUT    /users/{id}/address           住所変更
POST   /users/{id}/address/delete    住所削除
```

### payments（決済方法）
```
GET    /users/{id}/payment           決済方法取得
POST   /users/{id}/payment           決済方法登録
PUT    /users/{id}/payment           決済方法変更
POST   /users/{id}/payment/delete    決済方法削除
```

### favorites（お気に入り）
```
GET    /users/{id}/favorites                      お気に入り一覧
POST   /users/{id}/favorites                      お気に入り登録
POST   /users/{id}/favorites/{product_id}/delete  お気に入り解除
```

### threads / messages（メッセージ）
```
GET    /threads                              スレッド一覧
GET    /threads/{thread_id}                  スレッド詳細
POST   /threads                              スレッド作成
GET    /threads/{thread_id}/messages         メッセージ一覧
GET    /threads/{thread_id}/messages/{id}    メッセージ詳細
POST   /threads/{thread_id}/messages         メッセージ送信
```

### shipments（配送）
```
GET    /orders/{order_id}/shipment           配送状況確認
POST   /orders/{order_id}/shipment           配送情報登録（出品者）
```

### coupons（クーポン）
```
GET    /coupons                      クーポン一覧
GET    /coupons/{id}                 クーポン詳細
POST   /coupons                      クーポン作成
PUT    /coupons/{id}                 クーポン編集
POST   /coupons/{id}/delete          クーポン削除
```

### reports（通報）
```
GET    /reports                      通報一覧（管理者）
GET    /reports/{id}                 通報詳細（管理者）
POST   /reports                      通報作成（会員）
POST   /reports/{id}/resolve         通報処理完了（管理者）
```

### notifications（お知らせ）
```
GET    /notifications                お知らせ一覧
GET    /notifications/{id}           お知らせ詳細
POST   /notifications                お知らせ作成（管理者）
PUT    /notifications/{id}           お知らせ編集（管理者）
POST   /notifications/{id}/delete    お知らせ削除（管理者）
```

### statistics（統計）
```
GET    /statistics                   統計情報取得（管理者）
GET    /statistics/sales             売上統計
GET    /statistics/users             会員統計
```
