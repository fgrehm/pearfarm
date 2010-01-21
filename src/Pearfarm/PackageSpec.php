<?php

/* vim: set expandtab tabstop=2 shiftwidth=2: */

/**
 * Pearfarm_PackageSpec is the class that is used in pearfarm.spec files to specify PEAR packaging instructions.
 *
 * The API docs for Pearfarm_PackageSpec are very useful for creating PEAR packages with pearfarm.
 *
 * @package pearfarm
 */

/**
 * Pearfarm_PackageSpec is a simplified DSL for creating basic pear pacakges.
 *
 * Instead of having to learn about the complexities of PEAR you can just specify a few basic facts and Pearfarm will build you a reasonable package.xml.
 *
 * <b>If you don't already have a pearfarm.spec file for your project, just run "pearfarm init" and an example spec file will be generated for you.</b>
 * Then you need only edit the boilerplate in the pearfarm.spec file and customize it for your needs, using these docs as a reference.
 *
 * To build a PEAR package based on your pearfarm.spec, run "pear package". Pay careful attention to any errors and warnings you see; pearfarm itself
 * doesn't do much error checking since "pear package" already does a lot.
 *
 * NOTE: this code was hacked up quickly one weekend and isn't particularly pretty. I was learning PEAR architecture at the same time. My apologies in advance.
 *
 * NOTE: below are the meta-method docs for stuff done via __call. Phpdoc sucks so they don't show up very well.
 * - object Pearfarm_PackageSpec setName(string $value): Set the package name.
 * - object Pearfarm_PackageSpec setChannel(string $value): Set the channel. If you are deploying via pearfarm, this should be <pearfarm-username>.pearfarm.org.
 * - object Pearfarm_PackageSpec setSummary(string $value): Set the Summary of the pacakge.
 * - object Pearfarm_PackageSpec setDescription(string $value): Set the detailed Description of the package.
 * - object Pearfarm_PackageSpec setReleaseVersion(string $value): Set the release version string.
 * - object Pearfarm_PackageSpec setReleaseStability(string $value): Set the release stability (devel, alpha, beta, stable)
 * - object Pearfarm_PackageSpec setApiVersion(string $value): Set the API version string.
 * - object Pearfarm_PackageSpec setApiStability(string $value): Set the API stability (devel, alpha, beta, stable)
 * - object Pearfarm_PackageSpec setNotes(string $value): Set the notes for the package. Typically you should include a link to your project web site here.
 * @package pearfarm
 */
class Pearfarm_PackageSpec
{
  const LICENSE_MIT           = 'MIT';
  const LICENSE_BSD           = 'BSD';
  const LICENSE_PHP           = 'PHP';
  const LICENSE_GPL           = 'GPL';
  const LICENSE_LGPL          = 'LGPL';
  const LICENSE_GPL3          = 'GPL3';
  const LICENSE_LGPL3         = 'LGPL3';
  const LICENSE_APACHE        = 'APACHE';

  const ROLE_PHP              = 'php';
  const ROLE_SCRIPT           = 'script';
  const ROLE_DOC              = 'doc';
  const ROLE_TEST             = 'test';
  const ROLE_DATA             = 'data';

  const PLATFORM_ANY          = 'any';
  const PLATFORM_WIN          = 'windows';

  const OPT_BASEDIR           = 'basedir';
  const OPT_DEBUG             = 'debug';

  protected $options          = array();

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
  protected $dependsOnPHPVersionMin           = '5.0.0';
  protected $dependsOnPHPVersionMax           = NULL;
  protected $dependsOnPHPVersionExclude       = array();

  protected $dependsOnPearInstallerVersionMin         = '1.4.0';
  protected $dependsOnPearInstallerVersionMax         = NULL;
  protected $dependsOnPearInstallerVersionRecommended = NULL;
  protected $dependsOnPearInstallerVersionExclude     = array();

  protected $dependsOnExtensions              = array();
  protected $dependsOnPEARPackages            = array();

  // package contents
  protected $files                = array();
  protected $excludeFilesRegexs   = array();
  protected $executables          = array();

  private static $licenseData = array(
      self::LICENSE_MIT     => array('name' => 'MIT', 'uri' => 'http://www.opensource.org/licenses/mit-license.html'),
      self::LICENSE_BSD     => array('name' => 'BSD', 'uri' => 'http://www.opensource.org/licenses/bsd-license.php'),
      self::LICENSE_PHP     => array('name' => 'PHP', 'uri' => 'http://www.opensource.org/licenses/php-license.php'),
      self::LICENSE_GPL     => array('name' => 'GPL', 'uri' => 'http://www.opensource.org/licenses/gpl-license.php'),
      self::LICENSE_LGPL    => array('name' => 'LGPL', 'uri' => 'http://www.opensource.org/licenses/lgpl-license.php'),
      self::LICENSE_GPL3    => array('name' => 'GPL3', 'uri' => 'http://www.opensource.org/licenses/gpl3-license.php'),
      self::LICENSE_LGPL3   => array('name' => 'LGPL3', 'uri' => 'http://www.opensource.org/licenses/lgpl3-license.php'),
      self::LICENSE_APACHE  => array('name' => 'APACHE', 'uri' => 'http://www.opensource.org/licenses/apache-license.php'),
      );

  /**
   * Pearfarm_PackageSpec constructor.
   *
   * @param array Options for the Pearfarm_PackageSpec object, see Pearfarm_PackageSpec::OPT_*
   */
  public function __construct($options = array())
  {
    $this->options = array_merge(array(
          self::OPT_BASEDIR       => '.',
          self::OPT_DEBUG         => false,
          ), $options);

    $this->options[self::OPT_BASEDIR] = realpath($this->options[self::OPT_BASEDIR]);
  }

  /**
   * @ignore
   */
  private function debug($msg)
  {
    if ($this->options[self::OPT_DEBUG]) print "$msg\n";
  }

  /**
   * Add a Pearfarm_PackageSpecFile to the list of files to add to the package.
   *
   * @param object Pearfarm_PackageSpecFile
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function addFile(Pearfarm_PackageSpecFile $f)
  {
    $this->debug("Adding file {$f->getFilePath()}");
    $this->files[$f->getFilePath()] = $f;
  }

  /**
   * Add a file or files to the package based on simple paths.
   *
   * Paths should be relative to project root, ie: path/to/myfile.php.
   *
   * @param mixed A string filename, or an array of filenames
   * @param string The "role" of the file for PEAR's benefit; one of Pearfarm_PackageSpec::ROLE_*
   * @param array An array of options to pass to new Pearfarm_PackageSpecFile()
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function addFilesSimple($files, $role = self::ROLE_PHP, $options = array())
  {
    if (!is_array($files))
    {
      $files = array($files);
    }
    foreach ($files as $f) {
      $this->addFile( new Pearfarm_PackageSpecFile($f, $role, $options) );
    }
    return $this;
  }

  /**
   * Add files to the PEAR package based on regex filter of file paths.
   *
   * @param mixed A string regex pattern (must include //) or an array of patterns.
   * @param string The "role" of the file for PEAR's benefit; one of Pearfarm_PackageSpec::ROLE_*
   * @param array An array of options to pass to new Pearfarm_PackageSpecFile().
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function addFilesRegex($regexs, $role = self::ROLE_PHP, $options = array())
  {
    if (!is_array($regexs))
    {
      $regexs = array($regexs);
    }
    $basedirOffset = strlen($this->options[self::OPT_BASEDIR]) + 1; // +1 for dirsep
    foreach ($regexs as $regex) {
      foreach (new RecursiveFileRegexFilterIterator($this->options[self::OPT_BASEDIR], $regex) as $addFile) {
        $this->debug("[regex-match] {$addFile->getPathname()} matched {$regex}");
        $addFileRelPath = substr($addFile->getPathname(), $basedirOffset);
        $this->addFile( new Pearfarm_PackageSpecFile($addFileRelPath, $role, $options) );
      }
    }
    return $this;
  }

  /**
   * Get the {@link object Pearfarm_PackageSpecFile} for the given path.
   *
   * @param string The path (relative to the project root, ie a/b/c.php)
   * @return object Pearfarm_PackageSpecFile NULL if not found.
   */
  public function getFile($path)
  {
    if (isset($this->files[$path]))
    {
      return $this->files[$path];
    }
    return NULL;
  }

  /**
   * Add a regex pattern which will cause a file that has been included previously to be excluded from the final package.
   *
   * @param mixed A string regex pattern (must include //) or an array of patterns.
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function addExcludeFilesRegex($regexs)
  {
    if (!is_array($regexs))
    {
      $regexs = array($regexs);
    }
    $this->excludeFilesRegexs = array_merge($this->excludeFilesRegexs, $regexs);
    return $this;
  }

  /**
   * Add a file path which will cause a file that has been included previously to be excluded from the final package.
   *
   * @param mixed A filepath to a file to exclude, must be relative to project root, aka path/to/myfiletoexclude.php
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function addExcludeFiles($excludeFiles)
  {
    if (!is_array($excludeFiles))
    {
      $excludeFiles = array($excludeFiles);
    }
    foreach ($excludeFiles as $excludeFile) {
      $this->addExcludeFilesRegex("/^\Q{$excludeFile}\E$/");
    }
    return $this;
  }

  /**
   * Add all files that git knows about to the package.
   *
   * @return object Pearfarm_PackageSpec For fluent interface.
   * @throws
   */
  public function addGitFiles()
  {
    $result = NULL;
    $output = array();
    $lastLine = exec("cd {$this->options[self::OPT_BASEDIR]} && git ls-files", $output, $result);
    if ($result != 0) throw( new Exception("Error ($result) running git ls-files: " . join("\n", $output)) );

    $this->addFilesSimple($output);

    return $this;
  }

  /**
   * Add all files that svn knows about to the package.
   *
   * @return object Pearfarm_PackageSpec For fluent interface.
   * @throws
   */
  public function addSvnFiles()
  {
    $result = NULL;
    $output = array();
    $lastLine = exec("cd {$this->options[self::OPT_BASEDIR]} && svn ls -R", $output, $result);
    if ($result != 0) throw( new Exception("Error ($result) running svn ls -R: " . join("\n", $output)) );

    $filesOnly = array();
    foreach ($output as $svnItem) {
      if (!is_file($svnItem)) continue; // skip dirs

      $filesOnly[] = $svnItem;
    }

    $this->addFilesSimple($filesOnly);

    return $this;
  }

  /**
   * Add a maintainer.
   *
   * @param string Type
   * @param string Name
   * @param string Username (I think this is the "PEAR" username, meaning it's n/a for pearfarm)
   * @param string Email address
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function addMaintainer($type, $name, $user, $email, $active = true)
  {
    $this->maintainers[$type][] = array('name' => $name, 'user' => $user, 'email' => $email, 'active' => $active);
    return $this;
  }

  /**
   * Set the php version dependency info.
   *
   * @param string Minimum version.
   * @param string Maximum version.
   * @param array An array of strings of exact versions that the package is *not* compatible with.
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function setDependsOnPHPVersion($min, $max = NULL, $exclude = array())
  {
    $this->dependsOnPHPVersionMin = $min;
    $this->dependsOnPHPVersionMax = $max;
    $this->dependsOnPHPVersionExclude = $exclude;

    return $this;
  }

  /**
   * Set the PEAR installer version dependency info.
   *
   * @param string Minimum version.
   * @param string Maximum version.
   * @param string Recommended version.
   * @param array An array of strings of exact versions that the package is *not* compatible with.
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function setDependsOnPearInstallerVersion($min, $max = NULL, $recommended = NULL, $exclude = array())
  {
    $this->dependsOnPearInstallerVersionMin = $min;
    $this->dependsOnPearInstallerVersionMax = $max;
    $this->dependsOnPearInstallerVersionRecommended = $recommended;
    $this->dependsOnPearInstallerVersionExclude = $exclude;

    return $this;
  }

  /**
   * Add an "exectuable" file to your package.
   *
   * The file must have already been added to the package; this function just makes a previously included file be tagged
   * as an executable in your packate so that when installed PEAR will place it in the bin/ directory.
   *
   * This function is a high-level abstraction to the package.xml format for convenience reasons.
   *
   * Executables added with this function get two replace-tasks added to them:
   * 1. '/usr/bin/env php' => pear-installation "php_bin" location
   * 2. '@php_bin@'        => pear-installation "php_bin" location
   * 3. '@pear_directory@' => pear-installation "php_dir" location
   *
   * This allows you to write your executable script in a way that makes it easy to run locally and when installed via pear
   * if you write your exectuables so that they have the following shbang line:
   * <code>
   * #!/usr/bin/env php
   * </code>
   *
   * Here is example PHP code of making your executable run properly from download and PEAR installs:
   * <code>
   * if (strpos('@php_bin@', '@php_bin') === 0) {  // not a pear install
   *   define('PEARFARM_INCLUDE_PREFIX', dirname(__FILE__));
   *   define('PEARFARM_CMD', 'php pearfarm');
   * } else {
   *   define('PEARFARM_INCLUDE_PREFIX', 'pearfarm');
   *   define('PEARFARM_CMD', 'pearfarm');
   * }
   * </code>
   *
   * Here is example shell script code of making your executable run properly from download and PEAR installs:
   * <code>
   *  PROGRAM_RUN_FROM_DIR=`pwd`
   *  PEAR_INSTALL_DIR="@pear_directory@"
   *  if [ ${PEAR_INSTALL_DIR:0:15} == "@pear_directory" ] ; then
   *    # is not pear install
   *    # we never really expect PROJECT_HOME to be set... we get this info from the path to this executable
   *    # echo "WARNING: PROJECT_HOME environment not set. Attempting to guess."
   * 
   *    # try to find myproject
   *    
   *    ## resolve links - $0 may be a symlink
   *    PRG="$0"
   *    progname=`basename "$0"`
   * 
   *    # need this for relative symlinks
   *    dirname_prg=`dirname "$PRG"`
   *    cd "$dirname_prg"
   *    
   *    while [ -h "$PRG" ] ; do
   *      ls=`ls -ld "$PRG"`
   *      link=`expr "$ls" : '.*-> \(.*\)$'`
   *      if expr "$link" : '/.*' > /dev/null; then
   *          PRG="$link"
   *      else
   *          PRG=`dirname "$PRG"`"/$link"
   *      fi
   *    done
   *    cd "$PROGRAM_RUN_FROM_DIR"
   *    
   *    PROJECT_HOME=`dirname "$PRG"`/..
   * 
   *    # make it fully qualified
   *    PROJECT_HOME=`cd "$PROJECT_HOME" && pwd`
   *  else
   *    # is a pear install
   *    PROJECT_HOME="@pear_directory@/myproject"
   *  fi
   * </code>
   *
   * @param string The project-relative path to the executable.
   * @param string The name the executable should have once installed. Defaults to the name of the file.
   * @param string One of the Pearfarm_PackageSpec::PLATFORM_* constants specificying which platform this executable runs on.
   * @return object Pearfarm_PackageSpec For fluent interface.
   * @throws object Exception If the provided file path hasn't already been added to the spec.
   */
  public function addExecutable($scriptFilePath, $renameTo = NULL, $platform = self::PLATFORM_ANY)
  {
    if (!isset($this->files[$scriptFilePath])) throw new Exception("File {$scriptFilePath} does not exist.");

    $fileObj = $this->files[$scriptFilePath];
    // convert fileObj to role=script and set up to install in bin/
    $fileObj->setAttribute('role', 'script');
    $fileObj->setAttribute('baseinstalldir', '/');
    $fileObj->addReplaceTask('pear-config', '/usr/bin/env php', 'php_bin');
    $fileObj->addReplaceTask('pear-config', '@php_bin@', 'php_bin');
    $fileObj->addReplaceTask('pear-config', '@pear_directory@', 'php_dir');
    if ($renameTo === NULL)
    {
      $renameTo = basename($scriptFilePath);
    }
    $this->executables[$platform][] = array('name' => $scriptFilePath, 'as' => $renameTo);

    return $this;
  }

  /**
   * Add a package dependency.
   *
   * If your package depends on another pear package, this is how to manifest it.
   *
   * @param string The name of the dependency
   * @param string Either a channel name, or a URI.
   * @param array Options hash. Pass any of required (bool), name, channel, uri, min, max, recommended, recommendedMin, recommendedMax, exclude (array of version strings), conflict (bool).
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function addPackageDependency($name, $pkgSpec, $options = array())
  {
    $depInfo = array_merge(array(
          // order here matters!!! do not rearrange
          'required'          => true,
          'name'              => $name,
          'channel'           => NULL,
          'uri'               => NULL,
          'min'               => NULL,
          'max'               => NULL,
          'recommended'       => NULL,
          'recommendedMin'    => NULL,
          'recommendedMax'    => NULL,
          'exclude'           => array(),
          'conflicts'         => NULL,
          ), $options);
    if (preg_match('/http[s]?:\/\.*/', $pkgSpec))
    {
      $depInfo['uri'] = $pkgSpec;
    }
    else
    {
      $depInfo['channel'] = $pkgSpec;
    }
    if ($depInfo['conflicts'] === true)
    {
      $depInfo['conflicts'] = '';
    }
    $this->dependsOnPEARPackages[] = $depInfo;

    return $this;
  }

  /**
   * Set the license for the package.
   *
   * There are three forms:
   *
   * 1. setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
   * 2. setLicense(array('name' => 'MIT', 'uri' => 'http://www.opensource.org/licenses/mit-license.php'))
   * 3. setLicense('MIT', 'http://www.opensource.org/licenses/mit-license.php')
   *
   * @param mixed Form #1: string, one of the Pearfarm_PackageSpec::LICENSE_* constants.
   *              Form #2: array, in form array('name' => 'MIT', 'url' => 'http://www.opensource.org/licenses/mit-license.php')
   *              Form #3: string, Name of license
   * @param string Used only with Form #3, URI of license.
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function setLicense($license, $licenseURI = NULL)
  {
    if (is_null($licenseURI))
    {
      $this->license = $license;
    }
    else
    {
      $this->license = array('name' => $license, 'uri' => $licenseURI);
    }
    return $this;
  }

  /**
   * @ignore
   */
  public function getLicense()
  {
    if (is_array($this->license))
    {
      return $this->license;
    }
    return self::$licenseData[$this->license];
  }

  /**
   * @ignore
   */
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

  /**
   * @ignore
   */
  private function prepareFiles()
  {
    $removeTheseFiles = array();

    foreach ($this->files as $file => $fileObj) {
      // filter out excludes
      foreach ($this->excludeFilesRegexs as $excludeRegex) {
        if (preg_match($excludeRegex, $file))
        {
          $this->debug("Excluding file regex '{$excludeRegex}' matched: {$file}");
          $removeTheseFiles[] = $file;
          break; // no need to process further, it's already excluded
        }
      }
    }
    foreach ($removeTheseFiles as $removeThisFile) {
      unset($this->files[$removeThisFile]);
    }
    ksort($this->files);
    return $this->files;
  }

  /**
   * Outputs the package.xml file to the location speficied in {@link self::OPT_BASEDIR}
   */
  public function writePackageFile()
  {
    // http://pear.php.net/manual/en/guide.developers.package2.php
    // unfortunately, order matters in package.xml, so don't mess with it!
    $xml = simplexml_load_string('<package
        xmlns="http://pear.php.net/dtd/package-2.0"
        xmlns:tasks="http://pear.php.net/dtd/tasks-1.0"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0
        http://pear.php.net/dtd/tasks-1.0.xsd
http://pear.php.net/dtd/package-2.0
http://pear.php.net/dtd/package-2.0.xsd"/>', 'SuperSimpleXMLElement');
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
    // baseinstalldir = "name" of package --- prevents conflicts i suppose
    $rootDirObj = new Pearfarm_PackageSpecDir('.', array(Pearfarm_PackageSpecDir::BASEINSTALLDIR => $this->name));

    // build all dir & file blocks
    $this->prepareFiles();
    $dirs = array('.' => $rootDirObj);    // dirPath => object Pearfarm_PackageSpecDir
    foreach ($this->files as $filePath => $fileObj) {
      //print "processing $filePath\n";
      $fileDirPath = dirname($filePath);
      // make sure all dirs up to this point are represented
      $allDirs = explode(DIRECTORY_SEPARATOR, ltrim($fileDirPath, DIRECTORY_SEPARATOR));
      $dirPath = NULL;
      foreach ($allDirs as $dir) {
        $dirPath .= $dir;
        if (!isset($dirs[$dirPath]))
        {
          // create directory
          $dirObj = new Pearfarm_PackageSpecDir($dirPath);
          $dirs[$dirPath] = $dirObj;
          $parentDir = $dirs[dirname($dirPath)];
          //print "adding dir $dirPath to dir {$parentDir}\n";
          $parentDir->addItem($dirObj);
        }

        $dirPath .= DIRECTORY_SEPARATOR;
      }
      // add files to proper dir
      //print "adding file $filePath to dir $fileDirPath\n";
      $dirs[$fileDirPath]->addItem($fileObj);
    }
    //print_r($rootDirObj);
    $rootDirObj->addXMLAsChild($contentsNode);

    // deps
    $depsNode = $xml->addChild('dependencies');
    $reqNode = $depsNode->addChild('required');
    $optNode = NULL;

    // php & pear installer HAVE to be there
    // php
    $phpNode = $reqNode->addChild('php');
    $phpNode->addChild('min', $this->dependsOnPHPVersionMin);
    if ($this->dependsOnPHPVersionMax !== NULL)
    {
      $phpNode->addChild('max', $this->dependsOnPHPVersionMax);
    }
    foreach ($this->dependsOnPHPVersionExclude as $excludeVersion) {
      $phpNode->addChild('exclude', $excludeVersion);
    }
    // pear installer
    $pearInstallerNode = $reqNode->addChild('pearinstaller');
    $pearInstallerNode->addChild('min', $this->dependsOnPearInstallerVersionMin);
    if ($this->dependsOnPearInstallerVersionMax !== NULL)
    {
      $pearInstallerNode->addChild('max', $this->dependsOnPearInstallerVersionMax);
    }
    if ($this->dependsOnPearInstallerVersionMax !== NULL)
    {
      $pearInstallerNode->addChild('max', $this->dependsOnPearInstallerVersionMax);
    }
    if ($this->dependsOnPearInstallerVersionRecommended !== NULL)
    {
      $pearInstallerNode->addChild('recommended', $this->dependsOnPearInstallerVersionRecommended);
    }
    foreach ($this->dependsOnPearInstallerVersionExclude as $excludeVersion) {
      $pearInstallerNode->addChild('exclude', $excludeVersion);
    }

    // all other deps
    foreach ($this->dependsOnPEARPackages as $dep) {
      if ($dep['required'])
      {
        $addToNode = $reqNode;
      }
      else
      {
        if ($optNode === NULL)
        {
          $optNode = $depsNode->addChild('optional');
        }
        $addToNode = $optNode;
      }
      $pkgNode = $addToNode->addChild('package');
      foreach ($dep as $k => $v) {
        if ($v === NULL) continue;
        if ($k === 'required') continue;
        if ($k === 'recommendedMin' or $k === 'recommendedMax') continue;   // not sure where <compatible> goes yet
        if (is_array($v))
        {
          foreach ($v as $arrayVal) {
            $pkgNode->addTextNode($k, $arrayVal);
          }
        }
        else
        {
          $pkgNode->addTextNode($k, $v);
        }
      }
    }

    // create a "phprelease" tag
    $hasReleaseNode = false;
    foreach ($this->executables as $platform => $executables) {
      $hasReleaseNode = true;
      $phpReleaseNode = $xml->addChild('phprelease');
      if ($platform !== self::PLATFORM_ANY)
      {
        $installConditionNode = $phpReleaseNode->addChild('installconditions');
        $osNode = $installConditionNode->addChild('os');
        $osNode->addTextNode('name', $platform);
      }
      $fileListNode = $phpReleaseNode->addChild('filelist');
      foreach ($executables as $executable) {
        $installNode = $fileListNode->addChild('install');
        $installNode->addAttribute('as', $executable['as']);
        $installNode->addAttribute('name', $executable['name']);
      }
    }
    if (!$hasReleaseNode)
    {
      $xml->addChild('phprelease');
    }


    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;
    file_put_contents("{$this->options[self::OPT_BASEDIR]}/package.xml", $dom->saveXML());
  }

  /**
   * Fluent interface bootstrap static constructor.
   *
   * @param array Options hash to be passed to constructor.
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public static function create($options = array())
  {
    return new Pearfarm_PackageSpec($options);
  }
}

/**
 * @ignore
 * @package pearfarm
 */
abstract class Pearfarm_PackageSpecItem
{
  protected $nodeName;
  protected $requiredAttributes;
  protected $attributes;

  public function __construct($nodeName, $requiredAttributes)
  {
    $this->nodeName = $nodeName;
    $this->requiredAttributes = $requiredAttributes;
  }

  public function setAttribute($k, $v)
  {
    $this->attributes[$k] = $v;
    return $this;
  }

  public function setAttributes($attrs)
  {
    foreach ($attrs as $k => $v) {
      $this->setAttribute($k, $v);
    }
    return $this;
  }

  public function addXMLAsChild($parentNode)
  {
    //print_r($parentNode);
    $node = $parentNode->addChild($this->nodeName);
    foreach ($this->attributes as $k => $v) {
      if ($v === NULL and in_array($k, $this->requiredAttributes)) throw new Exception("Attribute {$k} is required for {get_class($this)}.");
      if ($v === NULL) continue;  // skip optional attributes
      $node->addAttribute($k, $v);
    }
    //print "adding $this->nodeName {$node['name']} to {$parentNode['name']} \n";
    return $node;
  }

  /**
   * Add generic attribute accessors/mutators.
   */
  public function __call($name, $value)
  {
    switch (substr($name, 0, 3)) {
      case 'set':
        $attrName = substr($name, 3);
        $attrName[0] = strtolower($attrName[0]);
        if (isset($this->attributes[$attrName]))
        {
          $this->setAttribute($attrName, $value[0]);
          return $this;
        }
        break;
      case 'get':
        $attrName = substr($name, 3);
        $attrName[0] = strtolower($attrName[0]);
        if (isset($this->attributes[$attrName]))
        {
          $this->setAttribute($value[0]);
          return $this->attributes[$attrName];
        }
        break;
    }
    throw new Exception("Function $name does not exist.");
  }
}

/**
 * A "directory" object within the pearfarm spec.
 *
 * Directories are created automatically.
 *
 * At this time you cannot add or manipulate directories via pearfarm.spec.
 *
 * @internal
 * @ignore
 * @package pearfarm
 */
class Pearfarm_PackageSpecDir extends Pearfarm_PackageSpecItem
{
  const BASEINSTALLDIR        = 'baseinstalldir';

  // relative path to dir
  private $dirPath;
  private $items; // all items contained in this dir

  public function __construct($dirPath, $options = array())
  {
    parent::__construct('dir', array('name'));

    // internal stuff
    $this->dirPath = $dirPath;

    // required attrs
    // The "root" dir . is called / in pear
    $baseDirName = basename($dirPath);
    if ($baseDirName === '.')
    {
      $baseDirName = '/';
    }
    $this->setAttribute('name', $baseDirName);

    // optional attrs
    $options = array_merge(array(
          self::BASEINSTALLDIR => NULL,
          ), $options);
    $this->setAttributes($options);
  }

  public function __toString()
  {
    return $this->dirPath;
  }

  public function addItem($item)
  {
    if (!($item instanceof Pearfarm_PackageSpecDir) and !($item instanceof Pearfarm_PackageSpecFile)) throw new Exception("Pearfarm_PackageSpecDir can only contain Pearfarm_PackageSpecDir and Pearfarm_PackageSpecFile objects.");
    $this->items[] = $item;
  }

  public function addXMLAsChild($parentNode)
  {
    //print "adding xml nodes for {$this->dirPath}\n";
    $node = parent::addXMLAsChild($parentNode);
    foreach ($this->items as $item) {
      //print "  [{$this->dirPath}] adding child {$item->nodeName} {$item} to {$node['name']} \n";
      $childNode = $item->addXMLAsChild($node);
    }
    return $node;
  }
}

/**
 * A "file" object within the pearfarm spec.
 *
 * Files are generally created for you automatically by the higher-level functions, but you can create them yourself if you like and then
 * add the file with {@link addFile()}.
 *
 * The more typical use case is to modify an existing file by calling {@link getFilePath() $spec->getFilePath()}.
 *
 * @package pearfarm
 */
class Pearfarm_PackageSpecFile extends Pearfarm_PackageSpecItem
{
  const BASEINSTALLDIR        = 'baseinstalldir';
  const MD5SUM                = 'md5sum';

  /**
   * @var string The relative (to the project root) path to the file.
   */
  private $filePath;
  /**
   * @var array All of the PEAR replace tasks to run on this file. See {@link addReplaceTask()}.
   * @ignore
   * @internal
   */
  protected $replaceTasks = array();

  /**
   * Create a Pearfarm_PackageSpecFile for the given file path.
   *
   * @param string The relative (to the project root) path of the file to add to the package.
   * @param string The role for the given file. One of the Pearfarm_PackageSpecFile::ROLE_* constants. Defaults to ROLE_PHP.
   * @param array Settings for optional attributes {@link Pearfarm_PackageSpecFile::BASEINSTALLDIR} and {@link Pearfarm_PackageSpecFile::MD5SUM}.
   * @return object Pearfarm_PackageSpecFile
   */
  public function __construct($filePath, $role = Pearfarm_PackageSpec::ROLE_PHP, $options = array())
  {
    parent::__construct('file', array('name', 'role'));

    // internal stuff
    $this->filePath = str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', '/', $filePath));

    // required attrs
    $this->setAttribute('name', basename($filePath));
    $this->setAttribute('role', $role);

    // optional attrs
    $options = array_merge(array(
          self::BASEINSTALLDIR => NULL,
          self::MD5SUM => NULL
          ), $options);
    $this->setAttributes($options);

    if (file_exists($filePath))
    {
      $this->setAttribute(self::MD5SUM, md5_file($filePath));
    }
  }

  /**
   * @ignore
   */
  public function __toString()
  {
    return $this->filePath;
  }

  /**
   * Add a <tasks:replace>, see {@link http://pear.php.net/manual/en/guide.developers.package2.tasks.php}
   *
   * @param string one of package-info, pear-config
   * @param string from string, traditionally @search@
   * @param string to string, the "abstract" variable from "type" for the replacement.
   * @return object Pearfarm_PackageSpec For fluent interface.
   */
  public function addReplaceTask($type, $from, $to)
  {
    $this->replaceTasks[] = array('type' => $type, 'from' => $from, 'to' => $to);

    return $this;
  }

  /**
   * Get the file path for this file.
   *
   * @return string
   */
  public function getFilePath()
  {
    return $this->filePath;
  }

  /**
   * @ignore
   * @internal
   */
  public function addXMLAsChild($parentNode)
  {
    $node = parent::addXMLAsChild($parentNode);
    foreach ($this->replaceTasks as $task) {
      $taskNode = $node->addChild('replace', NULL, 'http://pear.php.net/dtd/tasks-1.0');
      $taskNode->addAttribute('type', $task['type']);
      $taskNode->addAttribute('from', $task['from']);
      $taskNode->addAttribute('to', $task['to']);
    }
    return $node;
  }
}

/**
 * @ignore
 * @package pearfarm
 */
class SuperSimpleXMLElement extends SimpleXMLElement
{
  public function addTextNode($entityName, $text)
  {
    $newNode = $this->addChild($entityName);
    $newNode[0] = $text;
    return $newNode;
  }
}

/**
 * Thanks http://shiflett.org/blog/2007/dec/php-advent-calendar-day-7
 *
 * @ignore
 * @package pearfarm
 */
class RecursiveFileIterator extends RecursiveIteratorIterator
{
  /**
   * Takes a path to a directory, checks it, and then recurses into it.
   * @param $path directory to iterate
   */
  public function __construct($path)
  {
    // Use realpath() and make sure it exists; this is probably overkill, but I'm anal.
    $path = realpath($path);

    if (!file_exists($path)) {
      throw new Exception("Path $path could not be found.");
    } elseif (!is_dir($path)) {
      throw new Exception("Path $path is not a directory.");
    }

    // Use RecursiveDirectoryIterator() to drill down into subdirectories.
    parent::__construct(new RecursiveDirectoryIterator($path));
  }
}

/**
 * @ignore
 * @package pearfarm
 */
class RecursiveFileRegexFilterIterator extends FilterIterator
{
  /**
   * acceptable extensions - array of strings
   */
  protected $regex = NULL;
  protected $pathPrefix = NULL;
  protected $pathPrefixLen = NULL;

  /**
   * Takes a path and shoves it into our earlier class.
   * Turns $ext into an array.
   * @param $path directory to iterate
   * @param $ext comma delimited list of acceptable extensions
   */
  public function __construct($path, $regex)
  {
    parent::__construct(new RecursiveFileIterator($path));
    $this->regex = $regex;

    // normalize path
    $path = rtrim($path, '/\\');
    $this->pathPrefix = $path;
    $this->pathPrefixLen = strlen($this->pathPrefix);
  }

  /**
   * Makes sure that the path matches the regex.
   */
  public function accept()
  {
    $item = $this->getInnerIterator();
    $realPathToFile = $item->getRealPath();

    // there was a test for dirs in the sample code, but I don't think it can ever happen...
    if (is_dir($item->getRealPath())) {
      return false;
    }

    // assert this for a while so we can make sure it doesn't happen
    if (substr($realPathToFile, 0, $this->pathPrefixLen) !== $this->pathPrefix) throw new Exception("Weird thing happened: {$realPathToFile} not inside of {$this->pathPrefix}.");

    $normalizedFilePath = substr($realPathToFile, $this->pathPrefixLen + 1);

//    print "Testing preg_match('{$this->regex}', '{$normalizedFilePath}')\n";
    return preg_match($this->regex, $normalizedFilePath);
  }
}
