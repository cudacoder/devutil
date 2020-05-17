<?php

namespace DEVUtil\Dto;

use Dto\Dto;

/**
 * Class TaskContainerDefinitionObject
 *
 * @package DEVUtil\Dto
 * @property Dto $name
 * @property Dto $image
 * @property Dto $cpu
 * @property Dto $memory
 * @property Dto $environment
 * @property Dto $memoryReservation
 * @property ContainerPortMappingsDto[] $portMappings
 */
class ContainerDefinitionDto extends Dto {

    protected $schema = [
        'type' => 'object',
        'properties' => [
            'name' => ['type' => 'string'],
            'image' => ['type' => 'string'],
            'cpu' => ['type' => 'integer'],
            'memory' => ['type' => 'integer'],
            'memoryReservation' => ['type' => 'integer'],
            'logConfiguration' => [
                'type' => 'object',
                'properties' => [
                    'logDriver' => ['type' => 'string'],
                    'options' => [
                        'type' => 'object',
                        'properties' => [
                            'awslogs-group' => ['type' => 'string'],
                            'awslogs-region' => ['type' => 'string'],
                            'awslogs-stream-prefix' => ['type' => 'string'],
                        ]
                    ],
                ]
            ],
            'environment' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'value' => ['type' => 'string'],
                    ]
                ]
            ],
            'portMappings' => [
                'type' => 'array',
                'items' => [
                    '$ref' => 'DEVUtil\Dto\ContainerPortMappingsDto',
                ]
            ]
        ],
        'default' => [
            'logConfiguration' => [
                'logDriver' => 'awslogs',
                'options' => [
                    'awslogs-group' => '/ecs/App',
                    'awslogs-region' => 'eu-west-1',
                    'awslogs-stream-prefix' => 'ecs'
                ]
            ],
            'name' => 'main',
            'image' => '',
            'portMappings' => [
                [
                    'hostPort' => 80,
                    'containerPort' => 80
                ],
                [
                    'hostPort' => 22,
                    'containerPort' => 22
                ],
                [
                    'hostPort' => 443,
                    'containerPort' => 443
                ]
            ]
        ]
    ];
}