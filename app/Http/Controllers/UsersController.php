<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Request;

class UsersController extends Controller
{
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

        abort_if(!array_key_exists($email, $employees), 404, 'Not Found.');

        return new JsonResponse(
            [
                'message' => 'Success.',
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
        $apiURL = 'http://localhost:8000/api/v1/user/';

        //Creates a new request.
        $request = Request::create($apiURL . $userEmail);

        //Dispatch the created request.
        $handleRequest = app()->handle($request);

        $statusCode = $handleRequest->getStatusCode();
        /** @var string $response */
        $response = $handleRequest->getContent();
        /** @var array{message: string, data: array<string, bool>} $parsedResponse */
        $parsedResponse = json_decode($response, true);

        if (200 === $statusCode) {
            $response =
                [
                    'message' => $parsedResponse['message'],
                    'data' => $parsedResponse['data'],
                ];
        } else {
            $response = 404 === $statusCode
                ? ['message' => 'Not Found.', 'data' => ['email' => $userEmail, 'isEmployee' => false]]
                : ['message' => 'Internal server error.', 'data' => ['email' => $userEmail, 'isEmployee' => false]];
        }

        return $response;
    }
}
