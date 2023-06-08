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
    protected $shouldCrawler = false;
    protected $depthToContinue = null;
    protected $prevUrls = [];

    public function __construct(UrlRepository $urlRepository, Client $httpClient)
    {
        $this->urlRepository = $urlRepository;
        $this->httpClient = $httpClient;
    }

    public function crawlUrlsAndSave($url, $depth, $parentUrl = null)
    {
        $savedSubUrls = $this->crawlUrl($url, $depth, $parentUrl);

        // remove duplicate that saved in db
        $urlsForSave = $this->diffArraysByKey($this->urlsResults, $savedSubUrls);

        // remove duplicate if website has duplicate url
        $urlsForSave = $this->removeDuplicatesByKey($urlsForSave, 'url');

        if (count($urlsForSave)) {
            $this->urlRepository->saveUrls($urlsForSave);
        }
        return $this->urlsResults;
    }
    protected function removeDuplicatesByKey($array, $key)
    {
        return array_values(array_reduce($array, function ($carry, $item) use ($key) {
            $value = $item[$key];
            if (!isset($carry[$value])) {
                $carry[$value] = $item;
            }
            return $carry;
        }, []));
    }
    protected function crawlUrl($url, $depth, $parentUrl = null)
    {
        if (!$depth) {
            $this->urlsResults[] = ['url' => $url, 'parent_url' => $parentUrl];
            return [];
        }
        $savedSubUrls = $this->getSavedSubUrls($url, 0, $depth);

        if (count($savedSubUrls)) {
            // check if all depth found from db
            if (!$savedSubUrls['shouldCrawler']) {
                $this->urlsResults = $savedSubUrls['subUrls'];
                return $savedSubUrls['subUrls'];
            }
            // continue from depth that not save
            $depth = $savedSubUrls['depthToContinue'] ?? $depth;
        }

        try {
            $content = $this->getContentFromUrl($url);
            $this->urlsResults[] = ['url' => $url, 'parent_url' => $parentUrl];

            if ($depth > 1) {
                $subUrlsFromContent = $this->extractLinks($content);
                foreach ($subUrlsFromContent as $subUrl) {
                    $this->crawlUrl($subUrl, $depth - 1, $url); // Pass the current URL as the parent URL
                }
            }
        } catch (GuzzleException $e) {
            Log::error(`URL not valid`);
        } finally {
            return $savedSubUrls['subUrls'];
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

    protected function getSavedSubUrls($parentUrl, $depth, $orginalDepth)
    {
        if ($depth === $orginalDepth) {
            return ['subUrls' => $this->prevUrls, 'shouldCrawler' => false];

        }

        $subUrls = $this->urlRepository->getUrlByKey($parentUrl, $depth === 0 ? 'url' : 'parent_url');

        if (!count($subUrls)) {
            $this->shouldCrawler = true;
            $this->depthToContinue = $depth + 1;
        }

        foreach ($subUrls as $subUrl) {
            $this->prevUrls[] = $subUrl;
            $this->getSavedSubUrls($subUrl->url, $depth + 1, $orginalDepth);
        }

        return ['subUrls' => $this->prevUrls, 'shouldCrawler' => $this->shouldCrawler, 'depthToContinue' => $this->depthToContinue];
    }

    function diffArraysByKey(array $mainArray, array $itemsToRemove, string $key = 'url'): array
    {
        $keys = array_map('json_encode', array_column($itemsToRemove, $key));
        $filteredArr = array_filter($mainArray, function ($item) use ($keys, $key) {
            return !in_array(json_encode($item[$key]), $keys);
        });

        return array_values($filteredArr);
    }
}