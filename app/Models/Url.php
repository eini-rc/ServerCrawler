<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;


class Url extends Model
{

    protected $connection = 'mongodb';

    protected $collection = 'Urls';

    protected $fillable = ['url', 'parent_url'];

    protected $indexes = [
        ['key' => ['url' => 1], 'unique' => true],
    ];
}