<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Article;
use App\Models\Comment;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(2)->create([
            'password' => Hash::make('password123'),
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        Article::factory(2)->create()->each(function ($article) use ($users) {
            Comment::factory(3)->create([
                'article_id' => $article->id,
                'user_id' => $users->random()->id,
            ]);
        });
    }
}
