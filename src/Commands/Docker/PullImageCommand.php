<?php

namespace DEVUtil\Commands\Docker;

use DEVUtil\Helpers\AwsHelper;
use DEVUtil\Commands\BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PullImageCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('docker:pull-image')
            ->setAliases(['pull'])
            ->setDescription('Pull images from ECR')
            ->addOption('interactive', 'i', InputOption::VALUE_NONE, 'interactive option')
            ->addOption('profile', 'p', InputOption::VALUE_OPTIONAL, 'This is the login profile', 'default')
            ->addOption('tag', 't', InputOption::VALUE_OPTIONAL, 'image tag', 'latest')
            ->setHelp('Run the command without any arguments to choose an image interactively');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (is_null($input->getOption('interactive'))) {
            $this->initConfig();
        } else {
            $this->initAwsHelper($input->getOption('profile'));
        }
        $chosenRepo = $this->questionHelper->askForRepo($input, $output, $this->awsHelper);
        $process = new Process('docker pull ' . $chosenRepo['url'] . ":{$input->getOption('tag')}");
        $this->logger->progressStart();
        try {
            $process->setTimeout(null);
            $process->mustRun(function () {
                $this->logger->progressAdvance();
            });
        } catch (ProcessFailedException $e) {
            $this->logger->progressFinish();
            preg_match("/Error\sOutput:(\s|.)*/", $e->getMessage(), $matches);
            $this->logger->error("Pull process failed:");
            $this->logger->block($matches[0]);
            return;
        }
        $this->logger->progressFinish();
        $this->logger->success("Pulled image {$chosenRepo['url']}");
    }
}
