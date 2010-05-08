<?php
/**
 * DoCoMo.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: DoCoMo.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

require_once 'Net/UserAgent/Mobile/DoCoMo.php';

class BlockenMobile_DoCoMo extends Net_UserAgent_Mobile_DoCoMo
{
    /**
     * @access private
     * @var    array $_aryDisplayMap
     */
    var $_aryDisplayMap = null;

    /**
     * isFlash()
     *
     * @access public
     * @param  float  $fVersion
     * @return mixed            float | boolean
     */
    function isFlash( $fVersion = 0 )
    {
        $aryDisplay = $this->_getDisplay();

        $fFlash = floatval( $aryDisplay[ 'flash' ] );
        if ( 0 != $fFlash && $fVersion <= $fFlash )
        {
            return $fFlash;
        }

        return false;
    }

    /**
     * isPdf()
     *
     * @access public
     * @return boolean
     */
    function isPdf()
    {
        $aryDisplay = $this->_getDisplay();

        return ( '1' == $aryDisplay[ 'pdf' ] ) ? true : false;
    }

    /**
     * isSimulator()
     *
     * @access public
     * @return boolean
     */
    function isSimulator()
    {
        $bRet = preg_match( '/ISIM/', $this->getModel() );

        return ( 1 == $bRet ) ? true : false;
    }

    /**
     * hasEmoji()
     *
     * @access public
     * @param  string  $sMsg
     * @return boolean
     */
    function hasEmoji( $sMsg )
    {
        $sMsg = mb_convert_encoding( $sMsg, 'UTF-8', 'SJIS' );
        $bRet = preg_match( '/〓/', $sMsg );

        return ( 1 == $bRet ) ? true : false;
    }

    /**
     * &convertEmoji()
     *
     * @access public
     * @return array
     */
    function &convertEmoji()
    {
        $aryEmoji = array();

        return $aryEmoji;
    }

    /**
     * makeDisplay()
     *
     * @access public
     * @return object Net_UserAgent_Mobile_Display
     */
    function makeDisplay()
    {
        $aryDisplay = $this->_getDisplay();

        if ( ! is_null( $this->_displayBytes ) )
        {
            list( $aryDisplay[ 'width_bytes' ], $aryDisplay[ 'height_bytes' ] ) = explode( '*', $this->_displayBytes );
        }

        return new Net_UserAgent_Mobile_Display( $aryDisplay );
    }

    /**
     * _getDisplay()
     *
     * @access private
     * @return array
     */
    function _getDisplay()
    {
        if ( ! isset( $this->_aryDisplayMap ) )
        {
            $sXml = file_get_contents( BLOCKEN_MOBI_D_MAP );

            $aryValues  = array();
            $aryIndexes = array();

            $resParser = xml_parser_create();
            xml_parse_into_struct( $resParser, $sXml, $aryValues, $aryIndexes );
            xml_parser_free( $resParser );

            foreach ( $aryIndexes[ 'TERMINAL' ] as $iModelIndexes )
            {
                $sModelName = $aryValues[ $iModelIndexes ][ 'attributes' ][ 'MODEL' ];
                $this->_aryDisplayMap[ $sModelName ] = array();
                foreach ( $aryValues[ $iModelIndexes ][ 'attributes' ] as $sAttributeName => $sAttributeValue )
                {
                    $this->_aryDisplayMap[ $sModelName ][ strtolower( $sAttributeName ) ] = $sAttributeValue;
                }
            }
        }

        $aryDisplay = array();

        if ( array_key_exists( $this->getModel(), $this->_aryDisplayMap ) )
        {
            $aryDisplay = $this->_aryDisplayMap[ $this->getModel() ];
        }


        return $aryDisplay;
    }
}
?>
