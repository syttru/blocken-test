<?php
/**
 * BlockenSession.class.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: BlockenSession.class.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

require_once 'HTTP/Session.php';

class BlockenSession extends HTTP_Session
{
    /**
     * start()
     *
     * @access public
     * @param  string $sName
     * @param  string $sId
     * @return void
     */
    function start( $sName = '', $sId = null )
    {
        if ( '' == $sName )
        {
            $sName = session_name();
        }

        parent::start( $sName, $sId );
    }

    /**
     *  destroy()
     *
     * @access public
     * @return void
     */
    function destroy()
    {
        $_SESSION = array();

        if ( isset( $_COOKIE[ session_name() ] ) )
        {
            $aryCookieParams = session_get_cookie_params();
            setcookie( session_name(), '', time() - 42000,
                       $aryCookieParams[ 'path' ], $aryCookieParams[ 'domain' ], $aryCookieParams[ 'secure' ] );
        }

        parent::destroy();
    }

    /**
     * setExpire()
     *
     * @access public
     * @param  integer $iTime
     * @return void
     */
    function setExpire( $iTime )
    {
        parent::setExpire( time() + $iTime, false );
    }

    /**
     * setIdle()
     *
     * @access public
     * @param  integer $iTime
     * @return void
     */
    function setIdle( $iTime )
    {
        parent::setIdle( $iTime, true );
    }

    /**
     * setGcMaxLifetime()
     *
     * @access public
     * @param  integer $iGcMaxLifetime
     * @return void
     */
    function setGcMaxLifetime( $iGcMaxLifetime )
    {
        $iCurrentGcMaxLifetime = parent::setGcMaxLifetime();
        parent::setGcMaxLifetime( $iCurrentGcMaxLifetime + $iGcMaxLifetime );
    }

    /**
     * setCookieLifetime()
     *
     * @access public
     * @param  integer $iTime
     * @return void
     */
    function setCookieLifetime( $iTime )
    {
        session_set_cookie_params( $iTime );
    }
}
?>
