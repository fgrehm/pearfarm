<?php

/* vim: set expandtab tabstop=2 shiftwidth=2: */

require_once(dirname(__FILE__).'/../PackageSpec.php');

class Pearfarm_Task_Collect implements Pearfarm_ITask {
  public function run($args) {

    if (isset($args[2])) {
      $specfile = $args[2];
    } else {
      $specfile = getcwd() . '/pearfarm.spec';
    }

    print "Reading specfile at {$specfile}...\n";
    if (!file_exists($specfile)) {
      print "{$specfile} is not a pearfarm.spec file.\n";
      exit(1);
    }

    include $specfile;
    if (!isset($spec)) {
      print "specfile didn't create a local variable named '\$spec'.\n";
      exit(1);
    }

    $spec->writePackageFile();

    print "The package.xml file was written successfully, executing 'pear package'...\n";
    exec('pear package');

    print "The package was generated successfully.\n";
  }
  public function showHelp() {

  }
  public function getName() {
    return "collect";
  }
  public function getAliases() {
    return array('build');
  }
  public function getDescription() {
    return "builds the package";
  }
}
