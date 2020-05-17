<?php

namespace DEVUtil\Commands\Aws\Ecs;

use DEVUtil\Commands\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EcrCreateRepoCommand
 * @package devutil\commands\Aws
 */
class EcsCreateServiceCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('ecs:create-service')
            ->setAliases(['service'])
            ->setDescription('Create a service using a registered task definition.')
            ->setHelp('Use this command to create a new service in ECS.');
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
        $service = $this->config->ecs->service->toScalar();
        $cluster = $this->config->ecs->cluster->toScalar();
        $family = $this->config->ecs->task_definition->family->toScalar();
        $targetGroup = $this->config->ecs->exists('target_group_arn');
        if (!$targetGroup && $this->logger->confirm("No Target Group found.\nWould you like to create one now?")) {
            $name = $this->logger->ask('Please specify Target Group name: ');
            $targetGroupConf = [
                'HealthCheckIntervalSeconds' => 30,
                'HealthCheckTimeoutSeconds' => 5,
                'Matcher' => ['HttpCode' => '200,302'],
                'Name' => "{$name}",
                'Port' => 80,
                'Protocol' => 'HTTP',
                'TargetType' => 'ip',
                'VpcId' => '#DEFAULT_VPC_ID#',
            ];
            $this->logger->text(json_encode($targetGroupConf, JSON_PRETTY_PRINT));
            if ($this->logger->confirm("Create this Target Group?")) {
                $result = $this->awsHelper->elb->createTargetGroup($targetGroupConf)->get('TargetGroups');
                if (!empty($result)) {
                    $this->config->ecs->target_group_arn = $result[0]['TargetGroupArn'];
                    $this->config->save();
                }
            }
        }
        $this->logger->section("Creating service {$service} in cluster {$cluster}");
        $serviceConfig = [
            'cluster' => $cluster,
            'serviceName' => $service,
            'desiredCount' => 1,
            'launchType' => 'FARGATE',
            'taskDefinition' => $family,
            'loadBalancers' => [
                [
                    'containerPort' => 80,
                    'targetGroupArn' => $this->config->ecs->target_group_arn->toScalar(),
                    'containerName' => $this->config->ecs->task_definition->containerDefinitions[0]->name->toScalar(),
                ],
            ],
            'networkConfiguration' => [
                'awsvpcConfiguration' => [
                    'assignPublicIp' => 'DISABLED',
                    'securityGroups' => [],
                    'subnets' => [],
                ],
            ],
        ];
        $this->logger->text(json_encode($serviceConfig, JSON_PRETTY_PRINT));
        if ($this->logger->confirm('Create this service?')) {
            $this->awsHelper->ecs->createService($serviceConfig);
            $this->logger->success("Created {$service} service with {$family} task definition!");
        }
    }
}
