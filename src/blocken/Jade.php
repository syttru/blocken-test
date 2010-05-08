#!/usr/bin/php
<?php
/**
 * Jade.php
 *
 * PHP versions 4 and 5
 *
 * @package   Blocken
 * @author    Kouhei Suzuki <sigma@mfer.jp>
 * @copyright 2006-2009 SIGMA Project
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   $Id: Jade.php 27 2009-04-25 06:39:17Z sigmax $
 */
define( 'BLOCKEN_PATH', '{BlockenPath}' );

if ( ! isset( $_SERVER[ 'SCRIPT_NAME' ] ) ) { $_SERVER[ 'SCRIPT_NAME' ] = false; }
if ( ! isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) { $_SERVER[ 'HTTP_USER_AGENT' ] = false; }
if ( ! defined( 'STDOUT' ) ) { define( 'STDOUT', fopen( 'php://stdout', 'w' ) ); }

include_once BLOCKEN_PATH . '/.Blocken/prepend.php';

if ( BLOCKEN_WEB == BLOCKEN_MODE ) { exit; }

switch ( BLOCKEN_OS )
{
    case BLOCKEN_WINDOWS:
        ob_start( 'mb_output_handler' );
        mb_http_output( 'SJIS' );
        break;

    case BLOCKEN_UNIX:
    default:
        break;
}

include_once 'BlockenCommand.php';
cmdConsole( $aryPear );
exit;
?>
