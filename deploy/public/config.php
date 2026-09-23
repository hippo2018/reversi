<?php

declare(strict_types=1);

// 公開ディレクトリの外にある、PHPから書き込み可能なフォルダー。
// サブディレクトリに設置する場合などはサーバー上の絶対パスに変更してください。
define('REVERSI_DATA_DIR', dirname(__DIR__) . '/storage');
