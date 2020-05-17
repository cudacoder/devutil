<?php

namespace DEVUtil;

use DEVUtil\Helpers\BaseArrayHelper;
use Eloquent\Enumeration\AbstractMultiton;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Enums
 * @package application\common
 * @method static |Enums Git
 * @method static |Enums Docker
 * @method static |Enums AWS
 * @method static |Enums Dev
 * @method static |Enums Project
 * @method static |Enums Json
 */
class Enums extends AbstractMultiton
{
    private $_settings = [];

    const GIT_BASE_URL = 'github.com';
    const DEVUTIL_GIT_TOKEN_FILE = '.devutil-git-token';

    /**
     * @param string $key
     * @param        $settings
     *
     * @throws \Eloquent\Enumeration\Exception\ExtendsConcreteException
     */
    protected function __construct($key, $settings)
    {
        parent::__construct($key);
        $this->_settings = $settings;
    }

    private static function getHomeDirByFS()
    {
        $fs = new Filesystem();
        $envVariables = getenv();
        if (php_uname('s') == E_APP::LINUX_OS) {
            $fs->hardlink('/root/.local/bin/aws', '/usr/bin/aws');
            $defaultVar = 'HOME';
            $default = '/root';
        } else {
            $defaultVar = 'USERPROFILE';
            $default = $envVariables['HOMEDRIVE'] . $envVariables['HOMEPATH'];
        }
        $home = BaseArrayHelper::getValue($envVariables, $defaultVar, $default);
        return $home;
    }

    protected static function initializeMembers()
    {
        new static('Git', [
            'BaseURL' => self::GIT_BASE_URL,
            'BaseLoginURL' => 'https://' . self::GIT_BASE_URL . ':',
            'TokenFile' => self::getHomeDirByFS() . DS . self::DEVUTIL_GIT_TOKEN_FILE,
        ]);
        new static('Docker', ['DockerFilesPath' => dirname(__FILE__) . DS . '..' . DS . 'build']);
        new static('AWS', ['HomePath' => self::getHomeDirByFS()]);
        new static('Dev', ['DevFilesPath' => dirname(__FILE__) . DS . 'data' . DS . 'dev']);
        new static('Project', ['ProjectBaseDir' => dirname(__FILE__) . DS . '..']);
    }

    public function getSettings($key = null)
    {
        return !is_null($key) ? BaseArrayHelper::getValue($this->_settings, $key) : $this->_settings;
    }

    public function buildGitlabProjectUrl($project_id = null, $branch = 'master')
    {
        return "http://{$this->_settings['BaseURL']}/api/v3/projects/{$project_id}/repository/archive?sha={$branch}";
    }

    public function getDockerComposeTemplate()
    {
        return <<<YML
version: '3'
volumes:
  localdb:
services:
  web:
    image: php
    ports:
    - "8080:80"
    links:
    - db
    volumes:
    - ./:/var/www/html
    environment:
    - XDEBUG_CONFIG=remote_host=host.docker.internal remote_port=9000 remote_enable=1 idekey=PHPSTORM remote_log=/tmp/xdebug.c
    - PHP_IDE_CONFIG=serverName=local.project.com
  db:
    image: mysql:5.7.18
    ports:
    - "3308:3306"
    environment:
      MYSQL_ROOT_PASSWORD: r00tpass
    volumes:
    - localdb:/var/lib/mysql
YML;
    }
}
