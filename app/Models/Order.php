<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    protected $table = 'orders';
    protected $hidden = ['id'];

    function promotion() {
        return $this->belongsTo(Promotion::class);
    }
    function user() {
        return $this->belongsTo(User::class);
    }
    function getPriceTotalAttribute(){
        return number_format($this->attributes['price_total'], 0, '', '.');
    }
    function getDiscountAttribute(){
        return number_format($this->attributes['discount'], 0, '', '.');
    }
    function getFinalPriceAttribute(){
        return number_format($this->attributes['final_price'], 0, '', '.');
    }
}
