<?php

/**
 * Client.php
 *
 * @author Zhang Yi <loeyae@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License
 * @version 2020/4/28 14:24
 */
namespace loeye\client;


use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

/**
 * Class Client
 *
 * @package loeye\client
 */
class Client
{
    /**
     * @var LoopInterface
     */
    private $eventLoop;
    /**
     * @var \React\HttpClient\Client
     */
    private $client;

    public function __construct()
    {
        $this->eventLoop = Factory::create();
        $this->client = new \React\HttpClient\Client($this->eventLoop);
    }

    /**
     * @param Request $request
     * @param callable|null $callback
     * @return Response
     */
    public function fetch(Request $request, callable $callback = null): Response
    {
        if (!$callback) {
            $callback = static function ($chunk) {
                return true;
            };
        }
        $response = new Response($request);
        $body = $request->getBody() ?? '';
        if ($body) {
            $request->addHeader('Content-Length', strlen($body));
        }
        $httpRequest = $this->client->request($request->getMethod(), $request->getUri(), $request->getHeaders());
        $body = $request->getBody() ?? '';
        if ($body) {
            $httpRequest->write($body);
        }
        $httpRequest->on('response', static function (\React\HttpClient\Response $httpResponse) use ($response,
            $callback){
            $response->setCode($httpResponse->getCode());
            $response->setVersion($httpResponse->getVersion());
            $response->setProtocol($httpResponse->getProtocol());
            $response->setReasonPhrase($httpResponse->getReasonPhrase());
            $response->setHeaders($httpResponse->getHeaders());
            $httpResponse->on('data', static function ($chunk) use ($response) {
                $response->addChunk($chunk);
            });
            $httpResponse->on('end', static function () use ($response, $callback){
                if ($callback) {
                    $callback($response->getBody());
                }
            });
            $httpResponse->on('error', static function ($error) use ($response){
                var_dump($error);
                $response->setError($error);
                echo 'ERROR';
            });
        });
        $httpRequest->end();
        $this->eventLoop->run();
        return $response;
    }

}