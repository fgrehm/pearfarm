<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */

class Pearfarm_Task_Plant implements Pearfarm_ITask {

  public function run($args) {
    if(!isset($args[2])) {
      throw new Pearfarm_TaskArgumentException("You must specify a package name.\n");
    }

    //TODO: check if there is already a directory with that name
    //TODO: what should we do if we don't have write permissions?
    //TODO: validate package name
    $packageName = $args[2];
    print("Creating $packageName folders...\n");
    foreach(array('src', 'data', 'tests', 'doc', 'www', 'examples') as $folder) {
      $path = implode(DIRECTORY_SEPARATOR, array($packageName, $folder));
      mkdir($path, 0777, true);
      print("  created $path\n");
    }
    $spec_path = implode(DIRECTORY_SEPARATOR, array($packageName, 'pearfarm.spec'));
    file_put_contents($spec_path, $this->basicSpecFile($packageName));
    print("  created $spec_path\n");

    $this->generateClass($packageName);
  }

  public function generateClass($name) {
    //create default class
    //TODO: add doc block to class
    $file_name = str_replace('/', '_', basename($name)); //do not remove this it ensures that the vfsstream tests pass
    $class_path = implode(DIRECTORY_SEPARATOR, array($name, 'src', ucfirst($file_name) . '.php'));
    file_put_contents($class_path, "<?php\nclass " . ucfirst($name) . " {\n\n\n}");
    print("  created $class_path\n");
  }

  public function basicSpecFile($packageName = NULL) {
    if ($packageName === NULL) {
      $packageName = 'TODO; Your package name here';
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
  public function showHelp() {
    echo "TODO: Print some help.\n";
  }
  public function getName() {
    return "bootstrap";
  }
  public function getAliases() {
    return array("plant", "pl", "p");
  }
  public function getDescription() {
    return "creates the package directory and file";
  }
}
