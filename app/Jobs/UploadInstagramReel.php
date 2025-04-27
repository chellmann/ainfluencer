<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Post;

class UploadInstagramReel implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Post $post,
    ) {}

    public function handle()
    {
        if(!$this->post->unblock_post){
            throw new \Exception('Post is not unblock');
        }
        // ray($this->post);
        //check if media and caption are givven
        if($this->post->mp4 == null || $this->post->caption == null){
            throw new \Exception('Media or caption is missing');
        }
        //check if brand has an account
        if($this->post->brand->accounts()->where('platform', 'instagram')->count() == 0){
            throw new \Exception('Brand has no Instagram account');
        }
        $account = $this->post->brand->accounts()->where('platform', 'instagram')->first();

        //check if account is connected
        if($account->foreign_id == null){
            throw new \Exception('Instagram account is not connected');
        }

        //prepare argument array
        $args = [
            'media_type' => 'REELS',
            'video_url' => url('storage/'.$this->post->mp4),
            'caption' => $this->post->caption,
            'share_to_feed' => true,
            'access_token' => env('INSTAGRAM_TOKEN'),
        ];

        ray($args);

        $IG_MediaContainer = Http::post(env('FACEBOOK_PATH').$account->foreign_id.'/media', $args);
        //check if request was successful
        if($IG_MediaContainer->failed()){
            throw new \Exception('Instagram API request failed: ' . $IG_MediaContainer->body());
        }
        //check if media container was created
        if($IG_MediaContainer->json('id') == null){
            throw new \Exception('Instagram Media Container was not created: ' . $IG_MediaContainer->body());
        }
        // ray($IG_MediaContainer->json());

        //check if media container is ready
        $status = '';
        while($status != 'FINISHED'){
            $status_request = Http::get(env('FACEBOOK_PATH'). $IG_MediaContainer->json('id').'?fields=status_code,status&access_token=' . env('INSTAGRAM_TOKEN'));
            if($status_request->json('status_code') == 'ERROR'){
                throw new \Exception('Instagram Media Container is not ready: ' . $status_request->body());
            }
            $status = $status_request->json('status_code');
            // ray($status_request->json());
            if($status != 'FINISHED'){
                //wait 5 seconds before checking again
                sleep(5);
            }
        }
        //publish media container
        $args = [
            'creation_id' => $IG_MediaContainer->json('id'),
            'access_token' => env('INSTAGRAM_TOKEN'),
        ];
        $IG_MediaPublish = Http::post(env('FACEBOOK_PATH') . $account->foreign_id . '/media_publish', $args);
        //check if request was successful
        if($IG_MediaPublish->failed()){
            throw new \Exception('Instagram API request failed: ' . $IG_MediaPublish->body());
        }
        //check if media was published
        if($IG_MediaPublish->json('id') == null){
            throw new \Exception('Instagram Media was not published: ' . $IG_MediaPublish->body());
        }
        //set post as published
        $this->post->posted_at = now();
        $this->post->save();

        // ray($IG_MediaContainer->json());

    }
}
