<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    protected $fillable = [
        'name',
        'author',
        'description',
        'file',
        'start_time'
    ];

    public function brands(){
        return $this->belongsToMany(Brand::class);
    }
}
