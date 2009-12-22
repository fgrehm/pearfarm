<?php


interface Task {
	public function run($args);
	public function showHelp();
	public function getAliases();
	public function getName();
	public function getDescription();
}

class TaskArgumentException extends Exception {
	
}

class PlantTask implements Task {
	public function run($args) {
		if(!isset($args[2])) {
			throw new TaskArgumentException("You must specify a package name.\n");
		}
                echo "Creating folders...\n";
		//TODO: check if there is already a directory with that name
		//TODO: what should we do if we don't have write permissions?
		//TODO: validate package name
                $sep = DIRECTORY_SEPARATOR;
		$packageName = $args[2];
                echo "Creating $packageName folders...\n";
                
		mkdir($packageName);
                echo "  created $packageName{$sep}\n";

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

class CollectTask implements Task {
	public function run($args) {

	}
	public function showHelp() {

	}
	public function getName() {
		return "collect";
	}
	public function getAliases() {
		return array();
	}
	public function getDescription() {
		return "builds the package";
	}
}

class TryTask implements Task {
	public function run($args) {

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
		return "installs the package for testing purposes";
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

class PFarm {
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
		} catch(TaskArgumentException $ex) {
			echo $ex->getMessage()."\n";
			$task->showHelp();
			//TODO: define exit codes
			exit(-2);
		}
	}
	public function showHelp() {
		echo("usage: pfarm COMMAND [ARGS]\n\nThe pfarm commands are:\n");
		foreach($this->tasks as $task) {
			$aliases = implode(", ", $task->getAliases());
			if(!empty($aliases)) {
				$aliases = " (".$aliases.")";
			}
			echo str_pad($task->getName().$aliases, 20, " ", STR_PAD_LEFT)."\t".$task->getDescription()."\n";
		}
		echo("\n");
	}
	public function register(Task $task) {
		$this->tasks[$task->getName()] = $task;
		$this->verbs[$task->getName()] = $task;
		foreach($task->getAliases() as $verb) {
			$this->verbs[$verb] = $task;
		}
	}
}

$pfarm = new PFarm($argv);
$pfarm->register(new PlantTask());
$pfarm->register(new CollectTask());
$pfarm->register(new TryTask());
$pfarm->register(new DeliverTask());
$pfarm->run();

die();