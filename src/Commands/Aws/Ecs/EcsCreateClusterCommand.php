<?php

namespace DEVUtil\Commands\Aws\Ecs;

use DEVUtil\Commands\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EcrCreateRepoCommand
 * @package devutil\commands\Aws
 */
class EcsCreateClusterCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('ecs:create-cluster')
            ->setAliases(['cluster'])
            ->addArgument('name', InputArgument::OPTIONAL, 'Add a name for the new cluster. Defualts to ecs->cluster in devutil.json.')
            ->setDescription('Create a cluster.')
            ->setHelp('Use this command to create a new cluster in ECS.');
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
        $cluster = $input->getArgument('name') ?? $this->config->ecs->cluster->toScalar();
        $this->awsHelper->ecs->createCluster(['clusterName' => $cluster]);
    }
}
