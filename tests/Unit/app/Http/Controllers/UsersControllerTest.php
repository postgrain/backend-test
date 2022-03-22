<?php

namespace Tests\Unit\app\Http\Controllers;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testShouldGetUserExternalInformation(): void
    {
        //Set
        $apiURL = 'http://localhost:8000/api/v1/user/';
        $userEmail = 'boitata@boitata.com';
        $request = Request::create($apiURL . $userEmail);

        //Act
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


        //Assertions
        $this->assertEquals([
            'message' => 'Success.',
            'data' => [
                'email' => $userEmail,
                'isEmployee' => true,
            ],
        ], $response);
    }
}
