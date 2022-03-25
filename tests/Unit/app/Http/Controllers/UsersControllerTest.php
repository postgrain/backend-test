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
        $userEmail = 'boitata@boitata.com';
        $request = Request::create('http://localhost:8000/api/v1/user/' . $userEmail);
        $handleRequest = app()->handle($request);

        $statusCode = $handleRequest->getStatusCode();

        /** @var string $response */
        $response = $handleRequest->getContent();

        /** @var array{message: string, data: array<string, bool>} $parsedResponse */
        $parsedResponse = json_decode($response, true);

        $expected = [
            'message' => 'Success.',
            'data' => [
                'email' => 'boitata@boitata.com',
                'isEmployee' => true,
            ],
        ];
        $this->assertEquals($expected, $parsedResponse);
        $this->assertIsInt($statusCode);
    }
}
