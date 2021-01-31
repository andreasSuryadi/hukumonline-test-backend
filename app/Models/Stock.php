<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'car_model_id',

        'stock_number',
        'stock_date',
        'ip_address',
    ];

    public function carModel()
    {
        return $this->belongsTo(CarModel::class);
    }
}
