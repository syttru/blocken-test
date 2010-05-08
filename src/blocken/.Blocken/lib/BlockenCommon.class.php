<?php
/**
 * BlockenCommon.class.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: BlockenCommon.class.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

class BlockenCommon
{
    /**
     * getMicroTime()
     *
     * @access public
     * @return float
     */
    function getMicroTime()
    {
        list( $sUsec, $sSec ) = explode( ' ', microtime() );

        return floatval( $sSec ) + floatval( $sUsec );
    }

    /**
     * isMember()
     *
     * @access public
     * @return boolean
     */
    function isMember()
    {
        static $bIsMember;

        if ( ! isset( $bIsMember ) )
        {
            $bIsMember = false;

            $aryList = BlockenCommon::makeIPList( BLOCKEN_IS_MEMBER );

            if ( BlockenCommon::isAddr( $_SERVER[ 'REMOTE_ADDR' ], $aryList ) )
            {
                $bIsMember = true;
            }
        }

        return $bIsMember;
    }

    /**
     * isAddr()
     *
     * @access public
     * @param  string  $sAddr
     * @param  array   &$aryBand
     * @return boolean
     */
    function isAddr( $sAddr, &$aryBand )
    {
        if ( '0.0.0.0' == $sAddr )
        {
            return true;
        }

        $iAddr = BlockenCommon::_ip2long( $sAddr );
        if ( ! $iAddr )
        {
            return false;
        }

        foreach ( $aryBand as $sMask )
        {
            $aryTmp = explode( '/', $sMask );

            $sTarget = $aryTmp[ 0 ];
            if ( isset( $aryTmp[ 1 ] ) )
            {
                $sMask = $aryTmp[ 1 ];
            }
            else
            {
                $sMask = '32';
            }

            $iTarget = BlockenCommon::_ip2long( $sTarget );
            if ( ! $iTarget )
            {
                continue;
            }

            $sMask = BlockenCommon::_length2subnet( intval( $sMask ) );
            $iMask = BlockenCommon::_ip2long( $sMask );
            if ( ! $iMask )
            {
                continue;
            }

            if ( ( $iAddr & $iMask ) == ( $iTarget & $iMask ) )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * &makeIPList()
     *
     * @access public
     * @param  string $sFile
     * @return array
     */
    function &makeIPList( $sFile )
    {
        $aryBand = file( $sFile );

        foreach ( $aryBand as $iKey => $sValue )
        {
            $sValue = trim( $sValue );

            if ( '' == $sValue || preg_match( '/^#/', $sValue ) )
            {
                unset( $aryBand[ $iKey ] );
                continue;
            }

            $aryBand[ $iKey ] = $sValue;
        }

        return $aryBand;
    }

    /**
     * checkMailAdress()
     *
     * @access public
     * @param  string  $sEmail
     * @return boolean
     */
    function checkMailAdress( $sEmail )
    {
        if ( preg_match( '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i', $sEmail ) )
        {
            return true;
        }

        return false;
    }

    /**
     * checkDate()
     *
     * @access public
     * @param  string  $sDate
     * @return boolean
     */
    function checkDate( $sDate )
    {
        $aryDate = BlockenCommon::_parseDate( $sDate );
        if ( ! $aryDate )
        {
            return false;
        }

        $bRet = checkdate( $aryDate[ 'month' ], $aryDate[ 'day' ], $aryDate[ 'year' ] );

        return $bRet;
    }

    /**
     * makeTimestamp()
     *
     * @access public
     * @param  string $sDate
     * @param  string $sTarget
     * @param  string $iMove
     * @return mixed           integer | boolean
     */
    function makeTimestamp( $sDate, $sTarget = '', $iMove = 0 )
    {
        $aryDate = BlockenCommon::_parseDate( $sDate );
        if ( ! $aryDate )
        {
            return false;
        }

        if ( '' != $sTarget && isset( $aryDate[ $sTarget ] ) )
        {
            $aryDate[ $sTarget ] += $iMove;
        }

        $iTimestamp = mktime( $aryDate[ 'hour' ], $aryDate[ 'minute' ], $aryDate[ 'second' ],
                              $aryDate[ 'month' ], $aryDate[ 'day' ], $aryDate[ 'year' ] );

        return $iTimestamp;
    }

    /**
     * makeURL()
     *
     * @access public
     * @param  array  $aryParam
     * @return string
     */
    function makeURL( $aryParam )
    {
        unset( $aryParam[ 'uid' ] );
        unset( $aryParam[ 'emoji' ] );

        $sQuery = '';
        foreach ( $aryParam as $sKey => $sValue )
        {
            $sQuery .= $sKey . '=' . urlencode( $sValue ) . '&';
        }
        $sQuery = substr( $sQuery, 0, -1 );

        return $sQuery;
    }

    /**
     * registUrl()
     *
     * @access public
     * @param  string $sRtn
     * @param  array  &$aryPear
     * @return string
     */
    function registUrl( $sRtn, &$aryPear )
    {
        $sRegistUrl = BLOCKEN_HTTP_URL . BLOCKEN_ROOT_PATH . '/';

        if ( $aryPear[ 'mobile' ]->isDoCoMo() )
        {
            $sNl = $sRtn . ( ( preg_match( '/\?/', $sRtn ) ) ? '&' : '?' ) . 'uid=' . BLOCKEN_MOBI_D_UID;

            $sRegistUrl = sprintf( '%s?uid=%s&ci=%s&rl=%s&nl=%s&act=reg&arg1=%s',
                                    BLOCKEN_MOBI_D_MYMENU, BLOCKEN_MOBI_D_UID, BLOCKEN_MOBI_D_CID,
                                    urlencode( BLOCKEN_MOBI_REGIST_URL ), urlencode( $sNl ),
                                    $aryPear[ 'mobile' ]->getCarrierShortName() );
        }
        else if ( $aryPear[ 'mobile' ]->isEZweb() )
        {
            $sRtnUrl = urlencode( $sRtn );

            $sTu = '?_rtn=' . $sRtnUrl . '&arg1=' . $aryPear[ 'mobile' ]->getCarrierShortName();
            $sNu = '?_rtn=' . $sRtnUrl;

            $sRegistUrl = sprintf( '%s?cp=%s&sc=%s&tu=%s&nu=%s&lt=%s&flg=%s',
                                    BLOCKEN_MOBI_E_REGIST, BLOCKEN_MOBI_E_CP, BLOCKEN_MOBI_E_SC,
                                    urlencode( BLOCKEN_MOBI_REGIST_URL . $sTu ),
                                    urlencode( BLOCKEN_MOBI_REGIST_URL . $sNu ),
                                    BLOCKEN_MOBI_E_LT, BLOCKEN_MOBI_E_FLG );
        }
        else if ( $aryPear[ 'mobile' ]->isSoftBank() )
        {
            $sRtnUrl = urlencode( $sRtn );

            $sNl = '?_rtn=' . $sRtnUrl . '&arg1=' . $aryPear[ 'mobile' ]->getCarrierShortName();
            $sCl = '?_rtn=' . $sRtnUrl;

            $sRegistUrl = sprintf( '%s?uid=%s&sid=%s&nl=%s&cl=%s',
                                    BLOCKEN_MOBI_S_REGIST, BLOCKEN_MOBI_S_UID, BLOCKEN_MOBI_S_SID,
                                    urlencode( BLOCKEN_MOBI_REGIST_URL . $sNl ),
                                    urlencode( BLOCKEN_MOBI_REGIST_URL . $sCl ) );
        }
        else
        {
            $sRegistUrl = BLOCKEN_MOBI_REGIST_URL . '?_rtn=' . urlencode( $sRtn )
                        . '&arg1=' . $aryPear[ 'mobile' ]->getCarrierShortName();
        }

        return $sRegistUrl;
    }

    /**
     * expireUrl()
     *
     * @access public
     * @param  string $sRtn
     * @param  array  &$aryPear
     * @return string
     */
    function expireUrl( $sRtn, &$aryPear )
    {
        $sExpireUrl = BLOCKEN_HTTP_URL . BLOCKEN_ROOT_PATH . '/';

        if ( $aryPear[ 'mobile' ]->isDoCoMo() )
        {
            $sExpireUrl = sprintf( '%s?uid=%s&ci=%s&rl=%s&nl=&act=rel&arg1=%s',
                                    BLOCKEN_MOBI_D_MYMENU, BLOCKEN_MOBI_D_UID, BLOCKEN_MOBI_D_CID,
                                    urlencode( BLOCKEN_MOBI_EXPIRE_URL ),
                                    $aryPear[ 'mobile' ]->getCarrierShortName() );
        }
        else if ( $aryPear[ 'mobile' ]->isEZweb() )
        {
            $sRtnUrl = urlencode( $sRtn );

            $sTu = '?_rtn=' . $sRtnUrl . '&arg1=' . $aryPear[ 'mobile' ]->getCarrierShortName();
            $sNu = '?_rtn=' . $sRtnUrl;

            $sExpireUrl = sprintf( '%s?cp=%s&sc=%s&tu=%s&nu=%s&flg=%s',
                                    BLOCKEN_MOBI_E_EXPIRE, BLOCKEN_MOBI_E_CP, BLOCKEN_MOBI_E_SC,
                                    urlencode( BLOCKEN_MOBI_EXPIRE_URL . $sTu ),
                                    urlencode( BLOCKEN_MOBI_EXPIRE_URL . $sNu ),
                                    BLOCKEN_MOBI_E_FLG );
        }
        else if ( $aryPear[ 'mobile' ]->isSoftBank() )
        {
            $sRtnUrl = urlencode( $sRtn );

            $sNl = '?_rtn=' . $sRtnUrl . '&arg1=' . $aryPear[ 'mobile' ]->getCarrierShortName();
            $sCl = '?_rtn=' . $sRtnUrl;

            $sExpireUrl = sprintf( '%s?uid=%s&sid=%s&nl=%s&cl=%s',
                                    BLOCKEN_MOBI_S_EXPIRE, BLOCKEN_MOBI_S_UID, BLOCKEN_MOBI_S_SID,
                                    urlencode( BLOCKEN_MOBI_EXPIRE_URL . $sNl ),
                                    urlencode( BLOCKEN_MOBI_EXPIRE_URL . $sCl ) );
        }
        else
        {
            $sExpireUrl = BLOCKEN_MOBI_EXPIRE_URL . '?_rtn=' . urlencode( $sRtn )
                        . '&arg1=' . $aryPear[ 'mobile' ]->getCarrierShortName();
        }

        return $sExpireUrl;
    }

    /**
     * authCheck()
     *
     * @access public
     * @param  array &$aryPear
     * @param  array &$aryParam
     * @return void
     */
    function authCheck( &$aryPear, &$aryParam )
    {
        if ( ini_get( 'safe_mode' ) )
        {
            return;
        }

        $iAuth = $aryPear[ 'session' ]->get( 'auth', 1 );
        if ( time() < $iAuth )
        {
            return;
        }

        if ( ! isset( $aryParam[ '_reg' ] ) )
        {
            $aryParam[ '_reg' ] = '';
        }

        switch ( $aryParam[ '_reg' ] )
        {
            case '1':
                funcRegistMember( $aryParam[ 'uid' ], $aryPear );
                break;

            case '2':
                funcExpireMember( $aryParam[ 'uid' ], $aryPear );
                break;

            default:
                chdir( BLOCKEN_MOBI_E_AUTHCHK );

                $aryParamTmp = $aryParam;
                unset( $aryParamTmp[ '_reg' ] );

                $sQuery = BlockenCommon::makeURL( $aryParamTmp );

                $sRtnUrl = BLOCKEN_HTTP_URL . $_SERVER[ 'SCRIPT_NAME' ]
                         . ( ( '' == $sQuery ) ? '?_reg=' : '?' . $sQuery . '&_reg=' );

                $sExec = "./authcheck at=11000 cp=" . BLOCKEN_MOBI_E_CP . " sc=" . BLOCKEN_MOBI_E_SC
                       . " tu='{$sRtnUrl}1' nu='{$sRtnUrl}2' lt=" . BLOCKEN_MOBI_E_LT;
                $sBuff = `{$sExec}`;
                header( $sBuff );
                exit;
        }

        $aryPear[ 'session' ]->set( 'auth', time() + BLOCKEN_MOBI_E_AUTHTIME );
    }

    /**
     * outputImage()
     *
     * @access public
     * @param  array &$aryPear
     * @param  array &$aryParam
     * @return void
     */
    function outputImage( &$aryPear, &$aryParam )
    {
        $sFile = BLOCKEN_MOBI_IMG_DIR . "/{$aryParam[ 'file' ]}";

        $arySize = getimagesize( $sFile );

        switch ( $arySize[ 2 ] )
        {
            case IMAGETYPE_JPEG:
            case IMAGETYPE_GIF:
            case IMAGETYPE_PNG:
                header( "Content-type: {$arySize[ 'mime' ]}" );
                break;

            default:
                header( "Content-type: image/gif" );
                readfile( BLOCKEN_MOBI_IMG_DIR . '/dummy.gif' );
                return;
        }

        $objDisplay = $aryPear[ 'mobile' ]->makeDisplay();

        list( $iWidth, $iHeight ) = $objDisplay->getSize();
        if ( 0 == $iWidth )
        {
            $iWidth = 240;
            unset( $aryParam[ 'fixed' ] );
        }

        if ( ! isset( $aryParam[ 'fixed' ] ) )
        {
            $iHeight = floor( $arySize[ 1 ] * ( $iWidth / $arySize[ 0 ] ) );
        }

        include_once 'Cache.php';
        $objCache =& new Cache( BLOCKEN_CACHE_DRIVER, unserialize( BLOCKEN_CACHE_IMAGE ) );

        $sCacheId  = $objCache->generateID( "{$iWidth}_{$iHeight}" );
        $sCacheGrp = str_replace( '/', '__', $aryParam[ 'file' ] );

        $sCacheBuff = $objCache->get( $sCacheId, $sCacheGrp );
        if ( ! is_null( $sCacheBuff ) )
        {
            echo $sCacheBuff;
            return;
        }

        $resSrc = null;
        switch ( $arySize[ 2 ] )
        {
            case IMAGETYPE_JPEG:
                $resSrc = imagecreatefromjpeg( $sFile );
                break;

            case IMAGETYPE_GIF:
                $resSrc = imagecreatefromgif ( $sFile );
                break;

            case IMAGETYPE_PNG:
                $resSrc = imagecreatefrompng( $sFile );
                break;

            default:
                break;
        }

        $resDst = imagecreatetruecolor( $iWidth, $iHeight );

        imagecopyresized( $resDst, $resSrc, 0, 0, 0, 0, $iWidth, $iHeight, $arySize[ 0 ], $arySize[ 1 ] );

        ob_start();

        switch ( $arySize[ 2 ] )
        {
            case IMAGETYPE_JPEG:
                imagejpeg( $resDst );
                break;

            case IMAGETYPE_GIF:
                imagegif ( $resDst );
                break;

            case IMAGETYPE_PNG:
                imagepng( $resDst );
                break;

            default:
                break;
        }

        $sBuff = ob_get_clean();

        imagedestroy( $resDst );

        $iCacheExpire = BLOCKEN_CACHE_EXPIRE;
        if ( isset( $aryParam[ 'expire' ] ) )
        {
            $iCacheExpire = intval( $aryParam[ 'expire' ] );
            if ( 0 > $iCacheExpire )
            {
                $iCacheExpire = BLOCKEN_CACHE_EXPIRE;
            }
        }

        if ( 0 < $iCacheExpire )
        {
            $objCache->save( $sCacheId, $sBuff, $iCacheExpire, $sCacheGrp );
        }

        echo $sBuff;
    }

    /**
     * rmDir()
     *
     * @access public
     * @param  string $sDir
     * @return void
     */
    function rmDir( $sDir )
    {
        if ( is_dir( $sDir ) )
        {
            foreach ( glob( "{$sDir}/*" ) as $sRow )
            {
                if ( is_dir( $sRow ) && ! is_link( $sRow ) )
                {
                    BlockenCommon::rmDir( $sRow );
                }
                else
                {
                    unlink( $sRow );
                }
            }
            rmdir( $sDir );
        }
    }

    /**
     * _ip2long()
     *
     * @access private
     * @param  string $sIp
     * @return mixed       integer | boolean
     */
    function _ip2long( $sIp )
    {
        $iLong = ip2long( $sIp );
        if ( -1 == $iLong && '255.255.255.255' != $sIp )
        {
            return false;
        }

        return $iLong;
    }

    /**
     * _length2subnet()
     *
     * @access private
     * @param  integer $iLength
     * @return string
     */
    function _length2subnet( $iLength )
    {
        $arySubnet = array();

        for ( $i = 0; $i < 4; $i++ )
        {
            if ( 8 <= $iLength )
            {
                $arySubnet[] = '255';
            }
            else if ( 0 < $iLength )
            {
                $arySubnet[] = strval( 255 & ~bindec( str_repeat( '1', 8 - $iLength ) ) );
            }
            else
            {
                $arySubnet[] = '0';
            }
            $iLength -= 8;
        }

        return implode( '.', $arySubnet );
    }

    /**
     * _parseDate()
     *
     * @access private
     * @param  string $sDate
     * @return mixed         array: | boolean
     */
    function _parseDate( $sDate )
    {
        $aryDate[ 'year' ]   = 0;
        $aryDate[ 'month' ]  = 0;
        $aryDate[ 'day' ]    = 0;
        $aryDate[ 'hour' ]   = 0;
        $aryDate[ 'minute' ] = 0;
        $aryDate[ 'second' ] = 0;

        // YYYYMMDD
        if ( preg_match( '/^(\d{4})(\d{2})(\d{2})$/', $sDate, $aryMatches ) )
        {
            $aryDate[ 'year' ]  = intval( $aryMatches[ 1 ] );
            $aryDate[ 'month' ] = intval( $aryMatches[ 2 ] );
            $aryDate[ 'day' ]   = intval( $aryMatches[ 3 ] );
        }
        // YYYYMMDDHHIISS
        else if ( preg_match( '/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/', $sDate, $aryMatches ) )
        {
            $aryDate[ 'year' ]   = intval( $aryMatches[ 1 ] );
            $aryDate[ 'month' ]  = intval( $aryMatches[ 2 ] );
            $aryDate[ 'day' ]    = intval( $aryMatches[ 3 ] );
            $aryDate[ 'hour' ]   = intval( $aryMatches[ 4 ] );
            $aryDate[ 'minute' ] = intval( $aryMatches[ 5 ] );
            $aryDate[ 'second' ] = intval( $aryMatches[ 6 ] );
        }
        // Y-M-D
        // Y/M/D
        else if ( preg_match( '/^(\d{2,4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $sDate, $aryMatches ) )
        {
            $aryDate[ 'year' ]  = intval( $aryMatches[ 1 ] );
            if ( 1000 > $aryDate[ 'year' ] )
            {
                $aryDate[ 'year' ] += 2000;
            }
            $aryDate[ 'month' ] = intval( $aryMatches[ 2 ] );
            $aryDate[ 'day' ]   = intval( $aryMatches[ 3 ] );
        }
        // Y-M-D H:I:S
        // Y/M/D H:I:S
        else if ( preg_match( '/^(\d{2,4})[-\/](\d{1,2})[-\/](\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/',
                             $sDate, $aryMatches ) )
        {
            $aryDate[ 'year' ]  = intval( $aryMatches[ 1 ] );
            if ( 1000 > $aryDate[ 'year' ] )
            {
                $aryDate[ 'year' ] += 2000;
            }
            $aryDate[ 'month' ]  = intval( $aryMatches[ 2 ] );
            $aryDate[ 'day' ]    = intval( $aryMatches[ 3 ] );
            $aryDate[ 'hour' ]   = intval( $aryMatches[ 4 ] );
            $aryDate[ 'minute' ] = intval( $aryMatches[ 5 ] );
            $aryDate[ 'second' ] = intval( $aryMatches[ 6 ] );
        }
        else
        {
            return false;
        }

        return $aryDate;
    }
}

/**
 * loadTemplate()
 *
 * @param  booean $bIsPHP
 * @return mixed          BlockenTemplate | void
 */
function loadTemplate( $bIsPHP = true )
{
    global $aryPear;
    global $aryParam;
    global $sLoadFilename;
    global $bIsAuth;
    global $iCacheExpire;
    global $sContentType;

    if ( ! isset( $bIsAuth ) )
    {
        $bIsAuth = false;
    }

    if ( ! isset( $iCacheExpire ) )
    {
        $iCacheExpire = 0;
    }
    else if ( 0 > $iCacheExpire )
    {
        $iCacheExpire = BLOCKEN_CACHE_EXPIRE;
    }

    if ( ! isset( $sContentType ) )
    {
        $sContentType = 'html';
    }

    if ( '' != $sLoadFilename )
    {
        $bIsPHP = false;
    }

    $objBlocken =& new BlockenController();

    if ( ! $bIsPHP )
    {
        if ( '' == $sLoadFilename )
        {
            $sLoadFilename = $_SERVER[ 'SCRIPT_FILENAME' ];

            if ( BLOCKEN_MOBILE_USE )
            {
                if ( $aryPear[ 'mobile' ]->isDoCoMo() )
                {
                    $sLoadFilename = BLOCKEN_MOBI_D_TEMPLATE . '/' . basename( $_SERVER[ 'SCRIPT_FILENAME' ] );
                }
                else if ( $aryPear[ 'mobile' ]->isEZweb() )
                {
                    $sLoadFilename = BLOCKEN_MOBI_E_TEMPLATE . '/' . basename( $_SERVER[ 'SCRIPT_FILENAME' ] );
                }
                else if ( $aryPear[ 'mobile' ]->isSoftBank() )
                {
                    $sLoadFilename = BLOCKEN_MOBI_S_TEMPLATE . '/' . basename( $_SERVER[ 'SCRIPT_FILENAME' ] );
                }
                else
                {
                    $sLoadFilename = BLOCKEN_MOBI_P_TEMPLATE . '/' . basename( $_SERVER[ 'SCRIPT_FILENAME' ] );
                }

                if ( ! is_readable( $sLoadFilename ) )
                {
                    $sLoadFilename = $_SERVER[ 'SCRIPT_FILENAME' ];
                }
            }

            $sBuff = file_get_contents( $sLoadFilename );

            if ( BLOCKEN_MOBILE_USE )
            {
                $objBlocken->setIsHankaku( true );
                $objBlocken->parseMobile( $sBuff );
                $bIsAuth = $objBlocken->getIsAuth();
            }
        }
        else
        {
            $sBuff = file_get_contents( $sLoadFilename );

            if ( BLOCKEN_MOBILE_USE && BLOCKEN_MOBI_P_REDIRECT != $sLoadFilename )
            {
                $objBlocken->setIsHankaku( true );
            }
        }
    }

    if ( BLOCKEN_MOBILE_USE && $bIsAuth )
    {
        if ( ! isset( $aryParam[ 'uid' ] ) )
        {
            if ( ! $aryPear[ 'mobile' ]->isNonMobile() )
            {
                if ( $aryPear[ 'mobile' ]->isDoCoMo() )
                {
                    if ( BLOCKEN_MOBI_OFFCIAL )
                    {
                        $sLoadFilename = BLOCKEN_MOBI_D_UIDERR;
                    }
                    else
                    {
                        if ( 'GUID' == BLOCKEN_MOBI_D_AUTHUID )
                        {
                            $sLoadFilename = BLOCKEN_MOBI_D_GUIDERR;
                        }
                        else
                        {
                            $sLoadFilename = BLOCKEN_MOBI_D_UTN;
                            $aryParamTmp = $aryParam;
                            $aryParamTmp[ '_utn' ] = '1';
                            unset( $aryParamTmp[ $aryPear[ 'session' ]->name() ] );
                            $sQuery = BlockenCommon::makeURL( $aryParamTmp );
                            $sRtnUrl = $_SERVER[ 'SCRIPT_NAME' ] . ( ( '' == $sQuery ) ? '' : '?' . $sQuery );
                            $objBlocken->setRepStr( '/%RTN_PATH%/i', $sRtnUrl );
                        }
                    }
                }
                else if ( $aryPear[ 'mobile' ]->isEZweb() )
                {
                    $sLoadFilename = BLOCKEN_MOBI_E_SUBNOERR;
                }
                else if ( $aryPear[ 'mobile' ]->isSoftBank() )
                {
                    $sLoadFilename = BLOCKEN_MOBI_S_UIDERR;
                }
                $sBuff = file_get_contents( $sLoadFilename );
                $objBlocken->setIsHankaku( true );
                $bIsPHP = false;
            }
        }
        else
        {
            if ( BLOCKEN_MOBI_OFFCIAL && 0 != BLOCKEN_MOBI_E_AUTHTIME && $aryPear[ 'mobile' ]->isEZweb() )
            {
                BlockenCommon::authCheck( $aryPear, $aryParam );
            }

            if ( ! funcIsMember( $aryParam[ 'uid' ], $aryPear ) )
            {
                $sLoadFilename = BLOCKEN_MOBI_REGIST;
                $sBuff = file_get_contents( $sLoadFilename );
                $objBlocken->setIsHankaku( true );
                $bIsPHP = false;

                $sQuery = BlockenCommon::makeURL( $aryParam );
                $sRtnUrl = urlencode( BLOCKEN_HTTP_URL . $_SERVER[ 'SCRIPT_NAME' ]
                                      . ( ( '' == $sQuery ) ? '' : '?' . $sQuery ) );
                $objBlocken->setAddParam( '_rtn', $sRtnUrl );
            }
        }
    }

    if ( ! $bIsPHP )
    {
        if ( preg_match( '/\.(?:xhtml|xhtm)$/', $sLoadFilename ) )
        {
            $sContentType = 'xhtml';
        }
        else if ( preg_match( '/\.xml$/', $sLoadFilename ) )
        {
            $sContentType = 'xml';
        }
        else if ( preg_match( '/\.hdml$/', $sLoadFilename ) )
        {
            $sContentType = 'hdml';
        }

        $objBlocken->setHtml( $sBuff );

        $objBlocken->setPear( $aryPear );

        $objBlocken->setParam( $aryParam );

        $objBlocken->parse();

        while ( $objBlocken->hasNextBlock() )
        {
            $sType = $objBlocken->getBlockType();

            switch ( $sType )
            {
                case 'dynamic':
                    $objBlocken->execScript();
                    break;

                case 'static':
                default:
                    $objBlocken->readHtml();
                    break;
            }
        }
    }
    else
    {
        $sTemplateFile = str_replace( '.php', '.html', basename( $_SERVER[ 'SCRIPT_FILENAME' ] ) );
        $sTemplateDir  = BLOCKEN_TEMPLATE_NAME;
        $sCacheDir     = BLOCKEN_CACHE_NAME;
        if ( BLOCKEN_MOBILE_USE )
        {
            if ( $aryPear[ 'mobile' ]->isDoCoMo() )
            {
                if ( file_exists( BLOCKEN_MOBI_D_TEMPLATE . '/' . $sTemplateFile ) )
                {
                    $sTemplateDir = BLOCKEN_MOBI_D_TEMPLATE;
                    $sCacheDir    = BLOCKEN_MOBI_D_CACHE;
                }
            }
            else if ( $aryPear[ 'mobile' ]->isEZweb() )
            {
                if ( file_exists( BLOCKEN_MOBI_E_TEMPLATE . '/' . $sTemplateFile ) )
                {
                    $sTemplateDir = BLOCKEN_MOBI_E_TEMPLATE;
                    $sCacheDir    = BLOCKEN_MOBI_E_CACHE;
                }
            }
            else if ( $aryPear[ 'mobile' ]->isSoftBank() )
            {
                if ( file_exists( BLOCKEN_MOBI_S_TEMPLATE . '/' . $sTemplateFile ) )
                {
                    $sTemplateDir = BLOCKEN_MOBI_S_TEMPLATE;
                    $sCacheDir    = BLOCKEN_MOBI_S_CACHE;
                }
            }
            else
            {
                if ( file_exists( BLOCKEN_MOBI_P_TEMPLATE . '/' . $sTemplateFile ) )
                {
                    $sTemplateDir = BLOCKEN_MOBI_P_TEMPLATE;
                    $sCacheDir    = BLOCKEN_MOBI_P_CACHE;
                }
            }
        }

        $objBlocken =& new BlockenTemplate( $sTemplateDir, $sCacheDir, unserialize( BLOCKEN_CACHE_PAGE ) );

        if ( BLOCKEN_MOBILE_USE )
        {
            $objBlocken->setIsHankaku( true );
        }

        $objBlocken->setHtmlCacheExpire( $iCacheExpire );

        $aryParamCache = $aryParam;
        unset( $aryParamCache[ 'uid' ] );
        unset( $aryParamCache[ 'sid' ] );
        unset( $aryParamCache[ 'emoji' ] );
        unset( $aryParamCache[ 'guid' ] );
        unset( $aryParamCache[ '_utn' ] );
        unset( $aryParamCache[ '_reg' ] );
        unset( $aryParamCache[ $aryPear[ 'session' ]->name() ] );

        $argArgCache[ 'template' ] = str_replace( array( '/', '.php' ), array( '__', '' ), $_SERVER[ 'SCRIPT_NAME' ] );
        $argArgCache[ 'template_dir' ] = $sTemplateDir;

        $objBlocken->setCacheParam( $aryParamCache, $argArgCache );

        $objBlocken->setParam( $aryParam );

        $objBlocken->loadTemplateFile( $sTemplateFile );
    }

    $objBlocken->setRepStr( '/<\?php.*\?>/',  '' );
    $objBlocken->setRepStr( '/%HTTP_URL%/i',  BLOCKEN_HTTP_URL );
    $objBlocken->setRepStr( '/%HTTPS_URL%/i', BLOCKEN_HTTPS_URL );
    $objBlocken->setRepStr( '/%PHP_SELF%/i',  $_SERVER[ 'PHP_SELF' ] );
    $objBlocken->setRepStr( '/%ROOT_PATH%/i', BLOCKEN_ROOT_PATH );
    $objBlocken->setRepStr( '/%IMG_PATH%/i',  BLOCKEN_IMG_PATH );
    $objBlocken->setRepStr( '/%CSS_PATH%/i',  BLOCKEN_CSS_PATH );
    $objBlocken->setRepStr( '/%JS_PATH%/i',   BLOCKEN_JS_PATH );

    if ( BLOCKEN_MOBILE_USE && BLOCKEN_MOBI_P_REDIRECT != $sLoadFilename )
    {
        if ( BLOCKEN_MOBI_AUTOHEADER )
        {
            $sHead = file_get_contents( BLOCKEN_MOBI_AUTOHEADER );
            $objBlocken->setRepStr( '/<body(.*)>/i', "<body\\1>\n{$sHead}" );
        }

        if ( BLOCKEN_MOBI_AUTOFOOTER )
        {
            $sFoot = file_get_contents( BLOCKEN_MOBI_AUTOFOOTER );
            $objBlocken->setRepStr( '/<\/body>/i', "{$sFoot}\n</body>" );
        }

        if ( ! $aryPear[ 'mobile' ]->isNonMobile() )
        {
            if ( BLOCKEN_MOBI_OFFCIAL )
            {
                if ( $aryPear[ 'mobile' ]->isDoCoMo() )
                {
                    $objBlocken->setAddParam( 'uid', BLOCKEN_MOBI_D_UID );
                    $objBlocken->setRepStr( '/(.*):(.*)[\?&]uid=' . BLOCKEN_MOBI_D_UID . '/i',
                                            '\\1:\\2' );
                }
                else if ( $aryPear[ 'mobile' ]->isSoftBank() )
                {
                    $objBlocken->setAddParam( 'uid', BLOCKEN_MOBI_S_UID );
                    $objBlocken->setAddParam( 'sid', BLOCKEN_MOBI_S_SID );
                    $objBlocken->setRepStr( '/(.*):(.*)[\?&]uid=' . BLOCKEN_MOBI_S_UID
                                            . '&sid=' . BLOCKEN_MOBI_S_SID . '/i',
                                            '\\1:\\2' );
                }
            }
            else
            {
                if ( $aryPear[ 'mobile' ]->isDoCoMo() )
                {
                    if ( 'GUID' == BLOCKEN_MOBI_D_AUTHUID )
                    {
                        $objBlocken->setAddParam( 'guid', BLOCKEN_MOBI_D_GUID );
                        $objBlocken->setRepStr( '/(.*):(.*)[\?&]guid=' . BLOCKEN_MOBI_D_GUID . '/i',
                                                '\\1:\\2' );
                    }
                    else
                    {
                        if ( isset( $aryParam[ 'uid' ] ) )
                        {
                            $objBlocken->setAddParam( $aryPear[ 'session' ]->name(), $aryPear[ 'session' ]->id() );
                            $objBlocken->setRepStr( '/(.*):(.*)[\?&]'
                                                    . $aryPear[ 'session' ]->name() . '='
                                                    . $aryPear[ 'session' ]->id() . '/i',
                                                    '\\1:\\2' );
                        }
                    }
                }
            }

            if ( BLOCKEN_MOBI_E_BOOKMARK && $aryPear[ 'mobile' ]->isEZweb() )
            {
                $objBlocken->setRepStr( '/<head>/i',
                                        "<head>\n<meta name=\"vnd.up.bookmark\" wml:forua=\"true\" content=\""
                                        . BLOCKEN_MOBI_E_BOOKMARK . "\" />" );
            }

            if ( $aryPear[ 'mobile' ]->isSoftBank() && $aryPear[ 'mobile' ]->isTypeC() )
            {
                $objBlocken->setRepStr( '/<form(.*)method=[\"\']post[\"\'](.*)>/i', '<form\\1method="get"\\2>' );
            }

            $objBlocken->setRepStr( '/<meta(.*)charset=UTF-8/i', '<meta\\1charset=Shift_JIS' );

            if ( 'xhtml' != $sContentType )
            {
                $objBlocken->setRepStr( '/<(.*) \/>/', '<\\1>' );
                $objBlocken->setRepStr( '/<(.*)\/>/', '<\\1>' );
            }
            else
            {
                $objBlocken->setRepStr( '/<\?xml(.*)encoding="UTF-8"/i', '<?xml\\1encoding="Shift_JIS"' );
            }
        }
        else
        {
            if ( isset( $aryParam[ 'uid' ] ) && BLOCKEN_MOBI_P_DEBUGUID != $aryParam[ 'uid' ] )
            {
                $objBlocken->setAddParam( 'uid', $aryParam[ 'uid' ] );
                $objBlocken->setRepStr( '/(.*):(.*)[\?&]uid=' . $aryParam[ 'uid' ] . '/i', '\\1:\\2' );
            }
        }
    }

    $objBlocken->setRepStr( BlockenMobile::unicode2sjis() );
    $objBlocken->setRepStr( $aryPear[ 'mobile' ]->convertEmoji() );

    switch ( $sContentType )
    {
        case 'xhtml':
            if ( BLOCKEN_MOBILE_USE && ! $aryPear[ 'mobile' ]->isNonMobile() )
            {
                ob_start( '_bcomOutputHandler' );
                header( 'Content-type: application/xhtml+xml; charset=Shift_JIS' );
            }
            else
            {
                header( 'Content-type: application/xhtml+xml; charset=UTF-8' );
            }
            break;

        case 'xml':
            header( 'Content-type: text/xml; charset=UTF-8' );
            $objBlocken->setIsDebug( false );
            break;

        case 'hdml':
            header( 'Content-type: text/x-hdml; charset=Shift_JIS' );
            $objBlocken->setIsDebug( false );
            break;
    }

    if ( BLOCKEN_MOBILE_USE && $aryPear[ 'mobile' ]->isEZweb() )
    {
        header( 'Expires: Sat, 01 Jan 2000 00:00:00 GMT' );
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Pragma: no-cache' );
    }

    if ( ! $bIsPHP )
    {
        echo $objBlocken->getHtml();
        exit;
    }

    if ( ! $objBlocken->isExpired() )
    {
        $objBlocken->showCache();
        exit;
    }

    return $objBlocken;
}

/**
 * _bcomOutputHandler()
 *
 * @param  string $sBuff
 * @return string
 */
function _bcomOutputHandler( $sBuff )
{
    $sBuff = mb_convert_encoding( $sBuff, 'SJIS', 'UTF-8' );

    return $sBuff;
}
?>
