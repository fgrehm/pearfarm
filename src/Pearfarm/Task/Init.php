<?php
class Pearfarm_Task_Init extends Pearfarm_Task_Plant {
  
  public function run($args) {
    if(!isset($args[2]))
      throw new Pearfarm_TaskArgumentException("You must specify a package name.\n");
    $specfile = getcwd() . '/pearfarm.spec';
    file_put_contents($specfile, $this->basicSpecFile($args[2]));
    echo "  created $specfile\n";
  }
  
  public function showHelp() {
    
  }
  
  public function getAliases() {
    return array();
  }
  
  public function getName() {
    return 'init';
  }
  
  public function getDescription() {
    return "creates just a pearfarm.spec file";
  }
}
