<?php

namespace Library\Requester;

use DiDom\Document;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use function GuzzleHttp\Promise\settle;
use function GuzzleHttp\Promise\unwrap;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Requester
{
    /**
     * Отправляет post запросы по массиву ссылок,
     * возвращает успешные ответы в $fulfilled, отвергнутые в $rejected
     *
     * @param array $urls
     * @param array $postData
     * @param array $fulfilled
     * @param array $rejected
     * @param int $inRow Сколько запросов отправлять разом
     */
    public static function postGuzzleAsync(array $urls, array $postData, array &$fulfilled, array &$rejected = [], $inRow = 300)
    {
        $client = self::getGuzzleClient();

        $chunks = array_chunk($urls, $inRow);

        foreach ($chunks as $chunk) {
            $fulf = [];
            $rej = [];

            $requests = function (array $chunk) use ($client, $postData) {
                foreach ($chunk as $url) {
                    yield $url => function () use ($client, $url, $postData) {
                        return $client->postAsync($url, $postData);
                    };
                }
            };


            $pool = new Pool($client, $requests($chunk), [
                'concurrency' => 5,
                'fulfilled' => function (Response $response, $index) use (&$fulf) {
                    $fulf[$index] = $response;
                },
                'rejected' => function ($reason, $index) use (&$rej) {
                    $rej[$index] = $reason;
                },
            ]);

            $pool->promise()->wait();
            $fulfilled = array_merge($fulfilled, $fulf);
            $rejected = array_merge($rejected, $rej);
        }

    }

    /**
     * Единичный post запрос
     *
     * @param string $url
     * @param array $postData
     * @return ResponseInterface
     */
    public static function postGuzzle(string $url, array $postData) {
        return self::getGuzzleClient()->post($url, $postData);
    }

    /**
     * Возвращает Guzzle Client
     *
     * @return Client
     */
    public static function getGuzzleClient()
    {
        $client = new Client([
            //'referer' => true,
            'headers' => [
                'User-Agent' => self::generateUserAgent(),
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, br',
            ]
        ]);

        return $client;
    }

    /**
     * Генератор UserAgent'ов
     *
     * @return string
     */
    public static function generateUserAgent()
    { // todo генератор агентов
        return 'Name of your tool/v1.0';
        //return 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36';
    }
}