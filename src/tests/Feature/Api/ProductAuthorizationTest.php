<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_creation_requires_authentication(): void
    {
        $response = $this->postJson('/api/products', [
            'title' => 'Awesome T-Shirt',
            'price' => 99.99,
            'category' => 'Clothes',
            'images' => ['https://placehold.co/640x480'],
        ]);

        $response->assertUnauthorized();
    }

    public function test_product_update_requires_authentication(): void
    {
        $response = $this->putJson('/api/products/999999', [
            'title' => 'Updated Product',
        ]);

        $response->assertUnauthorized();
    }

    public function test_product_deletion_requires_authentication(): void
    {
        $response = $this->deleteJson('/api/products/999999');

        $response->assertUnauthorized();
    }
}
