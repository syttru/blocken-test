<?php
/**
 * BlockenAuth.class.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: BlockenAuth.class.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

require_once 'Auth.php';
require_once 'Auth/HTTP.php';

class BlockenAuth
{
    /**
     * &factory()
     *
     * @access public
     * @param  string $sMode
     * @param  string $sDriver
     * @param  mixed  $mOption string | array
     * @return mixed           Auth | Auth_HTTP
     */
    function &factory( $sMode, $sDriver, $mOption = '' )
    {
        $objAuth = null;

        switch ( $sMode )
        {
            case 'form':
                $objAuth =& new Auth( $sDriver, $mOption );
                break;

            case 'http':
            default:
                $objAuth =& new Auth_HTTP( $sDriver, $mOption );
                break;
        }

        return $objAuth;
    }

    /**
     * start()
     *
     * @access public
     * @return void
     */
    function start()
    {
    }

    /**
     * getAuth()
     *
     * @access public
     * @return boolean
     */
    function getAuth()
    {
        return true;
    }

    /**
     * getStatus()
     *
     * @access public
     * @return string
     */
    function getStatus()
    {
        return '';
    }
}
?>
