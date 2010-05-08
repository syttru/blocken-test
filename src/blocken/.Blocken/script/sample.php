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
 *
 * [専用パラメータ]
 * default <b>*</b>:  入力フォームに初期表示される値
 * maxcount <b>*</b>: ループ文を繰り返す回数
 *
 * <b>*</b> は必須パラメータ
 * </pre>
 *
 *
 * <b>使用例:</b>
 *
 * <code>
 * <block type="dynamic" name="sample" default="@hoge.com" maxcount="10" />
 * </code>
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @version   $Id: sample.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

/**
 * @param  object &$objTemplate HTMLテンプレートオブジェクト
 * @param  array  &$aryPear     PEARオブジェクト
 * @param  array  &$aryParam    GET,POSTパラメータ
 * @param  array  &$aryArgs     ブロック設定パラメータ
 * @return string               HTMLブロック
 */
function _sample( &$objTemplate, &$aryPear, &$aryParam, &$aryArgs )
{
    if ( ! isset( $aryParam[ 'sample' ] ) )
    {
        return $objTemplate->get();
    }

    for ( $i = 1; $i <= 7; $i++ )
    {
        $objTemplate->hideBlock( "sample{$i}" );
    }

    $objTemplate->touchBlock( "sample{$aryParam[ 'sample' ]}" );

    switch ( $aryParam[ 'sample' ] )
    {
        // sample1
        case '1':
            $objTemplate->setVariable( 'sample', 'サンプル' );

            $objTemplate->setVariable( 'b_kana',  'アイウエオ' );
            $objTemplate->setVariable( 'a_kana',  'アイウエオ', 'k' );

            $objTemplate->setVariable( 'b_date',  time() );
            $objTemplate->setVariable( 'a_date',  time(), 'd', 'Y年m月d日' );

            $objTemplate->setVariable( 'b_money', 1234.567 );
            $objTemplate->setVariable( 'a_money', 1234.567, 'm', 2 );

            $objTemplate->setVariable( 'b_url',   'アイウエオ' );
            $objTemplate->setVariable( 'a_url',   'アイウエオ', 'u' );

            $objTemplate->setVariable( 'b_nl2br', "アイウ\nエオ" );
            $objTemplate->setVariable( 'a_nl2br', "アイウ\nエオ", 'b' );
            break;

        // sample2
        case '2':
            break;

        // sample3
        case '3':
            for ( $i = 1; $i <= 10; $i++ )
            {
                $objTemplate->setCurrentBlock( 'loop' );
                $objTemplate->setVariable( 'count', $i );
                $objTemplate->parseCurrentBlock();
            }
            break;

        // sample4
        case '4':
            if ( ! isset( $aryParam[ 'anser' ] ) )
            {
                $objTemplate->touchBlock( 'question' );
                $objTemplate->hideBlock( 'anser' );
            }
            else
            {
                $objTemplate->touchBlock( 'anser' );
                $objTemplate->hideBlock( 'question' );

                if ( 'リキシマン' == $aryParam[ 'anser' ] )
                {
                    $objTemplate->touchBlock( 'true' );
                    $objTemplate->hideBlock( 'false' );
                }
                else
                {
                    $objTemplate->touchBlock( 'false' );
                    $objTemplate->hideBlock( 'true' );
                }
            }
            break;

        // sample5
        case '5':
            for ( $i = 1; $i <= 10; $i++ )
            {
                $objTemplate->setCurrentBlock( 'loopif' );
                $objTemplate->setVariable( 'count', $i );
                if ( 5 == $i )
                {
                    $objTemplate->touchBlock( 'abnormal' );
                    $objTemplate->hideBlock( 'normal' );
                }
                else
                {
                    $objTemplate->touchBlock( 'normal' );
                    $objTemplate->hideBlock( 'abnormal' );
                }
                $objTemplate->parseCurrentBlock();
            }
            break;

        // sample6
        case '6':
            if ( ! isset( $aryParam[ 'email' ] ) )
            {
                $objTemplate->setVariable( 'email', $aryArgs[ 'default' ] );
            }
            else
            {
                $objTemplate->setVariable( 'email', $aryParam[ 'email' ] );
            }

            for ( $i = 1; $i <= $aryArgs[ 'maxcount' ]; $i++ )
            {
                $objTemplate->setCurrentBlock( 'loop_block' );
                $objTemplate->setVariable( 'count', $i );
                $objTemplate->parseCurrentBlock();
            }
            break;

        // sample7
        case '7':
            ob_start();
            $aryPear[ 'auth' ]->start();
            $sAuth = ob_get_clean();
            $sAuth = str_replace( '</form>', '<input type="hidden" name="sample" value="7" /></form>', $sAuth );
            $objTemplate->setVariable( 'auth', $sAuth, 'h' );
            if ( $aryPear[ 'auth' ]->getAuth() )
            {
                $objTemplate->setVariable( 'auth', '認証成功！' );
            }
            //$aryPear[ 'auth' ]->logout();
            //$aryPear[ 'auth' ]->start();

            if ( ! $aryPear[ 'cookie' ]->get( 'cookie' ) )
            {
                $aryPear[ 'cookie' ]->set( 'cookie', date( 'Y/m/d H:i:s' ) );
            }
            else
            {
                $objTemplate->setVariable( 'cookie', $aryPear[ 'cookie' ]->get( 'cookie' ) );
            }
            //$aryPear[ 'cookie' ]->delete( 'cookie' );

            $mRet = $aryPear[ 'db' ]->connect( BLOCKEN_DB_DSN_SAMPLE, array( 'persistent' => true, 'debug' => 1 ) );
            if ( ! MDB2::isError( $mRet ) )
            {
                $aryRet = $aryPear[ 'db' ]->getAll( BLOCKEN_DB_DSN_SAMPLE, 'select * from sample' );
                //$aryRet = $aryPear[ 'db' ]->getRow( BLOCKEN_DB_DSN_SAMPLE, 'SELECT * FROM sample' );
                //$sRet   = $aryPear[ 'db' ]->getOne( BLOCKEN_DB_DSN_SAMPLE, 'SELECT value FROM sample where id = 1' );
                //$objRet = $aryPear[ 'db' ]->query( BLOCKEN_DB_DSN_SAMPLE, 'SELECT * FROM sample' );
                //while ( $aryRow = $objRet->fetchRow() )
                //{
                //}
                $objTemplate->setVariable( 'db', var_export( $aryRet, true ) );
            }
            else
            {
                $objTemplate->setVariable( 'db', '接続エラー！' );
            }

            if ( ! $aryPear[ 'session' ]->get( 'session' ) )
            {
                $aryPear[ 'session' ]->set( 'session', date( 'Y/m/d H:i:s' ) );
            }
            else
            {
                $objTemplate->setVariable( 'session', $aryPear[ 'session' ]->get( 'session' ) );
            }

            $aryPear[ 'log' ]->emerg( 'emergなメッセージ' );
            $aryPear[ 'log' ]->alert( 'alertなメッセージ' );
            $aryPear[ 'log' ]->crit( 'critなメッセージ' );
            $aryPear[ 'log' ]->err( 'errなメッセージ' );
            $aryPear[ 'log' ]->warning( 'warningなメッセージ' );
            $aryPear[ 'log' ]->notice( 'noticeなメッセージ' );
            $aryPear[ 'log' ]->info( 'infoなメッセージ' );
            $aryPear[ 'log' ]->debug( 'debugなメッセージ' );
            $aryPear[ 'log' ]->debug( $aryPear[ 'db' ]->debug( BLOCKEN_DB_DSN_SAMPLE ) );
            break;

        default:
            break;
    }

    return $objTemplate->get();
}
?>
