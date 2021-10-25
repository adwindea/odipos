<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderLog extends Model
{
    use SoftDeletes;
    protected $table = 'order_logs';
    protected $hidden = ['id'];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function getQuantityAttribute(){
        return number_format($this->attributes['quantity'], 0, '','');
    }
}
