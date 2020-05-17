<?php

namespace DEVUtil\Dto;

use Dto\Dto;

/**
 * Class EcrObject
 * @package DEVUtil\Dto
 * @property Dto $hostPort
 * @property Dto $protocol
 * @property Dto $containerPort
 */
class ContainerPortMappingsDto extends Dto {

    protected $schema = [
        'type' => 'object',
        'properties' => [
            'hostPort' => ['type' => 'integer'],
            'protocol' => ['type' => ['string']],
            'containerPort' => ['type' => 'integer'],
        ]
    ];
}