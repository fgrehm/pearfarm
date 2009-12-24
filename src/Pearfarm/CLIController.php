<?php
class Pearfarm_CLIController {
  private $args;
  private $tasks;
  private $verbs;

  public function __construct(array $args) {
    $this->args = $args;
  }
  
  public function run() {
    if(!isset($this->args[1]) || !isset($this->verbs[$this->args[1]])) {
      $this->showHelp();
      //TODO: define exit codes
      exit(-1);
    }
    $task = $this->verbs[$this->args[1]];
    try {
      $task->run($this->args);
      exit();
    } catch(Pearfarm_TaskArgumentException $ex) {
      echo $ex->getMessage()."\n";
      $task->showHelp();
      //TODO: define exit codes
      exit(-2);
    }
  }
  
  public function showHelp() {
    echo("usage: pearfarm COMMAND [ARGS]\n\nThe pfarm commands are:\n");
    foreach($this->tasks as $task) {
      $aliases = implode(", ", $task->getAliases());
      if(!empty($aliases)) {
        $aliases = " (".$aliases.")";
      }
      echo str_pad($task->getName().$aliases, 20, " ", STR_PAD_LEFT)."\t".$task->getDescription()."\n";
    }
    echo("\n");
  }
  
  public function register(Pearfarm_ITask $task) {
    $this->tasks[$task->getName()] = $task;
    $this->verbs[$task->getName()] = $task;
    foreach($task->getAliases() as $verb) {
      $this->verbs[$verb] = $task;
    }
  }
}
