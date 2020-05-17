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
class EcsUpdateServiceCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('ecs:update-service')
            ->setAliases(['update'])
            ->addArgument('definition', InputArgument::REQUIRED, 'The registered task definition name:revision to update service with.')
            ->setDescription('Update a service using a registered task definition.')
            ->setHelp('Use this command to update a service in ECS.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initConfig();
        $service = $this->config->ecs->service->toScalar();
        $cluster = $this->config->ecs->cluster->toScalar();
        $taskDefinitionRevision = $input->getArgument('definition');
        $this->logger->section("Updating service {$service} in cluster {$cluster} with task definition {$taskDefinitionRevision}");
        $this->awsHelper->ecs->updateService([
            'service' => $service,
            'cluster' => $cluster,
            'taskDefinition' => $taskDefinitionRevision,
        ]);
        $this->logger->success("Updated {$service} service with {$taskDefinitionRevision} task definition!");
    }
}
