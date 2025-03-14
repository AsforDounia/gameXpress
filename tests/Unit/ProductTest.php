<?php
namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Subcategory;
use App\Models\User;

class ProductTest extends TestCase
{
    public function test_product_index_returns_list_of_products()
    {

        Storage::fake('public');
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);

        $subcategory = Subcategory::factory()->create();
        $products = Product::factory()->count(3)->create([
            'subcategory_id' => $subcategory->id,
        ]);

        foreach ($products as $product) {
            $product->productImages()->create([
                'image_url' => UploadedFile::fake()->image('image.jpg')->store('product_images', 'public'),
                'is_primary' => true,
            ]);
        }

        $response = $this->getJson('/api/v1/admin/products');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'products' => [
                    '*' => [
                        'id', 'name', 'slug', 'price', 'stock', 'status', 'subcategory_id',
                        'subcategory' => [
                            'id', 'name',
                        ],
                        'product_images' => [
                            '*' => ['image_url', 'is_primary']
                        ]
                    ]
                ]
            ]);

    }

    public function test_a_product_can_be_created_with_images()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);
        $subcategory = Subcategory::factory()->create();

        $data = [
            'name' => 'Test Product 5',
            'slug' => 'test-product-5t',
            'price' => 199.99,
            'stock' => 10,
            'subcategory_id' => $subcategory->id,
            'primary_image' => UploadedFile::fake()->image('primary_image.jpg'),
            'images' => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg')
            ]
        ];

        $response = $this->postJson('/api/v1/admin/products', $data);


        $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'product' => [
                'id', 'name', 'slug', 'price', 'stock', 'status', 'subcategory_id',
                'product_images' => [
                    '*' => ['id', 'product_id', 'image_url', 'is_primary', 'created_at', 'updated_at']
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
        Storage::fake('public');
        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);

        $subcategory = Subcategory::factory()->create();
        $product = Product::factory()->create([
            'subcategory_id' => $subcategory->id
        ]);


        $images_deleted = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.jpg'),
        ];
        foreach ($images_deleted as $image) {
            $product->productImages()->create([
                'image_url' => $image->store('product_images', 'public'),
                'is_primary' => false,
            ]);
        }
        $images_deleted_url = $product->productImages->pluck('image_url')->toArray();
        $data = [
            'name' => 'Test Product 104',
            'slug' => 'test-product-104',
            'price' => 199.99,
            'stock' => 1,
            'subcategory_id' => $subcategory->id,
            'primary_image' => UploadedFile::fake()->image('primary_image.jpg'),
            'deleted_images' => $images_deleted_url,
            'images' => [
                UploadedFile::fake()->image('image3.jpg'),
                UploadedFile::fake()->image('image4.jpg')
            ]
        ];



        $response = $this->putJson('/api/v1/admin/products/' . $product->id, $data);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'product' => [
                        'id', 'name', 'slug', 'price', 'stock', 'status', 'subcategory_id',
                        'product_images' => [
                            '*' => ['image_url', 'is_primary']
                        ]
                    ]
                ]);
        $product->refresh();
        $this->assertEquals($product->name, $data['name']);
        $this->assertEquals($product->price, $data['price']);
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
