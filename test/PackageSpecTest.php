<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../src/Pearfarm/ITask.php');
require_once(dirname(__FILE__) . '/../src/Pearfarm/PackageSpec.php');
/**
	* Requires vfsstream for filesystem mocking
	* http://code.google.com/p/bovigo/wiki/vfsStreamDocsInstall
	*/
require_once('vfsStream/vfsStream.php');

class PackageSpecTest extends PHPUnit_Framework_TestCase {

  public function setUp() {
    $this->spec = new Pearfarm_PackageSpec();
    vfsStreamWrapper::register();
    $root = new vfsStreamDirectory('root');
    vfsStreamWrapper::setRoot($root);
  }

  public function testCreatesPearSpec() {
    $this->markTestIncomplete('need to figureo ut vfsStreamWrapper');
  }
}
