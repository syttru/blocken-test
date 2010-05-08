<?php
/**
 * BlockenCookie.class.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: BlockenCookie.class.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

class BlockenCookie
{
    /**
     * @access private
     * @var    boolean $_bStart
     */
    var $_bStart  = false;

    /**
     * @access private
     * @var    integer $_iExpire
     */
    var $_iExpire = 0;

    /**
     * @access private
     * @var    string $_sPath
     */
    var $_sPath   = '/';

    /**
     * @access private
     * @var    string $_sDomain
     */
    var $_sDomain = '';

    /**
     * @access private
     * @var    boolean $_bSecure
     */
    var $_bSecure = false;

    /**
     * BlockenCookie()
     *
     * @param  integer $iExpire
     * @param  string  $sPath
     * @param  string  $sDomain
     * @param  boolean $bSecure
     */
    function BlockenCookie( $iExpire = 0, $sPath = '/', $sDomain = '', $bSecure = false )
    {
        if ( 0 < $iExpire )
        {
            $this->_iExpire = time() + $iExpire;
        }
        $this->_sPath   = $sPath;
        $this->_sDomain = $sDomain;
        $this->_bSecure = $bSecure;
    }

    /**
     * start()
     *
     * @access public
     * @return void
     */
    function start()
    {
        $this->_bStart = true;
    }

    /**
     * setExpire()
     *
     * @access public
     * @param  integer $iExpire
     * @return void
     */
    function setExpire( $iExpire = 0 )
    {
        if ( 0 < $iExpire )
        {
            $this->_iExpire = time() + $iExpire;
        }
    }

    /**
     * setPath()
     *
     * @access public
     * @param  string $sPath
     * @return void
     */
    function setPath( $sPath = '/' )
    {
        $this->_sPath = $sPath;
    }

    /**
     * setDomain()
     *
     * @access public
     * @param  string $sDomain
     * @return void
     */
    function setDomain( $sDomain = '' )
    {
        $this->_sDomain = $sDomain;
    }

    /**
     * setSecure()
     *
     * @access public
     * @param  boolean $bSecure
     * @return void
     */
    function setSecure( $bSecure = false )
    {
        $this->_bSecure = $bSecure;
    }

    /**
     * set()
     *
     * @access public
     * @param  string  $sName
     * @param  string  $sValue
     * @return boolean
     */
    function set( $sName, $sValue )
    {
        if ( ! $this->_bStart )
        {
            return false;
        }

        $bRet = setcookie( $sName, $sValue, $this->_iExpire, $this->_sPath, $this->_sDomain, $this->_bSecure );

        return $bRet;
    }

    /**
     * delete()
     *
     * @access public
     * @param  string $sName
     * @return boolean
     */
    function delete( $sName )
    {
        if ( ! $this->_bStart )
        {
            return false;
        }

        $bRet = setcookie( $sName, '', time() - 42000, $this->_sPath, $this->_sDomain, $this->_bSecure );

        return $bRet;
    }

    /**
     * get()
     *
     * @access public
     * @param  string $sName
     * @param  string $sDefault
     * @return mixed           string | null
     */
    function get( $sName, $sDefault = null )
    {
        if ( ! $this->_bStart )
        {
            return null;
        }

        if ( ! isset( $_COOKIE[ $sName ] ) )
        {
            if ( '' != $sDefault )
            {
                $this->set( $sName, $sDefault );
                return $sDefault;
            }
            return null;
        }

        return $_COOKIE[ $sName ];
    }
}
?>
