<?php
require_once 'PHPUnit/Framework/TestCase.php';

class prependTest extends PHPUnit_Framework_TestCase {
  public function testRequire() {
    copy('../../blocken/.Blocken/prepend.php', 'prepend.php');
    require 'prepend.php';
    $this->fail('未実装なのです。');
  }
}
