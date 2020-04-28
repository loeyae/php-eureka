<?php

/**
 * Quit.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/4/29 1:23
 */

use loeye\Config;
use loeye\EurekaClient;

require_once('vendor/autoload.php');

$env = $argv[1] ?? 'local';
$local = ['EUREKA_HOST' => 'localhost', 'EUREKA_PORT' =>
    9900, 'SERVER_NAME' => 'php-demo', 'SERVER_PORT' => 8080,
    'EUREKA_USER' => 'admin', 'EUREKA_PWD' => 'ad2020min'];
$test = ['EUREKA_HOST' => '192.168.1.220', 'EUREKA_PORT' =>
    9900, 'SERVER_NAME' => 'php-demo', 'SERVER_PORT' => 8080,
    'EUREKA_USER' => 'admin', 'EUREKA_PWD' => 'ad2020min'];
$config = new Config($env === 'test' ? $test : $local);
$eurekaClient = new EurekaClient($config);
$eurekaClient->remove();