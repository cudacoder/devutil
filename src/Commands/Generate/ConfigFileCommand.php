<?php

namespace DEVUtil\Commands\Generate;

use DEVUtil\Dto\ContainerDefinitionDto;
use DEVUtil\Dto\EcsDto;
use DEVUtil\Dto\TaskDefinitionDto;
use DEVUtil\Enums;
use DEVUtil\Config;
use DEVUtil\Helpers\AwsHelper;
use DEVUtil\Commands\BaseCommand;
use DEVUtil\Helpers\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ConfigFileCommand extends BaseCommand
{

    private $yii2;
    private $image;
    private $family;
    private $gitlab;
    private $cluster;
    private $service;
    private $fargateFlag;

    protected function configure()
    {
        $this
            ->setName('generate:conf')
            ->setAliases(['gc'])
            ->addArgument('profile', InputArgument::OPTIONAL, 'AWS profile name, defaults to "default"', 'default')
            ->setDescription('Generate devutil.json configuration file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = new QuestionHelper();
        $awsConfigFile = Enums::AWS()->getSettings('HomePath') . DS . '.aws' . DS . 'config';
        $awsConfig = parse_ini_file($awsConfigFile, true);
        $profile = $input->getArgument('profile');
        $awsHelper = new AwsHelper(['profile' => $profile, 'region' => $awsConfig[$profile]['region']]);
        $this->image = $questionHelper->askForRepo($input, $output, $awsHelper)['url'];
        $this->cluster = $questionHelper->askForCluster($input, $output, $awsHelper);
        $this->service = $questionHelper->askForService($input, $output, $awsHelper, $this->cluster);
        $this->family = $questionHelper->askForTaskFamily($input, $output, $awsHelper);
        $this->gitlab = $questionHelper->askForGitProject($input, $output);
        $this->yii2 = $questionHelper->ask($input, $output, new ConfirmationQuestion('Is it a Yii2 project? '));
        $this->fargateFlag = $questionHelper->ask($input, $output, new ConfirmationQuestion('Are you deploying to Fargate? '));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profile = $input->getArgument('profile');
        $config = new Config();
        $config->yii2 = $this->yii2;
        $config->profile = $profile;
        $config->gitlab = $this->gitlab;
        $config->ecs = new EcsDto();
        $config->ecs->image = $this->image;
        $config->ecs->cluster = $this->cluster;
        $config->ecs->service = $this->service;
        $config->ecs->task_definition = new TaskDefinitionDto();
        $config->ecs->task_definition->family = $this->family;
        if ($this->fargateFlag) {
            $config->ecs->task_definition->networkMode = 'awsvpc';
            $config->ecs->task_definition->requiresCompatibilities = ['FARGATE'];
        }
        $config->ecs->task_definition->containerDefinitions = [new ContainerDefinitionDto()];
        $config->save();
    }
}
