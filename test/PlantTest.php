<?php
require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../src/Pearfarm/ITask.php');
require_once(dirname(__FILE__) . '/../src/Pearfarm/Task/Plant.php');
/**
	* Requires vfsstream for filesystem mocking
	* http://code.google.com/p/bovigo/wiki/vfsStreamDocsInstall
	*/
require_once('vfsStream/vfsStream.php');
class PlantTest extends PHPUnit_Framework_TestCase {
	
	public function setUp() {
		$this->folders = array('src', 'data', 'tests', 'doc', 'www', 'examples') ;
		$this->class = new Pearfarm_Task_Plant();
		vfsStreamWrapper::register();
		$root = new vfsStreamDirectory('root');
		foreach($this->folders as $folder) {
			$root->addChild(new vfsStreamDirectory($folder));
		}
		vfsStreamWrapper::setRoot($root);
	}
	
	public function testCreatesFiles() {
		ob_start();
		$args = array('', '', vfsStream::url('root/'));
		$this->class->run($args);
		foreach($this->folders as $folder) {
			$this->assertTrue(is_dir(vfsStream::url('root/' . $folder .'/')));
		}
		$this->assertTrue(file_exists(vfsStream::url('root/pearfarm.spec')));
		$this->assertTrue(file_exists(vfsStream::url('root/src/Root.php')));
		ob_get_clean();		
	}
	
}

