<?php

$spec = PackageSpec::create(array(PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
            ->setName('pearfarm')
            ->setChannel('pear.nimblize.com')
            ->setSummary('Build and distribute PEAR packages easily.')
            ->setDescription('Pearfarm makes it easy to create PEAR packages for your projects and host them on a channel server.')
            ->setNotes('See http://github.com/fgrehm/pearfarm for changelog, docs, etc.')
            ->setReleaseVersion('0.0.3')
            ->setReleaseStability('alpha')
            ->setApiVersion('0.0.3')
            ->setApiStability('alpha')
            ->setLicense(PackageSpec::LICENSE_MIT)
            ->addMaintainer('lead', 'Alan Pinstein', 'apinstein', 'apinstein@mac.com')
            ->addMaintainer('lead', 'Fábio Rehm', 'fgrehm', 'fgrehm@gmail.com')
            ->addGitFiles()
            ->addExcludeFiles('.gitignore')
            ->addExecutable('pearfarm')
            ;
