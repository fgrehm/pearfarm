<?php

class PEARFarm_Specification
{
    const LICENSE_MIT           = 'mit';

    // core settings
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
    protected $maintainers      = array();

    // dependencies
    protected $dependsOnPHPVersion              = NULL;
    protected $dependsOnPearInstallerVersion    = NULL;
    protected $dependsOnExtensions              = array();
    protected $dependsOnPEARPackages            = array();

    // package contents
    protected $files            = array();

    private static $licenseData = array(
        self::LICENSE_MIT => array('name' => 'MIT', 'uri' => 'http://www.opensource.org/licenses/mit-license.html')
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

    public function addMaintainer($type, $name, $user, $email, $active = true)
    {
        $this->maintainers[$type][] = array('name' => $name, 'user' => $user, 'email' => $email, 'active' => $active);
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
        // http://pear.php.net/manual/en/guide.developers.package2.php
        $xml = simplexml_load_string('<package/>', 'SuperSimpleXMLElement');
        $xml->addAttribute('version', '2.0');
        foreach (array('name', 'channel', 'summary', 'description') as $property) {
            $xml->addTextNode($property, htmlentities($this->$property));
        }

        // need to sort by leads, developers, contributors, helpers
        foreach ($this->maintainers as $type => $maintainers) {
            foreach ($maintainers as $maintainer) {
                $typeNode = $xml->addChild($type);
                $typeNode->addChild('name', $maintainer['name']);
                $typeNode->addChild('user', $maintainer['user']);
                $typeNode->addChild('email', $maintainer['email']);
                $typeNode->addChild('active', $maintainer['active'] ? 'yes' : 'no');
            }
        }

        $now = time();
        $xml->addTextNode('date', date('Y-m-d', $now));
        $xml->addTextNode('time', date('H:i:s', $now));

        $version = $xml->addChild('version');
        $version->addTextNode('release', $this->releaseVersion);
        $version->addTextNode('api', $this->apiVersion);

        $stability = $xml->addChild('stability');
        $stability->addTextNode('release', $this->releaseStability);
        $stability->addTextNode('api', $this->apiStability);

        $licenseData = $this->getLicense();
        $license = $xml->addTextNode('license', $licenseData['name']);
        $license->addAttribute('uri', $licenseData['uri']);

        foreach (array('notes') as $property) {
            $xml->addTextNode($property, htmlentities($this->$property));
        }

        $contentsNode = $xml->addChild('contents');

        $depsNode = $xml->addChild('dependencies');

        $phpReleaseNode = $xml->addChild('phprelease');

        file_put_contents('package.xml', $xml->asXML());
    }
}

class SuperSimpleXMLElement extends SimpleXMLElement
{
    public function addTextNode($entityName, $text)
    {
        $newNode = $this->addChild($entityName);
        $newNode[0] = $text;
        return $newNode;
    }
}
