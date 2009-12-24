<?php
class Pearfarm_Task_Try implements Pearfarm_ITask {
  
  public function run($args) {
    $cmd = PEARFARM_CMD . ' build';
    print "Building package with $cmd...\n";
    exec($cmd);

    require dirname(__FILE__).DIRECTORY_SEPARATOR.'builder.php';

    $specfile = getcwd() . '/pearfarm.spec';
    require $specfile;
    print "Installing {$spec->getName()} {$spec->getReleaseVersion()}...\n";

    $isUpgrade = (isset($args[2]) && $args[2] == '-u');
    $cmd = $isUpgrade ? 'upgrade' : 'install';

    $result = array();
    $output = '';
    exec("pear $cmd {$spec->getName()}-{$spec->getReleaseVersion()}.tgz", $result, $output);

    if (strstr($result[0], 'install ok') !== false)
        print "The package was installed successfully.\n";
    elseif (strstr($result[0], 'upgrade ok') !== false)
        print "The package was upgraded successfully.\n";
    else {
        $help = $isUpgrade ? '' : ' Try running with -u option to upgrade.';
        print "\n\nThere were errors installing the package.$help\n  PEAR output:\n    " . join("\n    ", $result) . "\n";
    }
  }
  
  public function showHelp() {

  }
  
  public function getName() {
    return "try";
  }
  
  public function getAliases() {
    return array();
  }
  
  public function getDescription() {
    return "installs the package for testing purposes (-u to upgrade the package)";
  }
}