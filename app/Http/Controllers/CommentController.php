<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Http\Resources\CommentResource;
use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\GlobalFilterRequest;
use App\Http\Requests\StoreCommentRequest;
use Illuminate\Support\Facades\Auth;
use App\Enums\CommentStatus;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ModerateCommentJob;

class CommentController extends Controller
{
    public function index(GlobalFilterRequest $request, string $articleId): JsonResponse
    {
        $filters = $request->filters(); 
        $page    = $filters['page'];
        $perPage = $filters['per_page'];

        $key = "comments:article:{$articleId}:page:{$page}";
        $ttl = config('comments.cache_ttl', 60);

        $comments = Cache::tags(["article:{$articleId}"])->remember(
            $key,
            $ttl,
            function () use ($articleId, $perPage) {
                return Comment::where('article_id', $articleId)
                    ->orderByDesc('created_at')
                    ->paginate($perPage);
            }
        );

        return ApiResponse::success([
            'items' => CommentResource::collection($comments),
            'meta'  => PaginationHelper::format($comments),
        ]);
    }

    public function store(StoreCommentRequest $request, string $articleId)
    {
        $comment = Comment::create([
            'article_id' => $articleId,
            'user_id'    => Auth::id(),
            'content'    => $request->input('content'),
            'status'     => CommentStatus::Pending->value,
        ]);

        ModerateCommentJob::dispatch($comment->id);

        return ApiResponse::success(
            ['comment_id' => $comment->id],
            'Comment queued for moderation',
            202
        );
    }
}
