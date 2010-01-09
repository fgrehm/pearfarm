<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */

/**
	* Requires vfsstream for filesystem mocking
	* http://code.google.com/p/bovigo/wiki/vfsStreamDocsInstall
	*/
require_once('vfsStream/vfsStream.php');

function makeTree($fileArray, $root = 'root')
{
    vfsStreamWrapper::setRoot(new vfsStreamDirectory($root));
    foreach ($fileArray as $file) {
      $vfsFile = vfsStream::url($file);
      $vfsFileDir = vfsStream::url(dirname($file));
      // containing dir
      if (!file_exists($vfsFileDir))
      {
        $ok = mkdir($vfsFileDir, 0755, true);
        if (!$ok) die("mkdir $vfsFileDir failed");
      }
      // item
      if (substr($file, -1) === '/')
      {
        // dir
        if (!file_exists($vfsFile))
        {
          $ok = mkdir($vfsFile);
          if (!$ok) die("mkdir $file failed");
        }
      }
      else
      {
        // file
        $vfsFileUrl = vfsStream::url($file);
        // touch doesn't work with vfs yet
        $f = fopen($vfsFileUrl, 'r');
        fclose($f);
      }
    }
}

function showTree($path)
{
    // dump path just made
    print "ls $path:\n";
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(vfsStream::url($path)), RecursiveIteratorIterator::SELF_FIRST) as $item) {
      print $item->getPathname();
      if ($item->isDir())
      {
        print "/";
      }
      print "\n";
    }
    print "DONE\n";
}

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../src/Pearfarm/ITask.php');
require_once(dirname(__FILE__) . '/../src/Pearfarm/PackageSpec.php');

class PackageSpecTest extends PHPUnit_Framework_TestCase {

  public function setUp() {
    $this->spec = new Pearfarm_PackageSpec(array(Pearfarm_PackageSpec::OPT_DEBUG => false));
    vfsStreamWrapper::register();
    makeTree(array(
            'root/file.php',
            'root/a/a.php',
            'root/a/b/b.php',
            'root/a/b/c/c.php',
            'root/b/',
            'root/c/d/',
    ));
    //showTree('root');
  }

  public function testCreatesPearSpec() {
    $this->markTestIncomplete();
  }
  public function testAddExcludeFilesRegex() {
    $this->markTestIncomplete();
  }
  public function testAddFilesRegex() {
    $this->markTestIncomplete();
  }
  /**
   * @dataProvider releaseVersionTestData
   */
  public function testSetReleaseVersionRegex($re, $tag, $expectedVersion, $expectedStability) {
    $this->spec->setReleaseVersionRegex($tag, $re);
    $this->assertEquals($expectedVersion, $this->spec->getReleaseVersion());
    $this->assertEquals($expectedStability, $this->spec->getReleaseStability());
  }
  public function releaseVersionTestData() {
    return array(
      array(Pearfarm_PackageSpec::SEMANTIC_VERSIONING_REGEX, 'v0.0.1', '0.0.1', 'beta'),        // assume beta if v<1.0.0
      array(Pearfarm_PackageSpec::SEMANTIC_VERSIONING_REGEX, 'v1.0.0', '1.0.0', 'stable'),      // assume stable if v>=1.0.0
      array(Pearfarm_PackageSpec::SEMANTIC_VERSIONING_REGEX, 'v0.0.1devel', '0.0.1', 'devel'),  // test stability parsing
      array(Pearfarm_PackageSpec::SEMANTIC_VERSIONING_REGEX, 'v0.0.1alpha', '0.0.1', 'alpha'),
      array(Pearfarm_PackageSpec::SEMANTIC_VERSIONING_REGEX, 'v0.0.1beta', '0.0.1', 'beta'),
      array(Pearfarm_PackageSpec::SEMANTIC_VERSIONING_REGEX, 'v1.0.1', '1.0.1', 'stable'),
      array(Pearfarm_PackageSpec::SEMANTIC_VERSIONING_REGEX, 'v1.0.0beta', '1.0.0', 'beta'),
    );
  }
  // etc
}
