<?php

/**
 * Application.php.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/4/27 20:13
 */
require_once('vendor/autoload.php');

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;

$loop = Factory::create();
$server = new Server(static function(ServerRequestInterface $request){
    return new Response(200,
        ['Content-Type' => 'application/json;charset=UTF-8'],
        json_encode(['code' => 200, 'msg' => 'Ok']));
});

$loop->addPeriodicTimer(10, static function(){
    print_r(date('Y-m-d H:i:s'));
});

$socket = new \React\Socket\Server(8080, $loop);
$server->listen($socket);
$loop->run();