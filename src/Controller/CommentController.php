<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/delete/{id}/{postId}", name="comment.delete")
     */
    public function delete(CommentRepository $commentRepository, int $id, int $postId): Response
    {
        $commentRepository = $commentRepository->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($commentRepository);
        $entityManager->flush();

        return $this->redirectToRoute('dashboard.comments', ['postId' => $postId]);
    }
}
