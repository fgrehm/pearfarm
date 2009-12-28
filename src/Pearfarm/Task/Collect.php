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

    $command = 'pear package';
    $result = NULL;
    $output = array();
    $lastLine = exec($command, $output, $result);
    if ($result !== 0) {
      if (strpos(join("\n", $output), 'Unknown channel') !== false) {
        $channel = $spec->getChannel();
        $discoverCommand = "pear channel-discover {$channel}";
        print "You need to run the following command (probably as root/sudo) to add this package's channel to pear, which is required for pear to package your app.\n\n{$discoverCommand}\n\n";
        exit(1);
      } else {
        throw( new Exception("Error ($result) running '{$command}': " . join("\n", $output)) );
      }
    }

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
