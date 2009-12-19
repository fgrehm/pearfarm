<?php

class PEARFarm_Specification
{
    protected $name             = NULL;
    protected $channel          = NULL;
    protected $summary          = NULL;
    protected $description      = NULL;
    protected $releaseVersion   = NULL;
    protected $releaseStability = NULL;
    protected $apiVersion       = NULL;
    protected $apiStability     = NULL;
    protected $license          = NULL;

    // contents
    protected $files            = array();

    // dependencies
    protected $dependsOnPHPVersion              = NULL;
    protected $dependsOnPearInstallerVersion    = NULL;
    protected $dependsOnExtensions              = array();
    protected $dependsOnPEARPackages            = array();

    public function __construct($options = array())
    {
    }

    public function addFiles($files = array())
    {
        foreach ($files as $f) {
            $this->files[] = $f;
        }
    }

    public function addGitFiles($exclude = array())
    {
        $result = NULL;
        $output = array();
        $lastLine = exec('git ls-files', $output, $result);
        if ($result != 0) throw( new Exception("Error ($result) running git ls-files: " . join("\n", $output)) );

        foreach ($output as $file) {
            $this->files[] = $file;
        }

        return $this;
    }

    public static function newSpec($options = array())
    {
        return new PEARFarm_Specification($options);
    }

    public function __call($name, $value)
    {
        if (strncmp($name, 'set', 3) === 0)
        {
            $varName = substr($name, 3);
            $varName[0] = strtolower($varName[0]);
            //if (isset($this->$varName)) // not sure how to test (w/o reflection) to make sure $this->$varName exists.
            //{
                $this->$varName = $value;
                return $this;
            //}
        }
        throw new Exception("Function $name does not exist.");
    }
}

class PEARFarm_Builder
{
    protected $spec = NULL;

    public function __construct(PEARFarm_Specification $spec)
    {
        $this->spec = $spec;
    }

    public function build()
    {
        require_once('PEAR/PackageFileManager2.php');
        PEAR::setErrorHandling(PEAR_ERROR_DIE);
        //require_once 'PEAR/Config.php';
        //PEAR_Config::singleton('/path/to/unusualpearconfig.ini');
        // use the above lines if the channel information is not validating
        $packagexml = new PEAR_PackageFileManager2;
        $e = $packagexml->setOptions(
        array('baseinstalldir' => 'PhpDocumentor',
         'packagedirectory' => '/',
         'ignore' => array('TODO', 'tests/'), // ignore TODO, all files in tests/
         'installexceptions' => array('phpdoc' => '/*'), // baseinstalldir ="/" for phpdoc
         'dir_roles' => array('tutorials' => 'doc'),
         'exceptions' => array('README' => 'doc', // README would be data, now is doc
                               'PHPLICENSE.txt' => 'doc'))); // same for the license
        $packagexml->setPackage('MyPackage');
        $packagexml->setSummary('this is my package');
        $packagexml->setDescription('this is my package description');
        $packagexml->setChannel('mychannel.example.com');
        $packagexml->setAPIVersion('1.0.0');
        $packagexml->setReleaseVersion('1.2.1');
        $packagexml->setReleaseStability('stable');
        $packagexml->setAPIStability('stable');
        $packagexml->setNotes("We've implemented many new and exciting features");
        $packagexml->setPackageType('php'); // this is a PEAR-style php script package
        $packagexml->addRelease(); // set up a release section
        $packagexml->setOSInstallCondition('windows');
        $packagexml->addInstallAs('pear-phpdoc.bat', 'phpdoc.bat');
        $packagexml->addIgnoreToRelease('pear-phpdoc');
        $packagexml->addRelease(); // add another release section for all other OSes
        $packagexml->addInstallAs('pear-phpdoc', 'phpdoc');
        $packagexml->addIgnoreToRelease('pear-phpdoc.bat');
        $packagexml->addRole('pkg', 'doc'); // add a new role mapping
        $packagexml->setPhpDep('4.2.0');
        $packagexml->setPearinstallerDep('1.4.0a12');
        $packagexml->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
        $packagexml->setLicense('PHP License', 'http://www.php.net/license');
        $packagexml->generateContents(); // create the <contents> tag
        // replace @PHP-BIN@ in this file with the path to php executable!  pretty neat
        $test->addReplacement('pear-phpdoc', 'pear-config', '@PHP-BIN@', 'php_bin');
        $test->addReplacement('pear-phpdoc.bat', 'pear-config', '@PHP-BIN@', 'php_bin');
        $pkg = &$packagexml->exportCompatiblePackageFile1(); // get a PEAR_PackageFile object
        // note use of {@link debugPackageFile()} - this is VERY important
        if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
            $pkg->writePackageFile();
            $packagexml->writePackageFile();
        } else {
            $pkg->debugPackageFile();
            $packagexml->debugPackageFile();
        }
    }
}

