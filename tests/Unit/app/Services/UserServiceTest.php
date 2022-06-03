<?php

namespace Tests\Unit\App\Services;

use App\Services\UserService;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    public function testShouldReturnUserData(): void
    {
        // Set
        $userService = new UserService();
        $email = 'johndoe@pm.me';

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
