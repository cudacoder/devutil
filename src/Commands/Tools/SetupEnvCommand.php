<?php


namespace DEVUtil\Commands\Tools;

use DEVUtil\Enums;
use DEVUtil\Commands\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupEnvCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('tools:setup-env')
            ->setAliases(['dev'])
            ->setDescription('Generates a local development environment for PHP projects.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fs->dumpFile(getcwd() . DS . 'docker-compose.yml', Enums::Dev()->getDockerComposeTemplate());
        $this->logger->success('Created example docker-compose.yml file in current directory!');
    }
}
