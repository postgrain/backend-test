<?php

namespace App\Services;

use App\Http\Controllers\UsersController;
use Exception;

class UsersServices extends UsersController
{
    /**
     * Determines whether the user account not exists.
     *
     * @param string $userEmail
     *
     * @return bool
     *
     * @throws Exception
     */
    public function isNewUser(string $userEmail): bool
    {
        $requestInfo = $this->getInfo($userEmail);
        $message = $requestInfo['message'];

        if (self::USER_NOT_FOUND_MESSAGE === $message) {
            return true;
        }

        return false;
    }

    /**
     * Determines whether the user are an employee
     *
     * @param string $userEmail
     *
     * @return bool
     *
     * @throws Exception
     */
    public function isUserEmployee(string $userEmail): bool
    {
        $requestInfo = $this->getInfo($userEmail);
        $userData = $requestInfo['data'];

        if ($userData) {
            return (bool) $userData['isEmployee'];
        }

        return false;
    }

    /**
     * Get user information from external api.
     *
     * @param string $userEmail
     *
     * @return array{message: string, data: array<string, bool>
     * @throws Exception
     */
    public function getInfo(string $userEmail): array
    {
        return $this->getExternalInformation($userEmail);
    }
}
