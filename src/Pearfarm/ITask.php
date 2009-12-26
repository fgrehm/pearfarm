<?php
interface Pearfarm_ITask {
  public function run($args);
  public function showHelp();
  public function getAliases();
  public function getName();
  public function getDescription();
}

class Pearfarm_TaskArgumentException extends Exception {}

abstract class Pearfarm_AbstractTask implements Pearfarm_ITask
{
    const CONFIG_FILE_NAME  = '.pearfarm_config';
    const CONFIG_KEYFILE    = 'keyfile';

    protected $config;

    public function __construct()
    {
        $this->initConfig();
    }

    public function initConfig()
    {
        $configFilePath = getenv('HOME') . '/' . self::CONFIG_FILE_NAME;
        if (!file_exists($configFilePath))
        {
            print "WARNING: {$configFilePath} does not exist. Creating default one.";
            file_put_contents($configFilePath, "
" . self::CONFIG_KEYFILE . " = " . getenv('HOME') . "/.ssh/id_dsa
");
        }
        $this->config = parse_ini_file($configFilePath);
        if ($this->config === false) throw new Exception("Error reading ini file {$configFilePath}");
    }
}
