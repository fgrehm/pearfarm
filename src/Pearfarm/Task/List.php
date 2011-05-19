<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */

class Pearfarm_Task_List extends Pearfarm_AbstractTask {
  public function run($args) {
    if (count($args) !== 3)
    {
      print "Usage: pearfarm list <pearfarm-username>\n";
      exit(1);
    }

    $channel = escapeshellarg("{$args[2]}.pearfarm.org");
    $command = "pear remote-list -c {$channel}";
    passthru($command);
    exit(0);
  }

  public function showHelp() {

  }

  public function getName() {
    return "list";
  }

  public function getAliases() {
    return array();
  }

  public function getDescription() {
    return "List the packages the specified user has on pearfarm.";
  }
}
