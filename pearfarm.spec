<?php

$spec = PackageSpec::create(array(PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
            ->setName('vfs://root/')
            ->setChannel('TODO: Release channel here')
            ->setSummary('TODO: One-line summary of your PEAR package')
            ->setDescription('TODO: Longer description of your PEAR package')
            ->setReleaseVersion('0.0.1')
            ->setReleaseStability('alpha')
            ->setApiVersion('0.0.1')
            ->setApiStability('alpha')
            ->setLicense(PackageSpec::LICENSE_MIT)
            ->setNotes('Initial release.')
            ->addMaintainer('lead', 'TODO: Your name here', 'TODO: Your username here', 'TODO: Your email here')
            ->addGitFiles()
            ->addExecutable('vfs://root/')
            ;