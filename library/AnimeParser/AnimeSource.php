<?php

namespace Library\AnimeParser;

use GuzzleHttp\Client;
use DiDom\Document;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Library\Requester\Requester;

/**
 * Class AnimeSource
 * Принимает источники аниме страниц и перерабатывает их в DiDom Document'ы
 *
 * @var $data Document
 * @var $source string имя файла - источника или ссылки etc.
 * @package Library\AnimeParser
 */
abstract class AnimeSource
{
    protected $data = null;
    protected $sourceName = null;

    /**
     * Принимает источник и возвращает класс-источник
     *
     * @param $source
     * @param string|null $sourceName
     * @return AnimeSource
     * @throws \Exception
     */
    public static function spawn($source, string $sourceName = null): AnimeSource
    {
        $returnSource = new static();
        $returnSource->sourceName = $sourceName;

        switch (static::filterSource($source)) {
            case 'url':
                $returnSource->data = static::getDocument($source);
                break;
            case 'document':
                $returnSource->data = $source;
                if ($sourceName == null) {
                    throw new \Exception('Ожидалось получение имени источника $sourceName');
                }
                $returnSource->sourceName = $sourceName;
                break;
        }

        return $returnSource;
    }

    /**
     * Принимает массив источников и возвращает массив классов-источников
     *
     * @param $sources
     * @return AnimeSource[]
     * @throws \Exception
     */
    public static function spawnMany(array $sources): array
    {
        $returnSources = [];

        switch (static::filterSource(array_first($sources))) {
            case 'url':
                $documents = static::processUrlsToDocuments($sources);
                foreach ($documents as $url => $document) {
                    $returnSources[] = static::spawn($document, $url);
                }
                break;
            case 'document':
                foreach ($sources as $url => $document) {
                    $returnSources[] = static::spawn($document, $url);
                }
                break;
        }

        return $returnSources;
    }

    /**
     * Возвращает формат источника
     *
     * @param $source
     * @return string
     * @throws \Exception
     */
    final protected static function filterSource($source): string
    {
        if(static::is_url($source)) {
            return 'url';
        } elseif ($source instanceof Document) {
            return 'document';
        } elseif (is_array($source)) {
            return 'array';
        } else {
            throw new \Exception('Источник не соответствует заданным форматам');
        }
    }

    /**
     * Проверяет ивляеется ли строка ссылкой
     *
     * @param $data
     * @return bool
     */
    final protected static function is_url(string $data): bool
    {
        return filter_var($data, FILTER_VALIDATE_URL);
    }

    /**
     * Возвращает источник для парсинга в виде DiDom документа
     *
     * @return Document
     * @throws \Exception
     */
    final public function getData(): Document
    {
        try {
            return $this->data;
        } catch (\Throwable $exception) {
            throw new \Exception("Не удалось получить Document с источника" . $this->getSourceName());
        }
    }

    /**
     * Возвращает имя файла - источника или ссылку etc.
     *
     * @return string
     */
    final public function getSourceName(): string
    {
        return $this->sourceName;
    }

    /**
     * Получает Document из ссылки
     *
     * @param string $link
     * @return Document
     */
    final protected static function getDocument(string $link): Document
    {
        $request = Requester::postGuzzle($link, static::getLoginData());
        $html = $request->getBody()->getContents();

        return new Document($html);
    }

    /**
     * Перерабатывает ссылки в документы
     *
     * @param string[] $urls
     * @return Document[]
     * @throws \Exception
     */
    final protected static function processUrlsToDocuments(array $urls): array
    {
        /**
         * @var Response[] $fulfilled
         * @var ConnectException[] $rejected
         * @var Document[] $documents
         */
        $fulfilled = $rejected = $documents = [];

        Requester::postGuzzleAsync($urls, static::getLoginData(), $fulfilled, $rejected);

        foreach ($fulfilled as $url => $response) {
            if(!static::is_url($url)) throw new \Exception('Ожидалось получение ссылки');
            $documents[$url] = new Document($response->getBody()->getContents());
        }

        foreach ($rejected as $url => $exception) {
            //throw new \Exception("Не удалось отправить запрос на $url || message: " . $exception->getMessage());
            Log::channel('parsing')->error("Не удалось отправить запрос на $url || message: " . $exception->getMessage());
        }

        return $documents;
    }

    /**
     * Получить данные для входа в аккаунт
     *
     * @return array
     */
    public static function getLoginData(): array
    {
        $login_data = array(
            'form_params' => [
                'login_name' => null,
                'login_password' => null,
                'login' => 'submit',
            ],
        );
        return $login_data;
    }
}