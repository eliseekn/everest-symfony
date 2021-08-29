<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class PostController extends AbstractController
{
    /**
     * @Route("/post/{slug}", name="post.index")
     */
    public function index(PostRepository $postRepository, string $slug): Response
    {
        $post = $postRepository->findOneBySlug($slug);
        return $this->render('post.html.twig', compact('post'));
    }

}
