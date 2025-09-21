<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Http\Resources\CommentResource;
use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\GlobalFilterRequest;

class CommentController extends Controller
{
    public function index(GlobalFilterRequest $request, string $articleId): JsonResponse
    {
        $filters = $request->filters(); 

        $query = Comment::where('article_id', $articleId);

        if ($filters['search']) {
            $query->where('content', 'ILIKE', "%{$filters['search']}%");
        }

        if ($filters['from']) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if ($filters['to']) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $comments = $query->orderByDesc('created_at')->paginate($filters['per_page']);

        return ApiResponse::success([
            'items' => CommentResource::collection($comments),
            'meta'  => PaginationHelper::format($comments),
        ]);
    }
}
