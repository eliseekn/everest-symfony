<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\PostRepository;
use App\Service\Paginator;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(Request $request, PostRepository $postRepository, Paginator $paginator): Response
    {
        $page = $request->query->getInt('page', 1);
        $posts = $postRepository->findAll();
        $paginator = $paginator->generate($posts, 2, $page);

        return $this->render('dashboard/index.html.twig', compact('paginator'));
    }

    /**
     * @Route("/dashboard/comments/{postId}", name="dashboard.comments")
     */
    public function comments(Request $request, CommentRepository $commentRepository, PostRepository $postRepository, Paginator $paginator, int $postId): Response
    {
        $page = $request->query->getInt('page', 1);
        $comments = $commentRepository->findByPost($postId);
        $post = $postRepository->find($postId);
        $paginator = $paginator->generate($comments, 2, $page);

        return $this->render('dashboard/comments.html.twig', compact('paginator', 'post'));
    }
}
