<?php
/**
 * BlockenCommand.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: BlockenCommand.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

require_once 'File/Archive.php';
require_once 'Text/Diff.php';
require_once 'Text/Diff/Renderer.php';
require_once 'Text/Diff/Renderer/unified.php';

/**
 * cmdExec()
 *
 * @param  string $sModule
 * @param  array  &$aryPear
 * @return void
 */
function cmdExec( $sModule, &$aryPear )
{
    $sScript = BLOCKEN_BIN_DIR . '/' . $sModule . '.php';
    if ( ! is_file( $sScript ) )
    {
        echo "拡張モジュールが存在しません: {$sModule}\n";
        return;
    }

    include_once $sScript;
    if ( ! function_exists( '_execute' ) )
    {
        echo "拡張モジュールが実行できません: _execute()\n";
        return;
    }

    _execute( $aryPear );
}

/**
 * cmdUpgrade()
 *
 * @param  array &$aryPear
 * @return void
 */
function cmdUpgrade( &$aryPear )
{
    $sFile = BLOCKEN_TMP_DIR . '/BlockenPackage.zip';

    $sRet = $aryPear[ 'curl' ]->execute( BLOCKEN_UPGRADE_URL );
    if ( PEAR::isError( $sRet ) )
    {
        echo "Blockenのアップグレードに失敗しました\n";
        return;
    }

    $fpPkg = fopen( $sFile, 'wb' );
    if ( ! $fpPkg )
    {
        echo "Blockenのアップグレードに失敗しました\n";
        return;
    }
    flock( $fpPkg, LOCK_EX );
    fwrite( $fpPkg, $sRet );
    flock( $fpPkg, LOCK_UN );
    fclose( $fpPkg );

    File_Archive::extract( File_Archive::read( "{$sFile}/" ), File_Archive::appender( BLOCKEN_BASE ) );
    unlink( $sFile );

    $aryOldFile = file( BLOCKEN_BASE . '/config.php' );
    $aryNewFile = file( BLOCKEN_BASE . '/config.php.default' );

    $objDiff     =& new Text_Diff( $aryOldFile, $aryNewFile );
    $objRenderer =& new Text_Diff_Renderer_unified();
    $sBuff       = $objRenderer->render( $objDiff );

    $fpDiff = fopen( BLOCKEN_BASE . '/config.php.diff', 'w' );
    if ( ! $fpDiff )
    {
        echo "Blockenのアップグレードに失敗しました\n";
        return;
    }
    flock( $fpDiff, LOCK_EX );
    fwrite( $fpDiff, $sBuff );
    flock( $fpDiff, LOCK_UN );
    fclose( $fpDiff );

    chmod( BLOCKEN_BASE . '/config.php.default',  0644 );
    chmod( BLOCKEN_BASE . '/config.php.diff', 0644 );

    echo "Blockenのアップグレードが完了しました\n";
    echo "configファイルを必要に応じてマージしてください\n";
    echo "New Config File:  " . BLOCKEN_BASE . "/config.php.default\n";
    echo "Diff Config File: " . BLOCKEN_BASE . "/config.php.diff\n";
}

/**
 * cmdPackage()
 *
 * @param  string $sPath
 * @return void
 */
function cmdPackage( $sPath )
{
    $sBaseDir = BLOCKEN_TMP_DIR . '/_BlockenPackage';
    $sBinDir  = $sBaseDir . str_replace( BLOCKEN_BASE, '', BLOCKEN_BIN_DIR );
    $sLibDir  = $sBaseDir . str_replace( BLOCKEN_BASE, '', BLOCKEN_LIB_DIR );

    File_Archive::extract( File_Archive::read( BLOCKEN_BIN_DIR , $sBinDir ), File_Archive::toFiles() );
    File_Archive::extract( File_Archive::read( BLOCKEN_LIB_DIR , $sLibDir ), File_Archive::toFiles() );
    copy( BLOCKEN_BASE . '/prepend.php', "{$sBaseDir}/prepend.php" );
    copy( BLOCKEN_BASE . '/config.php.default', "{$sBaseDir}/config.php.default" );

    if ( isset( $_SERVER[ 'PWD' ] ) )
    {
        chdir( $_SERVER[ 'PWD' ] );
    }

    File_Archive::extract(
        File_Archive::read( $sBaseDir ),
        File_Archive::toArchive( 'BlockenPackage.zip', File_Archive::toFiles( $sPath ) )
    );

    BlockenCommon::rmDir( $sBaseDir );
}

/**
 * cmdMakeDoCoMoIpList()
 *
 * @param  array &$aryPear
 * @return void
 */
function cmdMakeDoCoMoIpList( &$aryPear )
{
    $sRet = $aryPear[ 'curl' ]->execute( BLOCKEN_MOBI_D_IP_URL, null, 'SJIS' );
    if ( PEAR::isError( $sRet ) )
    {
        echo "DoCoMo IPアドレス帯域一覧表の作成に失敗しました\n";
        return;
    }

    $aryHtml = explode( "\n", $sRet );
    $bStart  = false;
    $sText   = "# " . BLOCKEN_MOBI_D_IP_URL . "\n";
    foreach ( $aryHtml as $sRow )
    {
        if ( ! $bStart )
        {
            if ( preg_match( '/^<ul class="normal txt">$/', $sRow ) )
            {
                $bStart = true;
            }
        }
        else
        {
            if ( preg_match( '/^<li>(\d+\.\d+\.\d+\.\d+\/\d+)<\/li>$/', $sRow, $aryMatches ) )
            {
                $sText .= "{$aryMatches[ 1 ]}\n";
            }
            else if ( preg_match( '/^<\/ul>$/', $sRow ) )
            {
                break;
            }
        }
    }

    $sFile = BLOCKEN_MOBI_D_IPLIST;
    copy( BLOCKEN_MOBI_D_IPLIST, BLOCKEN_MOBI_D_IPLIST . '.bak' );
    $fpText = fopen( $sFile, 'w' );
    if ( ! $fpText )
    {
        echo "DoCoMo IPアドレス帯域一覧表の作成に失敗しました\n";
        return;
    }
    flock( $fpText, LOCK_EX );
    fwrite( $fpText, $sText );
    flock( $fpText, LOCK_UN );
    fclose( $fpText );

    $aryOldFile = file( BLOCKEN_MOBI_D_IPLIST . '.bak' );
    $aryNewFile = file( BLOCKEN_MOBI_D_IPLIST );

    $objDiff     =& new Text_Diff( $aryOldFile, $aryNewFile );
    $objRenderer =& new Text_Diff_Renderer_unified();
    $sBuff       = $objRenderer->render( $objDiff );

    $fpDiff = fopen( BLOCKEN_MOBI_D_IPLIST . '.diff', 'w' );
    if ( ! $fpDiff )
    {
        echo "DoCoMo IPアドレス帯域一覧表の作成に失敗しました\n";
        return;
    }
    flock( $fpDiff, LOCK_EX );
    fwrite( $fpDiff, $sBuff );
    flock( $fpDiff, LOCK_UN );
    fclose( $fpDiff );

    chmod( BLOCKEN_MOBI_D_IPLIST . '.bak',  0644 );
    chmod( BLOCKEN_MOBI_D_IPLIST . '.diff', 0644 );

    echo "DoCoMo IPアドレス帯域一覧表の作成が完了しました\n";
    echo "New XML File:  " . BLOCKEN_MOBI_D_IPLIST . "\n";
    echo "Old XML File:  " . BLOCKEN_MOBI_D_IPLIST . ".bak\n";
    echo "Diff XML File: " . BLOCKEN_MOBI_D_IPLIST . ".diff\n";
}

/**
 * cmdMakeEZwebIpList()
 *
 * @param  array &$aryPear
 * @return void
 */
function cmdMakeEZwebIpList( &$aryPear )
{
    $sRet = $aryPear[ 'curl' ]->execute( BLOCKEN_MOBI_E_IP_URL, null, 'SJIS' );
    if ( PEAR::isError( $sRet ) )
    {
        echo "EZweb IPアドレス帯域一覧表の作成に失敗しました\n";
        return;
    }

    $sHtml = preg_replace( '/(?:\r?\n)+/', "\n", $sRet );
    $sPattern = <<< __RE__
\t\t\t<tr bgcolor=\"#ffffff\">
\t\t\t<td bgcolor=\"#f2f2f2\"><div class=\"TableText\">\d+<\/div><\/td>
\t\t\t<td><div class=\"TableText\">(\d+\.\d+\.\d+\.\d+)<\/div><\/td>
\t\t\t<td><div class=\"TableText\">(\/\d+)<\/div><\/td>
\t\t\t<td>&nbsp;<\/td>
\t\t\t<\/tr>
__RE__;

    preg_match_all( "/{$sPattern}/is", $sHtml, $aryMatches );

    $sText   = "# " . BLOCKEN_MOBI_E_IP_URL . "\n";
    if ( is_array( $aryMatches[ 0 ] ) )
    {
        foreach ( $aryMatches[ 0 ] as $sKey => $sValue )
        {
            $sText .= "{$aryMatches[ 1 ][ $sKey ]}{$aryMatches[ 2 ][ $sKey ]}\n";
        }
    }

    $sFile = BLOCKEN_MOBI_E_IPLIST;
    copy( BLOCKEN_MOBI_E_IPLIST, BLOCKEN_MOBI_E_IPLIST . '.bak' );
    $fpText = fopen( $sFile, 'w' );
    if ( ! $fpText )
    {
        echo "EZweb IPアドレス帯域一覧表の作成に失敗しました\n";
        return;
    }
    flock( $fpText, LOCK_EX );
    fwrite( $fpText, $sText );
    flock( $fpText, LOCK_UN );
    fclose( $fpText );

    $aryOldFile = file( BLOCKEN_MOBI_E_IPLIST . '.bak' );
    $aryNewFile = file( BLOCKEN_MOBI_E_IPLIST );

    $objDiff     =& new Text_Diff( $aryOldFile, $aryNewFile );
    $objRenderer =& new Text_Diff_Renderer_unified();
    $sBuff       = $objRenderer->render( $objDiff );

    $fpDiff = fopen( BLOCKEN_MOBI_E_IPLIST . '.diff', 'w' );
    if ( ! $fpDiff )
    {
        echo "EZweb IPアドレス帯域一覧表の作成に失敗しました\n";
        return;
    }
    flock( $fpDiff, LOCK_EX );
    fwrite( $fpDiff, $sBuff );
    flock( $fpDiff, LOCK_UN );
    fclose( $fpDiff );

    chmod( BLOCKEN_MOBI_E_IPLIST . '.bak',  0644 );
    chmod( BLOCKEN_MOBI_E_IPLIST . '.diff', 0644 );

    echo "EZweb IPアドレス帯域一覧表の作成が完了しました\n";
    echo "New XML File:  " . BLOCKEN_MOBI_E_IPLIST . "\n";
    echo "Old XML File:  " . BLOCKEN_MOBI_E_IPLIST . ".bak\n";
    echo "Diff XML File: " . BLOCKEN_MOBI_E_IPLIST . ".diff\n";
}

/**
 * cmdMakeSoftBankIpList()
 *
 * @param  array &$aryPear
 * @return void
 */
function cmdMakeSoftBankIpList( &$aryPear )
{
    $sRet = $aryPear[ 'curl' ]->execute( BLOCKEN_MOBI_S_IP_URL, null, 'SJIS' );
    if ( PEAR::isError( $sRet ) )
    {
        echo "SoftBank IPアドレス帯域一覧表の作成に失敗しました\n";
        return;
    }

    $aryHtml = explode( "\n", $sRet );
    $bStart  = false;
    $sText   = "# " . BLOCKEN_MOBI_S_IP_URL . "\n";
    foreach ( $aryHtml as $sRow )
    {
        if ( ! $bStart )
        {
            if ( preg_match( '/<table width="100%" border="0" cellspacing="1" cellpadding="3">$/', $sRow ) )
            {
                $bStart = true;
            }
        }
        else
        {
            if ( preg_match( '/<td bgcolor="#eeeeee">&nbsp;&nbsp;(\d+\.\d+\.\d+\.\d+\/\d+)<\/td>$/',
                            $sRow, $aryMatches ) )
            {
                $sText .= "{$aryMatches[ 1 ]}\n";
            }
            else if ( preg_match( '/<\/table>$/', $sRow ) )
            {
                break;
            }
        }
    }

    $sFile = BLOCKEN_MOBI_S_IPLIST;
    copy( BLOCKEN_MOBI_S_IPLIST, BLOCKEN_MOBI_S_IPLIST . '.bak' );
    $fpText = fopen( $sFile, 'w' );
    if ( ! $fpText )
    {
        echo "SoftBank IPアドレス帯域一覧表の作成に失敗しました\n";
        return;
    }
    flock( $fpText, LOCK_EX );
    fwrite( $fpText, $sText );
    flock( $fpText, LOCK_UN );
    fclose( $fpText );

    $aryOldFile = file( BLOCKEN_MOBI_S_IPLIST . '.bak' );
    $aryNewFile = file( BLOCKEN_MOBI_S_IPLIST );

    $objDiff     =& new Text_Diff( $aryOldFile, $aryNewFile );
    $objRenderer =& new Text_Diff_Renderer_unified();
    $sBuff       = $objRenderer->render( $objDiff );

    $fpDiff = fopen( BLOCKEN_MOBI_S_IPLIST . '.diff', 'w' );
    if ( ! $fpDiff )
    {
        echo "SoftBank IPアドレス帯域一覧表の作成に失敗しました\n";
        return;
    }
    flock( $fpDiff, LOCK_EX );
    fwrite( $fpDiff, $sBuff );
    flock( $fpDiff, LOCK_UN );
    fclose( $fpDiff );

    chmod( BLOCKEN_MOBI_S_IPLIST . '.bak',  0644 );
    chmod( BLOCKEN_MOBI_S_IPLIST . '.diff', 0644 );

    echo "SoftBank IPアドレス帯域一覧表の作成が完了しました\n";
    echo "New XML File:  " . BLOCKEN_MOBI_S_IPLIST . "\n";
    echo "Old XML File:  " . BLOCKEN_MOBI_S_IPLIST . ".bak\n";
    echo "Diff XML File: " . BLOCKEN_MOBI_S_IPLIST . ".diff\n";
}

/**
 * cmdShowIpList()
 *
 * @param  array  &$aryPear
 * @return string
 */
function cmdShowIpList( &$aryPear )
{
    $aryCarrier = array( 'DoCoMo'   => BLOCKEN_MOBI_D_IPLIST,
                         'EZweb'    => BLOCKEN_MOBI_E_IPLIST,
                         'SoftBank' => BLOCKEN_MOBI_S_IPLIST );

    $sBuff  = "IPアドレス帯域一覧表\n";
    $sBuff .= "Order deny,allow\n";
    $sBuff .= "Deny from all\n";
    foreach ( $aryCarrier as $sCarrier => $sFile )
    {
        $aryIpList = file( $sFile );
        unset( $aryIpList[ 0 ] );
        $sBuff .= "# {$sCarrier}\n";
        foreach ( $aryIpList as $sRow )
        {
            $sBuff .= "Allow from {$sRow}";
        }
    }

    return $sBuff;
}

/**
 * cmdMakeDoCoMoMap()
 *
 * @param  array &$aryPear
 * @return void
 */
function cmdMakeDoCoMoMap( &$aryPear )
{
    $aryXml = array();

    // 端末情報
    $sRet = $aryPear[ 'curl' ]->execute( BLOCKEN_MOBI_D_MAP_URL, null, 'SJIS' );
    if ( PEAR::isError( $sRet ) )
    {
        echo "DoCoMo端末一覧表の作成に失敗しました\n";
        return;
    }

    $sHtml = preg_replace( array( '/(?:\r?\n)+/', '/II/', '/&mu;/' ), array( "\n", '2', 'myu' ), $sRet );
    $sPattern = <<< __RE__
<td.*?><span class=\"txt\">([A-Z]+[0-9\-]+\w*\+?).*?<\/span><\/td>
<td><span class=\"txt\">.*?(?:<\/span><\/td>)?
<td><span class=\"txt\">.*?(?:<\/span><\/td>)?
<td><span class=\"txt\">.*?(\d+)×(\d+).*?<\/span><\/td>
<td><span class=\"txt\">.*?<\/span><\/td>
<td><span class=\"txt\">(白黒|カラー)(?:.*?)(\d+)(?:色|階調)<\/span><\/td>
__RE__;

    preg_match_all( "/{$sPattern}/is", $sHtml, $aryMatches );

    if ( is_array( $aryMatches[ 0 ] ) )
    {
        foreach ( $aryMatches[ 0 ] as $sKey => $sValue )
        {
            $sModel  = str_replace( '-', '', $aryMatches[ 1 ][ $sKey ] );
            $sWidth  = $aryMatches[ 2 ][ $sKey ];
            $sHeight = $aryMatches[ 3 ][ $sKey ];
            $sColor  = ( $aryMatches[ 4 ][ $sKey ] == 'カラー' ) ? '1' : '';
            $sDepth  = $aryMatches[ 5 ][ $sKey ];

            $aryXml[ $sModel ] = array( 'width' => $sWidth, 'height' => $sHeight,
                                        'color' => $sColor, 'depth' => $sDepth,
                                        'flash' => '', 'pdf' => '' );
        }
    }

    // FLASH対応端末
    $sRet = $aryPear[ 'curl' ]->execute( BLOCKEN_MOBI_D_FLA_URL, null, 'SJIS' );
    if ( PEAR::isError( $sRet ) )
    {
        echo "DoCoMo端末一覧表の作成に失敗しました\n";
        return;
    }

    $aryHtml = explode( "\n", $sRet );
    $sVersion = '';
    foreach ( $aryHtml as $sRow )
    {
        $sRow = preg_replace( array( '/II/', '/&mu;/' ), array( '2', 'myu' ), $sRow );

        if ( preg_match( '/^<h2 class=\"title\"><a name=\".*\">Flash Lite (.*)<\/a><\/h2>$/', $sRow, $aryMatches ) )
        {
            $sVersion = $aryMatches[ 1 ];
        }
        else if ( preg_match( '/^<td.*?><span class=\"txt\">([A-Z]+[0-9\-]+\w*\+?).*?<\/span><\/td>$/', $sRow, $aryMatches ) )
        {
            $sModel = str_replace( '-', '', $aryMatches[ 1 ] );
            if ( array_key_exists( $sModel, $aryXml ) )
            {
                $aryXml[ $sModel ][ 'flash' ] = $sVersion;
            }
        }
    }

    // PDF対応端末
    $sRet = $aryPear[ 'curl' ]->execute( BLOCKEN_MOBI_D_PDF_URL, null, 'SJIS' );
    if ( PEAR::isError( $sRet ) )
    {
        echo "DoCoMo端末一覧表の作成に失敗しました\n";
        return;
    }

    $aryHtml = explode( "\r\n", $sRet );
    foreach ( $aryHtml as $sRow )
    {
        $sRow = preg_replace( array( '/II/', '/&#956;/' ), array( '2', 'myu' ), $sRow );

        if ( preg_match( '/^<h3 class=\"title\">([A-Z]+[0-9\-]+\w*\+?).*?<\/h3>$/', $sRow, $aryMatches ) )
        {
            $sModel = str_replace( '-', '', $aryMatches[ 1 ] );
            if ( array_key_exists( $sModel, $aryXml ) )
            {
                $aryXml[ $sModel ][ 'pdf' ] = '1';
            }
        }
    }

    $sXml = "<?xml version=\"1.0\" ?>\n"
          . "<!-- " . BLOCKEN_MOBI_D_MAP_URL . " -->\n"
          . "<!-- " . BLOCKEN_MOBI_D_FLA_URL . " -->\n"
          . "<!-- " . BLOCKEN_MOBI_D_PDF_URL . " -->\n"
          . "<opt>\n";
    foreach ( $aryXml as $sKey => $aryValue )
    {
        $sXml .= sprintf( "  <terminal model=\"%s\" width=\"%d\" height=\"%d\" color=\"%s\" depth=\"%d\" flash=\"%s\" pdf=\"%s\" />\n",
                          $sKey, $aryValue[ 'width' ], $aryValue[ 'height' ], $aryValue[ 'color' ], $aryValue[ 'depth' ],
                          $aryValue[ 'flash' ], $aryValue[ 'pdf' ] );
    }
    $sXml .= "</opt>\n";

    $sFile = BLOCKEN_MOBI_D_MAP;
    copy( BLOCKEN_MOBI_D_MAP, BLOCKEN_MOBI_D_MAP . '.bak' );
    $fpXml = fopen( $sFile, 'w' );
    if ( ! $fpXml )
    {
        echo "DoCoMo端末一覧表の作成に失敗しました\n";
        return;
    }
    flock( $fpXml, LOCK_EX );
    fwrite( $fpXml, $sXml );
    flock( $fpXml, LOCK_UN );
    fclose( $fpXml );

    $aryOldFile = file( BLOCKEN_MOBI_D_MAP . '.bak' );
    $aryNewFile = file( BLOCKEN_MOBI_D_MAP );

    $objDiff     =& new Text_Diff( $aryOldFile, $aryNewFile );
    $objRenderer =& new Text_Diff_Renderer_unified();
    $sBuff       = $objRenderer->render( $objDiff );

    $fpDiff = fopen( BLOCKEN_MOBI_D_MAP . '.diff', 'w' );
    if ( ! $fpDiff )
    {
        echo "DoCoMo端末一覧表の作成に失敗しました\n";
        return;
    }
    flock( $fpDiff, LOCK_EX );
    fwrite( $fpDiff, $sBuff );
    flock( $fpDiff, LOCK_UN );
    fclose( $fpDiff );

    chmod( BLOCKEN_MOBI_D_MAP . '.bak',  0644 );
    chmod( BLOCKEN_MOBI_D_MAP . '.diff', 0644 );

    echo "DoCoMo端末一覧表の作成が完了しました\n";
    echo "New XML File:  " . BLOCKEN_MOBI_D_MAP . "\n";
    echo "Old XML File:  " . BLOCKEN_MOBI_D_MAP . ".bak\n";
    echo "Diff XML File: " . BLOCKEN_MOBI_D_MAP . ".diff\n";
}

/**
 * cmdMakeSoftBankMap()
 *
 * @param  array &$aryPear
 * @return void
 */
function cmdMakeSoftBankMap( &$aryPear )
{
    $aryXml = array();
    $aryFlash = array();

    // FLASH対応端末
    $sRet = $aryPear[ 'curl' ]->execute( BLOCKEN_MOBI_S_FLA_URL, null, 'SJIS' );
    if ( PEAR::isError( $sRet ) )
    {
        echo "SoftBank端末一覧表の作成に失敗しました\n";
        return;
    }

    $sHtml = preg_replace( array( '/(?:\r?\n)+/', '/　/' ), array( "\n", ' ' ), $sRet );
    $sPattern = <<< __RE__
\t<tr bgcolor=\"#FFFFFF\" height=\"18\">
\t\t<td width=\"6%\">([\w\s\-]*).*?<\/td>
\t\t<td align=\"center\" width="14%">(?:○|×)<\/td>
\t\t<td align=\"center\" width="14%">(?:○|×)<\/td>
\t\t<td width=\"14%\">(×|Flash Lite&trade;\d\.\d).*?<\/td>
__RE__;

    preg_match_all( "/{$sPattern}/is", $sHtml, $aryMatches );

    if ( is_array( $aryMatches[ 0 ] ) )
    {
        foreach ( $aryMatches[ 0 ] as $sKey => $sValue )
        {
            $sName    = $aryMatches[ 1 ][ $sKey ];
            $sVersion = $aryMatches[ 2 ][ $sKey ];

            if ( '×' != $sVersion )
            {
                $aryFlash[ $sName ] = preg_replace( '/Flash Lite&trade;(\d\.\d)/', '\1', $sVersion );
            }
        }
    }

    // 端末情報
    $sRet = $aryPear[ 'curl' ]->execute( BLOCKEN_MOBI_S_MAP_URL, null, 'SJIS' );
    if ( PEAR::isError( $sRet ) )
    {
        echo "SoftBank端末一覧表の作成に失敗しました\n";
        return;
    }

    $sHtml = preg_replace( array( '/(?:\r?\n)+/', '/　/' ), array( "\n", ' ' ), $sRet );
    $sPattern = <<< __RE__
\t<tr bgcolor=\"#FFFFFF\" height=\"18\">
\t\t<td>([\w\s\-]*).*?<\/td>
\t\t<td>([\w\-]+).*?<\/td>
__RE__;

    preg_match_all( "/{$sPattern}/is", $sHtml, $aryMatches );

    if ( is_array( $aryMatches[ 0 ] ) )
    {
        foreach ( $aryMatches[ 0 ] as $sKey => $sValue )
        {
            $sName  = $aryMatches[ 1 ][ $sKey ];
            $sModel = $aryMatches[ 2 ][ $sKey ];

            if ( array_key_exists( $sName, $aryFlash ) )
            {
                $aryXml[ $sModel ] = $aryFlash[ $sName ];
            }
        }
    }

    $sXml = "<?xml version=\"1.0\" ?>\n"
          . "<!-- " . BLOCKEN_MOBI_S_MAP_URL . " -->\n"
          . "<!-- " . BLOCKEN_MOBI_S_FLA_URL . " -->\n"
          . "<opt>\n";
    foreach ( $aryXml as $sKey => $sValue )
    {
        $sXml .= sprintf( "  <terminal model=\"%s\" flash=\"%s\" />\n", $sKey, $sValue );
    }
    $sXml .= "</opt>\n";

    $sFile = BLOCKEN_MOBI_S_MAP;
    copy( BLOCKEN_MOBI_S_MAP, BLOCKEN_MOBI_S_MAP . '.bak' );
    $fpXml = fopen( $sFile, 'w' );
    if ( ! $fpXml )
    {
        echo "SoftBank端末一覧表の作成に失敗しました\n";
        return;
    }
    flock( $fpXml, LOCK_EX );
    fwrite( $fpXml, $sXml );
    flock( $fpXml, LOCK_UN );
    fclose( $fpXml );

    $aryOldFile = file( BLOCKEN_MOBI_S_MAP . '.bak' );
    $aryNewFile = file( BLOCKEN_MOBI_S_MAP );

    $objDiff     =& new Text_Diff( $aryOldFile, $aryNewFile );
    $objRenderer =& new Text_Diff_Renderer_unified();
    $sBuff       = $objRenderer->render( $objDiff );

    $fpDiff = fopen( BLOCKEN_MOBI_S_MAP . '.diff', 'w' );
    if ( ! $fpDiff )
    {
        echo "SoftBank端末一覧表の作成に失敗しました\n";
        return;
    }
    flock( $fpDiff, LOCK_EX );
    fwrite( $fpDiff, $sBuff );
    flock( $fpDiff, LOCK_UN );
    fclose( $fpDiff );

    chmod( BLOCKEN_MOBI_S_MAP . '.bak',  0644 );
    chmod( BLOCKEN_MOBI_S_MAP . '.diff', 0644 );

    echo "SoftBank端末一覧表の作成が完了しました\n";
    echo "New XML File:  " . BLOCKEN_MOBI_S_MAP . "\n";
    echo "Old XML File:  " . BLOCKEN_MOBI_S_MAP . ".bak\n";
    echo "Diff XML File: " . BLOCKEN_MOBI_S_MAP . ".diff\n";
}

/**
 * cmdMakeDoc()
 *
 * @return string
 */
function cmdMakeDoc()
{
    global $_phpDocumentor_setting;

    $_GET[ 'interface' ] = 1;
    $_GET[ 'setting' ]   = array( 'directory' => BLOCKEN_SCRIPT_DIR,
                                  'target'    => BLOCKEN_DOC_DIR,
                                  'output'    => 'HTML:Smarty:PHP' );

    ob_start();
    include_once 'PhpDocumentor/phpDocumentor/phpdoc.inc';
    $sLog = ob_get_clean();

    return $sLog;
}

/**
 *  cmdReadDoc()
 *
 * @return void
 */
function cmdReadDoc()
{
    $sPath = BLOCKEN_DOC_PATH;

    echo <<< __HTML__
<html>
<head>
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<meta name="robots" content="noindex, nofollow" />
<title>Blocken --read_doc</title>
</head>
<frameset cols="100%,*">
<frame src="{$sPath}/" frameborder="0" noresize />
</frameset>
</html>
__HTML__;
}

/**
 * cmdDownloadDoc()
 *
 * @return void
 */
function cmdDownloadDoc()
{
    File_Archive::extract(
        File_Archive::read( BLOCKEN_DOC_DIR ),
        File_Archive::toArchive( 'BlockenDoc.zip', File_Archive::toOutput() )
    );
}

/**
 * cmdGetDoc()
 *
 * @param  string $sPath
 * @return void
 */
function cmdGetDoc( $sPath )
{
    if ( isset( $_SERVER[ 'PWD' ] ) )
    {
        chdir( $_SERVER[ 'PWD' ] );
    }

    File_Archive::extract(
        File_Archive::read( BLOCKEN_DOC_DIR ),
        File_Archive::toArchive( 'BlockenDoc.zip', File_Archive::toFiles( $sPath ) )
    );
}

/**
 * cmdCleanDoc()
 *
 * @return string
 */
function cmdCleanDoc()
{
    $aryBuff = array();

    $sDocDir = BLOCKEN_DOC_DIR . '/';
    foreach ( glob( $sDocDir . '*' ) as $sRow )
    {
        if ( is_dir( $sRow ) && ! is_link( $sRow ) )
        {
            BlockenCommon::rmDir( $sRow );
        }
        else
        {
            unlink( $sRow );
        }

        $sRow = str_replace( $sDocDir, '', $sRow );

        $aryBuff[] = "[{$sRow}]";
    }

    sort( $aryBuff );

    $sBuff = "ドキュメント消去一覧\n" . implode( "\n", $aryBuff );

    return $sBuff;
}

/**
 * cmdCleanCache()
 *
 * @return string
 */
function cmdCleanCache()
{
    include_once 'Cache.php';

    $objCacheBlock =& new Cache( BLOCKEN_CACHE_DRIVER, unserialize( BLOCKEN_CACHE_BLOCK ) );
    $objCachePage  =& new Cache( BLOCKEN_CACHE_DRIVER, unserialize( BLOCKEN_CACHE_PAGE ) );
    $objCacheImage =& new Cache( BLOCKEN_CACHE_DRIVER, unserialize( BLOCKEN_CACHE_IMAGE ) );

    $objCacheBlock->flush( '' );
    $objCachePage->flush( '' );
    $objCacheImage->flush( '' );

    $sBuff = "キャッシュ消去\nDriver:" . BLOCKEN_CACHE_DRIVER;

    return $sBuff;
}


/**
 * cmdShowLog()
 *
 * @param  string $sDate
 * @return string
 */
function cmdShowLog( $sDate )
{
    $sFile = BLOCKEN_LOG_NAME;
    if ( '' != $sDate )
    {
        $sFile = str_replace( date( 'Ymd' ), $sDate, $sFile );
    }

    if ( ! is_file( $sFile ) )
    {
        $sBuff = "ログファイルが存在しません: {$sFile}";

        return $sBuff;
    }

    $sBuff = "ログ参照\n" . file_get_contents( $sFile );

    return $sBuff;
}

/**
 * cmdCleanLog()
 *
 * @return string
 */
function cmdCleanLog()
{
    $aryBuff = array();

    $sLogDir = BLOCKEN_LOG_DIR . '/';
    foreach ( glob( $sLogDir . '*' ) as $sRow )
    {
        unlink( $sRow );

        $sRow = str_replace( $sLogDir, '', $sRow );

        $aryBuff[] = "[{$sRow}]";
    }

    sort( $aryBuff );

    $sBuff = "ログ消去一覧\n" . implode( "\n", $aryBuff );

    return $sBuff;
}

/**
 * cmdCleanTmp()
 *
 * @return string
 */
function cmdCleanTmp()
{
    $aryBuff = array();

    $sTmpDir = BLOCKEN_TMP_DIR . '/';
    foreach ( glob( $sTmpDir . '*' ) as $sRow )
    {
        if ( is_dir( $sRow ) && ! is_link( $sRow ) )
        {
            BlockenCommon::rmDir( $sRow );
        }
        else
        {
            unlink( $sRow );
        }

        $sRow = str_replace( $sTmpDir, '', $sRow );

        $aryBuff[] = "[{$sRow}]";
    }

    sort( $aryBuff );

    $sBuff = "一時ファイル消去一覧\n" . implode( "\n", $aryBuff );

    return $sBuff;
}

/**
 * cmdBlockenInfo()
 *
 * @return string
 */
function cmdBlockenInfo()
{
    $sBuff = "BLOCKENバージョン: " . BLOCKEN_VERSION;

    return $sBuff;
}

/**
 * cmdHelp()
 *
 * @return string
 */
function cmdHelp()
{
    $sBuff = '';

    switch ( BLOCKEN_MODE )
    {
        case BLOCKEN_CONSOLE:
            $sBuff = <<< __HTML__
Usage:
  -e, --exec <=module> 拡張モジュール実行
  -u, --upgrade        Blockenアップグレード
  --package [=path]    Blockenパッケージを作成
  --make_iplist        IPアドレス帯域一覧表を作成
  --show_iplist        IPアドレス帯域一覧表を参照
  --make_map           端末一覧表を作成
  --make_doc           ドキュメントを作成
  --get_doc [=path]    ドキュメントを取得
  --clean_doc          ドキュメントを消去
  --clean_cache        キャッシュを消去
  --show_log [=date]   ログを参照
  --clean_log          ログを消去
  --clean_tmp          一時ファイルを消去
  -p, --phpinfo        PHP情報
  -i, --info           Blocken情報
  -h, --help           ヘルプ
__HTML__;
            break;

        case BLOCKEN_WEB:
        default:
            $sBuff = <<< __HTML__
Usage:
  --make_doc         ドキュメントを作成
  --read_doc         ドキュメントを参照
  --download_doc     ドキュメントをダウンロード
  --clean_doc        ドキュメントを消去
  --clean_cache      キャッシュを消去
  --show_log [=date] ログを参照
  --clean_log        ログを消去
  --clean_tmp        一時ファイルを消去
  --phpinfo          PHP情報
  --info             Blocken情報
  --help             ヘルプ
__HTML__;
            break;
    }

    return $sBuff;
}

/**
 * cmdWeb()
 *
 * @param  array &$aryParam
 * @return void
 */
function cmdWeb( &$aryParam )
{
    $sBuff = '';

    switch ( $aryParam[ '_bin' ] )
    {
        case '--make_doc':
            if ( ! BLOCKEN_CMD_DEMO )
            {
                $sBuff = cmdMakeDoc();
            }
            else
            {
                $sBuff = 'このコマンドはデモモードのためご利用いただけません';
            }
            break;

        case '--read_doc':
            $sBuff = cmdReadDoc();
            exit;

        case '--download_doc':
            cmdDownloadDoc();
            exit;

        case '--clean_doc':
            if ( ! BLOCKEN_CMD_DEMO )
            {
                $sBuff = cmdCleanDoc();
            }
            else
            {
                $sBuff = 'このコマンドはデモモードのためご利用いただけません';
            }
            break;

        case '--clean_cache':
            $sBuff = cmdCleanCache();
            break;

        case '--show_log':
            $sBuff = cmdShowLog( @$aryParam[ 'date' ] );
            break;

        case '--clean_log':
            $sBuff = cmdCleanLog();
            break;

        case '--clean_tmp':
            $sBuff = cmdCleanTmp();
            break;

        case '--phpinfo':
            if ( ! BLOCKEN_CMD_DEMO )
            {
                phpinfo();
                exit;
            }
            else
            {
                $sBuff = 'このコマンドはデモモードのためご利用いただけません';
            }
            break;

        case '--info':
            if ( ! BLOCKEN_CMD_DEMO )
            {
                $sBuff = cmdBlockenInfo();
            }
            else
            {
                $sBuff = 'このコマンドはデモモードのためご利用いただけません';
            }
            break;

        case '--help':
            $sBuff = cmdHelp();
            break;

        default:
            return;
    }

    echo <<< __HTML__
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<meta name="robots" content="noindex, nofollow" />
<title>Blocken {$aryParam[ '_bin' ]}</title>
</head>
<body>
<pre>
{$sBuff}
</pre>
</body>
</html>
__HTML__;
    exit;
}

/**
 * cmdConsole()
 *
 * @param  array &$aryPear
 * @return void
 */
function cmdConsole( &$aryPear )
{
    foreach ( $aryPear[ 'opt' ] as $sKey => $sValue )
    {
        switch ( $sKey )
        {
            case 'e':
            case '--exec':
                cmdExec( $sValue, $aryPear );
                exit;

            case 'u':
            case '--upgrade':
                cmdUpgrade( $aryPear );
                exit;

            case '--package':
                cmdPackage( $sValue );
                exit;

            case '--make_iplist':
                cmdMakeDoCoMoIpList( $aryPear );
                cmdMakeEZwebIpList( $aryPear );
                cmdMakeSoftBankIpList( $aryPear );
                exit;

            case '--show_iplist':
                echo cmdShowIpList( $aryPear ) . "\n";
                exit;

            case '--make_map':
                cmdMakeDoCoMoMap( $aryPear );
                cmdMakeSoftBankMap( $aryPear );
                exit;

            case '--make_doc':
                echo cmdMakeDoc() . "\n";
                exit;

            case '--get_doc':
                cmdGetDoc( $sValue );
                exit;

            case '--clean_doc':
                echo cmdCleanDoc() . "\n";
                exit;

            case '--clean_cache':
                echo cmdCleanCache() . "\n";
                exit;

            case '--show_log':
                echo cmdShowLog( $sValue ) . "\n";
                exit;

            case '--clean_log':
                echo cmdCleanLog() . "\n";
                exit;

            case '--clean_tmp':
                echo cmdCleanTmp() . "\n";
                exit;

            case 'p':
            case '--phpinfo':
                phpinfo();
                exit;

            case 'i':
            case '--info':
                echo cmdBlockenInfo() . "\n";
                exit;

            default:
                break;
        }
    }

    echo cmdHelp() . "\n";
    exit;
}

/**
 * _execute()
 *
 * @param  array &$aryPear
 * @return void
 */
function _execute( &$aryPear )
{
}
?>
