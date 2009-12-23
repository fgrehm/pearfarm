<?php

interface Task {
	public function run($args);
	public function showHelp();
	public function getAliases();
	public function getName();
	public function getDescription();
}

class TaskArgumentException extends Exception {}

class PlantTask implements Task {
	public function run($args) {
		if(!isset($args[2])) {
			throw new TaskArgumentException("You must specify a package name.\n");
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


class InitTask extends PlantTask {
	public function run($args) {
		if(!isset($args[2]))
			throw new TaskArgumentException("You must specify a package name.\n");
		$specfile = getcwd() . '/pearfarm.spec';
		file_put_contents($specfile, $this->basicSpecFile($args[2]));
		echo "  created $specfile\n";
	}
	public function showHelp() {
	  
	}
	public function getAliases() {
	  return array();
	}
	public function getName() {
	  return 'init';
	}
	public function getDescription() {
	  return "creates just a pearfarm.spec file";
	}
}

class CollectTask implements Task {
	public function run($args) {
		require dirname(__FILE__).DIRECTORY_SEPARATOR.'builder.php';

		if (isset($argv[1]))
    			$specfile = $argv[1];
		else
    			$specfile = getcwd() . '/pearfarm.spec';

		print "Reading specfile at {$specfile}...\n";
		if (!file_exists($specfile)) {
    			print "{$specfile} is not a pearfarm.spec file.\n";
    			exit(1);
		}

		include $specfile;
		if (!isset($spec)) {
			print "specfile didn't create a local variable named '\$spec'.\n";
			exit(1);
		}

		$spec->writePackageFile();

		print "The package.xml file was written successfully, executing 'pear package'...\n";
		exec('pear package');

		print "The package was generated successfully.\n";
	}
	public function showHelp() {

	}
	public function getName() {
		return "collect";
	}
	public function getAliases() {
		return array('build');
	}
	public function getDescription() {
		return "builds the package";
	}
}

class TryTask implements Task {
	public function run($args) {
                $cmd = PEARFARM_CMD . ' build';
		print "Building package with $cmd...\n";
		exec($cmd);

		require dirname(__FILE__).DIRECTORY_SEPARATOR.'builder.php';

		$specfile = getcwd() . '/pearfarm.spec';
		include $specfile;
                print "Installing {$spec->getName()} {$spec->getReleaseVersion()}...\n";

                $isUpgrade = (isset($args[2]) && $args[2] == '-u');
                $cmd = $isUpgrade ? 'upgrade' : 'install';

                $result = array();
                $output = '';
		exec("pear $cmd {$spec->getName()}-{$spec->getReleaseVersion()}.tgz", $result, $output);

                if (strstr($result[0], 'install ok') !== false)
                    print "The package was installed successfully.\n";
                elseif (strstr($result[0], 'upgrade ok') !== false)
                    print "The package was upgraded successfully.\n";
                else {
                    $help = $isUpgrade ? '' : ' Try running with -u option to upgrade.';
                    print "\n\nThere were errors installing the package.$help\n  PEAR output:\n    " . join("\n    ", $result) . "\n";
                }
	}
	public function showHelp() {

	}
	public function getName() {
		return "try";
	}
	public function getAliases() {
		return array();
	}
	public function getDescription() {
		return "installs the package for testing purposes (-u to upgrade the package)";
	}
}

class DeliverTask implements Task {
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

