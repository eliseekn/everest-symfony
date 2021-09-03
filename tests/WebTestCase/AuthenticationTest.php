<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class AuthenticationTest extends WebTestCase
{
    public function testCanNotNavigateToDashboardIfNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        $this->assertResponseRedirects();
    }

    public function testCanNotNavigateToDashboardIfRoleIsUser(): void
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('user@mail.com');

        $client->loginUser($user);
        $client->request('GET', '/dashboard');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCanNavigateToDashboardIfRoleIsAdmin()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('admin@mail.com');

        $client->loginUser($user);
        $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();
    }
}
