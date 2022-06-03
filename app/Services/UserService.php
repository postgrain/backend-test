<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class UserService
{
    protected const BASE_URL = 'http://localhost:8000';

    /** @return array{email: string, isEmployee: bool} */
    public function getUser(string $email): ?array
    {
        try {
            $response = Http::get(self::BASE_URL . "/api/v1/user/$email");

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
