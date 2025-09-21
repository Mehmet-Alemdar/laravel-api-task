<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\JsonResponse;
use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;


class ArticleController extends Controller
{

    public function index(): JsonResponse
    {
        $perPage = request()->get('per_page', 10);
        $search  = request()->get('search');
        $from    = request()->get('from');
        $to      = request()->get('to');  

        $query = Article::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                ->orWhere('body', 'ILIKE', "%{$search}%");
            });
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $articles = $query->orderByDesc('created_at')->paginate($perPage);

        return ApiResponse::success([
            'items' => ArticleResource::collection($articles),
            'meta'  => PaginationHelper::format($articles),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        return ApiResponse::success(new ArticleResource($article));
    }
}