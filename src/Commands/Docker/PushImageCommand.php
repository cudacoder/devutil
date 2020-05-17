<?php

namespace DEVUtil\Commands\Docker;

use DEVUtil\Commands\BaseCommand;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class EcrUpdateImageCommand
 * @package devutil\commands\Aws
 */
class PushImageCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName('docker:push-image')
            ->setAliases(['push'])
            ->addArgument('nameTag', InputArgument::REQUIRED, 'Full URL with tag of the image to push')
            ->setDescription('Pushes a local docker image.')
            ->setHelp('Specify the name of the local image you wish to push. Leave empty to choose interactively');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nameTag = $input->getArgument('nameTag');
        $dockerPushProc = new Process("docker push {$nameTag}");
        try {
            $this->logger->section("Pushing: {$nameTag}");
            $this->logger->progressStart();
            $dockerPushProc->setTimeout(null);
            $dockerPushProc->mustRun(function ($t, $b) {
                $this->logger->progressAdvance();
            });
        } catch (ProcessFailedException $e) {
            $this->logger->newLine(2);
            preg_match("/Error\sOutput:(\s|.)*/", $e->getMessage(), $matches);
            $this->logger->error("Push failed:");
            $this->logger->block($matches[0]);
            return;
        }
        $this->logger->progressFinish();
        $this->logger->success("Pushed image {$nameTag}");
    }
}
