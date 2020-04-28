<?php

/**
 * Response.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/4/28 14:25
 */


namespace loeye\client;


class Response
{

    /**
     * @var string
     */
    private $body;
    private $protocol;
    private $version;
    private $code;
    private $reasonPhrase;

    /**
     * @var array
     */
    private $headers;
    private $chunks;

    /**
     * @var Request
     */
    private $request;

    private $error;

    /**
     * Response constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->setRequest($request);
    }

    /**
     * @return mixed
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param mixed $protocol
     * @return Response
     */
    public function setProtocol($protocol): Response
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     * @return Response
     */
    public function setVersion($version): Response
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return Response
     */
    public function setCode($code): Response
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * @param mixed $reasonPhrase
     * @return Response
     */
    public function setReasonPhrase($reasonPhrase): Response
    {
        $this->reasonPhrase = $reasonPhrase;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return Response
     */
    public function setHeaders(array $headers): Response
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChunks()
    {
        return $this->chunks;
    }

    /**
     * @param mixed $chunks
     * @return Response
     */
    public function setChunks($chunks): Response
    {
        $this->chunks = $chunks;
        return $this;
    }

    /**
     * @param $chunk
     * @return Response
     */
    public function addChunk($chunk): Response
    {
        $this->chunks[] = $chunk;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function setRequest(Request $request): Response
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param string $body
     * @return Response
     */
    public function setBody(string $body): Response
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body ?? implode('', $this->getChunks());
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error): void
    {
        $this->error = $error;
    }
}