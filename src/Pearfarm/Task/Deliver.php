<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */

class Pearfarm_Task_Deliver implements Pearfarm_ITask {

  public function run($args) {
    $pkgTgzPath = getcwd() . "/{$args[2]}";

    // read these from config file
    $apiKey = '247b7825633eb9e485364207c99973a1';
    $channel = 'joe.dev.pearfarm.org';  // read from pkg
    $hash = md5(md5_file($pkgTgzPath) . $apiKey);

    $ch = curl_init("http://{$channel}/upload.xml");
    curl_setopt($ch, CURLOPT_POSTFIELDS, array(
          'file'      => "@{$pkgTgzPath}",
          'hash'      => $hash
          )
        );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $postResult = curl_exec($ch);
    curl_close($ch);
    print "$postResult";

    exit(0);
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
