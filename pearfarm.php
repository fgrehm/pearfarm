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

        return $this;
    }

    public function addGitFiles($exclude = array())
    {
        $result = NULL;
        $output = array();
        $lastLine = exec('git ls-files', $output, $result);
        if ($result != 0) throw( new Exception("Error ($result) running git ls-files: " . join("\n", $output)) );

        $this->addFiles($output);

        return $this;
    }

    public static function newSpec($options = array())
    {
        return new PEARFarm_Specification($options);
    }

    public function __call($name, $value)
    {
        switch (substr($name, 0, 3)) {
            case 'set':
                $varName = substr($name, 3);
                $varName[0] = strtolower($varName[0]);
                //if (isset($this->$varName)) // not sure how to test (w/o reflection) to make sure $this->$varName exists.
                //{
                    $this->$varName = $value;
                    return $this;
                //}
                break;
            case 'get':
                $varName = substr($name, 3);
                $varName[0] = strtolower($varName[0]);
                return $this->$varName;
                break;
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
        $spec = $this->spec;

        require_once('PEAR/PackageFileManager2.php');
        PEAR::setErrorHandling(PEAR_ERROR_DIE);
        //require_once 'PEAR/Config.php';
        //PEAR_Config::singleton('/path/to/unusualpearconfig.ini');
        // use the above lines if the channel information is not validating
        $packagexml = new PEAR_PackageFileManager2;
#        $e = $packagexml->setOptions(
#            array(
#             'baseinstalldir' => 'PhpDocumentor',
#             'packagedirectory' => '/',
#             'ignore' => array('TODO', 'tests/'), // ignore TODO, all files in tests/
#             'installexceptions' => array('phpdoc' => '/*'), // baseinstalldir ="/" for phpdoc
#             'dir_roles' => array('tutorials' => 'doc'),
#             'exceptions' => array('README' => 'doc', // README would be data, now is doc
#                                   'PHPLICENSE.txt' => 'doc') // same for the license
#            )
#        );
        $packagexml->setPackage($spec->getName());
        $packagexml->setChannel($spec->getChannel());
        $packagexml->setSummary($spec->getSummary());
        $packagexml->setDescription($spec->getDescription());
        $packagexml->setReleaseVersion($spec->getReleaseVersion());
        $packagexml->setReleaseStability($spec->getReleaseStability());
        $packagexml->setAPIVersion($spec->getApiVersion());
        $packagexml->setAPIStability($spec->getApiStability());
        $packagexml->setPhpDep($spec->getDependsOnPHPVersion());
        $packagexml->setPearinstallerDep($spec->getDependsOnPearInstallerVersion());
        //$packagexml->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
        $packagexml->setLicense($spec->getLicense());
        $packagexml->generateContents(); // create the <contents> tag
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

