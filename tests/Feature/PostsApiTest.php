<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_posts_when_empty(): void
    {
        $this->get(route('posts.index'))
            ->assertStatus(200)
            ->assertExactJson([
                'posts' => [],
            ]);
    }

    public function test_lists_all_posts(): void
    {
        $category = Category::factory()->create();
        Post::factory(10)
            ->published()
            ->create();
        $this->get(route('posts.index'))
            ->assertStatus(200)
            ->assertJsonCount(10, 'posts');
    }

    public function test_posts_are_ordered_by_date(): void
    {
        $category = Category::factory()->create();
        Post::factory(10)
            ->published()
            ->create();

        $posts = $this->get(route('posts.index'))
            ->assertStatus(200)
            ->assertJsonCount(10, 'posts')
            ->json('posts');

        $latestPostDate = null;
        foreach ($posts as $post) {
            if (!$latestPostDate) {
                $latestPostDate = Carbon::parse($post['published_at']);
                continue;
            }

            $this->assertTrue($latestPostDate->isAfter(Carbon::parse($post['published_at'])));
            $latestPostDate = Carbon::parse($post['published_at']);
        }
    }

    public function test_can_create_post()
    {
        $category = Category::factory()->create();
        $this->postJson(route('posts.store', [
            'name' => 'Post de prueba 1',
            'published_at' => now()->format('Y-m-d'),
            'content' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et',
            'categories' => [$category->id],
        ]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'post' => [
                    'id',
                    'name',
                    'content',
                    'published_at',
                ],
            ]);
    }

    public function test_cannot_create_post_with_wrong_data()
    {
        $category = Category::factory()->create();
        $this->postJson(route('posts.store', [
            'name' => 'Post de prueba 1',
            'published_at' => now()->format('Y-m-d'),
            'content' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et',
            'categories' => [1000],
        ]))
            ->assertStatus(422);
        $this->postJson(route('posts.store', []))->assertStatus(422);
        $this->postJson(route('posts.store', [
            'name' => 'Post de prueba 1',
            'published_at' => now()->format('Y-m-d'),
            'content' => 'Lor',
            'categories' => [$category->id],
        ]))
            ->assertStatus(422);
        $this->postJson(route('posts.store', [
            'name' => 'Post de prueba 1',
            'published_at' => 'no es una fecha',
            'content' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et',
            'categories' => [$category->id],
        ]))
            ->assertStatus(422);
    }

    public function test_can_update_post()
    {
        $category = Category::factory()
            ->has(Post::factory(), 'posts')
            ->create();
        $this->putJson(route('posts.update', ['post' => $category->posts->first()->id]), [
            'name' => 'Post de prueba 1',
            'published_at' => now()->format('Y-m-d'),
            'content' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et',
            'categories' => [$category->id],
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'post' => [
                    'id',
                    'name',
                    'content',
                    'published_at',
                ],
            ]);
    }
    public function test_cannot_update_post_with_wrong_data()
    {
        $category = Category::factory()
            ->has(Post::factory(), 'posts')
            ->create();
        $this->putJson(route('posts.update', ['post' => $category->posts->first()->id]), [
            'name' => '',
            'published_at' => now()->format('Y-m-d'),
            'content' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et',
            'categories' => [$category->id],
        ])->assertStatus(422);
        $this->putJson(route('posts.update', ['post' => $category->posts->first()->id]), [
            'name' => 'Mi post',
            'published_at' => 'test',
            'content' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et',
            'categories' => [$category->id],
        ])->assertStatus(422);
        $this->putJson(route('posts.update', ['post' => $category->posts->first()->id]), [
            'name' => 'Mi post',
            'published_at' => now()->format('Y-m-d'),
            'content' => 'Lor',
            'categories' => [$category->id],
        ])->assertStatus(422);
        $this->putJson(route('posts.update', ['post' => $category->posts->first()->id]), [
            'name' => 'Mi post',
            'published_at' => now()->format('Y-m-d'),
            'content' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et',
            'categories' => [0],
        ])->assertStatus(422);
    }

    public function test_can_delete_post()
    {
        $category = Category::factory()
            ->has(Post::factory(), 'posts')
            ->create();
        $this->deleteJson(route('posts.destroy', ['post' => $category->posts->first()->id]))
            ->assertStatus(200);
    }
    public function test_cannot_delete_non_existent_post()
    {
        $category = Category::factory()
            ->has(Post::factory(), 'posts')
            ->create();
        $this->deleteJson(route('posts.destroy', ['post' => $category->posts->first()->id]))
            ->assertStatus(200);
        $this->deleteJson(route('posts.destroy', ['post' => $category->posts->first()->id]))
            ->assertStatus(404);
        $this->deleteJson(route('posts.destroy', ['post' => 1000]))
            ->assertStatus(404);
    }
}
