<?php

namespace DEVUtil\Commands\Aws\Ecs;

use Exception;
use DEVUtil\Commands\BaseCommand;
use DEVUtil\Helpers\BaseArrayHelper;
use Dto\Exceptions\InvalidDataTypeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EcrCreateRepoCommand
 * @package devutil\commands\Aws
 */
class EcsRegisterTaskCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('ecs:register-task')
            ->setAliases(['register'])
            ->setDescription('Registers a new task definition')
            ->setHelp('Register a new Task Definition using data from the "task_definition" property in the devutil.json file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws Exception
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
     * @throws InvalidDataTypeException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->section("Registering new task definition for {$this->config->ecs->task_definition->family->toScalar()}");
        $this->config->ecs->task_definition->containerDefinitions[0]->image = $this->config->ecs->image . ':' . $this->config->ecs->version;
        $responseArray = $this->awsHelper->ecs->registerTaskDefinition($this->config->ecs->task_definition->toArray())->toArray();
        $responseFamily = BaseArrayHelper::getValue($responseArray, 'taskDefinition.family');
        $responseRevision = BaseArrayHelper::getValue($responseArray, 'taskDefinition.revision');
        $this->logger->success("Registered new {$responseFamily} task definition - Revision {$responseRevision}");
    }
}
