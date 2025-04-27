<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Post;

class GenerateBackground implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Post $post,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->post->generateBackgroundWithAi();
        } catch (\Throwable $th) {
            // Handle the exception
            Log::error('Error generating video: ' . $th->getMessage());
            // Optionally, you can rethrow the exception if you want to retry the job
            $this->post->update([
                'unblock_image' => false
            ]);
            throw $th;
        }
    }
}
