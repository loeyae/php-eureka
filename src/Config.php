<?php

/**
 * Config.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/4/27 22:18
 */

namespace loeye;


class Config
{

    public $IP_ADDRESS;
    public $SERVER_NAME;
    public $SERVER_PORT;
    private $HTTPS;
    private $EUREKA_HOST;
    /**
     * @var int
     */
    private $EUREKA_PORT;
    public $EUREKA_USER;
    public $EUREKA_PWD;

    public function __construct($settings)
    {
        foreach ($settings as $key => $value) {
            $this->$key = $value;
        }
        $name = gethostname();
        $this->IP_ADDRESS = gethostbyname($name);
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return 'http://' . $this->IP_ADDRESS . ':' . $this->SERVER_PORT . '/';
    }

    /**
     * @return string
     */
    public function getEurekaUri()
    {
        $eurekaUri = ($this->HTTPS ? 'https' : 'http') . '://';
        $eurekaUri .= $this->EUREKA_HOST . ':' . $this->EUREKA_PORT . '/eureka/';
        return $eurekaUri;
    }

}