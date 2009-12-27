<?php

/* vim: set expandtab tabstop=2 shiftwidth=2: */

class Pearfarm_Task_Keygen implements Pearfarm_ITask {
  public function run($args) {
    if (isset($args[2])) {
      $privateKeyFile = $args[2];
    } else {
      $privateKeyFile = getenv('HOME') . '/.ssh/id_rsa';
    }

    print "Generating public key file from private key at {$privateKeyFile}...\n";
    if (!file_exists($privateKeyFile)) {
      print "{$privateKeyFile} does not exist.\n";
      print "Either supply a valid private keyfile as an argument or generate a new keypair using:\n";
      print " ssh-keygen -t rsa -C 'youremail@email.com'\n";
      print "And try again.\n";
      exit(1);
    }

    $out = `openssl rsa -in {$privateKeyFile} -pubout`;
    print $out;
  }
  public function showHelp() {
  }
  public function getName() {
    return 'keygen';
  }
  public function getAliases() {
    return array();
  }
  public function getDescription() {
    return "Output a public key suitable for copy/paste into your pearfarm account.";
  }
}
