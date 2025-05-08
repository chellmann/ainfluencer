<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostImagealternatives extends Model
{
    protected $fillable = [
        'post_id',
        'imagemodel_id',
        'image',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function imagemodel()
    {
        return $this->belongsTo(Imagemodel::class);
    }
}
