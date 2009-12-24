<?php
class Pearfarm_Task_Plant implements Pearfarm_ITask {
  
  public function run($args) {
    if(!isset($args[2])) {
      throw new Pearfarm_TaskArgumentException("You must specify a package name.\n");
    }
                
    //TODO: check if there is already a directory with that name
    //TODO: what should we do if we don't have write permissions?
    //TODO: validate package name
                $sep = DIRECTORY_SEPARATOR;
    $packageName = $args[2];
                echo "Creating $packageName folders...\n";
                
    mkdir($packageName);
                echo "  created $packageName{$sep}\n";

                file_put_contents($packageName . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'pearfarm.spec', $this->basicSpecFile($packageName));
                echo "  created $packageName{$sep}pearfarm.spec\n";

    mkdir($packageName . DIRECTORY_SEPARATOR . 'src');
                echo "  created $packageName{$sep}src\n";

    mkdir($packageName . DIRECTORY_SEPARATOR . 'data');
                echo "  created $packageName{$sep}data\n";

    mkdir($packageName . DIRECTORY_SEPARATOR . 'tests');
                echo "  created $packageName{$sep}tests\n";

    mkdir($packageName . DIRECTORY_SEPARATOR . 'doc');
                echo "  created $packageName{$sep}doc\n";

    mkdir($packageName . DIRECTORY_SEPARATOR . 'www');
                echo "  created $packageName{$sep}www\n";

    mkdir($packageName . DIRECTORY_SEPARATOR . 'examples');
                echo "  created $packageName{$sep}examples\n";

    // create default class
    // TODO: add doc block to class
    file_put_contents($packageName . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . ucfirst($packageName) . '.php', "<?php\nclass " . ucfirst($packageName) . " {\n\n\n}");
                echo "  created $packageName{$sep}src{$sep}" . ucfirst($packageName) . ".php\n";

  }

  public function basicSpecFile($packageName) {
    $creatorName = 'TODO: Your name here';
                $creatorEmail = 'TODO: Your email here';
                $user = 'TODO: Your username here';
                $channel = 'TODO: Release channel here';
                $summary = 'TODO: One-line summary of your PEAR package';
                $description = 'TODO: Longer description of your PEAR package';

    return <<<STR
<?php

\$spec = PackageSpec::create(array(PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
            ->setName('{$packageName}')
            ->setChannel('{$channel}')
            ->setSummary('{$summary}')
            ->setDescription('{$description}')
            ->setReleaseVersion('0.0.1')
            ->setReleaseStability('alpha')
            ->setApiVersion('0.0.1')
            ->setApiStability('alpha')
            ->setLicense(PackageSpec::LICENSE_MIT)
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
    return "plant";
  }
  public function getAliases() {
    return array("p", "pl");
  }
  public function getDescription() {
    return "creates the package";
  }
}