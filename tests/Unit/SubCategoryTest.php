<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Tests\TestCase;

class SubCategoryTest extends TestCase
{
    public function test_subcategories_can_be_listed_with_categories()
    {
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);

        $category = Category::factory()->create();
        Subcategory::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/v1/admin/subcategories');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'subcategories' => [
                         '*' => [
                             'id', 'name', 'category_id', 'created_at', 'updated_at',
                             'category' => ['id', 'name']
                         ]
                     ]
                 ]);
    }


    public function test_a_subcategory_can_be_created()
    {
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);

        $category = Category::factory()->create();

        $data = [
            'name' => 'Test Subcategory 6',
            'slug' => 'test-subcategory-6',
            'category_id' => $category->id
        ];

        $response = $this->postJson('/api/v1/admin/subcategories', $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'subcategory' => [
                        'id', 'name', 'slug', 'category_id', 'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('subcategories', [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'category_id' => $category->id
        ]);
    }


    public function test_a_subcategory_can_be_retrieved()
    {
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);

        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->getJson('/api/v1/admin/subcategories/' . $subcategory->id);

        $response->assertStatus(200)->assertJsonStructure([
                'subcategory' => [
                    'id', 'name', 'slug', 'category_id', 'created_at', 'updated_at',
                    'category' => ['id', 'name']
                ]
        ]);

        $this->assertEquals($subcategory->name, $response->json('subcategory.name'));
        $this->assertEquals($subcategory->slug, $response->json('subcategory.slug'));
        $this->assertEquals($subcategory->category_id, $response->json('subcategory.category_id'));
        $this->assertEquals($subcategory->category->name, $response->json('subcategory.category.name'));
    }


    public function test_a_subcategory_can_be_updated()
    {
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);

        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id
        ]);

        $data = [
            'name' => 'Updated Subcategory Name 1',
            'slug' => 'updated-subcategory-slug-1',
            'category_id' => $category->id,
        ];
        $response = $this->putJson('/api/v1/admin/subcategories/' . $subcategory->id, $data);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'subcategory' => [
                        'id', 'name', 'slug', 'category_id', 'created_at', 'updated_at'
                    ]
                ]);

        $subcategory->refresh();
        $this->assertEquals($data['name'], $subcategory->name);
        $this->assertEquals($data['slug'], $subcategory->slug);
        $this->assertEquals($data['category_id'], $subcategory->category_id);
    }



    public function test_a_subcategory_can_be_deleted()
{
    $user = User::factory()->create();
    $user->assignRole('product_manager');
    $this->actingAs($user);

    $category = Category::factory()->create();
    $subcategory = Subcategory::factory()->create([
        'category_id' => $category->id
    ]);

    $response = $this->deleteJson('/api/v1/admin/subcategories/' . $subcategory->id);
    $response->assertStatus(200)->assertJson([
        'message' => 'Subcategory deleted successfully'
    ]);

    $this->assertDatabaseMissing('subcategories', ['id' => $subcategory->id]);
}
}
