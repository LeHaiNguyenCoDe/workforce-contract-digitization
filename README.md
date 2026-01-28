# Workforce Contract Digitization

![Project Status](https://img.shields.io/badge/Project_Status-Active_Development-success)
![Version](https://img.shields.io/badge/Version-2.0.0-blue)
![License](https://img.shields.io/badge/License-Private-red)

## プロジェクト概要

**Workforce Contract Digitization**は、**Eコマース（電子商取引）**と**企業リソース計画（ERP）**の力を融合させた包括的なデジタルトランスフォーメーションプラットフォームです。

本システムは、フロントエンドとバックエンドを完全に分離した**Decoupled Monolith（分離型モノリス）**アーキテクチャに基づいて構築されており、柔軟な拡張性、高いパフォーマンス、そして最適なユーザー体験を保証します。単なる商品の販売にとどまらず、在庫管理、人事、財務から自動化されたカスタマーケアに至るまで、企業の内部運営プロセス全体をデジタル化します。

## 主な機能

### Eコマース & マーケットプレイス
*   **モダンなショッピング体験**: スマート検索、多階層商品フィルター、AIによる商品レコメンデーション。
*   **チェックアウトプロセス**: 手順の最適化、多様な決済ゲートウェイ（VNPAY, VietQR）対応、送料の自動計算。
*   **プロモーション管理**: バウチャー、フラッシュセール、会員ランク別の割引設定。

### 企業リソース計画 (ERP)
*   **倉庫管理 (WMS)**: リアルタイム在庫追跡、入出庫管理、安全在庫アラート。
*   **注文管理 (OMS)**: クローズドループの注文処理プロセス（注文 -> 確認 -> 梱包 -> 配送 -> 照合）。
*   **顧客関係管理 (CRM)**: 360度顧客プロファイル、購入履歴、顧客セグメンテーション、ロイヤリティポイント。
*   **財務 & 会計**: 売上追跡、債権債務管理、キャッシュフロー管理。

### リアルタイム & インタラクション
*   **スマートチャットシステム**: WebSocket (Laravel Reverb) を介したゲストとサポートスタッフの即時チャット。
*   **通知システム**: 注文状況、メッセージ、重要なイベントの即時更新通知。

## 技術スタック & アーキテクチャ

### バックエンド (`/web`)
強力な**ロジックエンジン**として機能し、複雑な業務処理とデータセキュリティを担います。
*   **Core Framework**: Laravel 11.x
*   **Language**: PHP 8.2+
*   **Database**: MySQL 8.0
*   **Real-time Server**: Laravel Reverb (高性能 WebSocket)
*   **Queue System**: Redis (高速バックグラウンド処理)
*   **API Standard**: RESTful API / OpenAPI 3.0 Specification

### フロントエンド (`/FE`)
スムーズなシングルページアプリケーション (SPA) で、極めて高速な応答速度を実現します。
*   **Framework**: Vue 3 (Composition API)
*   **Language**: TypeScript (Strongly typed)
*   **Build Tool**: Vite 5 (高速HMR & ビルド最適化)
*   **State Management**: Pinia
*   **UI System**: SCSS, Bootstrap Vue Next

## ソースコードへのアクセス

システムは専門化されたモジュールごとに科学的に構成されています。以下は詳細なディレクトリ構造図です：

<details open>
<summary><b>1. フロントエンド構造 (`FE/src`)</b></summary>

```
FE/src/
├── assets/          # 静的アセット (Styles, Fonts, Images)
├── components/      # グローバルコンポーネント: BaseButton, BaseInput...
├── layouts/         # レイアウト: Admin, Auth, Landing
├── modules/         # -- ドメインモジュール --
│   ├── admin/       # [管理パネル]
│   │   ├── chat/        # 管理者用チャットインターフェース
│   │   ├── dashboard/   # メインダッシュボード (分析)
│   │   ├── erp/         # -- ERP サブモジュール --
│   │   │   ├── finance/          # 財務・収益管理
│   │   │   ├── customers/        # 顧客プロファイル
│   │   │   ├── ...               # (Expenses, Points, Returns...)
│   │   ├── orders/      # 注文一覧・詳細
│   │   ├── products/    # 商品編集・管理
│   │   ├── users/       # 従業員・ユーザー管理
│   │   └── warehouses/  # 在庫・出荷管理
│   ├── landing/     # [カスタマーポータル]
│   │   ├── home/        # トップページ
│   │   ├── products/    # 商品一覧・詳細ページ (PDP)
│   │   ├── cart/        # ショッピングカート
│   │   ├── checkout/    # 決済プロセス
│   │   └── profile/     # ユーザーアカウント情報
│   └── marketplace/ # [独立型マーケットプレイス]
├── router/          # ルーティング定義とガード
└── stores/          # Pinia: Auth, Cart, Toast...
```
</details>

<details>
<summary><b>2. バックエンド構造 (`web/app`)</b></summary>

```
web/app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/             # ベースコントローラー
│   │   ├── Modules/
│   │   │   ├── Admin/       # 管理者用コントローラー
│   │   │   ├── Landing/     # 公開用コントローラー
│   │   │   └── Auth/        # 認証コントローラー
│   └── Middleware/          # ミドルウェア (Role, Locale...)
├── Models/                  # Eloquentエンティティ: Order, User, Product...
├── Services/                # -- サービス層 (ビジネスロジック) --
│   ├── Admin/               # [管理者向けロジック]
│   │   ├── FinanceService.php      # 収益計算
│   │   ├── OrderService.php        # 注文フロー処理
│   │   ├── ProductService.php      # 商品CRUD
│   │   ├── WarehouseService.php    # あらゆる在庫ロジック
│   │   └── ...
│   ├── Core/                # システムコア: FileUpload, Logger
│   ├── Marketing/           # プロモーション・キャンペーンロジック
│   ├── Payment/             # 決済ゲートウェイ統合 (VNPAY, Momo)
│   └── VietQR/              # QRコード生成
└── Events/                  # イベント・ブロードキャスト
```
</details>

## セットアップ & 導入ガイド

ローカル開発環境を構築するには、以下の手順に従ってください：

### 前提条件
*   **PHP**: >= 8.2 (必須)
*   **Node.js**: >= 18.x
*   **Composer**: 最新バージョン
*   **MySQL**: >= 8.0
*   **Redis**: (Queue & Cacheの最適化に推奨)

### 手順 1: バックエンドの構築
```bash
cd web
# 1. PHPライブラリのインストール
composer install

# 2. 環境設定
cp .env.example .env
# -> 注意: .envファイル内の DB_DATABASE, DB_PASSWORD を更新してください

# 3. データベースとキーの初期化
php artisan key:generate
php artisan migrate --seed  # テーブル作成とサンプルデータ投入 (Admin, Settings)

# 4. サーバーの起動
composer run dev
# このコマンドは Laravel Server (8000), Queue Worker, Reverb (8080) を並列で起動します
```

### 手順 2: フロントエンドの構築
```bash
cd FE
# 1. JSライブラリのインストール
yarn install  # または npm install

# 2. 環境設定
cp .env.example .env
# -> VITE_API_BASE_URL=http://localhost:8000/api/v1 (ローカルバックエンドを指定)

# 3. 開発サーバーの起動
yarn dev
```

**アプリケーションへのアクセス**:
*   **Frontend**: `http://localhost:3000`
*   **API Documentation**: `http://localhost:8000/docs` (Swagger導入済みの場合)

## 開発ワークフロー

1.  **ブランチ戦略**: 新機能 (`feat/feature-name`) や バグ修正 (`fix/bug-name`) は、必ず `dev` ブランチから作成してください。
2.  **コミット基準**: Conventional Commits に従ってください (例: `feat: add user login`, `fix: update cart calculation`)。
3.  **プルリクエスト**: Tech Lead によるコードレビューを経て、`dev` ブランチへマージされます。

---
**Workforce Contract Digitization** — *経営管理の高度化と業務運営の最適化*