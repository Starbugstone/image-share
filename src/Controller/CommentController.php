<?php

namespace App\Controller;

use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Comment routes now redirect to Vue.js frontend
 * All comment functionality should be handled by API endpoints in ApiController
 */
#[Route('/comments')]
#[IsGranted('ROLE_USER')]
class CommentController extends AbstractController
{
    #[Route('/add/{type}/{id}', name: 'app_comment_add', methods: ['POST'])]
    public function add(string $type, int $id): Response
    {
        // This should be handled by API endpoints
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['POST'])]
    public function edit(Comment $comment): Response
    {
        // This should be handled by API endpoints
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }

    #[Route('/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment): Response
    {
        // This should be handled by API endpoints
        // Redirect to Vue.js frontend
        return $this->redirectToRoute('app_spa');
    }
}