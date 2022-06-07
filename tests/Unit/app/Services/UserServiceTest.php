<?php

namespace Tests\Unit\App\Services;

use Tests\TestCase;
use App\Services\UserService;
use Illuminate\Support\Facades\Http;

class UserServiceTest extends TestCase
{
    public function testShouldReturnUserData(): void
    {
        // Set
        $userService = new UserService();
        $email = 'johndoe@pm.me';
        Http::fake([
            "http://localhost:8000/api/v1/user/$email" => Http::response([
                'message' => 'Success.',
                'data' => [
                    'email' => $email,
                    'isEmployee' => false,
                ],
            ], 200),
        ]);

        // Actions
        $user = $userService->getUser($email);

        // Assertions
        $this->assertIsArray($user);
    }

    public function testShouldReturnNullUserNotFound(): void
    {
        // Set
        $userService = new UserService();
        $email = 'notfound@test.com';

        // Actions
        $user = $userService->getUser($email);

        // Assertions
        $this->assertNull($user);
    }
}
