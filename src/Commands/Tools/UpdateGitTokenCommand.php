<?php


namespace DEVUtil\Commands\Tools;

use DEVUtil\Enums;
use DEVUtil\Commands\BaseCommand;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateGitTokenCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('tools:git-token')
            ->setAliases(['token'])
            ->setDescription('Use this tool to update the .devutil-git-token file with your correct Gitlab token')
            ->setHelp("Interactively update the Gitlab token to fetch your projects list.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tokenQuestion = (new Question('Please enter your GitHub Personal Access Token (secret): '))->setHidden(true);
        $token = $this->questionHelper->ask($input, $output, $tokenQuestion);
        $this->fs->dumpFile(Enums::Git()->getSettings('TokenFile'), $token);
    }
}
