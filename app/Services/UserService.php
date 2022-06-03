<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class UserService
{
    /** @return array{email: string, isEmployee: bool} */
    public function getUser(string $email): ?array
    {
        try {
            $response = Http::get(route('api.v1.users.email', ['email' => $email]));

            if (!$response->successful()) {
                $response->throw();
            }

            /** @var array{message: string, data: array{email: string, isEmployee: bool}} $data */
            $data = $response->json();

            return $data['data'];
        } catch (Exception $e) {
            return null;
        }
    }
}
