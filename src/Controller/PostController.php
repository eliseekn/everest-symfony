<?php

namespace App\Controller;

use DateTime;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Post;
use App\Service\FileUploader;
use App\Form\PostType;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    /**
     * @Route("/post/create", name="post.create")
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
     * @Route("/post/{slug}", name="post.index")
     */
    public function index(Request $request, PostRepository $postRepository, CommentRepository $commentRepository, string $slug): Response
    {
        $post = $postRepository->findOneBySlug($slug);
        $comments = $post->getComments();

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $comment->setCreatedAt(new DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Comment has been created successfully');

            return $this->redirectToRoute('post.index', ['slug' => $post->getSlug()]);
        }

        return $this->render('post.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/edit/{id}", name="post.edit")
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
     * @Route("/post/delete/{id}", name="post.delete")
     */
    public function delete(KernelInterface $kernelInterface, PostRepository $postRepository, int $id): Response
    {
        $post = $postRepository->find($id);

        try {
            unlink($kernelInterface->getProjectDir() . '/public/' . $post->getImage());
        } catch (Exception $e) {
            
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Post has been deleted successfully');

        return $this->redirectToRoute('dashboard');
    }
}

