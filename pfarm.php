<?php
require_once('PEAR/PackageFileManager2.php');
PEAR::setErrorHandling(PEAR_ERROR_DIE);

//TODO: We should make this nicer
switch($argv[1]) {
  case "plant": {
    if(!isset($argv[2])) {
      echo "You must specify a package name.\n";
      //TODO: define exit codes
      exit(-1);
    }
    //TODO: check if there is already a directory with that name
    //TODO: what should we do if we don't have write permissions?
    //TODO: validate package name
    $packageName = $argv[2];
    mkdir($packageName);
    mkdir($packageName . DIRECTORY_SEPARATOR . 'src');
    mkdir($packageName . DIRECTORY_SEPARATOR . 'data');
    mkdir($packageName . DIRECTORY_SEPARATOR . 'tests');
    mkdir($packageName . DIRECTORY_SEPARATOR . 'doc');
    mkdir($packageName . DIRECTORY_SEPARATOR . 'www');
    mkdir($packageName . DIRECTORY_SEPARATOR . 'examples');
    
    // create default class
    // TODO: add doc block to class
    file_put_contents($packageName . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . ucfirst($packageName) . '.php', "<?php\nclass " . ucfirst($packageName) . " {\n\n\n}");
    
    //TODO: generate package spesification file
    
  } break;
  case "collect": {
    //TODO: do it! ;)
  } break;
  case "try": {
    //TODO: do it! ;)
  } break;
  case "deliver": {
    //TODO: do it! ;)
  } break;
  default: {
    echo <<<EOT
usage: pfarm COMMAND [ARGS]

The pfarm commands are:
    plant       creates the package
    collect     builds the package
    try         installs the package for testing purposes
    deliver     sends the package to pearfarm.org


EOT;
  }
}

/*

THIS IS AN EXAMPLE OF HOW TO GENERATE THE XML PACKAGE FILE
$pfm = new PEAR_PackageFileManager2();
 
//TODO: Define defaults for most of these parameters and decide which one we will require from the user.
$e = $pfm->setOptions(
     array(
       'baseinstalldir' => '',
        'packagedirectory' => '.',
        //TODO: find a good way to add ignore files for .svn or .git, etc.
       'filelistgenerator' => 'file', //this should be file, because other options are svn or cvs, but I think it doesn't really make sense
       'ignore' => array(),
      'installexceptions' => array(),
      'dir_roles' => array(),
      'exceptions' => array()
     )
   ); // same for the license
$pfm->setPackage('MyPackage');
$pfm->setSummary('this is my package');
$pfm->setDescription('this is my package description');

//TODO: By default we should put pearfarm channel here
$pfm->setChannel('pear.php.net');

//what's api version?????
$pfm->setAPIVersion('1.0.0');
$pfm->setReleaseVersion('1.2.1');
$pfm->setReleaseStability('stable');

//again api???
$pfm->setAPIStability('stable');
$pfm->setNotes("We've implemented many new and exciting features");

//should we care about this?
$pfm->setPackageType('php'); // this is a PEAR-style php script package
$pfm->setOSInstallCondition('windows');
$pfm->setPhpDep('4.2.0');
$pfm->setPearinstallerDep('1.4.0a12');
$pfm->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pfm->setLicense('PHP License', 'http://www.php.net/license');
$pfm->generateContents(); // create the <contents> tag
$pfm->debugPackageFile(); //show the xml
$pfm->writePackageFile(); //write the xml
//*/