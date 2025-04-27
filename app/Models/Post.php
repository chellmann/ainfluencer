<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use App\Models\Brand;
use Mateffy\Color;
use Illuminate\Support\Facades\Process;
use OpenAI\Laravel\Facades\OpenAI;

class Post extends Model
{
    protected $fillable = [
        'brand_id',
        'image',
        'text',
        'author',
        'font_color',
        'font_size',
        'font_style',
        'caption',
        'mp4',
        'svg',
        'rendered_at',
        'posted_at',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function generateVector(){
        // Logic to generate vector image

        //generate textblock
        $textblock = '';
        foreach (explode("\n",$this->text) as $line) {
            # code...
            $textblock .= '<tspan x="50%" dy="1.2em">'.$line.'</tspan>';
        }
        if($this->author != ""){
            $textblock .= '<tspan x="50%" dy="1.8em" font-size="'. $this->font_size/1.25 . '">          -  '. $this->author .'</tspan>';
        }

        $font_color = Color::hex($this->font_color);

        $svg = '<svg width="1080" height="1920" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <pattern id="bg" patternUnits="userSpaceOnUse" width="2000" height="1920">
      <image href="'.url($this->image). '" x="-10" y="0" height="1920" />
    </pattern>
  </defs>
  <rect width="200%" height="140%" fill="url(#bg)">
    <animateTransform attributeName="transform" type="translate" from="-30 0" to="0 0" dur="3s" repeatCount="indefinite" />
  </rect>

  <text x="50%" y="70%" text-anchor="middle" dominant-baseline="middle"  stroke="'. $font_color->invert()->toHexString(). '" opacity="75%" stroke-width="' . $this->font_size . '" stroke-linecap="round" stroke-linejoin="round" font-size="'.$this->font_size.'" >'. $textblock.'</text>
  <text x="50%" y="70%" text-anchor="middle" dominant-baseline="middle"   fill="'.$this->font_color.'" font-size="'.$this->font_size.'" >'. $textblock.'</text>
</svg>';

        //save svg to file
        $svg_path = 'posts/'.$this->id.'.svg';
        Storage::put($svg_path, $svg);
        $this->svg = $svg_path;
        $this->rendered_at = now();
        $this->save();
    }

    public function generateVideo(){
        // Logic to generate video from SVG
        // $svg_path = storage_path('app/private/'.$this->svg);
        $mp4_path = 'posts/'.$this->id.'.mp4';

        $result = Process::path(base_path(''))->timeout(5000)
            ->run('node node_modules/timecut/cli.js '. route('videoinput',$this->id). ' --launch-arguments="--no-sandbox" --viewport="1080,1920" --start-delay=1 --fps=30 --duration=4 --frame-cache --pix-fmt=yuv420p --screenshot-type=jpeg --output='. storage_path('app/public/' . $mp4_path));
            // ->run('npx vite-node src/main.ts  --input='. $svg_path.'  --duration=5  --fps=30');
        if ($result->failed()) {
            throw new \Exception('Failed to generate video: ' . $result->errorOutput());
        }
        // $result_move = Process::path(base_path('svg-animation-to-video'))->run('mv '.$this->id.'.mov '. storage_path('app/public/'.$mp4_path));
        // if($result_move->failed()) {
        //     throw new \Exception('Failed to move video: ' . $result_move->errorOutput());
        // }

        $this->mp4 = $mp4_path;
        $this->save();
    }

    public function generateCaption(){
        ray('generateCaption');
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4.1',
            'messages' => [
                ['role' => 'system', 'content' => 'Du bist ein Content Creator für Social Media und erstellt ein Video welches die Menschen zum nachdenken anregen soll. Das Video ist nur 3 Sekunden lang.
Antworte in JSON. Erstelle mir die Caption für den Post auf Instagram, verwende passende Hashtags (variable: caption). Erstelle mir einen Prompt zur KI Erstellung eines passenden Hintergrundbildes, dabei soll im unteren drittel des bildes keine wichtigen elemente platziert werden. (variable: prompt).'],
                ['role' => 'user', 'content' => 'Hier ist der Text: '. $this->text],
                ['role' => 'user', 'content' => 'Hier ist der Autor: '. $this->author],
            ],
        ]);
        ray($result);
        $json = json_decode($result->choices[0]->message->content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to decode JSON: ' . json_last_error_msg());
        }
        ray($json);
        $this->caption = $json['caption'];
        $this->save();
        $this->generateBackgroundWithAi($json['prompt']);
    }

    public function generateBackgroundWithAi($prompt){
        ray('generateBackgroundWithAi',$prompt);
        $result = OpenAI::images()->create([
            'prompt' => $prompt,
            'model' => 'gpt-image-1',
            'n' => 1,
            'size' => '1024x1536',
        ]);

        // ray($result);

        $image = $result->data[0]->b64_json;
        if ($image === null) {
            throw new \Exception('Failed to generate image: ' . $result->error);
        }
        $image = base64_decode($image);
        if ($image === false) {
            throw new \Exception('Failed to decode image ');
        }
        //check if image is valid
        $image_info = getimagesizefromstring($image);
        if ($image_info === false) {
            throw new \Exception('Invalid image data');
        }
        //save image to file
        $image_path = 'posts/bg_'.$this->id.'.png';
        Storage::disk('public')->put($image_path, $image);
        $this->image = $image_path;
        $this->save();
    }
}
