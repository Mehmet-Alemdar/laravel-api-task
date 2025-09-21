<?php

namespace Tests\Unit;

use App\Enums\CommentStatus;
use App\Jobs\ModerateCommentJob;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ModerateCommentJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderate_comment_job_sets_status_to_published_for_clean_content()
    {
        $comment = Comment::factory()->create([
            'content' => 'This is a clean and appropriate comment',
            'status' => CommentStatus::Pending
        ]);

        $job = new ModerateCommentJob($comment->id);
        $job->handle();

        $comment->refresh();
        $this->assertEquals(CommentStatus::Published, $comment->status);
    }

    public function test_moderate_comment_job_sets_status_to_rejected_for_banned_keywords()
    {
        config(['comments.banned_keywords' => 'spam,badword,offensive']);

        $comment = Comment::factory()->create([
            'content' => 'This comment contains spam content',
            'status' => CommentStatus::Pending
        ]);

        $job = new ModerateCommentJob($comment->id);
        $job->handle();

        $comment->refresh();
        $this->assertEquals(CommentStatus::Rejected, $comment->status);
    }

    public function test_moderate_comment_job_detects_banned_words_case_insensitively()
    {
        config(['comments.banned_keywords' => 'spam,badword']);

        $comment = Comment::factory()->create([
            'content' => 'This comment contains SPAM in uppercase',
            'status' => CommentStatus::Pending
        ]);

        $job = new ModerateCommentJob($comment->id);
        $job->handle();

        $comment->refresh();
        $this->assertEquals(CommentStatus::Rejected, $comment->status);
    }

    public function test_moderate_comment_job_handles_multiple_banned_keywords()
    {
        config(['comments.banned_keywords' => 'spam,badword,offensive,inappropriate']);

        $comment1 = Comment::factory()->create([
            'content' => 'This is spam content',
            'status' => CommentStatus::Pending
        ]);

        $comment2 = Comment::factory()->create([
            'content' => 'This is inappropriate content',
            'status' => CommentStatus::Pending
        ]);

        $job1 = new ModerateCommentJob($comment1->id);
        $job1->handle();

        $job2 = new ModerateCommentJob($comment2->id);
        $job2->handle();

        $comment1->refresh();
        $comment2->refresh();

        $this->assertEquals(CommentStatus::Rejected, $comment1->status);
        $this->assertEquals(CommentStatus::Rejected, $comment2->status);
    }

    public function test_moderate_comment_job_handles_partial_word_matches()
    {
        config(['comments.banned_keywords' => 'spam']);

        $comment = Comment::factory()->create([
            'content' => 'This is spammy content but contains the word',
            'status' => CommentStatus::Pending
        ]);

        $job = new ModerateCommentJob($comment->id);
        $job->handle();

        $comment->refresh();
        $this->assertEquals(CommentStatus::Rejected, $comment->status);
    }

    public function test_moderate_comment_job_ignores_non_pending_comments()
    {
        $publishedComment = Comment::factory()->create([
            'content' => 'This comment contains spam',
            'status' => CommentStatus::Published
        ]);

        $rejectedComment = Comment::factory()->create([
            'content' => 'This comment contains spam',
            'status' => CommentStatus::Rejected
        ]);

        $job1 = new ModerateCommentJob($publishedComment->id);
        $job1->handle();

        $job2 = new ModerateCommentJob($rejectedComment->id);
        $job2->handle();

        $publishedComment->refresh();
        $rejectedComment->refresh();

        $this->assertEquals(CommentStatus::Published, $publishedComment->status);
        $this->assertEquals(CommentStatus::Rejected, $rejectedComment->status);
    }

    public function test_moderate_comment_job_handles_non_existent_comment()
    {
        $nonExistentId = 'non-existent-uuid';

        $job = new ModerateCommentJob($nonExistentId);
        
        $this->expectNotToPerformAssertions();
        $job->handle();
    }

    public function test_moderate_comment_job_flushes_cache_only_for_published_comments()
    {
        $this->markTestSkipped('Cache tags test requires Redis or Memcached driver');
    }

    public function test_moderate_comment_job_handles_empty_banned_keywords_config()
    {
        config(['comments.banned_keywords' => '']);

        $comment = Comment::factory()->create([
            'content' => 'This comment should be published',
            'status' => CommentStatus::Pending
        ]);

        $job = new ModerateCommentJob($comment->id);
        $job->handle();

        $comment->refresh();
        $this->assertEquals(CommentStatus::Published, $comment->status);
    }

    public function test_moderate_comment_job_handles_whitespace_in_banned_keywords()
    {
        config(['comments.banned_keywords' => ' spam , badword , offensive ']);

        $comment = Comment::factory()->create([
            'content' => 'This comment contains badword',
            'status' => CommentStatus::Pending
        ]);

        $job = new ModerateCommentJob($comment->id);
        $job->handle();

        $comment->refresh();
        $this->assertEquals(CommentStatus::Rejected, $comment->status);
    }

    public function test_moderate_comment_job_has_correct_backoff_strategy()
    {
        $job = new ModerateCommentJob('test-id');
        
        $backoff = $job->backoff();
        
        $this->assertEquals([1, 5, 10], $backoff);
    }
}
