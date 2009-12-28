<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */
class Pearfarm_Task_Init extends Pearfarm_Task_Plant {

  public function run($args) {
    if (isset($args[2])) {
      $specfile = getcwd() . '/' . $args[2];
    } else {
      $specfile = getcwd() . '/pearfarm.spec';
    }
    if (file_exists($specfile)) {
      throw new Exception("Spec file already exists at {$specfile}.");
    }
    file_put_contents($specfile, $this->basicSpecFile());
    echo "  created $specfile\n";
  }

  public function showHelp() {
    return "Usage:\npearfarm init [specfile:pearfarm.spec]\n";
  }

  public function getAliases() {
    return array();
  }

  public function getName() {
    return 'init';
  }

  public function getDescription() {
    return "Creates a pearfarm.spec file for your project.";
  }
}
