<?php

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__), Pearfarm_PackageSpec::OPT_DEBUG => true ))
            ->setName('pearfarm')
            ->setChannel('apinstein.dev.pearfarm.org')
            ->setSummary('Build and distribute PEAR packages easily.')
            ->setDescription('Pearfarm makes it easy to create PEAR packages for your projects and host them on a channel server.')
            ->setNotes('See http://github.com/fgrehm/pearfarm for changelog, docs, etc.')
            ->setReleaseVersion('0.1.1')
            ->setReleaseStability('alpha')
            ->setApiVersion('0.1.0')
            ->setApiStability('alpha')
            ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
            ->addMaintainer('lead', 'Alan Pinstein', 'apinstein', 'apinstein@mac.com')
            ->addMaintainer('lead', 'Fábio Rehm', 'fgrehm', 'fgrehm@gmail.com')
            ->addMaintainer('lead', 'Jonathan Leibiusky', 'xetorthio', 'ionathan@gmail.com')
            ->addMaintainer('lead', 'Scott Davis', 'jetviper21', 'jetviper21@gmail.com ')
            ->addFilesRegex(array('/src/', '/test/', '/pearfarm$/', '/^README.markdown$/'))
            ->addExcludeFilesRegex(array('/\.git/'))
            ->addExcludeFiles(array('.gitignore', 'pearfarm.spec'))
            ->addExecutable('pearfarm')
            ;
$spec->getFile('README.markdown')->setRole('doc');
