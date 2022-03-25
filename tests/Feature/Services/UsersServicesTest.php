<?php

namespace Services;

use App\Services\UsersServices;
use Exception;
use Tests\TestCase;

class UsersServicesTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testShouldBeNewUser(): void
    {
        //Set
        $userEmail = 'runForestRun@gmail.com';
        $userServices = new UsersServices();
        $requestInfo = $userServices->getInfo($userEmail);
        $message = $requestInfo['message'];

        //Act
        $result = $message === $userServices::USER_NOT_FOUND_MESSAGE
            ? true
            : false;

        //Assert
        $this->assertTrue($result);
    }

    /**
     * @throws Exception
     */
    public function testShouldBeEmployeeUser(): void
    {
        $userEmail = 'boitata@boitata.com';
        $userServices = new UsersServices();
        $requestInfo = $userServices->getInfo($userEmail);
        $userData = $requestInfo['data'];

        //Act
        if ($userData) {
            $isEmployee = $userData['isEmployee'];
            $response = $isEmployee ? true : false;
        } else {
            $response = false;
        }


        //Assert
        $this->assertTrue($response);
    }

    /**
     * @throws Exception
     */
    public function testShouldGetUserInfo(): void
    {
        //Set
        $usersServices = new UsersServices();
        $email = 'boitata@boitata.com';


        //Act
        $response = $usersServices->getInfo($email);

        //Assert
        $expected = [
            'message' => 'Success.',
            'data' => [
                'email' => 'boitata@boitata.com',
                'isEmployee' => true,
            ],
        ];
        $this->assertEquals($expected, $response);
    }
}
