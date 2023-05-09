<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_categories_empty(): void
    {
        $this->get(route('categories.index'))
            ->assertStatus(200)
            ->assertExactJson([
                'categories' => []
            ]);
    }

    public function test_list_categories_returns_all_categories(): void
    {
        Category::factory(10)
            ->active()
            ->create();
        Category::factory(5)
            ->inactive()
            ->create();


        $this->get(route('categories.index'))
            ->assertStatus(200)
            ->assertJsonCount(10, 'categories')
            ->assertJsonStructure([
                'categories' => [
                    [
                        'name',
                        'id',
                        'is_active',
                        'published_posts_count',
                        'created_at',
                    ],
                ],
            ]);
    }

    public function test_show_category(): void
    {
        $category = Category::factory()
            ->active()
            ->has(Post::factory()->published())
            ->create();

        $this->get(route('categories.show', ['category' => $category->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'category' => [
                    'id',
                    'name',
                    'is_active',
                    'published_posts' => [
                        [
                            'id',
                            'name',
                            'published_at',
                            'word_count',
                        ]
                    ]
                ],
            ]);
    }

    public function test_show_missing_category(): void
    {
        $this->get(route('categories.show', ['category' => 999999]))
            ->assertStatus(404);
    }

    public function test_can_create_category(): void
    {
        $this->postJson(route('categories.store'), [
            'name' => 'Categoria 1',
            'is_active' => true,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'category' => [
                    'id',
                    'name',
                    'is_active',
                ],
            ]);
    }

    public function test_cannot_create_category_with_wrong_parameters(): void
    {
        $this->postJson(route('categories.store'), [])
            ->assertStatus(422);

        $this->postJson(route('categories.store'), [
            'name' => '',
            'is_active' => true,
        ])
            ->assertStatus(422);

        $this->postJson(route('categories.store'), [
            'name' => null,
            'is_active' => true,
        ])
            ->assertStatus(422);

        $this->postJson(route('categories.store'), [
            'name' => 10,
            'is_active' => true,
        ])
            ->assertStatus(422);

        $this->postJson(route('categories.store'), [
            'name' => 'Mi categoria 1',
            'is_active' => true,
        ])->assertStatus(200);
        $this->postJson(route('categories.store'), [
            'name' => 'Mi categoria 1',
            'is_active' => true,
        ])->assertStatus(422);
    }

    public function test_can_update_category(): void
    {
        $category = Category::factory()->create();
        $this->putJson(route('categories.update', ['category' => $category->id]), [
            'name' => 'Categoria 2',
            'is_active' => true,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'category' => [
                    'id',
                    'name',
                    'is_active',
                ],
            ]);

        $category->refresh();
        $this->assertEquals($category->name, 'Categoria 2');
    }

    public function test_cannot_update_category_with_wrong_parameters(): void
    {
        $category = Category::factory()->create();

        $this->putJson(route('categories.update', ['category' => $category->id]))
            ->assertStatus(422);

        $this->putJson(route('categories.update', ['category' => $category->id]), [
            'name' => '',
            'is_active' => true,
        ])
            ->assertStatus(422);

        $this->putJson(route('categories.update', ['category' => $category->id]), [
            'name' => null,
            'is_active' => true,
        ])
            ->assertStatus(422);

        $this->putJson(route('categories.update', ['category' => $category->id]), [
            'name' => 10,
            'is_active' => true,
        ])
            ->assertStatus(422);

        $this->putJson(route('categories.update', ['category' => $category->id]), [
            'name' => 'Mi categoria 2',
            'is_active' => true,
        ])->assertStatus(200);
        $this->putJson(route('categories.update', ['category' => $category->id]), [
            'name' => 'Mi categoria 2',
            'is_active' => true,
        ])->assertStatus(200);
    }

    public function test_can_delete_category()
    {
        $category = Category::factory()->create();
        $this->deleteJson(route('categories.destroy', ['category' => $category->id]))
            ->assertStatus(200)
            ->assertExactJson([
                'status' => 'success',
            ]);
    }

    public function test_cannot_delete_category_twice()
    {
        $category = Category::factory()->create();
        $this->deleteJson(route('categories.destroy', ['category' => $category->id]))
            ->assertStatus(200)
            ->assertExactJson([
                'status' => 'success',
            ]);

        $this->deleteJson(route('categories.destroy', ['category' => $category->id]))
            ->assertStatus(404);
    }

    public function test_cannot_delete_non_existent_category()
    {
        $this->deleteJson(route('categories.destroy', ['category' => 9999]))
            ->assertStatus(404);
    }
}
