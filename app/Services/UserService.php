<?php

namespace App\Services;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;

class UserService
{
    /** @return array{email: string, isEmployee: bool} */
    public function getUser(string $email): ?array
    {
        $response = Http::get(URL::to("/api/v1/user/$email"));

        if (404 === $response->status()) {
            return null;
        }

        /** @var array{message: string, data: array{email: string, isEmployee: bool}} $data */
        $data = $response->json();

        return $data['data'];
    }
}
