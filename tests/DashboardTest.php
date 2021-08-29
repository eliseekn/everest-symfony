<?php

namespace App\Tests;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DashboardTest extends WebTestCase
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

    public function testCanStorePost()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('admin@mail.com');

        $client->loginUser($user);
        $client->request('GET', '/dashboard/post/create');

        $this->assertResponseIsSuccessful();

        $client->submitForm('Save', [
            'post[title]' => 'Lorem ipsum', 
            'post[content]' => 'Lorem ipsum dolor sit amet',
            'post[file]' => new UploadedFile(static::$kernel->getProjectDir() . '/public/uploads/img/post-image.svg', 'test-upload.svg')
        ]);

        $this->assertResponseRedirects('/dashboard/post/create');
    }

    public function testCanUpdatePost()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('admin@mail.com');

        $client->loginUser($user);
        $client->request('GET', '/dashboard/post/create');

        $postTitle = 'Lorem ipsum ' . rand(0, 100);

        $client->submitForm('Save', [
            'post[title]' => $postTitle, 
            'post[content]' => 'Lorem ipsum dolor sit amet',
            'post[file]' => new UploadedFile(static::$kernel->getProjectDir() . '/public/uploads/img/post-image.svg', 'test-upload.svg')
        ]);

        $postRepository = static::$container->get(PostRepository::class);
        $post = $postRepository->findOneBy(['title' => $postTitle]);

        $client->request('GET', '/dashboard/post/edit/' . $post->getId());
        $client->submitForm('Save', ['post[content]' => 'Excepteur sint occaecat cupidatat non proident']);

        $this->assertResponseRedirects('/dashboard/post/edit/' . $post->getId());
    }

    public function testCanDeletePost()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('admin@mail.com');

        $client->loginUser($user);
        $client->request('GET', '/dashboard/post/create');

        $postTitle = 'Lorem ipsum ' . rand(0, 100);

        $client->submitForm('Save', [
            'post[title]' => $postTitle, 
            'post[content]' => 'Lorem ipsum dolor sit amet',
            'post[file]' => new UploadedFile(static::$kernel->getProjectDir() . '/public/uploads/img/post-image.svg', 'test-upload.svg')
        ]);

        $postRepository = static::$container->get(PostRepository::class);
        $post = $postRepository->findOneBy(['title' => $postTitle]);

        $client->request('GET', '/dashboard/post/delete/' . $post->getId());
        $this->assertResponseRedirects('/dashboard');
    }
}
