<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
use App\Models\Account;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'prompt_info',
        'imagemodel_id',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function music(){
        return $this->belongsToMany(Music::class);
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function imagemodel()
    {
        return $this->belongsTo(ImageModel::class);
    }

}
