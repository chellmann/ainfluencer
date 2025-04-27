<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Account;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'prompt_info'
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
