<?php

namespace DEVUtil\Dto;

use Dto\Dto;

/**
 * Class Config
 * @package DEVUtil
 * @property Dto $project_id
 * @property Dto $project_name
 * @property Dto $branch
 */
class GitlabDto extends Dto {

    protected $schema = [
        'type' => 'object',
        'additionalProperties' => false,
        'properties' => [
            'project_id' => ['type' => 'integer'],
            'project_name' => ['type' => 'string'],
            'branch' => ['type' => 'string']
        ],
        'default' => [
            'branch' => 'master'
        ]
    ];
}
