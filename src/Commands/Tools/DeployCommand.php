<?php

namespace DEVUtil\Commands\Tools;

use DEVUtil\E_APP;
use Exception;
use DEVUtil\Commands\BaseCommand;
use Aws\Exception\AwsException;
use DEVUtil\Helpers\BaseArrayHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EcrCreateRepoCommand
 *
 * @package devutil\commands\Aws
 */
class DeployCommand extends BaseCommand
{

    private $newImageVersion = '0.0.1';

    protected function configure()
    {
        $this
            ->setName('tools:deploy')
            ->setAliases(['deploy'])
            ->addArgument('config', InputOption::VALUE_REQUIRED, 'The path to the devutil.json config file')
            ->addOption('deploy', 'd', InputOption::VALUE_NONE, 'Use this flag to deploy current task definition. Use this after running command in dry mode.')
            ->addOption('project', 'p', InputOption::VALUE_OPTIONAL, 'Project to deploy', 'base')
            ->setDescription('Use a config file to deploy a new version.')
            ->setHelp('BETA: A deployment tool');
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
        $this->logger->section("Fetching info on currently deployed task from ECS");
        try {
            $service = $this->awsHelper->ecs->describeServices([
                'cluster' => $this->config->ecs->cluster->toScalar(),
                'services' => [$this->config->ecs->service->toScalar()]
            ])->get('services');
            $currentTaskDefinition = BaseArrayHelper::getValue($service, '0.taskDefinition');
            $currentTaskDescription = $this->awsHelper->ecs->describeTaskDefinition(['taskDefinition' => $currentTaskDefinition])->toArray();
            $currentImage = BaseArrayHelper::getValue($currentTaskDescription, 'taskDefinition.containerDefinitions.0.image');
            preg_match('/(\d)*\.(\d)*\.(\d)*$/', $currentImage, $currentVersion);
            $this->newImageVersion = E_APP::bumpVersion($currentVersion);
        } catch (AwsException $e) {
            $this->logger->error($e->getAwsErrorMessage());
            exit;
        } catch (Exception $e) {
            $this->logger->error("Failed to fetch info from ECS!");
            $this->logger->block($e->getMessage());
            exit;
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('deploy')) {
            $this->config->ecs->version = $this->newImageVersion;
            $this->config->save();
            $newNameTag = $this->config->ecs->image->toScalar() . ':' . $this->newImageVersion;
            $buildCommand = $this->getApplication()->find('docker:build-image');
            $exitCode = $buildCommand->run(new ArrayInput(['command' => 'docker:build-image', 'project' => $input->getOption('project')]), $output);
            if ($exitCode) {
                throw new Exception("Error: Build command failed.");
            }
            $pushCommand = $this->getApplication()->find('docker:push-image');
            $exitCode = $pushCommand->run(new ArrayInput(['command' => 'docker:push-image', 'nameTag' => $newNameTag]), $output);
            if ($exitCode) {
                throw new Exception("Error: Push command failed.");
            }
        }
        if (!$input->getOption('deploy') && !$this->logger->confirm('Would like to deploy this new image now?')) {
            $this->logger->note([
                'Finished building and pushing image but haven\'t deployed yet.',
                'When you\'re ready, run "devutil deploy" again with the "-d" switch.'
            ]);
            exit;
        }

        $this->logger->section("Deploying version {$this->newImageVersion} for {$this->config->gitlab->project_name->toScalar()}");
        $this->config->ecs->task_definition->containerDefinitions[0]->image = $newNameTag;

        $registerTaskCommand = $this->getApplication()->find('ecs:register-task');
        $registerTaskCommand->run(new ArrayInput(['command' => 'ecs:register-task']), $output);

        $family = $this->config->ecs->task_definition->family->toScalar();
        $taskFamilies = $this->awsHelper->ecs->listTaskDefinitions(['familyPrefix' => $family])->get('taskDefinitionArns');
        preg_match('/\d*$/', $taskFamilies[count($taskFamilies) - 1], $task);

        $newTaskRevision = $family . ':' . ++$task[0];
        $this->logger->section("Updating service {$this->config->ecs->service->toScalar()} to {$newTaskRevision}");
        $updateCommand = $this->getApplication()->find('ecs:update-service');
        $updateCommand->run(new ArrayInput(['command' => 'ecs:update-service', 'definition' => $newTaskRevision]), $output);

        $this->logger->success("Finished deploying {$input->getOption('project')}!");
    }
}
