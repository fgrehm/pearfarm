<?php
class Pearfarm_Task_Deliver implements Pearfarm_ITask {
  
  public function run($args) {
 
  }
  
  public function showHelp() {
 
  }
  
  public function getName() {
    return "deliver";
  }
  
  public function getAliases() {
    return array();
  }
  
  public function getDescription() {
    return "sends the package to pearfarm.org";
  }
}