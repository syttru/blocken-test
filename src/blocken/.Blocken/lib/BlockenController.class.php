<?php
/**
 * BlockenController.class.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: BlockenController.class.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

class BlockenController
{
    /**
     * @access private
     * @var    string $_sHtml
     */
    var $_sHtml = '';

    /**
     * @access private
     * @var    string $_sPattern
     */
    var $_sPattern = '/<block([^>]*)>/i';

    /**
     * @access private
     * @var    string $_sPatternMob
     */
    var $_sPatternMob = '/<mobile([^>]*)>/i';

    /**
     * @access private
     * @var    array $_aryMatches
     */
    var $_aryMatches = array();

    /**
     * @access private
     * @var    integer $_iCount
     */
    var $_iCount = -1;

    /**
     * @access private
     * @var    array $_aryAttribute
     */
    var $_aryAttribute = array();

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
     * @var    array $_aryPear
     */
    var $_aryPear = array();

    /**
     * @access private
     * @var    array $_aryParam
     */
    var $_aryParam = array();

    /**
     * @access private
     * @var    array $_aryArgs
     */
    var $_aryArgs = array();

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
     * @var    boolean $_bIsAuth
     */
    var $_bIsAuth = false;

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
     * BlockenController()
     */
    function BlockenController()
    {
        if ( BLOCKEN_DEBUG_MODE && ( BLOCKEN_ALWAYS_DEBUG || BlockenCommon::isMember() ) )
        {
            $this->_fStart = BlockenCommon::getMicroTime();
        }

        if ( function_exists( 'tidy_set_encoding' ) )
        {
            tidy_set_encoding( 'UTF8' );
            tidy_setopt( 'input-xml', true );
        }
    }

    /**
     * parseMobile()
     *
     * @access public
     * @param  string &$sHtml
     * @return void
     */
    function parseMobile( &$sHtml )
    {
        if ( preg_match( $this->_sPatternMob, $sHtml, $aryMatches ) )
        {
            $this->_parseAttribute( $aryMatches[ 0 ], $aryAttribute );

            if ( isset( $aryAttribute[ 'auth' ] ) )
            {
                switch ( strtolower( trim( $aryAttribute[ 'auth' ] ) ) )
                {
                    case '1';
                    case 'on';
                    case 'true';
                        $this->setIsAuth( true );
                        break;

                    default:
                        break;
                }
            }

            if ( isset( $aryAttribute[ 'hankaku' ] ) )
            {
                switch ( strtolower( trim( $aryAttribute[ 'hankaku' ] ) ) )
                {
                    case '0';
                    case 'off';
                    case 'false';
                        $this->setIsHankaku( false );
                        break;

                    default:
                        break;
                }
            }
        }
    }

    /**
     * setHtml()
     *
     * @access public
     * @param  string &$sHtml
     * @return void
     */
    function setHtml( &$sHtml )
    {
        $this->_sHtml = $sHtml;
    }

    /**
     * &getHtml()
     *
     * @access public
     * @return string
     */
    function &getHtml()
    {
        $this->setRepStr( $this->_sPatternMob, '' );

        $this->_sHtml = $this->_addParam( $this->_sHtml );

        $this->_sHtml = $this->_repStr( $this->_sHtml );

        if ( $this->_bIsHankaku )
        {
            $this->_sHtml = mb_convert_kana( $this->_sHtml, 'aks' );
        }

        $this->_sHtml = $this->_debug( $this->_sHtml );

        $this->_sHtml = trim( $this->_sHtml );

        return $this->_sHtml;
    }

    /**
     * setPear()
     *
     * @access public
     * @param  array &$aryPear
     * @return void
     */
    function setPear( &$aryPear )
    {
        $this->_aryPear = $aryPear;
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
     * setIsAuth()
     *
     * @access public
     * @param  boolean $bIsAuth
     * @return void
     */
    function setIsAuth( $bIsAuth )
    {
        $this->_bIsAuth = $bIsAuth;
    }

    /**
     * getIsAuth()
     *
     * @access public
     * @return boolean
     */
    function getIsAuth()
    {
        return $this->_bIsAuth;
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
     * parse()
     *
     * @access public
     * @return void
     */
    function parse()
    {
        preg_match_all( $this->_sPattern, $this->_sHtml, $this->_aryMatches );
    }

    /**
     * hasNextBlock()
     *
     * @access public
     * @return boolean
     */
    function hasNextBlock()
    {
        if ( ! isset( $this->_aryMatches[ 0 ][ $this->_iCount + 1 ] ) )
        {
            return false;
        }

        ++$this->_iCount;

        $this->_parseAttribute( $this->_aryMatches[ 0 ][ $this->_iCount ], $this->_aryAttribute );

        return true;
    }

    /**
     * getBlockType()
     *
     * @access public
     * @return string
     */
    function getBlockType()
    {
        $sType = $this->_getAttribute( 'type' );

        return strtolower( trim( $sType ) );
    }

    /**
     * readHtml()
     *
     * @access public
     * @return void
     */
    function readHtml()
    {
        $sFileName = '';

        $sSrc = $this->_getAttribute( 'src' );
        $sSrc = trim( $sSrc );
        if ( '' != $sSrc )
        {
            $sFileName = realpath( $sSrc );
            if ( '' == $sFileName || ! is_file( $sFileName ) )
            {
                $sFileName = realpath( BLOCKEN_ROOT_DIR . $sSrc );
                if ( '' == $sFileName || ! is_file( $sFileName ) )
                {
                    $sFileName = getcwd() . '/' . $sSrc;
                }
            }
        }

        $sBuff = '';
        if ( is_file( $sFileName ) )
        {
            $sBuff = file_get_contents( $sFileName );
        }

        if ( BLOCKEN_DEBUG_MODE && ( BLOCKEN_ALWAYS_DEBUG || BlockenCommon::isMember() ) )
        {
            $sBuff = "<!-- START " . $sFileName . " -->\n" . $sBuff . "\n<!-- END " . $sFileName . " -->";
        }

        $this->_sHtml = str_replace( $this->_aryMatches[ 0 ][ $this->_iCount ], $sBuff, $this->_sHtml );
    }

    /**
     * execScript()
     *
     * @access public
     * @return void
     */
    function execScript()
    {
        $sSrc = $this->_getAttribute( 'name' );

        $sScript = BLOCKEN_SCRIPT_DIR . '/' . trim( $sSrc ) . '.php';

        $aryArgs = $this->_getAttribute();
        unset( $aryArgs[ 'type' ] );
        unset( $aryArgs[ 'src' ] );
        unset( $aryArgs[ 'name' ] );

        if ( ! isset( $aryArgs[ 'cache_expire' ] ) )
        {
            $aryArgs[ 'cache_expire' ] = 0;
        }
        else if ( 0 > intval( $aryArgs[ 'cache_expire' ] ) )
        {
            $aryArgs[ 'cache_expire' ] = BLOCKEN_CACHE_EXPIRE;
        }

        $sBlock = str_replace( '.php', '', basename( $sScript ) );

        $aryArgs[ 'template' ] = $sBlock . '.html';

        $this->_aryArgs[ $sBlock ] = $aryArgs;

        $objTpl = $this->_loadTemplate( $this->_aryParam, $aryArgs );

        $sBlockHtml = '';
        $sCacheHit  = '';
        if ( ! $objTpl->isExpired() )
        {
            $sBlockHtml = $objTpl->getCache();

            $sCacheHit = '(Cache Hit) ';
        }
        else
        {
            if ( is_file( $sScript ) )
            {
                include_once $sScript;
            }

            $sFunc = '_' . $sBlock;
            if ( function_exists( $sFunc ) )
            {
                $sBlockHtml = call_user_func( $sFunc, $objTpl, $this->_aryPear, $this->_aryParam, $aryArgs );
            }
            else
            {
                $sBlockHtml = $objTpl->get();
            }

            if ( BlockenTemplate::isError( $sBlockHtml ) )
            {
                $sBlockHtml = '';
            }
        }

        if ( BLOCKEN_DEBUG_MODE && ( BLOCKEN_ALWAYS_DEBUG || BlockenCommon::isMember() ) )
        {
            $sBlockHtml = "<!-- START " . $objTpl->getTakeTime() . " " . $sCacheHit
                        . BLOCKEN_TEMPLATE_DIR . "/" . $aryArgs[ 'template' ] . " -->\n"
                        . $sBlockHtml
                        . "\n<!-- END " . $objTpl->getTakeTime() . " " . $sCacheHit
                        . BLOCKEN_TEMPLATE_DIR . "/" . $aryArgs[ 'template' ] . " -->";
        }

        $this->_sHtml = str_replace( $this->_aryMatches[ 0 ][ $this->_iCount ], $sBlockHtml, $this->_sHtml );
    }

    /**
     * &_loadTemplate()
     *
     * @access private
     * @param  array  &$aryParam
     * @param  array  &$aryArgs
     * @return object            BlockenTemplate
     */
    function &_loadTemplate( &$aryParam, &$aryArgs )
    {
        $objTpl =& new BlockenTemplate( BLOCKEN_TEMPLATE_DIR, BLOCKEN_SIGMA_CACHE_DIR,
                                        unserialize( BLOCKEN_CACHE_BLOCK ) );

        $objTpl->setHtmlCacheExpire( intval( $aryArgs[ 'cache_expire' ] ) );

        $objTpl->setCacheParam( $aryParam, $aryArgs );

        $objTpl->loadTemplateFile( $aryArgs[ 'template' ] );

        return $objTpl;
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
                $sDebug .= sprintf( "<pre>\n\$aryParam = %s\n\n\$aryArgs = %s\n</pre>\n",
                                    var_export( $this->_aryParam, true ), var_export( $this->_aryArgs, true ) );

                if ( BLOCKEN_TIME_OVER < $fTakeTime )
                {
                    $sDebug .= sprintf( "<script language=\"JavaScript\"><!--\nalert(\"take time %01.03f sec.\");\n// --></script>\n", $fTakeTime );
                }

                $sBuff = preg_replace( '/<\/body>/i', "{$sDebug}\n</body>", $sBuff );
            }
            else
            {
                $sDebug = sprintf( "<!--\ntake time %01.03f sec.\n\n\$aryParam = %s\n\n\$aryArgs = %s\n-->\n",
                                   $fTakeTime, var_export( $this->_aryParam, true ), var_export( $this->_aryArgs, true ) );

                $sBuff .= $sDebug;
            }
        }

        return $sBuff;
    }

    /**
     * _parseAttribute()
     *
     * @access private
     * @param  string &$sBlock
     * @param  array  &$aryAttribute
     * @return void
     */
    function _parseAttribute( &$sBlock, &$aryAttribute )
    {
        if ( function_exists( 'tidy_set_encoding' ) )
        {
            $sXml = tidy_repair_string( $sBlock );
        }
        else if ( function_exists( 'tidy_repair_string' ) )
        {
            $sXml = tidy_repair_string( $sBlock, array( 'input-xml' => true ), 'UTF8' );
        }
        else
        {
            $sXml = $sBlock;
        }

        $resParser = xml_parser_create();
        xml_parse_into_struct( $resParser, $sXml, $aryTree );
        xml_parser_free( $resParser );

        $aryAttribute = array();
        foreach ( $aryTree[ 0 ][ 'attributes' ] as $sName => $sValue )
        {
            $aryAttribute[ strtolower( $sName ) ] = $sValue;
        }
    }

    /**
     * _getAttribute()
     *
     * @access private
     * @param  string $sName
     * @return mixed         string | array
     */
    function _getAttribute( $sName = '' )
    {
        if ( '' == $sName )
        {
            return $this->_aryAttribute;
        }

        if ( ! isset( $this->_aryAttribute[ $sName ] ) )
        {
            return '';
        }

        return $this->_aryAttribute[ $sName ];
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
?>
