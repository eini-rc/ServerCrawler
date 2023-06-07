<?php

namespace App\Services;

use App\Repositories\UrlRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class CrawlerService
{
    protected $urlRepository;
    protected $httpClient;
    protected $urlsResults = [];

    public function __construct(UrlRepository $urlRepository, Client $httpClient)
    {
        $this->urlRepository = $urlRepository;
        $this->httpClient = $httpClient;
    }

    public function crawlUrls($url, $depth)
    {
        return $this->crawlUrl($url, $depth);
    }

    protected function crawlUrl($url, $depth)
    {
        if ($depth === 0) {
            return;
        }

        try {
            $content = $this->getContentFromUrl($url);
            $this->urlsResults[] = ['url' => $url];

            if ($depth > 1) {
                $links = $this->extractLinks($content);
                foreach ($links as $link) {
                    $this->crawlUrl($link, $depth - 1);
                }
            }
            // Log::info(phpinfo());
        } catch (GuzzleException $e) {
            Log::error(`URL not valid`);
        } finally {
            $this->urlRepository->saveUrls($this->urlsResults);
            return $this->urlsResults;
        }
    }

    function getContentFromUrl($url)
    {
        $client = new Client([
            'verify' => false
        ]);

        try {
            $response = $client->get($url);
            if ($response->getStatusCode() == 200) {
                $content = $response->getBody()->getContents();
                return $content;
            }
        } catch (\Exception $e) {
            Log::error('Error retrieving content: ' . $e->getMessage());
            return null;
        }
    }

    protected function extractLinks($html)
    {
        $links = [];
        $pattern = '/<a\s(?:.*?)href=[\'"]([^\'"]+)[\'"](.*?)>/i';

        preg_match_all($pattern, $html, $matches);


        foreach ($matches[1] as $url) {
            $links[] = $url;
        }

        return $links;
    }
}