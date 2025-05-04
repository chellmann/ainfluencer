<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\PlatformPosts;

class UploadFacebookReel implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PlatformPosts $PlatformPost,
    ) {}

    public function handle()
    {
        $Post = $this->PlatformPost->post;
        if (!$Post->unblock_post) {
            throw new \Exception('Post is not unblock');
        }
        // ray($Post);
        //check if media and caption are givven
        if ($Post->mp4 == null || $Post->caption == null) {
            throw new \Exception('Media or caption is missing');
        }
        //check if brand has an account
        if ($Post->brand->accounts()->where('platform', 'facebook')->count() == 0) {
            throw new \Exception('Brand has no facebook account');
        }
        $account = $Post->brand->accounts()->where('platform', 'facebook')->first();

        //check if account is connected
        if ($account->foreign_id == null) {
            throw new \Exception('facebook account is not connected');
        }

        // Prepare argument array for Facebook API
        // pages_show_list, business_management, instagram_basic, instagram_manage_comments, instagram_manage_insights, instagram_content_publish, instagram_manage_messages, pages_read_engagement, pages_manage_posts
        $args = [
            'upload_phase' => 'start',
            'access_token' => env('FACEBOOK_TOKEN'),
        ];

        ray($args);
        Log::info("Starting video upload to Facebook", $args);

        // Step 1: Start the video upload session
        $startResponse = Http::post(env('FACEBOOK_PATH') . $account->foreign_id . '/video_reels', $args);
        if ($startResponse->failed()) {
            throw new \Exception('Facebook API request failed during upload start: ' . $startResponse->body());
        }

        $uploadSessionId = $startResponse->json('video_id');
        if (!$uploadSessionId) {
            throw new \Exception('Failed to start Facebook video upload session: ' . $startResponse->body());
        }

        // Step 2: Upload the video file
        $videoPath = storage_path('app/public/' . $Post->mp4);
        $uploadResponse = Http::post('https://rupload.facebook.com/video-upload/v22.0/' . $uploadSessionId, [
            'file_url' => url(' storage / '.$Post->mp4),
            'access_token' => env('FACEBOOK_TOKEN'),
        ]);

        if ($uploadResponse->failed()) {
            throw new \Exception('Facebook API request failed during video upload: ' . $uploadResponse->body());
        }
        //check if media container is ready
        $status = '';
        while ($status != 'FINISHED') {
            $status_request = Http::get(env('FACEBOOK_PATH') . $uploadSessionId . '?fields=status&access_token=' . env('FACEBOOK_TOKEN'));
            ray($status_request->json());
            // if ($status_request->json('status')['video_status'] == 'ERROR') {
            //     throw new \Exception('Instagram Media Container is not ready: ' . $status_request->body());
            // }
            $status = $status_request->json('status_code');
            Log::info("return from instagram status", $status_request->json());
            // ray($status_request->json());
            if ($status != 'FINISHED') {
                //wait 5 seconds before checking again
                sleep(5);
            }
        }

        // Step 3: Finish the video upload
        $finishResponse = Http::post(env('FACEBOOK_PATH') . $account->foreign_id . '/video_reels', [
            'upload_phase' => 'finish',
            'video_id' => $uploadSessionId,
            'video_state' => 'READY',
            'description' => $Post->caption,
            'access_token' => env('FACEBOOK_TOKEN'),
        ]);

        if ($finishResponse->failed()) {
            throw new \Exception('Facebook API request failed during upload finish: ' . $finishResponse->body());
        }

        if ($finishResponse->json('success') !== true) {
            throw new \Exception('Failed to finish Facebook video upload: ' . $finishResponse->body());
        }

        Log::info("Video uploaded to Facebook successfully", ['video_id' => $uploadSessionId]);

        // Step 4: Publish the video
        $publishArgs = [
            'video_id' => $uploadSessionId,
            'description' => $Post->caption,
            'access_token' => env('FACEBOOK_TOKEN'),
        ];

        $publishResponse = Http::post(env('FACEBOOK_PATH') . $account->foreign_id . '/feed', $publishArgs);
        if ($publishResponse->failed()) {
            throw new \Exception('Facebook API request failed during video publish: ' . $publishResponse->body());
        }

        Log::info("Video published to Facebook successfully", $publishResponse->json());

        // Set post as published
        $Post->posted_at = now();
        $Post->save();
    }
}
