<?php

$spec = PEARFarm_Specification::newSpec()
            ->setName('iphp')
            ->setChannel('iphp.pearfarm.net')
            ->setSummary('PHP Shell')
            ->setDescription('An interactive PHP Shell (or Console, or REPL).')
            ->setReleaseVersion('1.0.0')
            ->setReleaseStability('stable')
            ->setLicense('bsd')
            ->addGitFiles();
