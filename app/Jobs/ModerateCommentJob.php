<?php

namespace App\Jobs;

use App\Models\Comment;
use App\Enums\CommentStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ModerateCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $commentId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $commentId)
    {
        $this->commentId = $commentId;
    }

    public function backoff(): array
    {
        return [1, 5, 10];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $comment = Comment::find($this->commentId);

        if (!$comment || $comment->status !== CommentStatus::Pending) {
            return;
        }

        $banned = explode(',', config('comments.banned_keywords', 'spam,badword'));
        $content = strtolower($comment->content);

        $hasBanned = collect($banned)->first(
            fn($word) => str_contains($content, trim($word))
        );

        $comment->status = $hasBanned
            ? CommentStatus::Rejected->value
            : CommentStatus::Published->value;

        $comment->save();

        if ($comment->status === CommentStatus::Published->value) {
            Cache::tags(["article:{$comment->article_id}"])->flush();
        }
    }
}
