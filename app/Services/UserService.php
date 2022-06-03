<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class UserService
{
    /** @return array{email: string, isEmployee: bool} */
    public function getUser(string $email): ?array
    {
        $response = Http::get(route('api.v1.users.email', ['email' => $email]));

        if (200 != $response->status()) {
            return null;
        }

        /** @var array{message: string, data: array{email: string, isEmployee: bool}} $data */
        $data = $response->json();

        return $data['data'];
    }
}
