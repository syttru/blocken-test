<?php
/**
 * <b>サンプルブロック</b>
 *
 * PHP versions 4 and 5
 *
 *
 * <b>内容説明:</b>
 *
 * <pre>
 * 開発者達のサンプルブロック
 * ここを参考にしてブロックを作成してください
 * </pre>
 *
 *
 * <b>ブロック設定パラメータ説明:</b>
 *
 * <pre>
 * [共通パラメータ]
 * type:         dynamic 固定
 * name:         ブロック名
 * cache_expire: キャッシュ時間(秒) ※0(キャッシュなし、デフォルト) -1(無制限)
 * </pre>
 *
 *
 * <b>使用例:</b>
 *
 * <code>
 * <block type="dynamic" name="rss_sample" />
 * </code>
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @version   $Id: rss_sample.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

/**
 * @param  object &$objTemplate HTMLテンプレートオブジェクト
 * @param  array  &$aryPear     PEARオブジェクト
 * @param  array  &$aryParam    GET,POSTパラメータ
 * @param  array  &$aryArgs     ブロック設定パラメータ
 * @return string               HTMLブロック
 */
function _rss_sample( &$objTemplate, &$aryPear, &$aryParam, &$aryArgs )
{
    $objTemplate->setVariable( 'lastBuildDate', date( 'r' ) );

    $objTemplate->setCurrentBlock( 'rss' );
    $objTemplate->setVariable( 'title', 'RSS配信' );
    $objTemplate->setVariable( 'link', 'http://example.com/rss/' );
    $objTemplate->setVariable( 'pubDate', date( 'r' ) );
    $objTemplate->parseCurrentBlock();

    return $objTemplate->get();
}
?>
