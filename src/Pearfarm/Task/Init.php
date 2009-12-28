<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */
class Pearfarm_Task_Init implements Pearfarm_ITask {

  public function run($args) {
    if (isset($args[2])) {
      $specfile = $args[2];
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
    return array('create-spec');
  }

  public function getName() {
    return 'init';
  }

  public function getDescription() {
    return "Creates a pearfarm.spec file for your project.";
  }

  public function basicSpecFile($packageName = NULL) {
    if ($packageName === NULL) {
      $packageName = 'TODO: Your package name here';
    }
    $creatorName = 'TODO: Your name here';
    $creatorEmail = 'TODO: Your email here';
    $user = 'TODO: Your username here';
    $channel = 'TODO: Release channel here';
    $summary = 'TODO: One-line summary of your PEAR package';
    $description = 'TODO: Longer description of your PEAR package';

    return <<<STR
<?php

\$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('{$packageName}')
             ->setChannel('{$channel}')
             ->setSummary('{$summary}')
             ->setDescription('{$description}')
             ->setReleaseVersion('0.0.1')
             ->setReleaseStability('alpha')
             ->setApiVersion('0.0.1')
             ->setApiStability('alpha')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('Initial release.')
             ->addMaintainer('lead', '{$creatorName}', '{$user}', '{$creatorEmail}')
             ->addGitFiles()
             ->addExecutable('{$packageName}')
             ;
STR;
  }
}
