<?php

$spec = PEARFarm_Specification::newSpec(array(PEARFarm_Specification::OPT_BASEDIR => dirname(__FILE__)))
            ->setName('pearfarm')
            ->setChannel('pear.nimblize.com')
            ->setSummary('Build and distribute PEAR packages easily.')
            ->setDescription('PEARFarm makes it easy to create PEAR packages for your projects and host them on a channel server.')
            ->setReleaseVersion('0.0.1')
            ->setReleaseStability('alpha')
            ->setApiVersion('0.0.1')
            ->setApiStability('alpha')
            ->setLicense(PEARFarm_Specification::LICENSE_MIT)
            ->setNotes('Initial release.')
            ->addMaintainer('lead', 'Alan Pinstein', 'apinstein', 'apinstein@mac.com')
            ->addGitFiles()
            ->addExecutable('pearfarm')
            ;
