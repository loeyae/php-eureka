<?php

/**
 * Application.php.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/4/27 20:13
 */
require_once('vendor/autoload.php');

use loeye\Config;
use loeye\EurekaClient;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;

$loop = Factory::create();
$server = new Server(static function(ServerRequestInterface $request){

    $path = $request->getUri()->getPath();
    if ($path === '/actuator/info') {
        return EurekaClient::actuatorInfo();
    }
    if ($path === '/actuator/health') {
        return EurekaClient::actuatorHealth();
    }

    return new Response(200,
        ['Content-Type' => 'application/json;charset=UTF-8'],
        json_encode(['code' => 200, 'msg' => 'Ok']));
});

$env = $argv[1] ?? 'local';
$local = ['EUREKA_HOST' => 'localhost', 'EUREKA_PORT' =>
    9900, 'SERVER_NAME' => 'php-demo', 'SERVER_PORT' => 8080,
    'EUREKA_USER' => 'admin', 'EUREKA_PWD' => 'ad2020min'];
$test = ['EUREKA_HOST' => '192.168.1.220', 'EUREKA_PORT' =>
    9900, 'SERVER_NAME' => 'php-demo', 'SERVER_PORT' => 8080,
    'EUREKA_USER' => 'admin', 'EUREKA_PWD' => 'ad2020min'];
$config = new Config($env === 'test' ? $test : $local);
$eurekaClient = new EurekaClient($config);
$loop->addPeriodicTimer(10, static function() use ($eurekaClient){
    $eurekaClient->run();
});
//$loop->addSignal(SIGINT, static function () use ($eurekaClient) {
//    $eurekaClient->remove();
//});
//$loop->addSignal(SIGTERM, static function () use($eurekaClient) {
//    $eurekaClient->remove();
//});
$socket = new \React\Socket\Server('0.0.0.0:8080', $loop);
$server->listen($socket);
$loop->run();