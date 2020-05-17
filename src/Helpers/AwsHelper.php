<?php

namespace DEVUtil\Helpers;

use Aws\Sdk;
use Exception;
use Aws\Ecr\EcrClient;
use Aws\Ecs\EcsClient;
use Aws\ElasticLoadBalancingV2\ElasticLoadBalancingV2Client;

/**
 * Class AwsHelper
 * @package DEVUtil\Helper
 * @property Sdk                          $sdk
 * @property EcsClient                    $ecs
 * @property EcrClient                    $ecr
 * @property ElasticLoadBalancingV2Client $elb
 */
class AwsHelper
{

    /** @var $sdkClient Sdk */
    private $sdkClient;

    /** @var $ecsClient EcsClient */
    private $ecsClient;

    /** @var $ecrClient EcrClient */
    private $ecrClient;

    /** @var $elbClient ElasticLoadBalancingV2Client */
    private $elbClient;

    public function __construct($attributes = [])
    {
        $this->sdkClient = new Sdk($attributes + ['version' => 'latest']);
        $this->ecsClient = $this->sdkClient->createEcs();
        $this->ecrClient = $this->sdkClient->createEcr();
        $this->elbClient = $this->sdkClient->createElasticLoadBalancingV2();
    }

    /**
     * @param $name string
     * @return EcsClient | EcrClient
     * @throws Exception
     */
    public function __get($name)
    {
        $clientName = "{$name}Client";
        if (property_exists('DEVUtil\Helpers\AwsHelper', $clientName)) {
            return $this->$clientName;
        } else {
            throw new Exception('Missing property in AwsHelper: ' . $clientName);
        }
    }
}
