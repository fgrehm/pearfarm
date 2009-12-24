<?php
interface Pearfarm_ITask {
  public function run($args);
  public function showHelp();
  public function getAliases();
  public function getName();
  public function getDescription();
}

class Pearfarm_TaskArgumentException extends Exception {}