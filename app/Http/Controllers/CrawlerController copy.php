<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;

class CrawlerController2 extends Controller
{
    public function crawl(Request $request)
    {
        $url = $request->input('url');
        $depth = $request->input('depth');

        $results = $this->crawlURL($url, $depth);

        return response()->json($results);
    }

    private function crawlURL($url, $depth, $currentDepth = 0)
    {
        $client = new \GuzzleHttp\Client();
        $crawler = $client->request('GET', $url);

        $response = $client->getResponse();

        $results = [];

        if ($response->isSuccessful()) {
            $title = $crawler->filter('title')->text();
            $results[] = ['url' => $url, 'title' => $title];

            if ($currentDepth < $depth) {
                $links = $crawler->filter('a')->extract(['href']);

                foreach ($links as $link) {
                    if (strpos($link, 'http') === 0 && !in_array($link, $results)) {
                        $subResults = $this->crawlURL($link, $depth, $currentDepth + 1);
                        $results = array_merge($results, $subResults);
                    }
                }
            }
        }

        return $results;
    }
}