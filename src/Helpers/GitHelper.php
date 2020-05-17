<?php

namespace DEVUtil\Helpers;

use DEVUtil\Enums;
use Gitlab\Client;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;

class GitHelper
{

    /** @var  Client $_client */
    private $_client;

    /**
     * GitHelper constructor.
     */
    public function __construct()
    {
        $this->_client = Client::create(Enums::Git()->getSettings('BaseLoginURL'));
    }

    /**
     * @param array $options
     *
     * @return array All of the projects for the current user
     */
    public function getProjects($options = [])
    {
        return $this->_client->api('projects')->all($options);
    }

    /**
     * Authenticates the user in the Git client
     *
     * @param $gitToken
     *
     * @return void
     */
    public function authenticate($gitToken)
    {
        $this->_client->authenticate($gitToken, Client::AUTH_URL_TOKEN);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool|string
     */
    public function getToken(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $gitTokenFile = Enums::Git()->getSettings('TokenFile');
        $token = $fs->exists($gitTokenFile) ? file_get_contents($gitTokenFile) : false;
        if (!$token) {
            $questionHelper = new QuestionHelper();
            $tokenQuestion = (new Question('Please enter your GitLab Personal Access Token (secret): '))->setHidden(true);
            $token = $questionHelper->ask($input, $output, $tokenQuestion);
            $fs->dumpFile(Enums::Git()->getSettings('TokenFile'), $token);
        }
        return $token;
    }
}
