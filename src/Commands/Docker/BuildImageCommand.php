<?php

namespace DEVUtil\Commands\Docker;

use DEVUtil\Enums;
use DEVUtil\Commands\BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class BuildImageCommand
 *
 * @package devutil\commands\Docker
 */
class BuildImageCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('docker:build-image')
            ->setAliases(['build'])
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of image (optionally a URL). Defaults to ecs->image in devutil.json.')
            ->addArgument('tag', InputArgument::OPTIONAL, 'A tag for this image. Defaults to ecs->version in devutil.json.')
            ->addOption('project', 'p', InputArgument::OPTIONAL, 'Project to build', 'base')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Build debug image. Use only with "base" project!')
            ->setDescription('Build an image based on the contents of the "build" directory in the project root');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initConfig();
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
        $name = $input->getArgument('name') ?? $this->config->ecs->image->toScalar();
        $tag = $input->getArgument('tag') ?? $this->config->ecs->version->toScalar();
        $project = $input->getOption('project');
        $fwDockerfile = $input->getOption('debug') ? 'Debug' : 'Dockerfile';
        $buildCommand = 'docker build --no-cache';
        $buildContextDir = Enums::Docker()->getSettings('DockerFilesPath') . DS . $project;
        $dockerfilePath = Enums::Docker()->getSettings('DockerFilesPath') . DS . $project . DS . $fwDockerfile;
        $buildCommand .= " -t {$name}:{$tag}";
        $buildCommand .= " -f {$dockerfilePath} {$buildContextDir}";
        $process = (new Process($buildCommand))->setTimeout(null);
        $this->logger->section("Building Image");
        $this->logger->progressStart();
        try {
            $process->mustRun(function () {
                $this->logger->progressAdvance();
            });
        } catch (ProcessFailedException $e) {
            $this->logger->progressFinish();
            preg_match("/Error\sOutput:(\s|.)*/", $e->getMessage(), $matches);
            $this->logger->error("Build process failed!");
            $this->logger->block($matches);
            return;
        }
        $this->logger->progressFinish();
        $this->logger->success("Built image: {$name}:{$tag}");
    }
}
