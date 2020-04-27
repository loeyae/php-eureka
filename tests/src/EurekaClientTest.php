<?php

/**
 * EurekaClientTest.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/4/27 23:20
 */

namespace loeye\unit;

use loeye\Config;
use loeye\EurekaClient;
use PHPUnit\Framework\TestCase;

class EurekaClientTest extends TestCase
{

    public function testAll()
    {
        $config = new Config(['EUREKA_HOST' => 'localhost', 'EUREKA_PORT' =>
            9900, 'SERVER_NAME' => 'php-demo', 'SERVER_PORT' => 8080, 'EUREKA_USER' => 'admin', 'EUREKA_PWD' => 'ad2020min']);
        $client = new EurekaClient($config);
        $client->all();
        $this->assertIsArray($client->application);
        $this->assertNotEmpty($client->application);
    }

    public function testRegister()
    {

    }


}
