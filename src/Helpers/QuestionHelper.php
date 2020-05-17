<?php

namespace DEVUtil\Helpers;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Process;

class QuestionHelper extends \Symfony\Component\Console\Helper\QuestionHelper
{

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return string
     * @throws Exception
     */
    public function askForLocalImage(InputInterface $input, OutputInterface $output)
    {
        $images = new Process('docker images --format "{{.Repository}}:{{.Tag}}"');
        $images->run();
        $imagesArray = array_filter(explode("\n", $images->getOutput()));
        if (!$imagesArray) {
            throw new Exception('No images were found for pushing, aborting.');
        }
        $question = new ChoiceQuestion("\nSelect the Image:", $imagesArray);
        $question->setErrorMessage('Invalid Choice');
        return $this->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return array
     * @throws Exception
     */
    public function askForGitProject(InputInterface $input, OutputInterface $output)
    {
        $gitHelper = new GitHelper();
        $gitHelper->authenticate($gitHelper->getToken($input, $output));
        $gitProjects = $gitHelper->getProjects(['membership' => true]);
        $repositoriesPaths = BaseArrayHelper::getColumn($gitProjects, 'path_with_namespace');
        $repositoriesIds = BaseArrayHelper::getColumn($gitProjects, 'id');
        $repoArrayFlat = array_combine($repositoriesPaths, $repositoriesIds);

        $projectChoice = new ChoiceQuestion("\nSelect the project you want to update:", $repositoriesPaths);
        $projectPath = $this->ask($input, $output, $projectChoice);
        return ['project_id' => $repoArrayFlat[$projectPath], 'project_name' => $projectPath];
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AwsHelper $awsHelper
     * @param string $cluster
     *
     * @return string
     * @throws Exception
     */
    public function askForService(InputInterface $input, OutputInterface $output, AwsHelper $awsHelper, string $cluster)
    {
        try {
            $servicesArray = $awsHelper->ecs->listServices(['cluster' => $cluster])->get('serviceArns');
        } catch (Exception $e) {
            throw $e;
        }
        $servicesArray = array_map(function ($arn) {
            return explode('/', $arn)[1];
        }, $servicesArray);
        $question = new ChoiceQuestion("\nSelect a service:", $servicesArray);
        return $this->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AwsHelper $awsHelper
     *
     * @return string
     * @throws Exception
     */
    public function askForCluster(InputInterface $input, OutputInterface $output, AwsHelper $awsHelper)
    {
        try {
            $clusterArray = $awsHelper->ecs->listClusters()->get('clusterArns');
        } catch (Exception $e) {
            throw $e;
        }
        $clusterNames = array_map(function ($cluster) {
            return explode('/', $cluster)[1];
        }, $clusterArray);
        $question = new ChoiceQuestion("\nSelect a cluster:", $clusterNames);
        return $this->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AwsHelper $awsHelper
     *
     * @return array
     * @throws Exception
     */
    public function askForElb(InputInterface $input, OutputInterface $output, AwsHelper $awsHelper)
    {
        try {
            $elbResponse = $awsHelper->elb->describeLoadBalancers()->get("LoadBalancers");
            $question = new ChoiceQuestion("\nSelect a Load Balancer:", BaseArrayHelper::getColumn($elbResponse, 'LoadBalancerArn'));
            $chosenElbArn = $this->ask($input, $output, $question);
            $targetGroupsResponse = $awsHelper->elb->describeTargetGroups(['LoadBalancerArn' => $chosenElbArn])->get('TargetGroups');
        } catch (Exception $e) {
            throw $e;
        }
        return [
            'containerName' => 'app',
            'containerPort' => 80,
            'targetGroupArn' => BaseArrayHelper::getValue($targetGroupsResponse, '0.TargetGroupArn')
        ];
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AwsHelper $awsHelper
     *
     * @return string
     * @throws Exception
     */
    public function askForTaskFamily(InputInterface $input, OutputInterface $output, AwsHelper $awsHelper)
    {
        try {
            $taskFamilies = $awsHelper->ecs->listTaskDefinitionFamilies()->get('families');
            $question = new ChoiceQuestion("\nSelect a task family:", $taskFamilies);
            return $this->ask($input, $output, $question);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AwsHelper $awsHelper
     *
     * @return string
     * @throws Exception
     */
    public function askForTaskDefinition(InputInterface $input, OutputInterface $output, AwsHelper $awsHelper)
    {
        try {
            $taskFamilies = $awsHelper->ecs->listTaskDefinitionFamilies()->get('families');
            $question = new ChoiceQuestion("\nSelect a task family:", $taskFamilies);
            $family = $this->ask($input, $output, $question);
            $taskDefinitions = $awsHelper->ecs->listTaskDefinitions(['familyPrefix' => $family])->get('taskDefinitionArns');
        } catch (Exception $e) {
            throw $e;
        }
        $question = new ChoiceQuestion("\nSelect a task definition:", $taskDefinitions);
        return $this->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AwsHelper $awsHelper
     * @param array $repository
     *
     * @return string
     * @throws Exception
     */
    public function askForImage(InputInterface $input, OutputInterface $output, AwsHelper $awsHelper, $repository)
    {
        $imagesDescription = $awsHelper->ecr->describeImages([
            'filter' => ['tagStatus' => 'TAGGED'],
            'repositoryName' => $repository['name']
        ])->toArray();
        $imageTags = BaseArrayHelper::getColumn($imagesDescription['imageDetails'], 'imageTags.0');
        $imageQ = new ChoiceQuestion("\nChoose an image tag for this new task revision:", $imageTags);
        return $repository['url'] . ':' . $this->ask($input, $output, $imageQ);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AwsHelper $awsHelper
     *
     * @return array
     * @throws Exception
     */
    public function askForRepo(InputInterface $input, OutputInterface $output, AwsHelper $awsHelper)
    {
        try {
            $repoArray = $awsHelper->ecr->describeRepositories()->toArray();
        } catch (Exception $e) {
            throw $e;
        }
        $repoNames = BaseArrayHelper::getColumn($repoArray['repositories'], 'repositoryName');
        $repoUris = BaseArrayHelper::getColumn($repoArray['repositories'], 'repositoryUri');
        $repoArrayFlat = array_combine($repoNames, $repoUris);
        $question = new ChoiceQuestion("\nSelect an ECR repository:", $repoNames);
        $repoName = $this->ask($input, $output, $question);
        return ['name' => $repoName, 'url' => $repoArrayFlat[$repoName]];
    }
}
