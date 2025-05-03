<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Brand;

class Account extends Model
{
    protected $fillable = [
        'brand_id',
        'handle',
        'platform',
        'foreign_id',
        'times',
    ];

    protected $casts = [
        'times' => 'array',
    ];


    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

}
