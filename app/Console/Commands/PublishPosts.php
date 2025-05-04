<?php

namespace App\Console\Commands;

use App\Jobs\UploadFacebookReel;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Models\Post;
use App\Models\PlatformPosts;
use App\Jobs\UploadInstagramReel;

class PublishPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'search for publishable posts and publish them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accounts = \App\Models\Account::get();

        foreach ($accounts as $account) {
            //liste der geplanten postings heute erstellen
            $scheduledTimes = collect($account->times)->map(function ($time) {
                return \Carbon\Carbon::createFromFormat('H:i', $time);
            })->sort();
            // ray(now()->format('H:i'));
            foreach ($scheduledTimes as $time) {
                // ray($time->format('H:i') . '?');
                if($time->isFuture() || $time->addMinutes(20)->isPast()){
                    continue;
                }
                // ray('yes');
                //is there a post scheduled for this time?
                $scheduledPost = PlatformPosts::where('account_id', $account->id)
                    ->where('planned_time', $time)
                    ->first();
                if ($scheduledPost) {
                    Log::debug('Post already scheduled for account ' . $account->id);
                    continue;
                }

                $post = Post::where('brand_id', $account->brand_id)
                    ->where('unblock_post', 1)
                    ->whereDoesntHave('platform_posts', function ($query) use ($account) {
                        $query->where('platform', $account->platform);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();
                if (!$post) {
                    Log::debug('No post to post found for account ' . $account->id);
                    continue;
                }

                $PlatformPost = PlatformPosts::create([
                    'post_id' => $post->id,
                    'account_id' => $account->id,
                    'platform' => $account->platform,
                    'planned_time' => $time,
                ]);
                // ray($PlatformPost);

                switch ($account->platform) {
                    case 'instagram':
                        UploadInstagramReel::dispatch($PlatformPost);
                    break;

                    // case 'facebook':
                    //     UploadFacebookReel::dispatch($PlatformPost);
                    //     break;

                    default:
                        Log::error('Dont know how to post on ' . $account->platform);
                        break;
                }

            }


            // //get last posted post
            // $last_post = Post::where('account_id', $account->id)->orderBy('created_at', 'desc')->first();
            // //get last matching post schedule from account
            // if(!in_array(now()->format('H'), $account->hours)){
            //     continue;
            // }

        }
    }
}
