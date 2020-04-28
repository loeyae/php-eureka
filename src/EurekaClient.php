<?php

/**
 * EurekaClient.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/4/27 20:57
 */

namespace loeye;


use loeye\client\Client;
use loeye\client\Request;
use loeye\client\Response;

/**
 * Class EurekaClient
 *
 * @package loeye
 */
class EurekaClient
{

    const EUREKA_STATUS_UP = 'UP';
    const EUREKA_STATUS_DOWN = 'DOWN';
    const EUREKA_STATUS_OUT = 'OUT_OF_SERVICE';

    /**
     * @var Config
     */
    private $config;
    private $instanceId;
    private $name;
    private $port;
    private $securePort;
    private $uri;
    private $status;
    /**
     * @var array
     */
    public $application;
    /**
     * @var Client
     */
    private $client;
    private $REGISTERED = false;
    private $COUNTER = 0;

    public function __construct(Config $config)
    {
        $this->status = self::EUREKA_STATUS_UP;
        $this->application = [];
        $this->securePort = 443;
        $this->config = $config;
        $this->name = $this->config->SERVER_NAME;
        $this->port = $this->config->SERVER_PORT;
        $this->uri = $this->config->getEurekaUri();
        $this->instanceId = $this->name . '(' . $this->config->IP_ADDRESS . ':' . $this->port . ')';
        $this->client = new Client();
    }

    /**
     * __destruct
     */
    public function __destruct()
    {
        $this->remove();
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return Response
     */
    public function all(): Response
    {
        $url = $this->uri . 'apps';
        $request = new Request($url);
        return $this->_request($request, function ($chunk) {
            $applications = json_decode($chunk, true);
            foreach ($applications['applications']['application'] as $value) {
                $this->application[strtolower($value['name'])] = $value;
            }
        });
    }

    /**
     * @return Response
     */
    public function register(): Response
    {
        $message = [
            'instance' => [
                'instanceId'=> $this->instanceId,
                'app'=> strtoupper($this->name),
                'appGroutName'=> null,
                'ipAddr'=> $this->config->IP_ADDRESS,
                'sid'=> 'na',
                'homePageUrl'=> $this->config->getUri(),
                'statusPageUrl'=> $this->config->getUri() . 'actuator/info',
                'healthCheckUrl'=> $this->config->getUri() . 'actuator/health',
                'secureHealthCheckUrl'=> null,
                'vipAddress'=> $this->name,
                'secureVipAddress'=> $this->name,
                'countryId'=> 1,
                'dataCenterInfo'=> [
                    '@class'=> 'com.netflix.appinfo.InstanceInfo$DefaultDataCenterInfo',
                    'name'=> 'MyOwn'
                ],
                'hostName'=> $this->config->IP_ADDRESS,
                'status'=> self::EUREKA_STATUS_UP,
                'leaseInfo'=> null,
                'isCoordinatingDiscoveryServer'=> False,
                'lastUpdatedTimestamp'=> bcmul(microtime(true), 1000),
                'lastDirtyTimestamp'=> bcmul(microtime(true), 1000),
                'actionType'=> null,
                'asgName'=> null,
                'overridden_status'=> 'UNKNOWN',
                'port'=> [
                    '$'=> $this->port,
                    '@enabled'=> 'true'
                ],
                'securePort'=> [
                    '$'=> $this->securePort,
                    '@enabled'=> 'false'
                ],
                'metadata'=> [
                    '@class'=> 'java.util.Collections$EmptyMap'
                ]
            ]
        ];
        $url = $this->uri .'apps/'. $this->name;
        $request = new Request($url, Request::METHOD_POST, ['Content-Type' => 'application/json']);
        $request->setBody(json_encode($message));
        $response = $this->_request($request);
        if (204 === $response->getCode()) {
            $this->REGISTERED = true;
        }
        return $response;
    }

    /**
     * @return Response
     */
    public function remove(): Response
    {
        $uri = $this->uri . 'apps/' . $this->name . '/' . $this->instanceId;
        $request = new Request($uri, Request::METHOD_DELETE);
        return $this->_request($request);
    }

    /**
     * @return Response
     */
    public function heartbeat(): Response
    {
        $uri = $this->uri . 'apps/' . $this->name . '/' . $this->instanceId;
        $request = new Request($uri, Request::METHOD_PUT);
        $request->setBody(json_encode(['status' => $this->status]));
        return $this->_request($request);
    }

    /**
     * @return Response
     */
    public function status(): Response
    {
        $uri = $this->uri .'apps/'. $this->name .'/'. $this->instanceId .'/status?value='. $this->status;
        $request = new Request($uri, Request::METHOD_PUT);
        $request->setBody(json_encode([]));
        return $this->_request($request);
    }

    /**
     * run
     */
    public function run(): Response
    {
        if  (!$this->REGISTERED):
            return $this->register();
        elseif ($this->COUNTER > 4):
            $this->COUNTER = 0;
            return $this->status();
        else:
            ++$this->COUNTER;
            $this->all();
            return $this->heartbeat();
        endif;
    }

    /**
     * @return \React\Http\Response
     */
    public static function actuatorInfo(): \React\Http\Response
    {
        return new \React\Http\Response(200,
            ['Content-Type' => 'application/json;charset=UTF-8'], json_encode([]));
    }

    /**
     * @return \React\Http\Response
     */
    public static function actuatorHealth(): \React\Http\Response
    {
        return new \React\Http\Response(200,
            ['Content-Type' => 'application/json;charset=UTF-8'], json_encode(['status' => self::EUREKA_STATUS_UP]));
    }

    /**
     * @param Request $request
     * @param callable|null $callback
     * @return Response
     */
    protected function _request(Request $request,
                                callable $callback = null): Response
    {

        $request->addHeader('Accept', 'application/json;charset=UTF-8');
        $request->setAuthUsername($this->config->EUREKA_USER);
        $request->setAuthPassword($this->config->EUREKA_PWD);

        return $this->client->fetch($request, $callback);
    }
}