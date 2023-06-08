<?php

namespace App\Repositories;

use App\Models\Url;

class UrlRepository
{
    public function saveUrls($newUrls)
    {
        Url::insert($newUrls);
    }

    public function getUrlByKey($whereValue, $whereKey)
    {
        return Url::where([$whereKey => $whereValue])->get();
    }
}