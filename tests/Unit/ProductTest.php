<?php
namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;

class ProductTest extends TestCase
{

    public function test_a_product_can_be_created_with_images()
    {
        // Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);
        $subcategory = Subcategory::factory()->create();
        $data = [
            'name' => 'Test Product',
            'slug' => 'test-product-6',
            'price' => 199.99,
            'stock' => 10,
            'status' => "available",
            'subcategory_id' => $subcategory->id,
            'images' => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg')
            ]
        ];

        $response = $this->postJson('/api/v1/admin/products', $data);

        $response->assertJsonStructure([
            'message',
            'product' => [
                'id', 'name', 'slug', 'price', 'stock', 'status', 'subcategory_id',
                'product_images' => [
                    '*' => ['image_url', 'is_primary']
                ]
            ]
        ]);

        $product = Product::first();
        foreach ($product->productImages as $image) {
            Storage::disk('public')->assertExists($image->image_path);
        }
    }

    public function test_a_product_can_be_shown()
    {
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);

        $subcategory = Subcategory::factory()->create();
        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id
        ]);

        $response = $this->getJson('/api/v1/admin/products/' . $product->id);
        $response->assertStatus(200)->assertJsonStructure([
            'product' => [
                'id', 'name', 'slug', 'price', 'stock', 'status', 'subcategory_id',
                'product_images' => [
                    '*' => ['image_url', 'is_primary']
                ]
            ]
        ]);
    }

    public function test_a_product_can_be_updated()
    {
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);

        $subcategory = Subcategory::factory()->create();
        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id
        ]);

        $data = [
            'name' => 'Updated Product',
            'slug' => 'updated-product',
            'price' => 299.99,
            'stock' => 20,
            'status' => "out_of_stock",
            'subcategory_id' => $subcategory->id
        ];

        $response = $this->putJson('/api/v1/admin/products/' . $product->id, $data);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'product' => [
                        'id', 'name', 'slug', 'price', 'stock', 'status', 'subcategory_id'
                    ]
                ]);
        $product->refresh();
        $this->assertEquals($product->name, $data['name']);
        $this->assertEquals($product->price, $data['price']);
        $this->assertEquals($product->status, $data['status']);
    }


    public function test_a_product_can_be_destroyed()
    {
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);

        $subcategory = Subcategory::factory()->create();
        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id
        ]);

        $response = $this->deleteJson('/api/v1/admin/products/' . $product->id);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Product deleted successfully'
                ]);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }


}
