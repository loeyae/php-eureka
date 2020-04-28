<?php

/**
 * Request.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/4/28 14:25
 */
namespace loeye\client;

use RingCentral\Psr7\Uri;

/**
 * Class Request
 *
 * @package loeye\client
 */
class Request {

    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_DELETE = 'DELETE';
    /**
     * @var string
     */
    private $method;
    private $body;
    private $authUsername;
    private $authPassword;
    private $authType = 'Basic';

    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var array
     */
    private $headers;

    /**
     * Request constructor.
     * @param $uri
     * @param string $method
     * @param array $headers
     */
    public function __construct($uri, $method = self::METHOD_GET, $headers = [])
    {
        $this->setUri($uri);
        $this->setMethod($method);
        $this->setHeaders($headers);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return Request
     */
    public function setMethod(string $method): Request
    {
        $this->method = in_array(strtoupper($method), [self::METHOD_GET, self::METHOD_POST, self::METHOD_DELETE,
            self::METHOD_PUT], true) ? strtoupper($method) : self::METHOD_GET;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return (string)$this->uri;
    }

    /**
     * @param mixed $uri
     * @return Request
     */
    public function setUri($uri): Request
    {
        $this->uri = new Uri($uri);
        return $this;
    }

    /**
     * @return null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param null $body
     * @return Request
     */
    public function setBody($body): Request
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthUsername()
    {
        return $this->authUsername;
    }

    /**
     * @param mixed $authUsername
     * @return Request
     */
    public function setAuthUsername($authUsername): Request
    {
        $this->authUsername = $authUsername;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthPassword()
    {
        return $this->authPassword;
    }

    /**
     * @param mixed $authPassword
     * @return Request
     */
    public function setAuthPassword($authPassword): Request
    {
        $this->authPassword = $authPassword;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = $this->headers;
        if ($this->authUsername) {
            $headers['Authorization'] = $this->buildAuthorization();
        }
        return $headers;
    }

    /**
     * @param array $headers
     * @return Request
     */
    public function setHeaders(array $headers): Request
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return Request
     */
    public function addHeader($key, $value): Request
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getHeader($key)
    {
        return $this->headers[$key] ?? null;
    }

    /**
     * @return string
     */
    private function buildAuthorization(): string
    {
        return $this->authType .' '. base64_encode($this->authUsername.':'.$this->authPassword);
    }

}