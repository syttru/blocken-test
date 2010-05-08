<?php
/**
 * BlockenCurl.class.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: BlockenCurl.class.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

require_once 'Net/Curl.php';

class BlockenCurl extends Net_Curl
{
    /**
     * @access private
     * @var    array $_aryOptions
     */
    var $_aryOptions = array();

    /**
     * &execute()
     *
     * @access public
     * @param  string  $sUrl
     * @param  array   $aryPostFields
     * @param  string  $sEncode
     * @return mixed                  string | PEAR_Error
     */
    function &execute( $sUrl = '', $aryPostFields = array(), $sEncode = '' )
    {
        if ( ! is_resource( $this->_ch ) )
        {
            $objRet = parent::create();
            if ( PEAR::isError( $objRet ) )
            {
                return $objRet;
            }

            $this->verifyPeer = -1;
            $this->verifyHost = -1;
        }

        if ( '' != $sUrl )
        {
            $this->setUrl( $sUrl );
        }

        if ( ! empty( $aryPostFields ) )
        {
            $this->setPostFields( $aryPostFields );
        }

        foreach ( $this->_aryOptions as $iOption => $mValue )
        {
            if ( ! curl_setopt( $this->_ch, $iOption, $mValue ) )
            {
                return PEAR::raiseError( 'Error setting Options' );
            }
        }

        $sRet = parent::execute();

        parent::close();

        if ( '' != $sUrl )
        {
            $this->clearUrl();
        }

        if ( ! empty( $aryPostFields ) )
        {
            $this->clearPostFields();
        }

        if ( PEAR::isError( $sRet ) )
        {
            return $sRet;
        }

        if ( '' != $sEncode )
        {
            $sRet = mb_convert_encoding( $sRet, 'UTF-8', $sEncode );
        }

        return $sRet;
    }

    /**
     * &executeRetry()
     *
     * @access public
     * @param  string  $sUrl
     * @param  array   $aryPostFields
     * @param  string  $sEncode
     * @param  integer $iRetry
     * @param  integer $iSleep
     * @return mixed                  string | PEAR_Error
     */
    function &executeRetry( $sUrl = '', $aryPostFields = array(), $sEncode = '', $iRetry = 10, $iSleep = 30 )
    {
        for ( $i = 0; $i <= $iRetry; $i++ )
        {
            $sRet = $this->execute( $sUrl, $aryPostFields, $bEncFlg );
            if ( ! PEAR::isError( $sRet ) )
            {
                return $sRet;
            }

            sleep( $iSleep );
        }

        return PEAR::raiseError( 'Error retry timeout' );
    }

    /**
     * setUrl()
     *
     * @access public
     * @param  string  $sUrl
     * @return void
     */
    function setUrl( $sUrl )
    {
        $this->url = $sUrl;
        $this->setOption( CURLOPT_REFERER, $sUrl );
    }

    /**
     * clearUrl()
     *
     * @access public
     * @return void
     */
    function clearUrl()
    {
        $this->url = '';
        $this->clearOption( CURLOPT_REFERER );
    }

    /**
     * setPostFields()
     *
     * @access public
     * @param  array $aryPostFields
     * @return void
     */
    function setPostFields( $aryPostFields )
    {
        $this->setOption( CURLOPT_POST, true );
        $this->setOption( CURLOPT_POSTFIELDS, $aryPostFields );
    }

    /**
     * clearPostFields()
     *
     * @access public
     * @return void
     */
    function clearPostFields()
    {
        $this->clearOption( CURLOPT_POST );
        $this->clearOption( CURLOPT_POSTFIELDS );
    }

    /**
     * setOption()
     *
     * @access public
     * @param  integer $iOption
     * @param  mixed   $mValue
     * @return void
     */
    function setOption( $iOption, $mValue )
    {
        $this->_aryOptions[ $iOption ] = $mValue;
    }

    /**
     * clearOption()
     *
     * @access public
     * @param  integer $iOption
     * @return void
     */
    function clearOption( $iOption )
    {
        unset( $this->_aryOptions[ $iOption ] );
    }

    /**
     * setOptions()
     *
     * @access public
     * @param  array $aryOptions
     * @return void
     */
    function setOptions( $aryOptions )
    {
        $this->_aryOptions = $this->_aryOptions + $aryOptions;
    }

    /**
     * clearOptions()
     *
     * @access public
     * @return void
     */
    function clearOptions()
    {
        $this->_aryOptions = array();
    }
}
?>
