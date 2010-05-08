<?php
/**
 *  prepend.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: prepend.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

$bAutoPrependFile = false;
if ( preg_match( '/\/blocken_check\.html$/', $_SERVER[ 'SCRIPT_NAME' ] ) ) { $bAutoPrependFile = true; }

require_once dirname( __FILE__ ) . '/config.php';

error_reporting( BLOCKEN_ERROR_REPORTING );
if ( '' != BLOCKEN_ERROR_LOG ) { ini_set( 'error_log', BLOCKEN_ERROR_LOG ); }
ini_set( 'display_errors', BLOCKEN_DISPLAY_ERRORS );
ini_set( 'display_startup_errors', 0 );
ini_set( 'default_charset', 'UTF-8' );

if ( $bAutoPrependFile ) { return; }

mb_language( 'Japanese' );
mb_internal_encoding( 'UTF-8' );
mb_regex_encoding( 'UTF-8' );
mb_substitute_character( 'none' );
mb_http_output( 'pass' );

$sLibraryPath = PATH_SEPARATOR . BLOCKEN_BASE .
                PATH_SEPARATOR . BLOCKEN_BIN_DIR .
                PATH_SEPARATOR . BLOCKEN_LIB_DIR;
ini_set( 'include_path', ini_get( 'include_path' ) . $sLibraryPath );

require_once 'BlockenAuth.class.php';
require_once 'BlockenCommon.class.php';
require_once 'BlockenController.class.php';
require_once 'BlockenCookie.class.php';
require_once 'BlockenCurl.class.php';
require_once 'BlockenDB.class.php';
require_once 'BlockenLog.class.php';
require_once 'BlockenMobile.class.php';
require_once 'BlockenSession.class.php';
require_once 'BlockenTemplate.class.php';

require_once 'func_ext.php';

$aryPear = array();
$aryParam = $_GET + $_POST;

$aryPear[ 'cookie' ] =& new BlockenCookie();
if ( BLOCKEN_COOKIE_USE && ( BLOCKEN_WEB == BLOCKEN_MODE ) )
{
    $aryPear[ 'cookie' ]->setExpire( BLOCKEN_COOKIE_EXPIRE );
    $aryPear[ 'cookie' ]->setPath( BLOCKEN_COOKIE_PATH );
    $aryPear[ 'cookie' ]->setDomain( BLOCKEN_COOKIE_DOMAIN );
    $aryPear[ 'cookie' ]->setSecure( BLOCKEN_COOKIE_SECURE );
    $aryPear[ 'cookie' ]->start();
}

$aryPear[ 'db' ] =& new BlockenDB();

$aryPear[ 'log' ] = BlockenLog::singleton( BLOCKEN_LOG_HANDLER, BLOCKEN_LOG_NAME, BLOCKEN_LOG_IDENT,
                                           unserialize( BLOCKEN_LOG_CONF ), BLOCKEN_LOG_LEVEL );

$aryPear[ 'session' ] =& new BlockenSession();

$aryPear[ 'mobile' ] = BlockenMobile::singleton();
if ( BLOCKEN_MOBI_USERAGENT && ! $aryPear[ 'mobile' ]->isSimulator() &&
  ( BLOCKEN_ALWAYS_DEBUG || BlockenCommon::isMember() ) )
{
    $aryPear[ 'mobile' ] = BlockenMobile::singleton( BLOCKEN_MOBI_USERAGENT );
}

if ( BLOCKEN_MOBILE_USE && ( BLOCKEN_WEB == BLOCKEN_MODE ) )
{
    if ( ! $aryPear[ 'mobile' ]->isNonMobile() )
    {
        ob_start( 'mb_output_handler' );
        mb_http_output( 'SJIS' );
        mb_substitute_character( 0x3013 );

        foreach ( $aryParam as $sKey => $sValue )
        {
            if ( $aryPear[ 'mobile' ]->hasEmoji( $sValue ) )
            {
                if ( ! isset( $aryParam[ 'emoji' ] ) )
                {
                    $aryParam[ 'emoji' ] = array();
                }

                $aryParam[ 'emoji' ][ $sKey ] = true;
            }

            $sValue = mb_convert_encoding( $sValue, 'UTF-8', 'SJIS' );
            if ( $aryPear[ 'mobile' ]->isSoftBank() )
            {
                $sValue = preg_replace( '/\x1b...\x0f/', '〓', $sValue );
            }
            $aryParam[ $sKey ] = $sValue;
        }
    }
}

foreach ( $aryParam as $sKey => $sValue )
{
    if ( is_array( $sValue ) )
    {
        continue;
    }

    if ( get_magic_quotes_gpc() )
    {
        $sValue = stripslashes( $sValue );
    }

    $sValue = str_replace( array( "\r\n", "\r" ), "\n", $sValue );
    $sValue = rtrim( $sValue, " \t\n\r\0\x0b　" );
    if ( '' == $sValue )
    {
        unset( $aryParam[ $sKey ] );
    }
    else
    {
        $sValue = mb_convert_kana( $sValue, 'KV' );
        $aryParam[ $sKey ] = $sValue;
    }
}

$sLoadFilename = '';
$bSessionStart = false;
if ( BLOCKEN_MOBILE_USE && ( BLOCKEN_WEB == BLOCKEN_MODE ) )
{
    if ( ! $aryPear[ 'mobile' ]->isNonMobile() )
    {
        if ( $aryPear[ 'mobile' ]->isDoCoMo() )
        {
            if ( ! BLOCKEN_MOBI_OFFCIAL && 'UTN' == BLOCKEN_MOBI_D_AUTHUID )
            {
                if ( isset( $aryParam[ $aryPear[ 'session' ]->name() ] ) || isset( $aryParam[ '_utn' ] ) )
                {
                    $bSessionStart = true;
                    if ( isset( $_COOKIE[ $aryPear[ 'session' ]->name() ] ) )
                    {
                        $aryPear[ 'session' ]->useCookies( true );
                    }
                    else
                    {
                        $aryPear[ 'session' ]->useCookies( false );
                    }
                }
            }
        }
        else if ( $aryPear[ 'mobile' ]->isEZweb() )
        {
            if ( BLOCKEN_MOBI_OFFCIAL && 0 != BLOCKEN_MOBI_E_AUTHTIME )
            {
                $bSessionStart = true;
                $aryPear[ 'session' ]->useCookies( true );
                if ( BLOCKEN_SESSION_EXPIRE )
                {
                    $aryPear[ 'session' ]->setCookieLifetime( BLOCKEN_SESSION_EXPIRE );
                }
            }
        }
        if ( $bSessionStart )
        {
            if ( BLOCKEN_SESSION_EXPIRE )
            {
                $aryPear[ 'session' ]->setGcMaxLifetime( BLOCKEN_SESSION_EXPIRE );
            }
            $aryPear[ 'session' ]->useTransSID( 0 );
            $aryPear[ 'session' ]->start( BLOCKEN_SESSION_NAME );
            $aryPear[ 'session' ]->setGcProbability( 100 );
            if ( BLOCKEN_SESSION_EXPIRE )
            {
                $aryPear[ 'session' ]->setExpire( BLOCKEN_SESSION_EXPIRE );
                if ( $aryPear[ 'session' ]->isExpired() )
                {
                    $aryPear[ 'session' ]->destroy();
                }
            }
        }

        if ( BLOCKEN_ALWAYS_DEBUG || BlockenCommon::isMember() )
        {
            if ( $aryPear[ 'mobile' ]->isSimulator() )
            {
                if ( $aryPear[ 'mobile' ]->isDoCoMo() )
                {
                    if ( BLOCKEN_MOBI_OFFCIAL )
                    {
                        $aryParam[ 'uid' ] = BLOCKEN_MOBI_D_DEBUGUID;
                    }
                    else
                    {
                        if ( 'GUID' == BLOCKEN_MOBI_D_AUTHUID )
                        {
                            if ( isset( $aryParam[ 'guid' ] ) && ! isset( $_SERVER[ 'HTTP_X_DCMGUID' ] ) )
                            {
                                $_SERVER[ 'HTTP_X_DCMGUID' ] = substr( BLOCKEN_MOBI_D_DEBUGUID, 2 );
                            }
                        }
                    }
                }
            }
            else
            {
                if ( $aryPear[ 'mobile' ]->isDoCoMo() )
                {
                    if ( BLOCKEN_MOBI_OFFCIAL )
                    {
                        $aryParam[ 'uid' ] = BLOCKEN_MOBI_D_DEBUGUID;
                    }
                    else
                    {
                        if ( 'GUID' == BLOCKEN_MOBI_D_AUTHUID )
                        {
                            if ( isset( $aryParam[ 'guid' ] ) && ! isset( $_SERVER[ 'HTTP_X_DCMGUID' ] ) )
                            {
                                $_SERVER[ 'HTTP_X_DCMGUID' ] = substr( BLOCKEN_MOBI_D_DEBUGUID, 2 );
                            }
                        }
                        else
                        {
                            if ( isset( $aryParam[ '_utn' ] ) )
                            {
                                $aryPear[ 'mobile' ]->_cardID = substr( BLOCKEN_MOBI_D_DEBUGUID, 2 );
                                $aryPear[ 'mobile' ]->_serialNumber = substr( BLOCKEN_MOBI_D_DEBUGUID, 2 );
                            }
                        }
                    }
                }
                else if ( $aryPear[ 'mobile' ]->isEZweb() )
                {
                    if ( ! isset( $_SERVER[ 'HTTP_X_UP_SUBNO' ] ) )
                    {
                        $_SERVER[ 'HTTP_X_UP_SUBNO' ] = BLOCKEN_MOBI_E_DEBUGUID;
                    }
                }
                else if ( $aryPear[ 'mobile' ]->isSoftBank() )
                {
                    if ( ! isset( $_SERVER[ 'HTTP_X_JPHONE_UID' ] ) )
                    {
                        $_SERVER[ 'HTTP_X_JPHONE_UID' ] = BLOCKEN_MOBI_S_DEBUGUID;
                    }
                }
            }
        }

        if ( $aryPear[ 'mobile' ]->isDoCoMo() )
        {
            if ( BLOCKEN_MOBI_OFFCIAL )
            {
                if ( isset( $aryParam[ 'uid' ] ) )
                {
                    $aryParam[ 'uid' ] = substr( $aryParam[ 'uid' ], 2 );
                }

                if ( '' != $aryPear[ 'mobile' ]->getSerialNumber() )
                {
                    $sLoadFilename = BLOCKEN_MOBI_SYSTEMERR;
                }
            }
            else
            {
                if ( 'GUID' == BLOCKEN_MOBI_D_AUTHUID )
                {
                    if ( isset( $_SERVER[ 'HTTP_X_DCMGUID' ] ) )
                    {
                        $aryParam[ 'uid' ] = $_SERVER[ 'HTTP_X_DCMGUID' ];
                    }
                }
                else
                {
                    if ( isset( $aryParam[ '_utn' ] ) )
                    {
                        if ( $aryPear[ 'mobile' ]->isFOMA() )
                        {
                            if ( '' != $aryPear[ 'mobile' ]->getCardID() )
                            {
                                $aryPear[ 'session' ]->set( 'uid', $aryPear[ 'mobile' ]->getCardID() );
                                $aryParam[ 'uid' ] = $aryPear[ 'mobile' ]->getCardID();
                            }
                        }
                        else
                        {
                            if ( '' != $aryPear[ 'mobile' ]->getSerialNumber() )
                            {
                                $aryPear[ 'session' ]->set( 'uid', $aryPear[ 'mobile' ]->getSerialNumber() );
                                $aryParam[ 'uid' ] = $aryPear[ 'mobile' ]->getSerialNumber();
                            }
                        }
                    }
                    else
                    {
                        if ( $aryPear[ 'session' ]->get( 'uid' ) )
                        {
                            $aryParam[ 'uid' ] = $aryPear[ 'session' ]->get( 'uid' );
                        }

                        if ( '' != $aryPear[ 'mobile' ]->getSerialNumber() )
                        {
                            $sLoadFilename = BLOCKEN_MOBI_SYSTEMERR;
                        }
                    }
                }
            }
        }
        else if ( $aryPear[ 'mobile' ]->isEZweb() )
        {
            if ( isset( $_SERVER[ 'HTTP_X_UP_SUBNO' ] ) )
            {
                $aryParam[ 'uid' ] = $_SERVER[ 'HTTP_X_UP_SUBNO' ];
            }
        }
        else if ( $aryPear[ 'mobile' ]->isSoftBank() )
        {
            if ( isset( $_SERVER[ 'HTTP_X_JPHONE_UID' ] ) )
            {
                $aryParam[ 'uid' ] = substr( $_SERVER[ 'HTTP_X_JPHONE_UID' ], 1 );
            }
        }

        if ( ! ( BLOCKEN_ALWAYS_DEBUG || BlockenCommon::isMember() ) && $aryPear[ 'mobile' ]->isSimulator() )
        {
            $sLoadFilename = BLOCKEN_MOBI_SYSTEMERR;
        }

        if ( $aryPear[ 'mobile' ]->isDoCoMo() )
        {
            if ( BLOCKEN_MOBI_D_HV2ERR && in_array( $aryPear[ 'mobile' ]->getHTMLVersion(), array( '1.0', '2.0' ) ) )
            {
                $sLoadFilename = BLOCKEN_MOBI_D_HV2ERR;
            }
            else if ( BLOCKEN_MOBI_D_PDCERR && ! $aryPear[ 'mobile' ]->isFOMA() )
            {
                $sLoadFilename = BLOCKEN_MOBI_D_PDCERR;
            }
        }
        else if ( $aryPear[ 'mobile' ]->isEZweb() )
        {
            if ( BLOCKEN_MOBI_E_HDMLERR && ! $aryPear[ 'mobile' ]->isXHTMLCompliant() )
            {
                $sLoadFilename = BLOCKEN_MOBI_E_HDMLERR;
            }
            else if ( BLOCKEN_MOBI_E_CDMAERR && ! $aryPear[ 'mobile' ]->isWin() )
            {
                $sLoadFilename = BLOCKEN_MOBI_E_CDMAERR;
            }
        }
        else if ( $aryPear[ 'mobile' ]->isSoftBank() )
        {
            if ( BLOCKEN_MOBI_S_TYPECERR && $aryPear[ 'mobile' ]->isTypeC() )
            {
                $sLoadFilename = BLOCKEN_MOBI_S_TYPECERR;
            }
            else if ( BLOCKEN_MOBI_S_TYPEPERR && $aryPear[ 'mobile' ]->isTypeP() )
            {
                $sLoadFilename = BLOCKEN_MOBI_S_TYPEPERR;
            }
            else if ( BLOCKEN_MOBI_S_TYPEWERR && $aryPear[ 'mobile' ]->isTypeW() )
            {
                $sLoadFilename = BLOCKEN_MOBI_S_TYPEWERR;
            }
        }

        if ( BLOCKEN_MOBI_OFFCIAL )
        {
            if ( ! BLOCKEN_MOBI_D_OFFCIAL && $aryPear[ 'mobile' ]->isDoCoMo() )
            {
                $sLoadFilename = BLOCKEN_MOBI_OFFCIALERR;
            }
            else if ( ! BLOCKEN_MOBI_E_OFFCIAL && $aryPear[ 'mobile' ]->isEZweb() )
            {
                $sLoadFilename = BLOCKEN_MOBI_OFFCIALERR;
            }
            else if ( ! BLOCKEN_MOBI_S_OFFCIAL && $aryPear[ 'mobile' ]->isSoftBank() )
            {
                $sLoadFilename = BLOCKEN_MOBI_OFFCIALERR;
            }
        }

        if ( BLOCKEN_MOBI_ACCESSCHK )
        {
            $sIpList = '';
            if ( $aryPear[ 'mobile' ]->isDoCoMo() )
            {
                $sIpList = BLOCKEN_MOBI_D_IPLIST;
            }
            else if ( $aryPear[ 'mobile' ]->isEZweb() )
            {
                $sIpList = BLOCKEN_MOBI_E_IPLIST;
            }
            else if ( $aryPear[ 'mobile' ]->isSoftBank() )
            {
                $sIpList = BLOCKEN_MOBI_S_IPLIST;
            }

            $aryBand = BlockenCommon::makeIPList( BLOCKEN_IS_MEMBER );
            $aryBand = array_merge( $aryBand, BlockenCommon::makeIPList( BLOCKEN_MOBI_P_IPLIST ) );
            $aryBand = array_merge( $aryBand, BlockenCommon::makeIPList( $sIpList ) );

            if ( ! BlockenCommon::isAddr( $_SERVER[ 'REMOTE_ADDR' ], $aryBand ) )
            {
                $sLoadFilename = BLOCKEN_MOBI_ACCESSERR;
            }
        }
    }
    else
    {
        if ( ! ( BLOCKEN_ALWAYS_DEBUG || BlockenCommon::isMember() ) || isset( $aryParam[ '_pc' ] ) )
        {
            $sLoadFilename = BLOCKEN_MOBI_P_REDIRECT;
        }
        else
        {
            $aryParam[ 'uid' ] = BLOCKEN_MOBI_P_DEBUGUID;
        }
    }
}

if ( BLOCKEN_SESSION_USE && ( BLOCKEN_WEB == BLOCKEN_MODE ) && ! $bSessionStart )
{
    $aryPear[ 'session' ]->useCookies( true );
    if ( BLOCKEN_SESSION_EXPIRE )
    {
        $aryPear[ 'session' ]->setGcMaxLifetime( BLOCKEN_SESSION_EXPIRE );
    }
    $aryPear[ 'session' ]->useTransSID( 0 );
    $aryPear[ 'session' ]->start( BLOCKEN_SESSION_NAME );
    $aryPear[ 'session' ]->setGcProbability( 100 );
    if ( BLOCKEN_SESSION_IDLE )
    {
        $aryPear[ 'session' ]->setIdle( BLOCKEN_SESSION_IDLE );
        if ( $aryPear[ 'session' ]->isIdle() )
        {
            $aryPear[ 'session' ]->destroy();
        }
        $aryPear[ 'session' ]->updateIdle();
    }
}

if ( BLOCKEN_AUTH_USE && ( BLOCKEN_WEB == BLOCKEN_MODE ) )
{
    $aryPear[ 'auth' ] = BlockenAuth::factory( BLOCKEN_AUTH_MODE, BLOCKEN_AUTH_DRIVER,
                                               unserialize( BLOCKEN_AUTH_OPTION ) );
}
else
{
    $aryPear[ 'auth' ] =& new BlockenAuth();
}

$aryPear[ 'curl' ] =& new BlockenCurl( null, BLOCKEN_CURL_USERAGENT );
$aryPear[ 'curl' ]->setOptions( unserialize( BLOCKEN_CURL_OPTION ) );

if ( ( BLOCKEN_CMD_USE && ( BLOCKEN_WEB == BLOCKEN_MODE ) )
 && ( ! BLOCKEN_CMD_MEMBER_ONLY || ( BLOCKEN_CMD_MEMBER_ONLY && BlockenCommon::isMember() ) ) )
{
    $objCmdAuth = BlockenAuth::factory( 'http', BLOCKEN_CMD_DRIVER, unserialize( BLOCKEN_CMD_OPTION ) );

    if ( isset( $aryParam[ '_bin' ] ) )
    {
        $objCmdAuth->start();
        include_once 'BlockenCommand.php';
        cmdWeb( $aryParam );
    }

    if ( ereg( BLOCKEN_DOC_PATH, $_SERVER[ 'SCRIPT_NAME' ] ) )
    {
        $objCmdAuth->start();
        return;
    }
}
else
{
    if ( ereg( BLOCKEN_DOC_PATH, $_SERVER[ 'SCRIPT_NAME' ] ) )
    {
        header( 'HTTP/1.0 403 Forbidden' );
        echo '403 Forbidden';
        exit;
    }
}

if ( ! preg_match( '/\.php$/', $_SERVER[ 'SCRIPT_NAME' ] ) && ( BLOCKEN_WEB == BLOCKEN_MODE ) )
{
    loadTemplate( false );
}

if ( BLOCKEN_CONSOLE == BLOCKEN_MODE )
{
    $aryPear[ 'opt' ] = array();

    $objCg =& new Console_Getopt();
    $aryRet = $objCg->getopt( $objCg->readPHPArgv(), BLOCKEN_CONSOLE_SOPT, unserialize( BLOCKEN_CONSOLE_LOPT ) );
    if ( ! PEAR::isError( $aryRet ) )
    {
        foreach ( $aryRet[ 0 ] as $aryOpt )
        {
            $aryPear[ 'opt' ][ $aryOpt[ 0 ] ] = $aryOpt[ 1 ];
        }
        $aryPear[ 'opt' ][ 'argv' ] = $aryRet[ 1 ];
    }
}
?>
