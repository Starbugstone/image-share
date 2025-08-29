<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Share;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comments')]
#[IsGranted('ROLE_USER')]
class CommentController extends AbstractController
{
    #[Route('/add/{type}/{id}', name: 'app_comment_add', methods: ['POST'])]
    public function add(
        string $type,
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository
    ): Response {
        if (!$this->isCsrfTokenValid('add-comment', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $content = $request->request->get('content');
        if (empty(trim($content))) {
            $this->addFlash('error', 'Comment cannot be empty.');
            return $this->redirect($request->headers->get('referer', $this->generateUrl('app_dashboard')));
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setAuthor($this->getUser());

        if ($type === 'image') {
            $image = $entityManager->getRepository(Image::class)->find($id);
            if (!$image) {
                throw $this->createNotFoundException('Image not found.');
            }

            // Check if user can comment on this image
            $canComment = $image->getUser() === $this->getUser();

            if (!$canComment) {
                // Check if image is shared with user
                foreach ($image->getShares() as $share) {
                    if ($share->getSharedWith() === $this->getUser()) {
                        $canComment = true;
                        break;
                    }
                }
            }

            if (!$canComment) {
                throw $this->createAccessDeniedException('You cannot comment on this image.');
            }

            $comment->setImage($image);
            $redirectUrl = $this->generateUrl('app_image_show', ['id' => $image->getId()]);

        } elseif ($type === 'share') {
            $share = $entityManager->getRepository(Share::class)->find($id);
            if (!$share) {
                throw $this->createNotFoundException('Share not found.');
            }

            // Check if user can comment on this share
            $canComment = $share->getSharedBy() === $this->getUser() || $share->getSharedWith() === $this->getUser();

            if (!$canComment) {
                throw $this->createAccessDeniedException('You cannot comment on this share.');
            }

            $comment->setShare($share);

            // Redirect to the shared item
            $sharedItem = $share->getSharedItem();
            if ($sharedItem instanceof Image) {
                $redirectUrl = $this->generateUrl('app_image_show', ['id' => $sharedItem->getId()]);
            } else {
                $redirectUrl = $this->generateUrl('app_album_show', ['id' => $sharedItem->getId()]);
            }

        } else {
            throw $this->createNotFoundException('Invalid type.');
        }

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Comment added successfully!');

        return $this->redirect($redirectUrl);
    }

    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['POST'])]
    public function edit(
        Comment $comment,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Only author can edit
        if ($comment->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot edit this comment.');
        }

        if (!$this->isCsrfTokenValid('edit-comment' . $comment->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $content = $request->request->get('content');
        if (empty(trim($content))) {
            $this->addFlash('error', 'Comment cannot be empty.');
        } else {
            $comment->setContent($content);
            $entityManager->flush();
            $this->addFlash('success', 'Comment updated successfully!');
        }

        return $this->redirect($request->headers->get('referer', $this->generateUrl('app_dashboard')));
    }

    #[Route('/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(
        Comment $comment,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Only author can delete
        if ($comment->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot delete this comment.');
        }

        if ($this->isCsrfTokenValid('delete-comment' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Comment deleted successfully!');
        }

        return $this->redirect($request->headers->get('referer', $this->generateUrl('app_dashboard')));
    }
}
