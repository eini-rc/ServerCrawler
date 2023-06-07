<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;


class Url extends Model
{

    protected $connection = 'mongodb';

    protected $table = 'Urls2';

    protected $fillable = ['url'];
}