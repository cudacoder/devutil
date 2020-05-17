<?php

namespace DEVUtil\Dto;

use Dto\Dto;

/**
 * Class EcrObject
 * @package DEVUtil\Dto
 * @property Dto $cpu
 * @property Dto $memory
 * @property Dto $family
 * @property Dto $networkMode
 * @property Dto $executionRoleArn
 * @property Dto $requiresCompatibilities
 * @property ContainerDefinitionDto[] $containerDefinitions
 */
class TaskDefinitionDto extends Dto
{

    protected $schema = [
        'type' => 'object',
        'properties' => [
            'cpu' => ['type' => 'string'],
            'memory' => ['type' => 'string'],
            'family' => ['type' => 'string'],
            'networkMode' => ['type' => 'string'],
            'executionRoleArn' => ['type' => 'string'],
            'requiresCompatibilities' => [
                'type' => 'array',
                'items' => ['type' => 'string']
            ],
            'containerDefinitions' => [
                'type' => 'array',
                'items' => [
                    '$ref' => 'DEVUtil\Dto\ContainerDefinitionDto'
                ]
            ],
        ],
        'default' => [
            'cpu' => '512',
            'memory' => '1024',
            'executionRoleArn' => 'arn:aws:iam::USER_ID:role/NAME',
            'requiresCompatibilities' => ['EC2']
        ]
    ];
}
