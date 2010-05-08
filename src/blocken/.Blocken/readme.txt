/**
 * readme.txt
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: readme.txt 27 2009-04-25 06:39:17Z sigmax $
 */

■インストール方法(Windows版)
1. PHPをインストール
    http://php.net/
    からWindows版のインストーラかzipパッケージをダウンロードしてインストールする

2. 以下の拡張機能をロードする
    // php.iniでコメントアウトされている以下の行を有効にする
    // その他、MySQLやLDAPを利用する場合はその行も有効にする
    ;extension=php_bz2.dll
    extension=php_bz2.dll
    ;extension=php_curl.dll
    extension=php_curl.dll
    ;extension=php_gd2.dll
    extension=php_gd2.dll
    ;extension=php_mbstring.dll
    extension=php_mbstring.dll
    ;extension=php_tidy.dll
    extension=php_tidy.dll


■インストール方法(Linux版)
1. PHPをインストール
    yum install php
    yum install php-devel
    yum install php-curl
    yum install php-gd
    yum install php-mbstring
    yum install php-tidy
    yum install php-xml
    // その他、MySQLやLDAPを利用する場合はそのライブラリもインストールする


■インストール方法(共通)
1. PEARライブラリをインストール
    // 今インストールされているものをアップグレード
    pear upgrade-all
    // 各種PEARライブラリのパッケージをインストール
    pear install --alldeps Auth
    pear install --alldeps Auth_HTTP
    pear install --alldeps Cache
    pear install --alldeps File_Archive
    pear install --alldeps File_Passwd
    pear install --alldeps HTML_Template_Sigma
    pear install --alldeps HTTP_Session-beta
    pear install --alldeps Log
    pear install --alldeps MDB2
    pear install --alldeps Net_Curl
    pear install --alldeps Net_UserAgent_Mobile-beta
    pear install --alldeps PHPDocumentor
    pear install --alldeps Text_Diff
    // MySQLなどデータベースを利用する場合はそのPEARライブラリもインストールする
    pear install --alldeps MDB2_Driver_mysql

2. PHPDocumentorの日本語対応(※必須ではない)
    pear/data/PhpDocumentor/phpDocumentor/Converters/HTML/Smarty/templates/PHP/templates/
    の下にある header.tpl にMETAタグ等を必要に応じて追加する


■環境設定
1. Apacheの設定でhtaccess機能が有効になっているかチェック

2. php.ini の設定を確認する
    mbstring.encoding_translation = Off
    mbstring.http_input           = pass

3. .htaccess の編集
    「auto_prepend_file」のパスを設定する
    ※auto_prepend_file が設定できない場合は各ページの先頭で prepend.php を読込ませてください
    <?php include_once '{BlockenPath}/.Blocken/prepend.php'; ?>

4. Jade.php の編集
    「BLOCKEN_PATH」のパスを設定する
    Jade.php に実行権限を与える

5. config.php の編集
    設定が必須なのは BLOCKEN_ROOT_PATH のみ
    あとは開発環境などに応じて編集する

6. 以下の * ディレクトリに書込権限を与える
    blocken
    ├─.Blocken
    │  ├─html_cache *
    │  ├─log *
    │  ├─sigma_cache *
    │  └─tmp *
    ├─doc *
    └─sample
        ├─dynamic
        │  └─cache *
        │      ├─d *
        │      ├─e *
        │      └─s *
        └─mymenu
            └─cache *
                ├─d *
                ├─e *
                └─s *


■インストールチェック
    ブラウザから以下のファイルにアクセスするとインストールの状況を確認できる
    blocken_check.html


■使用方法
1. 管理モードをつかってみる
    サイト内で以下のパラメータを付加すると管理画面にログインできる
    (デフォルトユーザとパスワードは hoge/hoge)
    ※ただしBlockenインストールチェッカー上では動作しない
    ?_bin=--help

2. コマンドラインでつかってみる
    php Jade.php または ./Jade.php

・・・続きはウェブで
