<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{

    public function test_users_can_be_listed()
    {
        $userManager = User::factory()->create();
        $userManager->assignRole('user_manager');
        $this->actingAs($userManager);

        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/admin/users');
        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => ['id', 'name', 'email', 'created_at', 'updated_at']
                ]);
    }
    public function test_a_user_can_be_created()
    {
        $userManager = User::factory()->create();
        $userManager->assignRole('user_manager');
        $this->actingAs($userManager);

        $data = [
            "name" => "Test User 11",
            "email" => "testuser11@gmail.com",
            "password" => "password",
            "password_confirmation" => "password",
        ];

        $response = $this->postJson('/api/v1/admin/users', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'user' => [
                         'id', 'name', 'email', 'created_at', 'updated_at',
                     ]
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser11@gmail.com',
        ]);
    }

    public function test_a_user_can_be_shown()
    {
        $userManager = User::factory()->create();
        $userManager->assignRole('user_manager');
        $this->actingAs($userManager);

        $user = User::factory()->create();
        $response = $this->getJson('/api/v1/admin/users/' . $user->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id', 'name', 'email', 'created_at', 'updated_at',
                 ]);
    }

    public function test_a_user_can_be_updated()
    {
        $userManager = User::factory()->create();
        $userManager->assignRole('user_manager');
        $this->actingAs($userManager);

        $user = User::factory()->create();
        $data = [
            'name' => 'Updated User 8',
            'email' => 'updateduser8@gmail.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ];

        $response = $this->putJson('/api/v1/admin/users/' . $user->id, $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'User updated successfully',
                 ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Updated User 8',
            'email' => 'updateduser8@gmail.com',
        ]);
    }

    public function test_a_user_can_be_deleted()
    {
        $userManager = User::factory()->create();
        $userManager->assignRole('user_manager');
        $this->actingAs($userManager);

        $user = User::factory()->create();
        $response = $this->deleteJson('/api/v1/admin/users/' . $user->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'User deleted successfully',
                 ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

}
