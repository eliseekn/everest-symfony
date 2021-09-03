<?php

namespace App\Tests;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CommentTest extends WebTestCase
{
    public function testCanStoreComment()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('admin@mail.com');

        $client->loginUser($user);
        $client->request('GET', '/post/create');

        $postTitle = 'Lorem ipsum ' . rand(0, 100);

        $client->submitForm('Save', [
            'post[title]' => $postTitle, 
            'post[content]' => 'Lorem ipsum dolor sit amet',
            'post[file]' => new UploadedFile(static::$kernel->getProjectDir() . '/public/uploads/img/post-image.svg', 'test-upload.svg')
        ]);

        $postRepository = static::$container->get(PostRepository::class);
        $post = $postRepository->findOneBy(['title' => $postTitle]);

        $client->request('GET', "/post/{$post->getSlug()}");

        $client->submitForm('Save', [
            'comment[author]' => 'test@test.test', 
            'comment[content]' => 'Lorem ipsum dolor sit amet',
        ]);

        $this->assertResponseRedirects("/post/{$post->getSlug()}");
    }

    public function testCanDeleteComment()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('admin@mail.com');

        $client->loginUser($user);
        $client->request('GET', '/post/create');

        $postTitle = 'Lorem ipsum ' . rand(0, 100);

        $client->submitForm('Save', [
            'post[title]' => $postTitle, 
            'post[content]' => 'Lorem ipsum dolor sit amet',
            'post[file]' => new UploadedFile(static::$kernel->getProjectDir() . '/public/uploads/img/post-image.svg', 'test-upload.svg')
        ]);

        $postRepository = static::$container->get(PostRepository::class);
        $post = $postRepository->findOneBy(['title' => $postTitle]);

        $client->request('GET', "/post/{$post->getSlug()}");

        $client->submitForm('Save', [
            'comment[author]' => 'test@test.test', 
            'comment[content]' => 'Lorem ipsum dolor sit amet',
        ]);

        $commentRepository = static::$container->get(CommentRepository::class);
        $comment = $commentRepository->findOneBy(['author' => 'test@test.test']);

        $client->request('GET', "/comment/delete/{$comment->getId()}/{$post->getId()}");
        $this->assertResponseRedirects("/dashboard/comments/{$post->getId()}");
    }
}
