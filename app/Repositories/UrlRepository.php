<?php

namespace App\Repositories;

use MongoDB\Client as MongoDBClient;

use App\Models\Url;

class UrlRepository
{
    public function saveUrls($newUrls)
    {
        Url::create([$newUrls]);
    }
}