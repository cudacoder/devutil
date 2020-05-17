<?php

namespace DEVUtil\Commands\Aws\Ecr;

use DEVUtil\Commands\BaseCommand;
use DEVUtil\Helpers\BaseArrayHelper;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class EcrLoginCommand
 *
 * @package devutil\commands\Aws
 */
class EcrLoginCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('ecr:login')
            ->setAliases(['login'])
            ->setDescription('Logs-in to Docker image repository.')
            ->addOption('profile', 'p', InputOption::VALUE_OPTIONAL, 'AWS Profile', 'default')
            ->addOption('region', 'r', InputOption::VALUE_OPTIONAL, 'Region', 'eu-west-1')
            ->setHelp('Logs-in to the AWS ECR service using the credentials in the $HOME/.aws folder');
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
        $profile = $input->getOption('profile');
        $this->initConfig($profile);
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
        $this->logger->text("Logging in to ECR as {$input->getOption('profile')}...");
        try {
            $authData = $this->awsHelper->ecr->getAuthorizationToken()->get('authorizationData');
        } catch (\Exception $e) {
            $this->logger->error('Unable to connect to ECR!');
            $this->logger->block($e->getMessage());
            return;
        }
        $authTokenDecoded = base64_decode(BaseArrayHelper::getValue($authData, '0.authorizationToken'));
        $authDataArray = explode(':', $authTokenDecoded);
        $url = BaseArrayHelper::getValue($authData, '0.proxyEndpoint');
        try {
            $dockerLogin = new Process("docker login -u {$authDataArray[0]} -p {$authDataArray[1]} {$url}");
            $dockerLogin->mustRun();
        } catch (ProcessFailedException $e) {
            preg_match("/Error\sOutput:(\s|.)*/", $e->getMessage(), $matches);
            $this->logger->error("Login process failed:");
            $this->logger->block($matches);
            return;
        }
        $this->logger->success("Done!");
    }
}
