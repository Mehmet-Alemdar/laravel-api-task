<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\JsonResponse;
use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use App\Http\Requests\GlobalFilterRequest;


class ArticleController extends Controller
{

    public function index(GlobalFilterRequest $request): JsonResponse
    {
        $filters = $request->filters(); 

        $query = Article::query();

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'ILIKE', "%{$filters['search']}%")
                ->orWhere('body', 'ILIKE', "%{$filters['search']}%");
            });
        }

        if ($filters['from']) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if ($filters['to']) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $articles = $query->paginate($filters['per_page']);

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