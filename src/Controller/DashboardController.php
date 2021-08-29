<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\FileUploader;
use App\Service\Paginator;
use Symfony\Component\String\Slugger\SluggerInterface;
use DateTime;

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
     * @Route("/dashboard/post/edit/{id}", name="post.edit")
     */
    public function edit(Request $request, SluggerInterface $slugger, PostRepository $postRepository, FileUploader $fileUploader, int $id): Response
    {
        $post = $postRepository->find($id);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('file')->getData();
            
            if (!is_null($image)) {
                $filename = $fileUploader->upload($image);
                $post->setImage($filename);
            }

            $post->setSlug($slugger->slug($form->get('title')->getData(), '-', 'fr')->lower());
            $post->setCreatedAt(new DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post has been updated successfully');

            return $this->redirectToRoute('post.edit', ['id' => $post->getId()]);
        }

        return $this->render('dashboard/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post
        ]);
    }

    /**
     * @Route("/dashboard/post/create", name="post.create")
     */
    public function create(Request $request, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('file')->getData();
            $filename = $fileUploader->upload($image);

            $post->setImage($filename);
            $post->setSlug($slugger->slug($form->get('title')->getData(), '-', 'fr')->lower());
            $post->setCreatedAt(new DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post has been created successfully');

            return $this->redirectToRoute('post.create');
        }

        return $this->render('dashboard/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/post/delete/{id}", name="post.delete")
     */
    public function delete(PostRepository $postRepository, int $id): Response
    {
        $post = $postRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('dashboard');
    }

}
