<?php

namespace DEVUtil\Commands\Aws\Ecr;

use Exception;
use DEVUtil\Commands\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EcrCreateRepoCommand
 * @package DEVUtil\Commands\Aws
 */
class EcrCreateRepoCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('ecr:create-repo')
            ->setAliases(['repo'])
            ->setDescription('Creates a new repository')
            ->addArgument('name', InputArgument::REQUIRED, 'A name for the new image repository, could contain namespace as well with "name/repo"')
            ->addOption('profile', 'p', InputArgument::OPTIONAL, 'Profile to use', 'default')
            ->setHelp('Creates a new repository on ECR with the supplied name, namespaces supported.');
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
        $this->initConfig($input->getOption('profile'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $this->logger->writeln("Creating new ECR repository: {$name}");
        try {
            $response = $this->awsHelper->ecr->createRepository(['repositoryName' => $name])->toArray();
        } catch (Exception $e) {
            $this->logger->error("Failed to create repository!");
            $this->logger->block($e->getMessage());
            return;
        }
        $newRepoName = $response['repository']['repositoryName'];
        $newRepoUri = $response['repository']['repositoryUri'];
        $this->logger->success("New Repo created!");
        $this->logger->listing(["Name: {$newRepoName}", "URI: {$newRepoUri}"]);
    }
}
