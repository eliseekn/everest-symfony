<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Service\Paginator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, PostRepository $postRepository, Paginator $paginator): Response
    {
        $page = $request->query->getInt('page', 1);
        $posts = $postRepository->findAll();
        $paginator = $paginator->generate($posts, 4, $page);

        return $this->render('home.html.twig', compact('paginator'));
    }
}
