<?php

namespace App\Http\Requests;

use Tests\TestCase;

class CartDiscountRequestTest extends TestCase
{
    public function testShouldAuthorizeAnyRequest(): void
    {
        // Set
        $formRequest = CartDiscountRequest::create('/');

        // Actions
        $result = $formRequest->authorize();

        // Assertions
        $this->assertTrue($result);
    }

    public function testRules(): void
    {
        // Set
        $formRequest = CartDiscountRequest::create('/');

        // Actions
        $result = $formRequest->rules();

        // Assertions
        $this->assertNotEmpty($result);
    }
}
