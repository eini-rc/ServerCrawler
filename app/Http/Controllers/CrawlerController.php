<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CrawlerService;

class CrawlerController extends Controller
{
    private $crawlerService;

    public function __construct(CrawlerService $crawlerService)
    {
        $this->crawlerService = $crawlerService;
    }

    public function crawlAction(Request $request)
    {
        $url = $request->input('url');
        $depth = $request->input('depth');

        $results = $this->crawlerService->crawlUrlsAndSave($url, $depth);
        return response()->json($results);
    }
}