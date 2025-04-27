<?php

namespace App\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Post;

class GenerateVideo implements ShouldQueue
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
        $this->post->generateVideo();
    }
}
