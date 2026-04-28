<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Laundry;
use App\Models\PickupRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_delete_account_with_correct_password()
    {
        // Create a customer
        $customer = Customer::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // Create token
        $token = $customer->createToken('test-token')->plainTextToken;

        // Make delete request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/v1/account', [
            'password' => 'password123',
            'confirmation' => 'DELETE_MY_ACCOUNT',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Your account has been permanently deleted. We\'re sorry to see you go!',
                 ]);

        // Verify customer is deleted
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_cannot_delete_account_with_wrong_password()
    {
        $customer = Customer::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/v1/account', [
            'password' => 'wrongpassword',
            'confirmation' => 'DELETE_MY_ACCOUNT',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Password is incorrect',
                 ]);

        // Verify customer still exists
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_cannot_delete_account_without_confirmation()
    {
        $customer = Customer::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/v1/account', [
            'password' => 'password123',
            'confirmation' => 'WRONG_CONFIRMATION',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['confirmation']);

        // Verify customer still exists
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_cannot_delete_account_with_active_laundries()
    {
        $customer = Customer::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // Create an active laundry
        Laundry::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'processing',
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/v1/account', [
            'password' => 'password123',
            'confirmation' => 'DELETE_MY_ACCOUNT',
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Cannot delete account with active laundries. Please wait for all laundries to be completed or contact support.',
                 ]);

        // Verify customer still exists
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_cannot_delete_account_with_pending_pickups()
    {
        $customer = Customer::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // Create a pending pickup
        PickupRequest::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/v1/account', [
            'password' => 'password123',
            'confirmation' => 'DELETE_MY_ACCOUNT',
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Cannot delete account with pending pickup requests. Please wait for all pickups to be completed or contact support.',
                 ]);

        // Verify customer still exists
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_delete_account_requires_authentication()
    {
        $response = $this->deleteJson('/api/v1/account', [
            'password' => 'password123',
            'confirmation' => 'DELETE_MY_ACCOUNT',
        ]);

        $response->assertStatus(401);
    }
}