<?php
/**
 * func_ext.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: func_ext.php 27 2009-04-25 06:39:17Z sigmax $
 */
if ( basename( $_SERVER[ 'SCRIPT_NAME' ] ) == basename( __FILE__ ) ) { exit; }

/**
 * funcIsMember()
 *
 * @param  string  $sUid
 * @param  array   &$aryPear
 * @return boolean
 */
function funcIsMember( $sUid, &$aryPear )
{
    return true;
}

/**
 * funcRegistMember()
 *
 * @param  string  $sUid
 * @param  array   &$aryPear
 * @return boolean
 */
function funcRegistMember( $sUid, &$aryPear )
{
    return true;
}

/**
 * funcExpireMember()
 *
 * @param  string  $sUid
 * @param  array   &$aryPear
 * @return boolean
 */
function funcExpireMember( $sUid, &$aryPear )
{
    return true;
}
?>
