<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Request;

class UsersController extends Controller
{
    public const USER_NOT_FOUND_MESSAGE = 'Not Found.';
    public const SUCCESS_MESSAGE = 'Success.';
    public const INTERNAL_ERROR_MESSAGE = 'Internal server error.';
    public const EXTERNAL_API_URL = 'http://localhost:8000/api/v1/user/';

    /**
     * @throws ValidationException
     */
    public function information(Factory $validator, string $email): JsonResponse
    {
        $validator = $validator->make(compact('email'), ['email' => ['required', 'email']]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        /** @var array<string, bool> $employees */
        $employees = config('api.employees');

        abort_if(!array_key_exists($email, $employees), 404, self::USER_NOT_FOUND_MESSAGE);

        return new JsonResponse(
            [
                'message' => self::SUCCESS_MESSAGE,
                'data' => [
                    'email' => $email,
                    'isEmployee' => $employees[$email],
                ],
            ]
        );
    }

    /**
     * @param string $userEmail
     *
     * @return array{message: string, data: array<string, bool|string>}
     *
     * @throws Exception
     */
    public function getExternalInformation(string $userEmail): array
    {
        $request = Request::create(self::EXTERNAL_API_URL . $userEmail);
        $handleRequest = app()->handle($request);

        $statusCode = $handleRequest->getStatusCode();

        /** @var string $response */
        $response = $handleRequest->getContent();

        /** @var array{message: string, data: array<string, bool>} $parsedResponse */
        $parsedResponse = json_decode($response, true);

        switch ($statusCode) {
            case 200:
                return [
                    'message' => self::SUCCESS_MESSAGE,
                    'data' => $parsedResponse['data'],
                ];
            case 404:
                return [
                    'message' => self::USER_NOT_FOUND_MESSAGE,
                    'data' => [
                        'email' => $userEmail,
                        'isEmployee' => false,
                    ],
                ];
            default:
                return [
                    'message' => self::INTERNAL_ERROR_MESSAGE,
                    'data' => [
                        'email' => $userEmail,
                        'isEmployee' => false,
                    ],
                ];
        }
    }
}
