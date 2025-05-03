<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class PlatformPosts extends Model
{
    protected $fillable = [
        'post_id',
        'account_id',
        'platform',
        'planned_time',
        'published_at',
        'foreign_id',
    ];

    protected $casts = [
        'planned_time' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
