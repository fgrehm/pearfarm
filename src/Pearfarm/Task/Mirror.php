<?php

/* vim: set expandtab tabstop=2 shiftwidth=2: */

/**
 * Given an existing pear package, will update it to be pearfarm-hostable and publish the package to the specified channel
 *
 * @param 
 * @return
 * @throws
 */
class Pearfarm_Task_Mirror implements Pearfarm_ITask {
  public function run($args) {
    if (count($args) !== 4) throw new Exception("Usage: pearfarm mirror <package.tgz> <pearfarm user>");
    $packageFile = $args[2];
    $pearfarmMirrorUser = $args[3];

    print "\n\nWARNING: 'mirror' is experimental and has only been tested on Mac OS X platform.\n\n";

    $packageAndVersion = basename($packageFile, '.tgz');

    // make a tmp dir to "port" the package
    $workDir = "{$packageAndVersion}-work";
    if (file_exists($workDir)) throw new Exception("{$workDir} already exists. Please remove and try again.");
    mkdir($workDir);
    `tar -zxf {$packageFile} -C {$workDir}`;

    // update the XML file to point to the mirror channel
    $packageXMLFile = "{$workDir}/package.xml";
    $packageXML = simplexml_load_file($packageXMLFile);
    $packageName = $packageXML->name;                               // save this for later
    $packageXML->channel = "{$pearfarmMirrorUser}.pearfarm.org";    // update this so that it will work on the mirror
    file_put_contents("{$workDir}/package.xml", $packageXML->asXML());

    // re-build pacakge
    `cd {$workDir} && tar -czvf {$packageAndVersion}.tgz package.xml {$packageAndVersion}`;

    // push to pearfarm
    print "moving {$packageName} to {$pearfarmMirrorUser}.pearfarm.org\n";
    $pearfarmPushTask = new Pearfarm_Task_Push;
    $pearfarmPushTask->run(array(null, null, "{$workDir}/{$packageAndVersion}.tgz"));

    // clean up
    `rm -r {$workDir}`;

    print "The package was generated successfully.\n";
  }
  public function showHelp() {
  }
  public function getName() {
    return "mirror";
  }
  public function getAliases() {
    return array();
  }
  public function getDescription() {
    return "creates a pearfarm-based mirror of the given package.";
  }
}
