<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use OpenAI\Laravel\Facades\OpenAI;

class Imagemodel extends Model
{
    protected $fillable = [
        'name',
        'company',

    ];

    public function postImageAlternatives()
    {
        return $this->hasMany(PostImagealternatives::class);
    }

    public function generateImage($prompt){

        if($this->company == 'OpenAI'){
            $result = OpenAI::images()->create([
                'prompt' => $prompt,
                'model' => $this->name,
                'n' => 1,
                'size' => '1024x1536',
            ]);
        }

        if($this->company == 'minimax'){
            $url = 'https://api.minimaxi.chat/v1/image_generation';
            $parameters = [
                'prompt' => $prompt.'. Do not show any text.',
                'model' => $this->name,
                'n' => 1,
                'width' => 1024,
                'height' => 1536,
                'response_format' => 'base64',
            ];
            // ray($parameters);
            $result = Http::withHeaders([
                'Authorization'=>'Bearer ' . env('MINIMAX_API_KEY')
            ])->timeout(300)->post($url, $parameters)->json();
        }
        // ray($result);

        $image = $result['data']['image_base64'][0];

        if ($image === null) {
            throw new \Exception('Failed to generate image: ' . $result);
        }

        return base64_decode($image);
    }
}
