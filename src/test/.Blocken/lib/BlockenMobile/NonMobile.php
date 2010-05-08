<?php
/**
 * NonMobile.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: NonMobile.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

require_once 'Net/UserAgent/Mobile/NonMobile.php';

class BlockenMobile_NonMobile extends Net_UserAgent_Mobile_NonMobile
{
    /**
     * isFlash()
     *
     * @access public
     * @param  float  $fVersion
     * @return float
     */
    function isFlash( $fVersion = 3.1 )
    {
        return $fVersion;
    }

    /**
     * isPdf()
     *
     * @access public
     * @return boolean
     */
    function isPdf()
    {
        return true;
    }

    /**
     * isSimulator()
     *
     * @access public
     * @return boolean
     */
    function isSimulator()
    {
        return false;
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
        return false;
    }

    /**
     * &convertEmoji()
     *
     * @access public
     * @return array
     */
    function &convertEmoji()
    {
        $aryEmoji = array(
                    '/&#xE70C;/i'    => '&#xF9B1;',
                    '/&#xE70D;/i'    => '&#xF9B2;',
                    '/&#xE70E;/i'    => '&#xF9B3;',
                    '/&#xE70F;/i'    => '&#xF9B4;',
                    '/&#xE710;/i'    => '&#xF9B5;',
                    '/&#xE711;/i'    => '&#xF9B6;',
                    '/&#xE712;/i'    => '&#xF9B7;',
                    '/&#xE713;/i'    => '&#xF9B8;',
                    '/&#xE714;/i'    => '&#xF9B9;',
                    '/&#xE715;/i'    => '&#xF9BA;',
                    '/&#xE716;/i'    => '&#xF9BB;',
                    '/&#xE717;/i'    => '&#xF9BC;',
                    '/&#xE718;/i'    => '&#xF9BD;',
                    '/&#xE719;/i'    => '&#xF9BE;',
                    '/&#xE71A;/i'    => '&#xF9BF;',
                    '/&#xE71B;/i'    => '&#xF9C0;',
                    '/&#xE71C;/i'    => '&#xF9C1;',
                    '/&#xE71D;/i'    => '&#xF9C2;',
                    '/&#xE71E;/i'    => '&#xF9C3;',
                    '/&#xE71F;/i'    => '&#xF9C4;',
                    '/&#xE720;/i'    => '&#xF9C5;',
                    '/&#xE721;/i'    => '&#xF9C6;',
                    '/&#xE722;/i'    => '&#xF9C7;',
                    '/&#xE723;/i'    => '&#xF9C8;',
                    '/&#xE724;/i'    => '&#xF9C9;',
                    '/&#xE725;/i'    => '&#xF9CA;',
                    '/&#xE726;/i'    => '&#xF9CB;',
                    '/&#xE727;/i'    => '&#xF9CC;',
                    '/&#xE728;/i'    => '&#xF9CD;',
                    '/&#xE729;/i'    => '&#xF9CE;',
                    '/&#xE72A;/i'    => '&#xF9CF;',
                    '/&#xE72B;/i'    => '&#xF9D0;',
                    '/&#xE72C;/i'    => '&#xF9D1;',
                    '/&#xE72D;/i'    => '&#xF9D2;',
                    '/&#xE72E;/i'    => '&#xF9D3;',
                    '/&#xE72F;/i'    => '&#xF9D4;',
                    '/&#xE730;/i'    => '&#xF9D5;',
                    '/&#xE731;/i'    => '&#xF9D6;',
                    '/&#xE732;/i'    => '&#xF9D7;',
                    '/&#xE733;/i'    => '&#xF9D8;',
                    '/&#xE734;/i'    => '&#xF9D9;',
                    '/&#xE735;/i'    => '&#xF9DA;',
                    '/&#xE736;/i'    => '&#xF9DB;',
                    '/&#xE737;/i'    => '&#xF9DC;',
                    '/&#xE738;/i'    => '&#xF9DD;',
                    '/&#xE739;/i'    => '&#xF9DE;',
                    '/&#xE73A;/i'    => '&#xF9DF;',
                    '/&#xE73B;/i'    => '&#xF9E0;',
                    '/&#xE73C;/i'    => '&#xF9E1;',
                    '/&#xE73D;/i'    => '&#xF9E2;',
                    '/&#xE73E;/i'    => '&#xF9E3;',
                    '/&#xE73F;/i'    => '&#xF9E4;',
                    '/&#xE740;/i'    => '&#xF9E5;',
                    '/&#xE741;/i'    => '&#xF9E6;',
                    '/&#xE742;/i'    => '&#xF9E7;',
                    '/&#xE743;/i'    => '&#xF9E8;',
                    '/&#xE744;/i'    => '&#xF9E9;',
                    '/&#xE745;/i'    => '&#xF9EA;',
                    '/&#xE746;/i'    => '&#xF9EB;',
                    '/&#xE747;/i'    => '&#xF9EC;',
                    '/&#xE748;/i'    => '&#xF9ED;',
                    '/&#xE749;/i'    => '&#xF9EE;',
                    '/&#xE74A;/i'    => '&#xF9EF;',
                    '/&#xE74B;/i'    => '&#xF9F0;',
                    '/&#xE74C;/i'    => '&#xF9F1;',
                    '/&#xE74D;/i'    => '&#xF9F2;',
                    '/&#xE74E;/i'    => '&#xF9F3;',
                    '/&#xE74F;/i'    => '&#xF9F4;',
                    '/&#xE750;/i'    => '&#xF9F5;',
                    '/&#xE751;/i'    => '&#xF9F6;',
                    '/&#xE752;/i'    => '&#xF9F7;',
                    '/&#xE753;/i'    => '&#xF9F8;',
                    '/&#xE754;/i'    => '&#xF9F9;',
                    '/&#xE755;/i'    => '&#xF9FA;',
                    '/&#xE756;/i'    => '&#xF9FB;',
                    '/&#xE757;/i'    => '&#xF9FC;',
                    '/&#(\d{5});/e'  => "sprintf( '<img src=\"%s/%4x.gif\" width=\"12\" height=\"12\" border=\"0\" />', BLOCKEN_EMOJI_PATH, '\\1' )",
                    '/&#x(\w{4});/e' => "sprintf( '<img src=\"%s/%s.gif\" width=\"12\" height=\"12\" border=\"0\" />', BLOCKEN_EMOJI_PATH, strtolower( '\\1' ) )"
                );

        return $aryEmoji;
    }
}
?>
