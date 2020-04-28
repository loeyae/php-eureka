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

    /**
     * @var Config
     */
    private $config;
    /**
     * @var EurekaClient
     */
    private $client;

    protected function setUp()
    {
        $this->config = new Config(['EUREKA_HOST' => 'localhost', 'EUREKA_PORT' =>
            9900, 'SERVER_NAME' => 'php-demo', 'SERVER_PORT' => 8080, 'EUREKA_USER' => 'admin', 'EUREKA_PWD' => 'ad2020min']);

        $this->client = new EurekaClient($this->config);
    }

    /**
     * @covers \loeye\Config
     * @covers \loeye\client\Request
     * @covers \loeye\client\Response
     * @covers \loeye\EurekaClient
     * @depends testRegister
     */
    public function testAll(): void
    {
        $this->client->all();
        $this->assertIsArray($this->client->application);
        $this->assertNotEmpty($this->client->application);
    }

    /**
     * @covers \loeye\Config
     * @covers \loeye\client\Request
     * @covers \loeye\client\Response
     * @covers \loeye\EurekaClient
     * @covers \loeye\EurekaClient
     */
    public function testRegister(): void
    {
        $response = $this->client->register();
        $this->assertEquals(204, $response->getCode());
    }

    /**
     * @covers \loeye\Config
     * @covers \loeye\client\Request
     * @covers \loeye\client\Response
     * @covers \loeye\EurekaClient
     * @depends testRegister
     */
    public function testHeartbeat(): void
    {
        $response = $this->client->heartbeat();
        $this->assertEquals(200, $response->getCode());
    }

    /**
     * @covers \loeye\Config
     * @covers \loeye\client\Request
     * @covers \loeye\client\Response
     * @covers \loeye\EurekaClient
     * @depends testRegister
     */
    public function testStatus(): void
    {
        $this->client->setStatus(EurekaClient::EUREKA_STATUS_DOWN);
        $response = $this->client->status();
        $this->assertEquals(200, $response->getCode());
    }

    /**
     * @covers \loeye\Config
     * @covers \loeye\client\Request
     * @covers \loeye\client\Response
     * @covers \loeye\EurekaClient
     * @depends testRegister
     */
    public function testRemove(): void
    {
        $response = $this->client->remove();
        $this->assertEquals(200, $response->getCode());
    }

    /**
     * @covers \loeye\Config
     * @covers \loeye\client\Request
     * @covers \loeye\client\Response
     * @covers \loeye\EurekaClient
     */
    public function testRun(): void
    {
        for ($i=0;$i<7;$i++) {
            $response = $this->client->run();
            if (0 === $i) {
                $this->assertEquals(204, $response->getCode());
                $this->assertStringEndsWith('apps/php-demo', $response->getRequest()->getUri());
            } elseif (6 === $i) {
                $this->assertEquals(200, $response->getCode());
                $this->assertContains('/status', $response->getRequest()->getUri());
            } else {
                $this->assertEquals(200, $response->getCode());
                $this->assertContains('apps/php-demo/php-demo(', $response->getRequest()->getUri());

            }
        }
    }

}
