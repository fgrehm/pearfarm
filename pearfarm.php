<?php

class PEARFarm_Specification
{
    const LICENSE_MIT           = 'mit';

    protected $name             = NULL;
    protected $channel          = NULL;
    protected $summary          = NULL;
    protected $description      = NULL;
    protected $releaseVersion   = NULL;
    protected $releaseStability = NULL;
    protected $apiVersion       = NULL;
    protected $apiStability     = NULL;
    protected $license          = NULL;
    protected $notes            = NULL;

    // contents
    protected $files            = array();

    // dependencies
    protected $dependsOnPHPVersion              = NULL;
    protected $dependsOnPearInstallerVersion    = NULL;
    protected $dependsOnExtensions              = array();
    protected $dependsOnPEARPackages            = array();

    private static $licenseData = array(
        self::LICENSE_MIT => array('name' => 'MIT', 'url' => 'http://www.opensource.org/licenses/mit-license.html')
    );

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

    public function getLicense()
    {
        if (is_array($this->license))
        {
            return $this->license;
        }
        return self::$licenseData[$this->license];
    }

    public function __call($name, $value)
    {
        switch (substr($name, 0, 3)) {
            case 'set':
                $varName = substr($name, 3);
                $varName[0] = strtolower($varName[0]);
                //if (isset($this->$varName)) // not sure how to test (w/o reflection) to make sure $this->$varName exists.
                //{
                    $this->$varName = $value[0];
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

    public function writePackageFile()
    {
        $xml = '<xml>';
        file_put_contents('package.xml', $xml);
    }
}
