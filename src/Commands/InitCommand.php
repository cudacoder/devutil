<?php

namespace DEVUtil\Commands;

use DEVUtil\Enums;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{

    protected function configure()
    {
        $this->setName('init')
            ->setAliases(['i'])
            ->setDescription('Initialize DEVUtil interactively');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $awsConfigFile = Enums::AWS()->getSettings('HomePath') . DS . '.aws' . DS . 'config';
        $awsCredsFile = Enums::AWS()->getSettings('HomePath') . DS . '.aws' . DS . 'credentials';
        if (!$fs->exists([$awsCredsFile, $awsConfigFile])) {
            $fs->touch([$awsCredsFile, $awsConfigFile]);
        }
        $awsConfig = parse_ini_file($awsConfigFile, true);
        $awsCreds = parse_ini_file($awsCredsFile, true);
        if (array_key_exists('default', $awsConfig) || array_key_exists('default', $awsCreds)) {
            $logger = new SymfonyStyle($input, $output);
            $logger->text([
                'CMDBOI found existing AWS config files in $HOME/.aws folder with "default" profile already defined.',
                'If you would like to add a new profile or overwrite the "default" one,',
                'you can use the "devutil tools:add-profile {name-of-profile}" command to do so.',
                'After that, use the "devutil generate:conf" command to interactively create a devutil.json file,',
                'and in this file you can define the profile you want to use with the "profile" property.'
            ]);
            $logger->newLine();
            return;
        }
        $addProfileCmd = $this->getApplication()->find('tools:add-profile');
        $addProfileCmd->run(new ArrayInput(['command' => 'tools:add-profile', 'profile' => 'default']), $output);
    }
}
