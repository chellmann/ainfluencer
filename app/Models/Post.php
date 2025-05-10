<?php

namespace App\Models;

use function Illuminate\Events\queueable;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use App\Models\PostImagealternatives;
use App\Models\PlatformPosts;
use App\Models\Music;
use App\Models\Imagemodel;
use App\Models\BrandMusic;
use App\Models\Brand;

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
        'music_id',
        'rendered_at',
        'posted_at',
        'image_prompt',
        'unblock_image',
        'unblock_video',
        'unblock_post',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function music(){
        return $this->belongsTo(Music::class);
    }

    public function platform_posts(){
        return $this->hasMany(PlatformPosts::class);
    }

    public function imageAlternatives(){
        return $this->hasMany(PostImagealternatives::class);
    }

    protected static function booted(): void
    {
        static::created(function (Post $model) {
            // Hier kannst du die Methode aufrufen, die du ausführen möchtest
            $model->formatText();
        });

        static::created(queueable(function (Post $post) {
            $post->generateCaption();
            $post->chooseMusic();
            // $post->formatText();
        }));
    }

    public function generateVideo(){
        if (!$this->unblock_video) {
            throw new \Exception('Video generation is blocked.');
        }
        // Logic to generate video from SVG
        $mp4_path = 'posts/'.$this->id.'.mp4';

        Process::path(base_path(''))->run('rm tmp.mp4');

        $command = 'node node_modules/timecut/cli.js ' . route('videoinput', $this->id) . ' --launch-arguments="' . env('TIMECUT_EXTRA', '') . '" --viewport="1080,1920" --start-delay=1 --fps=30 --duration=4 --frame-cache --output-options="-colorspace bt709 -c:v libx264" --pix-fmt=yuv420p --screenshot-type=jpeg --output=tmp.mp4';

        Log::debug("running command: $command");
        $result = Process::path(base_path(''))->timeout(5000)->run($command);
        Log::debug($result->output());
        Log::debug($result->errorOutput());
        ray($result);

        if($this->music){
            $music_path = storage_path('app/public/'.$this->music->file);
            if (!file_exists($music_path)) {
                throw new \Exception('Music file not found: ' . $music_path);
            }

            $command = 'ffmpeg -i tmp.mp4 ';

            if($this->music->start_time > 0){
                $command .= '-itsoffset -' . $this->music->start_time;
            }

            $command .=' -i ' . $music_path . ' -c copy -map 0:v:0 -map 1:a:0 -shortest -c:a aac -b:a 192k ' . storage_path('app/public/' . $mp4_path);

            Log::debug("running command: $command");
            $result = Process::path(base_path(''))->timeout(5000)->run($command);
            Log::debug($result->output());
            Log::debug($result->errorOutput());

            ray($result);
        }else{
            Process::path(base_path(''))->run('mv tmp.mp4 '. storage_path('app/public/' . $mp4_path));
        }

        if ($result->failed()) {
            throw new \Exception('Failed to generate video: ' . $result->errorOutput());
        }

        $this->mp4 = $mp4_path;
        $this->rendered_at = now();
        $this->save();
    }

    public function chooseMusic(){
        if ($this->music_id !== null) {
            return;
        }
        //check if brand has music
        if (BrandMusic::where('brand_id', $this->brand_id)->count() != 0) {
            //select random music from brand
            $this->music_id = BrandMusic::where('brand_id', $this->brand_id)->inRandomOrder()->first()->id;
        }

        $this->save();
    }

    public function formatText()
    {
        // Prüfen, ob der Text bereits Zeilenumbrüche enthält
        if (strpos($this->text, "\n") !== false) {
            return;
        }

        // Text in Wörter aufteilen
        $words = explode(' ', $this->text);

        // Text in 3 bis 4 Zeilen aufteilen
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            // Prüfen, ob das Hinzufügen des nächsten Wortes die Zeilenlänge überschreitet
            if (strlen($currentLine . ' ' . $word) > 25) { // 30 ist ein Beispielwert für die maximale Zeilenlänge
                $lines[] = trim($currentLine);
                $currentLine = $word;
            } else {
                $currentLine .= ' ' . $word;
            }
        }

        // Letzte Zeile hinzufügen
        if (!empty($currentLine)) {
            $lines[] = trim($currentLine);
        }

        // Zeilen mit Zeilenumbrüchen verbinden
        $this->text = implode("\n", $lines);

        // Änderungen speichern
        $this->save();
    }

    public function generateCaption(){
        if($this->caption != null && $this->image_prompt != null) {
            ray('Caption&Prompt already generated');
            return;
        }
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4.1',
            'messages' => [
                ['role' => 'system', 'content' => 'Du bist ein Content Creator für Social Media und erstellst ein Video. '.$this->brand->prompt_info.' Das Video ist nur 3 Sekunden lang. Antworte in JSON. Erstelle mir die Caption für den Post auf Instagram, verwende passende Hashtags (variable: caption). Erstelle mir einen Prompt zur KI Erstellung eines passenden Hintergrundbildes, dabei soll im unteren drittel des bildes keine wichtigen elemente platziert werden. (variable: prompt).'],
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
        if($this->caption == '') $this->caption = $json['caption'];
        if($this->image_prompt == '') $this->image_prompt = $json['prompt'];

        $this->save();
    }

    public function generateBackgroundWithAi(){
        if(!$this->unblock_image || $this->image_prompt == '') {
            return;
        }

        $image_path = 'posts/bg_' . $this->id . '.png';

        // $image = Imagemodel::find(2)->generateImage($this->image_prompt);
        $image = $this->brand->imagemodel->generateImage($this->image_prompt);

        if ($image === false) {
            throw new \Exception('Failed to decode image ');
        }
        //check if image is valid
        $image_info = getimagesizefromstring($image);
        if ($image_info === false) {
            throw new \Exception('Invalid image data');
        }
        //save image to file
        //delete old image
        if (Storage::disk('public')->exists($image_path)) {
            Storage::disk('public')->delete($image_path);
        }
        Storage::disk('public')->put($image_path, $image);
        $this->image = $image_path;
        $this->save();

    }

}
