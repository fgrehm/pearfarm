<?php
require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../src/Pearfarm/ITask.php');
require_once(dirname(__FILE__) . '/../src/Pearfarm/Task/Init.php');
/**
 * Requires vfsstream for filesystem mocking
 * http://code.google.com/p/bovigo/wiki/vfsStreamDocsInstall
 */
require_once('vfsStream/vfsStream.php');
class InitTest extends PHPUnit_Framework_TestCase {

  public function setUp() {
    $this->class = new Pearfarm_Task_Init();
    vfsStreamWrapper::register();
    $root = new vfsStreamDirectory('root');
    vfsStreamWrapper::setRoot($root);
  }

  public function testCreatesFile() {
    ob_start();
    $args = array('', '', vfsStream::url('root/pearfarm.spec'));
    $this->class->run($args);
    $this->assertTrue(file_exists(vfsStream::url('root/pearfarm.spec')));
    ob_get_clean();
  }
}
