<?php
/**
 * BlockenTemplate.class.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: BlockenTemplate.class.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

require_once 'HTML/Template/Sigma.php';
require_once 'Cache.php';

class BlockenTemplate extends HTML_Template_Sigma
{
    /**
     * @access private
     * @var    integer $_iHtmlCacheExpire
     */
    var $_iHtmlCacheExpire = 0;

    /**
     * @access private
     * @var    boolean $_bTemplateCacheExpire
     */
    var $_bTemplateCacheExpire = false;

    /**
     * @access private
     * @var    object $_objCache
     */
    var $_objCache = null;

    /**
     * @access private
     * @var    string $_sCacheId
     */
    var $_sCacheId = '';

    /**
     * @access private
     * @var    string $_sCacheGrp
     */
    var $_sCacheGrp = '';

    /**
     * @access private
     * @var    string $_sCacheBuff
     */
    var $_sCacheBuff = '';

    /**
     * @access private
     * @var    float $_fStart
     */
    var $_fStart = 0;

    /**
     * @access private
     * @var    float $_fEnd
     */
    var $_fEnd = 0;

    /**
     * @access private
     * @var    array $_aryParam
     */
    var $_aryParam = array();

    /**
     * @access private
     * @var    array $_aryAddParam
     */
    var $_aryAddParam = array();

    /**
     * @access private
     * @var    array $_aryRepStr
     */
    var $_aryRepStr = array();

    /**
     * @access private
     * @var    boolean $_bCacheHit
     */
    var $_bCacheHit = false;

    /**
     * @access private
     * @var    boolean $_bIsHankaku
     */
    var $_bIsHankaku = false;

    /**
     * @access private
     * @var    boolean $_bIsDebug
     */
    var $_bIsDebug = true;

    /**
     * BlockenTemplate()
     *
     * @param string $sTemplateDir
     * @param string $sSigmaCacheDir
     * @param array  $arrayCacheOption
     */
    function BlockenTemplate( $sTemplateDir, $sSigmaCacheDir, $arrayCacheOption )
    {
        if ( BLOCKEN_DEBUG_MODE && ( BLOCKEN_ALWAYS_DEBUG || BlockenCommon::isMember() ) )
        {
            $this->_fStart = BlockenCommon::getMicroTime();
        }

        parent::HTML_Template_Sigma( $sTemplateDir, $sSigmaCacheDir );

        parent::setCallbackFunction( 'c', '_btplConvertValue' );
        parent::setCallbackFunction( 'd', '_btplDateFormat' );
        parent::setCallbackFunction( 'k', 'mb_convert_kana' );
        parent::setCallbackFunction( 'm', 'number_format' );
        parent::setCallbackFunction( 'b', 'nl2br' );

        $this->_objCache =& new Cache( BLOCKEN_CACHE_DRIVER, $arrayCacheOption );
    }

    /**
     * setHtmlCacheExpire()
     *
     * @access public
     * @param  integer $iExpire
     * @return void
     */
    function setHtmlCacheExpire( $iExpire = 0 )
    {
        $this->_iHtmlCacheExpire = $iExpire;
    }

    /**
     * disnableHtmlCacheExpire()
     *
     * @access public
     * @return void
     */
    function disnableHtmlCacheExpire()
    {
        $this->setHtmlCacheExpire();
    }

    /**
     * setCacheParam()
     *
     * @access public
     * @param  array &$aryParam
     * @param  array &$aryArgs
     * @return void
     */
    function setCacheParam( &$aryParam, &$aryArgs )
    {
        $aryParamCache = $aryParam;
        ksort( $aryParamCache );

        $aryArgsCache = $aryArgs;
        ksort( $aryArgsCache );

        $this->_sCacheId  = $this->_objCache->generateID( serialize( $aryParamCache ) . serialize( $aryArgsCache ) );
        $this->_sCacheGrp = str_replace( array( '/', '.html' ), array( '__', '' ), $aryArgs[ 'template' ] );
    }

    /**
     * setParam()
     *
     * @access public
     * @param  array &$aryParam
     * @return void
     */
    function setParam( &$aryParam )
    {
        $this->_aryParam = $aryParam;
    }

    /**
     * setAddParam()
     *
     * @access public
     * @param  mixed  $mKey   string | array
     * @param  string $sValue
     * @return void
     */
    function setAddParam( $mKey, $sValue = '' )
    {
        if ( is_array( $mKey ) )
        {
            $this->_aryAddParam += $mKey;
        }
        else
        {
            $this->_aryAddParam[ $mKey ] = $sValue;
        }
    }

    /**
     * setRepStr()
     *
     * @access public
     * @param  mixed  $mSearch  string | array
     * @param  string $sReplace
     * @return void
     */
    function setRepStr( $mSearch, $sReplace = '' )
    {
        if ( is_array( $mSearch ) )
        {
            $this->_aryRepStr += $mSearch;
        }
        else
        {
            $this->_aryRepStr[ $mSearch ] = $sReplace;
        }
    }

    /**
     * setIsHankaku()
     *
     * @access public
     * @param  boolean $bIsHankaku
     * @return void
     */
    function setIsHankaku( $bIsHankaku )
    {
        $this->_bIsHankaku = $bIsHankaku;
    }

    /**
     * setIsDebug()
     *
     * @access public
     * @param  boolean $bIsDebug
     * @return void
     */
    function setIsDebug( $bIsDebug )
    {
        $this->_bIsDebug = $bIsDebug;
    }

    /**
     * loadTemplateFile()
     *
     * @access public
     * @param  string $sTemplateFile
     * @return void
     */
    function loadTemplateFile( $sTemplateFile )
    {
        $this->_bTemplateCacheExpire = parent::_isCached( $sTemplateFile );

        parent::loadTemplateFile( $sTemplateFile );
    }

    /**
     * isExpired()
     *
     * @access public
     * @return boolean
     */
    function isExpired()
    {
        if ( 0 == $this->_iHtmlCacheExpire )
        {
            return true;
        }

        if ( ! $this->_bTemplateCacheExpire )
        {
            return true;
        }

        $this->_sCacheBuff = $this->_objCache->get( $this->_sCacheId, $this->_sCacheGrp );
        if ( is_null( $this->_sCacheBuff ) )
        {
            return true;
        }

        return false;
    }

    /**
     * &getCache()
     *
     * @access public
     * @return string
     */
    function &getCache()
    {
        return $this->_sCacheBuff;
    }

    /**
     * &get()
     *
     * @access public
     * @return string
     */
    function &get()
    {
        $sBuff = parent::get();

        if ( 0 < $this->_iHtmlCacheExpire )
        {
            $this->_objCache->save( $this->_sCacheId, $sBuff, $this->_iHtmlCacheExpire, $this->_sCacheGrp );
        }

        return $sBuff;
    }

    /**
     * showCache()
     *
     * @access public
     * @return void
     */
    function showCache()
    {
        $sBuff = $this->getCache();

        $this->_bCacheHit = true;

        $this->_show( $sBuff );
    }

    /**
     * show()
     *
     * @access public
     * @return void
     */
    function show()
    {
        $sBuff = parent::get();

        if ( 0 < $this->_iHtmlCacheExpire )
        {
            $this->_objCache->save( $this->_sCacheId, $sBuff, $this->_iHtmlCacheExpire, $this->_sCacheGrp );
        }

        $this->_show( $sBuff );
    }

    /**
     * setVariable()
     *
     * @access public
     * @param  mixed  $mVariable string | array
     * @param  mixed  $mValue    string | integer | float | array
     * @param  string $sConvert
     * @param  mixed  $mFormat   string | integer
     * @return void
     */
    function setVariable( $mVariable, $mValue = '', $sConvert = '', $mFormat = 0 )
    {
        if ( is_array( $mVariable ) )
        {
            foreach ( $mVariable as $sKey => $mRow )
            {
                $mVariable[ $sKey ] = _btplConvertValue( $mRow, $sConvert, $mFormat );
            }
        }
        else
        {
            $mValue = _btplConvertValue( $mValue, $sConvert, $mFormat );
        }

        parent::setVariable( $mVariable, $mValue );
    }

    /**
     * getTakeTime()
     *
     * @access public
     * @return string
     */
    function getTakeTime()
    {
        $sTakeTime = '';

        if ( BLOCKEN_DEBUG_MODE && ( BLOCKEN_ALWAYS_DEBUG || BlockenCommon::isMember() ) )
        {
            $this->_fEnd = BlockenCommon::getMicroTime();

            $sTakeTime = sprintf( 'take time %01.03f sec.', $this->_fEnd - $this->_fStart );
        }

        return $sTakeTime;
    }

    /**
     * _show()
     *
     * @access private
     * @param  string &$sBuff
     * @return void
     */
    function _show( &$sBuff )
    {
        $sBuff = $this->_addParam( $sBuff );

        $sBuff = $this->_repStr( $sBuff );

        if ( $this->_bIsHankaku )
        {
            $sBuff = mb_convert_kana( $sBuff, 'aks' );
        }

        $sBuff = $this->_debug( $sBuff );

        $sBuff = trim( $sBuff );

        echo $sBuff;
    }

    /**
     * &_debug()
     *
     * @access private
     * @param  string &$sBuff
     * @return string
     */
    function &_debug( &$sBuff )
    {
        if ( BLOCKEN_DEBUG_MODE && ( BLOCKEN_ALWAYS_DEBUG || BlockenCommon::isMember() ) )
        {
            $this->_fEnd = BlockenCommon::getMicroTime();

            $fTakeTime = $this->_fEnd - $this->_fStart;

            if ( $this->_bIsDebug )
            {
                $sDebug  = sprintf( "\n<br />take time %01.03f sec.<br />\n", $fTakeTime );
                $sDebug .= sprintf( "<pre>\n\$aryParam = %s\n</pre>\n", var_export( $this->_aryParam, true ) );

                if ( BLOCKEN_TIME_OVER < $fTakeTime )
                {
                    $sDebug .= sprintf( "<script language=\"JavaScript\"><!--\nalert(\"take time %01.03f sec.\");\n// --></script>\n", $fiTakeTime );
                }

                $sBuff = preg_replace( '/<\/body>/i', "{$sDebug}\n</body>", $sBuff );

                if ( $this->_bCacheHit )
                {
                    $sBuff = preg_replace( '/<body(.*)>/i', "<body\\1>\n(Cache Hit)<br />", $sBuff );
                }
            }
            else
            {
                $sDebug = sprintf( "<!--\ntake time %01.03f sec.\n\n\$aryParam = %s\n-->\n",
                                   $fTakeTime, var_export( $this->_aryParam, true ) );

                $sBuff .= $sDebug;
            }
        }

        return $sBuff;
    }

    /**
     * &_addParam()
     *
     * @access private
     * @param  string &$sBuff
     * @return string
     */
    function &_addParam( &$sBuff )
    {
        foreach ( $this->_aryAddParam as $sKey => $sValue )
        {
            $sKey   = htmlspecialchars( $sKey );
            $sValue = htmlspecialchars( $sValue );

            $sBuff = str_replace( '\\', '\\\\', $sBuff );

            $aryPattern = array();
            $aryReplace = array();
            // <a href>
            $aryPattern[] = "/<a href=[\"'][^#][^\"^']*[\"']/ie";
            $aryReplace[] = "substr( '\\0', 0, strlen( '\\0' ) - 1 )"
                          . " . ( ( 0 < strpos( '\\0', '?' ) ) ? '&' : '?' )"
                          . " . \"{$sKey}={$sValue}\" . substr( '\\0', strlen( '\\0' ) - 1, 1 )";
            // <form>
            if ( 'guid' == $sKey )
            {
                $aryPattern[] = "/<form .*method=[\"']post[\"'] .*action=[\"'][^#][^\"^']*[\"']/ie";
                $aryReplace[] = "substr( '\\0', 0, strlen( '\\0' ) - 1 )"
                              . " . ( ( 0 < strpos( '\\0', '?' ) ) ? '&' : '?' )"
                              . " . \"{$sKey}={$sValue}\" . substr( '\\0', strlen( '\\0' ) - 1, 1 )";
                $aryPattern[] = "/(<form .*action=[\"'][^#][^\"^']*)([\"'] .*method=[\"']post[\"'])/ie";
                $aryReplace[] = "'\\1' . ( ( 0 < strpos( '\\1', '?' ) ) ? '&' : '?' )"
                              . " . \"{$sKey}={$sValue}\" . '\\2'";
                $aryPattern[] = "/<form .*method=[\"']get[\"']*[^>]*>/ie";
                $aryReplace[] = "'\\0' . \"\n\" . '<input type=\"hidden\" name=\"{$sKey}\" value=\"{$sValue}\" />'";
            }
            else
            {
                $aryPattern[] = "/<form[^>]*>/ie";
                $aryReplace[] = "'\\0' . \"\n\" . '<input type=\"hidden\" name=\"{$sKey}\" value=\"{$sValue}\" />'";
            }

            $sBuff = preg_replace( $aryPattern, $aryReplace, $sBuff );

            // \\
            $sBuff = stripslashes( $sBuff );
        }

        return $sBuff;
    }

    /**
     * &_repStr()
     *
     * @access private
     * @param  string &$sBuff
     * @return string
     */
    function &_repStr( &$sBuff )
    {
        $sBuff = preg_replace( array_keys( $this->_aryRepStr ), array_values( $this->_aryRepStr ), $sBuff );

        return $sBuff;
    }
}

/**
 * _btplConvertValue()
 *
 * @param  mixed  $mValue   string | integer | float | array
 * @param  string $sConvert
 * @param  mixed  $mFormat  string | integer
 * @return string
 */
function _btplConvertValue( $mValue, $sConvert = '', $mFormat = '' )
{
    // mb_convert_kana
    if ( preg_match( '/[rRnNaAsSkKcCV]/', $sConvert ) )
    {
        $mValue = @mb_convert_kana( $mValue, $sConvert );
    }

    // date_format
    if ( false !== strpos( $sConvert, 'd' ) )
    {
        $mValue = @date( $mFormat, $mValue );
    }

    // money_format
    if ( false !== strpos( $sConvert, 'm' ) )
    {
        $mValue = @number_format( $mValue, intval( $mFormat ) );
    }

    // urlencode
    if ( false !== strpos( $sConvert, 'u' ) )
    {
        $mValue = urlencode( $mValue );
    }

    // htmlspecialchars
    if ( false === strpos( $sConvert, 'h' ) )
    {
        $mValue = htmlspecialchars( $mValue );
    }

    // nl2br
    if ( false !== strpos( $sConvert, 'b' ) )
    {
        $mValue = nl2br( $mValue );
    }

    return $mValue;
}

/**
 * _btplDateFormat()
 *
 * @param  integer $iTimestamp
 * @param  string  $sFormat
 * @return string
 */
function _btplDateFormat( $iTimestamp, $sFormat = '' )
{
    $sBuff = @date( $sFormat, $iTimestamp );

    return $sBuff;
}
?>
