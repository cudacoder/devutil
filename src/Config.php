<?php

namespace DEVUtil;

use Dto\Dto;
use DEVUtil\Dto\EcsDto;
use DEVUtil\Dto\GitlabDto;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Config
 * @package DEVUtil
 * @property Dto $dry
 * @property Dto $yii2
 * @property Dto $profile
 * @property Dto $force_build
 * @property GitlabDto $gitlab
 * @property EcsDto $ecs
 */
class Config extends Dto
{

    protected $schema = [
        'type' => 'object',
        'additionalProperties' => false,
        'properties' => [
            'dry' => ['type' => 'boolean'],
            'yii2' => ['type' => 'boolean'],
            'profile' => ['type' => 'string'],
            'force_build' => ['type' => 'boolean'],
            'gitlab' => ['$ref' => 'DEVUtil\Dto\GitlabDto'],
            'ecs' => ['$ref' => 'DEVUtil\Dto\EcsDto']
        ],
        'default' => [
            'dry' => true,
            'yii2' => false,
            'profile' => 'default',
            'force_build' => true
        ]
    ];

    public function save()
    {
        $fs = new Filesystem();
        $fs->dumpFile(getcwd() . '/devutil.json', $this->toJson(true));
    }
}
