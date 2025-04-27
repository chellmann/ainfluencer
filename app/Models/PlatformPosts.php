<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class PlatformPosts extends Model
{
    protected $fillable = [
        'post_id',
        'platform',
        'foreign_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
