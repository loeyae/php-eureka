<?php

/**
 * EurekaClient.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/4/27 20:57
 */

namespace loeye;


use React\EventLoop\Factory;
use React\HttpClient\Client;
use React\HttpClient\Response;

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
    /**
     * @var array
     */
    public $application;
    private $body;

    public function __construct(Config $config)
    {
        $this->application = [];
        $this->securePort = 443;
        $this->config = $config;
        $this->name = $this->config->SERVER_NAME;
        $this->port = $this->config->SERVER_PORT;
        $this->uri = $this->config->getEurekaUri();
        $this->instanceId = $this->name . '(' . $this->config->IP_ADDRESS . ':' . $this->port . ')';
    }

    public function all() {
        $url = $this->uri . 'apps';
        $this->_request($url, 'GET', [], [], function ($chunk) {
            $applications = json_decode($chunk, true);
            foreach ($applications['applications']['application'] as $value) {
                $this->application[strtolower($value['name'])] = $value;
            }
        });
    }

    public function register() {
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
                'lastUpdatedTimestamp'=> (int)(microtime(true) * 1000),
                'lastDirtyTimestamp'=> (int)(microtime() * 1000),
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
        $this->_request($url, 'POST', $message, ['content-type' => 'application/json']);
    }

    protected function _request($uri, $method, $data = [], $headers = [],
                                callable $callback = null) {
        if (!$callback) {
            $callback = static function ($chunk) {
                return true;
            };
        }
        $this->body = '';
        $loop = Factory::create();
        $client = new Client($loop);
        $headers['Accept'] = 'application/json;charset=UTF-8';
        $headers['Authorization'] = 'Basic '. base64_encode($this->config
                ->EUREKA_USER .':'. $this->config->EUREKA_PWD);
        $request = $client->request($method, $uri, $headers);
        if (in_array(strtoupper($method), ['POST', 'PUT'])) {
            $request->write(json_encode($data));
        }
        $request->on('response', function (Response $response) use ($callback){
            $response->on('data', function ($chunk) {
                $this->body .= $chunk;
            });
            $response->on('end', function () use ($callback){
                $callback($this->body);
            });
            $response->on('error', static function (){
                echo 'ERROR';
            });
        });
        $request->end();
        $loop->run();
        return $this->body;
    }
}