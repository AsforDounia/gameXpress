<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class CategoryTest extends TestCase
{

    public function test_a_category_can_be_created()
    {
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);
        $data = [
            'name' => 'Test Category',
            'slug' => 'test-category',
        ];

        $response = $this->postJson('/api/v1/admin/categories', $data);

        $response->assertStatus(201)->assertJsonStructure([
            'message',
            'category' => [
                'id', 'name', 'slug', 'created_at', 'updated_at',
            ]
        ]);
    }

    public function test_a_category_can_be_shown()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create();
        $response = $this->getJson('/api/v1/admin/categories/' . $category->id);

        $response->assertStatus(200)->assertJsonStructure([
            'category' => [
                'id', 'name', 'slug', 'created_at', 'updated_at',
            ]
        ]);
    }

    public function test_a_category_can_be_updated()
    {
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);
        $category = Category::factory()->create();
        $data = [
            'name' => 'Updated Category',
            'slug' => 'updated-category',
        ];

        $response = $this->putJson('/api/v1/admin/categories/' . $category->id, $data);

        $response->assertStatus(200)->assertJsonStructure([
            'message',
            'category' => [
                'id', 'name', 'slug', 'created_at', 'updated_at',
            ]
        ]);

        $category->refresh();
        $this->assertEquals( $data['name'], $category->name);
        $this->assertEquals( $data['slug'], $category->slug);
    }

    public function test_a_category_can_be_deleted()
    {
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);
        $category = Category::factory()->create();
        $response = $this->deleteJson('/api/v1/admin/categories/' . $category->id);

        $response->assertStatus(200)->assertJson(['message' => 'Category deleted successfully']);

        $this->assertDatabaseMissing('categories', ['id' => $category->id,]);
    }


}
