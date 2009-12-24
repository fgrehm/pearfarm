<?php
require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../src/Pearfarm/ITask.php');
require_once(dirname(__FILE__) . '/../src/Pearfarm/Task/Collect.php');
/**
	* Requires vfsstream for filesystem mocking
	* http://code.google.com/p/bovigo/wiki/vfsStreamDocsInstall
	*/
require_once('vfsStream/vfsStream.php');
class CollectTest extends PHPUnit_Framework_TestCase {
	
	public function setUp() {
			$this->class = new Pearfarm_Task_Collect();
			vfsStreamWrapper::register();
			$root = new vfsStreamDirectory('root');
			vfsStreamWrapper::setRoot($root);
	}
	
	
	public function testCreatesPearSpec() {
		$this->assertTrue(true);
		/**
			*
			* Will finish test once i figure out how =x
 			*
			*/
	}
	
}