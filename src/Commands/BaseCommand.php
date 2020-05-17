<?php

namespace DEVUtil\Commands;

use DEVUtil\E_APP;
use DEVUtil\Enums;
use DEVUtil\Config;
use DEVUtil\Helpers\AwsHelper;
use DEVUtil\Helpers\GitHelper;
use DEVUtil\Helpers\QuestionHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BaseCommand
 *
 * @package DEVUtil\Commands
 * @property Filesystem $fs
 * @property Config $config
 * @property SymfonyStyle $logger
 * @property GitHelper $gitHelper
 * @property AwsHelper $awsHelper
 * @property QuestionHelper $questionHelper
 */
class BaseCommand extends Command
{

    protected $fs;
    protected $logger;
    protected $config;
    protected $gitHelper;
    protected $awsHelper;
    protected $questionHelper;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->fs = new Filesystem();
        $this->gitHelper = new GitHelper();
        $this->questionHelper = new QuestionHelper();
        $this->logger = new SymfonyStyle($input, $output);
        $awsConfigFile = Enums::AWS()->getSettings('HomePath') . DS . '.aws' . DS . 'config';
        $awsCredsFile = Enums::AWS()->getSettings('HomePath') . DS . '.aws' . DS . 'credentials';
        if (!$this->fs->exists([$awsConfigFile, $awsCredsFile])) {
            $this->logger->note('Missing AWS config files - use "devutil init" to initialize them.');
            exit;
        }
    }

    protected function initAwsHelper($profile)
    {
        $awsConfig = parse_ini_file(Enums::AWS()->getSettings('HomePath') . DS . '.aws' . DS . 'config', true);
        $this->awsHelper = new AwsHelper([
            'profile' => $profile,
            'region' => $awsConfig[$profile][E_APP::AWS_INI_REGION_KEYNAME]
        ]);
    }

    /**
     * @param string|null $profile
     *
     * @throws \Exception
     */
    protected function initConfig(string $profile = null)
    {
        $configFile = getcwd() . DS . 'devutil.json';
        if (!$this->fs->exists($configFile)) {
            $this->logger->note('Missing devutil.json file - use "devutil generate:conf" to create it.');
            exit;
        }
        try {
            $this->config = new Config(json_decode(file_get_contents($configFile)));
        } catch (\Exception $e) {
            $this->logger->error("devutil.json validation failed!");
            $this->logger->block($e->getMessage());
            exit;
        }
        $this->initAwsHelper($profile ?? $this->config->profile->toScalar());
    }
}
