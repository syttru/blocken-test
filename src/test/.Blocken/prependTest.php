<?php
require_once 'PHPUnit/Framework/TestCase.php';

class prependTest extends PHPUnit_Framework_TestCase {

  public function setup() {
    $this->defineConstants();
  }
  
  public function testRequire() {
    copy('../../blocken/.Blocken/prepend.php', 'prepend.php');
    require 'prepend.php';
  }

  function defineConstants() {
    define('BLOCKEN_BASE', '.');
	define('BLOCKEN_BIN_DIR', '.');
	define('BLOCKEN_LIB_DIR', dirname(__FILE__) . '/lib');
	
	define('BLOCKEN_ERROR_LOG', '');
	define('BLOCKEN_DISPLAY_ERRORS', TRUE);
	define('BLOCKEN_ERROR_REPORTING', E_ALL);
	
	define('BLOCKEN_COOKIE_USE', FALSE);
    define('BLOCKEN_LOG_HANDLER', "");
    define('BLOCKEN_LOG_NAME', "");
    define('BLOCKEN_LOG_IDENT', "");
    define('BLOCKEN_LOG_CONF', "");
    define('BLOCKEN_LOG_LEVEL', "");

    define('BLOCKEN_MOBI_USERAGENT', "");
    
    define('BLOCKEN_MOBILE_USE', FALSE);
    define('BLOCKEN_SESSION_USE', FALSE);
    define('BLOCKEN_AUTH_USE', FALSE);
    define('BLOCKEN_CMD_USE', FALSE);

    define('BLOCKEN_CURL_OPTION', "");

    define('BLOCKEN_DOC_PATH', '/doc');

    define('BLOCKEN_WEB', 'web');
    define('BLOCKEN_CONSOLE', 'console');
    define('BLOCKEN_MODE', BLOCKEN_WEB);
  }

}

class BlockenCookie {
}

class BlockenDB {
}

class BlockenLog {
  public static function singleton() {
  }
}

class BlockenSession {
}

class BlockenMobile {
  public static function singleton() {
  }
}

class BlockenAuth {
}

class BlockenCurl {
  public static function setOptions() {
  }
}

function loadTemplate($bIsPHP) {
}

