<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rawmat extends Model
{
    use SoftDeletes;
    protected $table = 'rawmats';
    protected $hidden = ['id'];
}
