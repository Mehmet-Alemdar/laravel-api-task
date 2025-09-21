<?php

namespace Tests\Unit;

use App\Enums\CommentStatus;
use App\Http\Controllers\CommentController;
use App\Http\Requests\GlobalFilterRequest;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CommentCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_index_caches_results_on_first_call()
    {
        $this->markTestSkipped('Cache tags test requires Redis or Memcached driver');
    }

    public function test_comment_index_uses_cache_on_subsequent_calls()
    {
        $this->markTestSkipped('Cache tags test requires Redis or Memcached driver');
    }

    public function test_comment_index_uses_different_cache_keys_for_different_pages()
    {
        $this->markTestSkipped('Cache tags test requires Redis or Memcached driver');
    }

    public function test_cache_key_includes_article_id_and_page_number()
    {
        $this->markTestSkipped('Cache tags test requires Redis or Memcached driver');
    }

    public function test_cache_respects_configured_ttl()
    {
        $this->markTestSkipped('Cache tags test requires Redis or Memcached driver');
    }

    public function test_cache_uses_default_ttl_when_config_not_set()
    {
        $this->markTestSkipped('Cache tags test requires Redis or Memcached driver');
    }

    public function test_cache_flush_affects_only_specific_article()
    {
        $article1 = Article::factory()->create();
        $article2 = Article::factory()->create();

        Cache::tags(["article:{$article1->id}"])->put('test_key_1', 'data1', 60);
        Cache::tags(["article:{$article2->id}"])->put('test_key_2', 'data2', 60);

        Cache::tags(["article:{$article1->id}"])->flush();

        $this->assertNull(Cache::tags(["article:{$article1->id}"])->get('test_key_1'));
        $this->assertEquals('data2', Cache::tags(["article:{$article2->id}"])->get('test_key_2'));
    }

    public function test_cache_stores_paginated_results_correctly()
    {
        $article = Article::factory()->create();
        
        $comments = Comment::factory()->count(15)->create([
            'article_id' => $article->id,
            'status' => CommentStatus::Published
        ]);

        $controller = new CommentController();
        
        $request = new GlobalFilterRequest();
        $request->replace(['page' => 1, 'per_page' => 10]);
        
        $response = $controller->index($request, $article->id);
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('items', $responseData['data']);
        $this->assertArrayHasKey('meta', $responseData['data']);
        
        $meta = $responseData['data']['meta'];
        $this->assertEquals(1, $meta['current_page']);
        $this->assertEquals(10, $meta['per_page']);
        $this->assertEquals(15, $meta['total']);
        $this->assertEquals(2, $meta['last_page']);
        
        $this->assertCount(10, $responseData['data']['items']);
    }
}
