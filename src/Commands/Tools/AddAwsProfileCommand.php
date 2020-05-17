<?php

namespace DEVUtil\Commands\Tools;

use DEVUtil\E_APP;
use DEVUtil\Enums;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SetupCommand
 * @package devutil\commands\Aws
 * @property Filesystem $fs
 */
class AddAwsProfileCommand extends Command
{

    private $access;
    private $secret;
    private $region;

    protected function configure()
    {
        $this
            ->setName('tools:add-profile')
            ->setAliases(['add'])
            ->addArgument('profile', InputArgument::REQUIRED, 'AWS profile name.')
            ->setDescription('Adds a new profile in the $HOME/.aws/credentials and $HOME/.aws/config files')
            ->setHelp('This tool enables you to add new AWS credentials for a new AWS account for DEVUtil to work with');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $regionQ = new Question('Please enter your Region: ');
        $accessKeyQ = (new Question('Please enter AWS Access Key ID: '))->setHidden(true);
        $secretKeyQ = (new Question('Please enter AWS Secret Access Key: '))->setHidden(true);
        $this->access = $questionHelper->ask($input, $output, $accessKeyQ);
        $this->secret = $questionHelper->ask($input, $output, $secretKeyQ);
        $this->region = $questionHelper->ask($input, $output, $regionQ);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $logger = new SymfonyStyle($input, $output);
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $profile = $input->getArgument('profile');
        $awsConfigFile = Enums::AWS()->getSettings('HomePath') . DS . '.aws' . DS . 'config';
        $awsCredsFile = Enums::AWS()->getSettings('HomePath') . DS . '.aws' . DS . 'credentials';
        $awsConfig = !$fs->exists($awsConfigFile) ? [] : parse_ini_file($awsConfigFile, true);
        $awsCreds = !$fs->exists($awsCredsFile) ? [] : parse_ini_file($awsCredsFile, true);
        $confirmOverwrite = new ConfirmationQuestion('Are you sure you want to overwrite existing "default" profile? ');
        if ($profile == 'default' && array_key_exists('default', $awsCreds) && !$questionHelper->ask($input, $output, $confirmOverwrite)) {
            $logger->note('Rename existing "default" in $HOME/.aws config files and try again or try again with a different profile name.');
            return;
        }
        $awsCreds[$profile][E_APP::AWS_INI_ACCESS_KEYNAME] = $this->access;
        $awsCreds[$profile][E_APP::AWS_INI_SECRET_KEYNAME] = $this->secret;
        $awsConfig[$profile][E_APP::AWS_INI_REGION_KEYNAME] = $this->region;
        $fs->dumpFile($awsCredsFile, E_APP::convertToINI($awsCreds));
        $fs->dumpFile($awsConfigFile, E_APP::convertToINI($awsConfig));
        $logger->success("Added {$profile} profile to \$HOME/.aws/credentials & \$HOME/.aws/config files");
    }
}
